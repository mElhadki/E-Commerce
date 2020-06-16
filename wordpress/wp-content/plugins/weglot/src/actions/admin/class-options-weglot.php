<?php

namespace WeglotWP\Actions\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Tabs_Admin_Weglot;
use WeglotWP\Helpers\Helper_Pages_Weglot;
use WeglotWP\Helpers\Helper_Flag_Type;

use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 * Sanitize options after submit form
 *
 * @since 2.0
 */
class Options_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services   = weglot_get_service( 'Option_Service_Weglot' );
		$this->user_api_services = weglot_get_service( 'User_Api_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.0
	 * @version 3.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_post_weglot_save_settings', [ $this, 'weglot_save_settings' ] );
		$api_key = $this->option_services->get_api_key( true );
		if ( empty( $api_key ) && ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'weglot-settings' ) === false) ) { // phpcs:ignore
			//We don't show the notice if we are on Weglot configuration
			add_action( 'admin_notices', [ '\WeglotWP\Notices\No_Configuration_Weglot', 'admin_notice' ] );
		}
	}

	/**
	 * Activate plugin
	 *
	 * @return void
	 */
	public function activate() {
		update_option( 'weglot_version', WEGLOT_VERSION );
	}


	/**
	 * @since 3.0.0
	 * @return void
	 */
	public function weglot_save_settings() {
		$redirect_url = admin_url( 'admin.php?page=' . Helper_Pages_Weglot::SETTINGS );
		if ( ! isset( $_GET['tab'] ) || ! isset( $_GET['_wpnonce'] ) ) { //phpcs:ignore
			wp_redirect( $redirect_url );
			return;
		}

		if ( ! wp_verify_nonce( $_GET[ '_wpnonce' ], 'weglot_save_settings' ) ) { //phpcs:ignore
			wp_redirect( $redirect_url );
			return;
		}

		$tab         = $_GET[ 'tab' ]; //phpcs:ignore
		$options     = $_POST[ WEGLOT_SLUG ]; //phpcs:ignore
		$options_bdd = $this->option_services->get_options_bdd_v3();
		switch ( $tab ) {
			case Helper_Tabs_Admin_Weglot::SETTINGS:

				$has_first_settings = $this->option_services->get_has_first_settings();
				$options            = $this->sanitize_options_settings( $options, $has_first_settings );
				$response           = $this->option_services->save_options_to_weglot( $options,  $has_first_settings );

				if ( $response['success'] ) {
					delete_transient( 'weglot_cache_cdn' );

					$api_key_private        = $this->option_services->get_api_key_private();

					$option_v2 = $this->option_services->get_options_from_v2();
					if ( ! $api_key_private && $option_v2 ) {
						$options_bdd['custom_urls']             = $option_v2['custom_urls'];
						$options_bdd['menu_switcher']           = $option_v2['menu_switcher'];
						$options_bdd['has_first_settings']      = $option_v2['has_first_settings'];
						$options_bdd['show_box_first_settings'] = $option_v2['show_box_first_settings'];
					}

					if ( $has_first_settings ) {
						$options_bdd['has_first_settings']      = false;
						$options_bdd['show_box_first_settings'] = true;
					}

					if ( array_key_exists( 'flag_css', $options ) ) {
						$options_bdd['flag_css'] = $options['flag_css'];
					}

					$this->option_services->set_options( $options_bdd );

					update_option( sprintf( '%s-%s', WEGLOT_SLUG, 'api_key_private' ), $options['api_key_private'] );
					update_option( sprintf( '%s-%s', WEGLOT_SLUG, 'api_key' ), $response['result']['api_key'] );
				}
				break;
			case Helper_Tabs_Admin_Weglot::SUPPORT:
				if ( array_key_exists( 'active_wc_reload', $options ) && $options['active_wc_reload'] === 'on' ) {
					$options_bdd['active_wc_reload'] = true;
				}
				else {
					$options_bdd['active_wc_reload'] = false;
				}

				$this->option_services->set_options( $options_bdd );
				break;
			case Helper_Tabs_Admin_Weglot::CUSTOM_URLS:
				if (null === $options_bdd) {
					$options_bdd['custom_urls'] = [];
				}

				if ( array_key_exists( 'custom_urls', $options ) ) {
					$options_bdd['custom_urls'] = $options['custom_urls'];
				}
				else {
					$options_bdd['custom_urls'] = [];
				}

				$this->option_services->set_options( $options_bdd );
				break;
		}

		wp_redirect( $redirect_url ); //phpcs:ignore
	}


	/**
	 * @since 2.0
	 * @version 2.0.6
	 * @param array $options
	 * @param mixed $has_first_settings
	 * @return array
	 */
	public function sanitize_options_settings( $options, $has_first_settings = false ) {
		$user_info        = $this->user_api_services->get_user_info( $options['api_key_private'] );
		$plans            = $this->user_api_services->get_plans();

		// Limit language
		if (
			$user_info['plan_id'] <= 1 ||
			in_array( $user_info['plan_id'], $plans['starter_free']['ids'] ) // phpcs:ignore
		) {
			$options['languages'] = array_splice( $options['languages'], 0, $plans['starter_free']['limit_language'] );
		} elseif (
			in_array( $user_info['plan_id'], $plans['business']['ids'] ) // phpcs:ignore
		) {
			$options['languages'] = array_splice( $options['languages'], 0, $plans['business']['limit_language'] );
		}

		$default_options = $this->option_services->get_options_default();

		$options['custom_settings']['button_style']['is_dropdown']    = isset( $options['custom_settings']['button_style']['is_dropdown'] );
		$options['custom_settings']['button_style']['with_flags']     = isset( $options['custom_settings']['button_style']['with_flags'] );
		$options['custom_settings']['button_style']['full_name']      = isset( $options['custom_settings']['button_style']['full_name'] );
		$options['custom_settings']['button_style']['with_name']      = isset( $options['custom_settings']['button_style']['with_name'] );

		if ( $has_first_settings ) {
			$options['custom_settings']['button_style']['is_dropdown'] = $default_options['custom_settings']['button_style']['is_dropdown'];
			$options['custom_settings']['button_style']['with_flags']  = $default_options['custom_settings']['button_style']['with_flags'];
			$options['custom_settings']['button_style']['full_name']   = $default_options['custom_settings']['button_style']['full_name'];
			$options['custom_settings']['button_style']['with_name']   = $default_options['custom_settings']['button_style']['with_name'];
		}

		$options['custom_settings']['button_style']['custom_css']   = isset( $options['custom_settings']['button_style']['custom_css'] ) ? stripcslashes( $options['custom_settings']['button_style']['custom_css'] ) : '';

		$options['custom_settings']['button_style']['flag_type']    = isset( $options['custom_settings']['button_style']['flag_type'] ) ? $options['custom_settings']['button_style']['flag_type'] : Helper_Flag_Type::RECTANGLE_MAT;

		$options['custom_settings']['translate_email']              = isset( $options['custom_settings']['translate_email'] );
		$options['custom_settings']['translate_search']             = isset( $options['custom_settings']['translate_search'] );
		$options['custom_settings']['translate_amp']                = isset( $options['custom_settings']['translate_amp'] );

		$options['auto_switch']                = isset( $options['auto_switch'] );
		foreach ( $options['languages'] as $key => $language ) {
			if ( 'active' === $key ) {
				continue;
			}
			$options['languages'][ $key ]['enabled'] = ! isset( $options['languages'][ $key ]['enabled'] );
		}

		if ( ! isset( $options['excluded_paths'] ) ) {
			$options['excluded_paths'] = [];
		} else {
			$options['excluded_paths'] = array_values( $options['excluded_paths'] );
		}

		foreach ( $options['excluded_paths'] as $key => $item ) {
			if ( empty( $item['value'] ) ) {
				unset( $options['excluded_paths'][ $key ] );
			}
			else {
				$options['excluded_paths'][ $key ]['value'] = stripcslashes( $item['value'] );
			}
		}

		if ( ! isset( $options['excluded_blocks'] ) ) {
			$options['excluded_blocks'] = [];
		}
		else {
            array_walk_recursive( $options['excluded_blocks'], function ( &$element ) { //We remove unwanted backslashes
                $element = stripslashes( $element );
            } );
        }

		return $options;
	}
}
