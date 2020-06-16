<?php

namespace WeglotWP\Actions\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;

use WeglotWP\Helpers\Helper_Pages_Weglot;
use WeglotWP\Helpers\Helper_Tabs_Admin_Weglot;

/**
 * Register pages administration
 *
 * @since 2.0
 *
 */
class Pages_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services    = weglot_get_service( 'Option_Service_Weglot' );
		$this->language_services  = weglot_get_service( 'Language_Service_Weglot' );
		$this->button_services    = weglot_get_service( 'Button_Service_Weglot' );
		$this->user_api_services  = weglot_get_service( 'User_Api_Service_Weglot' );
		$this->wc_active_services = weglot_get_service( 'Wc_Active' );
		return $this;
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'weglot_plugin_menu' ] );
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], PHP_INT_MAX );
	}

	/**
	 * @since 3.1.7
	 * @return void
	 */
	public function add_admin_bar_menu() {

		global $wp_admin_bar;
		global $wp;

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'weglot',
				'title' => WEGLOT_NAME,
				'href'  => '',
			)
		);

		if ( is_admin() ) {
			$url_to_edit = get_home_url();
		} else {
			$url_to_edit = home_url( add_query_arg( array(), $wp->request ) );
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'weglot-settings',
				'parent' => 'weglot',
				'title'  => __( 'Plugin settings', 'weglot' ),
				'href'   => admin_url( 'admin.php?page=weglot-settings' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'weglot-dashboard',
				'parent' => 'weglot',
				'title'  => __( 'Weglot dashboard', 'weglot' ),
				'href'   => esc_url( 'https://dashboard.weglot.com/translations/', 'weglot' ),
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'weglot-visual-editor',
				'parent' => 'weglot',
				'title'  => __( 'Edit with visual editor', 'weglot' ),
				'href'   => add_query_arg( 'url', $url_to_edit, 'https://dashboard.weglot.com/translations/visual-editor/' ),
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

	}

	/**
	 * Add menu and sub pages
	 *
	 * @see admin_menu
	 *
	 * @since 2.0
	 * @return void
	 */
	public function weglot_plugin_menu() {

		$weglot_logo_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><g fill="#eee"><path d="M21.739 92.565l51.828 129.732 23.66-60.279 24.144 60.279L173.2 92.565h-28.007l-23.822 58.75-23.902-58.75-23.902 58.75-23.902-58.75H21.739z"/><path d="M210.006 92.71c-17.866 0-33.157 6.358-45.873 19.074-12.715 12.716-18.993 28.006-18.993 45.792 0 17.867 6.278 33.158 18.993 45.873 12.716 12.716 28.007 18.993 45.873 18.993 17.786 0 33.077-6.277 45.793-18.993 12.715-12.715 19.073-28.006 19.073-45.873 0-4.507-.483-8.852-1.288-12.957h-63.578v25.914h36.699c-2.737 7.565-7.485 13.843-14.084 18.671-6.68 4.83-14.245 7.244-22.615 7.244-10.784 0-19.958-3.783-27.523-11.348-7.566-7.565-11.348-16.74-11.348-27.524 0-10.623 3.782-19.798 11.348-27.443 7.565-7.645 16.74-11.508 27.523-11.508 10.623 0 19.798 3.863 27.524 11.428l18.35-18.35a67.963 67.963 0 00-20.764-13.842c-7.887-3.38-16.257-5.15-25.11-5.15z"/></g></svg>';

		$menu_icon = 'data:image/svg+xml;base64,' . base64_encode( $weglot_logo_svg );

		add_menu_page( 'Weglot', 'Weglot', 'manage_options', Helper_Pages_Weglot::SETTINGS, [ $this, 'weglot_plugin_settings_page' ], $menu_icon );
	}

	/**
	 * Page settings
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function weglot_plugin_settings_page() {
		$this->tabs       = Helper_Tabs_Admin_Weglot::get_full_tabs();
		$this->tab_active = Helper_Tabs_Admin_Weglot::SETTINGS;

		delete_transient( 'weglot_cache_cdn' );

		if ( isset( $_GET['tab'] ) ) { // phpcs:ignore
			$this->tab_active = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore
		}

		$this->options = $this->option_services->get_options();

		try {
			$user_info = $this->user_api_services->get_user_info();
			$this->option_services->set_option_by_key( 'allowed', $user_info['allowed'] );
		} catch ( \Exception $e ) {
			// If an exception occurs, do nothing, keep wg_allowed.
		}

		include_once WEGLOT_TEMPLATES_ADMIN_PAGES . '/settings.php';
	}
}
