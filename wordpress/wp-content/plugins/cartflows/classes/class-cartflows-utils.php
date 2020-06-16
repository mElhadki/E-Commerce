<?php
/**
 * Utils.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Utils.
 */
class Cartflows_Utils {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var checkout_products
	 */
	public $checkout_products = array();


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

	/**
	 *  Get current post type
	 *
	 * @param string $post_type post type.
	 * @return string
	 */
	public function current_post_type( $post_type = '' ) {

		if ( '' === $post_type ) {
			$post_type = get_post_type();
		}

		return $post_type;
	}

	/**
	 * Check if post type is of step.
	 *
	 * @param string $post_type post type.
	 * @return bool
	 */
	public function is_step_post_type( $post_type = '' ) {

		if ( $this->get_step_post_type() === $this->current_post_type( $post_type ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Check if post type is of flow.
	 *
	 * @param string $post_type post type.
	 * @return bool
	 */
	public function is_flow_post_type( $post_type = '' ) {

		if ( $this->get_flow_post_type() === $this->current_post_type( $post_type ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Get post type of step.
	 *
	 * @return string
	 */
	public function get_step_post_type() {

		return CARTFLOWS_STEP_POST_TYPE;
	}

	/**
	 * Get post type of flow.
	 *
	 * @return string
	 */
	public function get_flow_post_type() {

		return CARTFLOWS_FLOW_POST_TYPE;
	}

	/**
	 * Get flow id
	 *
	 * @return int
	 */
	public function get_flow_id() {

		global $post;

		return get_post_meta( $post->ID, 'wcf-flow-id', true );
	}

	/**
	 * Get flow id by step
	 *
	 * @param int $step_id step ID.
	 * @return int
	 */
	public function get_flow_id_from_step_id( $step_id ) {

		return get_post_meta( $step_id, 'wcf-flow-id', true );
	}

	/**
	 * Get flow steps by id
	 *
	 * @param int $flow_id flow ID.
	 * @return int
	 */
	public function get_flow_steps( $flow_id ) {

		$steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( is_array( $steps ) && ! empty( $steps ) ) {
			return $steps;
		}

		return false;
	}

	/**
	 * Get template type of step
	 *
	 * @param int $step_id step ID.
	 * @return int
	 */
	public function get_step_type( $step_id ) {

		return get_post_meta( $step_id, 'wcf-step-type', true );
	}

	/**
	 * Get next id for step
	 *
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 * @return bool
	 */
	public function get_next_step_id( $flow_id, $step_id ) {

		$steps   = $this->get_flow_steps( $flow_id );
		$step_id = intval( $step_id );

		if ( ! $steps ) {
			return false;
		}

		foreach ( $steps as $i => $step ) {

			if ( intval( $step['id'] ) === $step_id ) {

				$next_i = $i + 1;

				if ( isset( $steps[ $next_i ] ) ) {

					$navigation = $steps[ $next_i ];

					return intval( $navigation['id'] );
				}

				break;
			}
		}

		return false;
	}

	/**
	 * Get next id for step
	 *
	 * @param int $order_id order ID.
	 * @return int
	 */
	public function get_flow_id_from_order( $order_id ) {

		$flow_id = get_post_meta( $order_id, '_wcf_flow_id', true );

		return intval( $flow_id );
	}

	/**
	 * Get checkout id for order
	 *
	 * @param int $order_id order ID.
	 * @return int
	 */
	public function get_checkout_id_from_order( $order_id ) {

		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		return intval( $checkout_id );
	}

	/**
	 * We are using this function mostly in ajax on checkout page
	 *
	 * @return bool
	 */
	public function get_checkout_id_from_post_data() {

		if ( isset( $_POST['_wcf_checkout_id'] ) ) { //phpcs:ignore

			$checkout_id = filter_var( wp_unslash( $_POST['_wcf_checkout_id'] ), FILTER_SANITIZE_NUMBER_INT ); //phpcs:ignore

			return intval( $checkout_id );
		}

		return false;
	}

	/**
	 * We are using this function mostly in ajax on checkout page
	 *
	 * @return bool
	 */
	public function get_flow_id_from_post_data() {

		if ( isset( $_POST['_wcf_flow_id'] ) ) { //phpcs:ignore

			$flow_id = filter_var( wp_unslash( $_POST['_wcf_flow_id'] ), FILTER_SANITIZE_NUMBER_INT ); //phpcs:ignore

			return intval( $flow_id );
		}

		return false;
	}

	/**
	 * Get optin id for order
	 *
	 * @param int $order_id order ID.
	 * @return int
	 */
	public function get_optin_id_from_order( $order_id ) {

		$optin_id = get_post_meta( $order_id, '_wcf_optin_id', true );

		return intval( $optin_id );
	}

	/**
	 * We are using this function mostly in ajax on checkout page
	 *
	 * @return bool
	 */
	public function get_optin_id_from_post_data() {

		if ( isset( $_POST['_wcf_optin_id'] ) ) { //phpcs:ignore

			$optin_id = filter_var( wp_unslash( $_POST['_wcf_optin_id'] ), FILTER_SANITIZE_NUMBER_INT ); //phpcs:ignore

			return intval( $optin_id );
		}

		return false;
	}

	/**
	 * Check for thank you page
	 *
	 * @param int $step_id step ID.
	 * @return bool
	 */
	public function check_is_thankyou_page( $step_id ) {

		$step_type = $this->get_step_type( $step_id );

		if ( 'thankyou' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Check for offer page
	 *
	 * @param int $step_id step ID.
	 * @return bool
	 */
	public function check_is_offer_page( $step_id ) {

		$step_type = $this->get_step_type( $step_id );

		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 *  Check if loaded page requires woo.
	 *
	 * @return bool
	 */
	public function check_is_woo_required_page() {

		global $post;
		$step_id               = $post->ID;
		$woo_not_required_type = array( 'landing' );
		$step_type             = $this->get_step_type( $step_id );
		return ( ! in_array( $step_type, $woo_not_required_type, true ) );
	}

	/**
	 * Define constant for cache
	 *
	 * @return void
	 */
	public function do_not_cache() {

		global $post;

		if ( ! apply_filters( 'cartflows_do_not_cache_step', true, $post->ID ) ) {
			return;
		}

		wcf_maybe_define_constant( 'DONOTCACHEPAGE', true );
		wcf_maybe_define_constant( 'DONOTCACHEOBJECT', true );
		wcf_maybe_define_constant( 'DONOTCACHEDB', true );

		nocache_headers();
	}

	/**
	 * Get linking url
	 *
	 * @param array $args query args.
	 * @return string
	 */
	public function get_linking_url( $args = array() ) {

		$url = get_home_url();

		$url = add_query_arg( $args, $url );

		return $url;
	}

	/**
	 * Get assets urls
	 *
	 * @return array
	 * @since 1.1.6
	 */
	public function get_assets_path() {

		$rtl = '';

		if ( is_rtl() ) {
			$rtl = '-rtl';
		}

		$file_prefix = '';
		$dir_name    = '';

		$is_min = apply_filters( 'cartflows_load_min_assets', false );

		if ( $is_min ) {
			$file_prefix = '.min';
			$dir_name    = 'min-';
		}

		$js_gen_path  = CARTFLOWS_URL . 'assets/' . $dir_name . 'js/';
		$css_gen_path = CARTFLOWS_URL . 'assets/' . $dir_name . 'css/';

		return array(
			'css'         => $css_gen_path,
			'js'          => $js_gen_path,
			'file_prefix' => $file_prefix,
			'rtl'         => $rtl,
		);
	}

	/**
	 * Get assets css url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	public function get_css_url( $file ) {

		$assets_vars = wcf()->assets_vars;

		$url = $assets_vars['css'] . $file . $assets_vars['rtl'] . $assets_vars['file_prefix'] . '.css';

		return $url;
	}

	/**
	 * Get assets js url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	public function get_js_url( $file ) {

		$assets_vars = wcf()->assets_vars;

		$url = $assets_vars['js'] . $file . $assets_vars['file_prefix'] . '.js';

		return $url;
	}

	/**
	 * Get unique id.
	 *
	 * @param int $length    Length.
	 *
	 * @return string
	 */
	public function get_unique_id( $length = 8 ) {

		return substr( md5( microtime() ), 0, $length );
	}

	/**
	 * Get selected checkout products and data
	 *
	 * @param int   $checkout_id    Checkout id..
	 * @param array $saved_products Saved product.
	 *
	 * @return array
	 */
	public function get_selected_checkout_products( $checkout_id = '', $saved_products = array() ) {

		if ( empty( $checkout_id ) ) {

			global $post;

			$checkout_id = $post->ID;
		}

		if ( ! isset( $this->checkout_products[ $checkout_id ] ) ) {

			if ( ! empty( $saved_products ) ) {

				$products = $saved_products;
			} else {

				$products = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-products' );
			}

			$verify_url_data     = false;
			$default_add_to_cart = false;
			$default_ids         = array();
			$default_add_to_cart = true;

			if ( isset( $_GET['wcf-default'] ) ) { //phpcs:ignore
				//phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$default_sequence = sanitize_text_field( wp_unslash( $_GET['wcf-default'] ) );
				$default_ids      = array_map( 'intval', explode( ',', $default_sequence ) );
				$verify_url_data  = true;
			}

			if ( is_array( $products ) ) {

				foreach ( $products as $in => $data ) {

					if ( $verify_url_data ) {

						$default_add_to_cart = false;
						$sequence            = $in + 1;

						if ( in_array( $sequence, $default_ids, true ) ) {
							$default_add_to_cart = true;
						}
					}

					$default_data = array(
						'quantity'       => 1,
						'discount_type'  => '',
						'discount_value' => '',
						'unique_id'      => $this->get_unique_id(),
						'add_to_cart'    => $default_add_to_cart,
					);

					$products[ $in ] = wp_parse_args( $products[ $in ], $default_data );
				}
			}

			$this->checkout_products[ $checkout_id ] = $products;
		}

		return $this->checkout_products[ $checkout_id ];
	}

	/**
	 * Get selected checkout products and data
	 *
	 * @param int   $checkout_id    Checkout id..
	 * @param array $products_data  Saved product.
	 *
	 * @return array
	 */
	public function set_selcted_checkout_products( $checkout_id = '', $products_data = array() ) {

		if ( empty( $checkout_id ) ) {

			global $post;

			$checkout_id = $post->ID;
		}

		if ( isset( $this->checkout_products[ $checkout_id ] ) ) {

			$products = $this->checkout_products[ $checkout_id ];
		} else {
			$products = $this->get_selected_checkout_products( $checkout_id );
		}

		if ( is_array( $products ) && ! empty( $products_data ) ) {

			foreach ( $products as $in => $data ) {

				if ( isset( $products_data[ $in ] ) ) {
					$products[ $in ] = wp_parse_args( $products_data[ $in ], $products[ $in ] );
				}
			}
		}

		$this->checkout_products[ $checkout_id ] = $products;

		return $this->checkout_products[ $checkout_id ];
	}
}

/**
 * Get a specific property of an array without needing to check if that property exists.
 *
 * Provide a default value if you want to return a specific value if the property is not set.
 *
 * @param array  $array   Array from which the property's value should be retrieved.
 * @param string $prop    Name of the property to be retrieved.
 * @param string $default Optional. Value that should be returned if the property is not set or empty. Defaults to null.
 *
 * @return null|string|mixed The value
 */
function wcf_get_prop( $array, $prop, $default = null ) {

	if ( ! is_array( $array ) && ! ( is_object( $array ) && $array instanceof ArrayAccess ) ) {
		return $default;
	}

	if ( isset( $array[ $prop ] ) ) {
		$value = $array[ $prop ];
	} else {
		$value = '';
	}

	return empty( $value ) && null !== $default ? $default : $value;
}
