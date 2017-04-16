jQuery(document).ready(function(){

	/*********************************************************************************************************** Superfish Menu *********************************************************************/
	/* toggle nav */
	jQuery("#menu-icon").on("click", function(){
		jQuery(".sf-menu-phone").slideToggle();
		jQuery(this).toggleClass("active");
	});

		jQuery('.sf-menu-phone').find('li.parent').append('<strong></strong>');
		jQuery('.sf-menu-phone li.parent strong').on("click", function(){
			if (jQuery(this).attr('class') == 'opened') { jQuery(this).removeClass().parent('li.parent').find('> ul').slideToggle(); } 
				else {
					jQuery(this).addClass('opened').parent('li.parent').find('> ul').slideToggle();
				}
		});

	/*********************************************************************************************************** Cart Truncated *********************************************************************/
	
		jQuery('.truncated span').click(function(){
				jQuery(this).parent().find('.truncated_full_value').stop().slideToggle();
			});


	/*********************************************************************************************************** Product View Accordion *********************************************************************/
		jQuery.fn.slideFadeToggle = function(speed, easing, callback) {
		  return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);  
		};
		jQuery('.box-collateral').not('.box-up-sell').find('h2').append('<span class="toggle"></span>');
		jQuery('.form-add').find('.box-collateral-content').css({'display':'block'}).parents('.form-add').find('> h2 > span').addClass('opened');
		
		jQuery('.box-collateral > h2').click(function(){
			OpenedClass = jQuery(this).find('> span').attr('class');
			if (OpenedClass == 'toggle opened') { jQuery(this).find('> span').removeClass('opened'); }
			else { jQuery(this).find('> span').addClass('opened'); }
			jQuery(this).parents('.box-collateral').find(' > .box-collateral-content').slideFadeToggle()
		});
	/*********************************************************************************************************** Sidebar Accordion *********************************************************************/
		jQuery('.sidebar .block .block-title').append('<span class="toggle"></span>');
		jQuery('.sidebar .block .block-title').on("click", function(){
			if (jQuery(this).find('> span').attr('class') == 'toggle opened') { jQuery(this).find('> span').removeClass('opened').parents('.block').find('.block-content').slideToggle(); }
			else {
				jQuery(this).find('> span').addClass('opened').parents('.block').find('.block-content').slideToggle();
			}
		});

	/*********************************************************************************************************** Footer Accordion *********************************************************************/
		jQuery('.footer .footer-col .footer-col-title').append('<span class="toggle"></span>');
		jQuery('.footer .footer-col-title').on("click", function(){
			if (jQuery(this).find('span').attr('class') == 'toggle opened') { jQuery(this).find('span').removeClass('opened').parents('.footer-col').find('.footer-col-content').slideToggle(); }
			else {
				jQuery(this).find('span').addClass('opened').parents('.footer-col').find('.footer-col-content').slideToggle();
			}
		});

	/*********************************************************************************************************** Header Buttons *********************************************************************/

		jQuery('.header-button').not('.top-auth').on("click", function(e){
		    var ul=jQuery(this).find('ul')
		    if(ul.is(':hidden'))
		     ul.slideDown()
		     ,jQuery(this).addClass('active')
		    else
		     ul.slideUp()
		     ,jQuery(this).removeClass('active')
		     jQuery('.header-button').not(this).removeClass('active'),
		     jQuery('.header-button').not(this).find('ul').slideUp()
		     jQuery('.header-button ul li').click(function(e){
		      	 e.stopPropagation(); 
		    	});
		    	return false
		   });
		   jQuery(document).on('click',function(){ 
		    jQuery('.header-button').removeClass('active').find('ul').slideUp()
		   });
		   
	jQuery(".price-box.map-info a").click(function() { 
        jQuery(".map-popup").toggleClass("displayblock");
    });
   jQuery('.map-popup-close').on('click',function(){ 
    jQuery('.map-popup').removeClass('displayblock');
   });

	/*if(jQuery(".category-products").length){	jQuery('.products-grid .add-to-links li > a, .products-list .add-to-links li > a ').tooltip('hide')	};*/
	qwe = jQuery('.lang-list ul li span').text();
	jQuery('.lang-list > a').text(qwe);

	jQuery(window).bind('load resize',function(){
	      sw = jQuery('.container').width();
		  if ( sw > 723 ) {
				jQuery('body').addClass('opened-1');
		  } else { 
			   jQuery('body').removeClass('opened-1');
		  };
	});
	
	/*********************************************************************************************************** Header Cart *********************************************************************/
		jQuery('.block-cart-header .cart-content').hide();
		if (jQuery('.container').width() < 800) {
			jQuery('.block-cart-header .summary, .block-cart-header .cart-content, .block-cart-header .empty').click(function(){
					jQuery('.block-cart-header .cart-content').stop(true, true).slideToggle(300);
				}
			)
		}
		else {
			jQuery('.block-cart-header .summary, .block-cart-header .cart-content, .block-cart-header .empty').hover(
				function(){jQuery('.block-cart-header .cart-content').stop(true, true).slideDown(400);},
				function(){	jQuery('.block-cart-header .cart-content').stop(true, true).delay(400).slideUp(300);}
			);
		};

        /* toggle cat */
        jQuery('.cat-text .cat-text-opener').on('click',function(){
            var cat = jQuery(this).parent('.cat-text');
            if(jQuery(cat).hasClass('in')){
                jQuery(this).parent('.cat-text').removeClass('in');
            }else{
                jQuery(this).parent('.cat-text').addClass('in');
            }
        }); 
        
        jQuery('.categories .divmore span').on('click',function(){
            console.log('click');
            var cat = jQuery(this).parents('.subcategories');
            console.log(cat);
            if(jQuery(cat).hasClass('in')){
                console.log('remove in');
                jQuery(cat).removeClass('in');
            }else{
                console.log('set in');
                jQuery(cat).addClass('in');
            }
        }); 
        
                
});

