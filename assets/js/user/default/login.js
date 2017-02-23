define('user-default-login', function () {
    var user = {};
    user.init = function () {
        require(['jquery', 'core', 'jquery-form-validator'], function ($, core) {
            $('.profile-image').on('load', function () {
                $(this).fadeIn(1000);
            });
            $('#login-form').submit(function (e) {
                e.preventDefault();
                if ($(this).isValid()) {
                    $.ajax({
                        url: $(this).attr('action') + '.json',
                        data: $(this).serialize(),
                        method: 'POST',
                        dataType: 'json',
                        global: false,
                        success: function (data) {
                            $('#wait-modal').modal('hide');
                            if (data.response && data.response.success) {
                                window.location.href = $('#login-form').data('success-url') ? $('#login-form').data('success-url') : '/';
                            }
                        }
                    });
                }
            });

            $('#username').blur(function () {
                var userField = $(this);
                setTimeout(function () {
                    if (userField.closest('form').isValid()) {
                        $.ajax({
                            url: userField.data('get-image-profile') + userField.val() + '.json',
                            method: 'GET',
                            dataType: 'json',
                            global: false,
                            success: function (data) {
                            },
                            complete: function (data) {
                                user.show.profile_image(data);
                                $('.ajax-spin-username').remove();
                                $(userField).prop("disabled", false);
                            },
                            beforeSend: function () {
                                $(userField).before(core.show.spin('ajax-spin-username ajax-spin-input'));
                                $(userField).prop("disabled", true);
                            },
                            error: function () {
                                user.show.default_profile_image();
                            }
                        });
                    }
                }, 200);
            });
        });
    };

    user.show = {
        profile_image: function (data) {
            if (typeof data === 'object' && data.responseJSON) {
                var data = JSON.parse(data.responseText);
                if (data.response && data.response.success && data.response.data && data.response.data.user && data.response.data.user.image && data.response.data.user.image) {
                    $('.profile-image,.user-name').fadeOut(1000, function () {
                        $(this).attr('src', data.response.data.user.image.url);
                        $('.user-name').html(data.response.data.user.name).fadeIn(1000);
                    });
                } else {
                    user.show.default_profile_image();
                    user.show.default_profile_name();
                }
            } else {
                user.show.default_profile_image();
                user.show.default_profile_name();
            }
        },
        default_profile_image: function () {
            $('.profile-image').fadeOut(1000, function () {
                $(this).attr('src', $(this).data('default-image-profile'));
            });
        },
        default_profile_name: function () {
            $('.user-name').fadeOut(1000, function () {
                $(this).html($(this).data('user-not-found')).fadeIn(1000);
            });
        }
    };
    return user;
});
