<?php
/**
 * Pagina opzioni e gestione certificati
 *
 * @author ilGhera
 * @package wc-18app/includes
 *
 * @since 1.4.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC18_Admin class
 *
 * @since 1.4.1
 */
class WC18_Admin {

	/**
	 * The sandbox option
	 *
	 * @var bool
	 */
	private $sandbox;

	/**
	 * The constructor
	 *
	 * @return void
	 */
	public function __construct() {

		$this->sandbox = get_option( 'wc18-sandbox' );

		add_action( 'admin_init', array( $this, 'wc18_save_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_options_page' ) );
		add_action( 'wp_ajax_wc18-delete-certificate', array( $this, 'delete_certificate_callback' ), 1 );
		add_action( 'wp_ajax_wc18-add-cat', array( $this, 'add_cat_callback' ) );
		add_action( 'wp_ajax_wc18-sandbox', array( $this, 'sandbox_callback' ) );
	}

	/**
	 * Registra la pagina opzioni del plugin
	 *
	 * @return void
	 */
	public function register_options_page() {

		add_submenu_page( 'woocommerce', __( 'WooCommerce 18app - Impostazioni', 'wc18' ), __( 'WC 18app', 'wc18' ), 'manage_options', 'wc18-settings', array( $this, 'wc18_settings' ) );
	}

	/**
	 * Verifica la presenza di un file per estenzione
	 *
	 * @param string $ext l,estensione del file da cercare.
	 *
	 * @return string l'url file
	 */
	public static function get_the_file( $ext ) {
		$files = array();
		foreach ( glob( WC18_PRIVATE . '*' . $ext ) as $file ) {
			$files[] = $file;
		}

		$output = empty( $files ) ? false : $files[0];

		return $output;

	}

