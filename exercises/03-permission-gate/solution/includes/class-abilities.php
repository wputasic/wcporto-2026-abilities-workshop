<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_03_Abilities {

	public static function register(): void {
		wp_register_ability( 'wcporto/admin-only-greeting', [
			'label'               => __( 'Admin-Only Greeting', 'wcporto-03-permission-gate' ),
			'description'         => __( 'Returns a greeting only for users who can manage options.', 'wcporto-03-permission-gate' ),
			'category'            => 'wcporto/workshop-actions',
			'permission_callback' => static function ( array $input ): bool {
				return current_user_can( 'manage_options' );
			},
			'execute_callback'    => static function ( array $input ): array {
				return [
					'message' => 'Welcome, administrator!',
					'user_id' => get_current_user_id(),
				];
			},
		] );
	}
}
