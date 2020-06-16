<?php
/**
 * Widgets
 *
 * @package CartFlows
 */

define( 'CARTFLOWS_WIDGETS_DIR', CARTFLOWS_DIR . 'modules/widgets/' );
define( 'CARTFLOWS_WIDGETS_URL', CARTFLOWS_URL . 'modules/widgets/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Widgets {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		require_once CARTFLOWS_WIDGETS_DIR . 'class-cartflows-next-step.php';

		// Register and load the widget.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register widgets
	 */
	public function register_widgets() {
		register_widget( 'cartflows_next_step' );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Widgets::get_instance();
