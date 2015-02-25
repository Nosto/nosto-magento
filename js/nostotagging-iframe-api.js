/**
 * Nosto Iframe API.
 * Used to communicate between the Nosto iframe and the shop this script is included in.
 *
 * @link      https://developer.mozilla.org/en-US/docs/Web/API/Window.postMessage
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2015 Nosto Solutions Ltd
 * @license   Unknown
 */

// Define the "Nosto" namespace if not already defined.
if (typeof Nosto === "undefined") {
    var Nosto = {};
}

/**
 * Nosto iframe API.
 *
 * @param {Object} options
 */
Nosto.iframe = function(options) {
    var TYPE_NEW_ACCOUNT = "newAccount",
        TYPE_CONNECT_ACCOUNT = "connectAccount",
        TYPE_REMOVE_ACCOUNT = "removeAccount";

    /**
     * @type {Object}
     */
    var settings = {
        iframe: null,
        urls: {
            createAccount: "",
            connectAccount: "",
            deleteAccount: ""
        },
        xhrParams: {}
    };

    /**
     * Window.postMessage() event handler for catching messages from nosto.
     *
     * Supported messages must come from nosto.com and be formatted according
     * to the following example:
     *
     * { "type": "the message action", "params": {} }
     *
     * @param {Object} event
     */
    function receiveMessage(event)
    {
        // Check the origin to prevent cross-site scripting.
        // todo: enable origin restriction
//        if (event.origin !== "https://my.nosto.com" && event.origin !== "https://staging.nosto.com") {
//            return;
//        }

        var data = event.data ? JSON.parse(event.data) : null;
        if (typeof data === "object" && data.type) {
            switch (data.type) {
                case TYPE_NEW_ACCOUNT:
                    xhr(settings.urls.createAccount, {
                        data: {email: data.email},
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.success && response.redirect_url) {
                                console.log("redirecting: " + response.redirect_url);
                                settings.iframe.src = response.redirect_url;
                            } else {
                                // todo: handle failure
                                console.debug("FAIL", e);
                            }
                        }
                    });
                    break;

                case TYPE_CONNECT_ACCOUNT:
                    xhr(settings.urls.connectAccount, {
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.success && response.redirect_url) {
                                console.log("redirecting: " + response.redirect_url);
                                window.location.href = response.redirect_url;
                            } else {
                                // todo: handle failure
                                console.debug("FAIL", e);
                            }
                        }
                    });
                    break;

                case TYPE_REMOVE_ACCOUNT:
                    xhr(settings.urls.deleteAccount, {
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.success && response.redirect_url) {
                                console.log("redirecting: " + response.redirect_url);
                                settings.iframe.src = response.redirect_url;
                            } else {
                                // todo: handle failure
                                console.debug("FAIL", e);
                            }
                        }
                    });
                    break;
            }
        }
    }

    /**
     * Creates a new XMLHttpRequest.
     *
     * Usage example:
     *
     * xhr("http://localhost/target.html", {
     *      "method": "POST",
     *      "data": {"key": "value"},
     *      "success": function (e) { // handle success request },
     *      "error": function (e) { // handle failure request }
     * });
     *
     * @param {String} url the url to call.
     * @param {Object} params optional params.
     */
    function xhr(url, params) {
        var options = extend({
            method: "POST",
            async: true,
            data: {}
        }, params);

        extend(options.data, settings.xhrParams);
        var payload = buildParams(options.data);

        var oReq = new XMLHttpRequest();
        if (typeof options.success === "function") {
            oReq.addEventListener("load", options.success, false);
        }
        if (typeof options.error === "function") {
            oReq.addEventListener("error", options.error, false);
        }
        oReq.open(options.method, url, options.async);
        oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        oReq.send(payload);
    }

    /**
     * Extends a literal object with data from the other object.
     *
     * @param {Object} obj1 the object to extend.
     * @param {Object} obj2 the object to extend from.
     * @returns {Object}
     */
    function extend(obj1, obj2) {
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
    function buildParams(params) {
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

    // Configure the iframe API.
    extend(settings, options);

    // Register event handler for window.postMessage() messages from nosto.
    window.addEventListener("message", receiveMessage, false);
};