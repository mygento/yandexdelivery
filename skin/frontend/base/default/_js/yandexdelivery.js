/* Mygento_Yandexdelivery
 * http://www.mygento.ru
 * Copyright © 2017 NKS LLC
 * License GPLv2 */

jQuery(function () {
    function autocomp(name)
    {
        jQuery('input[name="' + name + '[city]"]').autocomplete({
            source: function (request, response) {
                jQuery.ajax({
                    url: MYGENTO_YANDEXDELIVERY_BASE_URL + "/yandexdelivery/index/json/?callback=?",
                    dataType: "jsonp",
                    data: {
                        search: function () {
                            return jQuery('input[name="' + name + '[city]"]').val();
                        },
                        type: 'locality'
                    },
                    success: function (data) {
                        response(jQuery.map(data.jsonresult, function (item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        }));
                    }
                });
            },
            minLength: 3,
            close: function () {
            },
            select: function (event, ui) {
                jQuery('input[name="' + name + '[city]"]').val(ui.item.value);
            }
        });
        jQuery('input[name="' + name + '[street][]"]').autocomplete({
            source: function (request, response) {
                jQuery.ajax({
                    url: MYGENTO_YANDEXDELIVERY_BASE_URL + "/yandexdelivery/index/json/?callback=?",
                    dataType: "jsonp",
                    data: {
                        city: function () {
                            return jQuery('input[name="' + name + '[city]"]').val();
                        },
                        search: function () {
                            return jQuery('input[name="' + name + '[street][]"]').val();
                        },
                        type: 'street'
                    },
                    success: function (data) {
                        response(jQuery.map(data.jsonresult, function (item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        }));
                    }
                });
            },
            minLength: 3,
            close: function () {
            },
            select: function (event, ui) {
                jQuery('input[name="' + name + '[street]"]').val(ui.item.value);
            }
        });
    }
    autocomp('billing');
    autocomp('shipping');
});
function changeT()
{
    if (jQuery(".js-widget-delivery-type").length) {
        jQuery(".js-widget-delivery-type").click(function (e) {
            e.preventDefault();
            console.log(this);
            var deliveryType = jQuery(this).attr('data-delivery-type');
            console.log('setted delivery type -> ' + deliveryType);
            window.deliveryTypes = [deliveryType];
            ydwidget.cartWidget.changeDeliveryTypes();
        });
    }
}
function ydMethodAdd(name, title, buttonTitle)
{
    var bodyClass = jQuery('body').attr('class');
    if (bodyClass.indexOf('adminhtml' > 0)) {
        setTimeout(function () {
            console.log('probably adminhtml -> ' + title);
            var div = jQuery("dt:contains(" + title + ")");
            var ul = div.next();
            var last = ul.find('li:last');
            last.after('<li><input id="yd_delivery" type="radio" class="radio" name="shipping_method"><label class="js-widget-delivery-type" id="yd_label" data-ydwidget-open data-delivery-type="' + name + '" for="yd_delivery">' + buttonTitle + '</label></li>');
            changeT();
        }, 1000);
    } else {
        var dt = jQuery("#checkout-shipping-method-load dt:contains(" + title + ")");
        if (!dt.length > 0) {
            var div = jQuery("#onestepcheckout-shipping-methods div:contains(" + title + ")");
            var ul = div.next();
            var last = ul.find('li:last');
        } else {
            var dd = dt.next();
            var last = dd.find('ul li:last');
        }
        last.after('<input id="yd_delivery" type="radio" class="radio" name="shipping_method"><label class="js-widget-delivery-type" id="yd_label" data-ydwidget-open data-delivery-type="' + name + '" for="yd_delivery">' + buttonTitle + '</label>');
    }
}