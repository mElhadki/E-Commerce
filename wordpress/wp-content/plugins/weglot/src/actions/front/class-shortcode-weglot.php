<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 *
 * @since 2.0
 */
class Shortcode_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->button_services      = weglot_get_service( 'Button_Service_Weglot' );
		$this->request_url_services = weglot_get_service( 'Request_Url_Service_Weglot' );

		add_shortcode( 'weglot_switcher', [ $this, 'weglot_switcher_callback' ] );
	}

	/**
	 * @see weglot_switcher
	 * @since 2.0
	 *
	 * @return string
	 */
	public function weglot_switcher_callback() {
		if ( ! $this->request_url_services->is_translatable_url() || ! weglot_current_url_is_eligible() ) {
			return;
		}

		return $this->button_services->get_html( 'weglot-shortcode' ); //phpcs:ignore
	}
}
