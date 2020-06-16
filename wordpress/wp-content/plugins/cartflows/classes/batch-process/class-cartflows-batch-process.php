<?php
/**
 * Batch Processing
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! class_exists( 'CartFlows_Batch_Process' ) ) :

	/**
	 * CartFlows_Batch_Process
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Batch_Process {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Elementor Batch Instance
		 *
		 * @since 1.1.1 Updated instance name with elementor specific.
		 *
		 * @since 1.0.0
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_elementor;

		/**
		 * Beaver Builder Batch Instance
		 *
		 * @since 1.1.1
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_bb;

		/**
		 * Divi Batch Instance
		 *
		 * @since 1.1.1
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_divi;

		/**
		 * Gutenberg Batch Instance
		 *
		 * @since 1.5.9
		 * @var object Class object.
		 * @access public
		 */
		public static $batch_instance_gb;

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

			// Not BB or Elementor then avoid importer.
			// if ( ! class_exists( '\Elementor\Plugin' ) && ! class_exists( 'FLBuilder' ) ) {
			// return;
			// }
			// Core Helpers - Image.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Core Helpers - Batch Processing.
			require_once CARTFLOWS_DIR . 'classes/batch-process/helpers/class-cartflows-importer-image.php';
			require_once CARTFLOWS_DIR . 'classes/batch-process/helpers/class-wp-async-request.php';
			require_once CARTFLOWS_DIR . 'classes/batch-process/helpers/class-wp-background-process.php';

			$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			// Elementor.
			if ( ( 'elementor' === $default_page_builder ) && class_exists( '\Elementor\Plugin' ) ) {
				// Add "elementor" in import [queue].
				// @todo Remove required `allow_url_fopen` support.
				if ( ini_get( 'allow_url_fopen' ) ) {
					require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-elementor.php';
					require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-elementor-batch.php';
					self::$batch_instance_elementor = new Cartflows_Importer_Elementor_Batch();
				}
			}

			// Beaver Builder.
			if ( ( 'beaver-builder' === $default_page_builder ) && class_exists( 'FLBuilder' ) ) {
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-beaver-builder.php';
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-beaver-builder-batch.php';
				self::$batch_instance_bb = new Cartflows_Importer_Beaver_Builder_Batch();
			}

			// Divi.
			if ( ( 'divi' === $default_page_builder ) && ( class_exists( 'ET_Builder_Plugin' ) || Cartflows_Compatibility::get_instance()->is_divi_enabled() ) ) {
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-divi.php';
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-divi-batch.php';
				self::$batch_instance_divi = new Cartflows_Importer_Divi_Batch();
			}

			// Gutenberg.
			if ( ( 'gutenberg' === $default_page_builder ) ) {
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-gutenberg.php';
				require_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-importer-gutenberg-batch.php';
				self::$batch_instance_gb = new Cartflows_Importer_Gutenberg_Batch();
			}

			// Start image importing after site import complete.
			add_action( 'cartflows_after_template_import', array( $this, 'start_batch_process' ) );
			add_action( 'cartflows_import_complete', array( $this, 'complete_batch_import' ) );
			add_filter( 'upload_mimes', array( $this, 'custom_upload_mimes' ) );
			add_filter( 'wp_prepare_attachment_for_js', array( $this, 'add_svg_image_support' ), 10, 3 );
		}

		/**
		 * Added .svg files as supported format in the uploader.
		 *
		 * @since 1.1.4
		 *
		 * @param array $mimes Already supported mime types.
		 */
		public function custom_upload_mimes( $mimes ) {

			// Allow SVG files.
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';

			// Allow XML files.
			$mimes['xml'] = 'text/xml';

			return $mimes;
		}

		/**
		 * Add SVG image support
		 *
		 * @since 1.1.4
		 *
		 * @param array  $response    Attachment response.
		 * @param object $attachment Attachment object.
		 * @param array  $meta        Attachment meta data.
		 */
		public function add_svg_image_support( $response, $attachment, $meta ) {
			if ( ! function_exists( 'simplexml_load_file' ) ) {
				return $response;
			}

			if ( ! empty( $response['sizes'] ) ) {
				return $response;
			}

			if ( 'image/svg+xml' !== $response['mime'] ) {
				return $response;
			}

			$svg_path = get_attached_file( $attachment->ID );

			$dimensions = self::get_svg_dimensions( $svg_path );

			$response['sizes'] = array(
				'full' => array(
					'url'         => $response['url'],
					'width'       => $dimensions->width,
					'height'      => $dimensions->height,
					'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
				),
			);

			return $response;
		}

		/**
		 * Get SVG Dimensions
		 *
		 * @since 1.1.4.
		 *
		 * @param  string $svg SVG file path.
		 * @return array      Return SVG file height & width for valid SVG file.
		 */
		public static function get_svg_dimensions( $svg ) {

			$svg = simplexml_load_file( $svg );

			if ( false === $svg ) {
				$width  = '0';
				$height = '0';
			} else {
				$attributes = $svg->attributes();
				$width      = (string) $attributes->width;
				$height     = (string) $attributes->height;
			}

			return (object) array(
				'width'  => $width,
				'height' => $height,
			);
		}

		/**
		 * Batch Process Complete.
		 *
		 * @return void
		 */
		public function complete_batch_import() {
			wcf()->logger->import_log( '(✓) BATCH Process Complete!' );
		}

		/**
		 * Start Image Import
		 *
		 * @param integer $post_id Post Id.
		 *
		 * @return void
		 */
		public function start_batch_process( $post_id = '' ) {

			$default_page_builder = Cartflows_Helper::get_common_setting( 'default_page_builder' );

			wcf()->logger->import_log( '(✓) BATCH Started!' );
			wcf()->logger->import_log( '(✓) Step ID ' . $post_id );

			// Add "elementor" in import [queue].
			if ( 'beaver-builder' === $default_page_builder && self::$batch_instance_bb ) {

				// Add to queue.
				self::$batch_instance_bb->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_bb->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Beaver Builder" Request..' );

			} elseif ( 'elementor' === $default_page_builder && self::$batch_instance_elementor ) {

				// Add to queue.
				self::$batch_instance_elementor->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_elementor->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Elementor" Request..' );
			} elseif ( 'divi' === $default_page_builder && self::$batch_instance_divi ) {

				// Add to queue.
				self::$batch_instance_divi->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_divi->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Divi" Request..' );
			} elseif ( 'gutenberg' === $default_page_builder && self::$batch_instance_gb ) {

				// Add to queue.
				self::$batch_instance_gb->push_to_queue( $post_id );

				// Dispatch Queue.
				self::$batch_instance_gb->save()->dispatch();

				wcf()->logger->import_log( '(✓) Dispatch "Gutenberg" Request..' );
			} else {
				wcf()->logger->import_log( '(✕) Could not import image due to allow_url_fopen() is disabled!' );
			}
		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	CartFlows_Batch_Process::get_instance();

endif;
