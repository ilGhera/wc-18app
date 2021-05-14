<?php
/**
 * Class WC18
 *
 * @author ilGhera
 * @package wc-18app/includes
 * @since 0.9.2
 */
class WC18 {

    
    public $coupon_option;
    

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct() {

        /* Controlla se l'optione è stata attivata dall'admin */
        $this->coupon_option = get_option( 'wc18-coupon' );

        /* Actions */
        add_action( 'wp_ajax_check-for-coupon', array( $this, 'wc18_check_for_coupon' ) );
        add_action( 'wp_ajax_nopriv_check-for-coupon', array( $this, 'wc18_check_for_coupon' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'process_coupon' ) );

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
     * Verifica se in sessione è stato applicato un coupon derivante da un buono 18app 
     *
     * @param bool $return restituisce il codice del coupon se valorizzato.
     *
     * @return mixed
     */
    public function wc18_coupon_applied( $return = false ) {

        $output       = false;
        $session_data = $this->get_session_data();

        if ( $session_data ) {

            $coupons = isset( $session_data['applied_coupons'] ) ? maybe_unserialize( $session_data['applied_coupons'] ) : null;
            
            if ( $coupons && is_array( $coupons ) ) {

                foreach ( $coupons as $coupon ) {

                    if ( false !== strpos( $coupon, 'wc18' ) ) {
                        
                        if ( $return ) {

                            $output = $coupon; 

                        } else {

                            $output = true;

                        }

                        continue;

                    }
                }

            }

        }

        return $output;

    }


    /**
     * Verifica di un coupon wc18 in sessione al click di aquisto in pagina di checkout
     *
     * @return void
     */
    public function wc18_check_for_coupon() {
        
        echo $this->wc18_coupon_applied();

        exit;

    }


    /**
     * Se presente un certificato, aggiunge il nuovo gateway a quelli disponibili in WooCommerce
     *
     * @param array $methods gateways disponibili 
     *
     * @return array
     */
    public function wc18_add_teacher_gateway_class( $methods ) {
        
        $available = ( $this->coupon_option && $this->wc18_coupon_applied() ) ? false : true;

        if ( $available && wc18_admin::get_the_file( '.pem' ) && get_option( 'wc18-cert-activation' ) ) {

            $methods[] = 'WC18_18app_Gateway'; 

        }

        return $methods;

    }


    /**
     * Durante la creazione dell'ordine se presente un coupon wc18 invia il buono a 18app 
     * L'ordine viene bloccato se il buono non risulta essere valido
     *
     * @return void
     */
    public function process_coupon() {

        if ( $this->coupon_option ) {

            $coupon_code = $this->wc18_coupon_applied( true );

            if ( $coupon_code ) {

                $parts         = explode( '-', $coupon_code );
                $coupon        = new WC_Coupon( $coupon_code );
                $coupon_amount = $coupon->get_amount();

                $notice = WC18_18app_Gateway::process_code( $parts[1], $parts[2], $coupon_amount, true );

                if ( 1 !== intval( $notice ) ) {

                    wc_add_notice( __( 'Buono 18app - ' . $notice, 'wc18' ), 'error' );         

                }
            }

        }
  
    }

}

new WC18();

