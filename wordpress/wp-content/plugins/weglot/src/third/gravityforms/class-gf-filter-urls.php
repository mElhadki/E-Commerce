<?php

namespace WeglotWP\Third\Gravityforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Filter_Url_Weglot;

/**
 * @since 3.0.0
 */
class GF_Filter_Urls implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 * @return void
	 */
	public function __construct() {
		$this->gf_active_services        = weglot_get_service( 'Gf_Active' );
	}

	/**
	 * @since 3.0.0
	 * @see Hooks_Interface_Weglot
	 *
	 * @return void
	 */
	public function hooks() {
		if ( ! $this->gf_active_services->is_active() ) {
			return;
		}

		add_filter( 'gform_confirmation', [ $this, 'weglot_gform_confirmation' ] );
	}

	/**
	 * @since 3.0.0
	 * @param array $data
	 * @return array
	 */
	public function weglot_gform_confirmation( $data ) {
		if( ! is_array( $data ) ){
			return $data;
		}

		if( ! array_key_exists( 'redirect', $data ) ){
			return $data;
		}

		$data['redirect'] = Helper_Filter_Url_Weglot::filter_url_with_ajax($data['redirect']);
		return $data;

	}

}
