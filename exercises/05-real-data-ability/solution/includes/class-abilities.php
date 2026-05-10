<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_05_Abilities {

	public static function register(): void {
		self::register_list_recent_posts();
		self::register_publish_draft();
	}

	private static function register_list_recent_posts(): void {
		wp_register_ability( 'wcporto/list-recent-posts', [
			'label'               => __( 'List Recent Posts', 'wcporto-05-real-data-ability' ),
			'description'         => __( 'Returns the most recent published posts.', 'wcporto-05-real-data-ability' ),
			'category'            => 'wcporto/content',
			'permission_callback' => static function ( array $input ): bool {
				return is_user_logged_in();
			},
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'count' => [ 'type' => 'integer', 'minimum' => 1, 'maximum' => 20, 'default' => 5 ],
				],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'posts' => [
						'type'  => 'array',
						'items' => [
							'type'                 => 'object',
							'properties'           => [
								'id'    => [ 'type' => 'integer' ],
								'title' => [ 'type' => 'string' ],
								'link'  => [ 'type' => 'string' ],
							],
							'required'             => [ 'id', 'title', 'link' ],
							'additionalProperties' => false,
						],
					],
				],
				'required'             => [ 'posts' ],
				'additionalProperties' => false,
			],
			'execute_callback'    => static function ( array $input ): array {
				$count = $input['count'] ?? 5;
				$posts = get_posts( [
					'numberposts' => $count,
					'post_status' => 'publish',
				] );

				return [
					'posts' => array_map(
						static fn( WP_Post $p ): array => [
							'id'    => (int) $p->ID,
							'title' => (string) get_the_title( $p ),
							'link'  => (string) get_permalink( $p ),
						],
						$posts
					),
				];
			},
		] );
	}

	private static function register_publish_draft(): void {
		wp_register_ability( 'wcporto/publish-draft', [
			'label'               => __( 'Publish Draft', 'wcporto-05-real-data-ability' ),
			'description'         => __( 'Publishes a draft post by ID.', 'wcporto-05-real-data-ability' ),
			'category'            => 'wcporto/content',
			'permission_callback' => static function ( array $input ): bool {
				return current_user_can( 'publish_posts' );
			},
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'post_id' => [ 'type' => 'integer', 'minimum' => 1 ],
				],
				'required'             => [ 'post_id' ],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'post_id' => [ 'type' => 'integer' ],
					'status'  => [ 'type' => 'string' ],
					'link'    => [ 'type' => 'string' ],
				],
				'required'             => [ 'post_id', 'status', 'link' ],
				'additionalProperties' => false,
			],
			'execute_callback'    => static function ( array $input ) {
				$post_id = (int) $input['post_id'];
				$post    = get_post( $post_id );

				if ( ! $post ) {
					return new WP_Error(
						'wcporto_post_not_found',
						sprintf( 'Post %d not found.', $post_id ),
						[ 'status' => 404 ]
					);
				}

				$updated = wp_update_post(
					[
						'ID'          => $post_id,
						'post_status' => 'publish',
					],
					true
				);

				if ( is_wp_error( $updated ) ) {
					return $updated;
				}

				return [
					'post_id' => $post_id,
					'status'  => 'publish',
					'link'    => (string) get_permalink( $post_id ),
				];
			},
		] );
	}
}
