<?php

namespace WeglotWP\Third\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 * WC_Mail_Weglot
 *
 * @since 3.1.6
 */
class WC_Mail_Weglot implements Hooks_Interface_Weglot {


	/**
	 * @since 3.1.6
	 * @return void
	 */
	public function __construct() {
		$this->wc_active_services = weglot_get_service( 'Wc_Active' );
	}

	/**
	 * @since 3.1.6
	 * @return void
	 */
	public function hooks() {
		if ( ! $this->wc_active_services->is_active() || ! apply_filters( 'weglot_wooccommerce_translate_following_mail', false ) ) {
			return;
		}

		add_action( 'woocommerce_new_order', [ $this, 'save_language' ], 10, 1 );
		add_action( 'woocommerce_mail_callback_params', [ $this, 'translate_following_mail' ], 10, 2 );
	}

	/**
	 * @since 3.1.6
	 * @return array
	 */
	public function translate_following_mail( $args, $mail ) {

		$translate_email = apply_filters( 'weglot_translate_email', weglot_get_option( 'email_translate' ), $args );

		if (
			$translate_email
			&& (
				is_a( $mail->object, 'Automattic\WooCommerce\Admin\Overrides\Order' )
				|| is_a( $mail->object, 'WC_Order' )
			)
		) {

			if ( $mail->is_customer_email() ) { // If mail is for customer
				$woocommerce_order_language = get_post_meta( $mail->object->get_id(), 'weglot_language', true );

				if ( ! empty( $woocommerce_order_language ) ) {

					$current_and_original_language            = weglot_get_current_and_original_language();
					$current_and_original_language['current'] = $woocommerce_order_language;

					add_filter(
						'weglot_translate_email_languages_forced',
						function() use ( $current_and_original_language ) {
							return $current_and_original_language;
						}
					);
				}
			} else { // If mail is for admin
				$current_and_original_language['original'] = weglot_get_original_language();
				$current_and_original_language['current']  = $current_and_original_language['original'];

				add_filter(
					'weglot_translate_email_languages_forced',
					function() use ( $current_and_original_language ) {
						return $current_and_original_language;
					}
				);
			}
		}

		return $args;
	}


	/**
	 * @since 3.1.6
	 * @return int
	 */
	public function save_language( $order_id ) {
		if ( Helper_Is_Admin::is_wp_admin() ) {
			return;
		}

		$current_language = weglot_get_current_language();
		if ( weglot_get_original_language() !== $current_language ) {
			add_post_meta( $order_id, 'weglot_language', weglot_get_current_language() );
		}

		return $order_id;
	}

}
