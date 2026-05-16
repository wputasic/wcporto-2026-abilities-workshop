<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_01_Abilities {

	public static function register(): void {
		wp_register_ability( 'wcporto/say-hello', [
			'label'               => __( 'Say Hello', 'wcporto-01-hello-ability' ),
			'description'         => __( 'Returns a fixed greeting.', 'wcporto-01-hello-ability' ),
			'category'            => 'wcporto-workshop-actions',
			'permission_callback' => '__return_true',
			'execute_callback'    => static function (): array {
				return [ 'message' => 'Hello, WordCamp Porto 2026!' ];
			},
			'meta'                => [
				'show_in_rest' => true,
			],
		] );
	}
}
