<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/** --------------------------------------------------------------------------------------------- */
/** BANNED IPS ================================================================================== */
/** --------------------------------------------------------------------------------------------- */

add_action( 'plugins_loaded', 'secupress_check_ban_ips' );
/**
 * Will remove expired banned IPs, then block the remaining ones. A form will be displayed to allow clumsy Administrators to unlock themselves.
 *
 * @since 1.0
 */
function secupress_check_ban_ips() {
	$ban_ips = get_site_option( SECUPRESS_BAN_IP );
	$ip      = secupress_get_ip();

	if ( secupress_ip_is_whitelisted( $ip ) ) {
		/**
		 * The user is white-listed. Make sure to remove the IP from the list.
		 * It will also prevent problems with `secupress_die()` not dying.
		 */
		if ( isset( $ban_ips[ $ip ] ) ) {
			unset( $ban_ips[ $ip ] );
			if ( $ban_ips ) {
				update_site_option( SECUPRESS_BAN_IP, $ban_ips );
			} else {
				delete_site_option( SECUPRESS_BAN_IP );
			}
		}

		return;
	}

	$time_ban = (int) secupress_get_module_option( 'login-protection_time_ban', 5, 'users-login' );
	$update   = false;
	$redirect = false;

	// If we got banned ips.
	if ( $ban_ips && is_array( $ban_ips ) ) {
		// The link to be unlocked?
		if ( ! empty( $_GET['action'] ) && 'secupress_self-unban-ip' === $_GET['action'] ) {
			$result = ! empty( $_GET['_wpnonce'] ) ? wp_verify_nonce( $_GET['_wpnonce'], 'secupress_self-unban-ip-' . $ip ) : false;

			if ( $result ) {
				// You're good to go.
				unset( $ban_ips[ $ip ] );
				$update   = true;
				$redirect = true;
			} elseif ( isset( $ban_ips[ $ip ] ) ) {
				// Cheating?
				$title   = '403 ' . get_status_header_desc( 403 );
				$content = __( 'Your unlock link has expired (or you\'re cheating).', 'secupress' );

				secupress_die( $content, $title, array( 'response' => 403 ) );
			}
		}

		// Purge the expired banned IPs.
		foreach ( $ban_ips as $timed_ip => $time ) {
			if ( ( $time + ( $time_ban * 60 ) ) < time() ) {
				unset( $ban_ips[ $timed_ip ] );
				$update = true;
			}
		}

		// Save the changes.
		if ( $update ) {
			if ( $ban_ips ) {
				update_site_option( SECUPRESS_BAN_IP, $ban_ips );
			} else {
				delete_site_option( SECUPRESS_BAN_IP );
			}
		}

		// The user just got unlocked. Redirect to homepage.
		if ( $redirect ) {
			wp_redirect( esc_url_raw( home_url() ) );
			die();
		}

		// Block the user if the IP is still in the array.
		if ( array_key_exists( $ip, $ban_ips ) ) {
			// Display a form in case of accidental ban.
			$unban_atts = secupress_check_ban_ips_maybe_send_unban_email( $ip );

			$title = ! empty( $unban_atts['title'] ) ? $unban_atts['title'] : ( '403 ' . get_status_header_desc( 403 ) );

			if ( $unban_atts['display_form'] ) {
				$in_ten_years = time() + YEAR_IN_SECONDS * 10;
				$time_ban     = $ban_ips[ $ip ] > $in_ten_years ? 0 : $time_ban;
				$error        = $unban_atts['message'];
				$content      = secupress_check_ban_ips_form( compact( 'ip', 'time_ban', 'error' ) );
			} else {
				$content = $unban_atts['message'];
			}

			secupress_die( $content, $title, array( 'response' => 403 ) );
		}
	} elseif ( false !== $ban_ips ) {
		delete_site_option( SECUPRESS_BAN_IP );
	}
}


/**
 * After submiting the email address with the form, send an email to the user or return an error.
 *
 * @since 1.0
 *
 * @param (string) $ip The user IP address.
 *
 * @return (array) An array containing at least a message and a "display_form" key to display or not the form after. Can contain a title.
 */
