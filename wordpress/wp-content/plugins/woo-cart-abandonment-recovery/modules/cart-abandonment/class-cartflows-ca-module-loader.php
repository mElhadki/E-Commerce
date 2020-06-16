<?php
/**
 * Cart Abandonment DB
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

/**
 * Cart Abandonment DB class.
 */
class Cartflows_Ca_Module_Loader {



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
	 *  Constructor
	 */
	public function __construct() {

		$this->load_module_files();
	}


	/**
	 *  Load required files for module.
	 */
	private function load_module_files() {

		/* Cart abandonment templates class */
		include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/class-cartflows-ca-email-templates.php';

		/* Cart abandonment templates table */
		include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/class-cartflows-ca-email-templates-table.php';

		/* Cart abandonment tracking */
		include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/class-cartflows-ca-cart-abandonment.php';

		/* Cart abandonment tracking table */
		include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/class-cartflows-ca-cart-abandonment-table.php';

	}

}

Cartflows_Ca_Module_Loader::get_instance();
