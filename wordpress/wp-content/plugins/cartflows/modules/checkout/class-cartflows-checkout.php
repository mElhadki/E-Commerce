<?php
/**
 * Checkout
 *
 * @package Woo Funnel Cart
 */

define( 'CARTFLOWS_CHECKOUT_DIR', CARTFLOWS_DIR . 'modules/checkout/' );
define( 'CARTFLOWS_CHECKOUT_URL', CARTFLOWS_URL . 'modules/checkout/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Checkout {


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
		require_once CARTFLOWS_CHECKOUT_DIR . 'classes/class-cartflows-checkout-markup.php';
		require_once CARTFLOWS_CHECKOUT_DIR . 'classes/class-cartflows-checkout-meta.php';
	}
}
/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Checkout::get_instance();
