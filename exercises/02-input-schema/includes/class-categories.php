<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_02_Categories {

	public static function register(): void {
		wp_register_ability_category( 'wcporto/workshop-actions', [
			'label'       => __( 'Workshop Actions', 'wcporto-02-input-schema' ),
			'description' => __( 'Abilities registered during the WordCamp Porto 2026 workshop.', 'wcporto-02-input-schema' ),
		] );
	}
}
