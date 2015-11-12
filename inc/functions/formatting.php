<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Create a URL to easily access to our pages.
 *
 * @since 1.0
 *
 * @param (string) $page  : the last word of the secupress page slug.
 * @param (string) $module: the required module.
 *
 * @return (string) The URL.
 */
function secupress_admin_url( $page, $module = '' ) {
	$module = $module ? '&module=' . sanitize_key( $module ) : '';
	$page   = str_replace( '&', '_', $page );
	$url    = 'admin.php?page=secupress_' . sanitize_key( $page ) . $module;

	return is_multisite() ? network_admin_url( $url ) : admin_url( $url );
}


/**
 * Get the user capability required to work with the plugin.
 *
 * @since 1.0
 *
 * @return (string) The capability.
 */
function secupress_get_capability() {
	return is_multisite() ? 'manage_network_options' : 'administrator';
}


/**
 * Like in_array but for nested arrays.
 *
 * @since 1.0
 *
 * @return (bool)
 */
if ( ! function_exists( 'in_array_deep' ) ) :
	function in_array_deep( $needle, $haystack ) {
		if ( $haystack ) {
			foreach ( $haystack as $item ) {
				if ( $item == $needle || ( is_array( $item ) && in_array_deep( $needle, $item ) ) ) {
					return true;
				}
			}
		}
		return false;
	}
endif;


/**
 * Return the path to a class.
 *
 * @since 1.0
 *
 * @param (string) $prefix         : only one possible value so far: "scan".
 * @param (string) $class_name_part: the classes name is built as follow: "SecuPress_{$prefix}_{$class_name_part}".
 *
 * @return (string) Path of the class.
 */
function secupress_class_path( $prefix, $class_name_part = '' ) {
	$folders = array(
		'scan'     => 'scanners',
		'settings' => 'settings',
	);

	$prefix = strtolower( str_replace( '_', '-', $prefix ) );

	$class_name_part = strtolower( str_replace( '_', '-', $class_name_part ) );
	$class_name_part = $class_name_part ? '-' . $class_name_part : '';

	return SECUPRESS_CLASSES_PATH . $folders[ $prefix ] . '/class-secupress-' . $prefix . $class_name_part . '.php';
}


/**
 * Require a class.
 *
 * @since 1.0
 *
 * @param (string) $prefix         : only one possible value so far: "scan".
 * @param (string) $class_name_part: the classes name is built as follow: "SecuPress_{$prefix}_{$class_name_part}".
*/
function secupress_require_class( $prefix, $class_name_part = '' ) {
	$path = secupress_class_path( $prefix, $class_name_part );

	if ( $path ) {
		require_once( $path );
	}
}


/**
 * Is current WordPress version older than X.X.X?
 *
 * @since 1.0
 *
 * @param (string) $version: the version to test.
 *
 * @return (bool) Result of the `version_compare()`.
 */
function secupress_wp_version_is( $version ) {
	global $wp_version;
	static $is = array();

	if ( isset( $is[ $version ] ) ) {
		return $is[ $version ];
	}

	return ( $is[ $version ] = version_compare( $wp_version, $version ) >= 0 );
}


/**
 * Return the "unaliased" version of an email address.
 *
 * @param (string) $email
 * @since 1.0
 * @return (string)
 **/
function secupress_remove_email_alias( $email ) {
	$provider = strstr( $email, '@' );
	$email    = strstr( $email, '@', true );
	$email    = explode( '+', $email );
	$email    = reset( $email );
	$email    = str_replace( '.', '', $email );
	return $email . $provider;
}


/**
 * Return the email "example@example.com" like "e%x%a%m%p%l%e%@example.com"
 *
 * @param (string) $email
 * @since 1.0
 * @return (string)
 **/
function secupress_prepare_email_for_like_search( $email ) {
	global $wpdb;
	$email    = secupress_remove_email_alias( $email );
	$provider = strstr( $email, '@' );
	$email    = $wpdb->esc_like( strstr( $email, '@', true ) );
	$email    = str_split( $email );
	$email    = implode( '%', $email );
	return $email . '%' . $provider;
}


/**
 * Store, get or delete static data.
 *
 * Getter:   no need to provide a second parameter.
 * Setter:   provide a second parameter for the value.
 * Deletter: provide null as second parameter to remove the previous value.
 *
 * @since 1.0
 *
 * @param (string) $key:  An identifier key.
 *
 * @return (mixed) The stored data or null.
 */
function secupress_cache_data( $key ) {
	static $datas = array();

	$func_get_args = func_get_args();

	if ( array_key_exists( 1, $func_get_args ) ) {
		if ( null === $func_get_args[1] ) {
			unset( $datas[ $key ] );
		} else {
			$datas[ $key ] = $func_get_args[1];
		}
	}

	return isset( $datas[ $key ] ) ? $datas[ $key ] : null;
}