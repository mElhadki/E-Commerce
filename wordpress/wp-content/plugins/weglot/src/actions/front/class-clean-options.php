<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;

class Clean_Options implements Hooks_Interface_Weglot {


	public function __construct() {
		$this->option_services         = weglot_get_service( 'Option_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'clean_options' ] );
	}



	/**
	 * @since 3.0.0
	 * @return void
	 */
	public function clean_options() {
		if( isset( $_GET['_weglot_clean_cache_cdn'] ) && 'true' === $_GET['_weglot_clean_cache_cdn'] ) {
			delete_transient( 'weglot_cache_cdn' );
		}
	}
}
