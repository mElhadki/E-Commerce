<?php
/**
 * CartFlows Admin
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! class_exists( 'CartFlows_Importer' ) ) :

	/**
	 * CartFlows Import
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Importer {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			add_action( 'wp_ajax_cartflows_step_import', array( $this, 'import_step' ) );
			add_action( 'wp_ajax_cartflows_create_flow', array( $this, 'create_flow' ) );
			add_action( 'wp_ajax_cartflows_import_flow_step', array( $this, 'import_flow' ) );
			add_action( 'wp_ajax_cartflows_default_flow', array( $this, 'create_default_flow' ) );
			add_action( 'wp_ajax_cartflows_step_create_blank', array( $this, 'step_create_blank' ) );

			add_action( 'admin_footer', array( $this, 'js_templates' ) );
			add_action( 'cartflows_import_complete', array( $this, 'clear_cache' ) );

			add_filter( 'cartflows_admin_js_localize', array( $this, 'localize_vars' ) );

			add_action( 'wp_ajax_cartflows_activate_plugin', array( $this, 'activate_plugin' ) );

			add_action( 'admin_menu', array( $this, 'add_to_menus' ) );
			add_action( 'admin_init', array( $this, 'export_json' ) );
			add_action( 'admin_init', array( $this, 'import_json' ) );
			add_filter( 'post_row_actions', array( $this, 'export_link' ), 10, 2 );
			add_action( 'admin_action_cartflows_export_flow', array( $this, 'export_flow' ) );
		}

		/**
		 * Add the export link to action list for flows row actions
		 *
		 * @since 1.1.4
		 *
		 * @param array  $actions Actions array.
		 * @param object $post Post object.
		 *
		 * @return array
		 */
		public function export_link( $actions, $post ) {
			if ( current_user_can( 'edit_posts' ) && isset( $post ) && CARTFLOWS_FLOW_POST_TYPE === $post->post_type ) {
				$actions['export'] = '<a href="' . wp_nonce_url( 'admin.php?action=cartflows_export_flow&post=' . $post->ID, basename( __FILE__ ), 'flow_export_nonce' ) . '" title="' . __( 'Export this flow', 'cartflows' ) . '" rel="permalink">' . __( 'Export', 'cartflows' ) . '</a>';
			}
			return $actions;
		}

		/**
		 * Add menus
		 *
		 * @since 1.1.4
		 */
		public function add_to_menus() {
			add_submenu_page( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE, __( 'Flow Export', 'cartflows' ), __( 'Flow Export', 'cartflows' ), 'export', 'flow_exporter', array( $this, 'exporter_markup' ) );
			add_submenu_page( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE, __( 'Flow Import', 'cartflows' ), __( 'Flow Import', 'cartflows' ), 'import', 'flow_importer', array( $this, 'importer_markup' ) );
		}

		/**
		 * Export flow with steps and its meta
		 *
		 * @since 1.1.4
		 */
		public function export_flow() {

			if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'cartflows_export_flow' == $_REQUEST['action'] ) ) ) {
				wp_die( esc_html__( 'No post to export has been supplied!', 'cartflows' ) );
			}

			if ( ! isset( $_GET['flow_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['flow_export_nonce'] ) ), basename( __FILE__ ) ) ) {
				return;
			}

			// Get the original post id.
			$flow_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

			$flows   = array();
			$flows[] = $this->get_flow_export_data( $flow_id );
			$flows   = apply_filters( 'cartflows_export_data', $flows );

			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=cartflows-flow-' . $flow_id . '-' . gmdate( 'm-d-Y' ) . '.json' );
			header( 'Expires: 0' );

			echo wp_json_encode( $flows );
			exit;
		}

		/**
		 * Export flow markup
		 *
		 * @since 1.1.4
		 */
		public function exporter_markup() {
			include_once CARTFLOWS_DIR . 'includes/exporter.php';
		}

		/**
		 * Import flow markup
		 *
		 * @since 1.1.4
		 */
		public function importer_markup() {
			include_once CARTFLOWS_DIR . 'includes/importer.php';
		}

		/**
		 * Export flow
		 *
		 * @since 1.1.4
		 */
		public function export_json() {
			if ( empty( $_POST['cartflows-action'] ) || 'export' != $_POST['cartflows-action'] ) {
				return;
			}

			if ( isset( $_POST['cartflows-action-nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cartflows-action-nonce'] ) ), 'cartflows-action-nonce' ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$flows = $this->get_all_flow_export_data();
			$flows = apply_filters( 'cartflows_export_data', $flows );

			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=cartflows-flow-export-' . gmdate( 'm-d-Y' ) . '.json' );
			header( 'Expires: 0' );

			echo wp_json_encode( $flows );
			exit;
		}

		/**
		 * Get flow export data
		 *
		 * @since 1.1.4
		 *
		 * @param  integer $flow_id Flow ID.
		 * @return array
		 */
		public function get_flow_export_data( $flow_id ) {

			$export_all = apply_filters( 'cartflows_export_all', true );

			$valid_step_meta_keys = array(
				'_wp_page_template',
				'_thumbnail_id',
				'classic-editor-remember',
			);

			$new_steps = array();
			$steps     = get_post_meta( $flow_id, 'wcf-steps', true );
			if ( $steps ) {
				foreach ( $steps as $key => $step ) {

					// Add step post meta.
					$new_all_meta = array();
					$all_meta     = get_post_meta( $step['id'] );

					// Add single step.
					$step_data_arr = array(
						'title'        => get_the_title( $step['id'] ),
						'type'         => $step['type'],
						'meta'         => $all_meta,
						'post_content' => '',
					);

					if ( $export_all ) {

						$step_post_obj = get_post( $step['id'] );

						$step_data_arr['post_content'] = $step_post_obj->post_content;
					}

					$new_steps[] = $step_data_arr;
				}
			}

			// Add single flow.
			return array(
				'title' => get_the_title( $flow_id ),
				'steps' => $new_steps,
			);
		}

		/**
		 * Get all flow export data
		 *
		 * @since 1.1.4
		 */
		public function get_all_flow_export_data() {

			$query_args = array(
				'post_type'      => CARTFLOWS_FLOW_POST_TYPE,

				// Query performance optimization.
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'posts_per_page' => -1,
			);

			$query = new WP_Query( $query_args );
			$flows = array();
			if ( $query->posts ) {
				foreach ( $query->posts as $key => $post_id ) {
					$flows[] = $this->get_flow_export_data( $post_id );
				}
			}

			return $flows;
		}

		/**
		 * Import our exported file
		 *
		 * @since 1.1.4
		 */
		public function import_json() {
			if ( empty( $_POST['cartflows-action'] ) || 'import' != $_POST['cartflows-action'] ) {
				return;
			}

			if ( isset( $_POST['cartflows-action-nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cartflows-action-nonce'] ) ), 'cartflows-action-nonce' ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$filename  = $_FILES['file']['name']; //phpcs:ignore
			$file_info = explode( '.', $filename );
			$extension = end( $file_info );

			if ( 'json' != $extension ) {
				wp_die( esc_html__( 'Please upload a valid .json file', 'cartflows' ) );
			}

			$file = $_FILES['file']['tmp_name']; //phpcs:ignore

			if ( empty( $file ) ) {
				wp_die( esc_html__( 'Please upload a file to import', 'cartflows' ) );
			}

			// Retrieve the settings from the file and convert the JSON object to an array.
			$flows = json_decode( file_get_contents( $file ), true );//phpcs:ignore

			$this->import_from_json_data( $flows );

			add_action( 'admin_notices', array( $this, 'imported_successfully' ) );
		}

		/**
		 * Import flow from the JSON data
		 *
		 * @since x.x.x
		 * @param  array $flows JSON array.
		 * @return void
		 */
		public function import_from_json_data( $flows ) {
			if ( $flows ) {

				$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

				foreach ( $flows as $key => $flow ) {

					$flow_title = $flow['title'];
					if ( post_exists( $flow['title'] ) ) {
						$flow_title = $flow['title'] . ' Copy';
					}

					// Create post object.
					$new_flow_args = apply_filters(
						'cartflows_flow_importer_args',
						array(
							'post_type'   => CARTFLOWS_FLOW_POST_TYPE,
							'post_title'  => $flow_title,
							'post_status' => 'publish',
						)
					);

					// Insert the post into the database.
					$flow_id = wp_insert_post( $new_flow_args );

					/**
					 * Fire after flow import
					 *
					 * @since x.x.x
					 * @param int $flow_id Flow ID.
					 * @param array $new_flow_args Flow post args.
					 * @param array $flows Flow JSON data.
					 */
					do_action( 'cartflows_flow_imported', $flow_id, $new_flow_args, $flows );

					if ( $flow['steps'] ) {
						foreach ( $flow['steps'] as $key => $step ) {

							$new_all_meta = array();
							if ( is_array( $step['meta'] ) ) {
								foreach ( $step['meta'] as $meta_key => $mvalue ) {
									$new_all_meta[ $meta_key ] = maybe_unserialize( $mvalue[0] );
								}
							}
							$new_step_args = apply_filters(
								'cartflows_step_importer_args',
								array(
									'post_type'    => CARTFLOWS_STEP_POST_TYPE,
									'post_title'   => $step['title'],
									'post_status'  => 'publish',
									'meta_input'   => $new_all_meta,
									'post_content' => isset( $step['post_content'] ) ? $step['post_content'] : '',
								)
							);

							$new_step_id = wp_insert_post( $new_step_args );

							/**
							 * Fire after step import
							 *
							 * @since x.x.x
							 * @param int $new_step_id step ID.
							 * @param int $flow_id flow ID.
							 * @param array $new_step_args Step post args.
							 * @param array $flow_steps Flow steps.
							 * @param array $flows All flows JSON data.
							 */
							do_action( 'cartflows_step_imported', $new_step_id, $flow_id, $new_step_args, $flow['steps'], $flows );

							// Insert post meta.
							update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );

							$step_taxonomy = CARTFLOWS_TAXONOMY_STEP_TYPE;
							$current_term  = term_exists( $step['type'], $step_taxonomy );

							// // Set type object.
							$data      = get_term( $current_term['term_id'], $step_taxonomy );
							$step_slug = $data->slug;
							wp_set_object_terms( $new_step_id, $data->slug, $step_taxonomy );

							// Set type.
							update_post_meta( $new_step_id, 'wcf-step-type', $data->slug );

							// Set flow.
							wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

							self::get_instance()->set_step_to_flow( $flow_id, $new_step_id, $step['title'], $step_slug );

							if ( isset( $step['post_content'] ) && ! empty( $step['post_content'] ) ) {

								// Download and replace images.
								$content = $this->get_content( $step['post_content'] );

								// Update post content.
								wp_update_post(
									array(
										'ID'           => $new_step_id,
										'post_content' => $content,
									)
								);
							}

							// Elementor Data.
							if ( ( 'elementor' === $default_page_builder ) && class_exists( '\Elementor\Plugin' ) ) {
								// Add "elementor" in import [queue].
								// @todo Remove required `allow_url_fopen` support.
								if ( ini_get( 'allow_url_fopen' ) && isset( $step['meta']['_elementor_data'] ) ) {
									$obj = new \Elementor\TemplateLibrary\CartFlows_Importer_Elementor();
									$obj->import_single_template( $new_step_id );
								}
							}

							// Beaver Builder.
							if ( ( 'beaver-builder' === $default_page_builder ) && class_exists( 'FLBuilder' ) ) {
								if ( isset( $step['meta']['_fl_builder_data'] ) ) {
									CartFlows_Importer_Beaver_Builder::get_instance()->import_single_post( $new_step_id );
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Download and Replace hotlink images
		 *
		 * @since x.x.x
		 *
		 * @param  string $content Mixed post content.
		 * @return array           Hotlink image array.
		 */
		public function get_content( $content = '' ) {

			$content = stripslashes( $content );

			// Extract all links.
			$all_links = wp_extract_urls( $content );

			// Not have any link.
			if ( empty( $all_links ) ) {
				return $content;
			}

			$link_mapping = array();
			$image_links  = array();
			$other_links  = array();

			// Extract normal and image links.
			foreach ( $all_links as $key => $link ) {
				if ( preg_match( '/^((https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?\/[\w\-]+\.(jpg|png|gif|jpeg)\/?$/i', $link ) ) {

					// Get all image links.
					// Avoid *-150x, *-300x and *-1024x images.
					if (
						false === strpos( $link, '-150x' ) &&
						false === strpos( $link, '-300x' ) &&
						false === strpos( $link, '-1024x' )
					) {
						$image_links[] = $link;
					}
				} else {

					// Collect other links.
					$other_links[] = $link;
				}
			}

			// Step 1: Download images.
			if ( ! empty( $image_links ) ) {
				foreach ( $image_links as $key => $image_url ) {
					// Download remote image.
					$image            = array(
						'url' => $image_url,
						'id'  => 0,
					);
					$downloaded_image = CartFlows_Import_Image::get_instance()->import( $image );

					// Old and New image mapping links.
					$link_mapping[ $image_url ] = $downloaded_image['url'];
				}
			}

			// Step 3: Replace mapping links.
			foreach ( $link_mapping as $old_url => $new_url ) {
				$content = str_replace( $old_url, $new_url, $content );

				// Replace the slashed URLs if any exist.
				$old_url = str_replace( '/', '/\\', $old_url );
				$new_url = str_replace( '/', '/\\', $new_url );
				$content = str_replace( $old_url, $new_url, $content );
			}

			return $content;
		}

		/**
		 * Imported notice
		 *
		 * @since 1.1.4
		 */
		public function imported_successfully() {
			?>
			<div class="notice notice-success">
				<p><?php esc_html_e( 'Successfully imported flows.', 'cartflows' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Clear Cache.
		 *
		 * @since 1.0.0
		 */
		public function clear_cache() {
			// Clear 'Elementor' file cache.
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
		}

		/**
		 * JS Templates
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function js_templates() {

			// Loading Templates.
			?>
			<script type="text/template" id="tmpl-cartflows-step-loading">
				<div class="template-message-block cartflows-step-loading">
					<h2>
						<span class="spinner"></span>
						<?php esc_html_e( 'Loading Steps', 'cartflows' ); ?>
					</h2>
					<p class="description"><?php esc_html_e( 'Getting steps from the cloud. Please wait for the moment.', 'cartflows' ); ?></p>
				</div>
			</script>

			<?php
			// Search Templates.
			?>
			<script type="text/template" id="tmpl-cartflows-searching-templates">
				<div class="template-message-block cartflows-searching-templates">
					<h2>
						<span class="spinner"></span>
						<?php esc_html_e( 'Searching Template..', 'cartflows' ); ?>
					</h2>
					<p class="description"><?php esc_html_e( 'Getting templates from the cloud. Please wait for the moment.', 'cartflows' ); ?></p>
				</div>
			</script>

			<?php
			// CartFlows Importing Template.
			?>
			<script type="text/template" id="tmpl-cartflows-step-importing">
				<div class="template-message-block cartflows-step-importing">
					<h2><span class="spinner"></span> <?php esc_html_e( 'Importing..', 'cartflows' ); ?></h2>
				</div>
			</script>

			<?php
			// CartFlows Imported.
			?>
			<script type="text/template" id="tmpl-cartflows-step-imported">
				<div class="template-message-block cartflows-step-imported">
					<h2><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Imported', 'cartflows' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Thanks for patience', 'cartflows' ); ?> <span class="dashicons dashicons-smiley"></span></p></div>
			</script>

			<?php
			// No templates.
			?>
			<script type="text/template" id="tmpl-cartflows-no-steps">
				<div class="cartflows-no-steps">
					<div class="template-message-block">
						<h2><?php esc_html_e( 'Coming Soon!', 'cartflows' ); ?></h2>
						<p class="description"></p>
					</div>
				</div>
			</script>

			<?php
			// No templates.
			?>
			<script type="text/template" id="tmpl-cartflows-no-flows">
				<div class="cartflows-no-flows">
					<div class="template-message-block">
						<h2><?php esc_html_e( 'Coming Soon!', 'cartflows' ); ?></h2>
						<p class="description"></p>
					</div>
				</div>
			</script>

			<?php
			// Error handling.
			?>
			<script type="text/template" id="tmpl-templator-error">
				<div class="notice notice-error"><p>{{ data }}</p></div>
			</script>

			<?php
			// Redirect to Elementor.
			?>
			<script type="text/template" id="tmpl-templator-redirect-to-elementor">
				<div class="template-message-block templator-redirect-to-elementor">
					<h2><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Imported', 'cartflows' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Thanks for patience', 'cartflows' ); ?> <span class="dashicons dashicons-smiley"></span><br/><br/><?php esc_html_e( 'Redirecting to the Elementor edit window.', 'cartflows' ); ?> </p></div>
			</script>

			<?php
			/**
			 * Responsive Buttons
			 */
			?>
			<script type="text/template" id="tmpl-cartflows-responsive-view">
				<span class="responsive-view">
					<span class="actions">
						<a class="desktop" href="#"><span data-view="desktop " class="active dashicons dashicons-desktop"></span></a>
						<a class="tablet" href="#"><span data-view="tablet" class="dashicons dashicons-tablet"></span></a>
						<a class="mobile" href="#"><span data-view="mobile" class="dashicons dashicons-smartphone"></span></a>
					</span>
				</span>
			</script>

			<?php
			// Templates data.
			?>
			<script type="text/template" id="tmpl-cartflows-flows-list">

				<# console.log( data.items.length ) #>
				<# console.log( data.items ) #>
				<# if ( data.items.length ) { #>
					<# for ( key in data.items ) { #>
					<#
						var flow_steps = [];
						if( data.items[ key ].flow_steps ) {
							flow_steps = data.items[ key ].flow_steps.map(function(value,index) {
								return value['id'];
							});
						}
						#>
						<div class="inner">
							<div class="template">
								<span class="thumbnail site-preview cartflows-preview-flow-steps" data-flow-steps="{{ JSON.stringify( data.items[ key ].flow_steps ) }}" data-title="{{ data.items[ key ].title.rendered }}">
									<div class="template-screenshot">
										<# if( data.items[ key ].featured_image_url ) { #>
											<img src="{{ data.items[ key ].featured_image_url }}" />
										<# } else { #>
											<img src="<?php echo esc_attr( CARTFLOWS_URL ); ?>assets/images/400x400.jpg" />
										<# } #>
									</div>
									<# if( data.items[ key ].flow_type && 'pro' === data.items[ key ].flow_type.slug ) { #>
										<span class="wcf-flow-type pro"><?php esc_html_e( 'Pro', 'cartflows' ); ?></span>
									<# } #>
									<# if( data.items[ key ].woo_required ) { #>
										<div class="notice notice-info" style="width: auto;">
											<p class="wcf-learn-how">
												Install/Activate WooCommerce to use this template.
												<a	href="https://cartflows.com/docs/cartflows-step-types/"	target="_blank"> 
													<strong><?php esc_html_e( 'Learn How', 'cartflows' ); ?></strong> 
													<i class="dashicons dashicons-external"></i>
												</a>
											</p>
										</div>
									<# } else { #>
											<a href="<?php echo CARTFLOWS_TEMPLATES_URL . 'preview/?'; ?>flow={{ data.items[ key ].id }}&title={{{ data.items[ key ].title.rendered }}}" class="preview" target="_blank">Preview <i class="dashicons dashicons-external"></i></a>
									<# } #>

								</span>
								<div class="template-id-container">
									<h3 class="template-name"> {{{ data.items[ key ].title.rendered }}} </h3>
									<div class="template-actions">

									<#
									if( data.items[ key ].page_builder.slug ) {
										required_plugin_group = data.items[ key ].page_builder.slug;
									} else {
										required_plugin_group = '';
									}

									if( data.items[ key ].page_builder.slug && CartFlowsImportVars.required_plugins[data.items[ key ].page_builder.slug] && CartFlowsImportVars.required_plugins[data.items[ key ].page_builder.slug].button_title ) {
										import_btn_title = CartFlowsImportVars.required_plugins[ data.items[ key ].page_builder.slug ].button_title;
									} else {
										import_btn_title = 'Import';
									} #>

									<# if( data.items[ key ].licence_status && 'valid' === data.items[ key ].licence_status ) { #>
										<# if( ! data.items[ key ].woo_required ) { #>
										<a data-flow-steps="{{ flow_steps }}" data-required-plugin-group="{{required_plugin_group}}" href="#" class="button button-primary cartflows-step-import" data-template-id="{{ data.items[ key ].id }}">{{ import_btn_title }}</a>
										<# } else { #>
										<a href='#' class='wcf-activate-wc button-primary'>Install & activate Woo</a>
										<# }  #>
									<# } else if( CartFlowsImportVars._is_pro_active ) { #>
										<a target="_blank" href="<?php echo esc_url( admin_url( 'plugins.php?cartflows-license-popup' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Activate License', 'cartflows' ); ?></a>
									<# } else { #>
										<a target="_blank" href="<?php echo esc_url( CARTFLOWS_DOMAIN_URL ); ?>" class="button button-primary"><?php esc_html_e( 'Get Pro', 'cartflows' ); ?></a>
									<# } #>
									</div>
								</div>
							</div>
						</div>
					<# } #>
				<# } #>
			</script>

			<?php
			// Empty Step.
			?>
			<script type="text/template" id="tmpl-cartflows-create-blank-step">
				<div class="inner">
					<div class="template">
						<span class="thumbnail site-preview cartflows-flow-preview">
							<div class="template-screenshot">
								<img src="<?php echo esc_attr( CARTFLOWS_URL ); ?>assets/images/start-scratch.jpg" />
							</div>
							<div id="wcf_create_notice" class=""><a href="https://cartflows.com/" target="_blank"></a></div>
						</span>
						<div class="template-id-container">
							<h3 class="template-name"> Blank </h3>
							<div class="template-actions">
								<a href="#" class="button button-primary cartflows-step-import-blank"><?php esc_html_e( 'Create', 'cartflows' ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</script>

			<?php
			// Templates data.
			?>
			<script type="text/template" id="tmpl-cartflows-steps-list">
				<# if ( data.items.length ) { #>
					<# for ( key in data.items ) { #>
					<#
						var flow_steps = [];
						if( data.items[ key ].flow_steps ) {
							flow_steps = data.items[ key ].flow_steps.map(function(value,index) {
								return value['id'];
							});
						}
						#>
						<div class="inner">
							<div class="template">
								<span class="thumbnail site-preview cartflows-preview-flow-steps" data-flow-steps="{{ JSON.stringify( data.items[ key ].flow_steps ) }}" data-title="{{ data.items[ key ].title.rendered }}">
									<div class="template-screenshot">
										<# if( data.items[ key ].featured_image_url ) { #>
											<img src="{{ data.items[ key ].featured_image_url }}" />
										<# } else { #>
											<img src="<?php echo esc_attr( CARTFLOWS_URL ); ?>assets/images/400x400.jpg" />
										<# } #>
									</div>
									<div id="wcf_create_notice" class=""><a href="https://cartflows.com/" target="_blank"></a></div>

									<# if( data.items[ key ].woo_required ) { #>
										<div class="notice notice-info" style="width: auto;">
											<p class="wcf-learn-how">
												Install/Activate WooCommerce to use this template.
												<a href="https://cartflows.com/docs/cartflows-step-types/" target="_blank"> 
													<strong><?php esc_html_e( 'Learn How', 'cartflows' ); ?></strong> 
													<i class="dashicons dashicons-external"></i>
												</a>
											</p>
										</div>
									<# } else { #>
											<a href="<?php echo CARTFLOWS_TEMPLATES_URL . 'preview/?'; ?>step={{ data.items[ key ].id }}&title={{{ data.items[ key ].title.rendered }}}" class="preview" target="_blank">Preview <i class="dashicons dashicons-external"></i></a>
									<# } #>

									<# if( data.items[ key ].flow_type && 'pro' === data.items[ key ].flow_type.slug ) { #>
										<span class="wcf-flow-type pro"><?php esc_html_e( 'Pro', 'cartflows' ); ?></span>
									<# } #>
								</span>
								<div class="template-id-container">
									<h3 class="template-name"> {{{ data.items[ key ].title.rendered }}} </h3>
									<div class="template-actions">

									<#

									var step_slug        = data.items[ key ].step_type.slug || '';
									var step_title       = data.items[ key ].step_type.name || '';
									var import_btn_title = 'Import';

									var required_plugin_group = '';
									if( data.items[ key ].page_builder ) {
										required_plugin_group = data.items[ key ].page_builder.slug;

										if( data.items[ key ].page_builder.slug && CartFlowsImportVars.required_plugins[data.items[ key ].page_builder.slug] && CartFlowsImportVars.required_plugins[data.items[ key ].page_builder.slug].button_title ) {
											import_btn_title = CartFlowsImportVars.required_plugins[ data.items[ key ].page_builder.slug ].button_title;
										}
									}
									#>

									<# if( data.items[ key ].licence_status && 'valid' === data.items[ key ].licence_status ) { #>

										<# if( ! data.items[ key ].woo_required ) { #>
										<a data-slug="{{step_slug}}" data-title="{{step_title}}" data-flow-steps="{{ flow_steps }}" data-required-plugin-group="{{required_plugin_group}}" href="#" class="button button-primary cartflows-step-import" data-template-id="{{ data.items[ key ].id }}">{{ import_btn_title }}</a>
										<# } else { #>
										<a href='#' class='wcf-activate-wc button-primary'>Install & activate Woo</a>
										<# }  #>

									<# } else if( CartFlowsImportVars._is_pro_active ) { #>
										<a target="_blank" href="<?php echo esc_url( admin_url( 'plugins.php?cartflows-license-popup' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Activate License', 'cartflows' ); ?></a>
									<# } else { #>
										<a target="_blank" href="<?php echo esc_url( CARTFLOWS_DOMAIN_URL ); ?>" class="button button-primary"><?php esc_html_e( 'Get Pro', 'cartflows' ); ?></a>
									<# } #>
									</div>
								</div>
							</div>
						</div>
					<# } #>
				<# } #>
			</script>

			<?php
			/**
			 * TMPL - Website Unreachable
			 */
			?>
			<script type="text/template" id="tmpl-cartflows-website-unreachable">
				<div class="postbox cartflows-website-unreachable">
					<h2><?php esc_html_e( 'Under Maintenance..', 'cartflows' ); ?></h2>
					<p><?php esc_html_e( 'If you are seeing this message, most likely our servers are under routine maintenance and we will be back shortly.', 'cartflows' ); ?></p>
					<p><?php esc_html_e( 'In rare case, it is possible your website is having trouble connecting with ours. If you need help, please feel free to get in touch with us from our website..', 'cartflows' ); ?></p>
				</div>
			</script>

			<?php
			/**
			 * TMPL - Filters
			 */
			?>
			<script type="text/template" id="tmpl-cartflows-page-builder-notice">
				<?php
				$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );
				$page_builder         = Cartflows_Helper::get_required_plugins_for_page_builder( Cartflows_Helper::get_common_setting( 'default_page_builder' ) );
				$title                = $page_builder['title'];

				$plugin_string = '<a href="#" data-slug="' . esc_html( $default_page_builder ) . '" class="wcf-install-plugin">Please click here and activate ' . esc_html( $title ) . '</a>';
				$theme_status  = '';
				if ( 'divi' === $default_page_builder ) {

					$theme_status  = $page_builder['theme-status'];
					$plugin_status = $page_builder['plugin-status'];

					if ( 'deactivate' === $theme_status || 'install' === $plugin_status ) {
						$plugin_string = 'Please activate ' . esc_html( $title );
					} elseif ( ( 'deactivate' === $theme_status || 'not-installed' === $theme_status ) && 'install' === $plugin_status ) {
						$plugin_string = 'Please install and activate ' . esc_html( $title );
					}
				}
				?>
				<div class="wcf-page-builder-message">
					<p><?php /* translators: %s: Plugin string */ printf( __( '%1$s to see CartFlows templates. If you prefer another page builder tool, you can <a href="%2$s" target="blank">select it here</a>.', 'cartflows' ), $plugin_string, admin_url( 'admin.php?page=' . CARTFLOWS_SETTINGS . '&action=common-settings#form-field-wcf_default_page_builder' ) ); ?></p>
					<p>If your preferred page builder is not available, feel free to <a href="#" data-slug="canvas" class="wcf-create-from-scratch-link">create your own</a> pages using page builder of your choice as CartFlows works with all major page builders.</p>
					<p>We plan to add design templates made with more page builder shortly!</p>
				</div>
			</script>

			<?php
			/**
			 * TMPL - Filters
			 */
			?>
			<script type="text/template" id="tmpl-cartflows-term-filters-dropdown">
				<# if ( data ) { #>
					<select class="{{ data.args.wrapper_class }} {{ data.args.class }}">
						<# if ( data.args.show_all ) { #>
							<option value="all"> <?php esc_html_e( 'All', 'cartflows' ); ?> </option>
						<# } #>
						<# if ( CartFlowsImportVars.step_type === data.args.remote_slug ) { #>
							<option value=""> <?php esc_html_e( 'Select Step Type', 'cartflows' ); ?> </option>
						<# } #>
						<# var step_slug_data = []; #>
						<# for ( key in data.items ) { #>
							<option value='{{ data.items[ key ].id }}' data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}" data-slug="{{ data.items[ key ].slug }}" data-title="{{ data.items[ key ].name }}">{{ data.items[ key ].name }}</option>

							<# step_slug_data.push( data.items[ key ].slug ); #>

						<# } #>
						<# if( step_slug_data.indexOf("optin") === -1){ #>
							<option value='0' data-group='0' class="Optin (Woo)" data-slug="optin" data-title="Optin (Woo)">Optin (Woo)</option>
						<# } #>
					</select>
				<# } #>
			</script>

			<script type="text/template" id="tmpl-cartflows-term-filters">

				<# if ( data ) { #>

					<?php /* <# if ( CartFlowsImportVars.flow_page_builder === data.args.remote_slug || CartFlowsImportVars.step_page_builder === data.args.remote_slug ) { #> */ ?>
						<ul class="{{ data.args.wrapper_class }} {{ data.args.class }}">

							<# if ( data.args.show_all ) { #>
								<li>
									<a href="#" data-group="all"> All </a>
								</li>
							<# } #>

							<# for ( key in data.items ) { #>
								<li>
									<a href="#" data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}" data-slug="{{ data.items[ key ].slug }}" data-title="{{ data.items[ key ].name }}">{{ data.items[ key ].name }}</a>
								</li>
							<# } #>

						</ul>

						<?php

						/**
						<# } else { #>
							<select class="{{ data.args.wrapper_class }} {{ data.args.class }}">

								<# if ( data.args.show_all ) { #>
									<option value="all"> <?php _e( 'All', 'cartflows' ); ?> </option>
								<# } #>

								<# if ( CartFlowsImportVars.step_type === data.args.remote_slug ) { #>
									<option value=""> <?php _e( 'Select Step Type', 'cartflows' ); ?> </option>
								<# } #>

								<# for ( key in data.items ) { #>
									<option value='{{ data.items[ key ].id }}' data-group='{{ data.items[ key ].id }}' class="{{ data.items[ key ].name }}" data-slug="{{ data.items[ key ].slug }}" data-title="{{ data.items[ key ].name }}">{{ data.items[ key ].name }}</option>
								<# } #>

							</select>
						 */
						?>

					<?php /* <# } #> */ ?>

				<# } #>
			</script>

			<?php
			// Step Type.
			?>
			<script type="text/template" id="tmpl-cartflows-step-types">
				<ul class="wcf-tab nav-tabs">
					<# if( data.items_count ) { #>
						<# for( key in data.items ) { #>
							<# console.log( data.items[ key ].id ) #>
							<li data-slug="{{data.items[ key ].slug}}" data-title="{{ data.items[ key ].name }}">
								<a href="#{{{ data.items[ key ].slug }}}">{{{ data.items[ key ].name }}}</a>
							</li>
						<# } #>
					<# } #>
				</ul>
			</script>

			<?php
			// Add to library button.
			?>
			<script type="text/template" id="tmpl-templator-add-to-library">
				<a class="templator-add-to-library page-title-action cartflows-load-steps-library"><i class="dashicons dashicons-cloud"></i><?php esc_attr_e( 'Import from Cloud', 'cartflows' ); ?></a>
			</script>
			<?php
		}

		/**
		 * Enqueue scripts
		 *
		 * @since 1.0.0
		 *
		 * @hook admin_enqueue_scripts
		 * @param  string $hook Current page hook.
		 */
		public function scripts( $hook = '' ) {

			if ( ! self::is_supported_post( get_current_screen()->post_type ) ) {
				return;
			}

			wp_enqueue_script( 'cartflows-rest-api', CARTFLOWS_URL . 'assets/js/rest-api.js', array( 'jquery' ), CARTFLOWS_VER, true );
			wp_enqueue_style( 'cartflows-import', CARTFLOWS_URL . 'assets/css/import.css', null, CARTFLOWS_VER, 'all' );
			wp_style_add_data( 'cartflows-import', 'rtl', 'replace' );
			wp_enqueue_script( 'cartflows-import', CARTFLOWS_URL . 'assets/js/import.js', array( 'jquery', 'wp-util', 'cartflows-rest-api', 'updates' ), CARTFLOWS_VER, true );

			$installed_plugins = get_plugins();
			$is_wc_installed   = isset( $installed_plugins['woocommerce/woocommerce.php'] ) ? 'yes' : 'no';
			$is_wc_activated   = wcf()->is_woo_active ? 'yes' : 'no';

			$localize_vars = array(
				'_is_pro_active'           => _is_cartflows_pro(),
				'is_wc_installed'          => $is_wc_installed,
				'is_wc_activated'          => $is_wc_activated,

				// Flow and its rest fields.
				'flow'                     => CARTFLOWS_FLOW_POST_TYPE,
				'flow_fields'              => array(
					'id',
					'title',
					'flow_type',
					'page_builder',
					'flow_steps',
					'licence_status',
					'featured_image_url',
					'featured_media', // @required for field `featured_image_url`.
				),

				// Flow type and rest fields.
				'flow_type'                => CARTFLOWS_TAXONOMY_FLOW_CATEGORY,
				'flow_type_fields'         => array(
					'id',
					'name',
					'slug',
				),

				// Flow page builder and rest fields.
				'flow_page_builder'        => CARTFLOWS_TAXONOMY_FLOW_PAGE_BUILDER,
				'flow_page_builder_fields' => array(
					'id',
					'name',
					'slug',
				),

				// Step page builder and rest fields.
				'step_page_builder'        => CARTFLOWS_TAXONOMY_STEP_PAGE_BUILDER,
				'step_page_builder_fields' => array(
					'id',
					'name',
					'slug',
				),

				// Step and its rest fields.
				'step'                     => CARTFLOWS_STEP_POST_TYPE,
				'step_fields'              => array(
					'title',
					'featured_image_url',
					'featured_media', // @required for field `featured_image_url`.
					'id',
					'flow_type',
					'step_type',
					'page_builder',
					'licence_status',
				),

				// Step type and its rest fields.
				'step_type'                => CARTFLOWS_TAXONOMY_STEP_TYPE,
				'step_type_fields'         => array(
					'id',
					'name',
					'slug',
				),

				'domain_url'               => CARTFLOWS_DOMAIN_URL,
				'server_url'               => CARTFLOWS_TEMPLATES_URL,
				'server_rest_url'          => CARTFLOWS_TEMPLATES_URL . 'wp-json/wp/v2/',
				'site_url'                 => site_url(),
				'import_url'               => admin_url( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE . '&page=flow_importer' ),
				'export_url'               => admin_url( 'edit.php?post_type=' . CARTFLOWS_FLOW_POST_TYPE . '&page=flow_exporter' ),
				'admin_url'                => admin_url(),
				'licence_args'             => CartFlows_API::get_instance()->get_licence_args(),
				'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
				'debug'                    => ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || isset( $_GET['debug'] ) ) ? true : false, //phpcs:ignore

				'required_plugins'         => Cartflows_Helper::get_plugins_groupby_page_builders(),

				'default_page_builder'     => Cartflows_Helper::get_common_setting( 'default_page_builder' ),
			);

			$localize_vars['cartflows_activate_plugin_nonce'] = wp_create_nonce( 'cartflows_activate_plugin' );

			// var_dump(Cartflows_Helper::get_common_setting( 'default_page_builder' ));
			// wp_die(  );
			// Add thickbox.
			add_thickbox();

			wp_localize_script( 'cartflows-import', 'CartFlowsImportVars', $localize_vars );
			wp_localize_script( 'cartflows-rest-api', 'CartFlowsImportVars', $localize_vars );
		}

		/**
		 * Import.
		 *
		 * @since 1.0.0
		 *
		 * @hook wp_ajax_cartflows_import_flow_step
		 * @return void
		 */
		public function import_flow() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			check_ajax_referer( 'cf-import-flow-step', 'security' );

			$flow_id     = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : '';
			$template_id = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : '';

			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( 'STARTED! Importing FLOW' );
			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( '(✓) Creating new step from remote step [' . $template_id . '] for FLOW ' . get_the_title( $flow_id ) . ' [' . $flow_id . ']' );

			$response = CartFlows_API::get_instance()->get_template( $template_id );

			$post_content = isset( $response['data']['content']->rendered ) ? $response['data']['content']->rendered : '';
			if ( 'divi' === Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
				if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {
					$post_content = $response['data']['divi_content'];
				}
			}

			if ( 'gutenberg' === Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
				if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {
					$post_content = $response['data']['divi_content'];
				}
			}

			if ( false === $response['success'] ) {
				wcf()->logger->import_log( '(✕) Failed to fetch remote data.' );
				wp_send_json_error( $response );
			}

			wcf()->logger->import_log( '(✓) Successfully getting remote step response ' . wp_json_encode( $response ) );

			$new_step_id = wp_insert_post(
				array(
					'post_type'    => CARTFLOWS_STEP_POST_TYPE,
					'post_title'   => $response['title'],
					'post_content' => $post_content,
					'post_status'  => 'publish',
				)
			);

			if ( is_wp_error( $new_step_id ) ) {
				wcf()->logger->import_log( '(✕) Failed to create new step for flow ' . $flow_id );
				wp_send_json_error( $new_step_id );
			}

			if ( 'divi' === Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
				if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {
					update_post_meta( $new_step_id, 'divi_content', $response['data']['divi_content'] );
				}
			}

			/* Imported Step */
			update_post_meta( $new_step_id, 'cartflows_imported_step', 'yes' );

			wcf()->logger->import_log( '(✓) Created new step ' . '"' . $response['title'] . '" id ' . $new_step_id );//phpcs:ignore
			// insert post meta.
			update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );
			wcf()->logger->import_log( '(✓) Added flow ID ' . $flow_id . ' in post meta key wcf-flow-id.' );

			/**
			 * Import & Set type.
			 */
			$term = isset( $response['data']['step_type'] ) ? $response['data']['step_type'] : '';

			$term_slug = '';
			if ( $term ) {

				$taxonomy   = CARTFLOWS_TAXONOMY_STEP_TYPE;
				$term_exist = term_exists( $term->slug, $taxonomy );

				if ( empty( $term_exist ) ) {
					$terms = array(
						array(
							'name' => $term->name,
							'slug' => $term->slug,
						),
					);

					Cartflows_Step_Post_Type::get_instance()->add_terms( $taxonomy, $terms );
					wcf()->logger->import_log( '(✓) Created new term name ' . $term->name . ' | term slug ' . $term->slug );
				}

				$current_term = term_exists( $term->slug, $taxonomy );

				// Set type object.
				$data      = get_term( $current_term['term_id'], $taxonomy );
				$term_slug = $data->slug;
				$term_name = $data->name;
				wp_set_object_terms( $new_step_id, $term_slug, CARTFLOWS_TAXONOMY_STEP_TYPE );
				wcf()->logger->import_log( '(✓) Assigned existing term ' . $term_name . ' to the template ' . $new_step_id );

				// Set type.
				update_post_meta( $new_step_id, 'wcf-step-type', $term_slug );
				wcf()->logger->import_log( '(✓) Updated term ' . $term_name . ' to the post meta wcf-step-type.' );
			}

			// Set flow.
			wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );
			wcf()->logger->import_log( '(✓) Assigned flow step flow-' . $flow_id );

			/**
			 * Update steps for the current flow.
			 */
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $response['title'],
				'type'  => $term_slug,
			);
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );
			wcf()->logger->import_log( '(✓) Updated flow steps post meta key \'wcf-steps\' ' . wp_json_encode( $flow_steps ) );

			// Import Post Meta.
			self::import_post_meta( $new_step_id, $response );

			wcf()->logger->import_log( '(✓) Importing step "' . get_the_title( $new_step_id ) . '" [' . $new_step_id . '] for FLOW "' . get_the_title( $flow_id ) . '" [' . $flow_id . ']' );
			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( 'COMPLETE! Importing FLOW' );
			wcf()->logger->import_log( '------------------------------------' );

			do_action( 'cartflows_import_complete' );
			wcf()->logger->import_log( '(✓) BATCH STARTED for step ' . $new_step_id . ' for Blog name \'' . get_bloginfo( 'name' ) . '\' (' . get_current_blog_id() . ')' );

			// Batch Process.
			do_action( 'cartflows_after_template_import', $new_step_id, $response );

			/**
			 * End
			 */
			wp_send_json_success( $new_step_id );
		}

		/**
		 * Import Step.
		 *
		 * @since 1.0.0
		 * @hook wp_ajax_cartflows_step_import
		 *
		 * @return void
		 */
		public function create_default_flow() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			check_ajax_referer( 'cf-default-flow', 'security' );

			// Create post object.
			$new_flow_post = array(
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
			);

			// Insert the post into the database.
			$flow_id = wp_insert_post( $new_flow_post );

			if ( is_wp_error( $flow_id ) ) {
				wp_send_json_error( $flow_id->get_error_message() );
			}

			$flow_steps = array();

			if ( wcf()->is_woo_active ) {
				$steps_data = array(
					'sales'              => array(
						'title' => __( 'Sales Landing', 'cartflows' ),
						'type'  => 'landing',
					),
					'order-form'         => array(
						'title' => __( 'Checkout (Woo)', 'cartflows' ),
						'type'  => 'checkout',
					),
					'order-confirmation' => array(
						'title' => __( 'Thank You (Woo)', 'cartflows' ),
						'type'  => 'thankyou',
					),
				);

			} else {
				$steps_data = array(
					'landing'  => array(
						'title' => __( 'Landing', 'cartflows' ),
						'type'  => 'landing',
					),
					'thankyou' => array(
						'title' => __( 'Thank You', 'cartflows' ),
						'type'  => 'landing',
					),
				);
			}

			foreach ( $steps_data as $slug => $data ) {

				$post_content = '';
				$step_type    = trim( $data['type'] );

				$step_id = wp_insert_post(
					array(
						'post_type'    => CARTFLOWS_STEP_POST_TYPE,
						'post_title'   => $data['title'],
						'post_content' => $post_content,
						'post_status'  => 'publish',
					)
				);

				if ( is_wp_error( $step_id ) ) {
					wp_send_json_error( $step_id->get_error_message() );
				}

				if ( $step_id ) {

					$flow_steps[] = array(
						'id'    => $step_id,
						'title' => $data['title'],
						'type'  => $step_type,
					);

					// insert post meta.
					update_post_meta( $step_id, 'wcf-flow-id', $flow_id );
					update_post_meta( $step_id, 'wcf-step-type', $step_type );

					wp_set_object_terms( $step_id, $step_type, CARTFLOWS_TAXONOMY_STEP_TYPE );
					wp_set_object_terms( $step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );

					update_post_meta( $step_id, '_wp_page_template', 'cartflows-default' );
				}
			}

			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );

			wp_send_json_success( $flow_id );
		}

		/**
		 * Create Flow
		 *
		 * @return void
		 */
		public function create_flow() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			check_ajax_referer( 'cf-create-flow', 'security' );

			// Create post object.
			$new_flow_post = array(
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => CARTFLOWS_FLOW_POST_TYPE,
			);

			// Insert the post into the database.
			$flow_id = wp_insert_post( $new_flow_post );

			if ( is_wp_error( $flow_id ) ) {
				wp_send_json_error( $flow_id->get_error_message() );
			}

			/* Imported Flow */
			update_post_meta( $flow_id, 'cartflows_imported_flow', 'yes' );

			wp_send_json_success( $flow_id );
		}

		/**
		 * Create Step
		 *
		 * @return void
		 */
		public function import_step() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			check_ajax_referer( 'cf-step-import', 'security' );

			$template_id       = isset( $_POST['template_id'] ) ? intval( $_POST['template_id'] ) : '';
			$flow_id           = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : '';
			$step_title        = isset( $_POST['step_title'] ) ? sanitize_text_field( wp_unslash( $_POST['step_title'] ) ) : '';
			$step_type         = isset( $_POST['step_type'] ) ? sanitize_title( wp_unslash( $_POST['step_type'] ) ) : '';
			$step_custom_title = isset( $_POST['step_custom_title'] ) ? sanitize_title( wp_unslash( $_POST['step_custom_title'] ) ) : $step_title;

			$cartflow_meta = Cartflows_Flow_Meta::get_instance();

			$post_id = $cartflow_meta->create_step( $flow_id, $step_type, $step_custom_title );

			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( 'STARTED! Importing STEP' );
			wcf()->logger->import_log( '------------------------------------' );

			if ( empty( $template_id ) || empty( $post_id ) ) {
				/* translators: %s: template ID */
				$data = sprintf( __( 'Invalid template id %1$s or post id %2$s.', 'cartflows' ), $template_id, $post_id );
				wcf()->logger->import_log( $data );
				wp_send_json_error( $data );
			}

			wcf()->logger->import_log( 'Remote Step ' . $template_id . ' for local flow "' . get_the_title( $post_id ) . '" [' . $post_id . ']' );

			$response = CartFlows_API::get_instance()->get_template( $template_id );

			if ( 'divi' === Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
				if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {

					update_post_meta( $post_id, 'divi_content', $response['data']['divi_content'] );

					wp_update_post(
						array(
							'ID'           => $post_id,
							'post_content' => $response['data']['divi_content'],
						)
					);
				}
			}

			if ( 'gutenberg' === Cartflows_Helper::get_common_setting( 'default_page_builder' ) ) {
				if ( isset( $response['data']['divi_content'] ) && ! empty( $response['data']['divi_content'] ) ) {

					wp_update_post(
						array(
							'ID'           => $post_id,
							'post_content' => $response['data']['divi_content'],
						)
					);
				}
			}

			/* Imported Step */
			update_post_meta( $post_id, 'cartflows_imported_step', 'yes' );

			// Import Post Meta.
			self::import_post_meta( $post_id, $response );

			do_action( 'cartflows_import_complete' );

			// Batch Process.
			do_action( 'cartflows_after_template_import', $post_id, $response );

			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( 'COMPLETE! Importing Step' );
			wcf()->logger->import_log( '------------------------------------' );

			wp_send_json_success( $post_id );
		}

		/**
		 * Import Step.
		 *
		 * @since 1.0.0
		 * @hook wp_ajax_cartflows_step_create_blank
		 *
		 * @return void
		 */
		public function step_create_blank() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			check_ajax_referer( 'cf-step-create-blank', 'security' );

			$flow_id    = isset( $_POST['flow_id'] ) ? intval( $_POST['flow_id'] ) : '';
			$step_type  = isset( $_POST['step_type'] ) ? sanitize_text_field( wp_unslash( $_POST['step_type'] ) ) : '';
			$step_title = isset( $_POST['step_title'] ) ? sanitize_text_field( wp_unslash( $_POST['step_title'] ) ) : '';

			if ( empty( $flow_id ) || empty( $step_type ) ) {
				/* translators: %s: flow ID */
				$data = sprintf( __( 'Invalid flow id %1$s OR step type %2$s.', 'cartflows' ), $flow_id, $step_type );
				wcf()->logger->import_log( $data );
				wp_send_json_error( $data );
			}

			wcf()->logger->import_log( '------------------------------------' );
			wcf()->logger->import_log( 'STARTED! Creating Blank STEP for Flow ' . $flow_id );

			$step_type_title = str_replace( '-', ' ', $step_type );
			$step_type_slug  = strtolower( str_replace( '-', ' ', $step_type ) );

			$new_step_id = wp_insert_post(
				array(
					'post_type'    => CARTFLOWS_STEP_POST_TYPE,
					'post_title'   => $step_title,
					'post_content' => '',
					'post_status'  => 'publish',
				)
			);

			// insert post meta.
			update_post_meta( $new_step_id, 'wcf-flow-id', $flow_id );

			$taxonomy   = CARTFLOWS_TAXONOMY_STEP_TYPE;
			$term_exist = term_exists( $step_type_slug, $taxonomy );

			if ( empty( $term_exist ) ) {
				$terms = array(
					array(
						'name' => $step_type_title,
						'slug' => $step_type_slug,
					),
				);

				Cartflows_Step_Post_Type::get_instance()->add_terms( $taxonomy, $terms );
				wcf()->logger->import_log( '(✓) Created new term name ' . $step_type_title . ' | term slug ' . $step_type_slug );
			}

			$current_term = term_exists( $step_type_slug, $taxonomy );

			// Set type object.
			$data      = get_term( $current_term['term_id'], $taxonomy );
			$step_slug = $data->slug;
			wp_set_object_terms( $new_step_id, $data->slug, CARTFLOWS_TAXONOMY_STEP_TYPE );
			wcf()->logger->import_log( '(✓) Assigned existing term ' . $step_type_title . ' to the template ' . $new_step_id );

			// Set Default page Layout.
			update_post_meta( $new_step_id, '_wp_page_template', 'cartflows-default' );

			// Set type.
			update_post_meta( $new_step_id, 'wcf-step-type', $data->slug );
			wcf()->logger->import_log( '(✓) Updated term ' . $data->name . ' to the post meta wcf-step-type.' );

			// Set flow.
			wp_set_object_terms( $new_step_id, 'flow-' . $flow_id, CARTFLOWS_TAXONOMY_STEP_FLOW );
			wcf()->logger->import_log( '(✓) Assigned flow step flow-' . $flow_id );

			self::get_instance()->set_step_to_flow( $flow_id, $new_step_id, $step_type_title, $step_slug );

			wcf()->logger->import_log( 'COMPLETE! Creating Blank STEP for Flow ' . $flow_id );
			wcf()->logger->import_log( '------------------------------------' );

			wp_send_json_success( $new_step_id );
		}

		/**
		 * Import Post Meta
		 *
		 * @since 1.0.0
		 *
		 * @param  integer $post_id  Post ID.
		 * @param  array   $response  Post meta.
		 * @return void
		 */
		public static function import_post_meta( $post_id, $response ) {

			$metadata = (array) $response['post_meta'];

			foreach ( $metadata as $meta_key => $meta_value ) {
				$meta_value = isset( $meta_value[0] ) ? $meta_value[0] : '';

				if ( $meta_value ) {

					if ( is_serialized( $meta_value, true ) ) {
						$raw_data = maybe_unserialize( stripslashes( $meta_value ) );
					} elseif ( is_array( $meta_value ) ) {
						$raw_data = json_decode( stripslashes( $meta_value ), true );
					} else {
						$raw_data = $meta_value;
					}

					if ( '_elementor_data' === $meta_key ) {
						if ( is_array( $raw_data ) ) {
							$raw_data = wp_slash( wp_json_encode( $raw_data ) );
						} else {
							$raw_data = wp_slash( $raw_data );
						}
					}
					if ( '_elementor_data' !== $meta_key && '_elementor_draft' !== $meta_key && '_fl_builder_data' !== $meta_key && '_fl_builder_draft' !== $meta_key ) {
						if ( is_array( $raw_data ) ) {
							wcf()->logger->import_log( '(✓) Added post meta ' . $meta_key . ' | ' . wp_json_encode( $raw_data ) );
						} else {
							if ( ! is_object( $raw_data ) ) {
								wcf()->logger->import_log( '(✓) Added post meta ' . $meta_key . ' | ' . $raw_data );
							}
						}
					}

					update_post_meta( $post_id, $meta_key, $raw_data );
				}
			}
		}

		/**
		 * Import Template for Elementor
		 *
		 * @since 1.0.0
		 *
		 * @param  integer $post_id  Post ID.
		 * @param  array   $response  Post meta.
		 * @param  array   $page_build_data  Page build data.
		 * @return void
		 */
		public static function import_template_elementor( $post_id, $response, $page_build_data ) {
			if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
				$data = __( 'Elementor is not activated. Please activate plugin Elementor Page Builder to import the step.', 'cartflows' );
				wcf()->logger->import_log( $data );
				wp_send_json_error( $data );
			}

			require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-elementor.php';

			wcf()->logger->import_log( '# Started "importing page builder data" for step ' . $post_id );

			$obj = new \Elementor\TemplateLibrary\CartFlows_Importer_Elementor();
			$obj->import_single_template( $post_id );

			wcf()->logger->import_log( '# Complete "importing page builder data" for step ' . $post_id );
		}

		/**
		 * Supported post types
		 *
		 * @since 1.0.0
		 *
		 * @return array Supported post types.
		 */
		public static function supported_post_types() {
			return apply_filters(
				'cartflows_supported_post_types',
				array(
					CARTFLOWS_FLOW_POST_TYPE,
				)
			);
		}

		/**
		 * Check supported post type
		 *
		 * @since 1.0.0
		 *
		 * @param  string $post_type Post type.
		 * @return boolean Supported post type status.
		 */
		public static function is_supported_post( $post_type = '' ) {

			if ( in_array( $post_type, self::supported_post_types(), true ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Set steps to the flow
		 *
		 * @param integer $flow_id     Flow ID.
		 * @param integer $new_step_id New step ID.
		 * @param string  $step_title    Flow Type.
		 * @param string  $step_slug Flow Type.
		 */
		public function set_step_to_flow( $flow_id, $new_step_id, $step_title, $step_slug ) {
			// Update steps for the current flow.
			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );

			if ( ! is_array( $flow_steps ) ) {
				$flow_steps = array();
			}

			$flow_steps[] = array(
				'id'    => $new_step_id,
				'title' => $step_title,
				'type'  => $step_slug,
			);
			update_post_meta( $flow_id, 'wcf-steps', $flow_steps );
			wcf()->logger->import_log( '(✓) Updated flow steps post meta key \'wcf-steps\' ' . wp_json_encode( $flow_steps ) );
		}

		/**
		 * Localize variables in admin
		 *
		 * @param array $vars variables.
		 */
		public function localize_vars( $vars ) {

			$ajax_actions = array(
				'cf_step_import',
				'cf_load_steps',
				'cf_create_flow',
				'cf_default_flow',
				'cf_step_create_blank',
				'cf_import_flow_step',
			);

			foreach ( $ajax_actions as $action ) {

				$vars[ $action . '_nonce' ] = wp_create_nonce( str_replace( '_', '-', $action ) );
			}

			return $vars;
		}

		/**
		 * Ajax action to activate plugin
		 */
		public function activate_plugin() {

			if ( ! check_ajax_referer( 'cartflows_activate_plugin', 'security', false ) ) {
				wp_send_json_error( esc_html__( 'Action failed. Invalid Security Nonce.', 'cartflows' ) );
			}

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'User have not plugin install permissions.', 'cartflows' ),
					)
				);
			}

			$plugin_init = isset( $_POST['plugin_init'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_init'] ) ) : '';

			$activate = activate_plugin( $plugin_init, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
						'init'    => $plugin_init,
					)
				);
			}

			wp_send_json_success(
				array(
					'success' => true,
					'message' => __( 'Plugin Successfully Activated', 'cartflows' ),
					'init'    => $plugin_init,
				)
			);
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer::get_instance();

endif;
