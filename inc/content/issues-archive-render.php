<?php
/**
 * Issues archive front render.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render shared issues archive UI (lead + carousels).
 *
 * @param array<string, mixed> $args Optional args.
 */
function altijara_render_issues_archive( $args = array() ) {
	unset( $args ); // Reserved for callers; layout pack owns the markup.

	// Theme Options pack, or theme preset (lead + carousel bands) when unset/reset.
	if ( ! function_exists( 'altijara_get_issues_layout' ) || ! function_exists( 'altijara_render_layout_rows' ) ) {
		return;
	}
	$pack = altijara_get_issues_layout( true );
	altijara_with_layout_context(
		array( 'surface' => 'issues' ),
		static function () use ( $pack ) {
			altijara_render_layout_rows( $pack );
		}
	);
}
