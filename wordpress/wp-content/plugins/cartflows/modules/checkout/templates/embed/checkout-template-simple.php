<?php
/**
 * Checkout template
 *
 * @package CartFlows
 */

$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );
$fields_skins    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );
?>
<div id="wcf-embed-checkout-form" class="wcf-embed-checkout-form wcf-embed-checkout-form-<?php echo $checkout_layout; ?> wcf-field-<?php echo $fields_skins; ?>">
<!-- CHECKOUT SHORTCODE -->
<?php do_action( 'cartflows_add_before_main_section', $checkout_layout ); ?>

<?php
	$checkout_html = do_shortcode( '[woocommerce_checkout]' );

if (
		empty( $checkout_html ) ||
		trim( $checkout_html ) == '<div class="woocommerce"></div>'
	) {

	do_action( 'cartflows_checkout_cart_empty', $checkout_id );

	echo esc_html__( 'Your cart is currently empty.', 'cartflows' );
} else {
	echo $checkout_html;
}
?>

<?php do_action( 'cartflows_add_after_main_section', $arg = '' ); ?>
<!-- END CHECKOUT SHORTCODE -->
</div>
