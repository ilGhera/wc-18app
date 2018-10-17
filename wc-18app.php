<?php
/**
 * Plugin name: WooCommerce 18app
 * Plugin URI: https://www.ilghera.com/product/wc-18app-premium/
 * Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano. 
 * Author: ilGhera
 * Version: 0.9.0
 * Author URI: https://ilghera.com 
 * Requires at least: 4.0
 * Tested up to: 4.9
 * WC tested up to: 3
 * Text Domain: wc18
 * Domain Path: /languages
 */


/**
 * Attivazione
 */
function wc18_activation() {

	/*WooCommerce è presente e attivo?*/
	if(!class_exists('WC_Payment_Gateway')) return;

	/*Definizione costanti*/
	define('WC18_DIR', plugin_dir_path(__FILE__));
	define('WC18_URI', plugin_dir_url(__FILE__));
	define('WC18_INCLUDES', WC18_DIR . 'includes/');
	define('WC18_PRIVATE', WC18_DIR . 'private/');
	define('WC18_PRIVATE_URI', WC18_URI . 'private/');

	/*Requires*/
	require WC18_INCLUDES . 'class-18app-gateway.php';
	require WC18_INCLUDES . 'class-18app-soap-client.php';
	require WC18_INCLUDES . 'class-18app-admin.php';

	/*Script e folgi di stile front-end*/
	function wc18_load_scripts() {
		wp_enqueue_style('wc18-style', WC18_URI . 'css/wc-18app.css');
	}

	/*Script e folgi di stile back-end*/
	function wc18_load_admin_scripts() {
		wp_enqueue_style('wc18-admin-style', WC18_URI . 'css/wc-18app-admin.css');
		wp_enqueue_script('wc18-admin-scripts', WC18_URI . 'js/wc-18app-admin.js');
	}

	/*Script e folgi di stile*/
	add_action('wp_enqueue_scripts', 'wc18_load_scripts');
	add_action('admin_enqueue_scripts', 'wc18_load_admin_scripts');
	
} 
add_action('plugins_loaded', 'wc18_activation', 100);