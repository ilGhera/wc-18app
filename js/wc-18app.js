/**
 * WC 18app - js
 *
 * @author ilGhera
 * @package wc-18app/js
 * @version 1.1.0
 */
var wc18Controller = function() {

	var self = this;

	self.onLoad = function() {

        self.checkForCoupon();

    }

    /**
     * Aggiorna la pagina di checkout nel caso ion cui sia stato inserito in coupon
     *
     * @return void
     */
    self.checkForCoupon = function() {
    
        jQuery(document).ready(function($){
            
            $('body').on('checkout_error', function() {
                
                if ( wc18Options.couponConversion ) {

                    var data = {
                        'action': 'check-for-coupon'
                    }

                    $.post(wc18Options.ajaxURL, data, function(response) {
                        
                        if (response) {

                            $('body').trigger('update_checkout');
                        
                        }

                    })
                }

            })

        })
            
    }

}

/**
 * Class starter with onLoad method
 */
jQuery(document).ready(function($) {
	
	var Controller = new wc18Controller;
	Controller.onLoad();

});

