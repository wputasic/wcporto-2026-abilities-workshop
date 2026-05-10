<?php
/**
 * Plugin Name: WCPorto 02 — Input Schema
 * Description: WordCamp Porto 2026 workshop exercise 02 — add input_schema validation (Gate 1).
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

add_action( 'wp_abilities_api_categories_init', [ 'WCPorto_02_Categories', 'register' ] );
add_action( 'wp_abilities_api_init',            [ 'WCPorto_02_Abilities',  'register' ] );
