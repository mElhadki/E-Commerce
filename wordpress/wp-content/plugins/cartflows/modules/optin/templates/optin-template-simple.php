<?php
/**
 * Checkout template
 *
 * @package CartFlows
 */

$optin_layout = 'one-column';
$fields_skins = wcf()->options->get_optin_meta_value( $optin_id, 'wcf-input-fields-skins' );
?>
<div id="wcf-optin-form" class="wcf-optin-form wcf-optin-form-one-column wcf-field-<?php echo $fields_skins; ?>">

<!-- CHECKOUT SHORTCODE -->
<?php do_action( 'cartflows_optin_before_main_section', $optin_layout ); ?>

<?php

$checkout_html = do_shortcode( '[woocommerce_checkout]' );

if (
		empty( $checkout_html ) ||
		trim( $checkout_html ) == '<div class="woocommerce"></div>'
	) {

	echo esc_html__( 'Your cart is currently empty.', 'cartflows' );
} else {
	echo $checkout_html;
}
?>

<?php do_action( 'cartflows_optin_after_main_section', $optin_layout ); ?>
<!-- END CHECKOUT SHORTCODE -->
</div>