function secupress_check_ban_ips_maybe_send_unban_email( $ip ) {
	global $wpdb;

	if ( ! isset( $_POST['email'] ) ) { // WPCS: CSRF ok.
		return array(
			'message'      => '',
			'display_form' => true,
		);
	}
	// Check nonce and referer.
	$siteurl = strtolower( set_url_scheme( site_url() ) );
	$result  = ! empty( $_POST['_wpnonce'] ) ? wp_verify_nonce( $_POST['_wpnonce'], 'secupress-unban-ip-' . $ip ) : false;
	$referer = strtolower( wp_unslash( $_POST['_wp_http_referer'] ) );

	if ( strpos( $referer, 'http' ) !== 0 ) {
		$port    = (int) $_SERVER['SERVER_PORT'];
		$port    = 80 !== $port && 443 !== $port ? ( ':' . $port ) : '';
		$url     = 'http' . ( is_ssl() ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $port;
		$referer = $url . $referer;
	}

	if ( ! $result || strpos( $referer, $siteurl ) !== 0 ) {
		return array(
			'title'        => __( 'Cheatin&#8217; uh?' ),
			'message'      => __( 'Cheatin&#8217; uh?' ),
			'display_form' => false,
		);
	}

	// Check email.
	if ( empty( $_POST['email'] ) ) {
		return array(
			'message'      => __( '<strong>Error</strong>: the email field is empty.', 'secupress' ),
			'display_form' => true,
		);
	}

	$email    = wp_unslash( $_POST['email'] );
	$is_email = is_email( $email );

	if ( ! $is_email ) {
		return array(
			/** Translators: guess what, %s is an email address */
			'message'      => sprintf( __( '<strong>Error</strong>: the email address %s is not valid.', 'secupress' ), '<code>' . esc_html( $email ) . '</code>' ),
			'display_form' => true,
		);
	}
	$email = $is_email;

	// Check user.
	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		// Try with the recovery email.
		$user = (int) $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'secupress_recovery_email' AND meta_value = %s LIMIT 1", $email ) );
		$user = $user ? get_userdata( $user ) : 0;
	}

	if ( ! $user || ! user_can( $user, secupress_get_capability() ) ) {
		return array(
			'message'      => __( '<strong>Error</strong>: this email address does not belong to an Administrator.', 'secupress' ),
			'display_form' => true,
		);
	}

	// Send message.
	$message  = '<p>' . __( 'Well, this is awkward, you got yourself locked out? No problem, it happens sometimes. I\'ve got your back! I won\'t tell anybody. Or maybe I will. It could be a great story to tell during a long winter evening.', 'secupress' ) . '</p>';
	$message .= '<p>' . sprintf(
		/** Translators: %s is a "unlock yourself" link. */
		__( 'Anyway, simply follow this link to %s.', 'secupress' ),
		'<a href="' . esc_url_raw( wp_nonce_url( home_url() . '?action=secupress_self-unban-ip', 'secupress_self-unban-ip-' . $ip ) ) . '">' . __( 'unlock yourself', 'secupress' ) . '</a>'
	) . '</p>';

	$bcc = get_user_meta( $user->ID, 'secupress_recovery_email', true );

	if ( $bcc && $bcc = is_email( $bcc ) ) {
		$headers = array( 'bcc: ' . $bcc );
	} else {
		$headers = array();
	}

	$sent = secupress_send_mail( $user->user_email, SECUPRESS_PLUGIN_NAME, $message, $headers );

	if ( ! $sent ) {
		return array(
			'title'        => __( 'Oh ooooooh...', 'secupress' ),
			'message'      => __( 'The message could not be sent. I guess you have to wait now :(', 'secupress' ),
			'display_form' => false,
		);
	}

	return array(
		'title'        => __( 'Message sent', 'secupress' ),
		'message'      => __( 'Everything went fine, your message is on its way to your mailbox.', 'secupress' ),
		'display_form' => false,
	);
}


/**
 * Return the form where the user can enter his email address.
 *
 * @since 1.0
 *
 * @param (array) $args An array with the following:
 *                      - (string) $ip       The user IP.
 *                      - (int)    $time_ban Banishment duration in minutes. 0 means forever.
 *                      - (string) $error    An error text.
 *
 * @return (string) The form.
 */
