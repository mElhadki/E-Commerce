<?php
/**
 * Generate product repeater.
 *
 * @package CartFlows
 */

$hide_advance = 'wcf-hide-advance';

if ( empty( $selected_data ) ) {

	$selected_data = array(
		'quantity'       => 1,
		'discount_type'  => '',
		'discount_value' => '',
		'unique_id'      => '',
	);
}

?>

<div class="wcf-repeatable-row" data-key="<?php echo $id; ?>">
	<div class="wcf_display_advance_fields wcf-repeater-fields-head-wrap">
	<div class="wcf-repeatable-row-standard-fields">
		<div class="wcf-checkout-products-dashicon dashicons dashicons-menu"></div>

		<!-- Product Name -->
		<div class="wcf-repeatable-fields wcf-sel-product">
			<span class="wcf-repeatable-row-setting-field">
				<select name="wcf-checkout-products[<?php echo $id; ?>][product]" class="wcf-product-search" data-allow_clear="allow_clear" data-placeholder="<?php echo __( 'Search for a product&hellip;', 'cartflows' ); ?>" data-action="woocommerce_json_search_products_and_variations"><?php echo $options; ?></select>
			</span>

			<span class="wcf-repeatable-row-actions">
				<a class="wcf-remove-row wcf-repeatable-remove button" data-type="product">
					<span class="dashicons dashicons-trash"></span>
					<span class="wcf-repeatable-remove-button"><?php echo __( 'Remove', 'cartflows' ); ?></span>
				</a>
			</span>
		</div>

		<div class="wcf_toggle_advance_fields"><i class="dashicons dashicons-arrow-down"></i></div>
	</div>
	</div>

	<div class="wcf-repeatable-row-advance-fields <?php echo $hide_advance; ?>">

		<!-- Qty field. -->
		<div class="wcf-repeatable-row-qty-field wcf-checkout-products-qty-<?php echo $id; ?>">
			<div class="wcf-field-row">
				<div class="wcf-field-row-heading">
					<label><?php echo __( 'Product Quantity', 'cartflows' ); ?></label>
				</div>

				<div class="wcf-field-row-content wcf-field-row-advance-content">
					<input type="number" class="input-text qty text" step="1" min="1" max="" name="wcf-checkout-products[<?php echo $id; ?>][quantity]" value="<?php echo $selected_data['quantity']; ?>" title="Qty" inputmode="numeric">
				</div>
			</div>
		</div>
		<!-- Qty field end -->

		<!-- Type field. -->
		<div class="wcf-repeatable-discount-type-field">
			<div class="wcf-field-row">
				<div class="wcf-field-row-heading">
					<label><?php echo __( 'Discount Type', 'cartflows' ); ?></label>
				</div>

				<div class="wcf-field-row-content wcf-field-row-advance-content">
					<select name="wcf-checkout-products[<?php echo $id; ?>][discount_type]" data-allow_clear="allow_clear" data-placeholder="<?php echo __( 'Select Discount Type', 'cartflows' ); ?>">
						<option value="" <?php selected( $selected_data['discount_type'], '', true ); ?>><?php echo __( 'Original', 'cartflows' ); ?></option>
						<option value="discount_percent" <?php selected( $selected_data['discount_type'], 'discount_percent', true ); ?>><?php echo __( 'Percentage', 'cartflows' ); ?></option>
						<option value="discount_price" <?php selected( $selected_data['discount_type'], 'discount_price', true ); ?>><?php echo __( 'Price', 'cartflows' ); ?></option>
					</select>
				</div>
			</div>
		</div>
		<!-- Type field end -->

		<!-- Discount field -->
		<div class="wcf-repeatable-row-discount-field <?php echo $hide_advance; ?> wcf-checkout-products-discount-<?php echo $id; ?>">
			<div class="wcf-field-row">
				<div class="wcf-field-row-heading">
					<label><?php echo __( 'Discount Value', 'cartflows' ); ?></label>
					<i class="wcf-field-heading-help dashicons dashicons-editor-help"></i>
					<span class="wcf-tooltip-text"><?php echo __( 'Discount value will apply for each quantity of product.', 'cartflows' ); ?></span>
				</div>
				<div class="wcf-field-row-content wcf-field-row-advance-content">
					<input type="text" class="input-text text" name="wcf-checkout-products[<?php echo $id; ?>][discount_value]" value="<?php echo $selected_data['discount_value']; ?>" title="" inputmode="numeric">
				</div>
			</div>
		</div>

		<div class="wcf-repeatable-row-unique-id-field">
			<input name="wcf-checkout-products[<?php echo $id; ?>][unique_id]" type="hidden" class="wcf-checkout-product-unique" value="<?php echo $selected_data['unique_id']; ?>">
		</div>

		<?php do_action( 'cartflows_repeatable_row_advance_fields', $id ); ?>
	</div>
</div>
