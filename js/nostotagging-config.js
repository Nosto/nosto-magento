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
 * @category    Nosto
 * @package     Nosto_Tagging
 * @copyright   Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

document.observe("dom:loaded", function() {
    // Change event handler for "Do you have an existing Nosto account?".
    if ($("nosto_has_account") !== null) {
        $("nosto_has_account").on("change", "input[name='nosto_has_account_radio']", function(event) {
            var val = parseInt($(event.target).getValue()),
                oldAccount = $("nosto_old_account"),
                newAccount = $("nosto_new_account");
            if (val === 1) {
                oldAccount.show();
                newAccount.hide();
            } else {
                oldAccount.hide();
                newAccount.show();
            }
        });
    }
    // Click event handler for the "Account settings".
    if ($("nosto_account_settings") !== null) {
        $("nosto_account_settings").on("click", function(event) {
            event.preventDefault();
            var iframe = $("nosto_iframe_container"),
                installedView = $("nosto_installed"),
                backButton = $("nosto_back_to_iframe"),
                settingsButton = $("nosto_account_settings");
            if (installedView && iframe) {
                installedView.show();
                backButton.show();
                iframe.hide();
                settingsButton.hide();
            }
        });
    }
    // Click event handler for the "Back" button on the "You have installed Nosto...." page.
    if ($("nosto_back_to_iframe") !== null) {
        $("nosto_back_to_iframe").on("click", function(event) {
            event.preventDefault();
            var iframe = $("nosto_iframe_container"),
                installedView = $("nosto_installed"),
                backButton = $("nosto_back_to_iframe"),
                settingsButton = $("nosto_account_settings");
            if (installedView && iframe) {
                iframe.show();
                settingsButton.show();
                installedView.hide();
                backButton.hide();
            }
        });
    }
    // Init the iframe re-sizer.
    iFrameResize({heightCalculationMethod : 'bodyScroll'});
});
