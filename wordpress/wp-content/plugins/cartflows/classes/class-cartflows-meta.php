<?php
/**
 * CartFlows Meta
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! class_exists( 'Cartflows_Meta' ) ) :

	/**
	 * CartFlows_Meta
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Meta {
		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
		}

		/**
		 * Flow & Step Actions
		 *
		 * @param array $options options.
		 * @param int   $post_id post ID.
		 */
		public function right_column_footer( $options, $post_id ) {
			?>
			<div class="wcf-column-right-footer">
				<?php submit_button( __( 'Update', 'cartflows' ), 'primary components-button is-primary', 'wcf-save', false ); ?>

				<?php
				$flow_id = get_post_meta( $post_id, 'wcf-flow-id', true );
				if ( $flow_id ) {
					?>
					<a href="<?php echo esc_url( get_edit_post_link( $flow_id ) ); ?>" class="button pull-right wcf-back-to-flow-edit">
						<i class="dashicons dashicons-arrow-left-alt"></i> 
						<?php esc_html_e( 'Back to edit Flow', 'cartflows' ); ?>
					</a>
				<?php } ?>

			</div>
			<?php
		}

		/**
		 * Script Header (Used for add script into header)
		 *
		 * @param array $options options.
		 * @param int   $post_id post ID.
		 */
		public function tab_custom_script( $options, $post_id ) {
			?>
			<div class="wcf-<?php echo wcf()->utils->get_step_type( $post_id ); ?>-custom-script-header wcf-tab-content widefat">
				<?php
				/* Script added onto the header */
				echo wcf()->meta->get_area_field(
					array(
						'label' => __( 'Custom Script', 'cartflows' ),
						'name'  => 'wcf-custom-script',
						'value' => htmlspecialchars( $options['wcf-custom-script'], ENT_COMPAT, 'utf-8' ),
						'help'  => esc_html__( 'Custom script lets you add your own custom script on front end of this flow page.', 'cartflows' ),
					)
				);
				?>
			</div>
			<?php
		}
	}

endif;