//******************************** Cart & compare process

function MarkAlreadyInCart (arid) { // меняем ссылки и текст на кнопках добавления в корзину
    jQuery.each(arid,function(index,id){
    jQuery('.add2cart[data-id='+id+']').addClass('already_in_cart').removeClass('add2cart')
                                  .text("В корзине").attr('href', "/personal/cart/")
                                  .unbind("click").click(function(){window.location='/personal/cart/'});
        
    });
}

function Add2CartWindow (respond) {
        if(!jQuery('#add2cart-popup').length){
            jQuery('body').append('<div  class="modal fade" id="add2cart-popup" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true"><div class="modal-dialog modal-dialog"><button type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button><div class="modal-content"><div class="modal-header"><h2>Товар добавлен в корзину</h2></div><div class="modal-body"><ul class="products"></ul></div><div class="modal-footer"><button class="button btn-gray" onclick="jQuery(\'#add2cart-popup\').modal(\'hide\');">Продолжить покупки</button><button class="button" href="/personal/cart/">В корзину</button></div></div></div></div>');
        }
        
        jQuery('#add2cart-popup .products').empty();

        jQuery('#add2cart-popup').modal('show');
        
        jQuery.each(respond.product,function(index,product){
            jQuery('#add2cart-popup .products').append('<li id="add2cart_item_'+index+'"><li>');
            jQuery('#add2cart_item_'+index).append('<a href="'+product.link + '" class="image"><img src="'+product.picture.src+'"/></a>');
            jQuery('#add2cart_item_'+index).append('<a href="'+product.link + '" class="name">'+product.name+'</a><br/>');
            jQuery('#add2cart_item_'+index).append('<span class="qty">'+product.qty+'</span> шт. ');
            jQuery('#add2cart_item_'+index).append('цена <span class="price">'+product.price.toString()+'</span>');
            //console.log(product);
        });
       
}

function MarkAlreadyInCompare (arid) { // меняем ссылки и текст на кнопках добавления в корзину
    jQuery.each(arid,function(index,id){
    jQuery('.add2compare[data-id='+id+']').addClass('already_in_compare').removeClass('add2compare')
                                  .text("В сравнении").attr('href', "/catalog/compare.php")
                                  .unbind("click").click(function(){window.location='/catalog/compare.php'});
        
    });
}
function MarkAlreadyInWishlist (arid) { // меняем ссылки и текст на кнопках добавления в корзину
    jQuery.each(arid,function(index,id){
    jQuery('.add2wishlist[data-id='+id+']').addClass('already_in_wishlist').removeClass('add2wishlist')
                                  .text("В избранном").attr('href', "/personal/wishlist/index.php")
                                  .unbind("click").click(function(){window.location='/personal/wishlist/index.php'});
        
    });
}
jQuery(document).ready(function(){    
    jQuery('.add2cart').click(function(){
        console.log('click');
        var id = jQuery(this).attr('data-id');
        var qty = 1; 
        if (jQuery('#add2cart_qty')) qty = parseInt(jQuery('#add2cart_qty').val());
        jQuery.getJSON('/local/ajax/add2cart.php', {'id':id, 'qty':qty}, function(resp){
            console.log(resp);
            MarkAlreadyInCart([id]);
            /*
            jQuery('.add2cart_'+id).addClass('already_in_cart').removeClass('add2cart')
                                  .text("Товар в корзине").attr('href', "/personal/cart/")
                                  .unbind("click").click(function(){window.location='/personal/cart/'});
            */
            Add2CartWindow(resp);
            return false;
        }).fail(function( jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                alert('Что то пошло не так! Мы не смогли добавить товар в корзину....');
                console.log( "Request Failed: " + err );
            });

            return false;
    });
    jQuery('.add2compare').click(function(){
        var id = jQuery(this).attr('data-id');
        jQuery.getJSON('/local/ajax/compare.php', {'id':id}, function(resp){
            console.log(resp);
            MarkAlreadyInCompare([id]);
            return false;
        }).fail(function( jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                alert('Что то пошло не так! Мы не смогли добавить товар в сравнение....');
                console.log( jqxhr.responseText );
                console.log( "Request Failed: " + err );
            });
        return false;
    });
    jQuery('.add2wishlist').click(function(){
        var id = jQuery(this).attr('data-id');
        jQuery.getJSON('/personal/wishlist/index.php', {id:id,ajax_wishlist:'Y',action:'add'}, function(resp){
            console.log(resp);
            MarkAlreadyInWishlist([id]);
            return false;
        }).fail(function( jqxhr, textStatus, error ) {
                var err = textStatus + ", " + error;
                alert('Что то пошло не так! Мы не смогли добавить товар в избранное....');
                console.log( jqxhr.responseText );
                console.log( "Request Failed: " + err );
            });
        return false;
    });    
    if(templVars){
       MarkAlreadyInCart(templVars.BASKET_ITEMS);
       MarkAlreadyInCompare(templVars.COMPARE_ITEMS);
       MarkAlreadyInWishlist(templVars.WISHLIST_ITEMS);
    }
});