function secupress_check_ban_ips_form( $args ) {
	$args = array_merge( array(
		'ip'       => '',
		'time_ban' => 0,
		'error'    => '',
	), $args );

	if ( $args['time_ban'] ) {
		$content = '<p>' . sprintf( _n( 'Your IP address <code>%1$s</code> has been banned for <strong>%2$d</strong> minute.', 'Your IP address <code>%1$s</code> has been banned for <strong>%2$d</strong> minutes.', $args['time_ban'], 'secupress' ), esc_html( $args['ip'] ), $args['time_ban'] ) . '</p>';
	} else {
		$content = '<p>' . sprintf( __( 'Your IP address <code>%s</code> has been banned.', 'secupress' ), esc_html( $args['ip'] ) ) . '</p>';
	}
	$content .= '<form method="post" autocomplete="on">';
		$content .= '<p>' . __( 'If you are an Administrator and have been accidentally locked out, enter your main email address or the backup one in the following field. A message will be sent to both addresses with a link allowing you to unlock yourself.', 'secupress' ) . '</p>';
		$content .= '<label for="email">';
			$content .= __( 'Your email address:', 'secupress' );
			$content .= ' <input id="email" type="email" name="email" value="" required="required" aria-required="true" />';
			$content .= $args['error'] ? '<br/><span class="error">' . $args['error'] . '</span>' : '';
		$content .= '</label>';
		$content .= '<p class="submit"><button type="submit" name="submit" class="button button-primary button-large">' . __( 'Submit', 'secupress' ) . '</button></p>';
		$content .= wp_nonce_field( 'secupress-unban-ip-' . $args['ip'], '_wpnonce', true , false );
	$content .= '</form>';

	return $content;
}


/** --------------------------------------------------------------------------------------------- */
/** FIX WP_DIE() HTML =========================================================================== */
/** --------------------------------------------------------------------------------------------- */

add_filter( 'wp_die_handler', 'secupress_get_wp_die_handler', SECUPRESS_INT_MAX );
/**
 * Filter the callback for killing WordPress execution for all non-Ajax, non-XML-RPC requests.
 * The aim is to fix the printed markup.
 *
 * @since 1.2.4
 * @author Grégory Viguier
 *
 * @param (string) $callback Callback function name.
 *
 * @return (string)
 */
function secupress_get_wp_die_handler( $callback ) {
	secupress_cache_data( 'wp_die_handler', $callback );
	return 'secupress_wp_die_handler';
}


/**
 * Kills WordPress execution and display HTML message with error message.
 * We first trigger the previous handler and then fix the markup.
 *
 * @since 1.2.4
 * @author Grégory Viguier
 *
 * @param (string|object) $message Error message or WP_Error object.
 * @param (string)        $title   Optional. Error title. Default empty.
 * @param (string|array)  $args    Optional. Arguments to control behavior. Default empty array.
 */
function secupress_wp_die_handler( $message, $title = '', $args = array() ) {
	ob_start( 'secupress_fix_wp_die_html' );

	$callback = secupress_cache_data( 'wp_die_handler' );
	$callback = $callback && is_callable( $callback ) ? $callback : '_default_wp_die_handler';

	call_user_func( $callback, $message, $title, $args );
}


/**
 * `ob_start()` callback to fix HTML markup.
 *
 * @since 1.2.4
 * @author Grégory Viguier
 *
 * @param (string) $buffer The error page HTML.
 *
 * @return (string)
 */
function secupress_fix_wp_die_html( $buffer ) {
	return str_replace( array( '<p><p>', '</p></p>', '<p><h1>', '</h1></p>' ), array( '<p>', '</p>', '<h1>', '</h1>' ), $buffer );
}


/** --------------------------------------------------------------------------------------------- */
/** VARIOUS STUFF =============================================================================== */
/** --------------------------------------------------------------------------------------------- */

add_filter( 'http_request_args', 'secupress_add_own_ua', 10, 2 );
/**
 * Force our user agent header when we call our urls.
 *
 * @since 1.0
 * @since 1.1.4 Available in global scope.
 *
 * @param (array)  $r   The request parameters.
 * @param (string) $url The request URL.
 *
 * @return (array)
 */
