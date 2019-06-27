<?php
/**
 * Plugin name: WooCommerce 18app - Premium
 * Plugin URI: https://www.ilghera.com/product/wc-18app-premium/
 * Description: Abilita in WooCommerce il pagamento con buoni 18app, il Bonus Cultura previsto dallo stato Italiano. 
 * Author: ilGhera
 * Version: 1.0.1
 * Author URI: https://ilghera.com 
 * Requires at least: 4.0
 * Tested up to: 5
 * WC tested up to: 3
 * Text Domain: wc18
 * Domain Path: /languages
 */


/**
 * Attivazione
 */
function wc18_premium_activation() {

	/*Se presente, disattiva la versione free del plugin*/
	if(function_exists('wc18_activation')) {
		deactivate_plugins('wc-18app/wc-18app.php');
	    remove_action( 'plugins_loaded', 'wc18_activation' );
	    wp_redirect(admin_url('plugins.php?plugin_status=all&paged=1&s'));
	}

	/*WooCommerce è presente e attivo?*/
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
add_action('plugins_loaded', 'wc18_premium_activation', 1);


/**
 * Update checker
 */
require( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php');
$wc18_update_checker = Puc_v4_Factory::buildUpdateChecker(
    'https://www.ilghera.com/wp-update-server-2/?action=get_metadata&slug=wc-18app-premium',
    __FILE__,
    'wc-18app-premium'
);

$wc18_update_checker->addQueryArgFilter('wc18_secure_update_check');
function wc18_secure_update_check($queryArgs) {
    $key = base64_encode( get_option('wc18-premium-key') );

    if($key) {
        $queryArgs['premium-key'] = $key;
    }
    return $queryArgs;
}


 /*
 * Avvisi utente in fase di aggiornaemnto plugin
 */
function wc18_update_message( $plugin_data, $response) {

	$message = null;
	$key = get_option('wc18-premium-key');

    $message = null;

	if(!$key) {

		$message = __('Per ricevere aggiornamenti devi inserire la tua <b>Premium Key</b> nelle <a href="' . admin_url() . 'admin.php/?page=wc18-settings">impostazioni del plugin</a>. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18');
	
	} else {
	
		$decoded_key = explode('|', base64_decode($key));
	    $bought_date = date( 'd-m-Y', strtotime($decoded_key[1]));
	    $limit = strtotime($bought_date . ' + 365 day');
	    $now = strtotime('today');

	    if($limit < $now) { 
	        $message = __('Sembra che la tua <strong>Premium Key</strong> sia scaduta. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18');
	    } elseif($decoded_key[2] != 3518) {
	    	$message = __('Sembra che la tua <strong>Premium Key</strong> non sia valida. Clicca <a href="https://www.ilghera.com/product/woocommerce-18app-premium/" target="_blank">qui</a> per maggiori informazioni.', 'wc18');
	    }

	}

	$allowed = array(
		'b' => array(),
		'a' => array(
			'href'   => array(),
			'target' => array()
		),
	);

	echo ($message) ? '<br><span class="wc18-alert">' . wp_kses($message, $allowed) . '</span>' : '';

}
add_action('in_plugin_update_message-wc-18app-premium/wc-18app.php', 'wc18_update_message', 10, 2);
