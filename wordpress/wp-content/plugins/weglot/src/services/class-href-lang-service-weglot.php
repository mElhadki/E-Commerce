<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.3.0
 */
class Href_Lang_Service_Weglot {
	protected $languages = null;

	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->custom_url_services          = weglot_get_service( 'Custom_Url_Service_Weglot' );
		$this->request_url_services         = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->private_language_service     = weglot_get_service( 'Private_Language_Service_Weglot' );
	}

	/**
	 * @since 2.3.0
	 */
	public function generate_href_lang_tags() {
		$destination_languages = weglot_get_all_languages_configured();
		$render                = "\n";
		if ( $this->private_language_service->private_mode_for_all_languages() ) {
			return apply_filters( 'weglot_href_lang', $render );
		}

		try {
			foreach ( $destination_languages as $language ) {
				if ( $this->private_language_service->is_active_private_mode_for_lang( $language ) ) {
					continue;
				}

				$url = $this->custom_url_services->get_link( $language, false );
				$render .= '<link rel="alternate" hreflang="' . $language . '" href="' . esc_url($url) . '"/>' . "\n";
			}
		} catch ( \Exception $e ) {
			$render = $this->request_url_services->get_weglot_url()->generateHrefLangsTags();
		}

		return apply_filters( 'weglot_href_lang', $render );
	}
}
