<?php
/**
 * General settings
 *
 * @package CartFlows
 */

$settings = Cartflows_Helper::get_common_settings();

$debug_data = Cartflows_Helper::get_debug_settings();

$permalink_settings = Cartflows_Helper::get_permalink_settings();

$google_analytics_settings = Cartflows_Helper::get_google_analytics_settings();

$facebook_settings = Cartflows_Helper::get_facebook_settings();

$debug_on = ( isset( $_GET['debug'] ) ) ? sanitize_text_field( wp_unslash( $_GET['debug'] ) ) : 'false'; //phpcs:ignore

$error_log = filter_input( INPUT_GET, 'cartflows-error-log', FILTER_VALIDATE_BOOLEAN );
?>


<?php if ( $error_log ) : ?>
	<div class="wrap wcf-addon-wrap wcf-clear wcf-container">
		<?php Cartflows_Logger::status_logs_file(); ?>
	</div>
<?php else : ?>
<div class="wrap wcf-addon-wrap wcf-clear wcf-container">
	<input type="hidden" name="action" value="wcf_save_common_settings">
	<h1 class="screen-reader-text"><?php esc_html_e( 'General Settings', 'cartflows' ); ?></h1>

	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">

				<!-- Getting Started -->
				<div class="postbox introduction">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php esc_html_e( 'Getting Started', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<div class="iframe-wrap">
							<iframe width="560" height="315" src="https://www.youtube.com/embed/SlE0moPKjMY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
						<p>
						<?php
							esc_attr_e( 'Modernizing WordPress eCommerce!', 'cartflows' );
						?>
						</p>
					</div>
				</div>
				<!-- Getting Started -->

				<!-- General Settings -->
				<div class="general-settings-form postbox">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php esc_html_e( 'General Settings', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<form method="post" class="wrap wcf-clear" action="" >
							<div class="form-wrap">
								<?php

								do_action( 'cartflows_before_settings_fields', $settings );

								echo Cartflows_Admin_Fields::checkobox_field(
									array(
										'id'    => 'wcf_disallow_indexing',
										'name'  => '_cartflows_common[disallow_indexing]',
										'title' => __( 'Disallow search engines from indexing flows', 'cartflows' ),
										'value' => $settings['disallow_indexing'],
									)
								);

								if ( wcf()->is_woo_active ) {
									echo Cartflows_Admin_Fields::flow_checkout_selection_field(
										array(
											'id'    => 'wcf_global_checkout',
											'name'  => '_cartflows_common[global_checkout]',
											'title' => __( 'Global Checkout', 'cartflows' ),
											'value' => $settings['global_checkout'],
										)
									);
								}
								echo Cartflows_Admin_Fields::select_field(
									array(
										'id'          => 'wcf_default_page_builder',
										'name'        => '_cartflows_common[default_page_builder]',
										'title'       => __( 'Show Templates designed with', 'cartflows' ),
										'description' => __( 'CartFlows offers flow templates that can be imported in one click. These templates are available in few different page builders. Please choose your preferred page builder from the list so you will only see templates that are made using that page builder..', 'cartflows' ),
										'value'       => $settings['default_page_builder'],
										'options'     => array(
											'elementor' => __( 'Elementor', 'cartflows' ),
											'beaver-builder' => __( 'Beaver Builder', 'cartflows' ),
											'divi'      => __( 'Divi', 'cartflows' ),
											'gutenberg' => __( 'Gutenberg', 'cartflows' ),
											'other'     => __( 'Other', 'cartflows' ),
										),
									)
								);

								do_action( 'cartflows_after_settings_fields', $settings );

								?>
							</div>
							<?php submit_button( __( 'Save Changes', 'cartflows' ), 'cartflows-common-setting-save-btn button-primary button', 'submit', false ); ?>
							<?php wp_nonce_field( 'cartflows-common-settings', 'cartflows-common-settings-nonce' ); ?>
						</form>
						</div>
				</div>
				<!-- General Settings -->

				<?php do_action( 'cartflows_after_general_settings' ); ?>

				<!-- Permalink Settings -->
				<div class="general-settingss-form postbox">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php esc_html_e( 'Permalink Settings', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<form method="post" class="wrap wcf-clear" action="" >
							<div class="form-wrap wcf_permalink_settings">
								<?php

								echo Cartflows_Admin_Fields::radio_field(
									array(
										'id'      => 'permalink_structure',
										'name'    => '_cartflows_permalink[permalink_structure]',
										'value'   => $permalink_settings['permalink_structure'],
										'options' => array(
											'' =>
													array(
														'label' => __( 'Default', 'cartflows' ),
														'description' => 'Default WordPress Permalink',
													),

											'/' . CARTFLOWS_FLOW_POST_TYPE . '/%flowname%/' . CARTFLOWS_STEP_POST_TYPE =>
													array(
														'label' => __( 'Flow and Step Slug', 'cartflows' ),
														'description' => get_site_url() . '/<code>' . CARTFLOWS_FLOW_POST_TYPE . '</code>/%flowname%/<code>' . CARTFLOWS_STEP_POST_TYPE . '</code>/%stepname%/',
													),

											'/' . CARTFLOWS_FLOW_POST_TYPE . '/%flowname%' =>
													array(
														'label' => __( 'Flow Slug', 'cartflows' ),
														'description' => get_site_url() . '/<code>' . CARTFLOWS_FLOW_POST_TYPE . '</code>/%flowname%/%stepname%/',
													),

											'/%flowname%/' . CARTFLOWS_STEP_POST_TYPE =>
													array(
														'label' => __( 'Step Slug', 'cartflows' ),
														'description' => get_site_url() . '/%flowname%/<code>' . CARTFLOWS_STEP_POST_TYPE . '</code>/%stepname%/',
													),
										),
									)
								);
								?>
							<hr/>
							<?php

							echo Cartflows_Admin_Fields::title_field(
								array(
									'title' => __( 'Post Type Permalink Base', 'cartflows' ),
								)
							);

							echo Cartflows_Admin_Fields::text_field(
								array(
									'id'          => 'wcf_permalink_step_base',
									'name'        => '_cartflows_permalink[permalink]',
									'title'       => __( 'Step Base', 'cartflows' ),
									'value'       => $permalink_settings['permalink'],
									'placeholder' => CARTFLOWS_STEP_POST_TYPE,
								)
							);

								echo Cartflows_Admin_Fields::text_field(
									array(
										'id'          => 'wcf_permalink_flow_base',
										'name'        => '_cartflows_permalink[permalink_flow_base]',
										'title'       => __( 'Flow Base', 'cartflows' ),
										'value'       => $permalink_settings['permalink_flow_base'],
										'placeholder' => CARTFLOWS_FLOW_POST_TYPE,
									)
								);

							?>


							</div>
							<p>
								<?php submit_button( __( 'Save Changes', 'cartflows' ), 'cartflows-common-setting-save-btn button-primary button', 'submit', false ); ?>
								<?php submit_button( __( 'Set Default', 'cartflows' ), 'cartflows-common-setting-save-btn button-primary button', 'reset', false ); ?>
								<?php wp_nonce_field( 'cartflows-permalink-settings', 'cartflows-permalink-settings-nonce' ); ?>
							</p>


						</form>
					</div>
				</div>
				<!-- Permalink Settings -->

				<!-- Facebook Pixel Tracking -->
				<div class="general-settingss-form postbox">
					<h2 class="wcf-facebook-hndle wcf-normal-cusror ui-sortable-handle hndle">

						<span><?php esc_html_e( 'Facebook Pixel Settings', 'cartflows' ); ?></span>
					</h2>

					<form method="post" class="wrap wcf-clear" action="">
						<div class="form-wrap">
							<input type="hidden" name="action" value="wcf_save_facebook_pixel_settings">
							<div id="post-body">

								<div class="inside">
									<div class="form-wrap">
										<?php
										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'wcf_facebook_pixel_tracking',
												'name'  => '_cartflows_facebook[facebook_pixel_tracking]',
												'title' => __( 'Enable Facebook Pixel Tracking', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_tracking'],
											)
										);

										echo "<div class='wcf-fb-pixel-wrapper'>";
										?>
											<hr/> 
										<?php
										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'wcf_facebook_pixel_tracking_for_site',
												'name'  => '_cartflows_facebook[facebook_pixel_tracking_for_site]',
												'title' => __( 'Enable for the whole site', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_tracking_for_site'],
											)
										);

										echo Cartflows_Admin_Fields::title_field(
											array(
												'title' => '',
												'description' => __( 'If this option is unchecked, it will only apply to CartFlows steps.', 'cartflows' ),
											)
										);
										?>
											<hr/> 
										<?php
										echo Cartflows_Admin_Fields::text_field(
											array(
												'id'    => 'wcf_facebook_pixel_id',
												'name'  => '_cartflows_facebook[facebook_pixel_id]',
												'title' => __( 'Enter Facebook pixel ID', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_id'],
											)
										);


										echo Cartflows_Admin_Fields::title_field(
											array(
												'title' => __( 'Enable Events:', 'cartflows' ),
											)
										);


										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'wcf_facebook_pixel_initiate_checkout',
												'name'  => '_cartflows_facebook[facebook_pixel_initiate_checkout]',
												'title' => __( 'Initiate Checkout', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_initiate_checkout'],
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'wcf_facebook_pixel_add_payment_info',
												'name'  => '_cartflows_facebook[facebook_pixel_add_payment_info]',
												'title' => __( 'Add Payment Info', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_add_payment_info'],
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'wcf_facebook_pixel_purchase_complete',
												'name'  => '_cartflows_facebook[facebook_pixel_purchase_complete]',
												'title' => __( 'Purchase Complete', 'cartflows' ),
												'value' => $facebook_settings['facebook_pixel_purchase_complete'],
											)
										);

										echo '</div>';

										?>
									</div>

									<?php submit_button( __( 'Save Changes', 'cartflows' ), 'cartflows-facebook-setting-save-btn button-primary button', 'submit', false ); ?>
									<?php wp_nonce_field( 'cartflows-facebook-settings', 'cartflows-facebook-settings-nonce' ); ?>
								</div>
							</div>
						</div>
					</form>
				</div>
				<!-- Facebook Pixel Tracking -->

				<!-- Google Analytics Tracking -->
				<div class="general-settingss-form postbox">
					<h2 class="hndle wcf-normal-cusror ui-sortable-handle">
						<span><?php esc_html_e( 'Google Analytics Settings', 'cartflows' ); ?></span>
					</h2>
					<div class="inside">
						<form method="post" class="wrap wcf-clear" action="" >
							<div class="form-wrap">
								<?php

								echo Cartflows_Admin_Fields::checkobox_field(
									array(
										'id'    => 'enable_google-analytics-id',
										'name'  => '_cartflows_google_analytics[enable_google_analytics]',
										'title' => __( 'Enable Google Analytics Tracking', 'cartflows' ),
										'value' => $google_analytics_settings['enable_google_analytics'],
									)
								);

								echo "<div class='wcf-google-analytics-wrapper'>";
								?>
									<hr/> 
								<?php
								echo Cartflows_Admin_Fields::checkobox_field(
									array(
										'id'    => 'enable_google_analytics_for_site',
										'name'  => '_cartflows_google_analytics[enable_google_analytics_for_site]',
										'title' => __( 'Enable for the whole website', 'cartflows' ),
										'value' => $google_analytics_settings['enable_google_analytics_for_site'],
									)
								);

									echo Cartflows_Admin_Fields::title_field(
										array(
											'title'       => '',
											'description' => __( 'If this option is unchecked, it will only apply to CartFlows steps.', 'cartflows' ),
										)
									);
								?>
									<hr/> 
									<?php
									echo Cartflows_Admin_Fields::text_field(
										array(
											'id'          => 'google-analytics-id',
											'name'        => '_cartflows_google_analytics[google_analytics_id]',
											'title'       => __( 'Google Analytics ID', 'cartflows' ),
											'value'       => $google_analytics_settings['google_analytics_id'],
											'description' => __( 'Log into your <a href="https://analytics.google.com/" target="_blank">google analytics account</a> to find your ID. eg: UA-XXXXXX-X&period;', 'cartflows' ),
										)
									);

										echo Cartflows_Admin_Fields::title_field(
											array(
												'title' => __( 'Enable Events:', 'cartflows' ),
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'enable_begin_checkout',
												'name'  => '_cartflows_google_analytics[enable_begin_checkout]',
												'title' => __( 'Begin Checkout', 'cartflows' ),
												'value' => $google_analytics_settings['enable_begin_checkout'],
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'enable_add_to_cart',
												'name'  => '_cartflows_google_analytics[enable_add_to_cart]',
												'title' => __( 'Add To Cart', 'cartflows' ),
												'value' => $google_analytics_settings['enable_add_to_cart'],
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'enable_add_payment_info',
												'name'  => '_cartflows_google_analytics[enable_add_payment_info]',
												'title' => __( 'Add Payment Info', 'cartflows' ),
												'value' => $google_analytics_settings['enable_add_payment_info'],
											)
										);

										echo Cartflows_Admin_Fields::checkobox_field(
											array(
												'id'    => 'enable_purchase_event',
												'name'  => '_cartflows_google_analytics[enable_purchase_event]',
												'title' => __( 'Purchase', 'cartflows' ),
												'value' => $google_analytics_settings['enable_purchase_event'],
											)
										);

										echo Cartflows_Admin_Fields::title_field(
											array(
												'title' => '',
												'description' => __( 'Google Analytics not working correctly? <a href="https://cartflows.com/docs/troubleshooting-google-analytics-tracking-issues/" > Click here </a> to know more. ', 'cartflows' ),
											)
										);

										do_action( 'cartflows_google_analytics_admin_fields', $google_analytics_settings );

										echo '</div>';
									?>


							</div>
							<p>
								<?php submit_button( __( 'Save Changes', 'cartflows' ), 'cartflows-common-setting-save-btn button-primary button', 'submit', false ); ?>
								<?php wp_nonce_field( 'cartflows-google-analytics-settings', 'cartflows-google-analytics-settings-nonce' ); ?>
							</p>


						</form>
					</div>
				</div>
				<!-- Google Analytics Tracking -->

				<?php do_action( 'cartflows_register_general_settings' ); ?>

			</div>

			<!-- Right Sidebar -->
			<div class="postbox-container" id="postbox-container-1">
				<div id="side-sortables">

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-book"></span>
							<span><?php esc_html_e( 'Knowledge Base', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Not sure how something works? Take a peek at the knowledge base and learn.', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://cartflows.com/docs' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit Knowledge Base »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-groups"></span>
							<span><?php esc_html_e( 'Community', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Join the community of super helpful CartFlows users. Say hello, ask questions, give feedback and help each other!', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://www.facebook.com/groups/cartflows/' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Join Our Facebook Group »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>

					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-sos"></span>
							<span><?php esc_html_e( 'Five Star Support', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<p>
								<?php esc_html_e( 'Got a question? Get in touch with CartFlows developers. We\'re happy to help!', 'cartflows' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( 'https://cartflows.com/contact' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Submit a Ticket »', 'cartflows' ); ?></a>
							</p>
						</div>
					</div>
				<?php
				if ( 'true' == $debug_on ) {
					?>
					<div class="postbox">
						<h2 class="hndle">
							<span class="dashicons dashicons-editor-code"></span>
							<span><?php esc_html_e( 'Load Minified CSS', 'cartflows' ); ?></span>
						</h2>
						<div class="inside">
							<form method="post" class="wrap wcf-clear" action="">
								<p>
									<?php esc_html_e( 'Load the Minified CSS from here. Just Enable it by checking the below given checkbox.', 'cartflows' ); ?>
								</p>
								<?php
									echo Cartflows_Admin_Fields::checkobox_field(
										array(
											'id'    => 'allow_minified_files',
											'name'  => '_cartflows_debug_data[allow_minified_files]',
											'title' => __( 'Load minified CSS & JS Files', 'cartflows' ),
											'value' => $debug_data['allow_minified_files'],
										)
									);
								?>
							<?php submit_button( __( 'Save', 'cartflows' ), 'button-primary button', 'submit', false ); ?>
							<?php wp_nonce_field( 'cartflows-debug-settings', 'cartflows-debug-settings-nonce' ); ?>
							</form>
						</div>
					</div>
					<?php
				}
				?>
				</div>
			</div>
			<!-- Right Sidebar -->

		</div>
		<!-- /post-body -->
		<br class="clear">
	</div>
</div>
<?php endif; ?>

<?php
	/**
	 *  Loads Zapier settings admin view.
	 */
	do_action( 'cartflows_after_general_settings' );
?>
