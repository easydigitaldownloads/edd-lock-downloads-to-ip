<?php
/*
Plugin Name: Easy Digital Downloads - Lock Downloads to IP
Plugin URI: https://easydigitaldownloads.com/downloads/lock-downloads-to-ip/
Description: An extension for Easy Digital Downloads to lock file downloads to the IP address used to purchase the file
Author: Sandhills Development, LLC
Version: 1.0
Author URI: https://sandhillsdev.com
Text Domain: edd-iplock
Domain Path: languages
*/

class EDD_Lock_Downloads_To_IP {

	function __construct() {

		// internationalization
		add_action( 'init', array( $this, 'textdomain' ) );

		// Check the IP during file download
		add_action( 'edd_process_verified_download', array( $this, 'check_ip' ) );

	}


	/**
	 * Load the plugin text domain for internationalization
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

		// Load the translations
		load_plugin_textdomain( 'edd-iplock', false, $lang_dir );

	}


	/**
	 * Check the IP address during file download and display an error if it doesn't match the purchase records
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function check_ip( $download_id = 0, $email = 0 ) {

		$payment_key = isset( $_GET['download_key'] ) ? urldecode( $_GET['download_key'] ) : false;
		if ( empty( $payment_key ) ) {
			return;
		}

		$payment_id = edd_get_purchase_id_by_key( $payment_key );
		if ( empty( $payment_id ) ) {
			return;
		}

		$order = false;
		if ( function_exists( 'edd_get_order' ) ) {
			$order = edd_get_order( $payment_id );
		}
		if ( ! empty( $order ) ) {
			$ip = $order->ip;
		} else {
			$ip = edd_get_payment_meta( $payment_id, '_edd_payment_user_ip' );
		}

		if ( $ip !== edd_get_ip() ) {
			wp_die( esc_html__( 'You do not have permission to download this file because your IP address doesn\'t match our records.', 'edd-iplock' ), esc_html__( 'Error', 'edd-iplock' ) );
		}
	}

}
new EDD_Lock_Downloads_To_IP();
