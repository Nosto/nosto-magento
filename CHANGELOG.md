# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.0.0-rc4]
### Fixed
- Fix modman mappings
- Handle currency conversions in line items

## [3.0.0-rc3]
### Added
- Add possibility to disable product updates to Nosto
- Update products to Nosto regardless of the product visibility
- Bump php-sdk version

### Removed
- Remove setting for javascript include style (using direct always)

### Fixed
- Fix duplicate header error in ajax calls (nginx)
- Improve payment provider handing in order

## [3.0.0-rc2]
### Added
- Support for using price variations
- Retouch of Nosto advanced settings view
- Update multi-currency method and currency rates to Nosto when changed
- Tagging for 404 pages and order confirmation page
- Tag for visitor checksum 
- Loosen up the data validation
- Support for coupon discounts on product level 
- Possibility to override variant block
- Fix the issue with long file paths in Magento packacking
- Tag products always in base currency if multiple currencies are used
- Change product observers to listen the "save after commit" event
- Enhance product and order exports
- Add search term tagging

### Fixed
- Use base currency in tagging if multiple currencies are used in a store level

## [3.0.0-rc1]
### Added
- Multi-currency + exchange rate support
- Account settings synchronisation support

## [2.6.10]
- Bug fixes

## [2.6.9]
- Fix resolving the payment provider
- Fix duplicate content-type header in admin controller

## [2.6.8]
- Tagging for 404 pages and order confirmation page
- Loosen up the data validation
- Support for coupon discounts on product level 
- Fix tagging currency for product
- Fix ACL handling after SUPEE-6285

## [2.6.7]
### Fixed
- Ignore order status in tagging and API calls if they are not set properly
- Ignore empty string values for order buyer information in tagging and API calls

## [2.6.6]
### Fixed
- Account removal for self-signup accounts through the iframe
- Uncaught "InvalidArgumentException" exception when order status is missing
a label

### Changed
- Make order buyer info optional in tagging and API calls

## [2.6.5]
### Added
- "external_order_ref" to order tagging and API requests in order to better
track orders
- "order_statuses" to order export in order to better track orders

## [2.6.4]
### Fixed
- Redirect to admin store after OAuth is finished

## [2.6.3]
### Fixed
- Tax price calculation for fixed priced bundle and grouped products

## [2.6.2]
### Fixed
- Account owner email address assignment when creating new account

## [2.6.1]
### Fixed
- Re-package for Enterprise Edition release

## [2.6.0]
### Added
- Better error handling and notifications to Nosto account OAuth cycle
- Support for bundle products with fixed pricing
- Support for using "tag1", "tag2" and "tag3" properties in Nosto product
tagging and product update API
- Support for Nosto partner code in account creation API
- Support for overriding meta data models

### Fixed
- Bug in cart/order item name tagging while running Magento Dev Mode
- Bug in product export while having product flat tables enabled
- Bug in order confirmation API when payment provider is no longer available

## [2.5.1]
### Fixed
- Re-packaged extension to support installs from Magento Connect with the latest
(1.9.2) Magento version.

## [2.5.0]
### Added
- Order status and payment provider info to order tagging
- Support for account specific sub-domains when configuring Nosto
- Support for choosing the product image version used in tagging

### Fixed
- Product update event observer store scope
- Cart/order tagging to always tag the same product ID as on the product page
the item was added to the cart/ordered from

## [2.4.0]
### Added
- JavaScript "add-to-cart" feature to enable adding products to cart directly
from recommendations

### Changed
- Deprecate product re-crawl feature and implement product update API in it's
place
- Refactor order status handling in order confirmations and order exports

## [2.3.0]
### Added
- Payment status info to server side order confirmations and order exports

## [2.2.1]
### Fixed
- Re-package for Enterprise Edition release

## [2.2.0]
### Changed
- Product re-crawl to send both id and url for products that have been added or
modified

## [2.1.3]
### Fixed
- Reading email address of account owner when creating new account

## [2.1.2]
### Fixed
- Product urls to be tagged with current store info so the crawler sees the
correct product data

## [2.1.1]
### Fixed
- Internal store view handling in backend to not apply the current store scope
when configuring nosto

## [2.1.0]
### Added
- New extension administration UI

### Fixed
- Product re-crawl API call to only load needed product meta data

## [2.0.1]
### Fixed
- Product tagging to check for tags only if Mage_Tag is enabled
- Add Mage_Core, Mage_Catalog and Mage_Sales as extension dependencies

## [2.0.0]
### Added
- Support current category tagging on product pages
- Update Nosto php-sdk

## [1.3.0]
### Added
- Support for product data re-crawling when a product is deleted
- Update Nosto php-sdk

### Fixed
- Offset for order/product history data exports
- Unmatched server-to-server order confirmations

