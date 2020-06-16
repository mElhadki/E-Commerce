<?php
/**
 * CARTFLOWS Admin Fields.
 *
 * @package CARTFLOWS
 */

/**
 * Class Cartflows_Admin_Fields.
 */
class Cartflows_Admin_Fields {

	/**
	 * Title Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function title_field( $args ) {

		$title       = $args['title'];
		$description = isset( $args['description'] ) ? $args['description'] : '';

		$output      = '<h4 class="form-field wcf-title-field">';
			$output .= '<span>' . $title . '</span>';
		$output     .= '</h4>';

		if ( ! empty( $description ) ) {
			$output .= '<div class="form-field-desc">';
			$output .= '<p>' . $description . '</p>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Text Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function text_field( $args ) {

		$id          = $args['id'];
		$name        = $args['name'];
		$title       = $args['title'];
		$value       = $args['value'];
		$description = isset( $args['description'] ) ? $args['description'] : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

		$output      = '<div class="form-field" id="form-field-' . $id . '">';
			$output .= '<label for="' . $id . '">' . $title . '</label>';
			$output .= '<input placeholder="' . $placeholder . '" type="text" name="' . $name . '" id="' . $id . '" class="placeholder placeholder-active" value="' . esc_attr( $value ) . '">';
		$output     .= '</div>';

		if ( ! empty( $description ) ) {
			$output .= '<div class="form-field-desc">';
			$output .= '<p>';
			$output .= $description;
			$output .= '</p>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * URL Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function url_field( $args ) {

		$id    = $args['id'];
		$name  = $args['name'];
		$title = $args['title'];
		$value = $args['value'];

		$output      = '<div class="form-field">';
			$output .= '<label for="' . $id . '">' . $title . '</label>';
			$output .= '<input type="text" name="' . $name . '" id="' . $id . '" class="placeholder placeholder-active" value="' . esc_url( $value ) . '">';
		$output     .= '</div>';

		return $output;
	}

	/**
	 * Checkbox Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function checkobox_field( $args ) {

		$id    = $args['id'];
		$name  = $args['name'];
		$title = $args['title'];
		$value = $args['value'];

		$output          = '<div class="form-field" id="form-field-' . $id . '">';
			$output     .= '<label for="' . $id . '">';
				$output .= '<input type="hidden" id="wcf_hid_' . $id . '" name="' . $name . '" value="disable">';
				$output .= '<input type="checkbox" id="wcf_' . $id . '" name="' . $name . '" value="enable" ' . checked( $value, 'enable', false ) . '>';
				$output .= $title;
			$output     .= '</label>';
		$output         .= '</div>';

		return $output;
	}

	/**
	 * Radio Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function radio_field( $args ) {

		$name    = $args['name'];
		$id      = $args['id'];
		$options = $args['options'];
		$value   = $args['value'];

		$output = '';

		if ( isset( $args['title'] ) ) {
			$output     .= '<h4 class="form-field-label">';
				$output .= '<span>' . $args['title'] . '</span>';
			$output     .= '</h4>';
		}

		foreach ( $options as $type => $data ) {

			$output .= '<div class="form-field">';
			$output .= '<label for="' . $id . '">';
			$output .= '<input type="radio" class="wcf_permalink_structure" name="' . $name . '" value="' . $type . '" ' . checked( $value, $type, false ) . '>' . $data['label'] . '</label>';
			$output .= '<div class="form-field-desc">';
			$output .= '<p>';

			$output .= $data['description'];

			$output .= '</p>';
			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Select Field
	 *
	 * @since 1.1.4
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function select_field( $args ) {

		$id          = $args['id'];
		$name        = $args['name'];
		$title       = $args['title'];
		$description = $args['description'];
		$value       = $args['value'];
		$options     = $args['options'];

		$output = '<div class="form-field" id="form-field-' . $id . '">';

		$output .= '<div class="form-field-label">';
		$output .= $title;
		$output .= '</div>';

		$output .= '<div class="form-field-data">';
		$output .= '<select id="wcf_' . $id . '" name="' . $name . '">';
		foreach ( $options as $option_value => $option_title ) {
			$output .= '<option value="' . $option_value . '" ' . selected( $value, $option_value, false ) . '>' . $option_title . '</option>';
		}
		$output .= '</select>';
		$output .= '</div>';

		$output .= '<div class="form-field-desc">';
		$output .= '<p>';
		$output .= $description;
		$output .= '</p>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Checkout Selection Field
	 *
	 * @param  array $args Args.
	 * @return string
	 */
	public static function flow_checkout_selection_field( $args ) {

		$id    = $args['id'];
		$name  = $args['name'];
		$title = $args['title'];
		$value = $args['value'];

		$checkout_steps = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'tax_query'      => array( //phpcs:ignore
					array(
						'taxonomy' => CARTFLOWS_TAXONOMY_STEP_TYPE,
						'field'    => 'slug',
						'terms'    => 'checkout',
					),
				),
			)
		);

		$output = '<div class="form-field" id="form-field-' . $id . '">';

			$output         .= '<div class="form-field-label">';
				$output     .= '<label for="' . $id . '">';
					$output .= $title;
				$output     .= '</label>';
			$output         .= '</div>';

			$output     .= '<div class="form-field-data">';
				$output .= '<select id="wcf_' . $id . '" name="' . $name . '">';

		if ( ! empty( $checkout_steps ) ) {
			$output .= '<option value="">' . __( 'Select', 'cartflows' ) . '</option>';
		} else {

			$output .= '<option value="">' . __( 'No Checkout Steps', 'cartflows' ) . '</option>';
		}

		foreach ( $checkout_steps as $index => $step_data ) {

			$output .= '<option value="' . $step_data->ID . '" ' . selected( $value, $step_data->ID, false ) . '>' . $step_data->post_title . ' (#' . $step_data->ID . ') </option>';
		}

				$output .= '</select>';
			$output     .= '</div>';

		if ( '' !== $value ) {
			$output         .= '<div class="form-field-actions">';
				$output     .= '<a href="' . get_edit_post_link( $value ) . '" target="_blank" class="" title="Edit">';
					$output .= '<span class="dashicons dashicons-edit"></span>';
					$output .= '<span class="">Edit</span>';
				$output     .= '</a>';
				$output     .= '<a href="' . get_permalink( $value ) . '" target="_blank" class="" title="View">';
					$output .= '<span class="dashicons dashicons-visibility"></span>';
					$output .= '<span class="">View</span>';
				$output     .= '</a>';
			$output         .= '</div>';
		}

			$output .= '<div class="form-field-desc">';
				/* translators: %s: link */
				$output .= '<p>' . sprintf( __( 'Be sure not to add any product in above selected Global Checkout step. Please read information about how to set up Global Checkout %1$shere%2$s.', 'cartflows' ), '<a href="https://cartflows.com/docs/global-checkout/" target="_blank">', '</a>' ) . '</p>';
			$output     .= '</div>';

		$output .= '</div>';

		return $output;
	}
}
