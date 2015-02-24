# Nosto Tagging

The extension implements the required tagging blocks for using Nosto marketing automation service.

If the store uses a layout similar to the Magento default layout, the required tagging blocks will attach automatically
to the correct places. If the layout is heavily customized, the tagging blocks must be added manually to the correct
places in the layout.

After Nosto tagging has been implemented to the store, Nosto can produce product recommendations and other elements
back to the store. These elements are called Nosto elements and they can be added to the store by dropping simple
placeholder div-elements to correct places in the store.

By default the extension creates the following nosto elements:

* 4 elements on the front page ( "frontpage-nosto-1", "frontpage-nosto-2", "frontpage-nosto-3", "frontpage-nosto-4" )
* 3 elements to the product page ( "nosto-page-product1", "nosto-page-product2", "nosto-page-product3" )
* 3 elements to the shopping cart page ( "nosto-page-cart1", "nosto-page-car2, "nosto-page-cart3" )
* 2 elements to the product category page, top and bottom ( "nosto-page-category1"  and "nosto-page-category2" )
* 2 element to the search results page, top and bottom ( "nosto-page-search1 and "nosto-page-search2" )
* 2 elements to all pages, top and bottom ( "nosto-page-top" and "nosto-page-footer" )
* 2 elements to sidebars, 1 left and 1 right ( "nosto-column-left" and "nosto-column-right" )

Other ways to add new nosto elements to a page:

* Add block to CMS pages via editor: {{block type="nosto_tagging/element" div_id="unique-id" }}
* Add nosto element div directly to CMS or phtml files: <div class="nosto_element" id="unique-id"></div>

Here are the implemented Nosto tagging blocks described in case you need to manually add the blocks to your store's
layout:

* [Block Name] ( [what it does], [where should it be shown] )
* Embed ( the Nosto embed script, for all pages of the site )
* Product (product information to Nosto, for product pages )
* Category ( currently viewed product category to Nosto, for product category pages )
* Cart ( current shopping cart contents to Nosto, for all pages of the site)
* Customer ( current customer's name and email to Nosto, for all pages of the site if user has logged in )
* Order ( order details to Nosto, for order success page )
* Element ( placeholder where Nosto will produce content, anywhere )

## Installation

Please refer to the Magento documentation in order to get the module to appear in your site backend.

After installing the module, go to your site backend and click the "Nosto" menu item in the top menu. If you cannot see
this menu item, please clear the cache and try again. On the settings page you can create a Nosto account or attach a
existing Nosto account to each store you have configured in Magento. You will need one Nosto account per store where
you want to use Nosto.

In order to create a new account for a store, you first choose the store in the drop down menu at the top left of the
page. After this you choose "No" where it says "Do you have an existing Nosto account?". This shows you a input field,
where you should put the email address of the Nosto account owner, and a create button. Clocking this button will create
a new account through Nosto's API, and show you the configuration page for this account. You need to repeat this
procedure for each store you want to configure Nosto for.

If you already have a Nosto account, you can attach it to any of your stores by simply choosing "Yes" where it says
"Do you have an existing Nosto account?" and clicking the add button. This will take you to Nosto where you need to
authenticate by logging in with your Nosto account credentials. After this you are presented with a choice of Nosto
accounts that are linked with your Nosto user. You need to choose the account you want to attach to your store and
click the accept button. This will take you back to your shops backend and you should see the configuration page for
you account. Please note that you can abort the "attaching process" at any time.

Now the extension is ready for use. Please note that the extension will not make any visual changes to your shops front
office at this time. This is normal procedure as configurations in the Nosto backend are required for the extension to
be able to show any product recommendations.

Please refer to the Nosto documentation, or contact Nosto support, for information and instructions on how to proceed:
[Nosto support] (http://support.nosto.com)

## Extending

Here you can find some guidelines for extending the functionality added by this extension.

The Nosto marketing automation service supports additional data to be tagged than that included by this extension. For
a complete set of supported features, please refer to the official documentation at http://connect.nosto.com/tagging.

Example 1:

If you wish to add tag data to the product tagging, then the easiest way to achieve this is to override the
template file used to render the product tagging data.
The template file can be located in
"YOUR_MAGENTO_INSTALLATION/app/design/frontend/base/default/template/nostotagging/product.phtml".
You will need to do the following:

1. Copy the file to
 "YOUR_MAGENTO_INSTALLATION/app/design/frontend/YOUR_THEME_FOLDER/default/template/nostotagging/product.phtml"
2. Add the additional html for the tagging, as well as the logic to fetch the data to be tagged

That's it; now Magento should be able to recognise the overridden file and use that instead of the original.

## License

The extension is released under Open Software License ("OSL") v3.0

## Dependencies

* Magento Community Edition; v1.6.x.x - v1.9.x.x

## Changelog

### 2.0.1
* Fix product tagging to check for tags only if Mage_Tag is enabled
* Add Mage_Core, Mage_Catalog and Mage_Sales as extension dependencies

### 2.0.0
* Support current category tagging on product pages
* Update Nosto php-sdk

### 1.3.0
* Support for product data re-crawling when a product is deleted
* Update Nosto php-sdk
* Fix offset for order/product history data exports
* Fix unmatched server-to-server order confirmations

### 1.2.0
* New configuration page for the extension in the store backend
* Support for creating new Nosto accounts from the store backend
* Support for linking existing Nosto accounts from the store backend
* Support for German, Spanish and French localizations in the store backend
* Support for additional recommendation blocks on the shops home page
* Support for server-to-server order confirmations
* Support for order/product history data export upon Nosto account creation
* Support for product data re-crawling when product data changes in the store

### 1.1.7
* Magento CE 1.9 dependency version fix

### 1.1.6
* Magento CE 1.9 support

### 1.1.5
* PHP 5.4 support

### 1.1.4
* Nosto embed script update

### 1.1.3
* PHP 5.3.28 compatibility

### 1.1.2
* Repackaged the 1.1.1 extension with proper extension author name. 

### 1.1.1
* Top list category is no longer created automatically
* 'nosto_id' attribute is no longer added as a product category attribute.
* Placeholder image (if configured) is used in recommendations if actual product image is not available.  

### 1.1.0
* Support for tagging grouped products
* New configuration setting for enabling/disabling collection of customer email-addresses

### 1.0.6
* Fix: install script could not create Top Sellers category while the "Use Flat Catalog Category" option was enabled

### 1.0.5
* Dist package updated with correct metadata

### 1.0.4
* Fix: remove unused observer event from config.xml

### 1.0.3

### 1.0.2

### 1.0.1

### 1.0.0
* initial release

## Known issues

* Does not support bundle products with fixed price setting
* Does not support bundle products including products that are not listed individually in the store
* The default position of the top nosto element on both the category page and the search result page is above the page
title. It may be relevant to move the element below the title. In order to do this you need to first remove it from
the layout by using the "remove" tag in your local.xml file. Then you need to add a div element directly in the .phtml
file that contains the page layout. In the Magento default theme the files are located in
"app\design\frontend\base\default\template\catalog\category\view.phtml" and
"app\design\frontend\base\default\template\catalogsearch\result.phtml". However, the path may vary if you are using a
custom theme.
