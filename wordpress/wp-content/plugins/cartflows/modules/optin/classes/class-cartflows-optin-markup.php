<?php
/**
 * Checkout markup.
 *
 * @package CartFlows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Optin_Markup {

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

		/* Set is checkout flag */
		add_filter( 'woocommerce_is_checkout', array( $this, 'woo_checkout_flag' ), 9999 );

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_optin_fields' ), 10, 2 );

		/* Optin Shortcode */
		add_shortcode( 'cartflows_optin', array( $this, 'optin_shortcode_markup' ) );

		/* Preconfigured cart data */
		add_action( 'wp', array( $this, 'preconfigured_cart_data' ), 1 );

		/* Optin shortcode data */
		add_action( 'wp', array( $this, 'shortcode_load_data' ), 1000 );

		/* Ajax Endpoint */
		add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'get_ajax_endpoint' ), 10, 2 );
		add_filter( 'woocommerce_login_redirect', array( $this, 'after_login_redirect' ), 10, 2 );

		/* Optin Fields */
		add_filter( 'woocommerce_default_address_fields', array( $this, 'set_optin_default_fields' ), 1000 );
		/**
		* It may required later
		* add_filter( 'woocommerce_checkout_fields', array( $this, 'set_optin_fields' ) );
		*/
		add_filter( 'woocommerce_billing_fields', array( $this, 'billing_optin_fields' ), 1000, 2 );

		add_filter( 'woocommerce_checkout_required_field_notice', array( $this, 'change_field_label_in_required_notice' ), 100, 2 );

		$this->elementor_editor_compatibility();
	}

	/**
	 * Elementor editor compatibility.
	 */
	public function elementor_editor_compatibility() {

		if ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] && is_admin() ) { //phpcs:ignore

			if ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) { //phpcs:ignore

				if ( _wcf_check_is_optin_by_id( intval( $_GET['post'] ) ) ) { //phpcs:ignore

					/* Submit Button */
					add_filter( 'woocommerce_order_button_text', array( $this, 'place_order_button_text' ), 10, 1 );
				}
			}
		}
	}

	/**
	 * Change order button text .
	 *
	 * @param string $woo_button_text place order.
	 * @return string
	 */
	public function place_order_button_text( $woo_button_text ) {

		$optin_id = get_the_ID();

		$wcf_order_button_text = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-button-text' );

		if ( ! empty( $wcf_order_button_text ) ) {
			$woo_button_text = $wcf_order_button_text;
		}

		return $woo_button_text;
	}

	/**
	 * Display all WooCommerce notices.
	 *
	 * @since 1.1.5
	 */
	public function display_woo_notices() {

		if ( null != WC()->session && function_exists( 'woocommerce_output_all_notices' ) ) {
			woocommerce_output_all_notices();
		}
	}

	/**
	 * Check for checkout flag
	 *
	 * @param bool $is_checkout is checkout.
	 *
	 * @return bool
	 */
	public function woo_checkout_flag( $is_checkout ) {

		if ( ! is_admin() ) {

			if ( _is_wcf_optin_type() ) {

				$is_checkout = true;
			}
		}

		return $is_checkout;
	}

	/**
	 * Render checkout shortcode markup.
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	public function optin_shortcode_markup( $atts ) {

		if ( ! function_exists( 'wc_print_notices' ) ) {
			$notice_out  = '<p class="woocommerce-notice">' . __( 'WooCommerce functions do not exist. If you are in an IFrame, please reload it.', 'cartflows' ) . '</p>';
			$notice_out .= '<button onClick="location.reload()">' . __( 'Click Here to Reload', 'cartflows' ) . '</button>';

			return $notice_out;
		}

		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts
		);

		$optin_id = intval( $atts['id'] );

		if ( empty( $optin_id ) ) {

			if ( ! _is_wcf_optin_type() ) {

				return '<h4>' . __( 'Please place shortcode on Optin step-type only.', 'cartflows' ) . '</h4>';
			}

			global $post;

			$optin_id = intval( $post->ID );
		}

		$output = '';

		ob_start();

		do_action( 'cartflows_optin_form_before', $optin_id );

		$optin_layout = 'default';

		$template_default = CARTFLOWS_OPTIN_DIR . 'templates/optin-template-simple.php';

		include $template_default;

		$output .= ob_get_clean();

		return $output;
	}

	/**
	 * Configure Cart Data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function preconfigured_cart_data() {

		if ( is_admin() ) {
			return;
		}

		global $post;

		if ( _is_wcf_optin_type() ) {

			if ( wp_doing_ajax() ) {
				return;
			} else {

				$optin_id = $post->ID;

				do_action( 'cartflows_optin_before_configure_cart', $optin_id );

				$products = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-optin-product' );

				if ( ! is_array( $products ) ) {
					return;
				}

				/* Empty the current cart */
				WC()->cart->empty_cart();

				if ( is_array( $products ) && count( $products ) < 1 ) {
					wc_add_notice( __( 'No product is selected. Please select a Simple, Virtual and Free product from the meta settings.', 'cartflows' ), 'error' );
					return;
				}

				/* Set customer session if not set */
				if ( ! is_user_logged_in() && WC()->cart->is_empty() ) {
					WC()->session->set_customer_session_cookie( true );
				}

				$product_id = reset( $products );
				$_product   = wc_get_product( $product_id );

				if ( ! empty( $_product ) ) {

					if ( $_product->is_type( 'simple' ) && $_product->is_virtual() ) {

						if ( $_product->get_price() > 0 ) {
							wc_add_notice( __( 'Please update the selected product\'s price to zero (0).', 'cartflows' ), 'error' );
						} else {

							$quantity = 1;

							WC()->cart->add_to_cart( $product_id, $quantity );
						}
					} else {

						wc_add_notice( __( 'Please select a Simple, Virtual and Free product.', 'cartflows' ), 'error' );
					}
				} else {

					wc_add_notice( __( 'Please select a Simple, Virtual and Free product.', 'cartflows' ), 'error' );
				}

				do_action( 'cartflows_optin_after_configure_cart', $optin_id );
			}
		}
	}

	/**
	 * Load shortcode data.
	 *
	 * @return void
	 */
	public function shortcode_load_data() {

		if ( _is_wcf_optin_type() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'shortcode_scripts' ), 21 );

			add_action( 'wp_enqueue_scripts', array( $this, 'compatibility_scripts' ), 101 );

			/* Show notices if cart has errors */
			add_action( 'woocommerce_cart_has_errors', 'woocommerce_output_all_notices' );

			// Outputting the hidden field in checkout page.
			add_action( 'woocommerce_after_order_notes', array( $this, 'checkout_shortcode_post_id' ), 99 );
			add_action( 'woocommerce_login_form_end', array( $this, 'checkout_shortcode_post_id' ), 99 );

			/* Remove unnecessary option */
			add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
			add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

			remove_all_actions( 'woocommerce_before_checkout_form' );
			remove_all_actions( 'woocommerce_checkout_billing' );
			remove_all_actions( 'woocommerce_checkout_shipping' );
			remove_all_actions( 'woocommerce_checkout_before_order_review' );
			remove_all_actions( 'woocommerce_checkout_order_review' );
			remove_all_actions( 'woocommerce_checkout_after_order_review' );
			add_filter( 'woocommerce_cart_needs_payment', '__return_false' );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'disable_payment_gateways' ) );

			/* Paypal Expresss remove */

			if ( function_exists( 'wc_gateway_ppec' ) ) {
				remove_action( 'wp_enqueue_scripts', array( wc_gateway_ppec()->cart, 'enqueue_scripts' ) );
			}

			// Hook in actions once.
			add_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
			add_action( 'woocommerce_checkout_billing', array( WC()->checkout, 'checkout_form_billing' ) );
			add_action( 'woocommerce_checkout_shipping', array( WC()->checkout, 'checkout_form_shipping' ) );
			add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

			/* Submit Button */
			add_filter( 'woocommerce_order_button_text', array( $this, 'place_order_button_text' ), 10, 1 );

			add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', '__return_false' );

			global $post;

			$optin_id = $post->ID;

			do_action( 'cartflows_optin_before_shortcode', $optin_id );
		}
	}

	/**
	 * Disable payment gateways.
	 *
	 * @param array $available_gateways gateways.
	 * @return array
	 */
	public function disable_payment_gateways( $available_gateways ) {

		if ( ! is_admin() ) {

			$available_gateways = array();
		}

		return $available_gateways;
	}

	/**
	 * Render checkout ID hidden field.
	 *
	 * @param array $checkout checkout session data.
	 * @return void
	 */
	public function checkout_shortcode_post_id( $checkout ) {

		if ( ! _is_wcf_optin_type() ) {
			return;
		}

		global $post;

		$optin_id = $post->ID;

		$flow_id = get_post_meta( $optin_id, 'wcf-flow-id', true );

		echo '<input type="hidden" class="input-hidden _wcf_flow_id" name="_wcf_flow_id" value="' . intval( $flow_id ) . '">';
		echo '<input type="hidden" class="input-hidden _wcf_optin_id" name="_wcf_optin_id" value="' . intval( $optin_id ) . '">';
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	public function shortcode_scripts() {

		wp_enqueue_style( 'wcf-optin-template', wcf()->utils->get_css_url( 'optin-template' ), '', CARTFLOWS_VER );

		wp_enqueue_script(
			'wcf-optin-template',
			wcf()->utils->get_js_url( 'optin-template' ),
			array( 'jquery' ),
			CARTFLOWS_VER,
			true
		);

		do_action( 'cartflows_optin_scripts' );

		$style = $this->generate_style();

		wp_add_inline_style( 'wcf-optin-template', $style );
	}

	/**
	 * Load compatibility scripts.
	 *
	 * @return void
	 */
	public function compatibility_scripts() {

		global $post;

		$optin_id = $post->ID;

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $optin_id )
		) {
			wp_enqueue_style( 'wcf-optin-template-divi', wcf()->utils->get_css_url( 'optin-template-divi' ), '', CARTFLOWS_VER );
		}
	}

	/**
	 * Generate styles.
	 *
	 * @return string
	 */
	public function generate_style() {

		global $post;

		$optin_id = $post->ID;

		/* Load all fonts */
		CartFlows_Font_Families::render_fonts( $optin_id );

		$r      = '';
		$g      = '';
		$b      = '';
		$output = '';

		/* Global */
		$primary_color    = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-primary-color' );
		$base_font_family = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-base-font-family' );

		/* Input Fields */
		$input_font_family  = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-input-font-family' );
		$input_font_weight  = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-input-font-weight' );
		$field_input_size   = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-input-field-size' );
		$field_tb_padding   = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-tb-padding' );
		$field_lr_padding   = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-lr-padding' );
		$field_color        = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-color' );
		$field_bg_color     = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-bg-color' );
		$field_border_color = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-border-color' );
		$field_label_color  = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-field-label-color' );

		if ( 'custom' == $field_input_size ) {
			$field_input_size = '38px';
		}

		/* Submit Button */
		$submit_button_width       = '100%';
		$optin_button_position     = '';
		$button_font_size          = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-font-size' );
		$button_font_family        = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-button-font-family' );
		$button_font_weight        = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-button-font-weight' );
		$submit_button_height      = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-button-size' );
		$submit_tb_padding         = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-tb-padding' );
		$submit_lr_padding         = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-lr-padding' );
		$submit_button_position    = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-button-position' );
		$submit_color              = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-color' );
		$submit_hover_color        = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-hover-color' );
		$submit_bg_color           = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-bg-color', $primary_color );
		$submit_bg_hover_color     = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-bg-hover-color', $primary_color );
		$submit_border_color       = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-border-color', $primary_color );
		$submit_border_hover_color = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-submit-border-hover-color', $primary_color );

		if ( 'custom' == $submit_button_height ) {
			$submit_button_height = '38px';
			$submit_button_width  = 'auto';

			switch ( $submit_button_position ) {
				case 'left':
					$optin_button_position = '0 auto 0 0';
					break;
				case 'center':
					$optin_button_position = '0 auto';
					break;
				case 'right':
					$optin_button_position = '0 0 0 auto';
					break;

				default:
					$optin_button_position = '0 auto';
					break;
			}
		}

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $optin_id ) ) {

			include CARTFLOWS_OPTIN_DIR . 'includes/optin-dynamic-divi-css.php';

		} else {
			include CARTFLOWS_OPTIN_DIR . 'includes/optin-dynamic-css.php';
		}

		return $output;
	}

	/**
	 * Get ajax end points.
	 *
	 * @param string $endpoint_url end point URL.
	 * @param string $request end point request.
	 * @return string
	 */
	public function get_ajax_endpoint( $endpoint_url, $request ) {

		global $post;

		if ( ! empty( $post ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {

			if ( _is_wcf_optin_type() ) {

				if ( mb_strpos( $endpoint_url, 'checkout', 0, 'utf-8' ) === false ) {

					if ( '' === $request ) {
						$query_args = array(
							'wc-ajax' => '%%endpoint%%',
						);
					} else {
						$query_args = array(
							'wc-ajax' => $request,
						);
					}

					$uri = explode( '?', $_SERVER['REQUEST_URI'], 2 ); //phpcs:ignore
					$uri = $uri[0];

					$endpoint_url = esc_url( add_query_arg( $query_args, $uri ) );
				}
			}
		}

		return $endpoint_url;
	}


	/**
	 * Save checkout fields.
	 *
	 * @param int   $order_id order id.
	 * @param array $posted posted data.
	 * @return void
	 */
	public function save_optin_fields( $order_id, $posted ) {

		if ( isset( $_POST['_wcf_optin_id'] ) ) { //phpcs:ignore

			$optin_id = wc_clean( wp_unslash( $_POST['_wcf_optin_id'] ) ); //phpcs:ignore

			update_post_meta( $order_id, '_wcf_optin_id', $optin_id );

			if ( isset( $_POST['_wcf_flow_id'] ) ) { //phpcs:ignore

				$flow_id = wc_clean( wp_unslash( $_POST['_wcf_flow_id'] ) ); //phpcs:ignore

				update_post_meta( $order_id, '_wcf_flow_id', $flow_id );
			}
		}

	}

	/**
	 * Redirect users to our checkout if hidden param
	 *
	 * @param string $redirect redirect url.
	 * @param object $user user.
	 * @return string
	 */
	public function after_login_redirect( $redirect, $user ) {

		if ( isset( $_POST['_wcf_optin_id'] ) ) { //phpcs:ignore

			$optin_id = intval( $_POST['_wcf_optin_id'] ); //phpcs:ignore

			$redirect = get_permalink( $optin_id );
		}

		return $redirect;
	}


	/**
	 * Add custom class to the fields to change the UI to three column.
	 *
	 * @param array $fields fields.
	 */
	public function set_optin_default_fields( $fields ) {

		if ( _is_wcf_optin_type() ) {

			global $post;

			$optin_id = $post->ID;
		} else {

			if ( _is_wcf_doing_optin_ajax() && wcf()->utils->get_optin_id_from_post_data() ) {

				$optin_id = wcf()->utils->get_optin_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$first_name = $fields['first_name'];
		$last_name  = $fields['last_name'];

		/* Make fields required */
		$first_name['required'] = true;
		$last_name['required']  = true;

		$fields = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
		);

		return apply_filters( 'cartflows_optin_default_fields', $fields, $optin_id );
	}

	/**
	 * Add custom class to the fields to change the UI to three column.
	 *
	 * @param array $fields fields.
	 */
	public function set_optin_fields( $fields ) {

		if ( _is_wcf_optin_type() ) {

			global $post;

			$optin_id = $post->ID;
		} else {

			if ( _is_wcf_doing_optin_ajax() && wcf()->utils->get_optin_id_from_post_data() ) {

				$optin_id = wcf()->utils->get_optin_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$billing_first_name = $fields['billing']['billing_first_name'];
		$billing_last_name  = $fields['billing']['billing_last_name'];
		$billing_email      = $fields['billing']['billing_email'];

		$fields['billing'] = array(
			'billing_first_name' => $billing_first_name,
			'billing_last_name'  => $billing_last_name,
			'billing_email'      => $billing_email,
		);

		return apply_filters( 'cartflows_optin_fields', $fields, $optin_id );
	}

	/**
	 * Billing field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	public function billing_optin_fields( $fields, $country ) {

		if ( _is_wcf_optin_type() ) {

			global $post;

			$optin_id = $post->ID;
		} else {

			if ( _is_wcf_doing_optin_ajax() && wcf()->utils->get_optin_id_from_post_data() ) {
				$optin_id = wcf()->utils->get_optin_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$billing_first_name = $fields['billing_first_name'];
		$billing_last_name  = $fields['billing_last_name'];
		$billing_email      = $fields['billing_email'];

		/* Make fields required */
		$billing_first_name['required'] = true;
		$billing_last_name['required']  = true;

		$fields = array(
			'billing_first_name' => $billing_first_name,
			'billing_last_name'  => $billing_last_name,
			'billing_email'      => $billing_email,
		);

		return apply_filters( 'cartflows_billing_optin_fields', $fields, $country, $optin_id );
	}


	/**
	 * Replace billing label.
	 *
	 * @param string $notice Notice.
	 * @param string $field_label Field name.
	 * @return string
	 */
	public function change_field_label_in_required_notice( $notice, $field_label ) {

		if ( _is_wcf_doing_optin_ajax() ) {

			$notice = str_replace( 'Billing ', '', $notice );
		}

		return $notice;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Optin_Markup::get_instance();
