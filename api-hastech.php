<?php 
/*
 * Plugin Name:       API Development
 * Plugin URI:        https://example.com/plugins/api-hastech/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            HasTech
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       api-hastech
 * Domain Path:       /languages
 */

function api_load_textdomain(){
    load_plugin_textdomain( 'word-count', false, dirname(__FILE__)."/languages" );
}

add_action( "plugins_loaded", 'api_load_textdomain' );


// API Development

//get contact
function create_contact ( \WP_REST_Request $request ) {
    global $wpdb;

    $params = $request->get_query_params();
    $status = isset( $params['status'] ) ? sanitize_key( $params['status'] ) : 'all';

    $table = $wpdb->prefix . 'downloadio_contact';

    $contacts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table} WHERE status=%s", $status), ARRAY_A );

    if ( $wpdb->last_error ){
        return new \WP_REST_Response( array( 'success' -> false, 'message' -> $wpdb->last_error ), 500 );
    }

    return new \WP_REST_Response( array( 'success' -> true, 'contacts' -> $contacts ), 200 );
}

//verfiy permission
function verify_permission ( \WP_REST_Request $request ) {
    $nonce = $request->get_header('X-WP-Nonce');
    $verify = wp_verify_nonce( $nonce,'wp_rest' );

    return $verify ? true : false;
}

//add custom endpoint
function custom_rest_endpoint(){
    register_rest_route('downloadio/v1','/customer/create', array(
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => 'create_contact',
        'permission_callback' => 'verify_permission',
    ));
}

add_action('rest_api_init','custom_rest_endpoint');