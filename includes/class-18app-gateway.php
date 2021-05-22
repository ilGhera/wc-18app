<?php
/**
 * Estende la classe WC_Payment_Gateway di WooCommerce aggiungendo il nuovo gateway 18app.
 *
 * @author ilGhera
 * @package wc-18app/includes
 * @version 1.1.0
 */
class WC18_18app_Gateway extends WC_Payment_Gateway {

    
	public function __construct() {
		$this->plugin_id          = 'woocommerce_18app';
		$this->id                 = '18app';
		$this->has_fields         = true;
		$this->method_title       = '18app';
		$this->method_description = 'Consente ai diciottenni di utilizzare il buono a loro riservato per l\'acquisto di materiale didattico.';
		
		if ( get_option( 'wc18-image' ) ) {

			$this->icon = WC18_URI . 'images/18app.png';			

		}

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option('title');
		$this->description = $this->get_option('description');

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_18app_code' ), 10, 1 );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'display_18app_code' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_18app_code' ), 10, 1 );
	}


	/**
	 * Campi relativi al sistema di pagamento, modificabili nel back-end
	 */
	public function init_form_fields() {
		
		$this->form_fields = apply_filters( 'wc_offline_form_fields',array(
			'enabled' => array(
		        'title'   => __( 'Enable/Disable', 'woocommerce' ),
		        'type'    => 'checkbox',
		        'label'   => __( 'Abilita pagamento con buono 18app', 'wc18' ),
		        'default' => 'yes',
		    ),
		    'title' => array(
		        'title'       => __( 'Title', 'woocommerce' ),
		        'type'        => 'text',
		        'description' => __( 'This controls the title which the user sees during checkout.', 'wc18' ),
		        'default'     => __( 'Buono 18app', 'wc18' ),
		        'desc_tip'    => true,
		    ),
		    'description' => array(
		        'title'   => __( 'Messaggio utente', 'woocommerce' ),
		        'type'    => 'textarea',
		        'default' => 'Consente ai diciottenni di utilizzare il buono a loro riservato per l\'acquisto di materiale didattico.',
		    )
		));

	}


	/**
	 * Campo per l'inserimento del buono nella pagina di checkout 
	 */
	public function payment_fields() {
		?>
		<p>
			<?php echo $this->description; ?>
			<label for="wc-codice-18app">
				<?php echo __('Inserisci qui il tuo codice', 'wc18');?>
				<span class="required">*</span>
			</label>
			<input type="text" class="wc-codice-18app" id="wc-codice-18app" name="wc-codice-18app" />
		</p>
		<?php
	}


	/**
	 * Restituisce la cateogia prodotto corrispondente al bene acquistabile con il buono
     *
	 * @param  string $purchasable bene acquistabile
     *
	 * @return int l'id di categoria acquistabile
	 */
	public static function get_purchasable_cats( $purchasable ) {

		$wc18_categories = get_option('wc18-categories');

		if ( $wc18_categories ) {
	
			$purchasable = str_replace( '(', '', $purchasable );
			$purchasable = str_replace( ')', '', $purchasable );
			$bene        = strtolower( str_replace( ' ', '-', $purchasable ) );
			
			$output = array();

			for ( $i=0; $i < count( $wc18_categories ); $i++ ) { 

                if ( array_key_exists( $bene, $wc18_categories[$i] ) ) {

                    $output[] = $wc18_categories[$i][$bene];

                }

			}

			return $output;
				
		}

	}


	/**
	 * Tutti i prodotti dell'ordine devono essere della tipologia (cat) consentita dal buono 18app. 
	 * @param  object $order  
	 * @param  string $bene il bene acquistabile con il buono
	 * @return bool
	 */
	public static function is_purchasable( $order, $bene ) {

		$cats   = self::get_purchasable_cats( $bene );
		$items  = $order->get_items();
		$output = true;
		
		if ( is_array( $cats ) && ! empty( $cats ) ) {

			foreach ( $items as $item ) {
				$terms = get_the_terms( $item['product_id'], 'product_cat' );
				$ids   = array();

				foreach ( $terms as $term ) {

                    $ids[] = $term->term_id;

				}

				$results = array_intersect( $ids, $cats );

				if ( ! is_array( $results ) || empty( $results ) ) {

					$output = false;
					continue;

				}

			}		

		}

		return $output;
		
	}


	/**
	 * Mostra il buono 18app nella thankyou page, nelle mail e nella pagina dell'ordine.
	 * @param  object $order
	 * @return mixed        testo formattato con il buono utilizzato per l'acquisto
	 */
	public function display_18app_code( $order ) {
		
		$data = $order->get_data();

		if ( $data['payment_method'] === '18app' ) {

		    echo '<p><strong>' . __( 'Buono 18app', 'wc18' ) . ': </strong>' . get_post_meta( $order->get_id(), 'wc-codice-18app', true ) . '</p>';
            
        }
	}


    /**
     * Processa il buono 18app inserito
     *
     * @param int    $order_id   l'id dell'ordine.
     * @param string $code_18app il valore del buono 18app.
     * @param float  $import     il totale dell'ordine o il valore del coupon.
     *
     * @return mixed string in caso di errore, 1 in alternativa
     */
    public static function process_code( $order_id, $code_18app, $import ) {

        global $woocommerce;

        $output     = 1; 
        $order      = wc_get_order( $order_id );
        $soapClient = new wc18_soap_client( $code_18app, $import );
        
        try {

            /*Prima verifica del buono*/
            $response      = $soapClient->check();
            $bene          = $response->checkResp->ambito; //il bene acquistabile con il buono inserito
            $importo_buono = floatval($response->checkResp->importo); //l'importo del buono inserito
            
            /*Verifica se i prodotti dell'ordine sono compatibili con i beni acquistabili con il buono*/
            $purchasable = self::is_purchasable( $order, $bene );

            if ( ! $purchasable ) {

                $output = __( 'Uno o più prodotti nel carrello non sono acquistabili con il buono inserito.', 'wccd' );

            } else {

                $type = null;

                if ( $importo_buono === $import ) {

                    $type = 'check';

                } else {

                    $type = 'confirm';

                }

                if ( $type ) {

                    try {

                        /*Operazione differente in base al rapporto tra valore del buono e totale dell'ordine*/
                        $operation = $type === 'check' ? $soapClient->check( 2 ) : $soapClient->confirm();

                        /*Ordine completato*/
                        $order->payment_complete();

                        /*Svuota carrello*/ 
                        $woocommerce->cart->empty_cart();	

                        /*Aggiungo il buono 18app all'ordine*/
                        update_post_meta( $order_id, 'wc-codice-18app', $code_18app );

                    } catch ( Exception $e ) {
        
                        $output = $e->detail->FaultVoucher->exceptionMessage;
                   
                    } 

                }

            }

        } catch ( Exception $e ) {

            $output = $e->detail->FaultVoucher->exceptionMessage;
        
        }  

		return $output;

	}


	/**
	 * Gestisce il processo di pagamento, verificando la validità del buono inserito dall'utente
	 * @param  int $order_id l'id dell'ordine
	 */
	public function process_payment( $order_id ) {

	    $order  = wc_get_order( $order_id );
		$import = floatval( $order->get_total() ); //il totale dell'ordine

		$notice = null;
		$output = array(
			'result'   => 'failure',
			'redirect' => '',
		);

		$data       = $this->get_post_data();
	    $code_18app = $data['wc-codice-18app']; //il buono inserito dall'utente

        if ( $code_18app ) {

            $notice = self::process_code( $order_id, $code_18app, $import );

            if ( 1 === intval( $notice ) ) {

                $output = array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );

            } else {

                wc_add_notice( __( 'Buono 18app - ' . $notice, 'wccd' ), 'error' );

            }

	    }	
		
		return $output;

    }

}

