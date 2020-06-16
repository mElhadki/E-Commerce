<?php
/**
 * Cartflows_Tracking
 *
 * @package CartFlows
 */

/**
 * Flow Markup
 *
 * @since 1.0.0
 */
class Cartflows_Tracking {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	public static $google_analytics_settings = array();

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

		add_action( 'wp_head', array( $this, 'wcf_render_gtag' ) );

		// Set Google analytics values.
		$this->get_google_analytics_settings( self::$google_analytics_settings );
	}

	/**
	 *  Get ga settings.
	 *
	 * @param array $google_analytics_settings ga settings.
	 */
	public function get_google_analytics_settings( $google_analytics_settings ) {
		self::$google_analytics_settings = Cartflows_Helper::get_google_analytics_settings();
	}


	/**
	 * Render google tag framework.
	 */
	public function wcf_render_gtag() {
		$get_tracking_code = $this->wcf_ga_id();

		if ( self::is_wcf_ga_tracking_on() ) {
			?>
			<!-- Google Analytics Script By CartFlows -->
			<script type="text/javascript">
				var tracking_id = '<?php echo $get_tracking_code; ?>';
			</script>
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
			<script async src=https://www.googletagmanager.com/gtag/js?id=<?php echo $get_tracking_code; ?>></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());
			</script>

			<!-- Google Analytics Script By CartFlows -->
			<?php
			if ( 'enable' === self::$google_analytics_settings['enable_google_analytics_for_site'] ) {
				?>
				<script>
					gtag('config', tracking_id);
				</script>
				<?php
			}
		}
	}

	/**
	 * Set cookies to send ga data.
	 *
	 * @param int   $order_id order id.
	 * @param array $offer_data offer product data.
	 */
	public static function send_ga_data_if_enabled( $order_id, $offer_data = array() ) {

		if ( self::is_wcf_ga_tracking_on() && self::wcf_track_ga_purchase() ) {

			setcookie( 'wcf_ga_trans_data', wp_json_encode( self::get_ga_purchase_transactions_data( $order_id, $offer_data ) ), strtotime( '+1 year' ), '/' );
		}
	}


	/**
	 * Prepare cart data for GA response.
	 *
	 * @param int   $order_id order id.
	 * @param array $offer_data offer product data.
	 * @return array
	 */
	public static function get_ga_purchase_transactions_data( $order_id, $offer_data ) {

		$response = array();

		$order             = wc_get_order( $order_id );
		$cart_total        = WC()->cart->get_cart_contents_total();
		$cart_items_count  = WC()->cart->get_cart_contents_count();
		$items             = $order->get_items();
		$cart_tax          = $order->get_cart_tax();
		$response['items'] = array();
		$cart_contents     = array();

		$response = array(
			'transaction_id' => $order_id,
			'affiliation'    => get_bloginfo( 'name' ),
			'value'          => $order->get_total(),
			'currency'       => $order->get_currency(),
			'tax'            => $order->get_cart_tax(),
			'shipping'       => $order->get_shipping_total(),
			'coupon'         => WC()->cart->get_coupons(),
		);
		if ( empty( $offer_data ) ) {
			// Iterating through each WC_Order_Item_Product objects.
			foreach ( $items as $item => $value ) {

				$_product = wc_get_product( $value['product_id'] );

				if ( ! $_product->is_type( 'variable' ) ) {
					$product_data = self::get_required_data( $_product );
				} else {
					$variable_product = wc_get_product( $value['variation_id'] );
					$product_data     = self::get_required_data( $variable_product );
				}
				array_push(
					$cart_contents,
					array(
						'id'       => $product_data['id'],
						'name'     => $product_data['name'],
						'category' => wp_strip_all_tags( wc_get_product_category_list( $_product->get_id() ) ),
						'price'    => $product_data['price'],
						'quantity' => $value['quantity'],
					)
				);
			}
		} else {
			array_push(
				$cart_contents,
				array(
					'id'       => $offer_data['id'],
					'name'     => $offer_data['name'],
					'quantity' => $offer_data['qty'],
					'price'    => $offer_data['price'],
				)
			);
		}

		$response['items'] = $cart_contents;

		// Prepare the json data to send it to google.
		return $response;
	}

	/**
	 * Prepare Ecommerce data for GA response.
	 *
	 * @return array
	 */
	public static function get_ga_items_list() {

		$items      = WC()->cart->get_cart();
		$items_data = array();

		foreach ( $items as $item => $value ) {

			$_product = wc_get_product( $value['product_id'] );

			if ( ! $_product->is_type( 'variable' ) ) {
				$product_data = self::get_required_data( $_product );
			} else {
				$variable_product = wc_get_product( $value['variation_id'] );
				$product_data     = self::get_required_data( $variable_product );
			}

			array_push(
				$items_data,
				array(
					'id'       => $product_data['id'],
					'name'     => $product_data['name'],
					'category' => wp_strip_all_tags( wc_get_product_category_list( $_product->get_id() ) ),
					'price'    => $product_data['price'],
					'quantity' => $value['quantity'],
				)
			);
		}
		return $items_data;
	}



	/**
	 * Check tracking on.
	 */
	public static function is_wcf_ga_tracking_on() {

		$is_enabled = false;

		if ( 'disable' === self::$google_analytics_settings['enable_google_analytics'] ) {
			$is_enabled = false;
		} else {
			$is_enabled = true;
		}

		return apply_filters( 'cartflows_google_analytics_tracking_enabled', $is_enabled );
	}



	/**
	 * Check purchase event enable.
	 */
	public static function wcf_track_ga_purchase() {

		$google_analytics_settings = Cartflows_Helper::get_google_analytics_settings();
		$wcf_track_ga_purchase     = $google_analytics_settings['enable_purchase_event'];

		if ( is_array( $google_analytics_settings ) && ! empty( $google_analytics_settings ) && 'enable' === $wcf_track_ga_purchase ) {
			return true;
		}

		return false;
	}

	/**
	 * Get product data.
	 *
	 * @param object $_product product data.
	 */
	public static function get_required_data( $_product ) {

		$data = array(
			'id'    => $_product->get_id(),
			'name'  => $_product->get_name(),
			'price' => $_product->get_price(),
		);
		return $data;
	}

	/**
	 * Retreive google anlytics ID.
	 */
	public function wcf_ga_id() {

		$get_ga_id = self::$google_analytics_settings['google_analytics_id'];

		return empty( $get_ga_id ) ? false : $get_ga_id;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Tracking::get_instance();
