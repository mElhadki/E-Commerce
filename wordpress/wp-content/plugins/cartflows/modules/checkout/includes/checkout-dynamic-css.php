<?php
/**
 * Dynamic checkout css
 *
 * @package CartFlows
 */

$output = "
	.wcf-embed-checkout-form .woocommerce .woocommerce-checkout .product-name .remove:hover{
		color:$primary_color !important;
		border:1px solid $primary_color !important;
	}
	.wcf-embed-checkout-form .wcf-checkout-header-image img{
		width: {$header_logo_width}px;
	}
	.wcf-embed-checkout-form .woocommerce #payment input[type=checkbox]:checked:before,
	.wcf-embed-checkout-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:checked:before{
	    color: {$primary_color};
	}
	.wcf-embed-checkout-form .woocommerce #payment input[type=radio]:checked:before{
		background-color: {$primary_color};
	}
	.wcf-embed-checkout-form .woocommerce #payment input[type=checkbox]:focus, 
	.wcf-embed-checkout-form .woocommerce .woocommerce-shipping-fields [type='checkbox']:focus,
	.wcf-embed-checkout-form .woocommerce #payment input[type=radio]:checked:focus,
	.wcf-embed-checkout-form .woocommerce #payment input[type=radio]:not(:checked):focus{
		border-color: {$primary_color};
		box-shadow: 0 0 2px rgba( " . $r . ',' . $g . ',' . $b . ", .8);
	}
	.wcf-embed-checkout-form .woocommerce-checkout label{
		color: {$field_label_color};
	}
	.wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box{
		background-color: {$hl_bg_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-embed-checkout-form #add_payment_method #payment div.payment_box::before, 
	.wcf-embed-checkout-form .woocommerce-cart #payment div.payment_box::before, 
	.wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box::before
	{
	    border-bottom-color: {$hl_bg_color};
	    border-right-color: transparent;
	    border-left-color: transparent;
	    border-top-color: transparent;
	    position: absolute;
	}

	.wcf-embed-checkout-form .woocommerce #payment [type='radio']:checked + label,
	.wcf-embed-checkout-form .woocommerce #payment [type='radio']:not(:checked) + label{
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	
	.wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type='text'],
	.wcf-embed-checkout-form .woocommerce form .form-row input.input-text,
	.wcf-embed-checkout-form .woocommerce form .form-row textarea,
	.wcf-embed-checkout-form .select2-container--default .select2-selection--single,
	.wcf-embed-checkout-form .woocommerce form .form-row select.select {
		color: {$field_color};
		background: {$field_bg_color};
		border-color: {$field_border_color};
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.wcf-embed-checkout-form .woocommerce .col2-set .col-1, 
	.wcf-embed-checkout-form .woocommerce .col2-set .col-2,
	.wcf-embed-checkout-form .woocommerce-checkout .shop_table,
	.wcf-embed-checkout-form .woocommerce-checkout #order_review_heading,
	.wcf-embed-checkout-form .woocommerce-checkout #payment,
	.wcf-embed-checkout-form .woocommerce form.checkout_coupon
	{
		background-color: {$section_bg_color};
		border-color: {$box_border_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}

	.woocommerce table.shop_table th{
		color: {$field_label_color};
	}
	/*.wcf-embed-checkout-form .woocommerce .woocommerce-info,
	.wcf-embed-checkout-form .woocommerce-message{
		border-top-color: {$primary_color};
		background-color: {$hl_bg_color};
	}*/
	.wcf-embed-checkout-form .woocommerce a{
		color: {$primary_color};
	}
	.wcf-embed-checkout-form .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: {$field_color};
	}
	.wcf-embed-checkout-form ::-webkit-input-placeholder { /* Chrome/Opera/Safari */
		color: {$field_color};
	}
	.wcf-embed-checkout-form ::-moz-placeholder { /* Firefox 19+ */
		color: {$field_color};
	}
	.wcf-embed-checkout-form :-ms-input-placeholder { /* IE 10+ */
		color: {$field_color};
	}
	.wcf-embed-checkout-form :-moz-placeholder { /* Firefox 18- */
		color: {$field_color};
	}
	.wcf-embed-checkout-form .woocommerce form p.form-row label {
		color: {$field_label_color};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce #order_review button,
	.wcf-embed-checkout-form .woocommerce form.woocommerce-form-login .form-row button, 
	.wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small {
		color: {$submit_color};
		background: {$submit_bg_color};
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button, 
	.wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button{
		background: {$submit_bg_color};
		border: 1px {$submit_border_color} solid;
		color: {$submit_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce-checkout form.login .button:hover, 
	.wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button:hover,
	.wcf-embed-checkout-form .woocommerce #payment #place_order:hover,
	.wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small:hover{
		color: {$submit_hover_color};
		background-color: {$submit_bg_hover_color};
		border-color: {$submit_border_hover_color};
	}
	.wcf-embed-checkout-form .woocommerce h3,
	.wcf-embed-checkout-form .woocommerce h3 span,
	.wcf-embed-checkout-form .woocommerce-checkout #order_review_heading{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	.wcf-embed-checkout-form .woocommerce-info::before,
	.wcf-embed-checkout-form .woocommerce-message::before{
		color: {$primary_color};
	}
	.wcf-embed-checkout-form{
	    font-family: {$base_font_family};
	}
	img.emoji, img.wp-smiley {}";
