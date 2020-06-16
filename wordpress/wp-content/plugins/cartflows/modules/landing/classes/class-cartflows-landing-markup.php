<?php
/**
 * Markup
 *
 * @package CartFlows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Landing_Markup {


	/**
	 * Member Variable
	 *
	 * @var object instance
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

		add_action( 'pre_get_posts', array( $this, 'wcf_pre_get_posts' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		if ( is_admin() ) {
			add_filter( 'wp_dropdown_pages', array( $this, 'wp_dropdown_pages' ) );
		}
	}

	/**
	 *  Add landing pages in WordPress reading section.
	 *
	 * @param array $output output.
	 */
	public function wp_dropdown_pages( $output ) {

		global $pagenow;

		if ( ( 'options-reading.php' === $pagenow || 'customize.php' === $pagenow ) && preg_match( '#page_on_front#', $output ) ) {

			$args = array(
				'post_type'   => CARTFLOWS_STEP_POST_TYPE,
				'numberposts' => 100,
				'meta_query'  => array( //phpcs:ignore
					'relation' => 'OR',
					array(
						'key'   => 'wcf-step-type',
						'value' => 'landing',
					),
					array(
						'key'   => 'wcf-step-type',
						'value' => 'checkout',
					),
					array(
						'key'   => 'wcf-step-type',
						'value' => 'optin',
					),
				),
			);

			$landing_pages = get_posts( $args );

			if ( is_array( $landing_pages ) && ! empty( $landing_pages ) ) {

				$cartflows_custom_option = '';

				$front_page_id = get_option( 'page_on_front' );

				foreach ( $landing_pages as $key => $landing_page ) {

					$selected = selected( $front_page_id, $landing_page->ID, false );

					$cartflows_custom_option .= "<option value=\"{$landing_page->ID}\"{$selected}>{$landing_page->post_title} ( #{$landing_page->ID} - CartFlows )</option>";
				}

				$cartflows_custom_option .= '</select>';

				$output = str_replace( '</select>', $cartflows_custom_option, $output );
			}
		}

		return $output;
	}

	/**
	 * Set post query.
	 *
	 * @param string $query post query.
	 */
	public function wcf_pre_get_posts( $query ) {

		if ( $query->is_main_query() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

			$post_type = $query->get( 'post_type' );

			$page_id = $query->get( 'page_id' );

			if ( empty( $post_type ) && ! empty( $page_id ) ) {
				$query->set( 'post_type', get_post_type( $page_id ) );
			}
		}
	}

	/**
	 * Redirect to homepage if landing page set as home page.
	 */
	public function template_redirect() {

		$compatibiliy = Cartflows_Compatibility::get_instance();

		// Do not redirect for page builder preview.
		if ( $compatibiliy->is_page_builder_preview() ) {
			return;
		}

		global $post;

		if ( is_singular() && ! is_front_page() && get_option( 'page_on_front' ) == $post->ID ) {
			wp_safe_redirect( site_url(), 301 );
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Landing_Markup::get_instance();
