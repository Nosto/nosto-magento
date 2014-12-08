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
            var iframe = $("nosto_iframe"),
                installedView = $("nosto_installed");
            if (installedView && iframe) {
                installedView.show();
                iframe.hide();
            }
        });
    }
    // Click event handler for the "Back" button on the "You have installed Nosto...." page.
    if ($("nosto_back_to_iframe") !== null) {
        $("nosto_back_to_iframe").on("click", function(event) {
            event.preventDefault();
            var iframe = $("nosto_iframe"),
                installedView = $("nosto_installed");
            if (installedView && iframe) {
                iframe.show();
                installedView.hide();
            }
        });
    }
});
