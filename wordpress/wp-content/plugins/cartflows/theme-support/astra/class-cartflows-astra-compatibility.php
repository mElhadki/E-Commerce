<?php
/**
 * Astra theme compatibility
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Astra_Compatibility' ) ) :

	/**
	 * Class for Astra theme compatibility
	 */
	class Cartflows_Astra_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.5.7
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.5.7
		 */
		public function __construct() {

			add_action( 'cartflows_checkout_before_shortcode', array( $this, 'cartflows_theme_compatibility_astra' ) );
			add_action( 'cartflows_optin_before_shortcode', array( $this, 'cartflows_theme_compatibility_astra' ) );

			add_action( 'wp', array( $this, 'cartflows_load_wp_actions_for_astra' ), 56 );
		}


		/**
		 * Function to remove the astra hooks.
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function cartflows_theme_compatibility_astra() {
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_wrapper_div', 1 );
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_ul_wrapper', 2 );
			remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_div_wrapper_close', 30 );
			remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_ul_close', 30 );
			remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_address_li_wrapper', 5 );
			remove_action( 'woocommerce_checkout_after_customer_details', 'astra_woocommerce_li_close' );
			remove_action( 'woocommerce_checkout_before_order_review', 'astra_two_step_checkout_order_review_wrap', 1 );
			remove_action( 'woocommerce_checkout_after_order_review', 'astra_woocommerce_li_close', 40 );

			add_filter( 'astra_theme_woocommerce_dynamic_css', '__return_empty_string' );
		}


		/**
		 * Function to add/remove the actions/hooks on wp action.
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function cartflows_load_wp_actions_for_astra() {

			// Return if not the CartFlows page.
			if ( ! wcf()->utils->is_step_post_type() ) {
				return;
			}

			$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );

			if ( _wcf_supported_template( $page_template ) ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'astra_compatibility_external_css' ), 101 );

			// Re-add the WooCommerce's styles & script swhich are form Astra.
			$astra_woo = Astra_Woocommerce::get_instance();
			add_filter( 'woocommerce_enqueue_styles', array( $astra_woo, 'woo_filter_style' ), 9999 );
		}

		/**
		 * Load the CSS
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function astra_compatibility_external_css() {

			wp_enqueue_style( 'wcf-checkout-astra-compatibility', CARTFLOWS_URL . 'theme-support/astra/css/astra-compatibility.css', '', CARTFLOWS_VER );
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Astra_Compatibility::get_instance();

endif;
