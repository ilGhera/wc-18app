<?php
/**
 * Plugin name: WooCommerce 18app
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
function wc18_activation() {

	/*Is WooCommerce activated?*/
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
	require WC18_INCLUDES . 'class-18app-gateway.php';
	require WC18_INCLUDES . 'class-18app-soap-client.php';
	require WC18_INCLUDES . 'class-18app-admin.php';
	require WC18_INCLUDES . 'class-18app.php';

	/**
	 * Script e folgi di stile front-end
	 *
	 * @return void
	 */
	function wc18_load_scripts() {
		wp_enqueue_style( 'wc18-style', WC18_URI . 'css/wc-18app.css', array(), WC18_VERSION );
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
			wp_enqueue_script( 'tzcheckbox', WC18_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ), WC18_VERSION, false );
			wp_enqueue_script( 'tzcheckbox-script', WC18_URI . 'js/tzCheckbox/js/script.js', array( 'jquery' ), WC18_VERSION, false );

		}

	}

	/*Script e folgi di stile*/
	add_action( 'wp_enqueue_scripts', 'wc18_load_scripts' );
	add_action( 'admin_enqueue_scripts', 'wc18_load_admin_scripts' );

}
add_action( 'plugins_loaded', 'wc18_activation', 100 );

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

