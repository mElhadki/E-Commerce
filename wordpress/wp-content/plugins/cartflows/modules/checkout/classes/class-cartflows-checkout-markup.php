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
class Cartflows_Checkout_Markup {

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

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_checkout_fields' ), 10, 2 );

		/* Show notice if cart is empty */
		add_action( 'cartflows_checkout_cart_empty', array( $this, 'display_woo_notices' ) );

		/* Checkout Shortcode */
		add_shortcode( 'cartflows_checkout', array( $this, 'checkout_shortcode_markup' ) );

		/* Preconfigured cart data */
		add_action( 'wp', array( $this, 'preconfigured_cart_data' ), 1 );

		/* Embed Checkout */
		add_action( 'wp', array( $this, 'shortcode_load_data' ), 999 );

		/* Ajax Endpoint */
		add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'get_ajax_endpoint' ), 10, 2 );

		add_filter( 'cartflows_add_before_main_section', array( $this, 'enable_logo_in_header' ) );

		add_filter( 'cartflows_primary_container_bottom', array( $this, 'show_cartflows_copyright_message' ) );

		add_filter( 'woocommerce_login_redirect', array( $this, 'after_login_redirect' ), 10, 2 );

		add_action( 'wp_ajax_wcf_woo_apply_coupon', array( $this, 'apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_apply_coupon', array( $this, 'apply_coupon' ) );

		add_filter( 'global_cartflows_js_localize', array( $this, 'add_localize_vars' ) );

		/* Global Checkout */
		add_action( 'template_redirect', array( $this, 'global_checkout_template_redirect' ), 1 );

		add_action( 'wp_ajax_wcf_woo_remove_coupon', array( $this, 'remove_coupon' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_remove_coupon', array( $this, 'remove_coupon' ) );

		add_action( 'wp_ajax_wcf_woo_remove_cart_product', array( $this, 'wcf_woo_remove_cart_product' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_remove_cart_product', array( $this, 'wcf_woo_remove_cart_product' ) );

		add_filter( 'woocommerce_paypal_args', array( $this, 'modify_paypal_args' ), 10, 2 );

		add_filter( 'woocommerce_cart_item_name', array( $this, 'wcf_add_remove_label' ), 10, 3 );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_price_to_cart_item' ), 9999 );
	}

	/**
	 * Modify WooCommerce paypal arguments.
	 *
	 * @param array    $args argumenets for payment.
	 * @param WC_Order $order order data.
	 * @return array
	 */
	public function modify_paypal_args( $args, $order ) {

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

		if ( ! $checkout_id ) {
			return $args;
		}

		// Set cancel return URL.
		$args['cancel_return'] = esc_url_raw( $order->get_cancel_order_url_raw( get_permalink( $checkout_id ) ) );

		return $args;
	}

	/**
	 * Modify WooCommerce paypal arguments.
	 *
	 * @param string $product_name product name.
	 * @param object $cart_item cart item.
	 * @param string $cart_item_key cart item key.
	 * @return string
	 */
	public function wcf_add_remove_label( $product_name, $cart_item, $cart_item_key ) {

		$checkout_id = get_the_ID();
		if ( ! $checkout_id ) {
			$checkout_id = ( isset( $_POST['option']['checkout_id'] ) ) ? wp_unslash( $_POST['option']['checkout_id'] ) : '';//phpcs:ignore
		}

		if ( ! empty( $checkout_id ) ) {
			$is_remove_product_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-remove-product-field' );
			if ( 'checkout' === get_post_meta( $checkout_id, 'wcf-step-type', true ) && ( 'yes' === $is_remove_product_option ) ) {
				$remove_label = apply_filters(
					'woocommerce_cart_item_remove_link',
					sprintf(
						'<a href="#" rel="nofollow" class="remove cartflows-icon-close" data-id="%s" data-item-key="%s" ></a>',
						esc_attr( $cart_item['product_id'] ),
						$cart_item_key
					),
					$cart_item_key
				);

				$product_name = $remove_label . $product_name;
			}
		}

		return $product_name;

	}

	/**
	 * Change order button text .
	 *
	 * @param string $woo_button_text place order.
	 * @return string
	 */
	public function place_order_button_text( $woo_button_text ) {

		$checkout_id = get_the_ID();
		if ( ! $checkout_id ) {
			$checkout_id = ( isset( $_POST['option']['checkout_id'] ) ) ? intval( $_POST['option']['checkout_id'] ) : 0; //phpcs:ignore
		}

		$wcf_order_button_text = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-place-order-button-text' );

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
	 * Redirect from default to the global checkout page
	 *
	 * @since 1.0.0
	 */
	public function global_checkout_template_redirect() {

		if ( ! is_checkout() ) {
			return;
		}

		if ( wcf()->utils->is_step_post_type() ) {
			return;
		}

		// Return if the key OR Order paramater is found in the URL for certain Payment gateways.
		if ( isset( $_GET['key'] ) || isset( $_GET['order'] ) ) { //phpcs:ignore
			return;
		}

		// redirect only for cartflows checkout pages.
		$order_pay_endpoint      = get_option( 'woocommerce_checkout_pay_endpoint', 'order-pay' );
		$order_received_endpoint = get_option( 'woocommerce_checkout_order_received_endpoint', 'order-received' );

		$common = Cartflows_Helper::get_common_settings();

		$global_checkout = $common['global_checkout'];

		if (
			isset( $_SERVER['REQUEST_URI'] ) &&
			// ignore on order-pay.
			false === wcf_mb_strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/' . $order_pay_endpoint . '/' ) &&
			// ignore on TY page.
			false === wcf_mb_strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/' . $order_received_endpoint . '/' ) &&
			// ignore if order-pay in query param.
			false === wcf_mb_strpos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $order_pay_endpoint . '=' )
		) {

			if ( '' !== $global_checkout ) {

				$link = apply_filters( 'cartflows_global_checkout_url', get_permalink( $global_checkout ) );

				if ( ! empty( $link ) ) {

					wp_safe_redirect( $link );
					die();
				}
			}
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

			if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {

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
	public function checkout_shortcode_markup( $atts ) {

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

		$checkout_id = intval( $atts['id'] );

		if ( empty( $checkout_id ) ) {

			if ( ! _is_wcf_checkout_type() ) {

				return '<h4>' . __( 'Checkout ID not found', 'cartflows' ) . '</h4>';
			}

			global $post;

			$checkout_id = intval( $post->ID );
		}

		$output = '';

		ob_start();

		do_action( 'cartflows_checkout_form_before', $checkout_id );

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		$template_default = CARTFLOWS_CHECKOUT_DIR . 'templates/embed/checkout-template-simple.php';

		$template_layout = apply_filters( 'cartflows_checkout_layout_template', $checkout_layout );

		if ( file_exists( $template_layout ) ) {
			include $template_layout;
		} else {
			include $template_default;
		}

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

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {

			if ( wp_doing_ajax() ) {
				return;
			} else {

				if ( _is_wcf_checkout_type() ) {
					$checkout_id = $post->ID;
				} else {
					$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
				}

				$global_checkout = intval( Cartflows_Helper::get_common_setting( 'global_checkout' ) );

				if ( ! empty( $global_checkout ) && $checkout_id === $global_checkout ) {

					if ( WC()->cart->is_empty() ) {
						wc_add_notice( __( 'Your cart is currently empty.', 'cartflows' ), 'error' );
					}

					return;
				}

				if ( apply_filters( 'cartflows_skip_configure_cart', false, $checkout_id ) ) {
					return;
				}

				do_action( 'cartflows_checkout_before_configure_cart', $checkout_id );

				$flow_id = wcf()->utils->get_flow_id_from_step_id( $checkout_id );

				if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
					$products = 'dummy';
				} else {
					$products = wcf()->utils->get_selected_checkout_products( $checkout_id );
				}

				if ( ! is_array( $products ) ) {

					if ( 'dummy' === $products ) {

						$args = array(
							'posts_per_page' => 1,
							'orderby'        => 'rand',
							'post_type'      => 'product',
							'meta_query'     => array( //phpcs:ignore
								// Exclude out of stock products.
								array(
									'key'     => '_stock_status',
									'value'   => 'outofstock',
									'compare' => 'NOT IN',
								),
							),
							'tax_query'      => array( //phpcs:ignore
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => 'simple',
								),
							),
						);

						$random_product = get_posts( $args );

						if ( isset( $random_product[0]->ID ) ) {
							$products = array(
								array(
									'product'     => $random_product[0]->ID,
									'add_to_cart' => true,
								),
							);
						} else {
							return;
						}
					} else {
						return;
					}
				}

				/* Empty the current cart */
				WC()->cart->empty_cart();

				if ( is_array( $products ) && empty( $products[0]['product'] ) ) {
					wc_add_notice( __( 'No product is selected. Please select products from the checkout meta settings to continue.', 'cartflows' ), 'error' );
					return;
				}

				/* Set customer session if not set */
				if ( ! is_user_logged_in() && WC()->cart->is_empty() ) {
					WC()->session->set_customer_session_cookie( true );
				}

				$cart_product_count = 0;
				$cart_key           = '';
				$products_new       = array();

				$products = apply_filters( 'cartflows_selected_checkout_products', $products, $checkout_id );

				foreach ( $products as $index => $data ) {

					if ( ! isset( $data['product'] ) ) {
						continue;
					}

					if ( empty( $data['add_to_cart'] ) ) {
						continue;
					}

					if ( apply_filters( 'cartflows_skip_other_products', false, $cart_product_count ) ) {
						break;
					}

					$product_id = $data['product'];
					$_product   = wc_get_product( $product_id );

					if ( ! empty( $_product ) ) {

						$quantity = 1;

						if ( isset( $data['quantity'] ) && ! empty( $data['quantity'] ) ) {
							$quantity = $data['quantity'];
						}

						$discount_type  = isset( $data['discount_type'] ) ? $data['discount_type'] : '';
						$discount_value = ! empty( $data['discount_value'] ) ? $data['discount_value'] : '';
						$_product_price = $_product->get_price( $data['product'] );

						$custom_price = $this->calculate_discount( '', $discount_type, $discount_value, $_product_price );

						$cart_item_data = array();

						if ( ! empty( $custom_price ) ) {

							$cart_item_data = array(
								'custom_price' => $custom_price,
							);
						}

						if ( ! $_product->is_type( 'grouped' ) && ! $_product->is_type( 'external' ) ) {

							if ( $_product->is_type( 'variable' ) ) {

								$default_attributes = $_product->get_default_attributes();

								if ( ! empty( $default_attributes ) ) {

									foreach ( $_product->get_children() as $variation_id ) {

										$single_variation = new WC_Product_Variation( $variation_id );

										if ( $default_attributes == $single_variation->get_attributes() ) {
											$cart_key = WC()->cart->add_to_cart( $variation_id, $quantity, 0, array(), $cart_item_data );
											$cart_product_count++;
										}
									}
								} else {

									$product_childrens = $_product->get_children();

									if ( isset( $product_childrens[0] ) ) {
										$variation      = wc_get_product( $product_childrens[0] );
										$_product_price = $variation->get_price();
										$custom_price   = $this->calculate_discount( '', $discount_type, $discount_value, $_product_price );
										if ( ! empty( $custom_price ) ) {
											$cart_item_data = array(
												'custom_price' => $custom_price,
												'data' => $data,
											);
										}
										$cart_key = WC()->cart->add_to_cart( $product_childrens[0], $quantity, 0, array(), $cart_item_data );
										$cart_product_count++;
									} else {
										echo '<p>' . esc_html__( 'Variations Not set', 'cartflows' ) . '</p>';
									}
								}
							} else {
								$cart_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );
								$cart_product_count++;
							}
						} else {
							$wrong_product_notice = __( 'This product can\'t be purchased', 'cartflows' );
							wc_add_notice( $wrong_product_notice );
							/**
							WC()->cart->add_to_cart( $product_id, $quantity );.
							*/
						}
					}
					$products_new[ $index ] = array(
						'cart_item_key' => $cart_key,
					);
				}

				/* Set checkout products data */
				wcf()->utils->set_selcted_checkout_products( $checkout_id, $products_new );

				/* Since 1.2.2 */
				wcf_do_action_deprecated( 'cartflows_checkout_aftet_configure_cart', array( $checkout_id ), '1.2.2', 'cartflows_checkout_after_configure_cart' );
				do_action( 'cartflows_checkout_after_configure_cart', $checkout_id );
			}
		}
	}

	/**
	 * Load shortcode data.
	 *
	 * @return void
	 */
	public function shortcode_load_data() {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'shortcode_scripts' ), 21 );

			add_action( 'wp_enqueue_scripts', array( $this, 'compatibility_scripts' ), 101 );

			/* Show notices if cart has errors */
			add_action( 'woocommerce_cart_has_errors', 'woocommerce_output_all_notices' );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'order_wrap_div_start' ), 99 );

			add_action( 'woocommerce_checkout_after_order_review', array( $this, 'order_wrap_div_end' ), 99 );

			// Outputting the hidden field in checkout page.
			add_action( 'woocommerce_after_order_notes', array( $this, 'checkout_shortcode_post_id' ), 99 );
			add_action( 'woocommerce_login_form_end', array( $this, 'checkout_shortcode_post_id' ), 99 );

			remove_all_actions( 'woocommerce_checkout_billing' );
			remove_all_actions( 'woocommerce_checkout_shipping' );

			// Hook in actions once.
			add_action( 'woocommerce_checkout_billing', array( WC()->checkout, 'checkout_form_billing' ) );
			add_action( 'woocommerce_checkout_shipping', array( WC()->checkout, 'checkout_form_shipping' ) );

			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form' );

			add_action( 'woocommerce_checkout_order_review', array( $this, 'display_custom_coupon_field' ) );

			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_three_column_layout_fields' ) );

			add_filter( 'woocommerce_cart_totals_coupon_html', array( $this, 'remove_coupon_text' ) );

			add_filter( 'woocommerce_order_button_text', array( $this, 'place_order_button_text' ), 10, 1 );

			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}

			do_action( 'cartflows_checkout_before_shortcode', $checkout_id );
		}
	}

	/**
	 * Render checkout ID hidden field.
	 *
	 * @param array $checkout checkout session data.
	 * @return void
	 */
	public function checkout_shortcode_post_id( $checkout ) {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$flow_id = get_post_meta( $checkout_id, 'wcf-flow-id', true );

		echo '<input type="hidden" class="input-hidden _wcf_flow_id" name="_wcf_flow_id" value="' . intval( $flow_id ) . '">';
		echo '<input type="hidden" class="input-hidden _wcf_checkout_id" name="_wcf_checkout_id" value="' . intval( $checkout_id ) . '">';
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	public function shortcode_scripts() {

		wp_enqueue_style( 'wcf-checkout-template', wcf()->utils->get_css_url( 'checkout-template' ), '', CARTFLOWS_VER );

		wp_enqueue_script(
			'wcf-checkout-template',
			wcf()->utils->get_js_url( 'checkout-template' ),
			array( 'jquery' ),
			CARTFLOWS_VER,
			true
		);

		do_action( 'cartflows_checkout_scripts' );

		$style = $this->generate_style();

		wp_add_inline_style( 'wcf-checkout-template', $style );

	}

	/**
	 * Load compatibility scripts.
	 *
	 * @return void
	 */
	public function compatibility_scripts() {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $checkout_id )
		) {
			wp_enqueue_style( 'wcf-checkout-template-divi', wcf()->utils->get_css_url( 'checkout-template-divi' ), '', CARTFLOWS_VER );
		}

		// Add Flatsome Compatibility css if Flatsome theme is enabled.
		if ( Cartflows_Compatibility::get_instance()->is_flatsome_enabled() ) {
			wp_enqueue_style( 'wcf-checkout-template-flatsome', wcf()->utils->get_css_url( 'checkout-template-flatsome' ), '', CARTFLOWS_VER );
		}

		// Add The7 Compatibility css if The7 theme is enabled.
		if ( Cartflows_Compatibility::get_instance()->is_the_seven_enabled() ) {
			wp_enqueue_style( 'wcf-checkout-template-the-seven', wcf()->utils->get_css_url( 'checkout-template-the-seven' ), '', CARTFLOWS_VER );
		}
	}

	/**
	 * Generate styles.
	 *
	 * @return string
	 */
	public function generate_style() {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		/*Output css variable */
		$output = '';

		CartFlows_Font_Families::render_fonts( $checkout_id );

		$primary_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-primary-color' );

		$base_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-base-font-family' );

		$header_logo_width = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-header-logo-width' );

		/**
		$base_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-base-font-weight' );*/
		$r = '';
		$g = '';
		$b = '';

		$field_tb_padding = '';
		$field_lr_padding = '';

		$field_heading_color  = '';
		$field_color          = '';
		$field_bg_color       = '';
		$field_border_color   = '';
		$field_label_color    = '';
		$submit_tb_padding    = '';
		$submit_lr_padding    = '';
		$hl_bg_color          = '';
		$field_input_size     = '';
		$box_border_color     = '';
		$section_bg_color     = '';
		$submit_button_height = '';
		$submit_color         = '';
		$submit_bg_color      = $primary_color;
		$submit_border_color  = $primary_color;

		$submit_hover_color        = '';
		$submit_bg_hover_color     = $primary_color;
		$submit_border_hover_color = $primary_color;

		$section_heading_color = '';

		$is_advance_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-advance-options-fields' );

		$button_font_family  = '';
		$button_font_weight  = '';
		$input_font_family   = '';
		$input_font_weight   = '';
		$heading_font_family = '';
		$heading_font_weight = '';
		$base_font_family    = $base_font_family;
		/**
		$base_font_weight    = $base_font_weight;*/

		if ( 'yes' == $is_advance_option ) {

			/**
			* Get Font Family and Font Weight weight values
			*/
			$section_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-section-bg-color' );

			$heading_font_family   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-family' );
			$heading_font_weight   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-weight' );
			$section_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-color' );
			$button_font_family    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-family' );
			$button_font_weight    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-weight' );
			$input_font_family     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-family' );
			$input_font_weight     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-weight' );
			$field_tb_padding      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-tb-padding' );
			$field_lr_padding      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-lr-padding' );
			$field_input_size      = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-field-size' );

			$field_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-heading-color' );

			$field_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-color' );

			$field_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-bg-color' );

			$field_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-border-color' );

			$field_label_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-label-color' );

			$submit_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-tb-padding' );

			$submit_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-lr-padding' );

			$submit_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-color' );

			$submit_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-color', $primary_color );

			$submit_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-color', $primary_color );

			$submit_border_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-hover-color', $primary_color );

			$submit_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-hover-color' );

			$submit_bg_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-hover-color', $primary_color );

			$hl_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-hl-bg-color' );

			$box_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-box-border-color' );

			$submit_button_height = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-button-size' );

			/**
			* Get font values
			*/

			if ( 'custom' == $submit_button_height ) {
				$submit_button_height = '38px';
			}

			if ( 'custom' == $field_input_size ) {
				$field_input_size = '38px';
			}
		}
		if ( isset( $primary_color ) ) {

			list($r, $g, $b) = sscanf( $primary_color, '#%02x%02x%02x' );
		}

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $checkout_id ) ) {

			include CARTFLOWS_CHECKOUT_DIR . 'includes/checkout-dynamic-divi-css.php';

		} else {
			include CARTFLOWS_CHECKOUT_DIR . 'includes/checkout-dynamic-css.php';
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

			if ( _is_wcf_checkout_type() ) {

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
	public function save_checkout_fields( $order_id, $posted ) {

		if ( isset( $_POST['_wcf_checkout_id'] ) ) { //phpcs:ignore

			$checkout_id = wc_clean( intval( $_POST['_wcf_checkout_id'] ) ); //phpcs:ignore

			update_post_meta( $order_id, '_wcf_checkout_id', $checkout_id );

			if ( isset( $_POST['_wcf_flow_id'] ) ) { //phpcs:ignore

				$flow_id = wc_clean( intval( $_POST['_wcf_flow_id'] ) ); //phpcs:ignore

				update_post_meta( $order_id, '_wcf_flow_id', $flow_id );
			}
		}

	}

	/**
	 * Enable Logo In Header Of Checkout Page
	 *
	 * @return void
	 */
	public function enable_logo_in_header() {
		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$header_logo_image = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-header-logo-image' );
		$add_image_markup  = '';

		if ( isset( $header_logo_image ) && ! empty( $header_logo_image ) ) {
			$add_image_markup      = '<div class="wcf-checkout-header-image">';
				$add_image_markup .= '<img src="' . $header_logo_image . '" />';
			$add_image_markup     .= '</div>';
		}

		echo $add_image_markup;
	}

	/**
	 * Add text to the bootom of the checkout page.
	 *
	 * @return void
	 */
	public function show_cartflows_copyright_message() {
		$output_string = '';

		$output_string             .= '<div class="wcf-footer-primary">';
			$output_string         .= '<div class="wcf-footer-content">';
				$output_string     .= '<p class="wcf-footer-message">';
					$output_string .= 'Checkout powered by CartFlows';
				$output_string     .= '</p>';
			$output_string         .= '</div>';
		$output_string             .= '</div>';

		echo $output_string;
	}

	/**
	 * Redirect users to our checkout if hidden param
	 *
	 * @param string $redirect redirect url.
	 * @param object $user user.
	 * @return string
	 */
	public function after_login_redirect( $redirect, $user ) {

		if ( isset( $_POST['_wcf_checkout_id'] ) ) { //phpcs:ignore

			$checkout_id = intval( $_POST['_wcf_checkout_id'] ); //phpcs:ignore

			$redirect = get_permalink( $checkout_id );
		}

		return $redirect;
	}

	/**
	 * Display coupon code field after review order fields.
	 */
	public function display_custom_coupon_field() {

		$coupon_enabled = apply_filters( 'woocommerce_coupons_enabled', true );
		$show_coupon    = apply_filters( 'cartflows_show_coupon_field', true );

		if ( ! ( $coupon_enabled && $show_coupon ) ) {
			return;

		}

		$coupon_field = array(
			'field_text'  => __( 'Coupon Code', 'cartflows' ),
			'button_text' => __( 'Apply', 'cartflows' ),
			'class'       => '',
		);

		$coupon_field = apply_filters( 'cartflows_coupon_field_options', $coupon_field );

		ob_start();
		?>
		<div class="wcf-custom-coupon-field <?php echo $coupon_field['class']; ?>" id="wcf_custom_coupon_field">
				<div class="wcf-coupon-col-1">
					<span>
						<input type="text" name="coupon_code" class="input-text wcf-coupon-code-input" placeholder="<?php echo $coupon_field['field_text']; ?>" id="coupon_code" value="">
					</span>
				</div>
				<div class="wcf-coupon-col-2">
					<span>
						<button type="button" class="button wcf-submit-coupon wcf-btn-small" name="apply_coupon" value="Apply"><?php echo $coupon_field['button_text']; ?></button>
					</span>
				</div>
		</div>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Apply filter to change class of remove coupon field.
	 *
	 * @param string $coupon coupon.
	 * @return string
	 */
	public function remove_coupon_text( $coupon ) {

		$coupon = str_replace( 'woocommerce-remove-coupon', 'wcf-remove-coupon', $coupon );
		return $coupon;

	}
	/**
	 * Apply filter to change the placeholder text of coupon field.
	 *
	 * @return string
	 */
	public function coupon_field_placeholder() {
		return apply_filters( 'cartflows_coupon_field_placeholder', __( 'Coupon Code', 'cartflows' ) );
	}

	/**
	 * Apply filter to change the button text of coupon field.
	 *
	 * @return string
	 */
	public function coupon_button_text() {
		return apply_filters( 'cartflows_coupon_button_text', __( 'Apply', 'cartflows' ) );
	}

	/**
	 * Apply coupon on submit of custom coupon form.
	 */
	public function apply_coupon() {

		$response = '';

		check_ajax_referer( 'wcf-apply-coupon', 'security' );
		if ( ! empty( $_POST['coupon_code'] ) ) {
			$result = WC()->cart->add_discount( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) );
		} else {
			wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
		}

		$response = array(
			'status' => $result,
			'msg'    => wc_print_notices( true ),
		);

		echo wp_json_encode( $response );

		die();
	}


	/**
	 * Added ajax nonce to localize variable.
	 *
	 * @param array $vars localize variables.
	 */
	public function add_localize_vars( $vars ) {

		$vars['wcf_validate_coupon_nonce'] = wp_create_nonce( 'wcf-apply-coupon' );

		$vars['wcf_validate_remove_coupon_nonce'] = wp_create_nonce( 'wcf-remove-coupon' );

		$vars['wcf_validate_remove_cart_product_nonce'] = wp_create_nonce( 'wcf-remove-cart-product' );

		$vars['allow_persistance'] = apply_filters( 'cartflows_allow_persistace', 'yes' );

		return $vars;
	}

	/**
	 * Add custom class to the fields to change the UI to three column.
	 *
	 * @param array $fields fields.
	 */
	public function add_three_column_layout_fields( $fields ) {

		if ( empty( $fields['billing']['billing_address_2'] ) ) {

			if ( isset( $fields['billing']['billing_address_1'] ) && is_array( $fields['billing']['billing_address_1'] ) ) {
				$fields['billing']['billing_address_1']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_company'] ) ) {

			if ( isset( $fields['billing']['billing_company'] ) && is_array( $fields['billing']['billing_company'] ) ) {
				$fields['billing']['billing_company']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['shipping']['shipping_company'] ) ) {

			if ( isset( $fields['shipping']['shipping_company'] ) && is_array( $fields['shipping']['shipping_company'] ) ) {
				$fields['shipping']['shipping_company']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_country'] ) ) {

			if ( isset( $fields['billing']['billing_country'] ) && is_array( $fields['billing']['billing_country'] ) ) {
				$fields['billing']['billing_country']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['shipping']['shipping_country'] ) ) {

			if ( isset( $fields['shipping']['shipping_country'] ) && is_array( $fields['shipping']['shipping_country'] ) ) {
				$fields['shipping']['shipping_country']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_phone'] ) ) {

			if ( isset( $fields['billing']['billing_phone'] ) && is_array( $fields['billing']['billing_phone'] ) ) {
				$fields['billing']['billing_phone']['class'][] = 'form-row-full';
			}
		}

		if ( ! empty( $fields['billing']['billing_email'] ) ) {

			if ( isset( $fields['billing']['billing_email'] ) && is_array( $fields['billing']['billing_email'] ) ) {
				$fields['billing']['billing_email']['class'][] = 'form-row-full';
			}
		}

		if ( empty( $fields['shipping']['shipping_address_2'] ) ) {

			if ( isset( $fields['shipping']['shipping_address_1'] ) && is_array( $fields['shipping']['shipping_address_1'] ) ) {
				$fields['shipping']['shipping_address_1']['class'][] = 'form-row-full';
			}
		}

		if ( isset( $fields['billing']['billing_city'] ) &&
		isset( $fields['billing']['billing_state'] ) && isset( $fields['billing']['billing_postcode'] ) ) {

			$fields['billing']['billing_city']['class'][]     = 'wcf-column-33';
			$fields['billing']['billing_state']['class'][]    = 'wcf-column-33';
			$fields['billing']['billing_postcode']['class'][] = 'wcf-column-33';
		}

		if ( isset( $fields['shipping']['shipping_city'] ) &&
		isset( $fields['shipping']['shipping_state'] ) && isset( $fields['shipping']['shipping_postcode'] ) ) {

			$fields['shipping']['shipping_city']['class'][]     = 'wcf-column-33';
			$fields['shipping']['shipping_state']['class'][]    = 'wcf-column-33';
			$fields['shipping']['shipping_postcode']['class'][] = 'wcf-column-33';
		}

		return $fields;
	}

	/**
	 * Add opening dev
	 *
	 * @since 1.0.0
	 */
	public function order_wrap_div_start() {

		echo "<div class='wcf-order-wrap'> ";
	}

	/**
	 * Add closing dev
	 *
	 * @since 1.0.0
	 */
	public function order_wrap_div_end() {

		echo '</div> ';
	}
	/**
	 * Remove coupon.
	 */
	public function remove_coupon() {

		check_ajax_referer( 'wcf-remove-coupon', 'security' );
		$coupon = isset( $_POST['coupon_code'] ) ? wc_clean( wp_unslash( $_POST['coupon_code'] ) ) : false; //phpcs:ignore

		if ( empty( $coupon ) ) {
			echo "<div class='woocommerce-error'>" . esc_html__( 'Sorry there was a problem removing this coupon.', 'cartflows' );
		} else {
			WC()->cart->remove_coupon( $coupon );
			echo "<div class='woocommerce-error'>" . esc_html__( 'Coupon has been removed.', 'cartflows' ) . '</div>';
		}
			wc_print_notices();
		wp_die();
	}

	/**
	 * Remove cart item.
	 */
	public function wcf_woo_remove_cart_product() {

		check_ajax_referer( 'wcf-remove-cart-product', 'security' );
		$product_key   = isset( $_POST['p_key'] ) ? wc_clean( wp_unslash( $_POST['p_key'] ) ) : false; //phpcs:ignore
		$product_id    = isset( $_POST['p_id'] ) ? wc_clean( wp_unslash( $_POST['p_id'] ) ) : ''; //phpcs:ignore
		$product_title = get_the_title( $product_id );

		$needs_shipping = false;

		if ( empty( $product_key ) ) {
			$msg = "<div class='woocommerce-message'>" . __( 'Sorry there was a problem removing ', 'cartflows' ) . $product_title;
		} else {
			WC()->cart->remove_cart_item( $product_key );
			$msg = "<div class='woocommerce-message'>" . $product_title . __( ' has been removed.', 'cartflows' ) . '</div>';
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			if ( $values['data']->needs_shipping() ) {
				$needs_shipping = true;
				break;
			}
		}

		$response = array(
			'need_shipping' => $needs_shipping,
			'msg'           => $msg,
		);

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Calculate discount for product.
	 *
	 * @param string $discount_coupon discount coupon.
	 * @param string $discount_type discount type.
	 * @param int    $discount_value discount value.
	 * @param int    $_product_price product price.
	 * @return int
	 * @since 1.1.5
	 */
	public function calculate_discount( $discount_coupon, $discount_type, $discount_value, $_product_price ) {

		$custom_price = '';

		if ( ! empty( $discount_type ) ) {
			if ( 'discount_percent' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $_product_price - ( ( $_product_price * $discount_value ) / 100 );
				}
			} elseif ( 'discount_price' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $_product_price - $discount_value;
				}
			} elseif ( 'coupon' === $discount_type ) {

				if ( ! empty( $discount_coupon ) ) {
					WC()->cart->add_discount( $discount_coupon );
				}
			}
		}

		return $custom_price;
	}

	/**
	 * Preserve the custom item price added by Variations & Quantity feature
	 *
	 * @param array $cart_object cart object.
	 * @since 1.0.0
	 */
	public function custom_price_to_cart_item( $cart_object ) {

		if ( wp_doing_ajax() && ! WC()->session->__isset( 'reload_checkout' ) ) {

			foreach ( $cart_object->cart_contents as $key => $value ) {

				if ( isset( $value['custom_price'] ) ) {

					$custom_price = floatval( $value['custom_price'] );
					$value['data']->set_price( $custom_price );
				}
			}
		}
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Checkout_Markup::get_instance();
