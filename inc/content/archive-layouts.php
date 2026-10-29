<?php
/**
 * Category archive settings and the /issues/ renderer — loads focused modules.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/archive-settings.php';
require_once __DIR__ . '/issue-magazine.php';
require_once __DIR__ . '/issues-archive-render.php';