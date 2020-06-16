<?php

namespace WeglotWP\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function helper for URL replace filter
 *
 * @since 2.0
 */
abstract class Helper_Filter_Url_Weglot {

	/**
	 * @since 3.0.0
	 * @param Weglot\Util\Url $url
	 * @return string
	 */
	protected static function prevent_subfolder_install( $url ) {
		$current_and_original_language   = weglot_get_current_and_original_language();

		$url_translate   = $url->getForLanguage( $current_and_original_language['current'] );
		$double_language = sprintf( '/%s/%s/', $current_and_original_language['current'], $current_and_original_language['current'] );

		if ( strpos( $url_translate, $double_language ) === false ) {
			return $url_translate;
		}

		return $url->getForLanguage( $current_and_original_language['original'] );
	}

	/**
	 * @since 2.0.2
	 * @param string $url
	 * @return string
	 */
	protected static function get_clean_base_url( $url ) {
		if ( strpos( $url, 'http' ) === false ) {
			$url = sprintf( '%s%s', get_site_url(), $url );
		}

		return apply_filters( 'weglot_get_clean_base_url', $url );
	}

	/**
	 * @since 2.4.0
	 * @param string $url
	 * @return string
	 */
	public static function filter_url_lambda( $url ) {
		$current_and_original_language   = weglot_get_current_and_original_language();
		$request_url_service             = weglot_get_request_url_service();

		if ( $current_and_original_language['current'] === $current_and_original_language['original'] ) {
			return $url;
		}

		$url = $request_url_service->create_url_object( $url );

		return apply_filters( 'weglot_helper_filter_url_lambda', self::prevent_subfolder_install( $url ) );
	}

	/**
	 * Filter URL log redirection
	 * @since 2.0
	 * @version 2.0.2
	 * @param string $url_filter
	 * @return string
	 */
	public static function filter_url_log_redirect( $url_filter ) {
		$current_and_original_language   = weglot_get_current_and_original_language();
		$request_url_service             = weglot_get_request_url_service();
		$choose_current_language         = $current_and_original_language['current'];

		$url_filter = self::get_clean_base_url( $url_filter );

		$url        = $request_url_service->create_url_object( $url_filter );

		if ( $current_and_original_language['current'] === $current_and_original_language['original']
			&& isset( $_SERVER['HTTP_REFERER'] ) //phpcs:ignore
		) {
			$url                     = $request_url_service->create_url_object( $_SERVER['HTTP_REFERER'] ); //phpcs:ignore
			$choose_current_language = $url->detectCurrentLanguage();

			if ( $choose_current_language !== $current_and_original_language['original'] ) {
				$url = $request_url_service->create_url_object( $url_filter );
			}
		}

		return $url->getForLanguage( $choose_current_language );
	}


	/**
	 * Filter url without Ajax
	 *
	 * @since 2.0
	 * @param string $url_filter
	 * @return string
	 */
	public static function filter_url_without_ajax( $url_filter ) {
		$current_and_original_language = weglot_get_current_and_original_language();
		$request_url_service           = weglot_get_request_url_service();
		if ( $current_and_original_language['current'] === $current_and_original_language['original'] ) {
			return $url_filter;
		}

		$url = $request_url_service->create_url_object( $url_filter );

		return apply_filters( 'weglot_helper_filter_url_without_ajax', self::prevent_subfolder_install( $url ) );
	}

	/**
	 * Filter url with optional Ajax
	 *
	 * @since 2.0
	 * @param string $url_filter
	 * @return string
	 */
	public static function filter_url_with_ajax( $url_filter ) {
		$current_and_original_language = weglot_get_current_and_original_language();
		$choose_current_language       = $current_and_original_language['current'];
		$request_url_service           = weglot_get_request_url_service();
		if ( $current_and_original_language['current'] !== $current_and_original_language['original'] ) { // Not ajax
			$url = $request_url_service->create_url_object( $url_filter );
		} else {
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) { //phpcs:ignore
				// Ajax
				$url                     = $request_url_service->create_url_object( $_SERVER['HTTP_REFERER'] ); //phpcs:ignore
				$choose_current_language = $url->detectCurrentLanguage();
				$url                     = $request_url_service->create_url_object( $url_filter );
			}
		}

		return $url->getForLanguage( $choose_current_language );
	}
}
