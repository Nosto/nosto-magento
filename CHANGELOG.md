All notable changes to this project will be documented in this file. This project adheres to Semantic Versioning.

### 3.11.3
* Remove function parameter type check for compatibility with PHP < 5.6

### 3.11.2
* Implement new add multiple products to cart method to fix cart products having no image

### 3.11.1
* Fix add multiple products to cart javascript function where some products added could have no image or link

### 3.11.0
* Use mock products in category personalisation when preview mode is enabled
* Ignore non-numeric product ids in Graphql responses
* Add inventory level to SKU

### 3.10.0
* Add Nosto category personalization in the default Magento category sorting options

### 3.9.0
* Feature flag to set the a percentage of total PHP available memory that can be used by Nosto indexer
* Exit indexer gracefully if the memory consumption goes over the predefined amount
* Include active domain and Nosto account in API calls
* Encode HTML characters automatically
* Remove feature flag for real-time cart updates

### 3.8.5
* Handle overriding custom group pricing 

### 3.8.4
* Handle duplicate key exception gracefully when saving Nosto customer

### 3.8.3
* Bump Nosto SDK version to fix the double encoded Oauth redirect URL
* Add null check to customer addresses

### 3.8.2
* Bump Nosto SDK version to support HTTP 2

### 3.8.1
* Bump magazine maximum PHP version to 7.3.0

### 3.8.0
* Add expiry check for Nosto indexed products
* Add possibility to define quantity for Nosto’s add to cart
* Fix bug for checking if graphql feature is available
* Refactor method names
* Add support for fetching category list sorting from Nosto (experimental)
* Escape search term in tagging
* Update Marketing Permission Via Api Call
* Introduce CLI tools for reconnecting and removing Nosto account

### 3.7.8
* Add a feature flag to disable the sending of inventory level when an order is made

### 3.7.7
* Fix an issue that would cause product attributes and custom fields to have a wrong translation 

### 3.7.6
* Fix an issue in Magento Enterprise that would prevent to save a simple product if the logged user does not have permission to see the parent product.

### 3.7.5
* Handle line items without concrete product in order confirmation
* Fix price variation when customer group variation has zero price

### 3.7.4
* Add explicit boolean check for product building

### 3.7.3
* Add missing hcid from customer block and cart meta

### 3.7.2
* Fix customer tagging throwing too many exceptions when user is not logged-in

### 3.7.1
* Fix an issue that prevents the Page Type to be rendered in the Nosto Debug Toolbar

### 3.7.0
* Implement support to add multiple products to cart via Javascript 
* Implement programmatic tagging generation 

### 3.6.5
* Improve date time handling for Vaimo KCO orders
* Fix duplicate check for custom fields in SKUs

### 3.6.4
* Fix the issue with additional URL parameters being escaped in restore cart redirection URL
* Add setting for choosing the redirect location after cart has been restored

### 3.6.3
* Fix the issue that the price of discounted bundle products could be tagged as 0 

### 3.6.2
* Handle downloadable and virtual products in ordered items

### 3.6.1
* Add advanced setting to disable sending customer data to Nosto servers

### 3.6.0
* Improve error bubbling in Nosto account opening and in API calls
* Add marketing permission consent for user tagging in order confirmation (GDPR compatibility) 

### 3.5.5
* Fix the bundle product list price was incorrect if there is any optional item

### 3.5.4
* Invalidate Nosto index if settings affecting the product data has been changed
* Revise some instructional texts

### 3.5.3
* Fix the issue that add to cart popup shows child product added to cart when adding a bundle product to cart

### 3.5.2
* Improve add to cart popup trigger

### 3.5.1
* Remove deleted products from Nosto index
* Use discontinue endpoint for removing (discontinuing)products in Nosto 
* Enable custom field tagging by default

### 3.5.0
* Introduce custom indexer for Nosto product data
* Update Nosto product data after catalog price rules are saved and applied
* Add user defined attributes as custom fields to Nosto product
* Add boolean return for data loaders
* Use fixed price for bundled product if defined

### 3.4.0 (skipped)

### 3.3.7
* Add validation for Nosto product before product tagging, sending product over the API and before exporting

### 3.3.6
* Fix currency conversion issue in cart tagging
* Add SKU id in cart tagging and order tagging
* Apply catalog price rules also for product API updates
* Change the sorting order of Nosto settings in store admin

### 3.3.5
* Handle orders when Vaimo KCO module is enabled but not used for order
* Fix issues with code style checks
* Add support Magento compilation mode

### 3.3.4
* Fix the list price for bundle product was aways same as final price

### 3.3.3
* Exclude OutOfStock simple products from configurable product price determination
* Add a link to configuration page
* Enable rating and review tagging by default
* Get product attribute type id in runtime instead of hard coded it to 4
* Fix phan settings and phpcs settings

### 3.3.2
* Update composer dependencies
* Update Magento package dependencies
* Define PHP requirements

### 3.3.1
* Fix the issue that new product url contains '_ignore_category' when flat catalog is enabled

### 3.3.0
* Send client’s phone number, postal code and country in order data 
* Improve error handling and error recovery in Nosto account creation 
* Verify dependencies (Nosto PHP SDK) before running Nosto

