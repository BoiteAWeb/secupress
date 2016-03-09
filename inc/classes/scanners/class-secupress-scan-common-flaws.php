<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Common Flaws scan class.
 *
 * @package SecuPress
 * @subpackage SecuPress_Scan
 * @since 1.0
 */

class SecuPress_Scan_Common_Flaws extends SecuPress_Scan implements iSecuPress_Scan {

	const VERSION = '1.0';

	/**
	 * @var Singleton The reference to *Singleton* instance of this class
	 */
	protected static $_instance;
	public    static $prio = 'high';


	protected static function init() {
		self::$type  = 'PHP';
		self::$title = __( 'Check if your website can easily be the target of common flaws.', 'secupress' );
		self::$more  = __( 'Every year, new flaws are discovered. You have to be sure that your website cannot be a target.', 'secupress' );
		self::$more_fix = sprintf( __( 'The fix will activate the option <em>%1$s</em> from the module <a href="%2$s">%3$s</a>.', 'secupress' ), __( 'Block Bad Contents', 'secupress' ), secupress_admin_url( 'modules', 'firewall#Block_Bad_Contents' ), __( 'Firewall', 'secupress' ) );
	}


	public static function get_messages( $message_id = null ) {
		$messages = array(
			// good
			0   => _n_noop( 'All is ok, %d test passed.', 'All is ok, %d tests passed.', 'secupress' ),
			1   => __( 'Protection activated', 'secupress' ),
			// warning
			100 => __( 'Unable to determine status of your homepage.', 'secupress' ),
			101 => sprintf( __( 'Unable to determine status of <strong>Shellshock</strong> flaw (%s).', 'secupress' ), '<em>CVE-2014-6271</em>' ),
			102 => sprintf( __( 'Unable to determine status of <strong>Shellshock</strong> flaw (%s).', 'secupress' ), '<em>CVE-2014-7169</em>' ),
			// bad
			200 => __( 'Your website pages should be <strong>different</strong> for each reload.', 'secupress' ),
			201 => sprintf( __( 'The server appears to be vulnerable to <strong>Shellshock</strong> (%s).', 'secupress' ), '<em>CVE-2014-6271</em>' ),
			202 => sprintf( __( 'The server appears to be vulnerable to <strong>Shellshock</strong> (%s).', 'secupress' ), '<em>CVE-2014-7169</em>' ),
			203 => __( 'Your website should block <strong>malicious requests</strong>.', 'secupress' ),
			// cantfix
			300 => __( 'I can not fix this, you have to do it yourself, have fun.', 'secupress' ),
		);

		if ( isset( $message_id ) ) {
			return isset( $messages[ $message_id ] ) ? $messages[ $message_id ] : __( 'Unknown message', 'secupress' );
		}

		return $messages;
	}


	public function scan() {
		$nbr_tests = 0;

		// Shellshock - http://plugins.svn.wordpress.org/shellshock-check/trunk/shellshock-check.php
		if ( 'WIN' !== strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
			++$nbr_tests;

			$env = array( 'SHELL_SHOCK_TEST' => '() { :;}; echo VULNERABLE' );

			$desc = array(
				0 => array( 'pipe', 'r' ),
				1 => array( 'pipe', 'w' ),
				2 => array( 'pipe', 'w' ),
			);

			// CVE-2014-6271
			$p      = proc_open( 'bash -c "echo Test"', $desc, $pipes, null, $env );
			$output = isset( $pipes[1] ) ? stream_get_contents( $pipes[1] ) : 'error';
			proc_close( $p );

			if ( false === strpos( $output, 'VULNERABLE' ) ) {
				// good
			} elseif ( 'error' === $output ) {
				// warning
				$this->add_message( 101 );
			} else {
				// bad
				$this->add_message( 201 );
			}

			// CVE-2014-7169
			$test_date = date( 'Y' );
			$p         = proc_open( "rm -f echo; env 'x=() { (a)=>\' bash -c \"echo date +%Y\"; cat echo", $desc, $pipes, sys_get_temp_dir() );
			$output    = isset( $pipes[1] ) ? stream_get_contents( $pipes[1] ) : 'error';
			proc_close( $p );

			if ( trim( $output ) !== $test_date ) {
				// good
			} elseif ( 'error' === $output ) {
				// warning
				$this->add_message( 102 );
			} else {
				// bad
				$this->add_message( 202 );
			}
		}

		// wp-config.php access
		++$nbr_tests;
		$response = wp_remote_get( user_trailingslashit( home_url() ) . '?' . time() . '=wp-config.php', array( 'redirection' => 0 ) );

		if ( ! is_wp_error( $response ) ) {

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				// bad
				$this->add_message( 203 );
			}

		} else {
			// warning
			$this->add_message( 100 );
		}

		// good
		$this->maybe_set_status( 0, array( $nbr_tests, $nbr_tests ) );

		return parent::scan();
	}


	public function fix() {

		// Activate.
		secupress_activate_submodule( 'firewall', 'bad-url-contents' );

		// good
		$this->add_fix_message( 1 );

		return parent::fix();
	}
}
