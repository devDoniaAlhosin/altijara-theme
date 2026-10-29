<?php
/**
 * Social login (Google / Facebook / Apple) — theme options + OAuth redirect flow.
 *
 * @package Altijara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/oauth/settings.php';
require_once __DIR__ . '/oauth/user-link.php';
require_once __DIR__ . '/oauth/providers/google.php';
require_once __DIR__ . '/oauth/providers/facebook.php';
require_once __DIR__ . '/oauth/providers/apple.php';
require_once __DIR__ . '/oauth/flow.php';
