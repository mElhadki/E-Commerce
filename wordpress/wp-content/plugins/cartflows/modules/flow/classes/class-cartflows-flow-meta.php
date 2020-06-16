<?php
/**
 * Flow meta
 *
 * @package CartFlows
 */

/**
 * Meta Boxes setup
 */
class Cartflows_Flow_Meta {


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
	private static $meta_option;

	/**
	 * For Gutenberg
	 *
	 * @var $is_gutenberg_editor_active
	 */
	private $is_gutenberg_editor_active = false;

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

		add_action( 'admin_head', array( $this, 'menu_highlight' ) );

		add_action( 'admin_init', array( $this, 'admin_init_actions' ) );

		/* Init Metabox */
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

		/* Add Scripts */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 20 );

		add_action( 'wp_ajax_cartflows_delete_flow_step', array( $this, 'cartflows_delete_flow_step' ) );
		add_action( 'wp_ajax_cartflows_reorder_flow_steps', array( $this, 'cartflows_reorder_flow_steps' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );

		/* To check the status of gutenberg */
		add_action( 'enqueue_block_editor_assets', array( $this, 'set_block_editor_status' ) );

		/* Add back to edit flow button for gutenberg */
		add_action( 'admin_footer', array( $this, 'gutenberg_module_templates' ) );
	}

	/**
	 * Display admin notices.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_notices() {

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type() ) {
			return;
		}

		$flow_id = get_post_meta( get_the_id(), 'wcf-flow-id', true );
		if ( $flow_id ) { ?>
			<div class="wcf-notice-back-edit-flow">
				<p>
					<a href="<?php echo esc_url( get_edit_post_link( $flow_id ) ); ?>" class="button button-primary button-hero wcf-header-back-button" style="text-decoration: none;">
						<i class="dashicons dashicons-arrow-left-alt"></i> 
						<?php esc_html_e( 'Back to edit Flow', 'cartflows' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Initialize admin actions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_init_actions() {
		add_action( 'before_delete_post', array( $this, 'step_post_sync' ) );
		add_action( 'wp_trash_post', array( $this, 'step_post_trash_sync' ) );
		add_action( 'untrashed_post', array( $this, 'step_post_untrash_sync' ) );
	}

	/**
	 * Delete term data and steps data after deleting flow.
	 *
	 * @since 1.0.0
	 * @param int $pid post id.
	 *
	 * @return void
	 */
	public function step_post_sync( $pid ) {

		global $post_type;

		if ( CARTFLOWS_FLOW_POST_TYPE === $post_type ) {

			$steps = get_post_meta( $pid, 'wcf-steps', true );

			if ( $steps && is_array( $steps ) ) {
				foreach ( $steps as $i => $step ) {
					wp_delete_post( $step['id'], true );
				}
			}

			$term_data = term_exists( 'flow-' . $pid, CARTFLOWS_TAXONOMY_STEP_FLOW );

			if ( is_array( $term_data ) ) {
				wp_delete_term( $term_data['term_id'], CARTFLOWS_TAXONOMY_STEP_FLOW );
			}
		}
	}

	/**
	 * Trash steps data after trashing flow.
	 *
	 * @since 1.0.0
	 * @param int $pid post id.
	 *
	 * @return void
	 */
	public function step_post_trash_sync( $pid ) {

		global $post_type;

		if ( CARTFLOWS_FLOW_POST_TYPE === $post_type ) {

			$steps = get_post_meta( $pid, 'wcf-steps', true );

			if ( $steps && is_array( $steps ) ) {
				foreach ( $steps as $i => $step ) {
					wp_trash_post( $step['id'] );
				}
			}
		}
	}

	/**
	 * Untrash steps data after restoring flow.
	 *
	 * @since 1.0.0
	 * @param int $pid post id.
	 *
	 * @return void
	 */
	public function step_post_untrash_sync( $pid ) {

		global $post_type;

		if ( CARTFLOWS_FLOW_POST_TYPE === $post_type ) {

			$steps = get_post_meta( $pid, 'wcf-steps', true );

			if ( $steps && is_array( $steps ) ) {
				foreach ( $steps as $i => $step ) {
					wp_untrash_post( $step['id'] );
				}
			}
		}
	}

	/**
	 * Create step for given flow.
	 *
	 * @param int $flow_id flow ID.
	 * @param int $step_type step type.
	 * @param int $step_title step title.
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function create_step( $flow_id, $step_type, $step_title ) {

		$new_step_id = wp_insert_post(
			array(
				'post_type'   => CARTFLOWS_STEP_POST_TYPE,
				'post_title'  => $step_title,
				'post_status' => 'publish',
			)
		);

		if ( $new_step_id ) {

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $step_title,
				'type'  => $step_type,
			);

			// insert post meta.
			update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );
			update_post_meta( $new_step_id, 'wcf-step-type', $step_type );

			wp_set_object_terms( $new_step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
			wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );
		}

		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		return $new_step_id;
	}

	/**
	 * Delete step for flow
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cartflows_delete_flow_step() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-delete-flow-step', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_id'] ) ) {
			$flow_id = intval( $_POST['post_id'] );
			$step_id = intval( $_POST['step_id'] );
		}
		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step not deleted for flow - %s', 'cartflows' ), $flow_id ),
		);

		if ( ! $flow_id || ! $step_id ) {
			wp_send_json( $result );
		}

		wp_delete_post( $step_id, true );

		$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $flow_steps ) ) {
			wp_send_json( $result );
		}

		foreach ( $flow_steps as $index => $data ) {

			if ( intval( $data['id'] ) === $step_id ) {
				unset( $flow_steps[ $index ] );
				break;
			}
		}

		/* Set index order properly */
		$flow_steps = array_merge( $flow_steps );

		update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Step deleted for flow - %s', 'cartflows' ), $flow_id ),
		);

		wp_send_json( $result );
	}

	/**
	 * Reorder step flow
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function cartflows_reorder_flow_steps() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'wcf-reorder-flow-steps', 'security' );

		if ( isset( $_POST['post_id'] ) && isset( $_POST['step_ids'] ) ) {
			$flow_id  = intval( $_POST['post_id'] );
			$step_ids = array_map( 'intval', $_POST['step_ids'] );
		}
		$result = array(
			'status' => false,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Steps not sorted for flow - %s', 'cartflows' ), $flow_id ),
		);

		if ( ! $flow_id || ! is_array( $step_ids ) ) {
			wp_send_json( $result );
		}

		$new_flow_steps = array();

		foreach ( $step_ids as $index => $step_id ) {

			$new_flow_steps[] = array(
				'id'    => intval( $step_id ),
				'title' => get_the_title( $step_id ),
				'type'  => get_post_meta( $step_id, 'wcf-step-type', true ),
			);
		}

		update_post_meta( $flow_id, 'wcf-steps', $new_flow_steps );

		$result = array(
			'status' => true,
			/* translators: %s flow id */
			'text'   => sprintf( __( 'Steps sorted for flow - %s', 'cartflows' ), $flow_id ),
		);

		wp_send_json( $result );
	}


	/**
	 * Load admin scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_scripts() {

		global $pagenow;
		global $post;

		$screen = get_current_screen();

		if ( ( 'post-new.php' == $pagenow || 'post.php' == $pagenow ) && CARTFLOWS_FLOW_POST_TYPE == $screen->post_type ) {

			wp_enqueue_script(
				'wcf-flow-meta',
				CARTFLOWS_URL . 'admin/assets/js/flow-admin-edit.js',
				array( 'jquery', 'jquery-ui-sortable' ),
				CARTFLOWS_VER,
				true
			);

			wp_enqueue_style( 'wcf-flow-meta', CARTFLOWS_URL . 'admin/assets/css/flow-admin-edit.css', '', CARTFLOWS_VER );
			wp_style_add_data( 'wcf-flow-meta', 'rtl', 'replace' );

			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);

			wp_localize_script( 'jquery', 'cartflows', apply_filters( 'wcf_js_localize', $localize ) );
		}
	}

	/**
	 * Initialize meta box
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_metabox() {

		/**
		 * Fires after the title field.
		 *
		 * @param WP_Post $post Post object.
		 */
		add_action( 'add_meta_boxes', array( $this, 'settings_meta_box' ) );
		add_action( 'edit_form_after_title', array( $this, 'setup_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 * Is first time import?
	 *
	 * @param  integer $post_id post ID.
	 * @return bool
	 */
	public function is_flow_imported( $post_id = 0 ) {

		if ( 0 === $post_id ) {
			$post_id = get_the_ID();
		}

		$steps  = get_post_meta( $post_id, 'wcf-steps', true );
		$choice = get_post_meta( $post_id, 'wcf-flow-choise', true );

		if ( empty( $steps ) && 'import' === $choice ) {
			return true;
		}

		return false;
	}

	/**
	 * Setup meta box.
	 *
	 * @return void
	 */
	public function setup_meta_box() {
		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		/**
		* Adding Add new step button to the top*/
		echo $this->add_add_new_step_button();

		$this->markup_meta_box();

		$this->add_upgrade_to_pro_metabox();
	}


	/**
	 *  Add metabox when cartflows pro is not enabled.
	 */
	public function add_upgrade_to_pro_metabox() {

		if ( ! _is_cartflows_pro() ) {
			add_meta_box(
				'wcf-upgrade-pro',
				__( 'Analytics', 'cartflows' ),
				array( $this, 'upgrade_to_pro' ),
				CARTFLOWS_FLOW_POST_TYPE,
				'side',
				'high'
			);
		}

	}

	/**
	 *  Show Upgrade To Pro markup.
	 */
	public function upgrade_to_pro() {

		echo '<div>';
			/* translators: %s: link */
			echo '<p><i>' . sprintf( esc_html__( 'Upgrade to %1$sCartFlows Pro%2$s for Analytics feature', 'cartflows' ), '<a href="https://cartflows.com/" target="_blank">', '</a>' ) . '</i></p>';
			echo '<p><a target="_blank" href="https://cartflows.com/" class="button button-primary">' . esc_html__( 'Get Pro', 'cartflows' ) . '</a></p>';
		echo '</div>';

	}

	/**
	 * Settings meta box.
	 *
	 * @return void
	 */
	public function settings_meta_box() {

		if ( CARTFLOWS_FLOW_POST_TYPE === get_post_type() ) {

			/* No need of sandbox will delete it later */
			add_meta_box(
				'wcf-sandbox-settings',                    // Id.
				__( 'Flow Settings', 'cartflows' ), // Title.
				array( $this, 'sandbox_meta_box' ),      // Callback.
				CARTFLOWS_FLOW_POST_TYPE,               // Post_type.
				'side',                               // Context.
				'high'                                  // Priority.
			);

			do_action( 'cartflows_add_flow_metabox' );
		}
	}

	/**
	 * Metabox Markup
	 *
	 * @return void
	 */
	public function markup_meta_box() {
		global $post;

		wp_nonce_field( 'save-nonce-flow-meta', 'nonce-flow-meta' );

		// Get defaults.
		$meta = self::get_current_post_meta( $post->ID );

		/**
		 * Get options
		 */
		$updated_data = array(
			'steps' => $meta['wcf-steps']['default'],
		);

		do_action( 'wcf_flow_settings_markup_before', $meta );
		$this->page_header_tab( $updated_data );
		do_action( 'wcf_flow_settings_markup_after', $meta );
	}

	/**
	 * Metabox Markup
	 *
	 * @param object $post Post object.
	 * @return void
	 */
	public function sandbox_meta_box( $post ) {

		// Get defaults.
		$meta = self::get_current_post_meta( $post->ID );

		/**
		 * Get options
		 */
		foreach ( $meta as $key => $value ) {
			$updated_data[ $key ] = $meta[ $key ]['default'];
		}

		do_action( 'wcf_flow_sandbox_markup_before', $meta );
		$this->sandbox_markup( $updated_data );
		do_action( 'wcf_flow_sandbox_markup_after', $meta );
	}

	/**
	 * Page Header Tabs
	 *
	 * @param  array $options Post meta.
	 * @return void
	 */
	public function page_header_tab( $options ) {

		include_once CARTFLOWS_FLOW_DIR . 'view/meta-flow-steps.php';
	}

	/**
	 * Sandbox Markup
	 *
	 * @param  array $options Post meta.
	 * @return void
	 */
	public function sandbox_markup( $options ) {
		?>
		<div class="wcf-flow-sandbox-table wcf-general-metabox-wrap widefat">
			<div class="wcf-flow-sandbox-table-container">
				<?php
				echo wcf()->meta->get_checkbox_field(
					array(
						'name'  => 'wcf-testing',
						'value' => $options['wcf-testing'],
						'after' => esc_html__( 'Enable Test Mode', 'cartflows' ),
					)
				);

				echo wcf()->meta->get_description_field(
					array(
						'name'    => 'wcf-testing-note',
						'content' => esc_html__( 'If you are using WooCommerce plugin then test mode will add random products in your flow, so you can preview it easily while testing.', 'cartflows' ),
					)
				);

				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Keep the menu open when editing the flows.
	 * Highlights the wanted admin (sub-) menu items for the CPT.
	 *
	 * @since 1.0.0
	 */
	public function menu_highlight() {
		global $parent_file, $submenu_file, $post_type;
		if ( CARTFLOWS_FLOW_POST_TYPE == $post_type ) :
			$parent_file  = CARTFLOWS_SLUG;//phpcs:ignore
			$submenu_file = 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE;//phpcs:ignore
		endif;
	}

	/**
	 * Get metabox options
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	public static function get_meta_option( $post_id ) {

		if ( null === self::$meta_option ) {
			/**
			 * Set metabox options
			 */
			self::$meta_option = wcf()->options->get_flow_fields( $post_id );
		}

		return self::$meta_option;
	}

	/**
	 * Get metabox options
	 *
	 * @param int $post_id post ID.
	 * @return array
	 */
	public static function get_current_post_meta( $post_id ) {

		$stored = get_post_meta( $post_id );

		$default_meta = self::get_meta_option( $post_id );

		// Set stored and override defaults.
		foreach ( $stored as $key => $value ) {
			if ( array_key_exists( $key, $default_meta ) ) {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? maybe_unserialize( $stored[ $key ][0] ) : '';
			} else {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
			}
		}

		return self::get_meta_option( $post_id );
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

		$is_valid_nonce = ( isset( $_POST['nonce-flow-meta'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce-flow-meta'] ) ), 'save-nonce-flow-meta' ) ) ? true : false;

		// Exits script depending on save status.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		wcf()->options->save_flow_fields( $post_id );
	}

	/**
	 * Localize variables in admin
	 *
	 * @param array $vars variables.
	 */
	public function localize_vars( $vars ) {

		$ajax_actions = array(
			'wcf_setup_default_steps',
			'wcf_add_flow_step',
			'wcf_delete_flow_step',
			'wcf_reorder_flow_steps',
		);

		foreach ( $ajax_actions as $action ) {

			$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
		}

		return $vars;
	}

	/**
	 * Add New Step Button
	 *
	 * @return string
	 */
	public function add_add_new_step_button() {
		$add_new_btn_markup          = '<style>.wrap{ position:relative;}</style>';
		$add_new_btn_markup         .= "<div class='wcf-button-wrap'>";
			$add_new_btn_markup     .= "<button class='wcf-trigger-popup page-title-action'>";
				$add_new_btn_markup .= esc_html__( 'Add New Step', 'cartflows' );
			$add_new_btn_markup     .= '</button>';
		$add_new_btn_markup         .= '</div>';

		return $add_new_btn_markup;
	}

	/**
	 * Back to flow button gutenberg template
	 *
	 * @return void
	 */
	public function gutenberg_module_templates() {

		// Exit if block editor is not enabled.
		if ( ! $this->is_gutenberg_editor_active ) {
			return;
		}

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type() ) {
			return;
		}

		$flow_id = get_post_meta( get_the_id(), 'wcf-flow-id', true );

		if ( $flow_id ) {
			?>
		<script id="wcf-gutenberg-back-flow-button" type="text/html">
			<div class="wcf-notice-back-edit-flow gutenberg-button" >
				<a href="<?php echo esc_url( get_edit_post_link( $flow_id ) ); ?>" class="button button-primary button-large wcf-header-back-button" style="text-decoration: none;">
					<i class="dashicons dashicons-arrow-left-alt"></i> 
					<?php esc_html_e( 'Back to edit Flow', 'cartflows' ); ?>
				</a>
			</div>
		</script>
			<?php
		}
	}

	/**
	 * Set status true for gutenberg.
	 *
	 * @return void
	 */
	public function set_block_editor_status() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Set gutenberg status here.
		$this->is_gutenberg_editor_active = true;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Meta::get_instance();