function secupress_add_own_ua( $r, $url ) {
	if ( false !== strpos( $url, 'secupress.me' ) ) {
		$r['headers']['X-SECUPRESS'] = secupress_user_agent( $r['user-agent'] );
	}
	return $r;
}


add_filter( 'secupress.plugin.blacklist_logins_list', 'secupress_maybe_remove_admin_from_blacklist' );
/**
 * If user registrations are open, the "admin" user should not be blacklisted.
 * This is to avoid a conflict between "admin should exist" and "admin is a blacklisted username".
 *
 * @since 1.0
 *
 * @param (array) $list List of usernames.
 *
 * @return (array) List of usernames minus "admin" if registrations are open.
 */
function secupress_maybe_remove_admin_from_blacklist( $list ) {
	if ( secupress_users_can_register() ) {
		$list = array_diff( $list, array( 'admin' ) );
	}

	return $list;
}


add_action( 'secupress.loaded', 'secupress_check_token_wp_registration_url' );
/**
 * Avoid sending emails when we do a "subscription test scan"
 *
 * @since 1.0
 */
function secupress_check_token_wp_registration_url() {
	if ( ! empty( $_POST['secupress_token'] ) && false !== ( $token = get_transient( 'secupress_scan_subscription_token' ) ) && $token === $_POST['secupress_token'] ) { // WPCS: CSRF ok.
		add_action( 'wp_mail', '__return_false' );
	}
}


add_filter( 'registration_errors', 'secupress_registration_test_errors', PHP_INT_MAX, 2 );
/**
 * This is used in the Subscription scan to test user registrations from the login page.
 *
 * @since 1.0
 * @see `register_new_user()`
 *
 * @param (object) $errors               A WP_Error object containing any errors encountered during registration.
 * @param (string) $sanitized_user_login User's username after it has been sanitized.
 *
 * @return (object) The WP_Error object with a new error if the user name is blacklisted.
 */
function secupress_registration_test_errors( $errors, $sanitized_user_login ) {
	if ( ! $errors->get_error_code() && false !== strpos( $sanitized_user_login, 'secupress' ) ) {
		set_transient( 'secupress_registration_test', 'failed', HOUR_IN_SECONDS );
		$errors->add( 'secupress_registration_test', 'secupress_registration_test_failed' );
	}

	return $errors;
}


/** --------------------------------------------------------------------------------------------- */
/** AFTER AUTOMATIC FIX / MANUAL FIX ============================================================ */
/** --------------------------------------------------------------------------------------------- */

add_action( 'plugins_loaded', 'secupress_rename_admin_username_logout', 50 );
/**
 * Will rename the "admin" account after the rename-admin-username manual fix.
 *
 * @since 1.0
 */
function secupress_rename_admin_username_logout() {
	global $current_user, $wpdb;

	if ( ! secupress_can_perform_extra_fix_action() ) {
		return;
	}

	$data = secupress_get_site_transient( 'secupress-rename-admin-username' );

	if ( ! $data ) {
		return;
	}

	if ( ! is_array( $data ) || ! isset( $data['ID'], $data['username'] ) ) {
		secupress_delete_site_transient( 'secupress-rename-admin-username' );
		return;
	}

	$current_user = wp_get_current_user(); // WPCS: override ok.

	if ( (int) $current_user->ID !== (int) $data['ID'] || 'admin' !== $current_user->user_login ) {
		return;
	}

	secupress_delete_site_transient( 'secupress-rename-admin-username' );

	$is_super_admin = false;

	if ( is_multisite() && is_super_admin() ) {
		require_once( ABSPATH . 'wp-admin/includes/ms.php' );
		revoke_super_admin( $current_user->ID );
		$is_super_admin = true;
	}

	$wpdb->update( $wpdb->users, array( 'user_login' => $data['username'] ), array( 'user_login' => 'admin' ) );

	// Current user auth cookie is now invalid, log in again is mandatory.
	wp_clear_auth_cookie();

	if ( function_exists( 'wp_destroy_current_session' ) ) { // WP 4.0 min.
		wp_destroy_current_session();
	}

	wp_cache_delete( $current_user->ID, 'users' );

	if ( $is_super_admin ) {
		grant_super_admin( $current_user->ID );
	}

	secupress_fixit( 'Admin_User' );

	// Auto-login.
	$token = md5( time() );
	secupress_set_site_transient( 'secupress_auto_login_' . $token, array( $data['username'], 'Admin_User' ) );

	wp_safe_redirect( esc_url_raw( add_query_arg( 'secupress_auto_login_token', $token ) ) );
	die();
}


