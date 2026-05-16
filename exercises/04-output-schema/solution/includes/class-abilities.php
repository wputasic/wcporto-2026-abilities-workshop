<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_04_Abilities {

	public static function register(): void {
		wp_register_ability( 'wcporto/structured-greeting', [
			'label'               => __( 'Structured Greeting', 'wcporto-04-output-schema' ),
			'description'         => __( 'Returns a greeting and its character length.', 'wcporto-04-output-schema' ),
			'category'            => 'wcporto-workshop-actions',
			'permission_callback' => '__return_true',
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'name' => [ 'type' => 'string', 'minLength' => 1, 'maxLength' => 50 ],
				],
				'required'             => [ 'name' ],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'greeting' => [ 'type' => 'string' ],
					'length'   => [ 'type' => 'integer' ],
				],
				'required'             => [ 'greeting', 'length' ],
				'additionalProperties' => false,
			],
			'execute_callback'    => static function ( array $input ): array {
				$greeting = sprintf( 'Hello, %s!', $input['name'] );

				return [
					'greeting' => $greeting,
					'length'   => mb_strlen( $greeting ),
				];
			},
			'meta'                => [
				'show_in_rest' => true,
			],
		] );
	}
}