## [1.2.0]
### Added
- New configuration page for the extension in the store backend
- Support for creating new Nosto accounts from the store backend
- Support for linking existing Nosto accounts from the store backend
- Support for German, Spanish and French localizations in the store backend
- Support for additional recommendation blocks on the shops home page
- Support for server-to-server order confirmations
- Support for order/product history data export upon Nosto account creation
- Support for product data re-crawling when product data changes in the store

## [1.1.7]
### Fixed
- Magento CE 1.9 dependency version

## [1.1.6]
### Fixed
- Magento CE 1.9 support

## [1.1.5]
### Fixed
- PHP 5.4 support

## [1.1.4]
### Fixed
- Nosto embed script update

## [1.1.3]
### Fixed
- PHP 5.3.28 compatibility

## [1.1.2]
### Fixed
- Repackaged the 1.1.1 extension with proper extension author name

## [1.1.1]
### Fixed
- Top list category is no longer created automatically
- The 'nosto_id' attribute is no longer added as a product category attribute
- Placeholder image (if configured) is used in recommendations if actual product
image is not available

## [1.1.0]
### Added
- Support for tagging grouped products
- New configuration setting for enabling/disabling collection of customer email
addresses

## [1.0.6]
### Fixed
- Install script could not create Top Sellers category while the "Use Flat
Catalog Category" option was enabled

## [1.0.5]
### Fixed
- Package updated with correct metadata

## [1.0.4]
### Fixed
- Remove unused observer event from config.xml

## [1.0.3]

## [1.0.2]

## [1.0.1]

## 1.0.0
### Added
- Initial release

[3.0.0-rc3]: https://github.com/nosto/nosto-magento-extension/compare/3.0.0-rc2...3.0.0-rc3
[3.0.0-rc2]: https://github.com/nosto/nosto-magento-extension/compare/3.0.0-1rc...3.0.0-rc2
[3.0.0-rc1]: https://github.com/Nosto/nosto-magento-extension/releases/tag/3.0.0-1rc

[unreleased]: https://github.com/nosto/nosto-magento-extension/compare/2.6.10...master
[2.6.10]: https://github.com/nosto/nosto-magento-extension/compare/2.6.9...2.6.10
[2.6.9]: https://github.com/nosto/nosto-magento-extension/compare/2.6.8...2.6.9
[2.6.8]: https://github.com/nosto/nosto-magento-extension/compare/2.6.7...2.6.8
[2.6.7]: https://github.com/nosto/nosto-magento-extension/compare/2.6.6...2.6.7
[2.6.6]: https://github.com/nosto/nosto-magento-extension/compare/2.6.5...2.6.6
[2.6.5]: https://github.com/nosto/nosto-magento-extension/compare/2.6.4...2.6.5
[2.6.4]: https://github.com/nosto/nosto-magento-extension/compare/2.6.3...2.6.4
[2.6.3]: https://github.com/nosto/nosto-magento-extension/compare/2.6.2...2.6.3
[2.6.2]: https://github.com/nosto/nosto-magento-extension/compare/2.6.1...2.6.2
[2.6.1]: https://github.com/nosto/nosto-magento-extension/compare/2.6.0...2.6.1
[2.6.0]: https://github.com/nosto/nosto-magento-extension/compare/2.5.1...2.6.0
[2.5.1]: https://github.com/nosto/nosto-magento-extension/compare/2.5.0...2.5.1
[2.5.0]: https://github.com/nosto/nosto-magento-extension/compare/2.4.0...2.5.0
[2.4.0]: https://github.com/nosto/nosto-magento-extension/compare/2.3.0...2.4.0
[2.3.0]: https://github.com/nosto/nosto-magento-extension/compare/2.2.1...2.3.0
[2.2.1]: https://github.com/nosto/nosto-magento-extension/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/nosto/nosto-magento-extension/compare/2.1.3...2.2.0
[2.1.3]: https://github.com/nosto/nosto-magento-extension/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/nosto/nosto-magento-extension/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/nosto/nosto-magento-extension/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/nosto/nosto-magento-extension/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/nosto/nosto-magento-extension/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/nosto/nosto-magento-extension/compare/1.3.0...2.0.0
[1.3.0]: https://github.com/nosto/nosto-magento-extension/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/nosto/nosto-magento-extension/compare/1.1.7...1.2.0
[1.1.7]: https://github.com/nosto/nosto-magento-extension/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/nosto/nosto-magento-extension/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/nosto/nosto-magento-extension/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/nosto/nosto-magento-extension/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/nosto/nosto-magento-extension/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/nosto/nosto-magento-extension/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/nosto/nosto-magento-extension/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/nosto/nosto-magento-extension/compare/1.0.6...1.1.0
[1.0.6]: https://github.com/nosto/nosto-magento-extension/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/nosto/nosto-magento-extension/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/nosto/nosto-magento-extension/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/nosto/nosto-magento-extension/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/nosto/nosto-magento-extension/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/nosto/nosto-magento-extension/compare/1.0.0...1.0.1

