<?php

namespace WeglotWP\Widgets;

defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


class Widget_Selector_Weglot extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			WEGLOT_SLUG,
			__( 'Weglot Translate', 'weglot' ),
			[
				'description' => __( 'Display Weglot selector in widget', 'weglot' ),
			]
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if ( ! weglot_current_url_is_eligible() ) {
			return;
		}
		$title = (isset($instance['title'])) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title );

		$tt = ( ! empty( $title ) ) ? $args['before_title'] . $title . $args['after_title'] : '';

		$button = weglot_get_button_selector_html( 'weglot-widget' );

		echo $args['before_widget'] . $tt . $button . $args['after_widget']; //phpcs:ignore
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = '';
		} ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'weglot' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance            = [];
		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( strip_tags( $new_instance['title'] ) ) : '';
		return $instance;
	}
}
