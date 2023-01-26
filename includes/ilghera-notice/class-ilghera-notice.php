<?php
/**
 * ilGhera Notice class
 *
 * @author ilGhera
 * @package ilghera-notice/ 
 * @version 0.9.0
 */

/**
 * ilGhera Notice
 */
class Ilghera_Notice {

    /**
     * The construct
     */
    public function __construct() {

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        $this->check_license();

    }

    
    public function enqueue_scripts() {

		wp_enqueue_style( 'ilghera-notice-style', plugin_dir_url( __FILE__ ) . 'css/ilghera-notice.css' );

    }


    /**
     * The notice 
     *
     * @return void
     */
    public function the_notice() {

        echo '<div class="update-nag notice notice-warning ilghera-notice-warning is-dismissible">';

            /* Translators: img URL, product URL */
            printf( wp_kses_post(
                __( '<img src="%1$s"><b>WooCommerce 18app - Premium</b>. Your license is <u>expired</u> and you\'re unable to receive updates, renew it by clicking on this <a href="%2$s" target="_blank">link</a>.', 'wcifd' ) ),
                esc_url( plugin_dir_url( __FILE__ ) . 'images/ilGhera-icon-40px.png' ),
                esc_url( 'https://www.ilghera.com/product/woocommerce-18app-premium/' )
            );

        echo '</div>';

    }


    /**
     * Check if the license is expired
     */
    public function check_license() {

        /* Get the Premium key details */
        $decoded_key = explode( '|', base64_decode( get_option( 'wc18-premium-key' ) ) );
        $bought_date = date( 'd-m-Y', strtotime( $decoded_key[1] ) );
        $limit       = strtotime( $bought_date . ' + 365 day' );
        $now         = strtotime( 'today' );

        if ( $limit < $now ) {

            add_action( 'admin_notices', array( $this, 'the_notice' ) );

        }

    }

} 
new Ilghera_Notice();

