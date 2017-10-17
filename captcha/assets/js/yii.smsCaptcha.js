/**
 * Yii Sms Captcha widget.
 *
 * This is the JavaScript widget used by the yii\captcha\Captcha widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {
    $.fn.yiiSmsCaptcha = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.yiiSmsCaptcha');
            return false;
        }
    };

    var defaults = {
        refreshUrl: undefined,
        hashKey: undefined,
        mobileField: undefined,
        buttonTime: undefined,
        buttonGet: undefined
    };

    var countdown;

    var mobileReg = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;

    var addCookie = function (name, value, expiresHours) {
        var cookieString = name + "=" + value;
        if (expiresHours > 0) {
            var date = new Date();
            date.setTime(date.getTime() + expiresHours * 1000);
            cookieString = cookieString + ";expires=" + date.toUTCString();
        }
        document.cookie = cookieString;
    };

    var editCookie = function (name, value, expiresHours) {
        var cookieString = name + "=" + value;
        if (expiresHours > 0) {
            var date = new Date();
            date.setTime(date.getTime() + expiresHours * 1000); //单位是毫秒
            cookieString = cookieString + ";expires=" + date.toGMTString();
        }
        document.cookie = cookieString;
    };

    var getCookie = function (name) {
        var strCookie = document.cookie;
        var arrCookie = strCookie.split("; ");
        for (var i = 0; i < arrCookie.length; i++) {
            var arr = arrCookie[i].split("=");
            if (arr[0] == name) {
                return arr[1];
                break;
            }
        }
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                //用当前设置合并默认设置
                var settings = $.extend({}, defaults, options || {});
                $e.data('yiiSmsCaptcha', {
                    settings: settings
                });

                //绑定事件
                $e.on('click.yiiSmsCaptcha', function () {
                    methods.refresh.apply($e);
                    return false;
                });
                //检查倒计时完了没
                var waitTime = getCookie(settings.hashKey) ? getCookie(settings.hashKey) : 0;
                if (waitTime > 0) {
                    methods.remainTime(settings.hashKey, $e);
                }
            });
        },


        refresh: function () {
            var $e = this,
                settings = this.data('yiiSmsCaptcha').settings;

            $.ajax({
                url: $e.data('yiiSmsCaptcha').settings.refreshUrl,
                data: {"mobile": $("#" + settings.mobileField).val()},
                dataType: 'json',
                type: 'POST',
                async: false,
                cache: false,
                success: function (data) {
                    //刷新成功开始倒计时
                    addCookie(settings.hashKey, data.waitTime, data.waitTime);
                    methods.remainTime(settings.hashKey, $e);
                    $('body').data(settings.hashKey, data.hash);
                    console.info(data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error("Http Error:" + XMLHttpRequest.statusText + ", status:" + XMLHttpRequest.status + ", responseText:" + XMLHttpRequest.responseText);
                }
            });
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.yiiSmsCaptcha');
                $(this).removeData('yiiSmsCaptcha');
            });
        },

        data: function () {
            return this.data('yiiSmsCaptcha');
        },

        remainTime: function (hashKey, e) {
            countdown = getCookie(hashKey) ? getCookie(hashKey) : 0;
            if (countdown == 0) {
                e.removeAttr("disabled");
                e.html(e.data('yiiSmsCaptcha').settings.buttonGet);
                return;
            } else {
                e.attr("disabled", true);
                e.html(countdown + e.data('yiiSmsCaptcha').settings.buttonTime);
                countdown--;
                editCookie(hashKey, countdown, countdown + 1);
            }
            setTimeout(function () {
                methods.remainTime(hashKey, e);
            }, 1000);
        }
    };
})(window.jQuery);

yii.validation.smsCaptcha = function (value, messages, options) {
    if (options.skipOnEmpty && pub.isEmpty(value)) {
        return;
    }

    // CAPTCHA may be updated via AJAX and the updated hash is stored in body data
    var hash = jQuery('body').data(options.hashKey);
    if (hash == null) {
        hash = options.hash;
    }
    for (var h = 0, i = value.length - 1; i >= 0; --i) {
        h += parseInt(value.charAt(i), 10);
    }
    if (h != hash) {
        yii.validation.addMessage(messages, options.message, value);
    }
};

