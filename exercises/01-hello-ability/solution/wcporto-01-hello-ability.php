<?php
/**
 * Plugin Name: WCPorto 01 — Hello Ability (Solution)
 * Description: WordCamp Porto 2026 workshop exercise 01 — completed reference implementation.
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

add_action( 'wp_abilities_api_categories_init', [ 'WCPorto_01_Categories', 'register' ] );
add_action( 'wp_abilities_api_init',            [ 'WCPorto_01_Abilities',  'register' ] );
