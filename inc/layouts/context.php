<?php
/**
 * Runtime layout overrides (sections map + archive context).
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var array<string, array<string, mixed>>|null
 */
$GLOBALS['altijara_layout_sections_override'] = null;

/**
 * @var array{category?:int,surface?:string}|null
 */
$GLOBALS['altijara_layout_context'] = null;

/**
 * Run a callback with a temporary sections map (for rendering a saved layout).
 *
 * @param array<string, array<string, mixed>> $sections Sections map.
 * @param callable                            $callback Callback.
 * @return mixed
 */
function altijara_with_layout_sections( array $sections, $callback ) {
	$prev = $GLOBALS['altijara_layout_sections_override'] ?? null;
	$GLOBALS['altijara_layout_sections_override'] = $sections;
	try {
		return call_user_func( $callback );
	} finally {
		$GLOBALS['altijara_layout_sections_override'] = $prev;
	}
}

/**
 * Run a callback with archive/layout context (e.g. force category).
 *
 * @param array<string, mixed> $context Context.
 * @param callable             $callback Callback.
 * @return mixed
 */
function altijara_with_layout_context( array $context, $callback ) {
	$prev = $GLOBALS['altijara_layout_context'] ?? null;
	$GLOBALS['altijara_layout_context'] = $context;
	try {
		return call_user_func( $callback );
	} finally {
		$GLOBALS['altijara_layout_context'] = $prev;
	}
}

/**
 * Current layout context.
 *
 * @return array<string, mixed>
 */
function altijara_get_layout_context() {
	$ctx = $GLOBALS['altijara_layout_context'] ?? null;
	return is_array( $ctx ) ? $ctx : array();
}
