<?php
/**
 * Lower sidebar widget: advertisement.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = ( isset( $args ) && is_array( $args ) && isset( $args['config'] ) && is_array( $args['config'] ) )
	? $args['config']
	: array();

if ( function_exists( 'altijara_render_sidebar_ad' ) ) {
	altijara_render_sidebar_ad( $config );
}
