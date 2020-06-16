<?php

namespace WeglotWP\Actions\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Pages_Weglot;
use WeglotWP\Helpers\Helper_Tabs_Admin_Weglot;

/**
 * Enqueue CSS / JS on administration
 *
 * @since 2.0
 *
 */
class Admin_Enqueue_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->language_services = weglot_get_service( 'Language_Service_Weglot' );
		$this->option_services   = weglot_get_service( 'Option_Service_Weglot' );
		$this->user_api_services = weglot_get_service( 'User_Api_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'weglot_admin_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'weglot_admin_enqueue_scripts_metaboxes' ] );
		add_action( 'admin_head', [ $this, 'weglot_admin_print_head' ] );
	}

	/**
	 * @since 2.1.0
	 *
	 * @return void
	 * @param mixed $page
	 */
	public function weglot_admin_enqueue_scripts_metaboxes( $page ) {
		if ( ! in_array( $page, [ 'post.php' ] ) ) { //phpcs:ignore
			return;
		}

		wp_enqueue_script( 'weglot-admin-metaboxes-js', WEGLOT_URL_DIST . '/metaboxes-js.js', [ 'jquery' ] );
		wp_enqueue_style( 'weglot-admin-css', WEGLOT_URL_DIST . '/css/admin-css.css', [], WEGLOT_VERSION );
	}


	/**
	 * Register CSS and JS
	 *
	 * @see admin_enqueue_scripts
	 * @since 2.0
	 * @param string $page
	 * @return void
	 */
	public function weglot_admin_enqueue_scripts( $page ) {
		if ( ! in_array( $page, [ 'toplevel_page_' . Helper_Pages_Weglot::SETTINGS ], true ) ) {
			return;
		}

		wp_enqueue_script( 'weglot-admin-selectize-js', WEGLOT_URL_DIST . '/selectize.js', [ 'jquery', 'jquery-ui-sortable' ] );

		wp_enqueue_script( 'weglot-admin', WEGLOT_URL_DIST . '/admin-js.js', [ 'weglot-admin-selectize-js' ], WEGLOT_VERSION );

		$user_info = $this->user_api_services->get_user_info();
		$plans     = $this->user_api_services->get_plans();
		$limit     = 1000;
		if (
			isset( $user_info['plan_id'] ) &&
			$user_info['plan_id'] <= 1 ||
			isset( $user_info['plan_id'] ) &&
			in_array( $user_info['plan_id'], $plans['starter_free']['ids'] ) // phpcs:ignore
		) {
			$limit = $plans['starter_free']['limit_language'];
		} elseif (
			isset( $user_info['plan_id'] ) &&
			in_array( $user_info['plan_id'], $plans['business']['ids'] ) // phpcs:ignore
		) {
			$limit = $plans['business']['limit_language'];
		}

		wp_localize_script(
			'weglot-admin',
			'weglot_languages',
			[
				'available' => json_decode(
					json_encode(
						$this->language_services->get_languages_available(
							[
								'sort' => true,
							]
						),
						true
					),
					true
				),
				'limit'     => $limit,
				'plans'     => $this->user_api_services->get_plans(),
				'original'  => weglot_get_original_language(),
			]
		);

		wp_enqueue_style( 'weglot-admin-css', WEGLOT_URL_DIST . '/css/admin-css.css', [], WEGLOT_VERSION );

		wp_enqueue_style( 'weglot-css', WEGLOT_URL_DIST . '/css/front-css.css', [], WEGLOT_VERSION );
		wp_localize_script(
			'weglot-admin',
			'weglot_css',
			[
				'inline'   => $this->option_services->get_css_custom_inline(),
				'flag_css' => $this->option_services->get_option( 'flag_css' ),
			]
		);

		/**
		 * Register Code Editor
		 */
		if ( function_exists( 'wp_enqueue_code_editor' ) ) {
			$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
			wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_enqueue_style( 'wp-codemirror' );
		}

	}

	/**
	 * Print in admin head
	 *
	 * @since 3.1.6
	 */
	public function weglot_admin_print_head() {
		?>
		<style type="text/css"> #toplevel_page_weglot-settings .wp-menu-image.svg {background-size: 24px auto !important;} #wp-admin-bar-weglot > .ab-item {background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIj48ZyBmaWxsPSIjYTBhNWFhIj48cGF0aCBkPSJNMjEuNzM5IDkyLjU2NWw1MS44MjggMTI5LjczMiAyMy42Ni02MC4yNzkgMjQuMTQ0IDYwLjI3OUwxNzMuMiA5Mi41NjVoLTI4LjAwN2wtMjMuODIyIDU4Ljc1LTIzLjkwMi01OC43NS0yMy45MDIgNTguNzUtMjMuOTAyLTU4Ljc1SDIxLjczOXoiLz48cGF0aCBkPSJNMjEwLjAwNiA5Mi43MWMtMTcuODY2IDAtMzMuMTU3IDYuMzU4LTQ1Ljg3MyAxOS4wNzQtMTIuNzE1IDEyLjcxNi0xOC45OTMgMjguMDA2LTE4Ljk5MyA0NS43OTIgMCAxNy44NjcgNi4yNzggMzMuMTU4IDE4Ljk5MyA0NS44NzMgMTIuNzE2IDEyLjcxNiAyOC4wMDcgMTguOTkzIDQ1Ljg3MyAxOC45OTMgMTcuNzg2IDAgMzMuMDc3LTYuMjc3IDQ1Ljc5My0xOC45OTMgMTIuNzE1LTEyLjcxNSAxOS4wNzMtMjguMDA2IDE5LjA3My00NS44NzMgMC00LjUwNy0uNDgzLTguODUyLTEuMjg4LTEyLjk1N2gtNjMuNTc4djI1LjkxNGgzNi42OTljLTIuNzM3IDcuNTY1LTcuNDg1IDEzLjg0My0xNC4wODQgMTguNjcxLTYuNjggNC44My0xNC4yNDUgNy4yNDQtMjIuNjE1IDcuMjQ0LTEwLjc4NCAwLTE5Ljk1OC0zLjc4My0yNy41MjMtMTEuMzQ4LTcuNTY2LTcuNTY1LTExLjM0OC0xNi43NC0xMS4zNDgtMjcuNTI0IDAtMTAuNjIzIDMuNzgyLTE5Ljc5OCAxMS4zNDgtMjcuNDQzIDcuNTY1LTcuNjQ1IDE2Ljc0LTExLjUwOCAyNy41MjMtMTEuNTA4IDEwLjYyMyAwIDE5Ljc5OCAzLjg2MyAyNy41MjQgMTEuNDI4bDE4LjM1LTE4LjM1YTY3Ljk2MyA2Ny45NjMgMCAwMC0yMC43NjQtMTMuODQyYy03Ljg4Ny0zLjM4LTE2LjI1Ny01LjE1LTI1LjExLTUuMTV6Ii8+PC9nPjwvc3ZnPg==") !important;background-size: 22px auto !important;background-repeat: no-repeat !important;background-position: 4px 5px !important;padding-left: 30px !important;}</style>
		<?php
	}
}