add_action( 'plugins_loaded', 'secupress_add_cookiehash_muplugin', 50 );
/**
 * Will create a mu plugin to modify the COOKIEHASH constant.
 *
 * @since 1.0
 */
function secupress_add_cookiehash_muplugin() {
	global $current_user, $wpdb;

	if ( ! secupress_can_perform_extra_fix_action() ) {
		return;
	}

	$data = secupress_get_site_transient( 'secupress-add-cookiehash-muplugin' );

	if ( ! $data ) {
		return;
	}

	if ( ! is_array( $data ) || ! isset( $data['ID'], $data['username'] ) ) {
		secupress_delete_site_transient( 'secupress-add-cookiehash-muplugin' );
		return;
	}

	if ( get_current_user_id() !== (int) $data['ID'] ) {
		return;
	}

	secupress_delete_site_transient( 'secupress-add-cookiehash-muplugin' );

	// Create the MU plugin.
	$cookiehash = file_get_contents( SECUPRESS_INC_PATH . 'data/cookiehash.phps' );
	$args       = array(
		'{{PLUGIN_NAME}}' => SECUPRESS_PLUGIN_NAME,
		'{{HASH}}'        => wp_generate_password( 64 ),
	);
	$cookiehash = str_replace( array_keys( $args ), $args, $cookiehash );

	if ( ! $cookiehash || ! secupress_create_mu_plugin( 'cookiehash_' . uniqid(), $cookiehash ) ) {
		// MU Plugin creation failed.
		secupress_set_site_transient( 'secupress-cookiehash-muplugin-failed', 1 );
		secupress_fixit( 'WP_Config' );
		return;
	}

	wp_clear_auth_cookie();

	if ( function_exists( 'wp_destroy_current_session' ) ) { // WP 4.0 min.
		wp_destroy_current_session();
	}

	// MU Plugin creation succeeded.
	secupress_set_site_transient( 'secupress-cookiehash-muplugin-succeeded', 1 );
	secupress_fixit( 'WP_Config' );

	// Auto-login.
	$token = md5( time() );
	secupress_set_site_transient( 'secupress_auto_login_' . $token, array( $data['username'], 'WP_Config' ) );

	wp_safe_redirect( esc_url_raw( add_query_arg( 'secupress_auto_login_token', $token ) ) );
	die();
}


add_action( 'plugins_loaded', 'secupress_add_salt_muplugin', 50 );
/**
 * Will create a mu plugin to early set the salt keys.
 *
 * @since 1.0
 */
