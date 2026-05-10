<?php
/**
 * Plugin Name: WCPorto 06 — REST and AI Tool
 * Description: WordCamp Porto 2026 workshop exercise 06 — register an AI-friendly ability and consume it via the built-in REST API.
 * Version:     1.0.0
 * Author:      WordCamp Porto 2026
 * License:     GPL-2.0-or-later
 * Requires at least: 6.9
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-categories.php';
require_once __DIR__ . '/includes/class-abilities.php';

add_action( 'wp_abilities_api_categories_init', [ 'WCPorto_06_Categories', 'register' ] );
add_action( 'wp_abilities_api_init',            [ 'WCPorto_06_Abilities',  'register' ] );
