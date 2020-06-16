<?php

namespace WeglotWP\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 3.0.0
 */
abstract class Helper_API {
	const API_BASE         = 'https://api.weglot.com';

	const API_BASE_STAGING = 'https://api-staging.weglot.com';

	const API_BASE_OLD     = 'https://weglot.com/api/';

	const CDN_BASE = 'https://cdn.weglot.com/projects-settings/';

	/**
	 * @since 3.0.0
	 * @return string
	 */
	public static function get_cdn_url() {
		if ( WEGLOT_DEV ) {
			return self::CDN_BASE . 'staging/';
		}

		return self::CDN_BASE;
	}

	/**
	 * @since 3.0.0
	 * @return string
	 */
	public static function get_api_url() {
		if ( WEGLOT_DEV ) {
			return self::API_BASE_STAGING;
		}

		return self::API_BASE;
	}

	/**
	 * @since 3.0.0
	 * @return string
	 */
	public static function get_old_api_url() {
		return self::API_BASE_OLD;
	}
}


