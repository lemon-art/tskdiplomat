/* General JS file */

/* create pf object */
var pf = {

    /* public var's */

    /* Element option group */
    $eOptGroup: '.e__options_group',
    $eOptGroupOpenSelector: 'open',

    /* Block's list open / close */
    $eItemBlock: '.e__item',
    $eItemOCselector: 'open',
    
    /* Calc params */
    calcParams: ['data-weight', 'data-mint', 'data-rashod'],
    calcForm: '#calc',
    
    /* calc method */
    calc: function (params, form) {

        if (typeof(params) == 'object' && jQuery(form).length) {

            var submit = jQuery(form).find('input[type="submit"]'),
                select = jQuery(form).find('#opt1'),
                select2 = jQuery(form).find('#opt2_1'),
                formula = select.children('option:selected').attr('data-formula') ? select.children('option:selected').attr('data-formula') : 0,
                rashod = select.children('option:selected').attr('data-rashod'),
                pl = jQuery('input[name="S"]').val(),
                weight_l = select.children('option:selected').attr('data-unweight-l') ? undefined : jQuery('input[name="F"]:visible').val(),
                weight = select.children('option:selected').attr('data-weight'),
                mint = select.children('option:selected').attr('data-mint') ? select.children('option:selected').attr('data-mint') : 0;
            
            console.log(select2);
            // показываем поля для 1-й или второй формулы
            jQuery('.formula1').hide();
            jQuery('.formula2').show();
            if(formula == 0) {
                jQuery('.formula2').hide();
                jQuery('.formula1').show();
            }

            select.children('option:selected').attr('data-unweight-l') ? jQuery('input[name="F"]:visible').parent().hide() : 0;

            //console.log(rashod + ', ' + pl + ', ' + weight_l + ', ' + weight + ', ' + mint);

            if (mint != undefined) {
                jQuery('input[name="F"]:visible').bind('blur', function () {
                    parseFloat(jQuery(this).val()) < mint ? alert('Недомустимо малое значение, введите больше') : 0;
                });
            }

            function DPCM(input) {
                var value = input.value, re = /[^0-9\-\.]/gi;
                if (re.test(value)) {
                    value = value.replace(re, '');
                    input.value = value;
                }
            }

            jQuery(form).find('input[type="text"]').bind('keypress', function (e) {
                DPCM(this);
            });

            select.bind('change', function () {
                formula = select.children('option:selected').attr('data-formula') ? select.children('option:selected').attr('data-formula') : 0;

                if(formula != 0) {
                    jQuery('.formula1').hide();
                    jQuery('.formula2').show();
                    // Проверяем поля, нужные в формуле 2
                    jQuery('input[name="v"]').val('');
                    jQuery('input[name="d"]').val('');

                    weight_l = undefined;

                    if (jQuery(this).children('option:selected').attr('data-rashod') && jQuery(this).children('option:selected').attr('data-weight')) {
                        weight = jQuery(this).children('option:selected').attr('data-weight');
                        rashod = jQuery(this).children('option:selected').attr('data-rashod');
                    }
                    else {
                            jQuery('#result').empty().append('<span class="error">Заданы не все значения для расчетов</span>');
                   }
                } else {
                    jQuery('.formula2').hide();
                    jQuery('.formula1').show();
                    // Проверяем поля, нужные в формуле 1
                    jQuery('input[name="F"]:visible').val('');
                    jQuery('input[name="S"]').val('');

                    if (jQuery(this).children('option:selected').attr('data-unweight-l')) {
                        weight_l = undefined;
                        jQuery('input[name="F"]:visible').parent().hide();
                    }
                    else {
                        jQuery('input[name="F"]').parent().show();
                        weight_l = jQuery('input[name="F"]:visible').val();
                    }

                    if (jQuery(this).children('option:selected').attr('data-rashod') && jQuery(this).children('option:selected').attr('data-weight')) {
                        weight = jQuery(this).children('option:selected').attr('data-weight');
                        rashod = jQuery(this).children('option:selected').attr('data-rashod');
                        mint = jQuery(this).children('option:selected').attr('data-mint') ? jQuery(this).children('option:selected').attr('data-mint') : 0;

                        //console.log(rashod + ', ' + pl + ', ' + weight_l + ', ' + weight + ', ' + mint);

                        if (mint != undefined) {
                            jQuery('input[name="F"]:visible').bind('blur', function () {
                                parseFloat(jQuery('input[name="F"]:visible').val()) < mint ? alert('Недомустимо малое значение, введите больше') : 0;
                            });
                        }

                    }
                    else {
                            jQuery('#result').empty().append('<span class="error">Заданы не все значения для расчетов, задайте их в админке</span>');
                    }
                }
            });

            jQuery(form).bind('submit', function (e) {
                jQuery('#result').empty();
                if (weight_l != undefined) {
                    console.log('weight_1');
                    // Расчёт по первой формуле в канистрах
                    if (jQuery('input[name="S"]').val() != '' && jQuery('input[name="S"]').val() != undefined && rashod != undefined && weight_l != undefined && jQuery('input[name="F"]:visible').val() != undefined) {
                        if (parseFloat(jQuery('input[name="F"]:visible').val()) >= mint) {
                            pl = jQuery('input[name="S"]').val();
                            weight_l = jQuery('input[name="F"]:visible').val();
                            jQuery('#result').empty().append('Количество мешков равно : <span>' + ((parseFloat(rashod) * parseFloat(weight_l) * parseFloat(pl)) / parseFloat(weight)).toFixed(1) + '</span>');
                        }
                        else {
                            jQuery('#result').empty().append('<span class="error">Недомустимо малое значение, введите больше</span>');
                        }
                    }
                }
                else {
                    if(formula == 0) {
                        if (jQuery('input[name="S"]').val() != '' && jQuery('input[name="S"]').val() != undefined && rashod != undefined && weight != undefined) {
                            pl = jQuery('input[name="S"]').val();
                            
                            jQuery('#result').empty().append('Количество канистр равно : <span>' + ((parseFloat(rashod) * parseFloat(pl)) / parseFloat(weight)).toFixed(1) + '</span>');
                        }
                    } else if(formula == 1 || formula == 2) {
                        // Расчёт по второй формуле в канистрах
                        if (
                            select2.children('option:selected').val() != '' &&
                            select2.children('option:selected').val() != undefined  &&
                            jQuery('input[name="v"]').val() != '' &&
                            jQuery('input[name="v"]').val() != undefined &&
                            jQuery('input[name="d"]').val() != '' &&
                            jQuery('input[name="d"]').val() != undefined &&
                            rashod != undefined &&
                            weight != undefined ) {
                            var a = select2.children('option:selected').val();
                            var v = jQuery('input[name="v"]').val();
                            var d = jQuery('input[name="d"]').val();
//                    console.log('formula:'+formula+' a: '+a+' v:'+v+' d:'+d+' rashiod:'+rashod+' weight:'+weight);

                            if(formula == 1) {
                                jQuery('#result').empty().append('Количество мешков равно : <span>' + (a * parseFloat(d) * parseFloat(v) * rashod * parseFloat(d) / weight).toFixed(1) + '</span>');
                            } else if(formula == 2) {
                                jQuery('#result').empty().append('Количество мешков равно : <span>' + (a * parseFloat(v) * rashod * parseFloat(d) / weight).toFixed(1))+'</span>';
                            }
                        }
                    }
                }

            });
        }

    },

    /* masked method */
    mask: function (pr) {
        if (pr != undefined) {
            jQuery.each(pr, function () {
                if (jQuery(this[0]).length != 0) {
                    jQuery(this[0]).mask(this[1]);
                }
            });
        }
    },


    /* pf init method */
    init: function () {

        /* masked input medthod init */
        this.mask(this.$mask);

        /* Calc method init */
        this.calc(this.calcParams, this.calcForm);

    }

}

/* DOM ready init pf object */
jQuery(document).ready(function () {

    /* Call to pf init method */
    pf.init();

});