<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_04_Categories {

	public static function register(): void {
		wp_register_ability_category( 'wcporto/workshop-actions', [
			'label'       => __( 'Workshop Actions', 'wcporto-04-output-schema' ),
			'description' => __( 'Abilities registered during the WordCamp Porto 2026 workshop.', 'wcporto-04-output-schema' ),
		] );
	}
}
