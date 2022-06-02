<?php
/**
 * Plugin name: WooCommerce 18app
 * Plugin URI: https://www.ilghera.com/product/wc-18app-premium/
 * Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano. 
 * Author: ilGhera
 * Version: 1.2.0
 * Author URI: https://ilghera.com 
 * Requires at least: 4.0
 * Tested up to: 6.0
 * WC tested up to: 6
 * Text Domain: wc18
 * Domain Path: /languages
 */


/*Attivazione*/
function wc18_activation() {

	/*Is WooCommerce activated?*/
	if(!class_exists('WC_Payment_Gateway')) return;

	/*Definizione costanti*/
	define('WC18_DIR', plugin_dir_path(__FILE__));
	define('WC18_URI', plugin_dir_url(__FILE__));
	define('WC18_INCLUDES', WC18_DIR . 'includes/');
	define('WC18_INCLUDES_URI', WC18_URI . 'includes/');

	/*Main directory di upload*/
	$wp_upload_dir = wp_upload_dir();

	/*Creo se necessario la cartella wc18-private*/
	if( wp_mkdir_p( trailingslashit( $wp_upload_dir['basedir'] . '/wc18-private/files/backups' ) ) ) {
		define('WC18_PRIVATE', $wp_upload_dir['basedir'] . '/wc18-private/');
		define('WC18_PRIVATE_URI', $wp_upload_dir['baseurl'] . '/wc18-private/');
	}
	
	/*Requires*/
	require WC18_INCLUDES . 'class-18app-gateway.php';
	require WC18_INCLUDES . 'class-18app-soap-client.php';
	require WC18_INCLUDES . 'class-18app-admin.php';
	require WC18_INCLUDES . 'class-18app.php';

	/*Script e folgi di stile front-end*/
	function wc18_load_scripts() {
		wp_enqueue_style('wc18-style', WC18_URI . 'css/wc-18app.css');
	}

	/*Script e folgi di stile back-end*/
	function wc18_load_admin_scripts() {

        $admin_page = get_current_screen();

        if ( isset( $admin_page->base ) && 'woocommerce_page_wc18-settings' === $admin_page->base ) {

            wp_enqueue_style('wc18-admin-style', WC18_URI . 'css/wc-18app-admin.css');
            wp_enqueue_script('wc18-admin-scripts', WC18_URI . 'js/wc-18app-admin.js');

            /*tzCheckBox*/
            wp_enqueue_style( 'tzcheckbox-style', WC18_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.css' );
            wp_enqueue_script( 'tzcheckbox', WC18_URI . 'js/tzCheckbox/jquery.tzCheckbox/jquery.tzCheckbox.js', array( 'jquery' ) );
            wp_enqueue_script( 'tzcheckbox-script', WC18_URI . 'js/tzCheckbox/js/script.js', array( 'jquery' ) );

        }

	}

	/*Script e folgi di stile*/
	add_action('wp_enqueue_scripts', 'wc18_load_scripts');
	add_action('admin_enqueue_scripts', 'wc18_load_admin_scripts');

} 
add_action('plugins_loaded', 'wc18_activation', 100);

