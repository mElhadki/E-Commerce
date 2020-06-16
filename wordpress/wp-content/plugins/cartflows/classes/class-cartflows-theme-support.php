<?php
/**
 * Adding the action on init to load the theme compatibility files.
 *
 * @package CartFlows
 */

add_action( 'init', 'load_cartflows_theme_support', 100 );

/**
 * Load popular theme fallback files.
 *
 * @since X.X.X
 *
 * @return void
 */
function load_cartflows_theme_support() {

	if ( defined( 'ASTRA_THEME_VERSION' ) ) {

		/**
		 * Astra
		 */
		include_once CARTFLOWS_DIR . 'theme-support/astra/class-cartflows-astra-compatibility.php';
	}

}
