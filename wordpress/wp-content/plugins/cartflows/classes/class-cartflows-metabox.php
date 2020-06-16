<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Metabox' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Metabox {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
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
			add_action( 'admin_init', array( $this, 'add_wcf_order_metabox' ) );
		}

		/**
		 *
		 * Add Analytics Metabox
		 *
		 * @return void
		 */
		public function add_wcf_order_metabox() {

			if ( ! isset( $_GET['cartflows_debug'] ) ) { //phpcs:ignore
				return;
			}

			$debug = filter_input( INPUT_GET, 'cartflows_debug', FILTER_SANITIZE_STRING );

			if ( $debug ) {
				add_meta_box(
					'wcf-order-details',
					__( 'Flow Details', 'cartflows' ),
					array( $this, 'flow_metabox_markup' ),
					'shop_order',
					'side',
					'low'
				);
			}

		}


		/**
		 *  Flow metabox markup.
		 */
		public function flow_metabox_markup() {
			global $post;
			$flow_id     = wcf()->utils->get_flow_id_from_order( $post->ID );
			$checkout_id = wcf()->utils->get_checkout_id_from_order( $post->ID );

			$html_data = "
            <div>
                <p> This is for debugging only. </p>
                <p> <strong>Flow ID:</strong>: <a href='" . admin_url( 'post.php?post=' . $flow_id . '&action=edit' ) . "'>  " . $flow_id . " </a> </p>
                <p> <strong>Checkout ID:</strong> <a href='" . admin_url( 'post.php?post=' . $checkout_id . '&action=edit' ) . "'> " . $checkout_id . ' </a></p>
            </div>
            ';

			echo $html_data;
		}


	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Metabox::get_instance();

endif;
