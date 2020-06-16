<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom URL services
 *
 * @since 2.3.0
 */
class Custom_Url_Service_Weglot {

	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
		$this->request_url_services      = weglot_get_service( 'Request_Url_Service_Weglot' );
	}

	/**
	 * @since 2.3.0
	 * @param string $key_code
	 * @param boolean $add_no_redirect
	 * @return string
	 */
	public function get_link( $key_code, $add_no_redirect = true ) {

		if( apply_filters( 'weglot_need_reset_postdata', false ) ) {
			wp_reset_postdata();
		}

		$weglot_url                = $this->request_url_services->get_weglot_url();
		$request_without_language  = array_filter( explode( '/', $weglot_url->getPath() ), 'strlen' );
		$index_entries             = count( $request_without_language );
		$custom_urls               = $this->option_services->get_option( 'custom_urls' );
		$url_lang                  = $weglot_url->getForLanguage( $key_code );
		$original_language         = weglot_get_original_language();
		$current_language          = weglot_get_current_language();
		$condition_test_custom_url = isset( $request_without_language[ $index_entries ] ) && ! is_admin() && ! empty( $custom_urls ) && ! is_post_type_archive() && ! is_category() && ! is_tax() && ! is_archive() && ! is_front_page() && ! is_home();

		if ( apply_filters( 'weglot_condition_test_custom_url', $condition_test_custom_url, $url_lang, $key_code ) ) {
			$slug_in_work             = $request_without_language[ $index_entries ];
            $original_slug_in_work         = $slug_in_work;

            $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
            if($current_language !== $original_language) {
                $toTranslateLanguageIso = ($key = array_search($current_language,$language_code_rewrited)) ? $key:$current_language;
                if ( isset( $custom_urls[ $toTranslateLanguageIso ] )) {
                    $value_slug = array_key_exists($slug_in_work, $custom_urls[$toTranslateLanguageIso]) ? $custom_urls[$toTranslateLanguageIso][$slug_in_work] : false;
                    if ( false !== $value_slug ) {
                        $original_slug_in_work = $value_slug;
                    }
                }
			}

            $toTranslateLanguageIso = ($key = array_search($key_code,$language_code_rewrited)) ? $key:$key_code;
            if ( isset( $custom_urls[ $toTranslateLanguageIso ] )) {
                $key_slug = array_search( $original_slug_in_work, $custom_urls[ $toTranslateLanguageIso ] ); //phpcs:ignore
                if ( false !== $key_slug ) {
                    $url_lang = str_replace($slug_in_work, $key_slug, $url_lang);
                }
            }
            else {

                $url_lang = str_replace( $slug_in_work, $original_slug_in_work, $url_lang );
            }
		}

		$link_button = apply_filters( 'weglot_link_language', $url_lang, $key_code );

		if (
			weglot_has_auto_redirect() &&
			strpos( $link_button, 'no_lredirect' ) === false && // If not exist
			( is_home() || is_front_page() ) && // Only for homepage
			$key_code === $original_language && // Only for original language
			$add_no_redirect // Example : for hreflang service
			) {
			$link_button .= '?no_lredirect=true';
		} else {
			$link_button = preg_replace( '#\?no_lredirect=true$#', '', $link_button ); // Remove ending "?no_lredirect=true"
		}

		return apply_filters( 'weglot_get_link_with_key_code', $link_button );
	}


	/**
	 * @since 2.3.0
	 * @return string
	 * @param mixed $key_code
	 */
	public function get_link_button_with_key_code( $key_code ) {
		$link_button = $this->get_link( $key_code );

		return apply_filters( 'weglot_get_link_button_with_key_code', $link_button );
	}
}
