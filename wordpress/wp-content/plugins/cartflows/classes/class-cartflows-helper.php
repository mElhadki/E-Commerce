<?php
/**
 * CARTFLOWS Helper.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Helper.
 */
class Cartflows_Helper {

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $common = null;

	/**
	 * Common Debug data
	 *
	 * @var zapier
	 */
	private static $debug_data = null;


	/**
	 * Permalink settings
	 *
	 * @var permalink_setting
	 */
	private static $permalink_setting = null;

	/**
	 * Google Analytics Settings
	 *
	 * @var permalink_setting
	 */
	private static $google_analytics_settings = null;

	/**
	 * Installed Plugins
	 *
	 * @since 1.1.4
	 *
	 * @access private
	 * @var array Installed plugins list.
	 */
	private static $installed_plugins = null;

	/**
	 * Checkout Fields
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Facebook pixel global data
	 *
	 * @var faceboook
	 */
	private static $facebook = null;


	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param  string  $key     The option key.
	 * @param  mixed   $default Option default value if option is not available.
	 * @param  boolean $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return string           Return the option value
	 */
	public static function get_admin_settings_option( $key, $default = false, $network_override = false ) {

		// Get the site-wide option if we're in the network admin.
		if ( $network_override && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		return $value;
	}

	/**
	 * Updates an option from the admin settings page.
	 *
	 * @param string $key       The option key.
	 * @param mixed  $value     The value to update.
	 * @param bool   $network   Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	public static function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Get single setting
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	public static function get_common_setting( $key = '', $default = '' ) {
		$settings = self::get_common_settings();

		if ( $settings && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}

	/**
	 * Get single debug options
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	public static function get_debug_setting( $key = '', $default = '' ) {
		$debug_data = self::get_debug_settings();

		if ( $debug_data && array_key_exists( $key, $debug_data ) ) {
			return $debug_data[ $key ];
		}

		return $default;
	}

	/**
	 * Get required plugins for page builder
	 *
	 * @since 1.1.4
	 *
	 * @param  string $page_builder_slug Page builder slug.
	 * @param  string $default Default page builder.
	 * @return array selected page builder required plugins list.
	 */
	public static function get_required_plugins_for_page_builder( $page_builder_slug = '', $default = 'elementor' ) {
		$plugins = self::get_plugins_groupby_page_builders();

		if ( array_key_exists( $page_builder_slug, $plugins ) ) {
			return $plugins[ $page_builder_slug ];
		}

		return $plugins[ $default ];
	}

	/**
	 * Get Plugins list by page builder.
	 *
	 * @since 1.1.4
	 *
	 * @return array Required Plugins list.
	 */
	public static function get_plugins_groupby_page_builders() {

		$divi_status  = self::get_plugin_status( 'divi-builder/divi-builder.php' );
		$theme_status = 'not-installed';
		if ( $divi_status ) {
			if ( true === Cartflows_Compatibility::get_instance()->is_divi_theme_installed() ) {
				$theme_status = 'installed';
				if ( false === Cartflows_Compatibility::get_instance()->is_divi_enabled() ) {
					$theme_status = 'deactivate';
					$divi_status  = 'activate';
				} else {
					$divi_status = '';
				}
			}
		}

		$plugins = array(
			'elementor' => array(
				'title'   => 'Elementor',
				'plugins' => array(
					array(
						'slug'   => 'elementor', // For download from wp.org.
						'init'   => 'elementor/elementor.php',
						'status' => self::get_plugin_status( 'elementor/elementor.php' ),
					),
				),
			),
			'gutenberg' => array(
				'title'   => 'Ultimate Addons for Gutenberg',
				'plugins' => array(
					array(
						'slug'   => 'ultimate-addons-for-gutenberg', // For download from wp.org.
						'init'   => 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
						'status' => self::get_plugin_status( 'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php' ),
					),
				),
			),
			'divi'      => array(
				'title'         => 'Divi',
				'theme-status'  => $theme_status,
				'plugin-status' => $divi_status,
				'plugins'       => array(
					array(
						'slug'   => 'divi-builder', // For download from wp.org.
						'init'   => 'divi-builder/divi-builder.php',
						'status' => $divi_status,
					),
				),
			),
		);

		$plugins['beaver-builder'] = array(
			'title'   => 'Beaver Builder',
			'plugins' => array(),
		);

		// Check Pro Exist.
		if ( file_exists( WP_PLUGIN_DIR . '/bb-plugin/fl-builder.php' ) && ! is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-plugin',
				'init'   => 'bb-plugin/fl-builder.php',
				'status' => self::get_plugin_status( 'bb-plugin/fl-builder.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'beaver-builder-lite-version', // For download from wp.org.
				'init'   => 'beaver-builder-lite-version/fl-builder.php',
				'status' => self::get_plugin_status( 'beaver-builder-lite-version/fl-builder.php' ),
			);
		}

		if ( file_exists( WP_PLUGIN_DIR . '/bb-ultimate-addon/bb-ultimate-addon.php' ) && ! is_plugin_active( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ) ) {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'bb-ultimate-addon',
				'init'   => 'bb-ultimate-addon/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'bb-ultimate-addon/bb-ultimate-addon.php' ),
			);
		} else {
			$plugins['beaver-builder']['plugins'][] = array(
				'slug'   => 'ultimate-addons-for-beaver-builder-lite', // For download from wp.org.
				'init'   => 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php',
				'status' => self::get_plugin_status( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ),
			);
		}

		return $plugins;
	}

