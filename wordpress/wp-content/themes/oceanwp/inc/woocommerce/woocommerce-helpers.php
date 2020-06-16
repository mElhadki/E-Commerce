<?php
/**
 * WooCommerce helper functions
 * This functions only load if WooCommerce is enabled because
 * they should be used within Woo loops only.
 *
 * @package OceanWP WordPress theme
 */

/**
 * Creates the WooCommerce link for the navbar
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'oceanwp_wcmenucart_menu_item' ) ) {

	function oceanwp_wcmenucart_menu_item() {

		// Classes
		$classes = array( 'wcmenucart' );

		// Hide items if "hide if empty cart" is checked
		if ( true == get_theme_mod( 'ocean_woo_menu_icon_hide_if_empty', false )
			&& ! WC()->cart->cart_contents_count > 0 ) {
			$classes[] = 'wcmenucart-hide';
		}

		// Turn classes into space seperated string
		$classes = implode( ' ', $classes );

		// Return if is in the Elementor edit mode, to avoid error
		if ( OCEANWP_ELEMENTOR_ACTIVE
			&& \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}
		
		// Vars
		$icon_style   = get_theme_mod( 'ocean_woo_menu_icon_style', 'drop_down' );
		$custom_link  = get_theme_mod( 'ocean_woo_menu_icon_custom_link' );

		// URL
		if ( 'custom_link' == $icon_style && $custom_link ) {
			$url = esc_url( $custom_link );
		} else {
			$cart_id = wc_get_page_id( 'cart' );
			if ( function_exists( 'icl_object_id' ) ) {
				$cart_id = icl_object_id( $cart_id, 'page' );
			}
			$url = get_permalink( $cart_id );
		}
		
		// Cart total
		$display = get_theme_mod( 'ocean_woo_menu_icon_display', 'icon_count' );
		if ( 'icon_total' == $display ) {
			$cart_extra = WC()->cart->get_total();
			$cart_extra = str_replace( 'amount', 'wcmenucart-details', $cart_extra );
		} elseif ( 'icon_count' == $display ) {
			$cart_extra = '<span class="wcmenucart-details count">'. WC()->cart->get_cart_contents_count() .'</span>';
		} elseif ( 'icon_count_total' == $display ) {
			$cart_extra = '<span class="wcmenucart-details count">'. WC()->cart->get_cart_contents_count() .'</span>';
			$cart_total = WC()->cart->get_total();
			$cart_extra .= str_replace( 'amount', 'wcmenucart-details', $cart_total );
		} else {
			$cart_extra = '';
		}

		// Get cart icon
		$icon = get_theme_mod( 'ocean_woo_menu_icon', 'icon-handbag' );
		$icon = $icon ? $icon : 'icon-handbag';

		// If has custom cart icon
		$custom_icon = get_theme_mod( 'ocean_woo_menu_custom_icon' );
		if ( '' != $custom_icon ) {
			$icon = $custom_icon;
		}

		// Cart Icon
		$cart_icon = '<i class="'. esc_attr( $icon ) .'" aria-hidden="true"></i>';
		$cart_icon = apply_filters( 'ocean_menu_cart_icon_html', $cart_icon );

		// If bag style
		if ( 'yes' == get_theme_mod( 'ocean_woo_menu_bag_style', 'no' ) ) { ?>

			<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $classes ); ?>">
				<?php
				if ( true == get_theme_mod( 'ocean_woo_menu_bag_style_total', false ) ) { ?>
					<span class="wcmenucart-total"><?php echo WC()->cart->get_total(); ?></span>
				<?php } ?>
				<span class="wcmenucart-cart-icon">
					<span class="wcmenucart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
				</span>
			</a>

		<?php } else { ?>

			<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $classes ); ?>">
				<span class="wcmenucart-count"><?php echo wp_kses_post( $cart_icon ); ?><?php echo wp_kses_post( $cart_extra ); ?></span>
			</a>

		<?php
		}

	}

}

/**
 * Outputs placeholder image
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'oceanwp_woo_placeholder_img' ) ) {

	function oceanwp_woo_placeholder_img() {
		if ( function_exists( 'wc_placeholder_img_src' ) && wc_placeholder_img_src() ) {
			$placeholder = '<div class="woo-entry-image clr"><img src="'. wc_placeholder_img_src() .'" alt="'. __( 'Placeholder Image', 'oceanwp' ) .'" class="woo-entry-image-main" /></div>';
			$placeholder = apply_filters( 'ocean_woo_placeholder_img_html', $placeholder );
			if ( $placeholder ) {
				echo wp_kses_post( $placeholder );
			}
		}
	}

}

/**
 * Check if product is in stock
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'oceanwp_woo_product_instock' ) ) {

	function oceanwp_woo_product_instock( $post_id = '' ) {
		global $post;
		$post_id      = $post_id ? $post_id : $post->ID;
		$product = wc_get_product($post_id);
		return $product->is_in_stock();
	}

}

/**
 * Returns catalog elements positioning
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'oceanwp_woo_product_elements_positioning' ) ) {

	function oceanwp_woo_product_elements_positioning() {

		// Default sections
		$sections = array( 'image', 'category', 'title', 'price-rating', 'description' , 'button' );

		// Get sections from Customizer
		$sections = get_theme_mod( 'oceanwp_woo_product_elements_positioning', $sections );

		// Turn into array if string
		if ( $sections && ! is_array( $sections ) ) {
			$sections = explode( ',', $sections );
		}

		// Apply filters for easy modification
		$sections = apply_filters( 'ocean_woo_product_elements_positioning', $sections );

		// Return sections
		return $sections;

	}

}

/**
 * Returns single product summary elements positioning
 *
 * @since 1.1.9
 */
if ( ! function_exists( 'oceanwp_woo_summary_elements_positioning' ) ) {

	function oceanwp_woo_summary_elements_positioning() {

		// Default sections
		$sections = array( 'title', 'rating', 'price', 'excerpt', 'quantity-button', 'meta' );

		// Get sections from Customizer
		$sections = get_theme_mod( 'oceanwp_woo_summary_elements_positioning', $sections );

		// Turn into array if string
		if ( $sections && ! is_array( $sections ) ) {
			$sections = explode( ',', $sections );
		}

		// Apply filters for easy modification
		$sections = apply_filters( 'ocean_woo_summary_elements_positioning', $sections );

		// Return sections
		return $sections;

	}

}

/**
 * Check if it is a product attribute archive page
 *
 * @since 1.8.2
 */
function ocean_is_wc_attribute() {

    /**
     * Attributes are proper taxonomies, therefore first thing is
     * to check if we are on a taxonomy page using the is_tax().
     */
    if ( is_tax() && function_exists( 'taxonomy_is_product_attribute') ) {

        $tax_obj = get_queried_object();
		return taxonomy_is_product_attribute( $tax_obj->taxonomy );
    }
    return false;
}

/**
 * Get the custom taxonomies
 *
 * @since 1.8.2
 */
function get_term_tax_attr() {

	if ( function_exists( 'taxonomy_is_product_attribute') ) {
		$attr_taxonomies = wc_get_attribute_taxonomies();
	}

	$taxonomy_terms = array();

	if ( $attr_taxonomies ) {
		foreach ($attr_taxonomies as $tax) {
			if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
				$taxonomy_terms[$tax->attribute_name] = get_terms( wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0' );
			}
		}
	}
}