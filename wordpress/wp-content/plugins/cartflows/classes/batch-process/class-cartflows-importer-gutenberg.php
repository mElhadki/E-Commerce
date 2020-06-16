<?php
/**
 * Gutenberg Importer
 *
 * @package CartFlows
 * @since x.x.x
 */

if ( ! class_exists( 'CartFlows_Importer_Gutenberg' ) ) :

	/**
	 * CartFlows Import Gutenberg
	 *
	 * @since x.x.x
	 */
	class CartFlows_Importer_Gutenberg {

		/**
		 * Instance
		 *
		 * @since x.x.x
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since x.x.x
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
		 * @since x.x.x
		 */
		public function __construct() {}

		/**
		 * Update post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return void
		 */
		public function import_single_post( $post_id = 0 ) {

			// Download and replace images.
			$content = get_post_field( 'post_content', $post_id );

			if ( empty( $content ) ) {
				wcf()->logger->import_log( '(✕) Not have "Gutenberg" Data. Post content is empty!' );
			} else {

				wcf()->logger->import_log( '(✓) Processing Request..' );

				// Update hotlink images.
				$content = CartFlows_Importer::get_instance()->get_content( $content );

				// Fix for gutenberg invalid html due & -> &amp -> \u0026amp.
				$content = str_replace( '&amp;', "\u0026amp;", $content );

				// Update post content.
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $content,
					)
				);

				wcf()->logger->import_log( '(✓) Process Complete' );
			}
		}

	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Importer_Gutenberg::get_instance();

endif;