	/**
	 * Get plugin status
	 *
	 * @since 1.1.4
	 *
	 * @param  string $plugin_init_file Plguin init file.
	 * @return mixed
	 */
	public static function get_plugin_status( $plugin_init_file ) {

		if ( null == self::$installed_plugins ) {
			self::$installed_plugins = get_plugins();
		}

		if ( ! isset( self::$installed_plugins[ $plugin_init_file ] ) ) {
			return 'install';
		} elseif ( ! is_plugin_active( $plugin_init_file ) ) {
			return 'activate';
		} else {
			return;
		}
	}

	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	public static function get_common_settings() {

		if ( null === self::$common ) {

			$common_default = apply_filters(
				'cartflows_common_settings_default',
				array(
					'disallow_indexing'    => 'disable',
					'global_checkout'      => '',
					'default_page_builder' => 'elementor',
				)
			);

			$common = self::get_admin_settings_option( '_cartflows_common', false, false );

			$common = wp_parse_args( $common, $common_default );

			if ( ! did_action( 'wp' ) ) {
				return $common;
			} else {
				self::$common = $common;
			}
		}

		return self::$common;
	}

	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_debug_settings() {

		if ( null === self::$debug_data ) {

			$debug_data_default = apply_filters(
				'cartflows_debug_settings_default',
				array(
					'allow_minified_files' => 'disable',
				)
			);

			$debug_data = self::get_admin_settings_option( '_cartflows_debug_data', false, false );

			$debug_data = wp_parse_args( $debug_data, $debug_data_default );

			if ( ! did_action( 'wp' ) ) {
				return $debug_data;
			} else {
				self::$debug_data = $debug_data;
			}
		}

		return self::$debug_data;
	}


	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_permalink_settings() {

		if ( null === self::$permalink_setting ) {

			$permalink_default = apply_filters(
				'cartflows_permalink_settings_default',
				array(
					'permalink'           => CARTFLOWS_STEP_POST_TYPE,
					'permalink_flow_base' => CARTFLOWS_FLOW_POST_TYPE,
					'permalink_structure' => '',

				)
			);

			$permalink_data = self::get_admin_settings_option( '_cartflows_permalink', false, false );

			$permalink_data = wp_parse_args( $permalink_data, $permalink_default );

			if ( ! did_action( 'wp' ) ) {
				return $permalink_data;
			} else {
				self::$permalink_setting = $permalink_data;
			}
		}

		return self::$permalink_setting;
	}


	/**
	 * Get debug settings data.
	 *
	 * @return  array.
	 */
	public static function get_google_analytics_settings() {

		if ( null === self::$google_analytics_settings ) {

			$google_analytics_settings_default = apply_filters(
				'cartflows_google_analytics_settings_default',
				array(
					'enable_google_analytics'          => 'disable',
					'enable_google_analytics_for_site' => 'disable',
					'google_analytics_id'              => '',
					'enable_begin_checkout'            => 'disable',
					'enable_add_to_cart'               => 'disable',
					'enable_add_payment_info'          => 'disable',
					'enable_purchase_event'            => 'disable',
				)
			);

			$google_analytics_settings_data = self::get_admin_settings_option( '_cartflows_google_analytics', false, true );

			$google_analytics_settings_data = wp_parse_args( $google_analytics_settings_data, $google_analytics_settings_default );

			if ( ! did_action( 'wp' ) ) {
				return $google_analytics_settings_data;
			} else {
				self::$google_analytics_settings = $google_analytics_settings_data;
			}
		}

		return self::$google_analytics_settings = $google_analytics_settings_data; //phpcs:ignore
	}

