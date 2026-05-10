<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_05_Abilities {

	public static function register(): void {
		// TODO 1: Register 'wcporto/list-recent-posts'
		//   - input_schema: optional 'count' integer 1..20 (default 5)
		//   - permission_callback: requires the user to be logged in (is_user_logged_in())
		//   - output_schema: { posts: array of { id: integer, title: string, link: string } }
		//   - execute_callback: returns the latest published posts as plain arrays
		//
		// Example execute body:
		// $count = $input['count'] ?? 5;
		// $posts = get_posts( [ 'numberposts' => $count, 'post_status' => 'publish' ] );
		// return [ 'posts' => array_map( fn( $p ) => [
		//     'id'    => (int) $p->ID,
		//     'title' => (string) get_the_title( $p ),
		//     'link'  => (string) get_permalink( $p ),
		// ], $posts ) ];

		// TODO 2: Register 'wcporto/publish-draft'
		//   - input_schema: required 'post_id' integer
		//   - permission_callback: current_user_can( 'publish_posts' )
		//   - output_schema: { post_id: integer, status: string, link: string }
		//   - execute_callback: wp_update_post([ 'ID' => $id, 'post_status' => 'publish' ])
		//                       Then return id, status, and link. Return WP_Error if the post does not exist.
	}
}
