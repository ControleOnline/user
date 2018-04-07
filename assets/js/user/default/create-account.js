define('user-default-create-account', ['jquery', 'core', 'bootstrap'], function (jQuery, core) {
    var user = {};
    user.init = function () {
        jQuery('#username').blur(function () {
            var userField = jQuery(this);
            if (userField.val()) {
                jQuery.ajax({
                    url: core.domain + userField.data('user-in-use') + userField.val() + '.json',
                    method: 'GET',
                    dataType: 'json',
                    global: false,
                    success: function (data) {
                    },
                    complete: function (data) {
                        if (typeof data === 'object' && data.responseJSON) {
                            var data = JSON.parse(data.responseText);
                            if (data.response && data.response.success) {
                                console.log('Pode usar');
                            } else {
                                console.log('User já existe');
                            }
                        }
                        jQuery('.ajax-spin-username').remove();
                        jQuery(userField).prop("readonly", false);


                    },
                    beforeSend: function () {
                        jQuery(userField).before('<i class="ajax-spin-username ajax-spin-input ajax-spin fa fa-spinner fa-spin"></i>');
                        jQuery(userField).prop("readonly", true);
                    },
                    error: function () {

                    }
                });
            }
        });
    };
    return user;
});
