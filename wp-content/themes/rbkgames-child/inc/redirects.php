<?php
/**
 * URL integrity: 301 redirects for URL variants of the original site.
 *
 * @package rbkgames-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'template_redirect',
	function () {
		if ( is_admin() ) {
			return;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$qs   = (string) wp_parse_url( $uri, PHP_URL_QUERY );
		$to   = '';

		if ( preg_match( '#^/index\.php/?$#', $path ) || preg_match( '#^/page/1/?$#', $path ) ) {
			$to = home_url( '/' );
		} elseif ( preg_match( '#^(/.+?)/(?:page/1|amp)/?$#', $path, $m ) ) {
			$to = home_url( trailingslashit( $m[1] ) );
		} elseif ( preg_match( '#^/editor_uploads/(.+)$#', $path, $m ) && is_404() ) {
			$to = content_url( '/uploads/' . $m[1] );
		} elseif ( preg_match( '#/{2,}#', $path ) ) {
			$to = home_url( '/' . ltrim( preg_replace( '#/{2,}#', '/', $path ), '/' ) );
		}

		if ( $to ) {
			wp_safe_redirect( $qs ? $to . '?' . $qs : $to, 301 );
			exit;
		}
	},
	1
);
