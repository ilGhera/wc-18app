<?php
/**
 * Estende la classe WC_Payment_Gateway di WooCommerce aggiungendo il nuovo gateway 18app.
 * @author ilGhera
 * @package wc-18app/includes
 * @version 0.9.0
 */
class WC18_18app_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->plugin_id = 'woocommerce_18app';
		$this->id = '18app';
		$this->has_fields = true;
		$this->method_title = '18app';
		$this->method_description = 'Consente ai diciottenni di utilizzare il buono a loro riservato per l\'acquisto di materiale didattico.';
		
		if(get_option('wc18-image')) {
			$this->icon = WC18_URI . 'images/18app.png';			
		}

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action('woocommerce_order_details_after_order_table', array($this, 'display_18app_code'), 10, 1);
		add_action('woocommerce_email_after_order_table', array($this, 'display_18app_code'), 10, 1);
		add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_18app_code'), 10, 1);
	}


	/**
	 * Campi relativi al sistema di pagamento, modificabili nel back-end
	 */
	public function init_form_fields() {
		
		$this->form_fields = apply_filters( 'wc_offline_form_fields',array(
			'enabled' => array(
		        'title' => __( 'Enable/Disable', 'woocommerce' ),
		        'type' => 'checkbox',
		        'label' => __( 'Abilita pagamento con buono 18app', 'wc18' ),
		        'default' => 'yes'
		    ),
		    'title' => array(
		        'title' => __( 'Title', 'woocommerce' ),
		        'type' => 'text',
		        'description' => __( 'This controls the title which the user sees during checkout.', 'wc18' ),
		        'default' => __( 'Buono 18app', 'wc18' ),
		        'desc_tip'      => true,
		    ),
		    'description' => array(
		        'title' => __( 'Messaggio utente', 'woocommerce' ),
		        'type' => 'textarea',
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
	 * @param  string $purchasable bene acquistabile
	 * @return int                 l'id di categoria acquistabile
	 */
	public function get_purchasable_cat($purchasable) {

		$wc18_categories = get_option('wc18-categories');
		$bene = strtolower($purchasable);
		
		for($i=0; $i < count($wc18_categories); $i++) { 
			if(array_key_exists($bene, $wc18_categories[$i])) {
				return $wc18_categories[$i][$bene];
			}
		}

	}


	/**
	 * Tutti i prodotti dell'ordine devono essere della tipologia (cat) consentita dal buono 18app. 
	 * @param  object $order  
	 * @param  string $bene il bene acquistabile con il buono
	 * @return bool
	 */
	public function is_purchasable($order, $bene) {
		$cat = $this->get_purchasable_cat($bene);

		$items = $order->get_items();

		$output = true;
		foreach ($items as $item) {
			$terms = get_the_terms($item['product_id'], 'product_cat');
			$ids = array();

			foreach($terms as $term) {
				$ids[] = $term->term_id;
			}

			if(!in_array($cat, $ids)) {
				$output = false;
				continue;
			}				

		}		
		
		return $output;
	}


	/**
	 * Mostra il buono 18app nella thankyou page, nelle mail e nella pagina dell'ordine.
	 * @param  object $order
	 * @return mixed        testo formattato con il buono utilizzato per l'acquisto
	 */
	public function display_18app_code($order) {
		
		$data = $order->get_data();

		if($data['payment_method'] === '18app') {
		    echo '<p><strong>' . __('Buono 18app', 'wc18') . ': </strong>' . get_post_meta($order->get_id(), 'wc-codice-18app', true) . '</p>';
		}
	}


	/**
	 * Gestisce il processo di pagamento, verificando la validità del buono inserito dall'utente
	 * @param  int $order_id l'id dell'ordine
	 */
	public function process_payment($order_id) {

		global $woocommerce;
	    $order = new WC_Order($order_id);
		$import = floatval($order->get_total()); //il totale dell'ordine

		$notice = null;
		$output = array(
			'result' => 'failure',
			'redirect' => ''
		);

		$data = $this->get_post_data();
	    $app_code = $data['wc-codice-18app']; //il buono inserito dall'utente

	    if($app_code) {

		    $soapClient = new wc18_soap_client($app_code, $import);
		    
		    try {

		    	/*Prima verifica del buono*/
	            $response = $soapClient->check();

				$bene    = $response->checkResp->bene; //il bene acquistabile con il buono inserito
			    $importo_buono = floatval($response->checkResp->importo); //l'importo del buono inserito
			    
			    /*Verifica se i prodotti dell'ordine sono compatibili con i beni acquistabili con il buono*/
			    $purchasable = $this->is_purchasable($order, $bene);

			    if(!$purchasable) {

					$notice = __('Uno o più prodotti nel carrello non sono acquistabili con il buono inserito.', 'wc18');

				} else {

					$type = null;
					if($importo_buono === $import) {

						$type = 'check';

					} else {

						$type = 'confirm';

					}

					if($type) {

						try {

							/*Operazione differente in base al rapporto tra valore del buono e totale dell'ordine*/
							$operation = $type === 'check' ? $soapClient->check(2) : $soapClient->confirm();

							/*Ordine completato*/
						    $order->payment_complete();

						    // Reduce stock levels
						    // $order->reduce_order_stock();// Deprecated
						    // wc_reduce_stock_levels($order_id);

						    /*Svuota carrello*/ 
						    $woocommerce->cart->empty_cart();	

						    /*Aggiungo il buono 18app all'ordine*/
							update_post_meta($order_id, 'wc-codice-18app', $app_code);

						    $output = array(
						        'result' => 'success',
						        'redirect' => $this->get_return_url($order)
						    );

						} catch(Exception $e) {
			
				            $notice = $e->detail->FaultVoucher->exceptionMessage;
				       
				        } 

					}

				}

	        } catch(Exception $e) {

	            $notice = $e->detail->FaultVoucher->exceptionMessage;
	        
	        }  

	    }	
		
		if($notice) {
			wc_add_notice( __('<b>Buono 18app</b> - ' . $notice, 'wc18'), 'error' );
		}

		return $output;

	}

}


/**
 * Se presente un certificato, aggiunge il nuovo gateway a quelli disponibili in WooCommerce
 * @param array $methods gateways disponibili 
 */
function wc18_add_18app_gateway_class($methods) {
	if(wc18_admin::get_the_file('.pem') && get_option('wc18-cert-activation')) {
	    $methods[] = 'WC18_18app_Gateway'; 
	}

    return $methods;
}
add_filter('woocommerce_payment_gateways', 'wc18_add_18app_gateway_class');