<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Post_Meta_Weglot;

use Weglot\Client\Api\Enum\BotType;
use Weglot\Util\Server;


/**
 * Translate page
 *
 * @since 2.0
 */
class Translate_Page_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services                = weglot_get_service( 'Option_Service_Weglot' );
		$this->request_url_services           = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->redirect_services              = weglot_get_service( 'Redirect_Service_Weglot' );
		$this->translate_services             = weglot_get_service( 'Translate_Service_Weglot' );
		$this->private_language_services      = weglot_get_service( 'Private_Language_Service_Weglot' );
		$this->href_lang_services             = weglot_get_service( 'Href_Lang_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.0
	 * @return void
	 */
	public function hooks() {
        if( Helper_Is_Admin::is_wp_admin()) {
            return;
        }

		if ( is_admin() && ( ! wp_doing_ajax() || $this->no_translate_action_ajax() ) ) {
			return;
		}

		$this->api_key            = $this->option_services->get_option( 'api_key' );

		if ( ! $this->api_key ) {
			return;
		}

		if (
			null === $this->request_url_services->get_current_language() ||
			! $this->request_url_services->is_translatable_url()
		) {
			return;
		}
        $this->request_url_services->init_weglot_url();
		$this->request_url_services->get_weglot_url()->detectUrlDetails();
		$this->current_language   = $this->request_url_services->get_current_language();

		if ( $this->private_language_services->is_active_private_mode_for_lang( $this->current_language ) ) {
			return;
		}

		$this->prepare_request_uri();
		$this->prepare_rtl_language();

		add_action( 'init', [ $this, 'weglot_init' ], 11 );
		add_action( 'wp_head', [ $this, 'weglot_href_lang' ] );
	}

	/**
	 * @since 2.1.1
	 *
	 * @return boolean
	 */
	protected function no_translate_action_ajax() {
		$action_ajax_no_translate = apply_filters( 'weglot_ajax_no_translate', [
			'add-menu-item', // WP Core
			'query-attachments', // WP Core
			'avia_ajax_switch_menu_walker', // Enfold theme
			'query-themes', // WP Core
			'wpestate_ajax_check_booking_valability_internal', // WP Estate theme
			'wpestate_ajax_add_booking', // WP Estate theme
			'wpestate_ajax_check_booking_valability', // WP Estate theme
			'mailster_get_template', // Mailster Pro,
			'mmp_map_settings', // MMP Map,
			'elementor_ajax', // Elementor since 2.5
			'ct_get_svg_icon_sets', // Oxygen
			'oxy_render_nav_menu', // Oxygen
			'hotel_booking_ajax_add_to_cart', // Hotel booking plugin
			'imagify_get_admin_bar_profile', // Imagify Admin Bar
		] );

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['action'] ) && in_array( $_POST['action'], $action_ajax_no_translate ) ) { //phpcs:ignore
			return true;
		}

		if ( 'GET' === $_SERVER['REQUEST_METHOD'] && isset( $_GET['action'] ) && in_array( $_GET['action'], $action_ajax_no_translate ) ) { //phpcs:ignore
			return true;
		}

		return false;
	}


	/**
	 * @see init
	 * @since 2.0
	 * @version 2.3.0
	 * @return void
	 */
	public function weglot_init() {
		do_action( 'weglot_init_start' );

		if ( $this->no_translate_action_ajax() ) {
			return;
		}

		$this->noredirect         = false;
		$this->original_language  = $this->option_services->get_option( 'original_language' );
		if ( empty( $this->original_language ) ) {
			return;
		}

		$full_url_no_language = $this->request_url_services->get_full_url_no_language();

		// URL not eligible
		if ( ! $this->request_url_services->is_eligible_url( $full_url_no_language ) ) {
			return;
		}

		$active_translation = apply_filters( 'weglot_active_translation_before_process', true );

		// Default : yes
		if ( ! $active_translation ) {
			return;
		}

		$this->redirect_services->verify_no_redirect();
		$this->check_need_to_redirect();

		do_action( 'weglot_init_before_translate_page' );

		if ( ! function_exists( 'curl_version' ) ) {
			return;
		}

		$active_translation = apply_filters( 'weglot_active_translation_before_treat_page', true );
		// Default : yes
		if ( ! $active_translation ) {
			return;
		}

		$file = apply_filters( 'weglot_debug_file', WEGLOT_DIR . '/content.html' );

		if ( defined( 'WEGLOT_DEBUG' ) && WEGLOT_DEBUG && file_exists( $file ) ) {
			$this->translate_services->set_original_language( weglot_get_original_language() );
			$this->translate_services->set_current_language( $this->request_url_services->get_current_language() );
			// header( 'Content-Type: application/json' );
			echo $this->translate_services->weglot_treat_page( file_get_contents( $file ) ); //phpcs:ignore
			die;
		} else {
			$this->translate_services->weglot_translate();
		}
	}



	/**
	 * @since 2.0
	 *
	 * @return void
	 */
	public function check_need_to_redirect() {
		if (
			! wp_doing_ajax() && // no ajax
			$this->request_url_services->get_weglot_url()->getPath() === '/' && // front_page
			! $this->redirect_services->get_no_redirect() && // No force redirect
			! Server::detectBot( $_SERVER ) !== BotType::OTHER && //phpcs:ignore
			$this->option_services->get_option( 'auto_redirect' ) // have option redirect
		) {
			$this->redirect_services->auto_redirect();
		}
	}

	/**
	 * @since 2.1.0
	 * @return void
	 */
	protected function request_uri_default() {
		$_SERVER['REQUEST_URI'] = str_replace(
			'/' . $this->request_url_services->get_current_language( false ) . '/',
			'/',
			$_SERVER['REQUEST_URI'] //phpcs:ignore
		);
	}

	/**
	 * @since 2.0
	 * @version 2.1.0
	 * @return void
	 */
	public function prepare_request_uri() {
		$original_language = weglot_get_original_language();
		$current_language  = $this->request_url_services->get_current_language( false );

		if ( $original_language === $current_language ) {
			return;
		}

		$request_without_language = array_values(array_filter( explode( '/', str_replace(
			'/' . $current_language . '/',
			'/',
            strpos($_SERVER['REQUEST_URI'], "?") ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "?")) : $_SERVER['REQUEST_URI']
        ) ), 'strlen' ));

		$index_entries = count( $request_without_language ) - 1;
		if ( isset( $request_without_language[ $index_entries ] ) ) {
			$slug_in_work  = $request_without_language[ $index_entries ];
		}

		// Like is_home
		if ( empty( $request_without_language ) || ! isset( $slug_in_work ) ) {
			$this->request_uri_default();
			return;
		}

		$custom_urls = $this->option_services->get_option( 'custom_urls' );

		// No language configured
        $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
        $toTranslateLanguageIso = ($key = array_search($current_language,$language_code_rewrited)) ? $key:$current_language;

        if ( ! isset( $custom_urls[ $toTranslateLanguageIso ] ) ) {
			$this->request_uri_default();
			return;
		}

		$key_slug = array_search( $slug_in_work, $custom_urls[ $toTranslateLanguageIso ] ); //phpcs:ignore

        // No custom URL for this language with this slug
		if ( ! isset( $custom_urls[ $toTranslateLanguageIso ][ $slug_in_work ] ) && false === $key_slug ) {
			$this->request_uri_default();
			return;
		}

		// Custom URL exist but not good slug
		if ( ! isset( $custom_urls[ $toTranslateLanguageIso ][ $slug_in_work ] ) ) {
			return;
		}

		$_SERVER['REQUEST_URI'] = str_replace(
			'/' . $current_language . '/',
			'/',
			str_replace( $slug_in_work, $custom_urls[ $toTranslateLanguageIso ][ $slug_in_work ], $_SERVER['REQUEST_URI'] ) //phpcs:ignore
		);
	}

	/**
	 * @since 2.0
	 *
	 * @return void
	 */
	public function prepare_rtl_language() {
		if ( $this->request_url_services->is_language_rtl( $this->current_language ) ) {
			$GLOBALS['text_direction'] = 'rtl';
		} else {
			$GLOBALS['text_direction'] = 'ltr';
		}
	}

	/**
	 * @see wp_head
	 * @since 2.0
	 * @version 2.3.0
	 * @return void
	 */
	public function weglot_href_lang() {
		echo $this->href_lang_services->generate_href_lang_tags(); //phpcs:ignore
	}
}


