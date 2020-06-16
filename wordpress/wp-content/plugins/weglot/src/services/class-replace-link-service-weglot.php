<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class Replace_Link_Service_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->multisite_service = weglot_get_service( 'Multisite_Service_Weglot' );
		$this->option_service    = weglot_get_service( 'Option_Service_Weglot' );
	}

	/**
	 * Replace an URL
	 * @since 2.0
	 * @param string $url
	 * @return string
	 */
	public function replace_url( $url ) {
		$no_replace_condition = apply_filters( 'weglot_no_replace_url_condition', 'wp-content/uploads' );

		if ( strpos( $url, $no_replace_condition ) !== false ) {
			return $url;
		}

		$current_and_original  = weglot_get_current_and_original_language();
		$custom_urls           = $this->option_service->get_option( 'custom_urls' );

		$parsed_url = wp_parse_url( $url );
		$scheme     = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
		$host       = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$port       = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$user       = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
		$pass       = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
		$pass       = ($user || $pass) ? "$pass@" : '';
		$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '/';
		$query      = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
		$fragment   = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

		$current_language = $current_and_original['current'];

		if ( $current_and_original['current'] === $current_and_original['original'] ) {
			return $url;
		} else {
			$request_without_language     = array_filter( explode( '/', $path ), 'strlen' );
			$index_entries                = count( $request_without_language );

            $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
            $toTranslateLanguageIso = ($key = array_search($current_language,$language_code_rewrited)) ? $key:$current_language;
			if ( isset( $request_without_language[ $index_entries ] ) && ! is_admin() && ! empty( $custom_urls ) && isset( $custom_urls[ $toTranslateLanguageIso ] ) ) {
				$slug_in_work             = $request_without_language[ $index_entries ];
				$key_slug                 = array_search( $slug_in_work, $custom_urls[ $toTranslateLanguageIso ] ); //phpcs:ignore
				if ( false !== $key_slug && ! empty( $key_slug ) ) {
					$path = str_replace( $slug_in_work, $key_slug, $path );
				}
			}

			$url_translated = ( strlen( $path ) > 2 && strpos($path, "/$current_language/") !== false  ) ?
				"$scheme$user$pass$host$port$path$query$fragment" : "$scheme$user$pass$host$port/$current_language$path$query$fragment";

			foreach ( array_reverse( $this->multisite_service->get_list_of_network_path() ) as $np ) {
				if ( strlen( $np ) > 2 && strpos( $url_translated, $np ) !== false ) {
					$url_translated = str_replace(
						str_replace( '//', '/', '/' . $current_language . $np . '/' ),
						str_replace( '//', '/', $np . '/' . $current_language . '/' ),
						$url_translated
					);
				}
			}

			return $url_translated;
		}
	}

	/**
	 * Replace href in <a>
	 * @since 2.0
	 * @version 2.0.4
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_a( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language     = weglot_get_current_language();
		$no_replace_condition = apply_filters( 'weglot_no_replace_a_href', 'wp-content/uploads' );

		if ( strpos( $current_url, $no_replace_condition ) !== false ) {
			return $translated_page;
		}

		$translated_page = preg_replace( '/<a' . preg_quote( $sometags, '/' ) . 'href=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . preg_quote( $sometags2, '/' ) . '>/', '<a' . $sometags . 'href=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2 . $sometags2 . '>', $translated_page );

		return $translated_page;
	}

	/**
	 * Replace data-link attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_datalink( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<' . preg_quote( $sometags, '/' ) . 'data-link=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<' . $sometags . 'data-link=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace data-url attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_dataurl( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<' . preg_quote( $sometags, '/' ) . 'data-url=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<' . $sometags . 'data-url=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace data-cart-url attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_datacart( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<' . preg_quote( $sometags, '/' ) . 'data-cart-url=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<' . $sometags . 'data-cart-url=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace form action attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_form( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<form' . preg_quote( $sometags, '/' ) . 'action=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<form ' . $sometags . 'action=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace canonical attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_canonical( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<link rel="canonical"' . preg_quote( $sometags, '/' ) . 'href=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<link rel="canonical"' . $sometags . 'href=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace amphtml attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_amp( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<link rel="amphtml"' . preg_quote( $sometags, '/' ) . 'href=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<link rel="amphtml"' . $sometags . 'href=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}

	/**
	 * Replace meta og url attribute
	 *
	 * @since 2.0
	 * @param string $translated_page
	 * @param string $current_url
	 * @param string $quote1
	 * @param string $quote2
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function replace_meta( $translated_page, $current_url, $quote1, $quote2, $sometags = null, $sometags2 = null ) {
		$current_language   = weglot_get_current_language();
		$translated_page    = preg_replace( '/<meta property="og:url"' . preg_quote( $sometags, '/' ) . 'content=' . preg_quote( $quote1 . $current_url . $quote2, '/' ) . '/', '<meta property="og:url"' . $sometags . 'content=' . $quote1 . $this->replace_url( $current_url, $current_language ) . $quote2, $translated_page );

		return $translated_page;
	}
}
