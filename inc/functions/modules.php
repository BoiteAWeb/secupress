<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Get modules title, icon, description and other informations.
 *
 * @since 1.0
 *
 * @return (array) All informations related to the modules.
 */
function secupress_get_modules() {
	$should_be_pro = ! secupress_is_pro()  ? true : false;

	$modules = array(
		'users-login'     => array(
			'title'       => __( 'Users &amp; Login', 'secupress' ),
			'icon'        => 'user-login',
			'summaries'   => array(
				'small'  => __( 'Protect your users', 'secupress' ),
				'normal' => __( 'You will find here the best and easy ways to be sure that users\' data will be protected, and their account not compromised.', 'secupress' ),
			),
			'description' => array(
				__( 'Your users &ndash; and every account on your website &ndash; want to be sure that their data will be protected, and their account not compromised. This is why you have to take care of them and protect them.', 'secupress' ),
				__( 'You will find here the best and easy ways to do this.', 'secupress' ),
			),
			'mark_as_pro' => false,
		),
		'plugins-themes'  => array(
			'title'       => __( 'Plugins &amp; Themes', 'secupress' ),
			'icon'        => 'themes-plugins',
			'summaries'   => array(
				'small'  => __( 'Check your plugins &amp; themes', 'secupress' ),
				'normal' => __( 'Installation, activation, deactivation and deletion of themes and plugins can be disallowed when you don\'t need it.', 'secupress' ),
			),
			'description' => array(
				__( 'When your website is online, there is no reason to let someone play with your plugins. Installation, activation, deactivation, upgrade and deletion can be disallowed when you don\'t need it.', 'secupress' ),
				__( 'Do not hesitate to check all, and then, when you need, come back here to deactivate only what you need.', 'secupress' ),
			),
			'mark_as_pro' => false,
		),
		'wordpress-core'  => array(
			'title'       => __( 'WordPress Core', 'secupress' ),
			'icon'        => 'core',
			'summaries'   => array(
				'small'  => __( 'Core Tweaking', 'secupress' ),
				'normal' => __( 'WordPress can be tweak by so many ways. But are you using the right ones. We will help', 'secupress' ),
			),
			'description' => array(
				__( 'WordPress can be tweak by so many ways. But are you using the right ones.', 'secupress' ),
			),
			'mark_as_pro' => false,
		),
		'sensitive-data'  => array(
			'title'       => __( 'Sensitive Data', 'secupress' ),
			'icon'        => 'sensitive-data',
			'summaries'   => array(
				'small'  => __( 'Keep your data safe', 'secupress' ),
				'normal' => __( 'Some pages can contains sensitive data. It\'s a good practice to lock these pages.', 'secupress' ),
			),
			'description' => array(
				__( 'Some pages can contains sensitive data. It\'s a good practice to lock these pages.', 'secupress' ),
				__( 'Do not hesitate to lock as much as you can to improve the security of your website.', 'secupress' ),
			),
			'mark_as_pro' => false,
		),
		'file-system'     => array(
			'title'       => __( 'Malware Scan', 'secupress' ),
			'icon'        => 'file-system',
			'summaries'   => array(
				'small'  => __( 'Permissions &amp; Antivirus', 'secupress' ),
				'normal' => __( 'Check file permissions, run monitoring and antivirus on your installation to verify files integrity.', 'secupress' ),
			),
			'with_form'   => false,
			'description' => array(
				__( 'Check the file permissions <em>(chmod)</em> at a glance and run a file monitoring on your installation', 'secupress' ),
				__( 'Also, an antivus scanner can be performed on your installation, this may take time but it\'s more efficient.', 'secupress' ),
			),
			'with_reset_box' => false,
			'mark_as_pro'    => false,
		),
		'firewall'     => array(
			'title'       => __( 'Firewall', 'secupress' ),
			'icon'        => 'firewall',
			'summaries'   => array(
				'small'  => __( 'Block bad requests', 'secupress' ),
				'normal' => __( 'Malicious requests are badly common. This will checks all incoming requests and quietly blocks all of these containing bad stuff.', 'secupress' ),
			),
			'description' => array(
				__( 'Malicious requests are badly common. This will checks all incoming requests and quietly blocks all of these containing bad stuff.', 'secupress' ),
			),
			'mark_as_pro' => $should_be_pro,
		),
		'backups'         => array(
			'title'       => __( 'Backups', 'secupress' ),
			'icon'        => 'backups',
			'summaries'   => array(
				'small'  => __( 'Never lose anything', 'secupress' ),
				'normal' => __( 'Backuping your database daily and you files weekly can reduce the risks to lose your content because of an attack.', 'secupress' ),
			),
			'with_form'   => false,
			'description' => array(
				__( 'Backuping your database daily and you files weekly can reduce the risks to lose your content because of an attack.', 'secupress' ),
				sprintf( __( 'Don\'t forget to <a href="%s">schedule backups</a> as soon as possible.', 'secupress' ), esc_url( secupress_admin_url( 'modules', 'schedules' ) ) ),
			),
			'with_reset_box' => false,
			'mark_as_pro'    => false,
		),
		'antispam'        => array(
			'title'       => __( 'Anti Spam', 'secupress' ),
			'icon'        => 'antispam',
			'summaries'   => array(
				'small'  => __( 'Get rid of junk', 'secupress' ),
				'normal' => __( 'Traffic done by bot represents about 60% of the internet. Spams are done by these bots. Don\'t let them do that!', 'secupress' ),
			),
			'description' => array(
				__( 'Comments are great for your website, but bot traffic represent about 60 % of the internet. Spams are done by these bots, and they just want to add their content in your website. Don\'t let them do that!', 'secupress' ),
				sprintf( __( 'Do not forget to visit the <a href="%s">Settings &rsaquo; Discussion</a> area to add words to the blacklist and other usual settings regarding comments.', 'secupress' ), esc_url( admin_url( 'options-discussion.php' ) ) ),
				__( 'By default, we block identity usurpation, so if someone tries to comment using your email/name, the comment will be blocked.', 'secupress' ),
				__( 'Also by default, we block bad IPs, author name, email and website url known as spammer.', 'secupress' ),
			),
			'mark_as_pro' => false,
		),
		'logs'            => array(
			'title'       => _x( 'Logs', 'post type general name', 'secupress' ),
			'icon'        => 'logs',
			'summaries'   => array(
				'small'  => __( 'Enter the matrix', 'secupress' ),
				'normal' => __( 'Logs are very usefull, it acts like a history of what happened on your website, filtered and at any time. You can also read and delete banned IPs from our modules here.', 'secupress' ),
			),
			'with_form'   => false,
			'description' => array(
				__( 'Logs are very usefull, it acts like a history of what happened on your website, filtered and at any time. You can also read and delete banned IPs from our modules here.', 'secupress' ),
			),
			'mark_as_pro' => $should_be_pro,
		),
		'alerts'          => array(
			'title'       => __( 'Alerts', 'secupress' ),
			'icon'        => 'information',
			'summaries'   => array(
				'small'  => __( 'Get alerted by events', 'secupress' ),
				'normal' => __( 'Being alerted of some important events might help to react quickly in case of possible attack vector.', 'secupress' ),
			),
			'description' => array(
				__( 'Being alerted of some important events might help to react quickly in case of possible attack vector.', 'secupress' ),
			),
			'mark_as_pro' => $should_be_pro,
		),
		'schedules'       => array(
			'title'       => __( 'Schedules', 'secupress' ),
			'icon'        => 'schedule',
			'summaries'   => array(
				'small'  => __( 'Automate your scans', 'secupress' ),
				'normal' => __( 'Scheduling recurrent tasks can be very useful to gain time and stay safe. At least each week a backup should be done, same for a full scan of vulnerabilities and file changes.', 'secupress' ),
			),
			'description' => array(
				__( 'Scheduling recurrent tasks can be very useful to gain time and stay safe. At least each week a backup should be done, same for a full scan of vulnerabilities and file changes.', 'secupress' ),
			),
			'with_reset_box' => false,
			'mark_as_pro'    => $should_be_pro,
		),
		'services'        => array(
			'title'       => __( 'Services', 'secupress' ),
			'icon'        => 'services',
			'summaries'   => array(
				'small'  => __( 'Pro configuration', 'secupress' ),
				'normal' => __( 'The page contains our services designed to help you with the plugin.', 'secupress' ),
			),
			'description' => array(
				__( 'The page contains our services designed to help you with the plugin.', 'secupress' ),
			),
			'with_reset_box' => false,
			'mark_as_pro'    => false,
		),
	);

	return $modules;
}


