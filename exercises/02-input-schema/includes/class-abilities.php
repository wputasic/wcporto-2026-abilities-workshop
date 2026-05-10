<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_02_Abilities {

	public static function register(): void {
		// TODO: Register ability 'wcporto/say-hello-to' with:
		//   - input_schema: an object that requires a 'name' string property
		//                   with minLength 1 and maxLength 50, no additional properties.
		//   - permission_callback: '__return_true'
		//   - execute_callback:    returns [ 'message' => 'Hello, '.$input['name'].'!' ]
		//
		// Hint — input_schema shape:
		// [
		//   'type'                 => 'object',
		//   'properties'           => [ 'name' => [ 'type' => 'string', 'minLength' => 1, 'maxLength' => 50 ] ],
		//   'required'             => [ 'name' ],
		//   'additionalProperties' => false,
		// ]
	}
}