/*************************************************************************************************************back-top*****************************************************************************/
jQuery(function () {
 jQuery(window).scroll(function () {
  if (jQuery(this).scrollTop() > 100) {
   jQuery('#back-top').fadeIn();
  } else {
   jQuery('#back-top').fadeOut();
  }
 });

 // scroll body to 0px on click
 jQuery('#back-top a').click(function () {
  jQuery('body,html').stop(false, false).animate({
   scrollTop: 0
  }, 800);
  return false;
 });
});

/***************************************************************************************************** Magento class **************************************************************************/
jQuery(document).ready(function() {
	jQuery('.sidebar .block').last().addClass('last_block');
	jQuery('.sidebar .block').first().addClass('first');
	jQuery('.box-up-sell li').eq(2).addClass('last');
	jQuery('.form-alt li:last-child').addClass('last');
	jQuery('.product-collateral #customer-reviews dl dd, #cart-sidebar .item').last().addClass('last');
    jQuery('.header .row-2 .links').first().addClass('LoginLink');
	jQuery('#checkout-progress-state li:odd').addClass('odd');
	jQuery('.product-view .product-img-box .product-image').append('<span></span>');
    jQuery('.links a.top-link-cart').parent().addClass('top-car');
    jQuery('.footer-cols-wrapper .footer-col').last().addClass('last');
  
	if (jQuery('.container').width() < 766) {
		jQuery('.my-account table td.order-id').prepend('<strong>Order #:</strong>');
		jQuery('.my-account table td.order-date').prepend('<strong>Date: </strong>');
		jQuery('.my-account table td.order-ship').prepend('<strong>Ship To: </strong>');
		jQuery('.my-account table td.order-total').prepend('<strong>Order Total: </strong>');
		jQuery('.my-account table td.order-status').prepend('<strong>Status: </strong>');
		jQuery('.my-account table td.order-sku').prepend('<strong>SKU: </strong>');
		jQuery('.my-account table td.order-price').prepend('<strong>Price: </strong>');
		jQuery('.my-account table td.order-subtotal').prepend('<strong>Subtotal: </strong>');
		
		//jQuery('.multiple-checkout td.order-qty, .multiple-checkout th.order-qty').prepend('<strong>Qty: </strong>');
		//jQuery('.multiple-checkout td.order-shipping, .multiple-checkout th.order-shipping, ').prepend('<strong>Send To: </strong>');
		//jQuery('.multiple-checkout td.order-subtotal, .multiple-checkout th.order-subtotal').prepend('<strong>Subtotal: </strong>');
		//jQuery('.multiple-checkout td.order-price, .multiple-checkout th.order-price').prepend('<strong>Price: </strong>');
	}
});

jQuery(document).ready(function() {
    
	if (jQuery('.container').width() < 724) {
		jQuery('.related-carousel').jcarousel({
			vertical: false,
			visible:1,
			scroll: 1
		});
                jQuery('.brands-carousel').jcarousel({
			vertical: false,
			visible:1,
			scroll: 1
		});
                
	}
	else {
		jQuery('.related-carousel').jcarousel({
			vertical: false,
			visible:3,
			scroll: 1
		});
                jQuery('.brands-carousel').jcarousel({
			vertical: false,
			visible:3,
			scroll: 1
		});
	}
        
        jQuery('.fancy').fancybox();
        /*
        //brands grey
                jQuery('.greyScale').greyScale({
                    // call the plugin with non-defult fadeTime (default: 400ms)
                    fadeTime: 50,
                    reverse: false
                });
        */        
});
(function(doc) {

	var addEvent = 'addEventListener',
	    type = 'gesturestart',
	    qsa = 'querySelectorAll',
	    scales = [1, 1],
	    meta = qsa in doc ? doc[qsa]('meta[name=viewport]') : [];

	function fix() {
		meta.content = 'width=device-width,minimum-scale=' + scales[0] + ',maximum-scale=' + scales[1];
		doc.removeEventListener(type, fix, true);
	}

	if ((meta = meta[meta.length - 1]) && addEvent in doc) {
		fix();
		scales = [.25, 1.6];
		doc[addEvent](type, fix, true);
	}

}(document));