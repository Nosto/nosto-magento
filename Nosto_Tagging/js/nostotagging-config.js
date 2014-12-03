document.observe("dom:loaded", function() {
    // Change event handler for "Do you have an existing Nosto account?".
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
    // Click event handler for the "Account settings".
//    $("#nostotagging_account_setup").click(function(event) {
//        event.preventDefault();
//        var $iframe = $('#nostotagging_iframe'),
//            $installedView = $('#nostotagging_installed');
//        $installedView.show();
//        $iframe.hide();
//    });
    // Click event handler for the "Back" button on the "You have installed Nosto...." page.
//    $('#nostotagging_back_to_iframe').click(function(event) {
//        event.preventDefault();
//        var $iframe = $('#nostotagging_iframe'),
//            $installedView = $('#nostotagging_installed');
//        $iframe.show();
//        $installedView.hide();
//    });
});