	/**
	 * Cancella il certificato
	 *
	 * @return void
	 */
	public function delete_certificate_callback() {

		if ( isset( $_POST['wc18-delete'], $_POST['delete-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['delete-nonce'] ) ), 'wc18-del-cert-nonce' ) ) {

			$cert = isset( $_POST['cert'] ) ? sanitize_text_field( wp_unslash( $_POST['cert'] ) ) : '';

			if ( $cert ) {

				unlink( WC18_PRIVATE . $cert );

			}
		}

		exit;

	}

	/**
	 * Restituisce il nome esatto del bene 18app partendo dallo slug
	 *
	 * @param  array  $beni       l'elenco dei beni di 18app.
	 * @param  string $bene_slug lo slug del bene.
	 *
	 * @return string
	 */
	public function get_bene_lable( $beni, $bene_slug ) {

		foreach ( $beni as $bene ) {

			if ( sanitize_title( $bene ) === $bene_slug ) {

				return $bene;

			}
		}

	}

	/**
	 * Categoria per la verifica in fase di checkout
	 *
	 * @param  int   $n             il numero dell'elemento aggiunto.
	 * @param  array $data          bene e categoria come chiave e velore.
	 * @param  array $exclude_beni  buoni già abbinati a categorie WC (al momento non utilizzato).
	 *
	 * @return mixed
	 */
	public function setup_cat( $n, $data = null, $exclude_beni = null ) {

		echo '<li class="setup-cat cat-' . esc_attr( $n ) . '">';

			/*L'elenco dei beni dei vari ambiti previsti dalla piattaforma*/
			$beni_index = array(
				'Cinema',
				'Concerti',
				'Eventi culturali',
				'Libri',
				'Quotidiani e periodici',
				'Musei, monumenti e parchi',
				'Teatro e danza',
				'Musica registrata',
				'Corsi di musica, di teatro o di lingua straniera',
			);

			$beni       = array_map( 'sanitize_title', $beni_index );
			$terms      = get_terms( 'product_cat' );
			$bene_value = is_array( $data ) ? key( $data ) : '';
			$term_value = $bene_value ? $data[ $bene_value ] : '';

			echo '<select name="wc18-beni-' . esc_attr( $n ) . '" class="wc18-field beni">';
				echo '<option value="">Bene 18app</option>';

			foreach ( $beni as $bene ) {

				echo '<option value="' . esc_attr( $bene ) . '"' . ( $bene === $bene_value ? ' selected="selected"' : '' ) . '>' . esc_html( $this->get_bene_lable( $beni_index, $bene ) ) . '</option>';

			}
			echo '</select>';

			echo '<select name="wc18-categories-' . esc_attr( $n ) . '" class="wc18-field categories">';
				echo '<option value="">Categoria WooCommerce</option>';

			foreach ( $terms as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) . '"' . ( intval( $term_value ) === $term->term_id ? ' selected="selected"' : '' ) . '>' . esc_html( $term->name ) . '</option>';
			}
			echo '</select>';

			if ( 1 === intval( $n ) ) {

				echo '<div class="add-cat-container">';
					echo '<img class="add-cat" src="' . esc_url( WC18_URI . 'images/add-cat.png' ) . '">';
					echo '<img class="add-cat-hover wc18" src="' . esc_url( WC18_URI . 'images/add-cat-hover.png' ) . '">';
				echo '</div>';

			} else {

				echo '<div class="remove-cat-container">';
					echo '<img class="remove-cat" src="' . esc_url( WC18_URI . 'images/remove-cat.png' ) . '">';
					echo '<img class="remove-cat-hover" src="' . esc_url( WC18_URI . 'images/remove-cat-hover.png' ) . '">';
				echo '</div>';

			}

			echo '</li>';
	}

	/**
	 * Aggiunge una nuova categoria per la verifica in fase di checkout
	 *
	 * @return void
	 */
	public function add_cat_callback() {

		if ( isset( $_POST['add-cat-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['add-cat-nonce'] ) ), 'wc18-add-cat-nonce' ) ) {

			$number       = isset( $_POST['number'] ) ? sanitize_text_field( wp_unslash( $_POST['number'] ) ) : '';
			$exclude_beni = isset( $_POST['exclude-beni'] ) ? sanitize_text_field( wp_unslash( $_POST['exclude-beni'] ) ) : '';

			if ( $number ) {

				$this->setup_cat( $number, null, $exclude_beni );

			}
		}

		exit;
	}

	/**
	 * Pulsante call to action Premium
	 *
	 * @param bool $no_margin aggiunge la classe CSS con true.
	 *
	 * @return string
	 */
	public function get_go_premium( $no_margin = false ) {

		$output      = '<span class="label label-warning premium' . ( $no_margin ? ' no-margin' : null ) . '">';
			$output .= '<a href="https://www.ilghera.com/product/woocommerce-18app-premium" target="_blank">Premium</a>';
		$output     .= '</span>';

		return $output;

	}

	/**
	 * Attivazione certificato
	 *
	 * @return string
	 */
	public function wc18_cert_activation() {

		$soap_client = new WC18_Soap_Client( '11aa22bb', '' );

		try {

			$operation = $soap_client->check( 1 );
			return 'ok';

		} catch ( Exception $e ) {

			$notice = isset( $e->detail->FaultVoucher->exceptionMessage ) ? $e->detail->FaultVoucher->exceptionMessage : $e->faultstring;
			error_log( 'Error wc18_cert_activation: ' . print_r( $e, true ) );

			return $notice;

		}
	}

	/**
	 * Funzionalita Sandbox
	 *
	 * @return void
	 */
	public function sandbox_callback() {

		if ( isset( $_POST['sandbox'], $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wc18-sandbox' ) ) {

			$this->sandbox = sanitize_text_field( wp_unslash( $_POST['sandbox'] ) );

			update_option( 'wc18-sandbox', $this->sandbox );
			update_option( 'wc18-cert-activation', $this->sandbox );

		}

		exit();

	}

	/**
	 * Pagina opzioni plugin
	 *
	 * @return void
	 */
	public function wc18_settings() {

		/*Recupero le opzioni salvate nel db*/
		$passphrase = base64_decode( get_option( 'wc18-password' ) );
		$categories = get_option( 'wc18-categories' );
		$tot_cats   = $categories ? count( $categories ) : 0;
		$wc18_image = get_option( 'wc18-image' );

		echo '<div class="wrap">';
			echo '<div class="wrap-left">';
				echo '<h1>WooCommerce 18app - ' . esc_html( __( 'Impostazioni', 'wc18' ) ) . '</h1>';

				/*Tabs*/
				echo '<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"></div>';
				echo '<h2 id="wc18-admin-menu" class="nav-tab-wrapper woo-nav-tab-wrapper">';
					echo '<a href="#" data-link="wc18-certificate" class="nav-tab nav-tab-active" onclick="return false;">' . esc_html( __( 'Certificato', 'wc18' ) ) . '</a>';
					echo '<a href="#" data-link="wc18-options" class="nav-tab" onclick="return false;">' . esc_html__( 'Opzioni', 'wc18' ) . '</a>';
				echo '</h2>';

				/*Certificate*/
				echo '<div id="wc18-certificate" class="wc18-admin" style="display: block;">';

					/*Carica certificato .pem*/
					echo '<h3>' . esc_html__( 'Carica il tuo certificato', 'wc18' ) . '</h3>';
					echo '<p class="description">' . esc_html__( 'Se sei già in posseso di un certificato non devi fare altro che caricarlo con relativa password, nient\'altro.', 'wc18' ) . '</p>';

					echo '<form name="wc18-upload-certificate" class="wc18-upload-certificate one-of" method="post" enctype="multipart/form-data" action="">';
						echo '<table class="form-table wc18-table">';

							/*Carica certificato*/
							echo '<tr>';
								echo '<th scope="row">' . esc_html__( 'Carica certificato', 'wc18' ) . '</th>';
								echo '<td>';
		if ( $file = self::get_the_file( '.pem' ) ) {

			$activation = $this->wc18_cert_activation();

			if ( 'ok' === $activation ) {

				echo '<span class="cert-loaded">' . esc_html( basename( $file ) ) . '</span>';
				echo '<a class="button delete wc18-delete-certificate">' . esc_html__( 'Elimina', 'wc18' ) . '</a>';
				echo '<p class="description">' . esc_html__( 'File caricato e attivato correttamente.', 'wc18' ) . '</p>';

				update_option( 'wc18-cert-activation', 1 );

			} else {

				echo '<span class="cert-loaded error">' . esc_html( basename( $file ) ) . '</span>';
				echo '<a class="button delete wc18-delete-certificate">' . esc_html__( 'Elimina', 'wc18' ) . '</a>';

				/* Translators: the error message */
				echo '<p class="description">' . sprintf( esc_html__( 'L\'attivazione del certificato ha restituito il seguente errore: %s', 'wc18' ), esc_html( $activation ) ) . '</p>';

				delete_option( 'wc18-cert-activation' );

			}
		} else {

			echo '<input type="file" accept=".pem" name="wc18-certificate" class="wc18-certificate">';
			echo '<p class="description">' . esc_html__( 'Carica il certificato (.pem) necessario alla connessione con 18app', 'wc18' ) . '</p>';

		}

								echo '</td>';
							echo '</tr>';

							/*Password utilizzata per la creazione del certificato*/
							echo '<tr>';
								echo '<th scope="row">' . esc_html__( 'Password', 'wc18' ) . '</th>';
								echo '<td>';
									echo '<input type="password" name="wc18-password" placeholder="**********" value="' . esc_attr( $passphrase ) . '" required>';
									echo '<p class="description">' . esc_html__( 'La password utilizzata per la generazione del certificato', 'wc18' ) . '</p>';

									wp_nonce_field( 'wc18-upload-certificate', 'wc18-certificate-nonce' );

									echo '<input type="hidden" name="wc18-certificate-hidden" value="1">';
									echo '<input type="submit" class="button-primary wc18-button" value="' . esc_html__( 'Salva certificato', 'wc18' ) . '">';
								echo '</td>';
							echo '</tr>';

						echo '</table>';
					echo '</form>';

		/*Se il certificato non è presente vengono mostrati gli strumentui per generarlo*/
		if ( ! self::get_the_file( '.pem' ) ) {

			/*Genera richiesta certificato .der*/
			echo '<h3>' . esc_html__( 'Richiedi un certificato', 'wc18' ) . wp_kses_post( $this->get_go_premium() ) . '</h3>';
			echo '<p class="description">' . esc_html__( 'Con questo strumento puoi generare un file .der necessario per richiedere il tuo certificato su 18app.', 'wc18' ) . '</p>';

			echo '<form id="generate-certificate-request" method="post" class="one-of" enctype="multipart/form-data" action="">';
				echo '<table class="form-table wc18-table">';
					echo '<tr>';
						echo '<th scope="row">' . esc_html__( 'Stato', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="countryName" placeholder="IT" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Provincia', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="stateOrProvinceName" placeholder="Es. Milano" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Località', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="localityName" placeholder="Es. Legnano" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Nome azienda', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="organizationName" placeholder="Es. Taldeitali srl" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Reparto azienda', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="organizationalUnitName" placeholder="Es. Vendite" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Nome', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="text" name="commonName" placeholder="Es. Franco Bianchi" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Email', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="email" name="emailAddress" placeholder="Es. franco.bianchi@taldeitali.it" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row">' . esc_html__( 'Password', 'wc18' ) . '</th>';
						echo '<td>';
							echo '<input type="password" name="wc18-password" placeholder="**********" disabled>';
						echo '</td>';
					echo '</tr>';

					echo '<th scope="row"></th>';
						echo '<td>';
						wp_nonce_field( 'wc18-generate-der', 'wc18-generate-der-nonce' );
						echo '<input type="hidden" name="wc18-generate-der-hidden" value="1">';
						echo '<input type="submit" name="generate-der" class="button-primary wc18-button generate-der" value="' . esc_attr__( 'Scarica file .der', 'wc18' ) . '" disabled>';
						echo '</td>';
					echo '</tr>';

				echo '</table>';
			echo '</form>';

			/*Genera certificato .pem*/
			echo '<h3>' . esc_html( __( 'Crea il tuo certificato', 'wc18' ) ) . wp_kses_post( $this->get_go_premium() ) . '</h3>';
			echo '<p class="description">' . esc_html( __( 'Con questo ultimo passaggio, potrai iniziare a ricevere pagamenti attraverso buoni 18app.', 'wc18' ) ) . '</p>';

			echo '<form name="wc18-generate-certificate" class="wc18-generate-certificate one-of" method="post" enctype="multipart/form-data" action="">';
				echo '<table class="form-table wc18-table">';

					/*Carica certificato*/
					echo '<tr>';
						echo '<th scope="row">' . esc_html( __( 'Genera certificato', 'wc18' ) ) . '</th>';
						echo '<td>';

							echo '<input type="file" accept=".cer" name="wc18-cert" class="wc18-cert" disabled>';
							echo '<p class="description">' . esc_html( __( 'Carica il file .cer ottenuto da 18app per procedere', 'wc18' ) ) . '</p>';

							echo '<input type="hidden" name="wc18-gen-certificate-hidden" value="1">';
							echo '<input type="submit" class="button-primary wc18-button" value="' . esc_html__( 'Genera certificato', 'wc18' ) . '" disabled>';

				echo '</table>';
			echo '</form>';

		}

				echo '</div>';

				/*Modalità Sandbox*/
				echo '<div id="wc18-sandbox-option" class="wc18-admin" style="display: block;">';
					echo '<h3>' . esc_html( __( 'Modalità Sandbox', 'wc18' ) ) . '</h3>';
				echo '<p class="description">';
					/* Translators: the email address */
					printf( wp_kses_post( __( 'Attiva questa funzionalità per testare buoni 18app in un ambiente di prova.<br>Richiedi i buoni test scrivendo a <a href="%s">numeroverde@cultura.gov.it</a>', 'wc18' ) ), 'mailto:numeroverde@cultura.gov.it' );
				echo '</p>';

					echo '<form name="wc18-sandbox" class="wc18-sandbox" method="post" enctype="multipart/form-data" action="">';
						echo '<table class="form-table wc18-table">';

							/*Carica certificato*/
							echo '<tr>';
								echo '<th scope="row">' . esc_html( __( 'Sandbox', 'wc18' ) ) . '</th>';
								echo '<td class="wc18-sandbox-field">';
									echo '<input type="checkbox" name="wc18-sandbox" class="wc18-sandbox"' . ( $this->sandbox ? ' checked="checked"' : null ) . '>';
									echo '<p class="description">' . esc_html( __( 'Attiva modalità Sandbox', 'wc18' ) ) . '</p>';

									wp_nonce_field( 'wc18-sandbox', 'wc18-sandbox-nonce' );

									echo '<input type="hidden" name="wc18-sandbox-hidden" value="1">';

								echo '</td>';
							echo '</tr>';

						echo '</table>';
					echo '</form>';
				echo '</div>';

				/*Options*/
				echo '<div id="wc18-options" class="wc18-admin">';

					echo '<form name="wc18-options" class="wc18-form wc18-options" method="post" enctype="multipart/form-data" action="">';
						echo '<table class="form-table">';

							echo '<tr>';
								echo '<th scope="row">' . esc_html__( 'Categorie', 'wc18' ) . '</th>';
								echo '<td>';

									echo '<ul  class="categories-container">';

		if ( $categories ) {

			for ( $i = 1; $i <= $tot_cats; $i++ ) {

				$this->setup_cat( $i, $categories[ $i - 1 ] );

			}
		} else {

			$this->setup_cat( 1 );

		}

									echo '</ul>';
									echo '<input type="hidden" name="wc18-tot-cats" class="wc18-tot-cats" value="' . ( is_array( $categories ) ? esc_attr( count( $categories ) ) : 1 ) . '">';
									echo '<p class="description">' . esc_html__( 'Seleziona le categorie di prodotti corrispondenti ai beni acquistabili.', 'wc18' ) . '</p>';
								echo '</td>';
							echo '</tr>';

							echo '<tr>';
								echo '<th scope="row">' . esc_html__( 'Utilizzo immagine', 'wc18' ) . '</th>';
								echo '<td>';
									echo '<input type="checkbox" name="wc18-image" value="1"' . ( 1 === intval( $wc18_image ) ? ' checked="checked"' : '' ) . '>';
									echo '<p class="description">' . wp_kses_post( __( 'Mostra il logo <i>18app</i> nella pagine di checkout.', 'wc18' ) ) . '</p>';
								echo '</td>';
							echo '</tr>';

							echo '<tr>';
								echo '<th scope="row">' . esc_html__( 'Controllo prodotti', 'wc18' ) . '</th>';
								echo '<td>';
										echo '<input type="checkbox" name="wc18-items-check" value="1" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'Mostra il metodo di pagamento solo se il/ i prodotti a carrello sono acquistabili con buoni <i>18app</i>.<br>Più prodotti dovranno prevedere l\'uso di buoni dello stesso ambito di utilizzo.', 'wc18' ) ) . '</p>';

									echo wp_kses_post( $this->get_go_premium( true ) );
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-orders-on-hold">';
								echo '<th scope="row">' . esc_html__( 'Ordini in sospeso', 'wc18' ) . '</th>';
								echo '<td>';
										echo '<input type="checkbox" name="wc18-orders-on-hold" value="1" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'I buoni 18app verranno validati con il completamento manuale degli ordini.', 'wc18' ) ) . '</p>';

									echo wp_kses_post( $this->get_go_premium( true ) );
								echo '</td>';

							echo '<tr class="wc18-exclude-shipping">';
								echo '<th scope="row">' . esc_html__( 'Spese di spedizione', 'wc18' ) . '</th>';
								echo '<td>';
										echo '<input type="checkbox" name="wc18-exclude-shipping" value="1" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'Escludi le spese di spedizione dal pagamento con 18app.', 'wc18' ) ) . '</p>';

									echo wp_kses_post( $this->get_go_premium( true ) );
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-coupon">';
								echo '<th scope="row">' . esc_html__( 'Conversione in coupon', 'wc18' ) . '</th>';
								echo '<td>';
									echo '<input type="checkbox" name="wc18-coupon" value="1" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'Nel caso in cui il buono <i>18app</i> inserito sia inferiore al totale a carrello, viene convertito in <i>Codice promozionale</i> ed applicato all\'ordine.', 'wc18' ) ) . '</p>';
									echo wp_kses_post( $this->get_go_premium( true ) );
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-email-order-received wc18-email-details">';
								echo '<th scope="row">' . esc_html__( 'Ordine ricevuto', 'wc18' ) . '</th>';
								echo '<td>';
									$default_order_received_message = __( 'L\'ordine verrà completato manualmente nei prossimi giorni e, contestualmente, verrà validato il buono 18app inserito. Riceverai una notifica email di conferma, grazie!', 'wc18' );
									echo '<textarea cols="6" rows="6" class="regular-text" name="wc18-email-order-received" placeholder="' . esc_html( $default_order_received_message ) . '" disabled></textarea>';
									echo '<p class="description">';
										echo wp_kses_post( __( 'Messaggio della mail inviata all\'utente al ricevimento dell\'ordine.', 'wc18' ) );
									echo '</p>';
									echo '<div class="wc18-divider"></div>';
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-email-subject wc18-email-details">';
								echo '<th scope="row">' . esc_html__( 'Oggetto email', 'wc18' ) . '</th>';
								echo '<td>';
										echo '<input type="text" class="regular-text" name="wc18-email-subject" placeholder="' . esc_attr__( 'Ordine fallito', 'wc18' ) . '" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'Oggetto della mail inviata all\'utente nel caso in cui la validazione del buono non sia andata a buon fine.', 'wc18' ) ) . '</p>';
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-email-heading wc18-email-details">';
								echo '<th scope="row">' . esc_html__( 'Intestazione email', 'wc18' ) . '</th>';
								echo '<td>';
										echo '<input type="text" class="regular-text" name="wc18-email-heading" placeholder="' . esc_attr__( 'Ordine fallito', 'wc18' ) . '" disabled>';
									echo '<p class="description">' . wp_kses_post( __( 'Intestazione della mail inviata all\'utente nel caso in cui la validazione del buono non sia andata a buon fine.', 'wc18' ) ) . '</p>';
								echo '</td>';
							echo '</tr>';

