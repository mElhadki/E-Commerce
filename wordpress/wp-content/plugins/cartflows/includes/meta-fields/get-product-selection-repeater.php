<?php
/**
 * Get product selection repeater.
 *
 * @package CartFlows
 */

$value = $field_data['value'];

if ( ! is_array( $value ) ) {

	$value[0] = array(
		'product' => '',
	);
} else {

	if ( ! isset( $value[0] ) ) {

		$value[0] = array(
			'product' => '',
		);
	}
}

$name_class = 'field-' . $field_data['name'];

?>

<!-- Repeater template -->
<script type="text/html" id="tmpl-wcf-product-repeater">
	<?php
		$template_data = array(
			'quantity'       => 1,
			'discount_type'  => '',
			'discount_value' => '',
			'unique_id'      => '{{unique_id}}',
		);
		echo $this->generate_product_repeater_html( '{{id}}', '', $template_data );
		?>
</script>
<!-- Repeater template end -->

<div class="wcf-field-row wcf-product-repeater-field-row <?php echo $name_class; ?>">
	<div class="wcf-field-row-content">
		<div class="wcf-repeatables-wrap">
		<?php

		if ( is_array( $value ) ) {

			$repeater_html    = '';
			$product_data_new = array();

			foreach ( $value as $p_key => $p_data ) {

				$selected_options = '';
				$selected_data    = array(
					'product'        => 0,
					'quantity'       => 1,
					'discount_type'  => '',
					'discount_value' => '',
					'unique_id'      => wcf()->utils->get_unique_id(),
				);

				if ( isset( $p_data['product'] ) ) {

					$product = wc_get_product( $p_data['product'] );


					// posts.
					if ( ! empty( $product ) ) {

						$post_title       = $product->get_name() . ' (#' . $p_data['product'] . ')';
						$selected_options = '<option value="' . $p_data['product'] . '" selected="selected" >' . $post_title . '</option>';
					}

					$selected_data['product'] = $p_data['product'];
				}

				if ( isset( $p_data['quantity'] ) ) {
					$selected_data['quantity'] = $p_data['quantity'];
				}


				if ( isset( $p_data['discount_type'] ) ) {
					$selected_data['discount_type'] = $p_data['discount_type'];

				}

				if ( isset( $p_data['discount_value'] ) ) {
					$selected_data['discount_value'] = $p_data['discount_value'];
				}

				if ( isset( $p_data['unique_id'] ) && ! empty( $p_data['unique_id'] ) ) {
					$selected_data['unique_id'] = $p_data['unique_id'];
				}

				$repeater_html .= $this->generate_product_repeater_html( $p_key, $selected_options, $selected_data );

				$product_data_new[] = $selected_data;
			}

			wcf()->utils->set_selcted_checkout_products( '', $product_data_new );

			echo $repeater_html;
		}
		?>

			<div class="wcf-add-fields"></div>
		</div>
		<div class="wcf-add-repeatable-row">
			<div class="submit wcf-add-repeatable-wrap">
				<button class="button-primary wcf-add-repeatable" data-name="wcf-checkout-products"><?php echo __( 'Add New Product', 'cartflows' ); ?></button>
				<a href="#!" class="button button-primary wcf-create-woo-product" data-name="wcf-create-woo-product"><?php echo __( 'Create Product', 'cartflows' ); ?></a>
			</div>
		</div>
	</div>
</div>
