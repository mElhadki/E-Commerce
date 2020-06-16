<?php

namespace WeglotWP\Actions;

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 * Registe widget weglot
 *
 * @since 2.0
 */
class Register_Widget_Weglot implements Hooks_Interface_Weglot {

	/**
	 * @see HooksInterface
	 * @return void
	 */
	public function hooks() {
		add_action( 'widgets_init', [ $this, 'register_widget_weglot' ] );
	}

	/**
	 * @since 2.0
	 * @return void
	 */
	public function register_widget_weglot() {
		register_widget( 'WeglotWP\Widgets\Widget_Selector_Weglot' );
	}
}
