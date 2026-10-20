<?php
/**
 * Small front-end helpers for page meta values.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tel href helper (strip spaces).
 *
 * @param string $phone Phone display string.
 * @return string
 */
function altijara_phone_href( $phone ) {
	$digits = preg_replace( '/[^\d+]/', '', (string) $phone );
	return $digits ? 'tel:' . $digits : '#';
}
