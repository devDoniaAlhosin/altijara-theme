<?php
/**
 * Homepage sections — sanitize
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function altijara_sanitize_section_row( $id, $row, $def = array() ) {
	$widgets = array_keys( altijara_get_widget_registry() );
	$hero    = in_array( $id, altijara_hero_section_ids(), true );

	$widget = isset( $row['widget'] ) ? sanitize_key( $row['widget'] ) : ( $def['widget'] ?? 'article_grid' );
	if ( ! in_array( $widget, $widgets, true ) ) {
		$widget = ! empty( $row['layout'] ) ? altijara_layout_to_widget( $row['layout'] ) : ( $def['widget'] ?? 'article_grid' );
	}
	if ( ! in_array( $widget, $widgets, true ) ) {
		$widget = $def['widget'] ?? 'article_grid';
	}

	$manual_raw = isset( $row['manual_posts'] ) ? (string) $row['manual_posts'] : '';
	$manual_ids = array_values(
		array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $manual_raw ) ?: array() ) )
	);
	$issue_raw = isset( $row['issue_ids'] ) ? (string) $row['issue_ids'] : '';
	$issue_ids = array_values(
		array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $issue_raw ) ?: array() ) )
	);

	$orderby = isset( $row['orderby'] ) ? sanitize_key( $row['orderby'] ) : ( $def['orderby'] ?? 'date' );
	if ( ! in_array( $orderby, array( 'date', 'modified', 'title', 'rand', 'menu_order', 'comment_count' ), true ) ) {
		$orderby = 'date';
	}

	$variant = isset( $row['variant'] ) ? sanitize_key( $row['variant'] ) : ( $def['variant'] ?? 'default' );
	$wdef    = altijara_get_widget_definition( $widget );
	if ( $wdef && ! empty( $wdef['variants'] ) && ! isset( $wdef['variants'][ $variant ] ) ) {
		$variant = 'default';
	}

	$view_period = isset( $row['view_period'] ) ? sanitize_key( $row['view_period'] ) : ( $def['view_period'] ?? 'all' );
	if ( ! in_array( $view_period, array( 'day', 'week', 'month', 'year', 'all' ), true ) ) {
		$view_period = 'all';
	}

	$source = isset( $row['source'] ) ? sanitize_key( $row['source'] ) : ( $def['source'] ?? 'posts' );
	if ( ! in_array( $source, array( 'issues', 'posts' ), true ) ) {
		$source = 'posts';
	}

	$cat = isset( $row['category'] ) ? sanitize_text_field( $row['category'] ) : ( $def['category'] ?? '' );
	// Prefer numeric term IDs from the live category picker.
	if ( is_numeric( $cat ) ) {
		$cat = (string) absint( $cat );
	}

	$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : ( $def['label'] ?? $id );
	$label = altijara_strip_hero_label_prefix( $label );

	return array(
		'zone'         => $hero ? 'hero' : 'body',
		'widget'       => $widget,
		'layout'       => altijara_widget_to_layout( $widget ),
		'label'        => $label,
		'visible'      => ! empty( $row['visible'] ),
		'category'     => $cat,
		'tag'          => isset( $row['tag'] ) ? absint( $row['tag'] ) : 0,
		'manual_posts' => $manual_ids,
		'count'        => isset( $row['count'] ) ? max( 1, min( 20, (int) $row['count'] ) ) : (int) ( $def['count'] ?? 4 ),
		'offset'       => isset( $row['offset'] ) ? max( 0, min( 100, (int) $row['offset'] ) ) : 0,
		'orderby'      => $orderby,
		'order'        => ( isset( $row['order'] ) && 'ASC' === strtoupper( (string) $row['order'] ) ) ? 'ASC' : 'DESC',
		'variant'      => $variant,
		'source'       => $source,
		'issue_ids'    => $issue_ids,
		'view_period'  => $view_period,
		'pair_with'    => isset( $row['pair_with'] ) ? sanitize_key( $row['pair_with'] ) : ( $def['pair_with'] ?? '' ),
	);
}

/**
 * Sanitize posted section settings (supports dynamic body instances).
 *
 * @param array<string, mixed> $raw   Raw POST sections.
 * @param array<int, string>   $order Optional body order from POST.
 * @return array{sections:array<string,array<string,mixed>>,order:string[]}
 */
function altijara_sanitize_sections_input( $raw, $order = array() ) {
	$defaults = altijara_default_sections();
	$out      = array();
	$hero_ids = altijara_hero_section_ids();

	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	foreach ( $hero_ids as $hid ) {
		$row        = isset( $raw[ $hid ] ) && is_array( $raw[ $hid ] ) ? $raw[ $hid ] : array();
		$out[ $hid ] = altijara_sanitize_section_row( $hid, $row, $defaults[ $hid ] ?? array() );
	}

	$body_ids = array();
	if ( is_array( $order ) && ! empty( $order ) ) {
		foreach ( $order as $oid ) {
			$oid = sanitize_key( $oid );
			if ( $oid && ! in_array( $oid, $hero_ids, true ) && isset( $raw[ $oid ] ) ) {
				$body_ids[] = $oid;
			}
		}
	}
	foreach ( array_keys( $raw ) as $rid ) {
		$rid = sanitize_key( $rid );
		if ( ! $rid || in_array( $rid, $hero_ids, true ) || in_array( $rid, $body_ids, true ) ) {
			continue;
		}
		if ( is_array( $raw[ $rid ] ) ) {
			$body_ids[] = $rid;
		}
	}

	foreach ( $body_ids as $bid ) {
		$row         = is_array( $raw[ $bid ] ) ? $raw[ $bid ] : array();
		$out[ $bid ] = altijara_sanitize_section_row( $bid, $row, $defaults[ $bid ] ?? array() );
	}

	return array(
		'sections' => $out,
		'order'    => $body_ids,
	);
}