							echo '<tr class="wc18-email-order-failed wc18-email-details">';
								echo '<th scope="row">' . esc_html__( 'Ordine fallito', 'wc18' ) . '</th>';
								echo '<td>';
										$default_order_failed_message = __( 'La validazone del buono 18app ha restituito un errore e non è stato possibile completare l\'ordine, effettua il pagamento a <a href="[checkout-url]">questo indirizzo</a>.' );
										echo '<textarea cols="6" rows="6" class="regular-text" name="wc18-email-order-failed" placeholder="' . esc_html( $default_order_failed_message ) . '" disabled></textarea>';
										echo '<p class="description">';
											echo '<span class="shortcodes">';
												echo '<code>[checkout-url]</code>';
											echo '</span>';
											echo wp_kses_post( __( 'Messaggio della mail inviata all\'utente nel caso in cui la validazione del buono non sia andata a buon fine.', 'wc18' ) );
										echo '</p>';
								echo '</td>';
							echo '</tr>';

						echo '</table>';

						wp_nonce_field( 'wc18-save-settings', 'wc18-settings-nonce' );

						echo '<input type="hidden" name="wc18-settings-hidden" value="1">';
						echo '<input type="submit" class="button-primary" value="' . esc_html__( 'Salva impostazioni', 'wc18' ) . '">';
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
	 *
	 * @return void
	 */
	public function not_valid_certificate() {

		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'ATTENZIONE! Il file caricato non sembra essere un certificato valido.', 'wc18' ); ?></p>
		</div>
		<?php

	}

	/**
	 * Salvataggio delle impostazioni dell'utente
	 *
	 * @return void
	 */
	public function wc18_save_settings() {

		if ( isset( $_POST['wc18-certificate-hidden'], $_POST['wc18-certificate-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc18-certificate-nonce'] ) ), 'wc18-upload-certificate' ) ) {

			/*Carica certificato*/
			if ( isset( $_FILES['wc18-certificate'] ) ) {

				$info = isset( $_FILES['wc18-certificate']['name'] ) ? pathinfo( sanitize_text_field( wp_unslash( $_FILES['wc18-certificate']['name'] ) ) ) : null;
				$name = isset( $info['basename'] ) ? sanitize_file_name( $info['basename'] ) : null;

				if ( $info ) {

					if ( 'pem' === $info['extension'] ) {

						if ( isset( $_FILES['wc18-certificate']['tmp_name'] ) ) {

							$tmp_name = sanitize_text_field( wp_unslash( $_FILES['wc18-certificate']['tmp_name'] ) );
							move_uploaded_file( $tmp_name, WC18_PRIVATE . $name );

						}
					} else {

						add_action( 'admin_notices', array( $this, 'not_valid_certificate' ) );

					}
				}
			}

			/*Password*/
			$wc18_password = isset( $_POST['wc18-password'] ) ? sanitize_text_field( wp_unslash( $_POST['wc18-password'] ) ) : '';

			/*Salvo passw nel db*/
			if ( $wc18_password ) {

				update_option( 'wc18-password', base64_encode( $wc18_password ) );

			}
		}

		if ( isset( $_POST['wc18-settings-hidden'], $_POST['wc18-settings-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc18-settings-nonce'] ) ), 'wc18-save-settings' ) ) {

			/*Impostazioni categorie per il controllo in fase di checkout*/
			if ( isset( $_POST['wc18-tot-cats'] ) ) {

				$tot = sanitize_text_field( wp_unslash( $_POST['wc18-tot-cats'] ) );
				$tot = $tot ? $tot : 1;

				$wc18_categories = array();

				for ( $i = 1; $i <= $tot; $i++ ) {

					$bene = isset( $_POST[ 'wc18-beni-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wc18-beni-' . $i ] ) ) : '';
					$cat  = isset( $_POST[ 'wc18-categories-' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'wc18-categories-' . $i ] ) ) : '';

					if ( $bene && $cat ) {

						$wc18_categories[] = array( $bene => $cat );

					}
				}

				update_option( 'wc18-categories', $wc18_categories );
			}

			/*Immagine in pagina di checkout*/
			$wc18_image = isset( $_POST['wc18-image'] ) ? sanitize_text_field( wp_unslash( $_POST['wc18-image'] ) ) : '';
			update_option( 'wc18-image', $wc18_image );

		}
	}

}

new WC18_Admin();

