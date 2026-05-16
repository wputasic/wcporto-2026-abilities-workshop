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
			// TODO 1: Add an 'output_schema' that requires:
			//   - 'greeting' (string)
			//   - 'length'   (integer)
			//   No additional properties.

			'execute_callback'    => static function ( array $input ): array {
				// TODO 2: Fix this engine. It currently returns the wrong shape —
				// the schema requires 'greeting' and 'length', not 'wrong_key'.
				return [
					'wrong_key' => sprintf( 'Hello, %s!', $input['name'] ),
				];
			},
		] );
	}
}
