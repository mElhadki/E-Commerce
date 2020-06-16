<?php
/**
 * Flow shortcodes
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Flow_Shortcodes {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		add_shortcode( 'cartflows_next_step_link', array( $this, 'next_step_link' ) );

		add_shortcode( 'cartflows_navigation', array( $this, 'navigation_shortcode' ) );
	}

	/**
	 *  Returns next step link
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	public function next_step_link( $atts ) {

		global $post;

		$output = '#';

		if ( $post && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			$navigation = false;

			$step_id = intval( $post->ID );
			$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

			if ( ! $flow_id ) {
				return $output;
			}

			$steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $steps ) || ( is_array( $steps ) && empty( $steps ) ) ) {
				return $output;
			}

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

				$output = get_permalink( $navigation['id'] );
			}
		}

		return $output;
	}

	/**
	 *  Navigation shortcode callback
	 *
	 * @param array $atts attributes for shortcode.
	 * @return string
	 */
	public function navigation_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'label'         => '',
				'icon'          => '',
				'icon_position' => '',
			),
			$atts
		);

		global $post;

		$output = '';

		if ( $post && CARTFLOWS_STEP_POST_TYPE === $post->post_type ) {

			$navigation = false;

			$step_id = intval( $post->ID );
			$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

			if ( ! $flow_id ) {
				return $output;
			}

			$steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $steps ) || ( is_array( $steps ) && empty( $steps ) ) ) {
				return $output;
			}

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

				$label  = ( '' != $atts['label'] ) ? $atts['label'] : __( 'Next Step', 'cartflows' );
				$before = '';
				$after  = '';

				if ( '' != $atts['icon'] ) {
					if ( '' != $atts['icon_position'] ) {
						if ( 'before' == $atts['icon_position'] ) {
							$before = '<span class="wcf-nextstep-icon wcf-nextstep-icon-before"><i class="' . $atts['icon'] . '" aria-hidden="true"></i></span>';
						} else {
							$after = '<span class="wcf-nextstep-icon wcf-nextstep-icon-after"><i class="' . $atts['icon'] . '" aria-hidden="true"></i></span>';
						}
					}
				}

				$output = '<div><a target="_self" href="' . get_permalink( $navigation['id'] ) . '">' . $before . $label . $after . '</a></div>';
			}
		}

		return $output;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Shortcodes::get_instance();