	/**
	 * Get Checkout field.
	 *
	 * @param string $key Field key.
	 * @param int    $post_id Post id.
	 * @return array.
	 */
	public static function get_checkout_fields( $key, $post_id ) {

		$saved_fields = get_post_meta( $post_id, 'wcf_fields_' . $key, true );

		if ( ! $saved_fields ) {
			$saved_fields = array();
		}

		$fields = array_filter( $saved_fields );

		if ( empty( $fields ) ) {
			if ( 'billing' === $key || 'shipping' === $key ) {

				$fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), $key . '_' );

				update_post_meta( $post_id, 'wcf_fields_' . $key, $fields );
			}
		}

		return $fields;
	}

	/**
	 * Add Checkout field.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param array  $field_data Field data.
	 * @param int    $post_id Post id.
	 * @return  boolean.
	 */
	public static function add_checkout_field( $type, $field_key, $field_data = array(), $post_id ) {

		$fields = self::get_checkout_fields( $type, $post_id );

		$fields[ $field_key ] = $field_data;

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}

	/**
	 * Get checkout fields settings.
	 *
	 * @param string $type Field type.
	 * @param string $field_key Field key.
	 * @param int    $post_id Post id.
	 * @return  array.
	 */
	public static function delete_checkout_field( $type, $field_key, $post_id ) {

		$fields = self::get_checkout_fields( $type, $post_id );

		if ( isset( $fields[ $field_key ] ) ) {
			unset( $fields[ $field_key ] );
		}

		update_post_meta( $post_id, 'wcf_fields_' . $type, $fields );

		return true;
	}

	/**
	 * Get checkout fields settings.
	 *
	 * @return  array.
	 */
	public static function get_checkout_fields_settings() {

		if ( null === self::$checkout_fields ) {
			$checkout_fields_default = array(
				'enable_customization'  => 'disable',
				'enable_billing_fields' => 'disable',
			);

			$billing_fields = self::get_checkout_fields( 'billing' );

			if ( is_array( $billing_fields ) && ! empty( $billing_fields ) ) {

				foreach ( $billing_fields as $key => $value ) {

					$checkout_fields_default[ $key ] = 'enable';
				}
			}

			$checkout_fields = self::get_admin_settings_option( '_wcf_checkout_fields', false, false );

			self::$checkout_fields = wp_parse_args( $checkout_fields, $checkout_fields_default );
		}

		return self::$checkout_fields;
	}

	/**
	 * Get meta options
	 *
	 * @since 1.0.0
	 * @param  int    $post_id     Product ID.
	 * @param  string $key      Meta Key.
	 * @param  string $default      Default value.
	 * @return string           Meta Value.
	 */
	public static function get_meta_option( $post_id, $key, $default = '' ) {

		$value = get_post_meta( $post_id, $key, true );

		if ( ! $value ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Save meta option
	 *
	 * @since 1.0.0
	 * @param  int   $post_id     Product ID.
	 * @param  array $args      Arguments array.
	 */
	public static function save_meta_option( $post_id, $args = array() ) {

		if ( is_array( $args ) && ! empty( $args ) ) {

			foreach ( $args as $key => $value ) {

				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	/**
	 * Check if Elementor page builder is installed
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public static function is_elementor_installed() {
		$path    = 'elementor/elementor.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}

	/**
	 * Check if Step has product assigned.
	 *
	 * @since 1.0.0
	 * @param int $step_id step ID.
	 *
	 * @access public
	 */
	public static function has_product_assigned( $step_id ) {

		$step_type = get_post_meta( $step_id, 'wcf-step-type', true );

		if ( 'checkout' == $step_type ) {
			$product = get_post_meta( $step_id, 'wcf-checkout-products', true );
		} else {
			$product = get_post_meta( $step_id, 'wcf-offer-product', true );
		}

		if ( ! empty( $product ) ) {
			return true;
		}
		return false;

	}

	/**
	 * Get attributes for cartflows wrap.
	 *
	 * @since 1.1.4
	 *
	 * @access public
	 */
	public static function get_cartflows_container_atts() {

		$attributes  = apply_filters( 'cartflows_container_atts', array() );
		$atts_string = '';

		foreach ( $attributes as $key => $value ) {

			if ( ! $value ) {
				continue;
			}

			if ( true === $value ) {
				$atts_string .= esc_html( $key ) . ' ';
			} else {
				$atts_string .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
			}
		}

		return $atts_string;
	}

	/**
	 * Get facebook pixel settings.
	 *
	 * @return  facebook array.
	 */
	public static function get_facebook_settings() {

		if ( null === self::$facebook ) {

			$facebook_default = array(
				'facebook_pixel_id'                => '',
				'facebook_pixel_add_to_cart'       => 'enable',
				'facebook_pixel_initiate_checkout' => 'enable',
				'facebook_pixel_add_payment_info'  => 'enable',
				'facebook_pixel_purchase_complete' => 'enable',
				'facebook_pixel_tracking'          => 'disable',
				'facebook_pixel_tracking_for_site' => 'disable',
			);

			$facebook = self::get_admin_settings_option( '_cartflows_facebook', false, false );

			$facebook = wp_parse_args( $facebook, $facebook_default );

			self::$facebook = apply_filters( 'cartflows_facebook_settings_default', $facebook );

		}

		return self::$facebook;
	}


	/**
	 * Prepare response data for facebook.
	 *
	 * @param int   $order_id order_id.
	 * @param array $offer_data offer data.
	 */
	public static function send_fb_response_if_enabled( $order_id, $offer_data = array() ) {

		// Stop Execution if WooCommerce is not installed & don't set the cookie.
		if ( ! Cartflows_Loader::get_instance()->is_woo_active ) {
			return;
		}

		$fb_settings = self::get_facebook_settings();
		if ( 'enable' === $fb_settings['facebook_pixel_tracking'] ) {
			setcookie( 'wcf_order_details', wp_json_encode( self::prepare_purchase_data_fb_response( $order_id, $offer_data ) ), strtotime( '+1 year' ), '/' );
		}

	}

	/**
	 * Prepare purchase response for facebook purcase event.
	 *
	 * @param integer $order_id order id.
	 * @param array   $offer_data offer data.
	 * @return mixed
	 */
	public static function prepare_purchase_data_fb_response( $order_id, $offer_data = array() ) {

		$thankyou = array();

		if ( ! Cartflows_Loader::get_instance()->is_woo_active ) {
			return $thankyou;
		}

		$thankyou['order_id']     = $order_id;
		$thankyou['content_type'] = 'product';
		$thankyou['currency']     = wcf()->options->get_checkout_meta_value( $order_id, '_order_currency' );
		$thankyou['userAgent']    = wcf()->options->get_checkout_meta_value( $order_id, '_customer_user_agent' );
		$thankyou['plugin']       = 'CartFlows';
		$order                    = wc_get_order( $order_id );
		if ( empty( $offer_data ) ) {
			// Iterating through each WC_Order_Item_Product objects.
			foreach ( $order->get_items() as $item_key => $item ) {
				$product                   = $item->get_product(); // Get the WC_Product object.
				$thankyou['content_ids'][] = (string) $product->get_id();
			}
			$thankyou['value'] = wcf()->options->get_checkout_meta_value( $order_id, '_order_total' );
		} else {
			$thankyou['content_ids'][] = (string) $offer_data['id'];
			$thankyou['value']         = $offer_data['total'];
		}

		return $thankyou;
	}

	/**
	 * Prepare cart data for fb response.
	 *
	 * @return array
	 */
	public static function prepare_cart_data_fb_response() {

		$params = array();

		if ( ! Cartflows_Loader::get_instance()->is_woo_active ) {
			return $params;
		}

		$cart_total       = WC()->cart->get_cart_contents_total();
		$cart_items_count = WC()->cart->get_cart_contents_count();
		$items            = WC()->cart->get_cart();
		$product_names    = '';
		$category_names   = '';
		$cart_contents    = array();
		foreach ( $items as $item => $value ) {

			$_product                = wc_get_product( $value['product_id'] );
			$params['content_ids'][] = (string) $_product->get_id();
			$product_names           = $product_names . ', ' . $_product->get_title();
			$category_names          = $category_names . ', ' . wp_strip_all_tags( wc_get_product_category_list( $_product->get_id() ) );
			array_push(
				$cart_contents,
				array(
					'id'         => $_product->get_id(),
					'name'       => $_product->get_title(),
					'quantity'   => $value['quantity'],
					'item_price' => $_product->get_price(),
				)
			);
		}

		$user                         = wp_get_current_user();
		$roles                        = implode( ', ', $user->roles );
		$params['content_name']       = substr( $product_names, 2 );
		$params['categoey_name']      = substr( $category_names, 2 );
		$params['user_roles']         = $roles;
		$params['plugin']             = 'CartFlows';
		$params['contents']           = wp_json_encode( $cart_contents );
		$params['content_type']       = 'product';
		$params['value']              = $cart_total;
		$params['num_items']          = $cart_items_count;
		$params['currency']           = get_woocommerce_currency();
		$params['language']           = get_bloginfo( 'language' );
		$params['userAgent']          = wp_unslash( $_SERVER['HTTP_USER_AGENT'] ); //phpcs:ignore
		$params['product_catalog_id'] = '';
		$params['domain']             = get_site_url();
		return $params;
	}

	/**
	 * Get the image url of size.
	 *
	 * @param int    $post_id post id.
	 * @param array  $key key.
	 * @param string $size image size.
	 *
	 * @return array
	 */
	public static function get_image_url( $post_id, $key, $size = false ) {

		$url     = get_post_meta( $post_id, $key, true );
		$img_obj = get_post_meta( $post_id, $key . '-obj', true );
		if ( is_array( $img_obj ) && ! empty( $img_obj ) && false !== $size ) {

			$url = ! empty( $img_obj['url'][ $size ] ) ? $img_obj['url'][ $size ] : $url;
		}

		return $url;
	}

}
