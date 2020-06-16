<?php

namespace WPForms\Integrations\Elementor;

use WPForms\Integrations\IntegrationInterface;

/**
 * Improve Elementor Compatibility.
 *
 * @since 1.6.0
 */
class Elementor implements IntegrationInterface {

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function allow_load() {

		return did_action( 'elementor/loaded' );
	}

	/**
	 * Load an integration.
	 *
	 * @since 1.6.0
	 */
	public function load() {

		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 1.6.0
	 */
	protected function hooks() {

		add_action( 'elementor/preview/init', [ $this, 'init' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Init an integration logic.
	 *
	 * @since 1.6.0
	 */
	public function init() {

		/**
		 * Allow developers to determine if use or not this compatibility.
		 * We make it on this place because we want that this filter will be available for theme developers too.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $use_compat
		 */
		$use_compat = apply_filters( 'wpforms_apply_elementor_preview_compat', true );

		if ( true !== $use_compat ) {
			return;
		}

		// Load WPForms assets globally in Elementor Preview mode only.
		add_filter( 'wpforms_global_assets', '__return_true' );
	}

	/**
	 * Load an integration javascript.
	 *
	 * @since 1.6.0
	 */
	public function enqueue_assets() {

		// Return, if no forms on Elementor page/popup.
		if ( empty( wpforms()->frontend->forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-elementor',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/wpforms-elementor{$min}.js",
			[ 'wpforms' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-elementor',
			'wpformsElementorVars',
			[
				'recaptcha_type' => wpforms_setting( 'recaptcha-type', 'v2' ),
			]
		);
	}
}