function secupress_add_salt_muplugin() {
	global $current_user, $wpdb;

	if ( ! secupress_can_perform_extra_fix_action() ) {
		return;
	}

	$data = secupress_get_site_transient( 'secupress-add-salt-muplugin' );

	if ( ! $data ) {
		return;
	}

	if ( ! is_array( $data ) || ! isset( $data['ID'], $data['username'] ) ) {
		secupress_delete_site_transient( 'secupress-add-salt-muplugin' );
		return;
	}

	if ( get_current_user_id() !== (int) $data['ID'] ) {
		return;
	}

	secupress_delete_site_transient( 'secupress-add-salt-muplugin' );

	// Make sure we find the `wp-config.php` file.
	$wpconfig_filepath = secupress_is_wpconfig_writable();

	if ( ! $wpconfig_filepath ) {
		return;
	}

	// Create the MU plugin.
	if ( ! defined( 'SECUPRESS_SALT_KEYS_ACTIVE' ) ) {
		$alicia_keys = file_get_contents( SECUPRESS_INC_PATH . 'data/salt-keys.phps' );
		$args        = array(
			'{{PLUGIN_NAME}}' => SECUPRESS_PLUGIN_NAME,
			'{{HASH1}}'        => wp_generate_password( 64, true, true ),
			'{{HASH2}}'        => wp_generate_password( 64, true, true ),
		);
		$alicia_keys = str_replace( array_keys( $args ), $args, $alicia_keys );

		if ( ! $alicia_keys || ! secupress_create_mu_plugin( 'salt_keys_' . uniqid(), $alicia_keys ) ) {
			return;
		}
	}

	/**
	 * Remove old secret keys from the `wp-config.php` file and add a comment.
	 * We have to make sure the comment is added, only once, only if one or more keys are found, even if some secret keys are missing, and do not create useless empty lines.
	 */
	$wp_filesystem    = secupress_get_filesystem();
	$wpconfig_content = $wp_filesystem->get_contents( $wpconfig_filepath );
	$keys             = array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' );
	$comment_added    = false;
	$comment          = '/** SecuPress: if you ever want to add secret keys back here, get new ones at https://api.wordpress.org/secret-key/1.1/salt. */';
	$placeholder      = '/** SecuPress salt placeholder. */';

	foreach ( $keys as $i => $constant ) {
		$pattern = '@define\s*\(\s*([\'"])' . $constant . '\1.*@';

		if ( preg_match( $pattern, $wpconfig_content, $matches ) ) {
			$replace          = $comment_added ? $placeholder : $comment;
			$wpconfig_content = str_replace( $matches[0], $replace, $wpconfig_content );
			$comment_added    = true;
		}
	}

	if ( $comment_added ) {
		$wpconfig_content = str_replace( $placeholder . "\n", '', $wpconfig_content );

		$wp_filesystem->put_contents( $wpconfig_filepath, $wpconfig_content, FS_CHMOD_FILE );
	}

	// Remove old secret keys from the database.
	foreach ( $keys as $constant ) {
		delete_site_option( $constant );
	}

	// Destroy the user session.
	wp_clear_auth_cookie();
	if ( function_exists( 'wp_destroy_current_session' ) ) { // WP 4.0 min.
		wp_destroy_current_session();
	}

	$token = md5( time() );
	secupress_set_site_transient( 'secupress_auto_login_' . $token, array( $data['username'], 'Salt_Keys' ) );

	wp_safe_redirect( esc_url_raw( add_query_arg( 'secupress_auto_login_token', $token, secupress_get_current_url( 'raw' ) ) ) );
	die();
}


add_action( 'plugins_loaded', 'secupress_auto_username_login', 60 );
/**
 * Will autologin the user found in the transient 'secupress_auto_login_' . $_GET['secupress_auto_login_token']
 *
 * @since 1.0
 */
function secupress_auto_username_login() {
	if ( ! isset( $_GET['secupress_auto_login_token'] ) ) {
		return;
	}

	list( $username, $action ) = secupress_get_site_transient( 'secupress_auto_login_' . $_GET['secupress_auto_login_token'] );

	secupress_delete_site_transient( 'secupress_auto_login_' . $_GET['secupress_auto_login_token'] );

	if ( ! $username ) {
		return;
	}

	add_filter( 'authenticate', 'secupress_give_him_a_user', 1, 2 );
	$user = wp_signon( array( 'user_login' => $username ) );
	remove_filter( 'authenticate', 'secupress_give_him_a_user', 1, 2 );

	if ( is_a( $user, 'WP_User' ) ) {
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID );
	}

	if ( $action ) {
		secupress_scanit( $action );
	}

	wp_safe_redirect( esc_url_raw( remove_query_arg( 'secupress_auto_login_token', secupress_get_current_url( 'raw' ) ) ) );
	die();
}


/**
 * Used in secupress_rename_admin_username_login() to force a user when auto authenticating.
 *
 * @since 1.0
 *
 * @param (null|object) $user     WP_User object if the user is authenticated.
 *                                WP_Error object or null otherwise.
 * @param (string)      $username Username or email address.
 *
 * @return (object|bool) A WP_User object or false.
 */
function secupress_give_him_a_user( $user, $username ) {
	return get_user_by( 'login', $username );
}
