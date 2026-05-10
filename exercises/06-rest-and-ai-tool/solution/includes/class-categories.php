<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPorto_06_Categories {

	public static function register(): void {
		wp_register_ability_category( 'wcporto/agent-tools', [
			'label'       => __( 'Agent Tools', 'wcporto-06-rest-and-ai-tool' ),
			'description' => __( 'Abilities designed to be discovered and invoked by AI agents.', 'wcporto-06-rest-and-ai-tool' ),
		] );
	}
}
