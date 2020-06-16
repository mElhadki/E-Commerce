<?php
/**
 * Step post meta.
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Step_Post_Meta {


	/**
	 * Member Variable
	 *
	 * @var instance
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
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Step_Post_Meta::get_instance();
