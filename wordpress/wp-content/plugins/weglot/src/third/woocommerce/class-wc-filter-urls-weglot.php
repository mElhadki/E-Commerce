<?php

namespace WeglotWP\Third\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Filter_Url_Weglot;

/**
 * WC_Filter_Urls_Weglot
 *
 * @since 2.0
 */
class WC_Filter_Urls_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 * @return void
	 */
	public function __construct() {
		$this->request_url_services      = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
		$this->wc_active_services        = weglot_get_service( 'Wc_Active' );
		$this->replace_url_services      = weglot_get_service( 'Replace_Url_Service_Weglot' );
	}

	/**
	 * @since 2.0
	 * @version 2.6.0
	 * @see Hooks_Interface_Weglot
	 *
	 * @return void
	 */
	public function hooks() {
		if ( ! $this->wc_active_services->is_active() ) {
			return;
		}

		add_filter( 'woocommerce_get_cart_url', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_without_ajax' ] );
		add_filter( 'woocommerce_get_checkout_url', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_without_ajax' ] );
		add_filter( 'woocommerce_get_myaccount_page_permalink', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_without_ajax' ] );
		add_filter( 'woocommerce_payment_successful_result', [ $this, 'woocommerce_filter_url_array' ] );
		add_filter( 'woocommerce_get_checkout_order_received_url',  [ $this, 'woocommerce_filter_order_received_url' ] );
		add_action( 'woocommerce_reset_password_notification', [ $this, 'woocommerce_filter_reset_password' ], 999 );

		add_filter( 'woocommerce_login_redirect', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_log_redirect' ] );
		add_filter( 'woocommerce_registration_redirect', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_log_redirect' ] );
		add_filter( 'woocommerce_cart_item_permalink',  [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_lambda' ] );

		/**
		 * @since 2.6.0
		 */
		add_filter( 'woocommerce_get_cart_page_permalink', [ '\WeglotWP\Helpers\Helper_Filter_Url_Weglot', 'filter_url_lambda' ] );


        add_filter( 'woocommerce_get_endpoint_url', [ $this, 'last_password_url_filter' ] , 10, 4);
	}

	/**
	 * Filter woocommerce order received URL
	 *
	 * @since 2.0
	 * @param string $url_filter
	 * @return string
	 */
	public function woocommerce_filter_order_received_url( $url_filter ) {
		$current_and_original_language   = weglot_get_current_and_original_language();
		$choose_current_language         = $current_and_original_language['current'];
		$url                             = $this->request_url_services->create_url_object( $url_filter );
		if ( $current_and_original_language['current'] !== $current_and_original_language['original'] ) { // Not ajax

			if ( substr( get_option( 'permalink_structure' ), -1 ) !== '/' ) {
				return str_replace( '/?key', '?key',  $url->getForLanguage( $choose_current_language ) );
			} else {
				return str_replace( '//?key', '/?key', str_replace( '?key', '/?key', $url->getForLanguage( $choose_current_language ) ) );
			}
		} else {
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) { //phpcs:ignore
				// Ajax
				$choose_current_language = $url->detectCurrentLanguage();
				if ( $choose_current_language && $choose_current_language !== $current_and_original_language['original'] ) {
					if ( substr( get_option( 'permalink_structure' ), -1 ) !== '/' ) {
						return str_replace( '/?key', '?key', $url->getForLanguage( $choose_current_language ) );
					} else {
						return str_replace( '//?key', '/?key', str_replace( '?key', '/?key', $url->getForLanguage( $choose_current_language ) ) );
					}
				}
			}
		}
		return $url_filter;
	}

	public function last_password_url_filter($url, $endpoint, $value, $permalink) {

	    if($endpoint === 'lost-password') {
            $current_headers = headers_list();
            foreach ($current_headers as $header) {
                if (strpos($header, 'wp-resetpass') !== false) {
                    preg_match("#wp-resetpass-(.*?)=(.*?);#" , $header , $matchesName);
                    preg_match("#path=(.*?);#" , $header , $matchesPath);
                    if(isset($matchesName[0]) && isset($matchesPath[0]) && isset($matchesPath[1])) {
                        setcookie( "wp-resetpass-". $matchesName[1], urldecode($matchesName[2]), 0, '/' . weglot_get_current_language() . $matchesPath[1], '' , is_ssl(), true );
                    }
                }
            }
        }
	    return $url;
    }

	/**
	 * Filter array woocommerce filter with optional Ajax
	 *
	 * @since 2.0
	 * @param array $result
	 * @return array
	 */
	public function woocommerce_filter_url_array( $result ) {
		$current_and_original_language = weglot_get_current_and_original_language();
		$choose_current_language       = $current_and_original_language['current'];
		if ( $current_and_original_language['current'] !== $current_and_original_language['original'] ) { // Not ajax
			$url = $this->request_url_services->create_url_object( $result['redirect'] );
		} else {
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) { //phpcs:ignore
				// Ajax
				$url                     = $this->request_url_services->create_url_object( $_SERVER['HTTP_REFERER'] ); //phpcs:ignore
				$choose_current_language = $url->detectCurrentLanguage();
				$url                     = $this->request_url_services->create_url_object( $result['redirect'] );
			}
		}
		if ( $this->replace_url_services->check_link( $result['redirect'] ) ) { // We must not add language code if external link
            if(isset($url) && $url) {
                $result['redirect'] = $url->getForLanguage( $choose_current_language );
            }
		}
		return $result;
	}


	/**
	 * Redirect URL Lost password for WooCommerce
	 * @since 2.0
	 * @version 2.0.4
	 * @param mixed $url
     * @return void
	 */
	public function woocommerce_filter_reset_password( $url ) {
		$current_and_original_language = weglot_get_current_and_original_language();

		if ( $current_and_original_language['current'] === $current_and_original_language['original'] ) {
			return $url;
		}

		$url_redirect = add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) );
		$url_redirect = $this->request_url_services->create_url_object( $url_redirect );

		wp_redirect( $url_redirect->getForLanguage( $current_and_original_language['current'] ) ); //phpcs:ignore
		exit;
	}
}
