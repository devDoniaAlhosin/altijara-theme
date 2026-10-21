<?php
/**
 * Widget: issues lead hero.
 *
 * @package Altijara
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$posts = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$issue = ! empty( $posts[0] ) && $posts[0] instanceof WP_Post ? $posts[0] : null;
get_template_part(
	'template-parts/archive/issues-lead',
	null,
	array(
		'issue' => $issue,
	)
);
