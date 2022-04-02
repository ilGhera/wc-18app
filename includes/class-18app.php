<?php
/**
 * Class WC18
 *
 * @author ilGhera
 * @package wc-18app/includes
 * @since 1.1.0
 */
class WC18 {

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct() {

        /* Filters */
        add_filter( 'woocommerce_payment_gateways', array( $this, 'wc18_add_teacher_gateway_class' ) );

    }


    /**
     * Restituisce i dati della sessione WC corrente
     *
     * @return array
     */
    public function get_session_data() {

        $session = WC()->session;

        if ( $session ) {

            return $session->get_session_data();

        }

    }


    /**
     * Se presente un certificato, aggiunge il nuovo gateway a quelli disponibili in WooCommerce
     *
     * @param array $methods gateways disponibili 
     *
     * @return array
     */
    public function wc18_add_teacher_gateway_class( $methods ) {
        
        if ( wc18_admin::get_the_file( '.pem' ) && get_option( 'wc18-cert-activation' ) ) {

            $methods[] = 'WC18_18app_Gateway'; 

        }

        return $methods;

    }

}

new WC18();

