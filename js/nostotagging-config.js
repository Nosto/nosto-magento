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

document.observe("dom:loaded", function() {

    var TYPE_NEW_ACCOUNT = "newAccount",
        TYPE_CONNECT_ACCOUNT = "connectAccount",
        TYPE_REMOVE_ACCOUNT = "removeAccount";

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
        // Check the origin to prevent cross-site scripting attacks.
        if (event.origin !== "http://10.0.1.150") {
            return;
        }

        var data = event.data ? JSON.parse(event.data) : null;
        if (typeof data === "object" && data.type) {
            switch (data.type) {
                case TYPE_NEW_ACCOUNT:
                    xhr(nosto.url.createAccount, {
                        data: {email: data.email},
                        done: function (e) {
                            var response = JSON.parse(e.target.response);
                            console.debug(response);
                            if (response.success) {
                                window.location.href = nosto.url.index;
                            }

                            // todo: how do we handle success/fail?
                            // success:
                            // 1. if response contains success flag and account name
                            // 2. then redirect to nosto/index so the iframe will be called with correct params
                            // failure:
                            // 1. send response as postMessage to nosto to we can show error message

                            // event.source.postMessage("foo", event.origin);
                        }
                    });
                    break;

                case TYPE_CONNECT_ACCOUNT:
                    // todo
                    break;

                case TYPE_REMOVE_ACCOUNT:
                    // todo
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
     *      "done": function (e) { // handle success request },
     *      "fail": function (e) { // handle failure request }
     * });
     *
     * @param {String} url the url to call.
     * @param {Object} params optional params.
     */
    function xhr(url, params) {
        var key,
            payload = "",
            elStoreSwitcher,
            storeId,
            settings = {
                method: "POST",
                async: true,
                data: {}
            };

        for (key in params) {
            if (params.hasOwnProperty(key)) {
                settings[key] = params[key];
            }
        }

        payload += "form_key="+window.FORM_KEY;
        elStoreSwitcher = document.getElementById("store_switcher");
        if (elStoreSwitcher) {
            storeId = elStoreSwitcher.options[elStoreSwitcher.selectedIndex].value;
            if (storeId) {
                payload += "&store="+parseInt(storeId);
            }
        }
        for (key in settings.data) {
            if (settings.data.hasOwnProperty(key)) {
                payload += "&"+key+"="+settings.data[key];
            }
        }

        var oReq = new XMLHttpRequest();
        if (typeof settings.done === "function") {
            oReq.addEventListener("load", params.done, false);
        }
        if (typeof settings.fail === "function") {
            oReq.addEventListener("error", params.fail, false);
        }
        oReq.open(settings.method, url+"?isAjax=true", settings.async);
        oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        oReq.send(payload);
    }

    // Register event handler for window.postMessage() messages from nosto.
    window.addEventListener("message", receiveMessage, false);

    // Init the iframe re-sizer.
    iFrameResize({heightCalculationMethod : "bodyScroll"});
});
