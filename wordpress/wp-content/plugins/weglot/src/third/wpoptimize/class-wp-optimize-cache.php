<?php

namespace WeglotWP\Third\WpOptimize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 * Wp_Optimize_Cache
 *
 * @since 3.1.4
 */
class Wp_Optimize_Cache implements Hooks_Interface_Weglot {

	/**
	 * @since 3.1.4
	 * @return void
	 */
	public function __construct() {
		$this->wp_optimize_active_services = weglot_get_service( 'Wp_Optimize_Active' );
	}

	/**
	 * @since 3.1.4
	 * @see Hooks_Interface_Weglot
	 * @return void
	 */
	public function hooks() {

		if ( ! $this->wp_optimize_active_services->is_active() ) {
			return;
		}

		add_filter( 'wpo_can_cache_page', [ $this, 'weglot_wpo_can_cache_page' ] );
	}


	/**
	 * @since 3.1.4
	 * @return bool
	 */
	public function weglot_wpo_can_cache_page( $can_cache_page ) {

		if ( weglot_get_original_language() !== weglot_get_current_language() ) {
			return false;
		}

		return $can_cache_page;
	}

}
