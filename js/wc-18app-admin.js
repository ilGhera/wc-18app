/**
 * WC 18app - Admin js
 * @author ilGhera
 * @package wc-18app/js
 * @version 0.9.0
 */

/**
 * Ajax - Elimina il certificato caricato precedentemente
 */
var wc18_delete_certificate = function() {
	jQuery(function($){
		$('.delete-certificate').on('click', function(){
			var sure = confirm('Sei sicuro di voler eliminare il certificato?');
			if(sure) {
				var cert = $('.cert-loaded').text();
				var data = {
					'action': 'delete-certificate',
					'delete': true,
					'cert': cert
				}			
				$.post(ajaxurl, data, function(response){
					// console.log(response);
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
		$('.add-cat-hover').on('click', function(){
			var number = $('.setup-cat').length + 1;
			var limit = $('.wc18-field.beni:first option').size() -1;

			/*Beni già impostati da escludere*/
			var beni_values = [];
			$('.wc18-field.beni').each(function(){
				beni_values.push($(this).val());
			})

			/*Categorie già utilizzate da escludere*/
			var cats_values = [];
			$('.wc18-field.categories').each(function(){
				cats_values.push($(this).val());
			})

			/*Se assegnate tutte le categorie visualizza messaggio*/
			if(number > limit) {
				alert('Tutte le categorie di prodotto sono state assegnate.');
			} else {
				
				var data = {
					'action': 'add-cat',
					'number': number,
					'exclude-beni': beni_values.toString(),
					'exclude-cats': cats_values.toString()
				}
				$.post(ajaxurl, data, function(response){
					$(response).appendTo('.categories-container');
					$('.wc18-tot-cats').val(number);
				})				
			}
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
 * Menu di navigazione della pagina opzioni
 */
var wc18_menu_navigation = function() {
	jQuery(function($){
		var $contents = $('.wc18-admin')
		var url = window.location.href.split("#")[0];
		var hash = window.location.href.split("#")[1];

		if(hash) {
	        $contents.hide();		    
		    $('#' + hash).fadeIn(200);		
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
	        
	        $contents.hide();
	        $("#" + $this.data("link")).fadeIn(200);
	        $('h2#wc18-admin-menu a.nav-tab-active').removeClass("nav-tab-active");
	        $this.addClass('nav-tab-active');

	        window.location = url + '#' + $this.data('link');

	        $('html, body').scrollTop(0);

	    })

	})
}
wc18_menu_navigation();