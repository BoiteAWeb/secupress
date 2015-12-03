<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * This warning is displayed when the plugin can not be deactivated correctly.
 *
 * @since 1.0
 */
add_action( 'admin_init', 'secupress_bad_deactivations' );

function secupress_bad_deactivations() {
	global $status, $page, $s;
	$current_user_id = get_current_user_id();

	if ( ! current_user_can( secupress_get_capability() ) || ! ( $msgs = secupress_get_site_transient( $current_user_id . '_donotdeactivatesecupress' ) ) ) {
		return;
	}

	secupress_delete_site_transient( $current_user_id . '_donotdeactivatesecupress' );

	$errors = array();

	foreach ( $msgs as $msg ) {
		switch ( $msg ) {
			case 'htaccess' :
				$errors['htaccess']  = sprintf( __( '%1$s can not be deactivated because of %2$s.', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>', '<code>.htaccess</code>' ) . '<br>';
				$errors['htaccess'] .= sprintf( __( 'This file is not writable and we can not remove these directives. Maybe we do not have writing permissions for %s.', 'secupress' ), '<code>.htaccess</code>' ) . '<br>';
				$errors['htaccess'] .= __( 'Please give us permissions or resolve the problem yourself. Then retry deactivation.', 'secupress' );
				break;
			case 'webconfig' :
				$errors['webconfig']  = sprintf( __( '%1$s can not be deactivated because of %2$s.', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>', '<code>web.config</code>' ) . '<br>';
				$errors['webconfig'] .= sprintf( __( 'This file is not writable and we can not remove these directives. Maybe we do not have writing permissions for %s.', 'secupress' ), '<code>web.config</code>' ) . '<br>';
				$errors['webconfig'] .= __( 'Please give us permissions or resolve the problem yourself. Then retry deactivation.', 'secupress' );
				break;
			case 'wp-config' :
				$errors['wp-config']  = sprintf( __( '%1$s can not be deactivated because of %2$s.', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>', '<code>wp-config.php</code>' ) . '<br>';
				$errors['wp-config'] .= sprintf( __( 'This file is not writable and we can not remove these directives. Maybe we do not have writing permissions for %s.', 'secupress' ), '<code>wp-config.php</code>' ) . '<br>';
				$errors['wp-config'] .= __( 'Please give us permissions or resolve the problem yourself. Then retry deactivation.', 'secupress' );
				break;
		}
	}

	/**
	  * Filter the output messages for each bad deactivation attempt.
	  *
	  * @since 2.0.0
	  *
	  * @param array $errors Contains the error messages to be filtered
	  * @param string $msg Contains the error type (wpconfig or htaccess)
	 */
	$errors = apply_filters( 'secupress_bad_deactivations', $errors, $msgs );

	if ( $errors ) {
		foreach ( $errors as $nessage ) {
			secupress_add_notice( $message, 'error' );
		}
	}

	/**
	  * Allow a "force deactivation" link to be printed, use at your own risks
	  *
	  * @since 2.0.0
	  *
	  * @param bool true will print the link
	 */
	$permit_force_deactivation = apply_filters( 'secupress_permit_force_deactivation', true );

	// We add a link to permit "force deactivation", use at your own risks.
	if ( ! $permit_force_deactivation ) {
		return;
	}

	$message = wp_nonce_url( 'plugins.php?action=deactivate&amp;secupress_nonce=' . wp_create_nonce( 'force_deactivation' ) . '&amp;plugin=' . SECUPRESS_PLUGIN_FILE . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . SECUPRESS_PLUGIN_FILE );
	$message = '<a href="' . $message . '">' . __( 'You can still force the deactivation by clicking here.', 'secupress' ) . '</a>';

	secupress_add_notice( $message, 'error' );
}


/**
 * This warning is displayed when some plugins may conflict with SecuPress.
 *
 * @since 1.0
 */
add_action( 'admin_init', 'secupress_plugins_to_deactivate' );

function secupress_plugins_to_deactivate() {
	if ( ! current_user_can( secupress_get_capability() ) ) {
		return;
	}

	$plugins = array(
		'wordfence/wordfence.php'
	);

	$plugins_to_deactivate = array_filter( $plugins, 'is_plugin_active' );

	if ( ! $plugins_to_deactivate ) {
		return;
	}

	$message  = sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
	$message .= __( 'The following plugins are not compatible with this plugin and may cause unexpected results:', 'secupress' );
	$message .= '<ul>';
	foreach ( $plugins_to_deactivate as $plugin ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
		$message .= '<li>' . $plugin_data['Name'] . '</span> <a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=deactivate_plugin&plugin=' . urlencode( $plugin ) ), 'deactivate_plugin' ) . '" class="button-secondary alignright">' . __( 'Deactivate' ) . '</a></li>';
	}
	$message .= '</ul>';

	secupress_add_notice( $message, 'error', 'deactivate-plugin' );
}


/**
 * This warning is displayed when the wp-config.php file isn't writable.
 *
 * @since 1.0
 */
add_action( 'admin_init', 'secupress_warning_wp_config_permissions' );

function secupress_warning_wp_config_permissions() {
	global $pagenow;

	if ( 'plugins.php' === $pagenow && isset( $_GET['activate'] ) ) {
		return;
	}

	if ( ! current_user_can( secupress_get_capability() ) || wp_is_writable( secupress_find_wpconfig_path() ) ) {
		return;
	}

	$message  = sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
	$message .= sprintf( __( 'It seems we don\'t have <a href="%1$s" target="_blank">writing permissions</a> on %2$s file.', 'secupress' ), 'http://codex.wordpress.org/Changing_File_Permissions', '<code>wp-config.php</code>' );

	secupress_add_notice( $message, 'error', 'wpconfig-not-writable' );
}


/**
 * This warning is displayed when the .htaccess file or the web.config file doesn't exist or isn't writable.
 *
 * @since 1.0
 */
add_action( 'admin_admin', 'secupress_warning_htaccess_permissions' );

function secupress_warning_htaccess_permissions() {
	global $pagenow, $is_apache, $is_iis7;

	if ( ! current_user_can( secupress_get_capability() ) ) {
		return;
	}

	if ( $is_apache ) {
		$file = '.htaccess';
		$htaccess_file = secupress_get_home_path() . $file;

		if ( is_writable( $htaccess_file ) ) {
			return;
		}
	} elseif ( $is_iis7 ) {
		$file = 'web.config';
		$web_config_file = secupress_get_home_path() . $file;

		if ( wp_is_writable( $web_config_file ) ) {
			return;
		}
	} else {
		return;
	}

	$message  = sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
	$message .= sprintf( __( 'If you had <a href="%1$s" target="_blank">writing permissions</a> on %2$s file, %3$s could do more things automatically.', 'secupress' ), 'http://codex.wordpress.org/Changing_File_Permissions', '<code>' . $file . '</code>', '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );

	secupress_add_notice( $message, 'error', 'htaccess-not-writable' );
}


/**
 * These warnings are displayed when a module has been activated/deactivated.
 *
 * @since 1.0
 */
add_action( 'admin_init', 'secupress_warning_module_activity' );

function secupress_warning_module_activity() {
	$current_user_id = get_current_user_id();

	if ( ! current_user_can( secupress_get_capability() ) ) {
		return;
	}

	$activated_modules   = secupress_get_site_transient( 'secupress_module_activation_' . $current_user_id );
	$deactivated_modules = secupress_get_site_transient( 'secupress_module_deactivation_' . $current_user_id );

	if ( false !== $activated_modules ) {
		$message  = '<p>' . sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
		$message .= _n( 'This module has been activated:', 'These modules have been activated:', count( $activated_modules ), 'secupress' );
		$message .= sprintf( '</p><ul><li>%s</li></ul>', implode( '</li><li>', $activated_modules ) );

		secupress_add_notice( $message );
		secupress_delete_site_transient( 'secupress_module_activation_' . $current_user_id );
	}

	if ( false !== $deactivated_modules ) {
		$message  = '<p>' . sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
		$message .= _n( 'This module has been deactivated:', 'These modules have been deactivated:', count( $deactivated_modules ), 'secupress' );
		$message .= sprintf( '</p><ul><li>%s</li></ul>', implode( '</li><li>', $deactivated_modules ) );

		secupress_add_notice( $message );
		secupress_delete_site_transient( 'secupress_module_deactivation_' . $current_user_id );
	}
}


/**
 * This warning is displayed when the backup email is not set.
 *
 * @since 1.0
 */
add_action( 'admin_init', 'secupress_warning_no_backup_email' );

function secupress_warning_no_backup_email() {
	if ( get_user_meta( get_current_user_id(), 'backup_email', true ) ) {
		return;
	}

	$message  = sprintf( __( '%s: ', 'secupress' ), '<strong>' . SECUPRESS_PLUGIN_NAME . '</strong>' );
	$message .= sprintf( __( 'Your <a href="%s">Backup E-mail</a> isn\'t yet set. Please do it.', 'secupress' ), get_edit_profile_url() . '#secupress_backup_email' );

	secupress_add_notice( $message, 'error', false );
}


/**
 * Add a notice with the SecuPress_Admin_Notices class.
 *
 * @since 1.0
 *
 * @param (string)      $message    The message to display in the notice.
 * @param (string)      $error_code Like WordPress notices: "error" or "updated". Default is "updated".
 * @param (string|bool) $notice_id  A unique identifier to tell id the notice is dismissible.
 *                                  false: the notice is not dismissible.
 *                                  string: the notice is dismissible and send an ajax call to store the "dismissed" state into a user meta to prevent it to popup again.
 *                                  enpty string: meant for a one-shot use. The notice is dismissible but the "dismissed" state is not stored, it will popup again. This is the exact same behavior than the WordPress dismissible notices.
 */
function secupress_add_notice( $message, $error_code = null, $notice_id = '' ) {
	SecuPress_Admin_Notices::get_instance()->add( $message, $error_code, $notice_id );
}


/**
 * Dismiss a notice added with the SecuPress_Admin_Notices class.
 *
 * @since 1.0
 *
 * @param (string) $notice_id The notice identifier.
 * @param (int)    $user_id   User ID. If not set, fallback to the current user ID.
 *
 * @return (bool) true on success.
 */
function secupress_dismiss_notice( $notice_id, $user_id = 0 ) {
	return SecuPress_Admin_Notices::dismiss( $notice_id, $user_id );
}
