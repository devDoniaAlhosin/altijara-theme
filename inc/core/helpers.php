<?php
/**
 * Shared PHP helpers — loads focused core modules.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/urls.php';
require_once __DIR__ . '/media.php';
require_once __DIR__ . '/taxonomy-labels.php';
require_once __DIR__ . '/ads.php';
require_once __DIR__ . '/search.php';