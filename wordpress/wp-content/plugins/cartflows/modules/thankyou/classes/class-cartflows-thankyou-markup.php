<?php
/**
 * Front end and markup
 *
 * @package CartFlows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Thankyou_Markup {


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

		/* Downsell Shortcode */
		add_shortcode( 'cartflows_order_details', array( $this, 'cartflows_order_details_shortcode_markup' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'thank_you_scripts' ), 21 );

		add_action( 'woocommerce_is_order_received_page', array( $this, 'set_order_received_page' ) );

		/* Set is checkout flag */
		add_filter( 'woocommerce_is_checkout', array( $this, 'woo_checkout_flag' ), 9999 );

		/* Custom redirection of thank you page */
		add_action( 'template_redirect', array( $this, 'redirect_tq_page_to_custom_url' ) );

		add_action( 'cartflows_thank_you_scripts', array( $this, 'add_divi_compatibility_css' ) );
	}

	/**
	 *  Redirect to custom url instead of thank you page.
	 */
	public function redirect_tq_page_to_custom_url() {
		global $post;

		if ( _is_wcf_thankyou_type() ) {

			$thank_you_id       = $post->ID;
			$enable_redirection = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-tq-redirect-section' );
			$redirect_link      = wp_http_validate_url( wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-redirect-link' ) );

			if ( 'yes' === $enable_redirection && ! empty( $redirect_link ) ) {
				exit( wp_redirect( $redirect_link ) ); //phpcs:ignore
			}
		}
	}
	/**
	 * Order shortcode markup
	 *
	 * @param array $atts attributes.
	 * @since 1.0.0
	 */
	public function cartflows_order_details_shortcode_markup( $atts ) {

		$output = '';

		if ( _is_wcf_thankyou_type() ) {
			/* Remove order item link */
			add_filter( 'woocommerce_order_item_permalink', '__return_false' );

			/* Change order text */
			add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'custom_tq_text' ), 10, 2 );

			if ( ! function_exists( 'wc_print_notices' ) ) {

				$notice_out  = '<p class="woocommerce-notice">' . __( 'WooCommerce functions do not exist. If you are in an IFrame, please reload it.', 'cartflows' ) . '</p>';
				$notice_out .= '<button onClick="location.reload()">' . __( 'Click Here to Reload', 'cartflows' ) . '</button>';

				return $notice_out;
			}

			$order = false;

			$id_param  = 'wcf-order';
			$key_param = 'wcf-key';

			if ( isset( $_GET['wcf-opt-order'] ) ) { //phpcs:ignore
				$id_param  = 'wcf-opt-order';
				$key_param = 'wcf-opt-key';
			}

			if ( ! isset( $_GET[ $id_param ] ) && wcf()->flow->is_flow_testmode() ) { //phpcs:ignore
				$args = array(
					'limit'     => 1,
					'order'     => 'DESC',
					'post_type' => 'shop_order',
					'status'    => array( 'completed', 'processing' ),
				);

				$latest_order = wc_get_orders( $args );

				$order_id = ( ! empty( $latest_order ) ) ? current( $latest_order )->get_id() : 0;

				if ( $order_id > 0 ) {
					$order = wc_get_order( $order_id );

					if ( ! $order ) {
						$order = false;
					}
				}
			} else {
				if ( ! isset( $_GET[ $id_param ] ) ) { //phpcs:ignore
					return '<p class="woocommerce-notice">Order not found. You cannot access this page directly.</p>';
				}

				// Get the order.
				$order_id  = apply_filters( 'woocommerce_thankyou_order_id', empty( $_GET[ $id_param ] ) ? 0 : intval( $_GET[ $id_param ] ) ); //phpcs:ignore
				$order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET[ $key_param ] ) ? '' : wc_clean( wp_unslash( $_GET[ $key_param ] ) ) ); //phpcs:ignore

				if ( $order_id > 0 ) {
					$order = wc_get_order( $order_id );

					if ( ! $order || $order->get_order_key() !== $order_key ) {
						$order = false;
					}
				}
			}

			// Empty awaiting payment session.
			unset( WC()->session->order_awaiting_payment );

			if ( null !== WC()->session ) {
				if ( ! isset( WC()->cart ) || '' === WC()->cart ) {
					WC()->cart = new WC_Cart();
				}

				if ( ! WC()->cart->is_empty() ) {
					// wc_empty_cart();
					// Empty current cart.
					WC()->cart->empty_cart( true );

					wc_clear_notices();
				}

				wc_print_notices();
			}

			do_action( 'cartflows_thankyou_details_before', $order );

			ob_start();
			echo "<div class='wcf-thankyou-wrap' id='wcf-thankyou-wrap'>";
				wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
			echo '</div>';
			$output = ob_get_clean();
		}

		return $output;
	}

	/**
	 * Load Thank You scripts.
	 *
	 * @return void
	 */
	public function thank_you_scripts() {

		if ( _is_wcf_thankyou_type() ) {

			do_action( 'cartflows_thank_you_scripts' );

			$style = $this->generate_thank_you_style();

			wp_add_inline_style( 'wcf-frontend-global', $style );
		}
	}

	/**
	 * Load DIVI compatibility Thank You style.
	 *
	 * @return void
	 */
	public function add_divi_compatibility_css() {

		global $post;

		$thank_you_id = $post->ID;

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $thank_you_id )
		) {
			wp_enqueue_style( 'wcf-frontend-global-divi', wcf()->utils->get_css_url( 'frontend-divi' ), array(), CARTFLOWS_VER );
		}
	}

	/**
	 * Set thank you as a order received page.
	 *
	 * @param boolean $is_order_page order page.
	 * @return boolean
	 */
	public function set_order_received_page( $is_order_page ) {

		if ( _is_wcf_thankyou_type() ) {
			$is_order_page = true;
		}

		return $is_order_page;
	}

	/**
	 * Generate Thank You Styles.
	 *
	 * @return string
	 */
	public function generate_thank_you_style() {

		global $post;

		if ( _is_wcf_thankyou_type() ) {
			$thank_you_id = $post->ID;
		} else {
			$thank_you_id = _get_wcf_thankyou_id( $post->post_content );
		}

		CartFlows_Font_Families::render_fonts( $thank_you_id );

		$text_color          = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-text-color' );
		$text_font_family    = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-font-family' );
		$text_font_size      = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-font-size' );
		$heading_text_color  = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-color' );
		$heading_font_family = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-font-family' );
		$heading_font_weight = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-font-wt' );
		$container_width     = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-container-width' );
		$section_bg_color    = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-section-bg-color' );

		$show_order_review = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-overview-section' );

		$show_order_details = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-details-section' );

		$show_billing_details = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-billing-section' );

		$show_shipping_details = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-shipping-section' );

		$output = "
		.wcf-thankyou-wrap{
			color: {$text_color};
			font-family: {$text_font_family};
			max-width:{$container_width}px;
			font-size: {$text_font_size}px;
		}

		.woocommerce-order h2.woocommerce-column__title, 
		.woocommerce-order h2.woocommerce-order-details__title, 
		.woocommerce-order .woocommerce-thankyou-order-received,
		.woocommerce-order-details h2,
		.woocommerce-order h2.wc-bacs-bank-details-heading,
		.woocommerce-order h2.woocommerce-order-downloads__title {
			color: {$heading_text_color};
			font-family: {$heading_font_family};
			font-weight: {$heading_font_weight};
		}

		.woocommerce-order ul.order_details,
		.woocommerce-order .woocommerce-order-details,
		.woocommerce-order .woocommerce-customer-details,
		.woocommerce-order .woocommerce-bacs-bank-details,
		.woocommerce-order .woocommerce-order-downloads{
			background-color: {$section_bg_color}
		}
		img.emoji, img.wp-smiley {}
		";

		if ( 'no' == $show_order_review ) {
			$output .= '
			.woocommerce-order ul.order_details{
				display: none;
			}
			';
		}

		if ( 'no' == $show_order_details ) {
			$output .= '
			.woocommerce-order .woocommerce-order-details{
				display: none;
			}
			';
		}

		if ( 'no' == $show_billing_details ) {
			$output .= '
			.woocommerce-order .woocommerce-customer-details .woocommerce-column--billing-address{
				display: none;
			}
			';
		}

		if ( 'no' == $show_shipping_details ) {
			$output .= '
			.woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address{
				display: none;
			}
			';
		}

		if ( 'no' == $show_billing_details && 'no' == $show_shipping_details ) {
			$output .= '
			.woocommerce-order .woocommerce-customer-details{
				display: none;
			}
			';
		}

		return $output;
	}

	/**
	 * Set as a checkout page if it is thank you page.
	 * Thank you page need to be set as a checkout page.
	 * Becauye ayment gateways will not load if it is not checkout.
	 *
	 * @param bool $is_checkout is checkout.
	 *
	 * @return bool
	 */
	public function woo_checkout_flag( $is_checkout ) {

		if ( ! is_admin() ) {
			if ( _is_wcf_thankyou_type() ) {
				$is_checkout = true;
			}
		}

		return $is_checkout;
	}

	/**
	 *  Add custom text on thank you page.
	 *
	 * @param string $woo_text Default text.
	 * @param int    $order order.
	 */
	public function custom_tq_text( $woo_text, $order ) {

		global $post;

		$thank_you_id = $post->ID;
		$new_text     = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-text' );

		if ( ! empty( $new_text ) ) {
			$woo_text = do_shortcode( $new_text );
		}

		return $woo_text;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Thankyou_Markup::get_instance();
