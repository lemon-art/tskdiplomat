
function ps_round(value, precision) {
    if (typeof(roundMode) == 'undefined') roundMode = 2;
    if (typeof(precision) == 'undefined') precision = 2;
    method = roundMode;
    if (method == 0) return ceilf(value, precision);
    else if (method == 1) return floorf(value, precision);
    precisionFactor = precision == 0 ? 1 : Math.pow(10, precision);
    return Math.round(value * precisionFactor) / precisionFactor;
}
function ceilf(value, precision) {
    if (typeof(precision) == 'undefined') precision = 0;
    precisionFactor = precision == 0 ? 1 : Math.pow(10, precision);
    tmp = value * precisionFactor;
    tmp2 = tmp.toString();
    if (tmp2[tmp2.length - 1] == 0) return value;
    return Math.ceil(value * precisionFactor) / precisionFactor;
}
function floorf(value, precision) {
    if (typeof(precision) == 'undefined') precision = 0;
    precisionFactor = precision == 0 ? 1 : Math.pow(10, precision);
    tmp = value * precisionFactor;
    tmp2 = tmp.toString();
    if (tmp2[tmp2.length - 1] == 0) return value;
    return Math.floor(value * precisionFactor) / precisionFactor;
}
function formatedNumberToFloat(price, currencyFormat, currencySign) {
    price = price.replace(currencySign, '');
    if (currencyFormat == 1) return parseFloat(price.replace(',', '').replace(' ', ''));
    else if (currencyFormat == 2) return parseFloat(price.replace(' ', '').replace(',', '.'));
    else if (currencyFormat == 3) return parseFloat(price.replace('.', '').replace(' ', '').replace(',', '.'));
    else if (currencyFormat == 4) return parseFloat(price.replace(',', '').replace(' ', ''));
    return price;
}
function formatCurrency(price, currencyFormat, currencySign, currencyBlank) {
    blank = '';
    price = parseFloat(price.toFixed(6));
    price = ps_round(price, priceDisplayPrecision);
    if (currencyBlank > 0) blank = ' ';
    if (currencyFormat == 1) return currencySign + blank + formatNumber(price, priceDisplayPrecision, ',', '.');
    if (currencyFormat == 2) return (formatNumber(price, priceDisplayPrecision, ' ', ',') + blank + currencySign);
    if (currencyFormat == 3) return (currencySign + blank + formatNumber(price, priceDisplayPrecision, '.', ','));
    if (currencyFormat == 4) return (formatNumber(price, priceDisplayPrecision, ',', '.') + blank + currencySign);
    if (currencyFormat == 5) return (formatNumber(price, priceDisplayPrecision, ' ', '.') + blank + currencySign);
    return price;
}
function formatNumber(value, numberOfDecimal, thousenSeparator, virgule) {
    value = value.toFixed(numberOfDecimal);
    var val_string = value + '';
    var tmp = val_string.split('.');
    var abs_val_string = (tmp.length == 2) ? tmp[0] : val_string;
    var deci_string = ('0.' + (tmp.length == 2 ? tmp[1] : 0)).substr(2);
    var nb = abs_val_string.length;
    for (var i = 1; i < 4; i++) if (value >= Math.pow(10, (3 * i))) abs_val_string = abs_val_string.substring(0, nb - (3 * i)) + thousenSeparator + abs_val_string.substring(nb - (3 * i));
    if (parseInt(numberOfDecimal) == 0) return abs_val_string;
    return abs_val_string + virgule + (deci_string > 0 ? deci_string: '00');
}
function updateTextWithEffect(jQueryElement, text, velocity, effect1, effect2, newClass) {
    if (jQueryElement.text() != text) if (effect1 == 'fade') jQueryElement.fadeOut(velocity,
    function() {
        $(this).addClass(newClass);
        if (effect2 == 'fade') $(this).text(text).fadeIn(velocity);
        else if (effect2 == 'slide') $(this).text(text).slideDown(velocity);
        else if (effect2 == 'show') $(this).text(text).show(velocity,
        function() {});
    });
    else if (effect1 == 'slide') jQueryElement.slideUp(velocity,
    function() {
        $(this).addClass(newClass);
        if (effect2 == 'fade') $(this).text(text).fadeIn(velocity);
        else if (effect2 == 'slide') $(this).text(text).slideDown(velocity);
        else if (effect2 == 'show') $(this).text(text).show(velocity);
    });
    else if (effect1 == 'hide') jQueryElement.hide(velocity,
    function() {
        $(this).addClass(newClass);
        if (effect2 == 'fade') $(this).text(text).fadeIn(velocity);
        else if (effect2 == 'slide') $(this).text(text).slideDown(velocity);
        else if (effect2 == 'show') $(this).text(text).show(velocity);
    });
}
function dbg(value) {
    var active = false;
    var firefox = true;
    if (active) if (firefox) console.log(value);
    else alert(value);
}
function print_r(arr, level) {
    var dumped_text = "";
    if (!level) level = 0;
    var level_padding = "";
    for (var j = 0; j < level + 1; j++) level_padding += "    ";
    if (typeof(arr) == 'object') {
        for (var item in arr) {
            var value = arr[item];
            if (typeof(value) == 'object') {
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value, level + 1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else {
        dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
    }
    return dumped_text;
}
function in_array(value, array) {
    for (var i in array) if (array[i] == value) return true;
    return false;
}
function resizeAddressesBox(nameBox) {
    maxHeight = 0;
    if (typeof(nameBox) == 'undefined') nameBox = '.address';
    $(nameBox).each(function() {
        $(this).css('height', 'auto');
        currentHeight = $(this).height();
        if (maxHeight < currentHeight) maxHeight = currentHeight;
    });
    $(nameBox).height(maxHeight);
}
$(document).ready(function() {
    $.fn.checkboxChange = function(fnChecked, fnUnchecked) {
        if ($(this).prop('checked') && fnChecked) fnChecked.call(this);
        else if (fnUnchecked) fnUnchecked.call(this);
        if (!$(this).attr('eventCheckboxChange')) {
            $(this).live('change',
            function() {
                $(this).checkboxChange(fnChecked, fnUnchecked)
            });
            $(this).attr('eventCheckboxChange', true);
        }
    }
});
$(function() {
    $('a.js-new-window').click(function() {
        window.open(this.href);
        return false;
    });
});


$(document).ready(function() {
    if (window.ajaxCart !== undefined) {
        $('.cart_quantity_up').unbind('click').live('click',
        function() {
            upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));
            return false;
        });
        $('.cart_quantity_down').unbind('click').live('click',
        function() {
            downQuantity($(this).attr('id').replace('cart_quantity_down_', ''));
            return false;
        });
        $('.cart_quantity_delete').unbind('click').live('click',
        function() {
            deleteProductFromSummary($(this).attr('id'));
            return false;
        });
        $('.cart_quantity_input').typeWatch({
            highlight: true,
            wait: 600,
            captureLength: 0,
            callback: function(val) {
                updateQty(val, true, this.el)
            }
        });
    }
    $('.cart_address_delivery').live('change',
    function() {
        changeAddressDelivery($(this));
    });
    cleanSelectAddressDelivery();
});
function cleanSelectAddressDelivery() {
    if (window.ajaxCart !== undefined) {
        $.each($('.cart_address_delivery'),
        function(it, item) {
            var options = $(item).find('option');
            var address_count = 0;
            var ids = $(item).attr('id').split('_');
            var id_product = ids[3];
            var id_product_attribute = ids[4];
            var id_address_delivery = ids[5];
            $.each(options,
            function(i) {
                if ($(options[i]).val() > 0 && ($('#product_' + id_product + '_' + id_product_attribute + '_0_' + $(options[i]).val()).length == 0 || id_address_delivery == $(options[i]).val())) address_count++;
            });
            if (address_count < 2) $($(item).find('option[value=-2]')).remove();
            else if ($($(item).find('option[value=-2]')).length == 0) $(item).append($('<option value="-2">' + ShipToAnOtherAddress + '</option>'));
        });
    }
}
function changeAddressDelivery(obj) {
    var ids = obj.attr('id').split('_');
    var id_product = ids[3];
    var id_product_attribute = ids[4];
    var old_id_address_delivery = ids[5];
    var new_id_address_delivery = obj.val();
    if (new_id_address_delivery == old_id_address_delivery) return;
    if (new_id_address_delivery > 0) {
        $.ajax({
            type: 'GET',
            url: baseUri,
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=cart&ajax=true&changeAddressDelivery&summary&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&old_id_address_delivery=' + old_id_address_delivery + '&new_id_address_delivery=' + new_id_address_delivery + '&token=' + static_token + '&allow_refresh=1',
            success: function(jsonData) {
                if (typeof(jsonData.hasErrors) != 'undefined' && jsonData.hasErrors) {
                    alert(jsonData.error);
                    $('#select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + old_id_address_delivery).val(old_id_address_delivery);
                } else {
                    if ($('#product_' + id_product + '_' + id_product_attribute + '_0_' + new_id_address_delivery).length) {
                        updateCartSummary(jsonData.summary);
                        updateCustomizedDatas(jsonData.customizedDatas);
                        updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
                        updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
                        if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
                        $('#product_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery).remove();
                        $('.product_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery).remove();
                    }
                    if (window.ajaxCart !== undefined) ajaxCart.refresh();
                    updateAddressId(id_product, id_product_attribute, old_id_address_delivery, new_id_address_delivery);
                    cleanSelectAddressDelivery();
                }
            }
        });
    } else if (new_id_address_delivery == -1) window.location = $($('.address_add a')[0]).attr('href');
    else if (new_id_address_delivery == -2) {
        if (old_id_address_delivery == 0) {
            alert(txtSelectAnAddressFirst);
            return false;
        }
        var id_address_delivery = 0;
        var options = $('#select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + old_id_address_delivery + ' option');
        $.each(options,
        function(i) {
            if ($(options[i]).val() > 0 && $(options[i]).val() != old_id_address_delivery && $('#product_' + id_product + '_' + id_product_attribute + '_0_' + $(options[i]).val()).length == 0) {
                id_address_delivery = $(options[i]).val();
                return false;
            }
        });
        $.ajax({
            type: 'GET',
            url: baseUri,
            async: true,
            cache: false,
            dataType: 'json',
            context: obj,
            data: 'controller=cart' + '&ajax=true&duplicate&summary' + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&id_address_delivery=' + old_id_address_delivery + '&new_id_address_delivery=' + id_address_delivery + '&token=' + static_token + '&allow_refresh=1',
            success: function(jsonData) {
                if (jsonData.error) {
                    alert(jsonData.reason);
                    return;
                }
                var line = $('#product_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery);
                var new_line = line.clone();
                updateAddressId(id_product, id_product_attribute, old_id_address_delivery, id_address_delivery, new_line);
                line.after(new_line);
                new_line.find('input[name=quantity_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery + '_hidden]').val(1);
                new_line.find('.cart_quantity_input').val(1);
                $('#select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + old_id_address_delivery).val(old_id_address_delivery);
                $('#select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + id_address_delivery).val(id_address_delivery);
                cleanSelectAddressDelivery();
                updateCartSummary(jsonData.summary);
                if (window.ajaxCart !== undefined) ajaxCart.refresh();
            }
        });
    }
    return true;
}
function updateAddressId(id_product, id_product_attribute, old_id_address_delivery, id_address_delivery, line) {
    if (typeof(line) == 'undefined') var line = $('#product_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery);
    line.attr('id', 'product_' + id_product + '_' + id_product_attribute + '_0_' + id_address_delivery);
    line.find('.cart_quantity_input').attr('name', 'quantity_' + id_product + '_' + id_product_attribute + '_0_' + id_address_delivery);
    line.find('input[name=quantity_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery + '_hidden]').attr('name', 'quantity_' + id_product + '_' + id_product_attribute + '_0_' + id_address_delivery + '_hidden');
    line.find('#cart_quantity_down_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery).attr('id', 'cart_quantity_down_' + id_product + '_' + id_product_attribute + '_0_' + id_address_delivery);
    line.find('#cart_quantity_up_' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery).attr('id', 'cart_quantity_up_' + id_product + '_' + id_product_attribute + '_0_' + id_address_delivery);
    line.find('#' + id_product + '_' + id_product_attribute + '_0_' + old_id_address_delivery).attr('id', id_product + '_' + id_product_attribute + '_0_' + id_address_delivery);
    line.find('#select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + old_id_address_delivery).attr('id', 'select_address_delivery_' + id_product + '_' + id_product_attribute + '_' + id_address_delivery);
}
function updateQty(val, cart, el) {
    if (typeof(cart) == 'undefined' || cart) var prefix = '#order-detail-content ';
    else var prefix = '#fancybox-content ';
    var id = $(el).attr('name');
    var exp = new RegExp("^[0-9]+$");
    if (exp.test(val) == true) {
        var hidden = $(prefix + 'input[name=' + id + '_hidden]').val();
        var input = $(prefix + 'input[name=' + id + ']').val();
        var QtyToUp = parseInt(input) - parseInt(hidden);
        if (parseInt(QtyToUp) > 0) upQuantity(id.replace('quantity_', ''), QtyToUp);
        else if (parseInt(QtyToUp) < 0) downQuantity(id.replace('quantity_', ''), QtyToUp);
    } else $(prefix + 'input[name=' + id + ']').val($(prefix + 'input[name=' + id + '_hidden]').val());
    if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
}
function deleteProductFromSummary(id) {
    var customizationId = 0;
    var productId = 0;
    var productAttributeId = 0;
    var id_address_delivery = 0;
    var ids = 0;
    ids = id.split('_');
    productId = parseInt(ids[0]);
    if (typeof(ids[1]) != 'undefined') productAttributeId = parseInt(ids[1]);
    if (typeof(ids[2]) != 'undefined') customizationId = parseInt(ids[2]);
    if (typeof(ids[3]) != 'undefined') id_address_delivery = parseInt(ids[3]);
    $.ajax({
        type: 'GET',
        url: baseUri,
        async: true,
        cache: false,
        dataType: 'json',
        data: 'controller=cart' + '&ajax=true&delete&summary' + '&id_product=' + productId + '&ipa=' + productAttributeId + '&id_address_delivery=' + id_address_delivery + ((customizationId != 0) ? '&id_customization=' + customizationId: '') + '&token=' + static_token + '&allow_refresh=1',
        success: function(jsonData) {
            if (jsonData.hasError) {
                var errors = '';
                for (error in jsonData.errors) if (error != 'indexOf') errors += jsonData.errors[error] + "\n";
            } else {
                if (jsonData.refresh) location.reload();
                if (parseInt(jsonData.summary.products.length) == 0) {
                    if (typeof(orderProcess) == 'undefined' || orderProcess != 'order-opc') document.location.href = document.location.href;
                    else {
                        $('#center_column').children().each(function() {
                            if ($(this).attr('id') != 'emptyCartWarning' && $(this).attr('class') != 'breadcrumb' && $(this).attr('id') != 'cart_title') {
                                $(this).fadeOut('slow',
                                function() {
                                    $(this).remove();
                                });
                            }
                        });
                        $('#summary_products_label').remove();
                        $('#emptyCartWarning').fadeIn('slow');
                    }
                } else {
                    $('#product_' + id).fadeOut('slow',
                    function() {
                        $(this).remove();
                        cleanSelectAddressDelivery();
                        if (!customizationId) refreshOddRow();
                    });
                    var exist = false;
                    for (i = 0; i < jsonData.summary.products.length; i++) if (jsonData.summary.products[i].id_product == productId && jsonData.summary.products[i].id_product_attribute == productAttributeId && jsonData.summary.products[i].id_address_delivery == id_address_delivery) exist = true;
                    if (!exist && customizationId) $('#product_' + productId + '_' + productAttributeId + '_0_' + id_address_delivery).fadeOut('slow',
                    function() {
                        $(this).remove();
                        refreshOddRow();
                    });
                }
                updateCartSummary(jsonData.summary);
                updateCustomizedDatas(jsonData.customizedDatas);
                updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
                updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
                if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            if (textStatus != 'abort') alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
        }
    });
}
function refreshOddRow() {
    var odd_class = 'odd';
    var even_class = 'even';
    $.each($('.cart_item'),
    function(i, it) {
        if (i == 0) {
            if ($(this).hasClass('even')) {
                odd_class = 'even';
                even_class = 'odd';
            }
            $(this).addClass('first_item');
        }
        if (i % 2) $(this).removeClass(odd_class).addClass(even_class);
        else $(this).removeClass(even_class).addClass(odd_class);
    });
    $('.cart_item:last-child, .customization:last-child').addClass('last_item');
}
function upQuantity(id, qty) {
    if (typeof(qty) == 'undefined' || !qty) qty = 1;
    var customizationId = 0;
    var productId = 0;
    var productAttributeId = 0;
    var id_address_delivery = 0;
    var ids = 0;
    ids = id.split('_');
    productId = parseInt(ids[0]);
    if (typeof(ids[1]) != 'undefined') productAttributeId = parseInt(ids[1]);
    if (typeof(ids[2]) != 'undefined') customizationId = parseInt(ids[2]);
    if (typeof(ids[3]) != 'undefined') id_address_delivery = parseInt(ids[3]);
    $.ajax({
        type: 'GET',
        url: baseUri,
        async: true,
        cache: false,
        dataType: 'json',
        data: 'action=inc&ajax=Y&id=' + productId + '&token=' + static_token,
        success: function(jsonData) {
            if (jsonData.hasError) {
                var errors = '';
                for (error in jsonData.errors) if (error != 'indexOf') errors += jsonData.errors[error] + "\n";
                alert(errors);
                $('input[name=quantity_' + id + ']').val($('input[name=quantity_' + id + '_hidden]').val());
            } else {
                if (jsonData.refresh) location.reload();
                updateCartSummary(jsonData.summary);
                updateCustomizedDatas(jsonData.customizedDatas);
                updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
                updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
                if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            if (textStatus != 'abort') alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
        }
    });
}
function downQuantity(id, qty) {
    var val = $('input[name=quantity_' + id + ']').val();
    var newVal = val;
    if (typeof(qty) == 'undefined' || !qty) {
        qty = 1;
        newVal = val - 1;
    } else if (qty < 0) qty = -qty;
    var customizationId = 0;
    var productId = 0;
    var productAttributeId = 0;
    var id_address_delivery = 0;
    var ids = 0;
    ids = id.split('_');
    productId = parseInt(ids[0]);
    if (typeof(ids[1]) != 'undefined') productAttributeId = parseInt(ids[1]);
    if (typeof(ids[2]) != 'undefined') customizationId = parseInt(ids[2]);
    if (typeof(ids[3]) != 'undefined') id_address_delivery = parseInt(ids[3]);
    if (newVal > 0 || $('#product_' + id + '_gift').length) {
        $.ajax({
            type: 'GET',
            url: baseUri,
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=cart' + '&ajax=true' + '&add' + '&getproductprice' + '&summary' + '&id_product=' + productId + '&ipa=' + productAttributeId + '&id_address_delivery=' + id_address_delivery + '&op=down' + ((customizationId != 0) ? '&id_customization=' + customizationId: '') + '&qty=' + qty + '&token=' + static_token + '&allow_refresh=1',
            success: function(jsonData) {
                if (jsonData.hasError) {
                    var errors = '';
                    for (error in jsonData.errors) if (error != 'indexOf') errors += jsonData.errors[error] + "\n";
                    alert(errors);
                    $('input[name=quantity_' + id + ']').val($('input[name=quantity_' + id + '_hidden]').val());
                } else {
                    if (jsonData.refresh) location.reload();
                    updateCartSummary(jsonData.summary);
                    updateCustomizedDatas(jsonData.customizedDatas);
                    updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
                    updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
                    if (newVal == 0) $('#product_' + id).hide();
                    if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus != 'abort') alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
            }
        });
    } else {
        deleteProductFromSummary(id);
    }
}
function updateCartSummary(json) {
    var i;
    var nbrProducts = 0;
    if (typeof json == 'undefined') return;
    $('.cart_quantity_input').val(0);
    product_list = {};
    for (i = 0; i < json.products.length; i++) product_list[json.products[i].id_product + '_' + json.products[i].id_product_attribute + '_' + json.products[i].id_address_delivery] = json.products[i];
    if (!$('.multishipping-cart:visible').length) {
        for (i = 0; i < json.gift_products.length; i++) {
            if (typeof(product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery]) != 'undefined') product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery].quantity -= json.gift_products[i].cart_quantity;
        }
    } else {
        for (i = 0; i < json.gift_products.length; i++) {
            if (typeof(product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery]) == 'undefined') product_list[json.gift_products[i].id_product + '_' + json.gift_products[i].id_product_attribute + '_' + json.gift_products[i].id_address_delivery] = json.gift_products[i];
        }
    }
    for (i in product_list) {
        var reduction = product_list[i].reduction_applies;
        var initial_price_text = '';
        initial_price = '';
        if (typeof(product_list[i].price_without_quantity_discount) != 'undefined') initial_price = formatCurrency(product_list[i].price_without_quantity_discount, currencyFormat, currencySign, currencyBlank);
        var current_price = '';
        if (priceDisplayMethod != 0) current_price = formatCurrency(product_list[i].price, currencyFormat, currencySign, currencyBlank);
        else current_price = formatCurrency(product_list[i].price_wt, currencyFormat, currencySign, currencyBlank);
        if (reduction && typeof(initial_price) != 'undefined') {
            if (initial_price != '' && initial_price > current_price) initial_price_text = '<span style="text-decoration:line-through;">' + initial_price + '</span><br />';
        }
        key_for_blockcart = product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery;
        $('#cart_block_product_' + key_for_blockcart + ' span.quantity').html(product_list[i].quantity);
        if (priceDisplayMethod != 0) {
            $('#cart_block_product_' + key_for_blockcart + ' span.price').html(formatCurrency(product_list[i].total, currencyFormat, currencySign, currencyBlank));
            $('#product_price_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery).html(initial_price_text + current_price);
            $('#total_product_price_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery).html(formatCurrency(product_list[i].total, currencyFormat, currencySign, currencyBlank));
        } else {
            $('#cart_block_product_' + key_for_blockcart + ' span.price').html(formatCurrency(product_list[i].total_wt, currencyFormat, currencySign, currencyBlank));
            $('#product_price_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery).html(initial_price_text + current_price);
            $('#total_product_price_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery).html(formatCurrency(product_list[i].total_wt, currencyFormat, currencySign, currencyBlank));
        }
        nbrProducts += parseInt(product_list[i].quantity);
        $('input[name=quantity_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_0_' + product_list[i].id_address_delivery + ']').val(product_list[i].quantity_without_customization);
        $('input[name=quantity_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_0_' + product_list[i].id_address_delivery + '_hidden]').val(product_list[i].quantity_without_customization);
        if (typeof(product_list[i].customizationQuantityTotal) != 'undefined') {
            $('#cart_quantity_custom_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery).html(product_list[i].customizationQuantityTotal);
            $('input[name=quantity_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_customization + '_' + product_list[i].id_address_delivery + ']').val(product_list[i].customizationQuantityTotal);
        }
        if (parseInt(product_list[i].minimal_quantity) == parseInt(product_list[i].quantity) && product_list[i].minimal_quantity != 1) $('#cart_quantity_down_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + Number(product_list[i].id_customization) + '_' + product_list[i].id_address_delivery).fadeTo('slow', 0.3);
        else $('#cart_quantity_down_' + product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + Number(product_list[i].id_customization) + '_' + product_list[i].id_address_delivery).fadeTo('slow', 1);
    }
    if (json.discounts.length == 0) {
        $('.cart_discount').each(function() {
            $(this).remove()
        });
        $('.cart_total_voucher').remove();
    } else {
        if ($('.cart_discount').length == 0) location.reload();
        if (priceDisplayMethod != 0) $('#total_discount').html(formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank));
        else $('#total_discount').html(formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));
        $('.cart_discount').each(function() {
            var idElmt = $(this).attr('id').replace('cart_discount_', '');
            var toDelete = true;
            for (i = 0; i < json.discounts.length; i++) {
                if (json.discounts[i].id_discount == idElmt) {
                    if (json.discounts[i].value_real != '!') {
                        if (priceDisplayMethod != 0) $('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_tax_exc * -1, currencyFormat, currencySign, currencyBlank));
                        else $('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));
                    }
                    toDelete = false;
                }
            }
            if (toDelete) $('#cart_discount_' + idElmt + ', #cart_total_voucher').fadeTo('fast', 0,
            function() {
                $(this).remove();
            });
        });
    }
    if (priceDisplayMethod != 0) {
        $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
        $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping_tax_exc, currencyFormat, currencySign, currencyBlank));
        $('#cart_block_total').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
    } else {
        $('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
        $('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
        $('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
    }
    $('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
    $('.ajax_cart_quantity').html(nbrProducts);
    $('#summary_products_quantity').html(nbrProducts + ' ' + (nbrProducts > 1 ? txtProducts: txtProduct));
    if (priceDisplayMethod != 0) $('#total_product').html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));
    else $('#total_product').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
    $('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
    $('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
    $('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
    if (json.total_shipping > 0) {
        if (priceDisplayMethod != 0) {
            $('#total_shipping').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
        } else {
            $('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
        }
    } else {
        $('#total_shipping').html(freeShippingTranslation);
    }
    if (json.free_ship > 0 && !json.is_virtual_cart) {
        $('.cart_free_shipping').fadeIn();
        $('#free_shipping').html(formatCurrency(json.free_ship, currencyFormat, currencySign, currencyBlank));
    } else $('.cart_free_shipping').hide();
    if (json.total_wrapping > 0) {
        $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
        $('#total_wrapping').parent().show();
    } else {
        $('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
        $('#total_wrapping').parent().hide();
    }
    if (window.ajaxCart !== undefined) ajaxCart.refresh();
}
function updateCustomizedDatas(json) {
    for (i in json) for (j in json[i]) for (k in json[i][j]) for (l in json[i][j][k]) {
        var quantity = json[i][j][k][l]['quantity'];
        $('input[name=quantity_' + i + '_' + j + '_' + l + '_' + k + '_hidden]').val(quantity);
        $('input[name=quantity_' + i + '_' + j + '_' + l + '_' + k + ']').val(quantity);
    }
}
function updateHookShoppingCart(html) {
    $('#HOOK_SHOPPING_CART').html(html);
}
function updateHookShoppingCartExtra(html) {
    $('#HOOK_SHOPPING_CART_EXTRA').html(html);
}
function refreshDeliveryOptions() {
    $.each($('.delivery_option_radio'),
    function() {
        if ($(this).prop('checked')) {
            if ($(this).parent().find('.delivery_option_carrier.not-displayable').length == 0) $(this).parent().find('.delivery_option_carrier').show();
            var carrier_id_list = $(this).val().split(',');
            carrier_id_list.pop();
            var it = this;
            $(carrier_id_list).each(function() {
                $(it).parent().find('input[value="' + this.toString() + '"]').change();
            });
        } else $(this).parent().find('.delivery_option_carrier').hide();
    });
}
$(document).ready(function() {
    refreshDeliveryOptions();
    $('.delivery_option_radio').live('change',
    function() {
        refreshDeliveryOptions();
    });
    $('#allow_seperated_package').live('click',
    function() {
        $.ajax({
            type: 'GET',
            url: baseUri,
            async: true,
            cache: false,
            data: 'controller=cart&ajax=true&allowSeperatedPackage&value=' + ($(this).prop('checked') ? '1': '0') + '&token=' + static_token + '&allow_refresh=1',
            success: function(jsonData) {
                if (typeof(getCarrierListAndUpdate) != 'undefined') getCarrierListAndUpdate();
            }
        });
    });
    $('#gift').checkboxChange(function() {
        $('#gift_div').show('slow');
    },
    function() {
        $('#gift_div').hide('slow');
    });
    $('#enable-multishipping').checkboxChange(function() {
        $('.standard-checkout').hide(0);
        $('.multishipping-checkout').show(0);
    },
    function() {
        $('.standard-checkout').show(0);
        $('.multishipping-checkout').hide(0);
    });
});
function updateExtraCarrier(id_delivery_option, id_address) {
    if (typeof(orderOpcUrl) != 'undefined') var url = orderOpcUrl;
    else var url = orderUrl;
    $.ajax({
        type: 'POST',
        url: url,
        async: true,
        cache: false,
        dataType: "json",
        data: 'ajax=true' + '&method=updateExtraCarrier' + '&id_address=' + id_address + '&id_delivery_option=' + id_delivery_option + '&token=' + static_token + '&allow_refresh=1',
        success: function(jsonData) {
            $('#HOOK_EXTRACARRIER_' + id_address).html(jsonData['content']);
        }
    });
}



