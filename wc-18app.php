<?php
/**
 * Plugin name: WooCommerce 18app - Premium
 * Plugin URI: https://www.ilghera.com/product/wc-18app-premium/
 * Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano.
 * Author: ilGhera
 *
 * Version: 1.4.0
 * Author URI: https://ilghera.com
 * Requires at least: 4.0
 * Tested up to: 6.4
 * WC tested up to: 8
 * Text Domain: wc18
 * Domain Path: /languages
 *
 * @package wc-18app
 */

/**
 * Attivazione
 */
function wc18_premium_activation() {

	/*Se presente, disattiva la versione free del plugin*/
	if ( function_exists( 'wc18_activation' ) ) {
		deactivate_plugins( 'wc-18app/wc-18app.php' );
		remove_action( 'plugins_loaded', 'wc18_activation' );
		wp_safe_redirect( admin_url( 'plugins.php?plugin_status=all&paged=1&s' ) );
	}

	/*WooCommerce è presente e attivo?*/
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/*Definizione costanti*/
	define( 'WC18_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WC18_URI', plugin_dir_url( __FILE__ ) );
	define( 'WC18_INCLUDES', WC18_DIR . 'includes/' );
	define( 'WC18_INCLUDES_URI', WC18_URI . 'includes/' );
	define( 'WC18_VERSION', '1.3.0' );

	/*Main directory di upload*/
	$wp_upload_dir = wp_upload_dir();

	/*Creo se necessario la cartella wc18-private*/
	if ( wp_mkdir_p( trailingslashit( $wp_upload_dir['basedir'] . '/wc18-private/files/backups' ) ) ) {
		define( 'WC18_PRIVATE', $wp_upload_dir['basedir'] . '/wc18-private/' );
		define( 'WC18_PRIVATE_URI', $wp_upload_dir['baseurl'] . '/wc18-private/' );
	}

	/*Requires*/
	require WC18_INCLUDES . 'class-wc18-gateway.php';
	require WC18_INCLUDES . 'class-wc18-soap-client.php';
	require WC18_INCLUDES . 'class-wc18-admin.php';
	require WC18_INCLUDES . 'class-wc18.php';
	require WC18_INCLUDES . 'ilghera-notice/class-ilghera-notice.php';

	/**
	 * Script e folgi di stile front-end
	 *
	 * @return void
	 */
	function wc18_load_scripts() {
		wp_enqueue_style( 'wc18-style', WC18_URI . 'css/wc-18app.css', array(), WC18_VERSION );
		wp_enqueue_script( 'wc18-scripts', WC18_URI . 'js/wc-18app.js', array(), WC18_VERSION, false );
		wp_localize_script(
			'wc18-scripts',
			'wc18Options',
			array(
				'ajaxURL'          => admin_url( 'admin-ajax.php' ),
				'couponConversion' => get_option( 'wc18-coupon' ),
			)
		);
	}

	/**
	 * Script e folgi di stile back-end
	 *
	 * @return void
	 */
	function wc18_load_admin_scripts() {

		$admin_page = get_current_screen();

		if ( isset( $admin_page->base ) && 'woocommerce_page_wc18-settings' === $admin_page->base ) {

			wp_enqueue_style( 'wc18-admin-style', WC18_URI . 'css/wc-18app-admin.css', array(), WC18_VERSION );
			wp_enqueue_script( 'wc18-admin-scripts', WC18_URI . 'js/wc-18app-admin.js', array(), WC18_VERSION, false );

			/* Nonce per l'eliminazione del certificato */
			$delete_nonce  = wp_create_nonce( 'wc18-del-cert-nonce' );
			$add_cat_nonce = wp_create_nonce( 'wc18-add-cat-nonce' );

			wp_localize_script(
				'wc18-admin-scripts',
				'wc18Data',
				array(
					'delCertNonce' => $delete_nonce,
					'addCatNonce'  => $add_cat_nonce,
				)
			);

			/*tzCheckBox*/
			wp_enqueue_style( 'tzcheckbox-style', WC18_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css', array(), WC18_VERSION );
			wp_enqueue_script( 'tzcheckbox', WC18_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ), WC18_VERSION, true );
			wp_enqueue_script( 'tzcheckbox-script', WC18_URI . 'js/tzCheckbox/js/script.js', array( 'jquery' ), WC18_VERSION, true );

		}

	}

	/*Script e folgi di stile*/
	add_action( 'wp_enqueue_scripts', 'wc18_load_scripts' );
	add_action( 'admin_enqueue_scripts', 'wc18_load_admin_scripts' );

}
add_action( 'plugins_loaded', 'wc18_premium_activation', 1 );


/**
 * HPOS compatibility
 */
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);


/**
 * Update checker
 */
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$wc18_update_checker = PucFactory::buildUpdateChecker(
	'https://www.ilghera.com/wp-update-server-2/?action=get_metadata&slug=wc-18app-premium',
	__FILE__,
	'wc-18app-premium'
);

$wc18_update_checker->addQueryArgFilter( 'wc18_secure_update_check' );

/**
 * PUC Secure update check
 *
 * @param array $query_args the parameters.
 *
 * @return array
 */
function wc18_secure_update_check( $query_args ) {
	$key = base64_encode( get_option( 'wc18-premium-key' ) );

	if ( $key ) {
		$query_args['premium-key'] = $key;
	}
	return $query_args;
}


/**
 * Avvisi utente in fase di aggiornaemnto plugin
 *
 * @param array  $plugin_data the plugin metadata.
 * @param object $response    metadata about the available plugin update.
 *
 * @return void
 */
function wc18_update_message( $plugin_data, $response ) {

	$message = null;
	$key     = get_option( 'wc18-premium-key' );

	$message = null;

	if ( ! $key ) {

		/* Translators: the admin URL */
		$message = sprintf( __( 'Per ricevere aggiornamenti devi inserire la tua <b>Premium Key</b> nelle <a href="%sadmin.php/?page=wc18-settings">impostazioni del plugin</a>. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18' ), admin_url() );

	} else {

		$decoded_key = explode( '|', base64_decode( $key ) );
		$bought_date = date( 'd-m-Y', strtotime( $decoded_key[1] ) );
		$limit       = strtotime( $bought_date . ' + 365 day' );
		$now         = strtotime( 'today' );

		if ( $limit < $now ) {
			$message = __( 'Sembra che la tua <strong>Premium Key</strong> sia scaduta. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18' );
		} elseif ( 3518 !== intval( $decoded_key[2] ) ) {
			$message = __( 'Sembra che la tua <strong>Premium Key</strong> non sia valida. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18' );
		}
	}

	$allowed = array(
		'b' => array(),
		'a' => array(
			'href'   => array(),
			'target' => array(),
		),
	);

	echo ( $message ) ? '<br><span class="wc18-alert">' . wp_kses( $message, $allowed ) . '</span>' : '';

}
add_action( 'in_plugin_update_message-wc-18app-premium/wc-18app.php', 'wc18_update_message', 10, 2 );

