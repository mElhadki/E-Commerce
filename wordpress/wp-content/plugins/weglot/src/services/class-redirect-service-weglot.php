<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Util\Url;
use Weglot\Util\Server;


/**
 * Redirect URL
 *
 * @since 2.0
 */
class Redirect_Service_Weglot {
	/**
	 * @since 2.0
	 *
	 * @var string
	 */
	protected $weglot_url = null;

	/**
	 *
	 * @var boolean
	 */
	protected $no_redirect = false;

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
		$this->request_url_services      = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->private_services          = weglot_get_service( 'Private_Language_Service_Weglot' );
	}

	/**
	 * @since 2.0
	 *
	 * @return bool
	 */
	public function get_no_redirect() {
		return $this->no_redirect;
	}

	/**
	 * @since 2.3.0
	 * @param string $server_lang
	 * @return string
	 */
	protected function language_exception( $server_lang ) {
		if ( in_array( $server_lang, ['nb', 'nn', ] ) ) { //phpcs:ignore
			// Case Norwegian
			$server_lang = 'no';
		}

		return apply_filters( 'weglot_redirection_language_exception', $server_lang );
	}

	/**
	 * @since 2.0
	 * @version 2.3.0
	 * @return string
	 */
	public function auto_redirect() {
		if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && ! isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) { //phpcs:ignore
			return;
		}

		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) { //phpcs:ignore
            $server_lang           = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, apply_filters('weglot_number_of_character_for_language',2) );
		} else {
			if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) { // phpcs:ignore
				// Compatibility Cloudfare
				$server_lang = strtolower( $_SERVER['HTTP_CF_IPCOUNTRY'] ); //phpcs:ignore
			}
		}

		$server_lang = $this->language_exception( $server_lang );

		$destination_languages = weglot_get_destination_languages();

		if (
			in_array( $server_lang, $destination_languages ) && // phpcs:ignore
			weglot_get_original_language() === $this->request_url_services->get_current_language() &&
			! $this->private_services->is_active_private_mode_for_lang( $server_lang )
		) {
			$url_auto_redirect = apply_filters( 'weglot_url_auto_redirect', $this->request_url_services->get_weglot_url()->getForLanguage( $server_lang ) );
			header( "Location: $url_auto_redirect", true, 302 );
			exit();
		}

        if (
            !in_array( $server_lang, $destination_languages ) && // phpcs:ignore
            $server_lang !== weglot_get_original_language() &&
            weglot_get_original_language() === $this->request_url_services->get_current_language() &&
            ! $this->private_services->is_active_private_mode_for_lang( $server_lang ) &&
            $this->option_services->get_option('autoswitch_fallback') !== null
        ) {
            $url_auto_redirect = apply_filters( 'weglot_url_auto_redirect', $this->request_url_services->get_weglot_url()->getForLanguage( $this->option_services->get_option('autoswitch_fallback') ) );
            header( "Location: $url_auto_redirect", true, 302 );
            exit();
        }
    }

	/**
	 * @since 2.0
	 *
	 * @return void
	 */
	public function verify_no_redirect() {
		if ( strpos( $this->request_url_services->get_weglot_url()->getUrl(), '?no_lredirect=true' ) === false ) {
			return;
		}

		$this->no_redirect = true;

		if ( isset( $_SERVER['REQUEST_URI'] ) ) { // phpcs:ignore
			$_SERVER['REQUEST_URI'] = str_replace(
				'?no_lredirect=true',
				'',
				$_SERVER['REQUEST_URI'] //phpcs:ignore
			);
		}
	}
}


