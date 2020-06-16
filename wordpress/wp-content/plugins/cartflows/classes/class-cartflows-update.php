<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

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
		 *  Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function init() {

			do_action( 'cartflows_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-version', CARTFLOWS_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_VER, '=' ) ) {
				return;
			}

			$this->logger_files();

			if ( version_compare( $saved_version, '1.1.22', '<' ) ) {
				update_option( 'wcf_setup_skipped', true );
			}

			if ( version_compare( $saved_version, '1.2.0', '<' ) ) {

				$this->changed_wp_templates();
			}

			// Update auto saved version number.
			update_option( 'cartflows-version', CARTFLOWS_VER );

			do_action( 'cartflows_update_after' );
		}


		/**
		 * Loading logger files.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function logger_files() {

			if ( ! defined( 'CARTFLOWS_LOG_DIR' ) ) {

				$upload_dir = wp_upload_dir( null, false );

				define( 'CARTFLOWS_LOG_DIR', $upload_dir['basedir'] . '/cartflows-logs/' );
			}

			wcf()->create_files();
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function changed_wp_templates() {

			global $wpdb;

			$query_results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT  {$wpdb->posts}.ID FROM {$wpdb->posts}  LEFT JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )
                where {$wpdb->posts}.post_type = %s AND  {$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.meta_value != %s AND {$wpdb->postmeta}.meta_value != %s",
					'cartflows_step',
					'_wp_page_template',
					'cartflows-canvas',
					'cartflows-default'
				)
			); // db call ok; no-cache ok.

			if ( is_array( $query_results ) && ! empty( $query_results ) ) {

				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-change-template-batch.php';

				wcf()->logger->log( '(✓) Update Templates BATCH Started!' );

				$change_template_batch = new Cartflows_Change_Template_Batch();

				foreach ( $query_results as $query_result ) {

					wcf()->logger->log( '(✓) POST ID ' . $query_result->ID );
					$change_template_batch->push_to_queue( $query_result->ID );
				}

				$change_template_batch->save()->dispatch();
			}
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Update::get_instance();

endif;
