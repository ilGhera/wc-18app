<?php
/**
 * Pagina opzioni e gestione certificati
 * @author ilGhera
 * @package wc-18app/includes
 * @version 0.9.0
 */
class wc18_admin {

	public function __construct() {
		add_action('admin_init', array($this, 'wc18_save_settings'));
		add_action('admin_menu', array($this, 'register_options_page'));
		add_action('wp_ajax_delete-certificate', array($this, 'delete_certificate_callback'));
		add_action('wp_ajax_add-cat', array($this, 'add_cat_callback'));
	}


	/**
	 * Registra la pagina opzioni del plugin
	 */
	public function register_options_page() {
		add_submenu_page( 'woocommerce', __('WooCommerce 18app - Impostazioni', 'wc18'), __('WC 18app', 'wc18'), 'manage_options', 'wc18-settings', array($this, 'wc18_settings'));
	}


	/**
	 * Verifica la presenza di un file per estenzione
	 * @param string $ext l,estensione del file da cercare
	 * @return string l'url file
	 */
	public static function get_the_file($ext) {
		$files = [];
		foreach (glob(WC18_PRIVATE . '*' . $ext) as $file) {
			$files[] = $file; 
		}
		$output = empty($files) ? false : $files[0];

		return $output;
	}


	/**
	 * Cancella il certificato
	 */
	public function delete_certificate_callback() {
		if(isset($_POST['delete'])) {
			$cert = isset($_POST['cert']) ? sanitize_text_field($_POST['cert']) : '';
			if($cert) {
				unlink(WC18_PRIVATE . $cert);	
			}
		}

		exit;
	}


	/**
	 * Categoria per la verifica in fase di checkout
	 * @param  int   $n            il numero dell'elemento aggiunto
	 * @param  array $data         bene e categoria come chiave e velore
	 * @param  array $exclude_beni beni già utilizzati da escludere
	 * @param  array $exclude_cats categorie già utilizzate da escludere
	 * @return mixed]
	 */
	public function setup_cat($n, $data = null, $exclude_beni = null, $exclude_cats = null) {
		echo '<li class="setup-cat cat-' . $n . '">';

			$beni = array_diff(array('libri', 'testi', 'hardware', 'software'), explode(',', $exclude_beni));
			$terms = get_terms('product_cat', array('exclude' => $exclude_cats));

			$bene_value = is_array($data) ? key($data) : '';
			$term_value = $bene_value ? $data[$bene_value] : '';


			echo '<select name="wc18-beni-' . $n . '" class="wc18-field beni">';
				echo '<option value="">Bene 18app</option>';
				foreach ($beni as $bene) {
    				echo '<option value="' . $bene . '"' . ($bene === $bene_value ? ' selected="selected"' : '') . '>' . ucfirst($bene) . '</option>';
				}
			echo '</select>';

			echo '<select name="wc18-categories-' . $n . '" class="wc18-field categories">';
				echo '<option value="">Categoria WooCommerce</option>';
				foreach ($terms as $term) {
    				echo '<option value="' . $term->term_id . '"' . ($term->term_id == $term_value ? ' selected="selected"' : '') . '>' . $term->name . '</option>';
				}
			echo '</select>';

			if($n === 1) {

				echo '<div class="add-cat-container">';
	    			echo '<img class="add-cat" src="' . WC18_URI . 'images/add-cat.png">';
	    			echo '<img class="add-cat-hover" src="' . WC18_URI . 'images/add-cat-hover.png">';
				echo '</div>';				

			} else {

    			echo '<div class="remove-cat-container">';
	    			echo '<img class="remove-cat" src="' . WC18_URI . 'images/remove-cat.png">';
	    			echo '<img class="remove-cat-hover" src="' . WC18_URI . 'images/remove-cat-hover.png">';
    			echo '</div>';

			}

		echo '</li>';
	}


	/**
	 * Aggiunge una nuova categoria per la verifica in fase di checkout
	 */
	public function add_cat_callback() {

		$number = isset($_POST['number']) ? sanitize_text_field($_POST['number']) : '';
		$exclude_beni = isset($_POST['exclude-beni']) ? sanitize_text_field($_POST['exclude-beni']) : '';
		$exclude_cats = isset($_POST['exclude-cats']) ? sanitize_text_field($_POST['exclude-cats']) : '';

		if($number) {
			$this->setup_cat($number, null, $exclude_beni, $exclude_cats);
		}

		exit;
	}


