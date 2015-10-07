# Personalization for Magento

Increase your conversion rate and average order value by delivering your
customers personalized product recommendations throughout their shopping
journey.

Nosto allows you to deliver every customer a personalized shopping experience
through recommendations based on their unique user behavior - increasing
conversion, average order value and customer retention as a result.

[http://nosto.com](http://nosto.com/)

## Getting started

### How it works

The extension automatically adds product recommendation elements to the store
when installed. Basically, empty "div" placeholder elements. These elements will
appear on the home page, product pages, category pages, search result pages and
the shopping cart page. These elements are automatically populated with product
recommendations from your store.

This is possible by mining data from the store when the user visits the pages.
For example, when the user is browsing a product page, the product information
is asynchronously sent to Nosto, that in turn delivers product recommendations
based on that product to the store and displays them to the user.

The more users that are visiting the site, and the more page views they create,
the better and more accurate the recommendations become.

In addition to the recommendation elements and the real time data gathering, the
extension also includes some behind the scenes features for keeping the product
information up to date and keeping track of orders in the store.

Every time a product is updated in the store, e.g. the price is changed, the
information is sent to Nosto over an API. This will sync the data across all
the users visiting the store that will see up to date recommendations.

All orders that are placed in the store are also sent to Nosto. This is done to
keep track of the orders that were a direct result of the product
recommendations, i.e. when a user clicks a product in the recommendation,
adds it to the shopping cart and places the order.

Nosto also keeps track of the order statuses, i.e. when an order is changed to
"payed" or "canceled" the order is updated over an API.

All you need to take Nosto into use in your store, is to install the extension
and create a Nosto account for your store. This is as easy as clicking a button,
so read on.

### Installing

The preferred way of installing the extension is through the Magento Connect
Manager. It can, however, also be installed as a local extension package or
directly from the GitHub repository if needed.

#### Magento Connect (preferred)

The preferred way of installing the extension is through Magento Connect.

1. Open the Magento Connect Manager from your Magento backend, and click the
connect link under the "Install New Extensions" section
2. Search for the Nosto extension to find the [Nosto extension
page](http://www.magentocommerce.com/magento-connect/nosto-personalization-for-magento.html)
3. Click the "Install Now" button and copy the install link
4. Go back to your Magento installation and paste the link into the text field
under the "Install New Extensions" section and click "install"
5. Wait for the the installation to finish and go back to the Magento admin
6. Learn how to [configure](#configuration) the extension

#### Local

The extension can also be installed as a local package by uploading the
extension package archive manually in the Magento Connect Manager, or by
unpacking it directly into the Magento installation directory which will place
the files and folders in the correct places.

The extension package archive can be obtained from the projects
[releases](https://github.com/Nosto/nosto-magento-extension/releases) page on
GitHub.

Note: do NOT download the "source code" as that will not include the needed
dependencies for the extension, instead use the "Nosto_Tagging-x.x.x.tgz"
archive.

#### Repository

For development purposes the plugin can be installed directly from the GitHub
repository by cloning the project to your server. For the extension to work,
it's dependencies also need to be installed. For this we recommend using
[composer](https://getcomposer.org/), which is a dependency manager for PHP. By
executing `composer install` in the root folder of the cloned extension, the
dependencies will automatically be fetched and installed in a `vendor` folder
relative to the extension root directory.

After this you need to either copy or symlink the files and folders to the
correct locations in Magento. You can following the guidelines in the "modman"
file located in the extension root folder.

### Configuration

The extension creates a new menu item, to the backend top menu, during
installation. Note that you may have to clear the cache for the menu item to
show up.

By clicking the menu item, you will be redirected to the Nosto account
configuration page were you can create and manage your Nosto accounts. You will
need a Nosto account for each store view in the installation.

Creating the account is as easy as clicking the install button on the page. Note
the email field above it. You will need to enter your own email to be able to
activate your account. After clicking install, the window will refresh and show
the account configuration.

You can also connect and existing Nosto account to a store, by using the link
below the install button. This will take you to Nosto where you choose the
account to connect, and you will then be redirected back where you will see the
same configuration screen as when having created a new account.

This concludes the needed configurations in Magento. Now you should be able to
view the default recommendations in your stores frontend by clicking the preview
button on the page.

You can read more about how to modify Nosto to suit your needs in our
[support center](https://support.nosto.com/), where you will find Magento
related documentation and guides.

## License

Open Software License ("OSL") v3.0

## Dependencies

* Magento Community Edition v1.6.x.x - v1.9.x.x

## Changelog

### 2.6.5
* Add "external_order_ref" to order tagging and API requests in order to better
track orders

### 2.6.4
* Fix redirect to admin store after OAuth is finished

### 2.6.3
* Fix tax price calculation for fixed priced bundle and grouped products

### 2.6.2
* Fix account owner email address assignment when creating new account

### 2.6.1
* Re-package for Enterprise Edition release

### 2.6.0
* Add better error handling and notifications to Nosto account OAuth cycle
* Add support for bundle products with fixed pricing
* Add support for using "tag1", "tag2" and "tag3" properties in Nosto product
tagging and product update API
* Add support for Nosto partner code in account creation API
* Add support for overriding meta data models
* Fix bug in cart/order item name tagging while running Magento Dev Mode
* Fix bug in product export while having product flat tables enabled
* Fix bug in order confirmation API when payment provider is no longer available

### 2.5.1
* Re-packaged extension to support installs from Magento Connect with the latest
(1.9.2) Magento version.

### 2.5.0
* Add order status and payment provider info to order tagging
* Add support for account specific sub-domains when configuring Nosto
* Add support for choosing the product image version used in tagging
* Fix product update event observer store scope
* Fix cart/order tagging to always tag the same product ID as on the product
page the item was added to the cart/ordered from

### 2.4.0
* Deprecate product re-crawl feature and implement product update API in it's
place
* Add JavaScript "add-to-cart" feature to enable adding products to cart
directly from recommendations
* Refactor order status handling in order confirmations and order exports

### 2.3.0
* Add payment status info to server side order confirmations and order exports

### 2.2.1
* Add Enterprise support

### 2.2.0
* Change product re-crawl to send both id and url for products that have been
added/modified

### 2.1.3
* Fix reading email address of account owner when creating new account

### 2.1.2
* Fix product urls to be tagged with current store info so the crawler sees the
correct product data

### 2.1.1
* Fix internal store view handling in backend to not apply the current store
scope when configuring nosto

### 2.1.0
* New extension administration UI
* Fix product re-crawl API call to only load needed product meta data

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
* Placeholder image (if configured) is used in recommendations if actual product
image is not available.

### 1.1.0
* Support for tagging grouped products
* New configuration setting for enabling/disabling collection of customer email-
addresses

### 1.0.6
* Fix: install script could not create Top Sellers category while the "Use Flat
Catalog Category" option was enabled

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

* The default position of the top nosto element on both the category page and
the search result page is above the page title. It may be relevant to move the
element below the title. In order to do this you need to first remove it from
the layout by using the "remove" tag in your local.xml file. Then you need to
add a div element directly in the .phtml file that contains the page layout. In
the Magento default theme the files are located in
"app\design\frontend\base\default\template\catalog\category\view.phtml" and
"app\design\frontend\base\default\template\catalogsearch\result.phtml". However,
the path may vary if you are using a custom theme.
