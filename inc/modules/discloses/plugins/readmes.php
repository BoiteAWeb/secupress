<?php
/*
Module Name: Protect readme's.
Description: Deny access to all <code>readme</code> and <code>changelog</code> files.
Main Module: discloses
Author: SecuPress
Version: 1.0
*/
defined( 'SECUPRESS_VERSION' ) or die( 'Cheatin&#8217; uh?' );

/*------------------------------------------------------------------------------------------------*/
/* ACTIVATION / DEACTIVATION ==================================================================== */
/*------------------------------------------------------------------------------------------------*/

/**
 * On module activation, maybe write the rules.
 *
 * @since 1.0
 */
add_action( 'secupress_activate_plugin_' . basename( __FILE__, '.php' ), 'secupress_protect_readmes_activation' );

function secupress_protect_readmes_activation() {
	global $is_apache, $is_nginx, $is_iis7;

	// Apache
	if ( $is_apache ) {
		$rules = secupress_protect_readmes_apache_rules();
	}
	// IIS7
	elseif ( $is_iis7 ) {
		$rules = secupress_protect_readmes_iis7_rules();
	}
	// Nginx
	elseif ( $is_nginx ) {
		$rules = secupress_protect_readmes_nginx_rules();
	}
	// Not supported.
	else {
		$rules = '';
	}

	secupress_add_module_rules_or_notice_and_deactivate( array(
		'rules'     => $rules,
		'marker'    => 'readme_discloses',
		'module'    => 'discloses',
		'submodule' => basename( __FILE__, '.php' ),
		'title'     => __( 'Protect readme\'s', 'secupress' ),
	) );
}


/**
 * On module deactivation, maybe remove rewrite rules from the `.htaccess`/`web.config` file.
 *
 * @since 1.0
 *
 * @param (array) $args Some parameters.
 */
add_action( 'secupress_deactivate_plugin_' . basename( __FILE__, '.php' ), 'secupress_protect_readmes_deactivate' );

function secupress_protect_readmes_deactivate( $args = array() ) {
	if ( empty( $args['no-tests'] ) ) {
		secupress_remove_module_rules_or_notice( 'readme_discloses', __( 'Protect readme\'s', 'secupress' ) );
	}
}


/**
 * On SecuPress activation, add the rules to the list of the rules to write.
 *
 * @since 1.0
 *
 * @param (array) $rules Other rules to write.
 *
 * @return (array) Rules to write.
 */
add_filter( 'secupress.plugins.activation.write_rules', 'secupress_protect_readmes_plugin_activate', 10, 2 );

function secupress_protect_readmes_plugin_activate( $rules ) {
	global $is_apache, $is_nginx, $is_iis7;
	$marker = 'readme_discloses';

	if ( $is_apache ) {
		$rules[ $marker ] = secupress_protect_readmes_apache_rules();
	} elseif ( $is_iis7 ) {
		$rules[ $marker ] = array( 'nodes_string' => secupress_protect_readmes_iis7_rules() );
	} elseif ( $is_nginx ) {
		$rules[ $marker ] = secupress_protect_readmes_nginx_rules();
	}

	return $rules;
}


/*------------------------------------------------------------------------------------------------*/
/* RULES ======================================================================================== */
/*------------------------------------------------------------------------------------------------*/

/**
 * Protect readme's: get rules for apache.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_protect_readmes_apache_rules() {
	$pattern = '(README|CHANGELOG|readme|changelog)\.(TXT|MD|HTML|txt|md|html)$';

	$rules  = "<IfModule mod_rewrite.c>\n";
	$rules .= "    RewriteEngine On\n";
	$rules .= "    RewriteRule (/|^)$pattern [R=404,L]\n"; // NC flag, why you no work?
	$rules .= "</IfModule>\n";
	$rules .= "<IfModule !mod_rewrite.c>\n";
	$rules .= "    <FilesMatch \"^$pattern\">\n";
	$rules .= "        deny from all\n";
	$rules .= "    </FilesMatch>\n";
	$rules .= "</IfModule>\n";

	return $rules;
}


/**
 * Protect readme's: get rules for iis7.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_protect_readmes_iis7_rules() {
	$marker = 'readme_discloses';
	$spaces = str_repeat( ' ', 8 );
	$bases  = secupress_get_rewrite_bases();
	$match  = '^' . $bases['home_from'] . '(.*/)?(readme|changelog)\.(txt|md|html)$';

	$rules  = "<rule name=\"SecuPress $marker\" stopProcessing=\"true\">\n";
	$rules .= "$spaces  <match url=\"$match\"/ ignoreCase=\"true\">\n";
	$rules .= "$spaces  <action type=\"CustomResponse\" statusCode=\"404\"/>\n";
	$rules .= "$spaces</rule>";

	return $rules;
}


/**
 * Protect readme's: get rules for nginx.
 *
 * @since 1.0
 *
 * @return (string)
 */
function secupress_protect_readmes_nginx_rules() {
	$marker  = 'readme_discloses';
	$pattern = '(readme|changelog)\.(txt|md|html)$';
	$base    = secupress_get_rewrite_bases();
	$base    = rtrim( $bases['home_from'], '/' );

	// http://nginx.org/en/docs/http/ngx_http_core_module.html#location
	$rules  = "
server {
	# BEGIN SecuPress $marker
	location ~* ^$base(/|/.+/)$pattern {
		return 404;
	}
	# END SecuPress
}";

	return trim( $rules );
}