define('user-default-login', ['jquery', 'core', 'bootstrap'], function (jQuery, core) {
    var user = {};
    user.init = function () {
        jQuery('.profile-image').on('load', function () {
            jQuery(this).fadeIn(1000);
        });
        jQuery('.profile-username').blur(function () {
            var e = jQuery(this);
            setTimeout(function () {
                user.checkNewUser(e);
            }, 500);
        });
    };
    user.checkNewUser = function (userField) {
        jQuery.ajax({
            url: core.domain + userField.data('get-image-profile') + userField.val() + '.json',
            method: 'GET',
            dataType: 'json',
            global: false,
            success: function (data) {
            },
            complete: function (data) {
                user.show.profile_image(data, userField);
                jQuery('.ajax-spin-username').remove();
                jQuery(userField).prop("readonly", false);
            },
            beforeSend: function () {
                jQuery(userField).before('<i class="ajax-spin-username ajax-spin-input ajax-spin fa fa-spinner fa-spin"></i>');
                jQuery(userField).prop("readonly", true);
            },
            error: function () {
                user.show.default_profile_image();
            }
        });
    };
    user.show = {
        profile_image: function (data, userField) {
            if (typeof data === 'object' && data.responseJSON) {
                var data = JSON.parse(data.responseText);
                if (data.response && data.response.success && data.response.data && data.response.data.user && data.response.data.user.image && data.response.data.user.image) {
                    jQuery(userField).attr('data-user-exists', true);
                    jQuery('.profile-image,.user-name').fadeOut(1000, function () {
                        jQuery(this).attr('src', data.response.data.user.image.url);
                        jQuery('.user-name').html(data.response.data.user.name).fadeIn(1000);
                    });
                } else {
                    user.show.default_profile_image(userField);
                }
            } else {
                user.show.default_profile_image(userField);
            }
        },
        default_profile_image: function (userField) {
            user.show.default_profile_name(userField);
            jQuery('.profile-image').fadeOut(1000, function () {
                jQuery(this).attr('src', jQuery(this).data('default-image-profile'));
            });
        },
        default_profile_name: function (userField) {
            jQuery(userField).attr('data-user-exists', false);
            jQuery('.user-name').fadeOut(1000, function () {
                jQuery(this).html('');
            });
        }
    };
    return user;
});
