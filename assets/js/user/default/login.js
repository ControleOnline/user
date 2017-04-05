define('user-default-login', ['jquery', 'core', 'jquery-form-validator','bootstrap'], function ($, core) {
    var user = {};
    user.init = function () {
        $('.profile-image').on('load', function () {
            $(this).fadeIn(1000);
        });
        $('#username').blur(function () {
            var e = $(this);
            setTimeout(function () {
                if ($(e).closest('form').isValid()) {
                    user.checkNewUser(e);
                }
            }, 500);
        });
    };    
    user.checkNewUser = function (userField) {
        $.ajax({
            url: userField.data('get-image-profile') + userField.val() + '.json',
            method: 'GET',
            dataType: 'json',
            global: false,
            success: function (data) {
            },
            complete: function (data) {
                user.show.profile_image(data, userField);
                $('.ajax-spin-username').remove();
                $(userField).prop("readonly", false);
            },
            beforeSend: function () {
                $(userField).before('<i class="ajax-spin-username ajax-spin-input ajax-spin fa fa-spinner fa-spin"></i>');
                $(userField).prop("readonly", true);
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
                    $(userField).attr('data-user-exists', true);
                    $('.profile-image,.user-name').fadeOut(1000, function () {
                        $(this).attr('src', data.response.data.user.image.url);
                        $('.user-name').html(data.response.data.user.name).fadeIn(1000);
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
            $('.profile-image').fadeOut(1000, function () {
                $(this).attr('src', $(this).data('default-image-profile'));
            });
        },
        default_profile_name: function (userField) {
            $(userField).attr('data-user-exists', false);
            $('.user-name').fadeOut(1000, function () {
                $(this).html('');
            });
        }
    };
    return user;
});