/**
 * Check whether a sub-module is active.
 *
 * @since 1.0
 *
 * @param (string) $module    A module.
 * @param (string) $submodule A sub-module.
 *
 * @return (bool)
 */
function secupress_is_submodule_active( $module, $submodule ) {
	$submodule         = sanitize_key( $submodule );
	$active_submodules = get_site_option( SECUPRESS_ACTIVE_SUBMODULES );

	if ( isset( $active_submodules[ $module ] ) ) {
		$active_submodules[ $module ] = array_flip( $active_submodules[ $module ] );
		return isset( $active_submodules[ $module ][ $submodule ] );
	}

	return false;
}


/**
 * Get a sub-module file path.
 *
 * @since 1.0
 *
 * @param (string) $module    The module.
 * @param (string) $submodule The sub-module.
 *
 * @return (string|bool) The file path on success. False on failure.
 */
function secupress_get_submodule_file_path( $module, $submodule ) {
	$file_path = sanitize_key( $module ) . '/plugins/' . sanitize_key( $submodule ) . '.php';

	if ( defined( 'SECUPRESS_PRO_MODULES_PATH' ) && file_exists( SECUPRESS_PRO_MODULES_PATH . $file_path ) ) {
		return SECUPRESS_PRO_MODULES_PATH . $file_path;
	}

	if ( file_exists( SECUPRESS_MODULES_PATH . $file_path ) ) {
		return SECUPRESS_MODULES_PATH . $file_path;
	}

	return false;
}
