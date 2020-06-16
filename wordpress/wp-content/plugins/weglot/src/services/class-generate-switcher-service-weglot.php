<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @since 2.3.0
 */
class Generate_Switcher_Service_Weglot {
	protected $string_version = '<!--Weglot %s-->';
	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->option_services            = weglot_get_service( 'Option_Service_Weglot' );
		$this->request_url_services       = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->language_services          = weglot_get_service( 'Language_Service_Weglot' );
		$this->custom_url_services        = weglot_get_service( 'Custom_Url_Service_Weglot' );
		$this->button_services            = weglot_get_service( 'Button_Service_Weglot' );
	}

	/**
	 * @since 2.3.0
	 *
	 * @param string $dom
	 * @return string
	 */
	public function replace_div_id( $dom ) {
		if ( strpos( $dom, '<div id="weglot_here"></div>' ) === false ) {
			return $dom;
		}

		$button_html  = $this->button_services->get_html( 'weglot-shortcode' );
		$dom          = str_replace( '<div id="weglot_here"></div>', $button_html, $dom );

		return apply_filters( 'weglot_replace_div_id', $dom );
	}

	/**
	 * @since 2.3.0
	 * @version 3.0.0
	 * @param string $dom
	 * @return string
	 */
     public function check_weglot_menu( $dom ) {
         return apply_filters( 'weglot_replace_weglot_menu', $dom );
     }

	/**
	 * @since 2.3.0
	 *
	 * @param string $dom
	 * @return string
	 */
	public function render_default_button( $dom ) {
		if ( strpos( $dom, 'weglot-language' ) !== false ) {
			return $dom;
		}

		// Place the button if not in the page
		$button_html  = $this->button_services->get_html( 'weglot-default' );
		$dom          = ( strpos( $dom, '</body>' ) !== false) ? str_replace( '</body>', $button_html . ' </body>', $dom ) : str_replace( '</footer>', $button_html . ' </footer>', $dom );

		return apply_filters( 'weglot_render_default_button', $dom );
	}

	/**
	 * @since 2.3.0
	 * @param string $dom
	 * @return string
	 */
	public function generate_switcher_from_dom( $dom ) {
		$dom = $this->replace_div_id( $dom );
		$dom = $this->check_weglot_menu( $dom );
		$dom = $this->render_default_button( $dom );

		return apply_filters( 'weglot_generate_switcher_from_dom', $dom );
	}
}