### 3.2.0
* Add support for group price and catalog price rule using price variation
* Update rating and reviews to Nosto whenever they are changed or new ones are added
* Respect tax rules in product tagging
* Dispatch events after Nosto product, Nosto order or Nosto cart is loaded
* Fix bug in SKU availability

### 3.1.2
* Rename RestoreCartController to CartController to avoid issues with case sensitivity

### 3.1.1
* Rename low stock builder class
* Add possibility enable / disable low stock tagging

### 3.1.0
* Add support for adding SKUs to cart from Nosto recommendations
* Add low stock indication to product tag1 attribute
* Add possibility to use custom thumbnail URL in recommendations
* Render Nosto javascript stub only when Nosto account is installed
* Add debug info if Nosto's product model has been overridden

### 3.0.2
* Fix the attribute name for SKU custom fields

### 3.0.1
* Update SDK to fix the missing of default nosto backend url issue

### 3.0.0
Major changes
* Add support for restore cart link and create cart restore functionality
* Add support for product variations (SKUs)
* Possibility to enable / disable Nosto features from advanced settings
* Introduce "pearify" to generate (rename) autoloadable classes from 3rd party libraries / composer packages
* Refactor the codebase to use Nosto PHP SDK version 3.0.0
* Refactor signup and oauth to use ready implementations from SDK
   
Bugfixes and enhancements
* Fix the add to cart function to respect the store's secure (https) settings
* Fix the order handling to support multiple Klarna extensions
* Fix custom attribute handling for arrays
* Fix displaying the exchange rate cron drop down
* Introduce helper for logging

### 2.11.4
* Fix the array keys in category building
* Include only active categories into tagging

### 2.11.3
* Remove price formatting from product prices
* Implement method for loading exchange rates into the collection

### 2.11.2
* Fixed a bug that caused issues when flat tables were enabled and a product was updated from the main store scope

### 2.11.1
* Add support for Magento core modules version 1.9.3.2

### 2.11.0
* Refactor the extension to meet Magento Extension Quality Program Coding Standard (MEQP1)

### 2.10.0
* Add support for sending following attributes to Nosto
 * rating and reviews data to Nosto (Magento native ratings and Yotpo)
 * alternative images
 * inventory level
 * supplier cost
* Improve the comparison of original installed store url and the current one
* Send integrated modules info to Nosto
* Technical improvements
 * Send inventory level to Nosto after purchase
 * Create product service and refactor order services
 * Add support for sending batch updates via product service

### 2.9.0
* Add support for Vaimo Klarna checkout extension
* Add support for new product attributes
* Add "js stub" for Nosto javascript
* Add possibility to run Nosto exchange rate cron hourly
* Fix issue with configurable product visibility
* Fix oauth redirection URL issue when store codes are added to URLs
* Display applicable Nosto settings in all configuration scopes
* Display errors if Nosto account creation or account connect fails
* Move source models used by Nosto admin configuration under a single directory
* Allow zero as a unit price for ordered item
* Fetch min price from variation when configurable (parent) product doesn't have a price defined
* Send missing API tokens to Nosto

### 2.8.2
* Change the customer reference type to be text

### 2.8.1
* Add css for hiding the page type tagging

### 2.8.0
* Add support for using multiple currencies
* Add notifications for incomplete or invalid Nosto settings
* Add support for PHP 7
* Remove field date_published from product tagging
* Add possibility to choose attribute for brand tagging
* Add page type tagging
* Check that Nosto account is not installed into a different domain
* Use direct include for Nosto tagging
* Introduce customer reference for Nosto

### 2.7.1
* Fix product URL generation for Magento EE

### 2.7.0
* Introduce product attribute tagging 
* Support for product URLs without store parameter
* Force Oauth to use always the base url
* Define recommendation as viewed when calling Nosto's addToCart method

### 2.6.15
* Add support for sending the account details & new platform UI
* Add check if table `nosto_tagging_customer` already exists before creating it
* Add visitor checksum tagging
* Strip out _Main Website_ from the default account title
* Disable tagging categories if there are hidden categories in the category path

### 2.6.14
* Support for SDK version headers

### 2.6.13
* Update modman settings
* Bug fixes for multi currency handling

### 2.6.12
* Restore the type casting to quantity

### 2.6.11
* Hide hidden categories from tagging
* Remove type casting from templates
* Loosen up data validation for order items
* Add search term tagging
* Update products to Nosto regardless of the product visibility

### 2.6.10
* Bug fixes

### 2.6.9
* Fix resolving the payment provider
* Fix duplicate content-type header in admin controller

### 2.6.8
* Tagging for 404 pages and order confirmation page
* Loosen up the data validation
* Support for coupon discounts on product level 
* Fix tagging currency for product
* Fix ACL handling after SUPEE-6285

### 2.6.7
* Ignore order status in tagging and API calls if they are not set properly
* Ignore empty string values for order buyer information in tagging and API calls

### 2.6.6
* Fix account removal for self-signup accounts through the iframe
* Fix uncaught "InvalidArgumentException" exception when order status is missing
a label
* Make order buyer info optional in tagging and API calls

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

