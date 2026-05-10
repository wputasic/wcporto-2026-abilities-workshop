<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_03_Abilities {

	public static function register(): void {
		// TODO: Register ability 'wcporto/admin-only-greeting' with:
		//   - permission_callback: returns current_user_can( 'manage_options' )
		//   - execute_callback:    returns [
		//                            'message' => 'Welcome, administrator!',
		//                            'user_id' => get_current_user_id(),
		//                          ]
		//
		// Hint:
		// 'permission_callback' => static function ( array $input ): bool {
		//     return current_user_can( 'manage_options' );
		// },
	}
}
