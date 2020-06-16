<?php
/**
 * Cartflow default options.
 *
 * @package Cartflows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Default_Meta {



	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $thankyou_fields = null;

	/**
	 * Member Variable
	 *
	 * @var flow_fields
	 */
	private static $flow_fields = null;

	/**
	 * Member Variable
	 *
	 * @var landing_fields
	 */
	private static $landing_fields = null;

	/**
	 * Member Variable
	 *
	 * @var optin_fields
	 */
	private static $optin_fields = null;

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
	 *  Checkout Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_checkout_fields( $post_id ) {

		if ( null === self::$checkout_fields ) {
			self::$checkout_fields = array(
				'wcf-field-google-font-url'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-checkout-products'                => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_CHECKOUT_PRODUCTS',
				),
				'wcf-checkout-layout'                  => array(
					'default'  => 'two-column',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-family'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-weight'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-font-family'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-font-weight'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-base-font-family'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-advance-options-fields'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-remove-product-field'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-checkout-place-order-button-text' => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_STRING',
				),
				'wcf-base-font-weight'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-button-font-family'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-button-font-weight'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-primary-color'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-color'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-section-bg-color'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-hl-bg-color'                      => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-tb-padding'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-lr-padding'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-fields-skins'                     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-field-size'                 => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-color'                      => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-bg-color'                   => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-border-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-box-border-color'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-label-color'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-tb-padding'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-lr-padding'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-button-size'                => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-color'                     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-hover-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-color'                  => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-hover-color'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-color'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-hover-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-active-tab'                       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-header-logo-image'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-header-logo-width'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-custom-script'                    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);

			self::$checkout_fields = apply_filters( 'cartflows_checkout_meta_options', self::$checkout_fields, $post_id );
		}

		return self::$checkout_fields;
	}

	/**
	 *  Save Checkout Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	public function save_checkout_fields( $post_id ) {

		$post_meta = $this->get_checkout_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save Landing Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	public function save_landing_fields( $post_id ) {

		$post_meta = $this->get_landing_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save ThankYou Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	public function save_thankyou_fields( $post_id ) {

		$post_meta = $this->get_thankyou_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Flow Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_flow_fields( $post_id ) {

		if ( null === self::$flow_fields ) {
			self::$flow_fields = array(
				'wcf-steps'   => array(
					'default'  => array(),
					'sanitize' => 'FILTER_DEFAULT',
				),

				'wcf-testing' => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}

		return apply_filters( 'cartflows_flow_meta_options', self::$flow_fields );
	}

	/**
	 *  Save Flow Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	public function save_flow_fields( $post_id ) {

		$post_meta = $this->get_flow_fields( $post_id );

		if ( isset( $post_meta['wcf-steps'] ) ) {
			unset( $post_meta['wcf-steps'] );
		}

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save Meta fields - Common Function.
	 *
	 * @param int   $post_id post id.
	 * @param array $post_meta options to store.
	 * @return void
	 */
	public function save_meta_fields( $post_id, $post_meta ) {

		if ( ! ( $post_id && is_array( $post_meta ) ) ) {
			return;
		}

		foreach ( $post_meta as $key => $data ) {
			$meta_value = false;

			// Sanitize values.
			$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

			switch ( $sanitize_filter ) {
				case 'FILTER_SANITIZE_STRING':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
					break;

				case 'FILTER_SANITIZE_URL':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
					break;

				case 'FILTER_SANITIZE_NUMBER_INT':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
					break;

				case 'FILTER_CARTFLOWS_ARRAY':
					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) { //phpcs:ignore
						$meta_value = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ); //phpcs:ignore
					}
					break;

				case 'FILTER_CARTFLOWS_CHECKOUT_PRODUCTS':
					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) { //phpcs:ignore
						$i = 0;
						$q = 0;

						foreach ( $_POST[ $key ] as $p_index => $p_data ) { // phpcs:ignore
							foreach ( $p_data as $i_key => $i_value ) {
								if ( is_array( $i_value ) ) {
									foreach ( $i_value as $q_key => $q_value ) {
										$meta_value[ $i ][ $i_key ][ $q ] = array_map( 'sanitize_text_field', $q_value );

										$q++;
									}
								} else {
									$meta_value[ $i ][ $i_key ] = sanitize_text_field( $i_value );
								}
							}

							$i++;
						}
					}
					break;

				case 'FILTER_CARTFLOWS_IMAGES':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );

					if ( isset( $_POST[ $key . '-obj' ] )) { //phpcs:ignore

						if ( ! is_serialized( $_POST[ $key . '-obj' ] ) ) { //phpcs:ignore

							$image_obj  = json_decode( stripcslashes( wp_unslash( $_POST[ $key . '-obj' ] ) ), true ); //phpcs:ignore
							$image_url = isset( $image_obj['sizes'] ) ? $image_obj['sizes'] : array();

							$image_data = array(
								'id'  => isset( $image_obj['id'] ) ? intval( $image_obj['id'] ) : 0,
								'url' => array(
									'thumbnail' => isset( $image_url['thumbnail']['url'] ) ? esc_url_raw( $image_url['thumbnail']['url'] ) : '',
									'medium'    => isset( $image_url['medium']['url'] ) ? esc_url_raw( $image_url['medium']['url'] ) : '',
									'full'      => isset( $image_url['full']['url'] ) ? esc_url_raw( $image_url['full']['url'] ) : '',
								),
							);

							$new_meta_value = 0 !== $image_data['id'] ? $image_data : '';
							update_post_meta( $post_id, $key . '-obj', $new_meta_value );
						}
					}

					break;

				default:
					if ( 'FILTER_DEFAULT' === $sanitize_filter ) {
						$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
					} else {
						$meta_value = apply_filters( 'cartflows_save_meta_field_values', $meta_value, $post_id, $key, $sanitize_filter );
					}

					break;
			}

			if ( false !== $meta_value ) {
				update_post_meta( $post_id, $key, $meta_value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_flow_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_flow_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_checkout_meta_value( $post_id = 0, $key = '', $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$fields = $this->get_checkout_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Get post meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @return string
	 */
	public function get_save_meta( $post_id, $key ) {

		$value = get_post_meta( $post_id, $key, true );

		return $value;
	}

	/**
	 *  Thank You Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_thankyou_fields( $post_id ) {

		if ( null === self::$thankyou_fields ) {
			self::$thankyou_fields = array(
				'wcf-field-google-font-url'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-active-tab'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-text-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-font-family'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-font-size'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-font-family'    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-font-wt'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-container-width'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-section-bg-color'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-advance-options-fields' => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-overview-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-details-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-billing-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-shipping-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-tq-redirect-section'  => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-redirect-link'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_URL',
				),
				'wcf-tq-text'                   => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-custom-script'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}

		return apply_filters( 'cartflows_thankyou_meta_options', self::$thankyou_fields, $post_id );
	}

	/**
	 *  Get Thank you section meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_thankyou_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_thankyou_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

		/**
		 *  Get Landing section meta.
		 *
		 * @param int    $post_id post id.
		 * @param string $key options key.
		 * @param mix    $default options default value.
		 * @return string
		 */
	public function get_landing_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );
		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_landing_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Landing Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_landing_fields( $post_id ) {

		if ( null === self::$landing_fields ) {
			self::$landing_fields = array(
				'wcf-custom-script' => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}
		return apply_filters( 'cartflows_landing_meta_options', self::$landing_fields, $post_id );
	}

	/**
	 *  Optin Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public function get_optin_fields( $post_id ) {

		if ( null === self::$optin_fields ) {
			self::$optin_fields = array(

				'wcf-optin-product'              => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
				),

				/* Style */
				'wcf-field-google-font-url'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-primary-color'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-base-font-family'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-fields-skins'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-family'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-weight'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-field-size'           => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-tb-padding'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-field-lr-padding'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-field-color'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-bg-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-border-color'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-label-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-button-text'         => array(
					'default'  => __( 'Submit', 'cartflows' ),
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-font-size'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-button-font-family'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-button-font-weight'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-button-size'         => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-tb-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-lr-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-submit-button-position'     => array(
					'default'  => 'center',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-hover-color'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-color'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-hover-color'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-hover-color'  => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),

				/* Settings */
				'wcf-optin-pass-fields'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-optin-pass-specific-fields' => array(
					'default'  => 'first_name',
					'sanitize' => 'FILTER_DEFAULT',
				),

				/* Script */
				'wcf-custom-script'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),

				/* Hidden */
				'wcf-active-tab'                 => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}
		return apply_filters( 'cartflows_optin_meta_options', self::$optin_fields, $post_id );
	}

	/**
	 *  Save Optin Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	public function save_optin_fields( $post_id ) {

		$post_meta = $this->get_optin_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Get optin meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	public function get_optin_meta_value( $post_id = 0, $key = '', $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$fields = $this->get_optin_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Default_Meta::get_instance();
