<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_06_Abilities {

	public static function register(): void {
		wp_register_ability( 'wcporto/echo-with-metadata', [
			'label'               => __( 'Echo With Metadata', 'wcporto-06-rest-and-ai-tool' ),
			'description'         => __( 'Returns the input message along with its character length and a server timestamp. Useful as a smoke test for AI agents and MCP clients.', 'wcporto-06-rest-and-ai-tool' ),
			'category'            => 'wcporto/agent-tools',
			'permission_callback' => static function ( array $input ): bool {
				return is_user_logged_in();
			},
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'message' => [
						'type'      => 'string',
						'minLength' => 1,
						'maxLength' => 200,
					],
				],
				'required'             => [ 'message' ],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'echoed'      => [ 'type' => 'string' ],
					'length'      => [ 'type' => 'integer' ],
					'received_at' => [ 'type' => 'string' ],
				],
				'required'             => [ 'echoed', 'length', 'received_at' ],
				'additionalProperties' => false,
			],
			'execute_callback'    => static function ( array $input ): array {
				$message = (string) $input['message'];

				return [
					'echoed'      => $message,
					'length'      => mb_strlen( $message ),
					'received_at' => gmdate( 'c' ),
				];
			},
		] );
	}
}
