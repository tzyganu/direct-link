<?php
/**
 * Easylife_DirectLink extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE_DIRECT_LINK.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       Easylife
 * @package        Easylife_DirectLink
 * @copyright      2014 Marius Strajeru
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class Easylife_DirectLink_Block_Config extends Mage_Core_Block_Template
{
    const PAGE_TYPE_CATALOG = 'catalog';
    const PAGE_TYPE_SEARCH = 'search';
    const PAGE_TYPE_SEARCH_ADVANCED = 'search_advanced';
    const DEFAULT_PAGE_TYPE = 'catalog';

    const XML_ENABLED_PATH = 'easylife_directlink/settings/enabled';
    /**
     * @var array
     */
    protected $_pageTypes;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_ENABLED_PATH);
    }

    /**
     * @param null $page
     * @return bool
     */
    public function isEnabledForPageType($page = null)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        if (is_null($page)) {
            $page = $this->getPageType();
        }
        return Mage::getStoreConfigFlag('easylife_directlink/settings/enabled_'.$page);
    }
    /**
     * @return string|bool
     */
    public function getConfigJson()
    {
        if (!$this->isEnabledForPageType()) {
            return false;
        }
        $config = array();
        $blockName = $this->getListBlockName();
        /** @var Mage_Catalog_Block_Product_List $block */
        $block = $this->getLayout()->getBlock($blockName);
        $filtersBlockName = $this->getFilterBlockName();
        $filtersBlock = $this->getLayout()->getBlock($filtersBlockName);
        $stateBlock = false;
        if ($filtersBlock) {
            /** @var Mage_Catalog_Block_Layer_State $stateBlock */
            $stateBlock = $filtersBlock->getChild('layer_state');
        }
        if ($block && $stateBlock) {
            if (is_callable(array($block, 'getLoadedProductCollection')) &&
                is_callable(array($stateBlock, 'getActiveFilters'))
            ) {
                $filters = $stateBlock->getActiveFilters();
                if (count($filters) == 0) {
                    return false;
                }
                $filteredAttributes = array();
                foreach ($filters as $filter) {
                    /** @var Mage_Catalog_Model_Layer_Filter_Item $filter */
                    $filteredAttributes[$filter->getFilter()->getRequestVar()] = $filter->getValue();
                }
                if (count($filteredAttributes) == 0) {
                    return false;
                }
                $products = $block->getLoadedProductCollection();
                foreach ($products as $product) {
                    /** @var Mage_Catalog_Model_Product $product */
                    $links = $this->getDirectLinks($product, $filteredAttributes);
                    $config = array_merge($config, $links);
                }
            }
            return $this->getCoreHelper()->jsonEncode($config);
        }
        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $filteredAttributes
     * @return array
     */
    public function getDirectLinks(Mage_Catalog_Model_Product $product, $filteredAttributes)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return array();
        }
        /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $productConfig = array();
        $directLinks = array();
        $configurableAttributes = $typeInstance->getConfigurableAttributes($product);
        foreach ($configurableAttributes as $configurableAttribute) {
            $attributeCode = $configurableAttribute->getProductAttribute()->getAttributeCode();
            if (isset($filteredAttributes[$attributeCode])) {
                $attributeId = $configurableAttribute->getProductAttribute()->getId();
                $productConfig[$attributeId] = $filteredAttributes[$attributeCode];
            }
        }
        if (count($productConfig) > 0) {
            $productUrl = $product->getProductUrl();
            $newUrl = $productUrl;
            $hashSet = false;
            foreach ($productConfig as $attributeId => $attributeValue) {
                if (!$hashSet) {
                    $newUrl .= '#';
                    $hashSet = true;
                }
                else {
                    $newUrl .= '&';
                }
                $newUrl .= $attributeId.'='.$attributeValue;
            }
            $directLinks[$productUrl] = $newUrl;
        }
        return $directLinks;
    }
    /**
     * @return string
     */
    public function getListBlockName()
    {
        $pageType = $this->getPageType();
        return Mage::getStoreConfig('easylife_directlink/settings/list_block_name_'.$pageType);
    }

    /**
     * @return mixed
     */
    public function getFilterBlockName()
    {
        $pageType = $this->getPageType();
        return Mage::getStoreConfig('easylife_directlink/settings/filter_block_name_'.$pageType);
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('core');
    }

    public function getPageType()
    {
        if (!$this->hasData('page_type') || !in_array($this->getData('page_type'), $this->getAllowedPageTypes())) {
            $this->setData('page_type', self::DEFAULT_PAGE_TYPE);
        }
        return $this->getData('page_type');
    }
    /**
     * @return array
     */
    public function getAllowedPageTypes()
    {
        if (is_null($this->_pageTypes)) {
            $pageTypes = array(
                self::PAGE_TYPE_CATALOG,
                self::PAGE_TYPE_SEARCH,
                self::PAGE_TYPE_SEARCH_ADVANCED
            );
            $pageTypeObject = new Varien_Object(
                array('page_types' => $pageTypes)
            );
            Mage::dispatchEvent('easylife_directlink_page_types', array('types' => $pageTypeObject));
            $this->_pageTypes = $pageTypeObject->getData('page_types');
        }
        return $this->_pageTypes;
    }
}
