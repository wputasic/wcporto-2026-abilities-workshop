<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_02_Abilities {

	public static function register(): void {
		wp_register_ability( 'wcporto/say-hello-to', [
			'label'               => __( 'Say Hello To Someone', 'wcporto-02-input-schema' ),
			'description'         => __( 'Returns a personalized greeting.', 'wcporto-02-input-schema' ),
			'category'            => 'wcporto-workshop-actions',
			'permission_callback' => '__return_true',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'name' => [
						'type'      => 'string',
						'minLength' => 1,
						'maxLength' => 50,
					],
				],
				'required'             => [ 'name' ],
				'additionalProperties' => false,
			],
			'execute_callback'    => static function ( array $input ): array {
				return [ 'message' => sprintf( 'Hello, %s!', $input['name'] ) ];
			},
			'meta'                => [
				'show_in_rest' => true,
			],
		] );
	}
}
