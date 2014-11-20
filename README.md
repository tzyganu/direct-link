Easylife Direct Link
========

A small Magento module that changes the links of the configurable products in the product list to point to the product page with the configurable options already selected depending on the filters applied.

Version
--------

1.0.0

Compatibility
----------
The extension is compatible with Magento CE versions 1.7 and above.  
It probably works on older versions. I just didn't test because I don't care.  

What it does 
----------

An example explains it better.  
Let's say you have in your store configurable products that can be configured based on size and color (in this order).  
When looking at a category page or search results page and you filter the results by size and color, the configurable products urls will be changed so they point to the product page and have the filtered options already selected.  
For example if you have a t-shirt that has among the available sizes "XL" and among the colors "Black" and you filter the category products or search products by size:xl and color:black, the link to that t-shirt will get you to the t-shirt page with the size xl and color black already selected.  

If you filter the products list only by size XL, the XL size will be selected in the product page.  
But there is a limitation. Using the example above, if you filter the list only by color, none of the options will be selected because magento works that way. The configurable options are dependent.  
So you can autoselect the options in the order they are displayed in the product view page.

Configuration
----------

In the configuration section the extensiona adds a new tab called 'Easylife Direct Link'.  
In this section you can configure the following fields.  

 - **Enabled** - this enables/disables the functionality of the extension.
 - **Enabled for catalog pages** - this enables the functionality for category pages/
 - **Product block name in catalog pages** - In order not to rewrite anything, this extension takes the products calling `getLoadedProductCollection` on the block that lists the products.  
   Depending on the page you are in, and on how your theme is built, this block can have different names in the layout. You need to add the block layout name in here.  
   Usually you find it in `catalog.xml` layout file. `<block type="catalog/product_list" name="product_list" template="catalog/product/list.phtml">`. The `name` attribute is what you are looking for.
 - **Filters block name in catalog pages** - similar to the approach on getting the products, the filters are retrieved by calling `getActiveFilters` from the filter state block. Here you need to fill in the name of the filters block.  
   You will find it in the same `catalog.xml` layout file.  `<block type="catalog/layer_view" name="catalog.leftnav" after="currency" template="catalog/layer/view.phtml"/>`. Again, the `name` attribute is what you need.  
 - **Enabled for quick search page** - Enables the functionality for the quick search result page
 - **Product block name in quick search results pages** - this is the same thing as **Product block name in catalog pages**, but for quick search it can be found in `catalogsearch.xml` under the `catalogsearch_result_index` layout handle.
   `<block type="catalog/product_list" name="search_result_list" template="catalog/product/list.phtml">`. See how the name is different. That's why these configuration settings are needed.  
 - **Filters block name in quick search results pages** - similar to **Filters block name in catalog pages** but found in `catalogsearch.xml`.  
 - **Enabled for advanced search page** - Enables the functionality in the advanced search results page. By default Magento does not support filters in the advanced search results page. But in case you implement it, you already have the support for this.
 - **Product block name in advanced search results pages** - The same as **Product block name in catalog pages** but for advanced search results page.
 - **Filters block name in advanced search results pages** - the same as **Filters block name in catalog pages** but for advances search results page.  
 
How it does it
----------
The extension gets all the listed products. The same instance of the product collection is used so the database is not touched. It looks for the configurable products that has options similar to the applied filters and changes all the links in the page for the products found by adding #attrId=attrValue at the end of the URL.  

Extending the module
-------

If you have custom pages that list products and you want the same functionality, you can extend this module without modifying it.  
You need to create 3 more system fields similar to the ones above. To enable the functionality for your pages and the product list block name and filter block name.  
Observe the event `easylife_directlink_page_types` and add the key of your page in the list of allowed pages.  
and in the layout file for your module add this:

    <your_page_handle><!-- change this to fit your needs -->
        <update handle="easylife_directlink" />
        <reference name="easylife_directlink_config">
            <action method="setPageType">
                <page_type>YOUR PAGE KEY HERE</page_type><!-- the page key can be anything. Just don't use `catalog`, `search` or `search_advanced` and make sure it's the same key as the one you set in the observer -->
            </action>
        </reference>
    </your_page_handle>
	
Rewrites
------
The extension does not rewrite any core class

Issues
-------
[Please report any issue or feature request in here](https://github.com/tzyganu/direct-link/issues)

License
-----
The module is released under the [MIT license](http://opensource.org/licenses/mit-license.php).  
You can find a [copy of the license here](https://github.com/tzyganu/direct-link/blob/master/LICENSE_DIRECT_LINK.txt)