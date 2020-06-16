<?php
/**
 * Next Step Widget
 *
 * @package CartFlows
 */

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Next_Step extends WP_Widget {

	/**
	 *  Constructor
	 */
	public function __construct() {

		parent::__construct(
			// Base ID of your widget.
			'cartflows_next_step',
			// Widget name will appear in UI.
			__( 'CartFlows Next Step', 'cartflows' ),
			// Widget description.
			array( 'description' => __( 'Next Step Widgets', 'cartflows' ) )
		);
	}

	/**
	 * Creating widget front-end
	 *
	 * @param array $args arguments array.
	 * @param array $instance widget instance.
	 */
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', $instance['title'] );

		$step_id = intval( $instance['step_id'] );
		$flow_id = intval( $instance['flow_id'] );

		if ( ! $step_id || ! $flow_id ) {

			global $post;

			if ( $post && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {
				$step_id = intval( $post->ID );
				$flow_id = intval( get_post_meta( $step_id, 'wcf-flow-id', true ) );
			}
		}
		$output = 'No Data';

		if ( $flow_id ) {

			$navigation = false;

			$steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( is_array( $steps ) && ! empty( $steps ) ) {

				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $step_id ) {

						$next_i = $i + 1;

						if ( isset( $steps[ $next_i ] ) ) {
							$navigation = $steps[ $next_i ];
						}

						break;
					}
				}

				if ( $navigation && is_array( $navigation ) ) {

					$output = '<div class="wcf-step-navigation"><a class="button" target="_self" href="' . get_permalink( $navigation['id'] ) . '">' . __( 'Next Step', 'cartflows' ) . '</a></div>';
				}
			}
		}

		// before and after widget arguments are defined by themes.
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// This is where you run the code and display the output.
		echo $output;

		echo $args['after_widget'];
	}

	/**
	 * Creating widget back-end
	 *
	 * @param array $instance widget instance.
	 */
	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'New title', 'cartflows' );
		}

		if ( isset( $instance['flow_id'] ) ) {
			$flow_id = $instance['flow_id'];
		} else {
			$flow_id = '';
		}

		if ( isset( $instance['step_id'] ) ) {
			$step_id = $instance['step_id'];
		} else {
			$step_id = '';
		}
		// Widget admin form.
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'cartflows' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'flow_id' ); ?>"><?php esc_html_e( 'Flow ID:', 'cartflows' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'flow_id' ); ?>" name="<?php echo $this->get_field_name( 'flow_id' ); ?>" type="text" value="<?php echo esc_attr( $flow_id ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'step_id' ); ?>"><?php esc_html_e( 'Step ID:', 'cartflows' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'step_id' ); ?>" name="<?php echo $this->get_field_name( 'step_id' ); ?>" type="text" value="<?php echo esc_attr( $step_id ); ?>" />
		</p>
		<?php
	}

	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance new widget instance.
	 * @param array $old_instance old widget instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['flow_id'] = ( ! empty( $new_instance['flow_id'] ) ) ? wp_strip_all_tags( $new_instance['flow_id'] ) : '';
		$instance['step_id'] = ( ! empty( $new_instance['step_id'] ) ) ? wp_strip_all_tags( $new_instance['step_id'] ) : '';

		return $instance;
	}
}
