<?php
/**
 * View Flow steps
 *
 * @package CartFlows
 */

$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

$steps = array(
	'landing'  => __( 'Landing', 'cartflows' ),
	'checkout' => __( 'Checkout (Woo)', 'cartflows' ),
	'thankyou' => __( 'Thank You (Woo)', 'cartflows' ),
	'upsell'   => __( 'Upsell (Woo)', 'cartflows' ),
	'downsell' => __( 'Downsell (Woo)', 'cartflows' ),
	'optin'    => __( 'Optin (Woo)', 'cartflows' ),
);

?>
	<div class="wcf-flow-steps-meta-box">
		<div class="wcf-flow-settings">
			<?php do_action( 'cartflows_above_flow_steps' ); ?>
			<div class="wcf-flow-steps-wrap">
				<div class="wcf-flow-steps-container">
					<?php if ( is_array( $options['steps'] ) ) { ?>
						<?php foreach ( $options['steps'] as $index => $data ) { ?>
							<?php
							$term_slug            = '';
							$term_name            = '';
							$step_wrap_class      = '';
							$has_product_assigned = true;
							$is_global_checkout   = '';
							$common               = '';
							$data_step_id         = intval( $data['id'] );

							if ( isset( $data['type'] ) ) {
								$term_slug = $data['type'];
								$term_name = $steps[ $data['type'] ];
							}

							if ( ! _is_cartflows_pro() && ( 'upsell' === $term_slug || 'downsell' === $term_slug ) ) {
								$step_wrap_class .= ' invalid-step';
							}

							if ( isset( $_GET['highlight-step-id'] ) ) { //phpcs:ignore

								$highlight_step_id = intval( $_GET['highlight-step-id'] ); //phpcs:ignore

								if ( $highlight_step_id === $data_step_id ) {
									$step_wrap_class .= ' wcf-new-step-highlight';
								}
							}

							if ( 'checkout' === $term_slug ) {

								$common = Cartflows_Helper::get_common_settings();

								$is_global_checkout = (int) $common['global_checkout'];

								if ( $data['id'] === $is_global_checkout ) {
									$step_wrap_class .= ' wcf-global-checkout';
								}
							}

							if ( 'upsell' === $term_slug || 'downsell' === $term_slug || 'checkout' === $term_slug ) {

								$has_product_assigned = Cartflows_Helper::has_product_assigned( $data['id'] );

								if ( ( ! $has_product_assigned ) && ( $data['id'] != $is_global_checkout ) ) {
									$step_wrap_class .= ' wcf-no-product-step';
								}
							}

							?>
							<div class="wcf-step-wrap <?php echo $step_wrap_class; ?>" data-id="<?php echo $data['id']; ?>" data-term-slug="<?php echo esc_attr( $term_slug ); ?>">
								<div class="wcf-step">
									<div class="wcf-step-left-content">
										<span class="dashicons dashicons-menu"></span> 
										<span><?php echo wp_trim_words( get_the_title( $data['id'] ), 3 ); ?></span>
										<span class="wcf-flow-badge"><?php echo esc_attr( $term_name ); ?></span>

										<?php
										if ( ( ! $has_product_assigned ) && ( $data['id'] != $is_global_checkout ) ) {
											?>
											<span class="wcf-no-product-badge"><?php esc_html_e( 'No Product Assigned', 'cartflows' ); ?></span>
											<?php
										} elseif ( ( $has_product_assigned ) && ( $data['id'] === $is_global_checkout ) ) {
											?>
											<span class="wcf-global-checkout-badge wcf-error-badge"><?php esc_html_e( 'Global Checkout - Remove selected checkout product', 'cartflows' ); ?></span>
											<?php
										} elseif ( ( ! $has_product_assigned ) && $data['id'] === $is_global_checkout ) {
											?>
											<span class="wcf-global-checkout-badge"><?php esc_html_e( 'Global Checkout', 'cartflows' ); ?></span>
											<?php
										}
										?>

										<input type="hidden" class="wcf-steps-hidden" name="wcf-steps[]" value="<?php echo $data['id']; ?>">
										<?php do_action( 'cartflows_step_left_content', $data['id'], $term_slug ); ?>
									</div>
									<div class="wcf-steps-action-buttons">
										<a href="<?php echo get_permalink( $data['id'] ); ?>" target="_blank"  class="wcf-step-view wcf-action-button wp-ui-text-highlight" title="<?php echo esc_html__( 'View Step', 'cartflows' ); ?>" >
											<span class="dashicons dashicons-visibility"></span>
											<span class="wcf-step-act-btn-text"><?php echo esc_html__( 'View', 'cartflows' ); ?></span>
										</a>
										<a href="<?php echo get_edit_post_link( $data['id'] ); ?>" class="wcf-step-edit wcf-action-button wp-ui-text-highlight" title="<?php echo esc_html__( 'Edit Step', 'cartflows' ); ?>" >
											<span class="dashicons dashicons-edit"></span>
											<span class="wcf-step-act-btn-text"><?php echo esc_html__( 'Edit', 'cartflows' ); ?></span>
										</a>
										<?php
										if ( _is_cartflows_pro() ) {
											?>
										<a href="<?php echo wp_nonce_url( 'admin.php?action=cartflows_clone_step&post=' . $data['id'], 'step_clone', 'step_clone_nonce' ); ?>" class="wcf-step-clone wcf-action-button wp-ui-text-highlight" title="<?php echo esc_html__( 'Clone Step', 'cartflows' ); ?>" data-id="<?php echo $data['id']; ?>">
											<span class="dashicons dashicons-admin-page"></span>
											<span class="wcf-step-act-btn-text"><?php echo esc_html__( 'Clone', 'cartflows' ); ?></span>
										</a>
										<?php } ?>
										<a href="#" class="wcf-step-delete wcf-action-button wp-ui-text-highlight" title="<?php echo esc_html__( 'Delete Step', 'cartflows' ); ?>" data-id="<?php echo $data['id']; ?>">
											<span class="dashicons dashicons-trash"></span>
											<span class="wcf-step-act-btn-text"><?php echo esc_html__( 'Delete', 'cartflows' ); ?></span>
										</a>						
									</div>
								</div>
							</div><!-- .wcf-step-wrap -->
						<?php } ?>
					<?php } ?>
				</div><!-- .wcf-flow-steps-container -->
			</div> <!-- .wcf-flow-steps-wrap -->
			<div class="wcf-flow-buttons-wrap"> <!-- .wcf-flow-buttons-wrap -->
				<?php do_action( 'cartflows_bellow_flow_steps' ); ?>
				<div class='wcf-add-new-step-btn-wrap'>
					<button class='wcf-trigger-popup button button-primary'>
						<?php echo esc_html__( 'Add New Step', 'cartflows' ); ?>
					</button>
				</div>
			</div><!-- .wcf-flow-buttons-wrap -->
		</div><!-- .wcf-flow-settings -->

		<div id="wcf-remote-step-importer" class="wcf-templates-popup-overlay">
			<div class="wcf-templates-popup-content">
				<div class="spinner"></div>
				<div class="wcf-templates-wrap wcf-templates-wrap-flows">

					<div id="wcf-remote-step-actions" class="wcf-template-header">
						<div class="wcf-template-logo-wrap">
							<span class="wcf-cartflows-logo-img">
								<span class="cartflows-logo-icon"></span>
							</span>
							<span class="wcf-cartflows-title"><?php esc_html_e( 'Steps Library', 'cartflows' ); ?></span>
						</div>
						<div class="wcf-tab-wrapper">
							<?php if ( 'other' !== $default_page_builder ) { ?>
								<div id="wcf-get-started-steps">
									<ul class="filter-links ">
										<li>
											<a href="#" class="current" data-slug="ready-templates" data-title="<?php esc_html_e( 'Ready Templates', 'cartflows' ); ?>"><?php esc_html_e( 'Ready Templates', 'cartflows' ); ?></a>
										</li>
										<li>
											<a href="#" data-slug="canvas" data-title="<?php esc_html_e( 'Create Your Own', 'cartflows' ); ?>"><?php esc_html_e( 'Create Your Own', 'cartflows' ); ?></a>
										</li>
									</ul>
								</div>
							<?php } ?>
						</div>
						<div class="wcf-popup-close-wrap">
							<span class="close-icon"><span class="wcf-cartflow-icons dashicons dashicons-no"></span></span>
						</div>
					</div>

					<!--<div class="wcf-search-form">
						<label class="screen-reader-text" for="wp-filter-search-input"><?php esc_html_e( 'Search Sites', 'cartflows' ); ?> </label>
						<input placeholder="<?php esc_html_e( 'Search Flow...', 'cartflows' ); ?>" type="text" aria-describedby="live-search-desc" class="wcf-flow-search-input">
					</div>-->

					<div id="wcf-remote-content">
						<?php if ( 'other' !== $default_page_builder ) { ?>
							<div id="wcf-ready-templates">
								<div id="wcf-remote-filters">
									<div id="wcf-page-builders"></div>
									<div id="wcf-categories"></div>
								</div>
								<div class="wcf-page-builder-notice"></div>
								<div id="wcf-remote-step-list" class="wcf-remote-list wcf-template-list-wrap"><span class="spinner is-active"></span></div>
								<div id="wcf-upcoming-page-builders" style="display: none;" class="wcf-remote-list wcf-template-list-wrap"></div>
							</div>
						<?php } ?>
						<div id="wcf-start-from-scratch" style="<?php echo ( 'other' !== $default_page_builder ) ? 'display: none;' : ''; ?>">
							<div class="inner">
								<div id="wcf-scratch-steps-categories">
									<select class="step-type-filter-links filter-links">
										<option value="" class="current"> Select Step Type </option>

										<?php foreach ( $steps as $key => $value ) { ?>
											<option class="<?php echo $key; ?>" data-slug="<?php echo $key; ?>" data-title="<?php echo $key; ?>"><?php echo $value; ?></option>
										<?php } ?>
									</select>
								</div>
								<a href="#" class="button button-primary cartflows-step-import-blank"><?php esc_html_e( 'Create Step', 'cartflows' ); ?></a>
								<?php if ( ! _is_cartflows_pro() ) { ?>
								<div class="wcf-template-notice"><p><?php echo esc_html__( 'You need a Cartflows Pro version to import Upsell / Downsell', 'cartflows' ); ?></p></div>
								<?php } ?>
								<p class="wcf-learn-how"><a href="https://cartflows.com/docs/cartflows-step-types/" target="_blank"><?php esc_html_e( 'Learn How', 'cartflows' ); ?> <i class="dashicons dashicons-external"></i></a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- .wcf-templates-popup-overlay -->
	</div>
<?php
