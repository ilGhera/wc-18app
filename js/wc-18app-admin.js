/**
 * WC 18app - Admin js
 *
 * @author ilGhera
 * @package wc-18app/js
 * 
 * @since 1.4.0
 */

/**
 * Ajax - Elimina il certificato caricato precedentemente
 */
var wc18_delete_certificate = function() {
	jQuery(function($){
		$('.wc18-delete-certificate').on('click', function(){
			var sure = confirm('Sei sicuro di voler eliminare il certificato?');
			if(sure) {
				var cert = $('.cert-loaded').text();
				var data = {
					'action': 'wc18-delete-certificate',
					'wc18-delete': true,
                    'delete-nonce': wc18Data.delCertNonce,
					'cert': cert
				}			
				$.post(ajaxurl, data, function(response){
					location.reload();
				})
			}
		})	
	})
}
wc18_delete_certificate();


/**
 * Aggiunge un nuovo abbinamento bene/ categoria per il controllo in pagina di checkout
 */
var wc18_add_cat = function() {
	jQuery(function($){
		$('.add-cat-hover.wc18').on('click', function(){
			var number = $('.setup-cat').length + 1;

			/*Beni già impostati da escludere*/
			var beni_values = [];
			$('.wc18-field.beni').each(function(){
				beni_values.push($(this).val());
			})

			var data = {
				'action': 'wc18-add-cat',
				'number': number,
				'exclude-beni': beni_values.toString(),
                'add-cat-nonce': wc18Data.addCatNonce,
			}
			$.post(ajaxurl, data, function(response){
				$(response).appendTo('.categories-container');
				$('.wc18-tot-cats').val(number);
			})				
		})
	})
}
wc18_add_cat();


/**
 * Rimuove un abbinamento bene/ categoria
 */
var wc18_remove_cat = function() {
	jQuery(function($){
		$(document).on('click', '.remove-cat-hover', function(response){
			var cat = $(this).closest('li');
			$(cat).remove();
			var number = $('.setup-cat').length;
			$('.wc18-tot-cats').val(number);
		})
	})
}
wc18_remove_cat();


/**
 * Funzionalità Sandbox
 */
var wc18_sandbox = function() {
	jQuery(function($){

        var data, sandbox;
        var nonce = $('#wc18-sandbox-nonce').attr('value');
        
        $(document).ready(function() {

            if ( 'wc18-certificate' == $('.nav-tab.nav-tab-active').data('link') ) {

                if ( $('.wc18-sandbox-field .tzCheckBox').hasClass( 'checked' ) ) {
                    $('#wc18-certificate').hide();
                    $('#wc18-sandbox-option').show();

                } else {
                    $('#wc18-certificate').show();
                    $('#wc18-sandbox-option').show();
                }

            }

        })

        $(document).on( 'click', '.wc18-sandbox-field .tzCheckBox', function() {

            if ( $(this).hasClass( 'checked' ) ) {
                $('#wc18-certificate').hide();
                sandbox = 1;
            } else {
                $('#wc18-certificate').show('slow');
                sandbox = 0;
            }

            data = {
                'action': 'wc18-sandbox',
                'sandbox': sandbox,
                'nonce': nonce
            }

            $.post(ajaxurl, data);

        })

    })
}
wc18_sandbox();


/**
 * Menu di navigazione della pagina opzioni
 */
var wc18_menu_navigation = function() {
	jQuery(function($){

		var contents = $('.wc18-admin')
		var url      = window.location.href.split("#")[0];
		var hash     = window.location.href.split("#")[1];

		if(hash) {
	        contents.hide();		    
            
            if( 'wc18-certificate' == hash ) {
                wc18_sandbox();
            } else {
                $('#' + hash).fadeIn(200);		
            }

	        $('h2#wc18-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
	        $('h2#wc18-admin-menu a').each(function(){
	        	if($(this).data('link') == hash) {
	        		$(this).addClass('nav-tab-active');
	        	}
	        })
	        
	        $('html, body').animate({
	        	scrollTop: 0
	        }, 'slow');
		}

		$("h2#wc18-admin-menu a").click(function () {
	        var $this = $(this);
	        
	        contents.hide();
	        $("#" + $this.data("link")).fadeIn(200);

            if( 'wc18-certificate' == $this.data("link") ) {
                $('#wc18-sandbox-option').fadeIn(200);
            
                wc18_sandbox();
            
            }
	        
            $('h2#wc18-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
	        $this.addClass('nav-tab-active');

	        window.location = url + '#' + $this.data('link');

	        $('html, body').scrollTop(0);

	    })

	})
}
wc18_menu_navigation();

/**
 * Mostra i dettagli della mail all'utente
 * nel caso la funzione ordini in sospeso sia stata attivata
 *
 * @return void
 */
var wc18_email_details = function() {
    jQuery(function($){
        $(document).ready(function() {

            var on_hold       = $('.wc18-orders-on-hold');
            var email_details = $('.wc18-email-details');

            if ( $('.tzCheckBox', on_hold).hasClass( 'checked' ) ) {
                $(email_details).show();
            }

            $('.tzCheckBox', on_hold).on( 'click', function() {

                if ( $(this).hasClass( 'checked' ) ) {
                    $(email_details).show('slow');
                } else {
                    $(email_details).hide();
                }

            })
            
        })
    })
}
wc18_email_details();

/**
 * Attivazione opzione coupon con esclusione spese di spedizione
 *
 * @return void
 */
var wc18_exclude_shipping = function() {

    jQuery(function($){
        $(document).ready(function() {

            var excludeShipping = $('.wc18-exclude-shipping');
            var coupon          = $('.wc18-coupon');

            $('.tzCheckBox', excludeShipping).on( 'click', function() {

                if ( $(this).hasClass( 'checked' ) && ! $('.tzCheckBox', coupon).hasClass( 'checked' ) ) {
                    $('.tzCheckBox', coupon).trigger('click');
                }

            })

            // Non disattivare opzione coupon con esclusione spese di spedizione attive
            $('.tzCheckBox', coupon).on( 'click', function() {

                if ( ! $(this).hasClass( 'checked' ) && $('.tzCheckBox', excludeShipping).hasClass( 'checked' ) ) {
                    alert( 'L\'esclusione delle spese di spedizione prevedere l\'utilizzo di questa funzionalità.' );
                    $('.tzCheckBox', coupon).trigger('click');
                }

            })
        })
    })

}
wc18_exclude_shipping();