	/**
	 * Pulsante call to action Premium
	 */
	public function get_go_premium() {
		$output = '<span class="label label-warning premium">';
			$output .= '<a href="https://www.ilghera.com/product/woocommerce-18app-premium" target="_blank">Premium</a>';
		$output .= '</span>';

		return $output;
	}


	/**
	 * Attivazione certificato
	 */
	public function wc18_cert_activation() {
	    $soapClient = new wc18_soap_client('11aa22bb', '');

	    try {

		    $operation = $soapClient->check(1);
		    return 'ok';

		} catch(Exception $e) {

            $notice = isset($e->detail->FaultVoucher->exceptionMessage) ? $e->detail->FaultVoucher->exceptionMessage : $e->faultstring;
		    error_log('Error wc18_cert_activation: ' . print_r($e, true));
		    return $notice;

        } 
	}


	/**
	 * Pagina opzioni plugin
	 */
	public function wc18_settings() {

		/*Recupero le opzioni salvate nel db*/
		$passphrase = base64_decode(get_option('wc18-password'));
		$categories = get_option('wc18-categories');
		$tot_cats = $categories ? count($categories) : 0;
		$wc18_image = get_option('wc18-image');

		echo '<div class="wrap">';
	    	echo '<div class="wrap-left">';
			    echo '<h1>WooCommerce 18app - ' . esc_html(__('Impostazioni', 'wc18')) . '</h1>';

				/*Tabs*/
				echo '<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"></div>';
				echo '<h2 id="wc18-admin-menu" class="nav-tab-wrapper woo-nav-tab-wrapper">';
					echo '<a href="#" data-link="wc18-certificate" class="nav-tab nav-tab-active" onclick="return false;">' . esc_html(__('Certificato', 'wc18')) . '</a>';
					echo '<a href="#" data-link="wc18-options" class="nav-tab" onclick="return false;">' . esc_html(__('Opzioni', 'wc18')) . '</a>';
				echo '</h2>';

			    /*Certificate*/
			    echo '<div id="wc18-certificate" class="wc18-admin" style="display: block;">';

		    		/*Carica certificato .pem*/
		    		echo '<h3>' . esc_html(__('Carica il tuo certificato', 'wc18')) . '</h3>';
	    			echo '<p class="description">' . esc_html(__('Se sei già in posseso di un certificato non devi fare altro che caricarlo, nient\'altro.', 'wc18')) . '</p>';

				    echo '<form name="wc18-upload-certificate" class="wc18-upload-certificate one-of" method="post" enctype="multipart/form-data" action="">';
				    	echo '<table class="form-table wc18-table">';

				    		/*Carica certificato*/
				    		echo '<tr>';
				    			echo '<th scope="row">' . esc_html(__('Carica certificato', 'wc18')) . '</th>';
				    			echo '<td>';
				    				if($file = self::get_the_file('.pem')) {

				    					$activation = $this->wc18_cert_activation();

				    					if($activation === 'ok') {

					    					echo '<span class="cert-loaded">' . esc_html(basename($file)) . '</span>';
					    					echo '<a class="button delete delete-certificate">' . esc_html(__('Elimina'), 'wc18') . '</a>';
					    					echo '<p class="description">' . esc_html(__('File caricato e attivato correttamente.', 'wc18')) . '</p>';

					    					update_option('wc18-cert-activation', 1);

				    					} else {

					    					echo '<span class="cert-loaded error">' . esc_html(basename($file)) . '</span>';
					    					echo '<a class="button delete delete-certificate">' . esc_html(__('Elimina'), 'wc18') . '</a>';
					    					echo '<p class="description">' . sprintf(esc_html(__('L\'attivazione del certificato ha restituito il seguente errore: %s', 'wc18')), $activation) . '</p>';

					    					delete_option('wc18-cert-activation');

				    					}

				    				} else {

						    			echo '<input type="file" accept=".pem" name="wc18-certificate" class="wc18-certificate">';
						    			echo '<p class="description">' . esc_html(__('Carica il certificato (.pem) necessario alla connessione con 18app', 'wc18')) . '</p>';
			
				    				}
				    			echo '</td>';
				    		echo '</tr>';

				    		/*Password utilizzata per la creazione del certificato*/
				    		echo '<tr>';
				    			echo '<th scope="row">' . esc_html(__('Password', 'wc18')) . '</th>';
				    			echo '<td>';
			    					echo '<input type="password" name="wc18-password" placeholder="**********" value="' . $passphrase . '" required>';
						    			echo '<p class="description">' . esc_html(__('La password utilizzata per la generazione del certificato', 'wc18')) . '</p>';	

							    	wp_nonce_field('wc18-upload-certificate', 'wc18-certificate-nonce');
							    	echo '<input type="hidden" name="wc18-certificate-hidden" value="1">';
							    	echo '<input type="submit" class="button-primary wc18-button" value="' . esc_html('Salva certificato', 'wc18') . '">';
				    			echo '</td>';
				    		echo '</tr>';

			    		echo '</table>';
			    	echo '</form>';
	
				    /*Se il certificato non è presente vengono mostrati gli strumentui per generarlo*/
		    		if(!self::get_the_file('.pem')) {
				
			    		/*Genera richiesta certificato .der*/
			    		echo '<h3>' . esc_html(__('Richiedi un certificato', 'wc18')) . $this->get_go_premium() . '</h3>';
		    			echo '<p class="description">' . esc_html(__('Con questo strumento puoi generare un file .der necessario per richiedere il tuo certificato su 18app.', 'wc18')) . '</p>';

	    				echo '<form id="generate-certificate-request" method="post" class="one-of" enctype="multipart/form-data" action="">';
							echo '<table class="form-table wc18-table">';
					    		echo '<tr>';
					    			echo '<th scope="row">' . esc_html(__('Stato', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="countryName" placeholder="IT" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Provincia', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="stateOrProvinceName" placeholder="Es. Milano" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Località', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="localityName" placeholder="Es. Legnano" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Nome azienda', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="organizationName" placeholder="Es. Taldeitali srl" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Reparto azienda', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="organizationalUnitName" placeholder="Es. Vendite" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Nome', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="text" name="commonName" placeholder="Es. Franco Bianchi" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Email', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="email" name="emailAddress" placeholder="Es. franco.bianchi@taldeitali.it" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row">' . esc_html(__('Password', 'wc18')) . '</th>';
					    			echo '<td>';
				    					echo '<input type="password" name="wc18-password" placeholder="**********" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    			echo '<th scope="row"></th>';
					    			echo '<td>';
					    			echo '<input type="hidden" name="generate-der-hidden" value="1">';
				    				echo '<input type="submit" name="generate-der" class="button-primary wc18-button generate-der" value="' . __('Scarica file .der', 'wc18') . '" disabled>';
					    			echo '</td>';
					    		echo '</tr>';

				    		echo '</table>';
	    				echo '</form>';


			    		/*Genera certificato .pem*/
			    		echo '<h3>' . esc_html(__('Crea il tuo certificato', 'wc18')) . $this->get_go_premium() . '</h3>';
		    			echo '<p class="description">' . esc_html(__('Con questo ultimo passaggio, potrai iniziare a ricevere pagamenti attraverso buoni 18app.', 'wc18')) . '</p>';

						echo '<form name="wc18-generate-certificate" class="wc18-generate-certificate" method="post" enctype="multipart/form-data" action="">';
					    	echo '<table class="form-table wc18-table">';

					    		/*Carica certificato*/
					    		echo '<tr>';
					    			echo '<th scope="row">' . esc_html(__('Genera certificato', 'wc18')) . '</th>';
					    			echo '<td>';
					    				
						    			echo '<input type="file" accept=".cer" name="wc18-cert" class="wc18-cert" disabled>';
						    			echo '<p class="description">' . esc_html(__('Carica il file .cer ottenuto da 18app per procedere', 'wc18')) . '</p>';
								    	
								    	wp_nonce_field('wc18-generate-certificate', 'wc18-gen-certificate-nonce');
								    	echo '<input type="hidden" name="wc18-gen-certificate-hidden" value="1">';
								    	echo '<input type="submit" class="button-primary wc18-button" value="' . esc_html('Genera certificato', 'wc18') . '" disabled>';

					    			echo '</td>';
					    		echo '</tr>';

				    		echo '</table>';
				    	echo '</form>';			

					}

			    echo '</div>';


			    /*Options*/
			    echo '<div id="wc18-options" class="wc18-admin">';

				    echo '<form name="wc18-options" class="wc18-form wc18-options" method="post" enctype="multipart/form-data" action="">';
				    	echo '<table class="form-table">';
				    		
				    		echo '<tr>';
				    			echo '<th scope="row">' . esc_html(__('Categorie', 'wc18')) . '</th>';
				    			echo '<td>';

				    				echo '<ul  class="categories-container">';

				    					if($categories) {
				    						for ($i=1; $i <= $tot_cats ; $i++) { 
			    								$this->setup_cat($i, $categories[$i - 1]);
				    						}
				    					} else {
		    								$this->setup_cat(1);
				    					}

						    		echo '</ul>';
						    		echo '<input type="hidden" name="wc18-tot-cats" class="wc18-tot-cats" value="' . ($categories ? count($categories) : '') . '">';
					    			echo '<p class="description">' . esc_html(__('Seleziona le categorie di prodotti corrispondenti ai beni acquistabili.', 'wc18')) . '</p>';
				    			echo '</td>';
				    		echo '</tr>';

				    		echo '<tr>';
				    			echo '<th scope="row">' . esc_html(__('Utilizzo immagine ', 'wc18')) . '</th>';
			    				echo '<td>';
					    			echo '<label>';
					    			echo '<input type="checkbox" name="wc18-image" value="1"' . ($wc18_image === '1' ? ' checked="checked"' : '') . '>';
					    			echo esc_html(__('Mostra il logo 18app nella pagine di checkout.', 'wc18'));
					    			echo '</label>';
			    				echo '</td>';
				    		echo '</tr>';

				    	echo '</table>';
				    	wp_nonce_field('wc18-save-settings', 'wc18-settings-nonce');
				    	echo '<input type="hidden" name="wc18-settings-hidden" value="1">';
				    	echo '<input type="submit" class="button-primary" value="' . esc_html('Salva impostazioni', 'wc18') . '">';
				    echo '</form>';
			    echo '</div>';

		    echo '</div>';

	    	echo '<div class="wrap-right">';
				echo '<iframe width="300" height="1300" scrolling="no" src="http://www.ilghera.com/images/wc18-iframe.html"></iframe>';

	    	echo '</div>';
			echo '<div class="clear"></div>';

	    echo '</div>';

	}


	/**
	 * Mostra un mesaggio d'errore nel caso in cui il certificato non isa valido
	 * @return string
	 */
	public function not_valid_certificate() {
		?>
		<div class="notice notice-error">
	        <p><?php esc_html_e(__( 'ATTENZIONE! Il file caricato non sembra essere un certificato valido.', 'wc18' )); ?></p>
	    </div>
		<?php
	}


	/**
	 * Salvataggio delle impostazioni dell'utente
	 */
	public function wc18_save_settings() {

		if(isset($_POST['wc18-certificate-hidden']) && wp_verify_nonce($_POST['wc18-certificate-nonce'], 'wc18-upload-certificate')) {
			
			/*Carica certificato*/
			if(isset($_FILES['wc18-certificate'])) {
				$info = pathinfo($_FILES['wc18-certificate']['name']);
				$name = sanitize_file_name($info['basename']);
				if($info) {
					if($info['extension'] === 'pem') {
						move_uploaded_file($_FILES['wc18-certificate']['tmp_name'], WC18_PRIVATE . $name);	
					} else {
						add_action('admin_notices', array($this, 'not_valid_certificate'));
					}					
				}
			}

			/*Password*/
            $wc18_password = isset($_POST['wc18-password']) ? sanitize_text_field($_POST['wc18-password']) : '';

            /*Salvo passw nel db*/
            if($wc18_password) {
            	update_option('wc18-password', base64_encode($wc18_password));
            }
		}

		if(isset($_POST['wc18-settings-hidden']) && wp_verify_nonce($_POST['wc18-settings-nonce'], 'wc18-save-settings')) {

			/*Impostazioni categorie per il controllo in fase di checkout*/
			if(isset($_POST['wc18-tot-cats'])) {
				$tot = sanitize_text_field($_POST['wc18-tot-cats']);

				$wc18_categories = array();

				for ($i=1; $i <= $tot ; $i++) { 
					$bene = isset($_POST['wc18-beni-' . $i]) ? sanitize_text_field($_POST['wc18-beni-' . $i]) : '';
					$cat = isset($_POST['wc18-categories-' . $i]) ? sanitize_text_field($_POST['wc18-categories-' . $i]) : '';

					if($bene && $cat) {
						$wc18_categories[] = array($bene => $cat);
					}
				}

				update_option('wc18-categories', $wc18_categories);
			}

			/*Immagine in pagina di checkout*/
			$wc18_image = isset($_POST['wc18-image']) ? sanitize_text_field($_POST['wc18-image']) : '';															
			update_option('wc18-image', $wc18_image);
		}
	}

}
new wc18_admin();