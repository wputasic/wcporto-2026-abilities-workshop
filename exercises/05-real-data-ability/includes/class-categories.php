<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_05_Categories {

	public static function register(): void {
		wp_register_ability_category( 'wcporto-content', [
			'label'       => __( 'Content Operations', 'wcporto-05-real-data-ability' ),
			'description' => __( 'Abilities for reading and writing posts.', 'wcporto-05-real-data-ability' ),
		] );
	}
}
