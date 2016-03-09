<?php
defined( 'ABSPATH' ) or die('Cheatin\' uh?');

/**
 * Core Update scan class.
 *
 * @package SecuPress
 * @subpackage SecuPress_Scan
 * @since 1.0
 */

class SecuPress_Scan_Core_Update extends SecuPress_Scan implements iSecuPress_Scan {

	const VERSION = '1.0';

	/**
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	protected static $_instance;
	public    static $prio = 'high';


	protected static function init() {
		self::$type     = 'WordPress';
		self::$title    = __( 'Check if your WordPress core is up to date.', 'secupress' );
		self::$more     = __( 'It\'s very important to maintain your WordPress installation up to date. If you can not update for any reason, contact your hosting provider as soon as possible.', 'secupress' );
		self::$more_fix = __( 'This will will update your WordPress installation.', 'secupress' );
	}


	public static function get_messages( $message_id = null ) {
		$messages = array(
			// good
			0   => __( 'WordPress core is up to date.', 'secupress' ),
			1   => __( 'WordPress has been updated to version <strong>%s</strong>.', 'secupress' ),
			2   => '%s', // already translated
			// bad
			200 => __( 'WordPress core is <strong>not up to date</strong>.', 'secupress' ),
			// cantfix
			300 => '%s', // already translated
			301 => __( 'You have the latest version of WordPress.' ), // wp i18n
		);

		if ( isset( $message_id ) ) {
			return isset( $messages[ $message_id ] ) ? $messages[ $message_id ] : __( 'Unknown message', 'secupress' );
		}

		return $messages;
	}


	public function scan() {
		ob_start();

		if ( ! function_exists( 'get_preferred_from_update_core' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/update.php' );
		}

		wp_version_check();
		$core_update = get_preferred_from_update_core();
		$core_update = isset( $core_update->response ) && 'upgrade' === $core_update->response;

		ob_flush();

		if ( $core_update ) {
			// bad
			$this->add_message( 200 );
		}

		// good
		$this->maybe_set_status( 0 );

		return parent::scan();
	}


	public function fix() {

		ob_start();
		@set_time_limit( 0 );

		$core    = get_preferred_from_update_core();
		$version = isset( $core->version ) ? $core->version : false;
		$locale  = isset( $core->locale )  ? $core->locale  : 'en_US';
		$result  = false;

		if ( $version ) {
			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

			$url       = 'update-core.php?action=do-core-upgrade';
			$nonce     = 'upgrade-core';
			$url_nonce = wp_nonce_url( $url, $nonce );
			$update = find_core_update( $version, $locale );

			if ( $update ) {
				$allow_relaxed_file_ownership = isset( $update->new_files ) && ! $update->new_files;
				$credentials = request_filesystem_credentials( $url_nonce, '', false, ABSPATH, array( 'version', 'locale' ), $allow_relaxed_file_ownership );

				if ( WP_Filesystem( $credentials, ABSPATH, $allow_relaxed_file_ownership ) ) {

					// remove the WP upgrade process for translation since it will output data, use our own based on core but using a silent upgrade
					remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
					add_action( 'upgrader_process_complete', 'secupress_async_upgrades', 20 );
					
					$skin = new Automatic_Upgrader_Skin( compact( 'nonce', 'url' ) );
					$upgrader = new Core_Upgrader( $skin );
					$result   = $upgrader->upgrade( $update, array(
						'allow_relaxed_file_ownership' => $allow_relaxed_file_ownership
					) );
				}
			}
		}

		ob_end_clean();
		if ( is_string( $result ) ) {
			$this->add_fix_message( 1, array( $result ) );
		} elseif( false === $result ) {
			$this->add_fix_message( 301 );
		} else {
			$errors = reset( $result->errors );
			$code = isset( $errors['up_to_date'] ) ? 2 : 300;
			$this->add_fix_message( 300, array( reset( $errors ) ) );
		}

		return parent::fix();
	}
}
