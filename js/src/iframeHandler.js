/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Nosto
 * @package   Nosto_Tagging
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

(function (window) {
    'use strict';

    /**
     * Nosto iframe postMessage() handler.
     *
     * @param {Object} data the data sent by Nosto.
     * @param {Object} options the options for the requests.
     */
    var handlePostMessage = function(data, options) {
        var TYPE_NEW_ACCOUNT = "newAccount",
            TYPE_CONNECT_ACCOUNT = "connectAccount",
            TYPE_REMOVE_ACCOUNT = "removeAccount";

        var settings = extendObject({
                iframeId: "nosto_iframe",
                urls: {
                    createAccount: "",
                    connectAccount: "",
                    deleteAccount: ""
                }
        }, options);
        var $iframe = document.getElementById(settings.iframeId);

        switch (data.type) {
            case TYPE_NEW_ACCOUNT:
                xhr(settings.urls.createAccount, {
                    data: {email: data.params.email},
                    success: function (response) {
                        if (response.success && response.redirect_url) {
                            $iframe.src = response.redirect_url;
                        }
                    }
                });
                break;

            case TYPE_CONNECT_ACCOUNT:
                xhr(settings.urls.connectAccount, {
                    success: function (response) {
                        if (response.success && response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else if (!response.success && response.redirect_url) {
                            $iframe.src = response.redirect_url;
                        }
                    }
                });
                break;

            case TYPE_REMOVE_ACCOUNT:
                xhr(settings.urls.deleteAccount, {
                    success: function (response) {
                        if (response.success && response.redirect_url) {
                            $iframe.src = response.redirect_url;
                        }
                    }
                });
                break;

            default:
                throw new Error("Nosto: invalid postMessage `type`.");
        }
    };

    /**
     * Creates a new XMLHttpRequest.
     *
     * Usage example:
     *
     * xhr("http://localhost/target.html", {
     *      "method": "POST",
     *      "data": {"key": "value"},
     *      "success": function (response) { // handle success request }
     * });
     *
     * @param {String} url the url to call.
     * @param {Object} params optional params.
     */
    function xhr(url, params) {
        var options = extendObject({
            method: "POST",
            async: true,
            data: {}
        }, params);
        // Always add the Magento form_key property for request authorization.
        options.data.form_key = window.FORM_KEY;
        var oReq = new XMLHttpRequest();
        if (typeof options.success === "function") {
            oReq.addEventListener("load", function (e) {
                options.success(JSON.parse(e.target.response));
            }, false);
        }
        oReq.open(options.method, decodeURIComponent(url), options.async);
        oReq.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        oReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        oReq.send(buildQueryString(options.data));
    }

    /**
     * Extends a literal object with data from the other object.
     *
     * @param {Object} obj1 the object to extend.
     * @param {Object} obj2 the object to extend from.
     * @returns {Object}
     */
    function extendObject(obj1, obj2) {
        for (var key in obj2) {
            if (obj2.hasOwnProperty(key)) {
                obj1[key] = obj2[key];
            }
        }
        return obj1;
    }

    /**
     * Builds a query string based on params.
     *
     * @param {Object} params the params to turn into a query string.
     * @returns {string} the built query string.
     */
    function buildQueryString(params) {
        var queryString = "";
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                if (queryString !== "") {
                    queryString += "&";
                }
                queryString += encodeURIComponent(key)+"="+encodeURIComponent(params[key]);
            }
        }
        return queryString;
    }

    // Define the "Nosto" global namespace if not already defined.
    window.Nosto = window.Nosto || {};

    // Expose the "handlePostMessage" method in the Nosto namespace.
    window.Nosto.handlePostMessage = handlePostMessage;

})(window || {});
