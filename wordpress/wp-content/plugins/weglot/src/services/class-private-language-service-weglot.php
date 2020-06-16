<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Util\Url;
use Weglot\Util\Server;


/**
 * Private_Language_Service_Weglot
 *
 * @since 2.3.0
 */
class Private_Language_Service_Weglot {
	protected $role_private_mode = 'administrator';

	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
	}

	/**
	 * @since 2.3.0
	 * @version 3.0.1
	 * @param string $key_lang
	 * @return boolean
	 */
	public function is_active_private_mode_for_lang( $key_lang ) {
		$private_mode_languages    = $this->option_services->get_option( 'private_mode' );

		if ( ! is_array( $private_mode_languages ) ) {
			return false;
		}

		if ( ! array_key_exists( 'active', $private_mode_languages ) || ! $private_mode_languages['active'] ) {
			return false;
		}

		unset( $private_mode_languages['active'] );
		foreach ( $private_mode_languages as $lang => $lang_active ) {
			if ( $key_lang === $lang && $lang_active && ! $this->is_allowed_view_private() ) {
				return true;
			}
		}

		return false;
	}

	public function is_allowed_view_private() {
	    return current_user_can( $this->role_private_mode ) || strpos(weglot_get_current_full_url(), 'weglot-private=1') !== false;
    }

	/**
	 * @since 2.4.0
	 * @return bool
	 */
	public function private_mode_for_all_languages() {
		$private_mode_languages    = $this->option_services->get_option( 'private_mode' );
		if ( $this->is_allowed_view_private() ) { // No check if admin
			return false;
		}

		if ( ! is_array( $private_mode_languages ) ) {
			return false;
		}

		if ( ! array_key_exists( 'active', $private_mode_languages ) ) {
			return false;
		}

		if ( ! $private_mode_languages['active'] ) {
			return false;
		}

		$original_language = weglot_get_original_language();

		unset( $private_mode_languages['active'] );
		if ( array_key_exists( $original_language, $private_mode_languages ) ) {
			unset( $private_mode_languages['original_language'] );
		}

		foreach ( $private_mode_languages as $lang => $lang_active ) {
			if ( ! $lang_active ) {
				return false;
			}
		}

		return true;
	}
}


