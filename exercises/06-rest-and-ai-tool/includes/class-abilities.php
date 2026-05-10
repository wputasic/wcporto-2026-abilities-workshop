<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_06_Abilities {

	public static function register(): void {
		// TODO: Register ability 'wcporto/echo-with-metadata' with:
		//   - category:            'wcporto/agent-tools'
		//   - permission_callback: requires the user to be logged in
		//   - input_schema:        { message: string (minLength 1, maxLength 200), required }
		//   - output_schema:       { echoed: string, length: integer, received_at: string, all required }
		//   - execute_callback:    returns the echoed message, character length, and a current timestamp
		//
		// Tip — to make an ability *not* exposed via REST, pass:
		//   'meta' => [ 'show_in_rest' => false ]
		// (We want it exposed, so leave that out.)
	}
}
