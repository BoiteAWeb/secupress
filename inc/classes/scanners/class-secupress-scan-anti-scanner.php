<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Anti Scanner scan class.
 *
 * @package SecuPress
 * @subpackage SecuPress_Scan
 * @since 1.0
 */

class SecuPress_Scan_Anti_Scanner extends SecuPress_Scan implements iSecuPress_Scan {

	const VERSION = '1.0';

	/**
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	protected static $_instance;
	public    static $prio = 'high';


	protected static function init() {
		self::$type     = 'WordPress';
		self::$title    = __( 'Check if automated scanner can target your website.', 'secupress' );
		self::$more     = __( 'Automated scanner requires a triple page reload to be identical regarding contents. By giving them a different content for each request, it will not be possible for it to work properly.', 'secupress' );
		self::$more_fix = sprintf( __( 'The fix will activate the option <em>%1$s</em> from the module <a href="%2$s">%3$s</a>.', 'secupress' ), __( 'Block SQLi Scan Attempts', 'secupress' ), secupress_admin_url( 'modules', 'firewall#Block_SQLi_Scan_Attempts' ), __( 'Firewall', 'secupress' ) );
	}


	public static function get_messages( $message_id = null ) {
		$messages = array(
			// good
			0   => __( 'You are currently blocking <strong>automated scanning</strong>.', 'secupress' ),
			1   => __( 'Protection activated', 'secupress' ),
			// warning
			100 => __( 'Unable to determine status of your homepage.', 'secupress' ),
			// bad
			200 => __( 'Your website should block <strong>automated scanning</strong>.', 'secupress' ),
			// cantfix
			300 => __( 'I can not fix this, you have to do it yourself, have fun.', 'secupress' ),
		);

		if ( isset( $message_id ) ) {
			return isset( $messages[ $message_id ] ) ? $messages[ $message_id ] : __( 'Unknown message', 'secupress' );
		}

		return $messages;
	}


	public function scan() {

		// Scanners and Breach
		$hashes = array();

		for ( $i = 0 ; $i < 3 ; ++$i ) {
			$response = wp_remote_get( user_trailingslashit( home_url() ) . '?nocache=1', array( 'redirection' => 0 ) );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$hashes[] = md5( wp_remote_retrieve_body( $response ) );
			}
		}

		$hashes = array_values( array_flip( array_flip( $hashes ) ) );

		if ( isset( $hashes[2] ) ) { // = 3 different
			// good
			$this->add_message( 0 );

		} elseif ( ! isset( $hashes[0] ) ) { // = error during page request
			// warning
			$this->add_message( 100 );

		} else { // = we got 1 or 2 different hashes only.
			// bad
			$this->add_message( 200 );

		}

		return parent::scan();
	}


	public function fix() {

		// Activate.
		secupress_activate_submodule( 'firewall', 'bad-sqli-scan' );

		// good
		$this->add_fix_message( 1 );

		return parent::fix();
	}
}
