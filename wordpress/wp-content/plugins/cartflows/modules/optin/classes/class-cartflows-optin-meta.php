<?php
/**
 * Checkout post meta
 *
 * @package CartFlows
 */

/**
 * Meta Boxes setup
 */
class Cartflows_Optin_Meta extends Cartflows_Meta {


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

		if ( _is_wcf_optin_type() ) {
			add_meta_box(
				'wcf-optin-settings',                // Id.
				__( 'Optin Settings', 'cartflows' ), // Title.
				array( $this, 'markup_meta_box' ),      // Callback.
				wcf()->utils->get_step_post_type(),                 // Post_type.
				'normal',                               // Context.
				'high'                                  // Priority.
			);
		}
	}

	/**
	 * Metabox Markup
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	public function markup_meta_box( $post ) {

		wp_nonce_field( 'save-nonce-optin-step-meta', 'nonce-optin-step-meta' );

		$stored = get_post_meta( $post->ID );

		$optin_meta = self::get_meta_option( $post->ID );

		// Set stored and override defaults.
		foreach ( $stored as $key => $value ) {
			if ( array_key_exists( $key, $optin_meta ) ) {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? maybe_unserialize( $stored[ $key ][0] ) : '';
			} else {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
			}
		}

		// Get defaults.
		$meta       = self::get_meta_option( $post->ID );
		$optin_meta = array();

		foreach ( $meta as $key => $value ) {
			$optin_meta[ $key ] = $meta[ $key ]['default'];
		}

		do_action( 'wcf_optin_settings_markup_before' );
		$this->tabs_markup( $optin_meta, $post->ID );
		do_action( 'wcf_optin_settings_markup_after' );
	}

	/**
	 * Page Header Tabs
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tabs_markup( $options, $post_id ) {

		$active_tab = get_post_meta( $post_id, 'wcf-active-tab', true );

		if ( empty( $active_tab ) ) {
			$active_tab = 'wcf-optin-shortcodes';
		}

		$tab_array = array(
			array(
				'title' => __( 'Shortcodes', 'cartflows' ),
				'id'    => 'wcf-optin-shortcodes',
				'class' => 'wcf-optin-shortcodes' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-editor-code',
			),
			array(
				'title' => __( 'Select Product', 'cartflows' ),
				'id'    => 'wcf-optin-general',
				'class' => 'wcf-optin-general' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-info',
			),
			array(
				'title' => __( 'Design', 'cartflows' ),
				'id'    => 'wcf-optin-style',
				'class' => 'wcf-optin-style' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-admin-customizer',
			),
			array(
				'title' => __( 'Form Fields', 'cartflows' ),
				'id'    => 'wcf-optin-custom-fields',
				'class' => 'wcf-optin-custom-fields' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-welcome-widgets-menus',
			),
			array(
				'title' => __( 'Settings', 'cartflows' ),
				'id'    => 'wcf-optin-custom-settings',
				'class' => 'wcf-optin-custom-settings' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-admin-generic',
			),
			array(
				'title' => __( 'Custom Script', 'cartflows' ),
				'id'    => 'wcf-optin-custom-script-header',
				'class' => 'wcf-optin-custom-script-header' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
				'icon'  => 'dashicons-format-aside',
			),
		);

		$tabs = $tab_array;

		?>
		<div class="wcf-optin-table wcf-metabox-wrap widefat">
			<div class="wcf-table-container">
				<div class="wcf-column-left">
					<div class="wcf-tab-wrapper">

						<?php foreach ( $tabs as $key => $tab ) { ?>
							<div class="<?php echo esc_attr( $tab['class'] ); ?>" data-tab="<?php echo esc_attr( $tab['id'] ); ?>">
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
								<span class="wcf-tab-title"><?php echo esc_html( $tab['title'] ); ?></span>
							</div>
						<?php } ?>

						<input type="hidden" id="wcf-active-tab" name="wcf-active-tab" value="<?php echo esc_attr( $active_tab ); ?>" />
					</div>
				</div>
				<div class="wcf-column-right">
					<?php $this->tab_shortcodes( $options, $post_id ); ?>
					<?php $this->tab_general( $options, $post_id ); ?>
					<?php $this->tab_style( $options, $post_id ); ?>
					<?php $this->tab_custom_fields( $options, $post_id ); ?>
					<?php $this->tab_custom_settings( $options, $post_id ); ?>
					<?php $this->tab_custom_script( $options, $post_id ); ?>
					<?php $this->right_column_footer( $options, $post_id ); ?>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Shortcodes tab
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tab_shortcodes( $options, $post_id ) {
		?>
		<div class="wcf-optin-shortcodes wcf-tab-content widefat">

			<?php

			echo wcf()->meta->get_shortcode_field(
				array(
					'label'   => 'Optin Page',
					'name'    => 'wcf-optin-shortcode',
					'content' => '[cartflows_optin]',
					'help'    => esc_html__( 'Add this shortcode to your optin page', 'cartflows' ),
				)
			);
			?>
		</div>
		<?php
	}


	/**
	 * General tab
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tab_general( $options, $post_id ) {
		?>
		<div class="wcf-optin-general wcf-tab-content widefat">

			<?php

			echo wcf()->meta->get_product_selection_field(
				array(
					'name'        => 'wcf-optin-product',
					'value'       => $options['wcf-optin-product'],
					'label'       => __( 'Select Free Product', 'cartflows' ),
					'help'        => __( 'Select Free and Virtual product only.', 'cartflows' ),
					'multiple'    => false,
					'allow_clear' => true,
				)
			);

			?>
		</div>
		<?php
	}

	/**
	 * Tab custom fields
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tab_custom_fields( $options, $post_id ) {
		?>
		<div class="wcf-optin-custom-fields wcf-tab-content widefat">
			<?php
				/* Custom Checkout Fields Section */

			if ( ! _is_cartflows_pro() ) {
				echo wcf()->meta->get_description_field(
					array(
						'name'    => 'wcf-upgrade-to-pro',
						/* translators: %s: link */
						'content' => '<i>' . sprintf( esc_html__( 'Upgrade to %1$sCartFlows Pro%2$s for Custom Fields feature.', 'cartflows' ), '<a href="https://cartflows.com/" target="_blank">', '</a>' ) . '</i>',
					)
				);
			}
			?>
			<?php do_action( 'cartflows_optin_custom_fields_tab_content', $options, $post_id ); ?>
		</div>
		<?php
	}

	/**
	 * Tab custom settings
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tab_custom_settings( $options, $post_id ) {
		?>
		<div class="wcf-optin-custom-settings wcf-tab-content widefat">
			<div class="wcf-custom-settings-fields">
			<?php
				echo wcf()->meta->get_checkbox_field(
					array(
						'label'  => __( 'Pass Fields as URL Parameters', 'cartflows' ),
						'name'   => 'wcf-optin-pass-fields',
						'value'  => $options['wcf-optin-pass-fields'],
						'after'  => __( 'Enable', 'cartflows' ),
						'help'   => __( 'You can pass specific fields from the form to next step as URL query parameters.', 'cartflows' ),
						'toggle' => array(
							'fields' => array(
								'yes' => array( 'wcf-optin-pass-specific-fields' ),
							),
						),
					)
				);

				echo wcf()->meta->get_text_field(
					array(
						'label' => __( 'Enter form field', 'cartflows' ),
						'name'  => 'wcf-optin-pass-specific-fields',
						'value' => $options['wcf-optin-pass-specific-fields'],
						'help'  => __( 'Enter comma seprated field name. E.g. first_name, last_name', 'cartflows' ),
						'attr'  => array(
							'placeholder' => __( 'Fields to pass, separated by commas', 'cartflows' ),
						),
					)
				);

				echo wcf()->meta->get_description_field(
					array(
						'name'    => 'wcf-optin-pass-fields-doc',
						'content' => __( 'Enter comma seprated field name. E.g. first_name, last_name', 'cartflows' ),
						/* translators: %s: link */
						'content' => sprintf( esc_html__( 'You can pass field value as a URL parameter to the next step. %1$sClick here%2$s for more information.', 'cartflows' ), '<a href="https://cartflows.com/docs/pass-variable-as-query-parameters-to-url/" target="_blank">', '</a>' ),
					)
				);

			?>
			</div>
		</div>
		<?php
	}

	/**
	 * Tab style
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	public function tab_style( $options, $post_id ) {
		?>

		<div class="wcf-optin-style wcf-tab-content widefat">
			<?php
				echo wcf()->meta->get_color_picker_field(
					array(
						'label' => __( 'Primary Color', 'cartflows' ),
						'name'  => 'wcf-primary-color',
						'value' => $options['wcf-primary-color'],
					)
				);

				echo wcf()->meta->get_font_family_field(
					array(
						'for'   => 'wcf-base',
						'label' => esc_html__( 'Font Family', 'cartflows' ),
						'name'  => 'wcf-base-font-family',
						'value' => $options['wcf-base-font-family'],
					)
				);
			?>
			<div class="wcf-cs-fields">
				<div class="wcf-cs-fields-options">
					<?php
						echo wcf()->meta->get_section(
							array(
								'label' => __( 'Input Fields', 'cartflows' ),
							)
						);

						$fields_skin_pro_option = array();
					if ( ! _is_cartflows_pro() ) {
						$fields_skin_pro_option = array(
							'floating-labels' => __( 'Floating Labels (Available in CartFlows Pro)', 'cartflows' ),
						);
					}

						echo wcf()->meta->get_select_field(
							array(
								'label'       => __( 'Style', 'cartflows' ),
								'name'        => 'wcf-input-fields-skins',
								'value'       => $options['wcf-input-fields-skins'],
								'options'     => array(
									'default'         => esc_html__( 'Default', 'cartflows' ),
									'floating-labels' => esc_html__( 'Floating Labels', 'cartflows' ),
								),
								'pro-options' => $fields_skin_pro_option,

							)
						);

						echo wcf()->meta->get_font_family_field(
							array(
								'for'   => 'wcf-input',
								'label' => esc_html__( 'Font Family', 'cartflows' ),
								'name'  => 'wcf-input-font-family',
								'value' => $options['wcf-input-font-family'],
							)
						);

						echo wcf()->meta->get_font_weight_field(
							array(
								'for'   => 'wcf-input',
								'label' => esc_html__( 'Font Weight', 'cartflows' ),
								'name'  => 'wcf-input-font-weight',
								'value' => $options['wcf-input-font-weight'],
							)
						);

						echo wcf()->meta->get_select_field(
							array(
								'label'   => __( 'Size', 'cartflows' ),
								'name'    => 'wcf-input-field-size',
								'value'   => $options['wcf-input-field-size'],
								'options' => array(
									'33px'   => esc_html__( 'Extra Small', 'cartflows' ),
									'38px'   => esc_html__( 'Small', 'cartflows' ),
									'44px'   => esc_html__( 'Medium', 'cartflows' ),
									'58px'   => esc_html__( 'Large', 'cartflows' ),
									'68px'   => esc_html__( 'Extra Large', 'cartflows' ),
									'custom' => esc_html__( 'Custom', 'cartflows' ),
								),
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Top Bottom Spacing', 'cartflows' ),
								'name'  => 'wcf-field-tb-padding',
								'value' => $options['wcf-field-tb-padding'],
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Left Right Spacing', 'cartflows' ),
								'name'  => 'wcf-field-lr-padding',
								'value' => $options['wcf-field-lr-padding'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Label Color', 'cartflows' ),
								'name'  => 'wcf-field-label-color',
								'value' => $options['wcf-field-label-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Text / Placeholder Color', 'cartflows' ),
								'name'  => 'wcf-field-color',
								'value' => $options['wcf-field-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Background Color', 'cartflows' ),
								'name'  => 'wcf-field-bg-color',
								'value' => $options['wcf-field-bg-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Border Color', 'cartflows' ),
								'name'  => 'wcf-field-border-color',
								'value' => $options['wcf-field-border-color'],
							)
						);

					?>
				</div>
				<div class="wcf-cs-button-options">
					<?php

						echo wcf()->meta->get_section(
							array(
								'label' => __( 'Submit Button', 'cartflows' ),
							)
						);

						echo wcf()->meta->get_text_field(
							array(
								'label' => __( 'Button Text', 'cartflows' ),
								'name'  => 'wcf-submit-button-text',
								'value' => $options['wcf-submit-button-text'],
								'attr'  => array(
									'placeholder' => __( 'Submit', 'cartflows' ),
								),
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Font Size', 'cartflows' ),
								'name'  => 'wcf-submit-font-size',
								'value' => $options['wcf-submit-font-size'],
							)
						);

						echo wcf()->meta->get_font_family_field(
							array(
								'for'   => 'wcf-button',
								'label' => esc_html__( 'Font Family', 'cartflows' ),
								'name'  => 'wcf-button-font-family',
								'value' => $options['wcf-button-font-family'],
							)
						);

						echo wcf()->meta->get_font_weight_field(
							array(
								'for'   => 'wcf-button',
								'label' => esc_html__( 'Font Weight', 'cartflows' ),
								'name'  => 'wcf-button-font-weight',
								'value' => $options['wcf-button-font-weight'],
							)
						);

						echo wcf()->meta->get_select_field(
							array(
								'label'   => __( 'Size', 'cartflows' ),
								'name'    => 'wcf-submit-button-size',
								'value'   => $options['wcf-submit-button-size'],
								'options' => array(
									'33px'   => esc_html__( 'Extra Small', 'cartflows' ),
									'38px'   => esc_html__( 'Small', 'cartflows' ),
									'44px'   => esc_html__( 'Medium', 'cartflows' ),
									'58px'   => esc_html__( 'Large', 'cartflows' ),
									'68px'   => esc_html__( 'Extra Large', 'cartflows' ),
									'custom' => esc_html__( 'Custom', 'cartflows' ),
								),
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Top Bottom Spacing', 'cartflows' ),
								'name'  => 'wcf-submit-tb-padding',
								'value' => $options['wcf-submit-tb-padding'],
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Left Right Spacing', 'cartflows' ),
								'name'  => 'wcf-submit-lr-padding',
								'value' => $options['wcf-submit-lr-padding'],
							)
						);

						echo wcf()->meta->get_select_field(
							array(
								'label'   => __( 'Position', 'cartflows' ),
								'name'    => 'wcf-submit-button-position',
								'value'   => $options['wcf-submit-button-position'],
								'options' => array(
									'left'   => esc_html__( 'Left', 'cartflows' ),
									'center' => esc_html__( 'Center', 'cartflows' ),
									'right'  => esc_html__( 'Right', 'cartflows' ),
								),
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Text Color', 'cartflows' ),
								'name'  => 'wcf-submit-color',
								'value' => $options['wcf-submit-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Text Hover Color', 'cartflows' ),
								'name'  => 'wcf-submit-hover-color',
								'value' => $options['wcf-submit-hover-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Background Color', 'cartflows' ),
								'name'  => 'wcf-submit-bg-color',
								'value' => $options['wcf-submit-bg-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Background Hover Color', 'cartflows' ),
								'name'  => 'wcf-submit-bg-hover-color',
								'value' => $options['wcf-submit-bg-hover-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Border Color', 'cartflows' ),
								'name'  => 'wcf-submit-border-color',
								'value' => $options['wcf-submit-border-color'],
							)
						);

						echo wcf()->meta->get_color_picker_field(
							array(
								'label' => __( 'Border Hover Color', 'cartflows' ),
								'name'  => 'wcf-submit-border-hover-color',
								'value' => $options['wcf-submit-border-hover-color'],
							)
						);

					?>
				</div>
			</div>
			<?php
			echo wcf()->meta->get_hidden_field(
				array(
					'name'  => 'wcf-field-google-font-url',
					'value' => $options['wcf-field-google-font-url'],
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Get metabox options
	 *
	 * @param int $post_id post ID.
	 */
	public static function get_meta_option( $post_id ) {

		if ( null === self::$meta_option ) {

			/**
			 * Set metabox options
			 *
			 * @see http://php.net/manual/en/filter.filters.sanitize.php
			 */
			self::$meta_option = wcf()->options->get_optin_fields( $post_id );
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

		$is_valid_nonce = ( isset( $_POST['nonce-optin-step-meta'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce-optin-step-meta'] ) ), 'save-nonce-optin-step-meta' ) ) ? true : false;

		// Exits script depending on save status.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		wcf()->options->save_optin_fields( $post_id );
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Optin_Meta::get_instance();
