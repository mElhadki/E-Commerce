<?php

namespace WeglotWP\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 3.1.1
 */
abstract class Helper_Is_Admin {

	/**
	 * @since 3.1.1
	 * @return bool
	 */
	public static function is_wp_admin() {

        return is_admin() &&
            (!wp_doing_ajax() || (wp_doing_ajax() && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'wp-admin') !== false));
	}
}