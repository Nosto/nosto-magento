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

## Documentation

For more information, please see our exhaustive [wiki](https://github.com/Nosto/nosto-magento/wiki)

## License

Open Software License ("OSL") v3.0

## Dependencies

Magento Community Edition v1.6.x.x - v1.9.x.x or Magento Enterprise Edition v1.11.x - v1.14.x
