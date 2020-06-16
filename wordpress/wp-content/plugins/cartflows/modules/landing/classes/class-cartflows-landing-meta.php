<?php
/**
 * Landing post meta box
 *
 * @package CartFlows
 */

/**
 * Meta Boxes setup
 */
class Cartflows_Landing_Meta extends Cartflows_Meta {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var $meta_option
	 */
	private static $meta_option = null;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		/* Init Metabox */
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
	}

	/**
	 * Init Metabox
	 */
	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 *  Setup Metabox
	 */
	public function setup_meta_box() {

		if ( _is_wcf_landing_type() ) {
			add_meta_box(
				'wcf-ladning-settings',                // Id.
				__( 'Landing Page Settings', 'cartflows' ), // Title.
				array( $this, 'landing_meta_box' ),      // Callback.
				wcf()->utils->get_step_post_type(),                 // Post_type.
				'normal',                               // Context.
				'high'                                  // Priority.
			);
		}
	}

	/**
	 * Landing Metabox Markup
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	public function landing_meta_box( $post ) {

		wp_nonce_field( 'save-nonce-landing-step-meta', 'nonce-landing-step-meta' );
		$stored = get_post_meta( $post->ID );

		$checkout_meta = self::get_meta_option( $post->ID );

		// Set stored and override defaults.
		foreach ( $stored as $key => $value ) {
			if ( array_key_exists( $key, $checkout_meta ) ) {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? maybe_unserialize( $stored[ $key ][0] ) : '';
			} else {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
			}
		}

		// Get defaults.
		$meta = self::get_meta_option( $post->ID );

		/**
		 * Get options
		 */
		$landing_data = array();
		foreach ( $meta as $key => $value ) {

			$landing_data[ $key ] = $meta[ $key ]['default'];
		}

		do_action( 'wcf_landing_settings_markup_before', $meta );
		$this->page_header_tab( $landing_data, $post->ID );
		do_action( 'wcf_landing_settings_markup_after', $meta );
	}

	/**
	 * Page Header Tabs
	 *
	 * @param  array $options Post meta.
	 * @param  int   $post_id Post ID.
	 */
	public function page_header_tab( $options, $post_id ) {

		$active_tab = get_post_meta( $post_id, 'wcf_active_tab', true );

		if ( empty( $active_tab ) ) {
			$active_tab = 'wcf-landing-shortcodes';
		}

		$tabs = array(
			array(
				'title' => __( 'Shortcodes', 'cartflows' ),
				'id'    => 'wcf-landing-shortcodes',
				'class' => 'wcf-landing-shortcodes' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-info',
			),
			array(
				'title' => __( 'Custom Script', 'cartflows' ),
				'id'    => 'wcf-landing-custom-script-header',
				'class' => 'wcf-landing-custom-script-header' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-format-aside',
			),
		);

		?>
		<div class="wcf-landing-table wcf-metabox-wrap widefat">
			<div class="wcf-table-container">
				<div class="wcf-column-left">
					<div class="wcf-tab-wrapper">

						<?php foreach ( $tabs as $key => $tab ) { ?>
							<div class="<?php echo esc_attr( $tab['class'] ); ?>" data-tab="<?php echo esc_attr( $tab['id'] ); ?>">
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
								<span class="wcf-tab-title"><?php echo esc_html( $tab['title'] ); ?></span>
							</div>
						<?php } ?>
						<input type="hidden" id="wcf_active_tab" name="wcf_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

					</div>
				</div>
				<div class="wcf-column-right">
					<div class="wcf-landing-shortcodes wcf-tab-content active widefat">
						<?php

						$next_step_link = wcf()->utils->get_linking_url(
							array( 'class' => 'wcf-next-step' )
						);

						echo wcf()->meta->get_shortcode_field(
							array(
								'label'   => __( 'Next Step Link', 'cartflows' ),
								'name'    => 'wcf-next-step-link',
								'content' => $next_step_link,
							)
						);

						?>
					</div>

					<?php $this->tab_custom_script( $options, $post_id ); ?>

					<?php $this->right_column_footer( $options, $post_id ); ?>
				</div>
			</div>
		</div>

		<?php

	}

	/**
	 * Get metabox options
	 *
	 * @param int $post_id post ID.
	 * @return array
	 */
	public static function get_meta_option( $post_id ) {

		if ( null === self::$meta_option ) {
			/**
			 * Set metabox options
			 *
			 * @see http://php.net/manual/en/filter.filters.sanitize.php
			 */
			self::$meta_option = wcf()->options->get_landing_fields( $post_id );
		}

		return self::$meta_option;
	}

	/**
	 * Metabox Save
	 *
	 * @param  number $post_id Post ID.
	 * @return void
	 */
	public function save_meta_box( $post_id ) {

		// Checks save status.
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		$is_valid_nonce = ( isset( $_POST['nonce-landing-step-meta'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce-landing-step-meta'] ) ), 'save-nonce-landing-step-meta' ) ) ? true : false;

		// Exits script depending on save status.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		wcf()->options->save_landing_fields( $post_id );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Landing_Meta::get_instance();
