<?php
/**
 * Module Name: Ban 404 on .php
 * Description: Ban requests on any .php file
 * Main Module: firewall
 * Author: SecuPress
 * Version: 1.0
 */

defined( 'SECUPRESS_VERSION' ) or die( 'Cheatin&#8217; uh?' );

add_action( 'template_redirect', 'secupress_ban_404_php' );
/**
 * Ban IP if a 404 file if a .php one
 *
 * @since 1.0
 * @author Julio potier
 **/
function secupress_ban_404_php() {
	if ( is_404() && 'php' === pathinfo( basename( secupress_get_current_url( 'uri' ) ), PATHINFO_EXTENSION ) ) {
		secupress_ban_ip();
	}
}