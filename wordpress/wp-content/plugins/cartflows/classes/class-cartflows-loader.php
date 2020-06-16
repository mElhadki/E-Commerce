<?php
/**
 * CartFlows Loader.
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Loader' ) ) {

	/**
	 * Class Cartflows_Loader.
	 */
	final class Cartflows_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Member Variable
		 *
		 * @var utils
		 */
		public $utils = null;

		/**
		 * Member Variable
		 *
		 * @var logger
		 */
		public $logger = null;

		/**
		 * Member Variable
		 *
		 * @var options
		 */
		public $options = null;

		/**
		 * Member Variable
		 *
		 * @var meta
		 */
		public $meta = null;

		/**
		 * Member Variable
		 *
		 * @var Tracking_Data
		 */
		public $alldata;

		/**
		 * Member Variable
		 *
		 * @var flow
		 */
		public $flow = null;

		/**
		 * Member Variable
		 *
		 * @var assets_vars
		 */
		public $assets_vars = null;

		/**
		 *  Member Variable
		 *
		 *  @var assets_vars
		 */

		public $is_woo_active = true;

		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

				/**
				 * CartFlows loaded.
				 *
				 * Fires when Cartflows was fully loaded and instantiated.
				 *
				 * @since 1.0.0
				 */
				do_action( 'cartflows_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->define_constants();

			// Activation hook.
			register_activation_hook( CARTFLOWS_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( CARTFLOWS_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );
			add_action( 'plugins_loaded', array( $this, 'load_cf_textdomain' ) );
		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants() {

			define( 'CARTFLOWS_BASE', plugin_basename( CARTFLOWS_FILE ) );
			define( 'CARTFLOWS_DIR', plugin_dir_path( CARTFLOWS_FILE ) );
			define( 'CARTFLOWS_URL', plugins_url( '/', CARTFLOWS_FILE ) );
			define( 'CARTFLOWS_VER', '1.5.10' );
			define( 'CARTFLOWS_SLUG', 'cartflows' );
			define( 'CARTFLOWS_SETTINGS', 'cartflows_settings' );

			define( 'CARTFLOWS_FLOW_POST_TYPE', 'cartflows_flow' );
			define( 'CARTFLOWS_STEP_POST_TYPE', 'cartflows_step' );

			if ( ! defined( 'CARTFLOWS_SERVER_URL' ) ) {
				define( 'CARTFLOWS_SERVER_URL', 'https://my.cartflows.com/' );
			}
			define( 'CARTFLOWS_DOMAIN_URL', 'https://cartflows.com/' );
			define( 'CARTFLOWS_TEMPLATES_URL', 'https://templates.cartflows.com/' );
			define( 'CARTFLOWS_TAXONOMY_STEP_TYPE', 'cartflows_step_type' );
			define( 'CARTFLOWS_TAXONOMY_STEP_FLOW', 'cartflows_step_flow' );

			if ( ! defined( 'CARTFLOWS_TAXONOMY_STEP_PAGE_BUILDER' ) ) {
				define( 'CARTFLOWS_TAXONOMY_STEP_PAGE_BUILDER', 'cartflows_step_page_builder' );
			}
			if ( ! defined( 'CARTFLOWS_TAXONOMY_FLOW_PAGE_BUILDER' ) ) {
				define( 'CARTFLOWS_TAXONOMY_FLOW_PAGE_BUILDER', 'cartflows_flow_page_builder' );
			}
			if ( ! defined( 'CARTFLOWS_TAXONOMY_FLOW_CATEGORY' ) ) {
				define( 'CARTFLOWS_TAXONOMY_FLOW_CATEGORY', 'cartflows_flow_category' );
			}

			if ( ! defined( 'CARTFLOWS_LOG_DIR' ) ) {

				$upload_dir = wp_upload_dir( null, false );

				define( 'CARTFLOWS_LOG_DIR', $upload_dir['basedir'] . '/cartflows-logs/' );
			}
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {

			$this->load_helper_files_components();
			$this->load_core_files();
			$this->load_core_components();

			add_action( 'wp_loaded', array( $this, 'initialize' ) );
			add_action( 'cartflows_pro_init', array( $this, 'after_cartflows_pro_init' ) );

			if ( ! $this->is_woo_active ) {
				add_action( 'admin_notices', array( $this, 'fails_to_load' ) );
			}

			/**
			 * CartFlows Init.
			 *
			 * Fires when Cartflows is instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'cartflows_init' );
		}

		/**
		 * After CartFlows Pro init.
		 *
		 * @since 1.1.19
		 *
		 * @return void
		 */
		public function after_cartflows_pro_init() {

			if ( ! is_admin() ) {
				return;
			}

			if ( version_compare( CARTFLOWS_PRO_VER, '1.5.4', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'required_cartflows_pro_notice' ) );
			}
		}

		/**
		 * Required CartFlows Pro Notice.
		 *
		 * @since 1.1.19
		 *
		 * @return void
		 */
		public function required_cartflows_pro_notice() {

			$required_pro_version = '1.5.4';

			$class = 'notice notice-warning';
			/* translators: %s: html tags */
			$message = sprintf( __( 'You are using an older version of %1$sCartFlows Pro%2$s. Please update %1$sCartFlows Pro%2$s plugin to version %1$s%3$s%2$s or higher.', 'cartflows' ), '<strong>', '</strong>', $required_pro_version );

			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
		}

		/**
		 * Load Helper Files and Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_helper_files_components() {

			$this->is_woo_active = function_exists( 'WC' );

			/* Public Utils */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-utils.php';

			/* Public Global Namespace Function */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-functions.php';

			/* Admin Helper */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-helper.php';

			/* Meta Default Values */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-default-meta.php';

			require_once CARTFLOWS_DIR . 'classes/class-cartflows-tracking.php';

			$this->utils   = Cartflows_Utils::get_instance();
			$this->options = Cartflows_Default_Meta::get_instance();
			$this->alldata = Cartflows_Tracking::get_instance();
		}

		/**
		 * Init hooked function.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function initialize() {
			$this->assets_vars = $this->utils->get_assets_path();
		}

		/**
		 * Load Core Files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_files() {

			/* Update compatibility. */
			require_once CARTFLOWS_DIR . 'classes/class-cartflows-update.php';

			/* Page builder compatibilty class */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-compatibility.php';

			/* Theme support */
			if ( $this->is_woo_active ) {
				include_once CARTFLOWS_DIR . 'classes/class-cartflows-theme-support.php';
			}

			/* Admin Meta Fields*/
			include_once CARTFLOWS_DIR . 'classes/fields/typography/class-cartflows-font-families.php';
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-meta-fields.php';
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-meta.php';

			/* Cloning */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-cloning.php';

			/* Admin Settings */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-admin.php';

			/* Logger */
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-log-handler-interface.php';
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-log-handler.php';
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-log-handler-file.php';
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-log-levels.php';
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-logger-interface.php';
			include_once CARTFLOWS_DIR . 'classes/logger/class-cartflows-wc-logger.php';

			/* Core Modules */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-logger.php';

			/* Frontend Global */
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-frontend.php';
			require_once CARTFLOWS_DIR . 'classes/class-cartflows-flow-frontend.php';

			/* Modules */
			include_once CARTFLOWS_DIR . 'modules/flow/class-cartflows-flow.php';
			include_once CARTFLOWS_DIR . 'modules/landing/class-cartflows-landing.php';

			if ( $this->is_woo_active ) {
				include_once CARTFLOWS_DIR . 'modules/checkout/class-cartflows-checkout.php';
				include_once CARTFLOWS_DIR . 'modules/thankyou/class-cartflows-thankyou.php';
				include_once CARTFLOWS_DIR . 'modules/optin/class-cartflows-optin.php';
			}

			include_once CARTFLOWS_DIR . 'classes/class-cartflows-api.php';
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-importer-core.php';

			include_once CARTFLOWS_DIR . 'classes/batch-process/class-cartflows-batch-process.php';
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-importer.php';

			include_once CARTFLOWS_DIR . 'classes/class-cartflows-wizard.php';

			include_once CARTFLOWS_DIR . 'classes/class-cartflows-metabox.php';

			include_once CARTFLOWS_DIR . 'classes/deprecated/deprecated-hooks.php';

		}

		/**
		 * Load Core Components.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_components() {

			$this->meta   = Cartflows_Meta_Fields::get_instance();
			$this->logger = Cartflows_Logger::get_instance();
			$this->flow   = Cartflows_Flow_Frontend::get_instance();
		}

		/**
		 * Create files/directories.
		 */
		public function create_files() {

			// Install files and folders for uploading files and prevent hotlinking.
			$upload_dir = wp_upload_dir();

			$files = array(
				array(
					'base'    => CARTFLOWS_LOG_DIR,
					'file'    => '.htaccess',
					'content' => 'deny from all',
				),
				array(
					'base'    => CARTFLOWS_LOG_DIR,
					'file'    => 'index.html',
					'content' => '',
				),
			);

			foreach ( $files as $file ) {
				if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
					$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore 
					if ( $file_handle ) {
						fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
						fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
					}
				}
			}
		}
		/**
		 * Load CartFlows Pro Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/cartflows/ folder
		 *      2. Local dorectory /wp-content/plugins/cartflows/languages/ folder
		 *
		 * @since 1.0.3
		 * @return void
		 */
		public function load_cf_textdomain() {

			// Default languages directory for CartFlows Pro.
			$lang_dir = CARTFLOWS_DIR . 'languages/';

			/**
			 * Filters the languages directory path to use for CartFlows Pro.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'cartflows_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			global $wp_version;

			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			/**
			 * Language Locale for CartFlows Pro
			 *
			 * @var $get_locale The locale to use.
			 * Uses get_user_locale()` in WordPress 4.7 or greater,
			 * otherwise uses `get_locale()`.
			 */
			$locale = apply_filters( 'plugin_locale', $get_locale, 'cartflows' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'cartflows', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/cartflows/ folder.
				load_textdomain( 'cartflows', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/cartflows/languages/ folder.
				load_textdomain( 'cartflows', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'cartflows', false, $lang_dir );
			}
		}

		/**
		 * Fires admin notice when Elementor is not installed and activated.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function fails_to_load() {

			$screen = get_current_screen();

			if ( ! wcf()->utils->is_step_post_type() ) {
				return;
			}

			if ( ! wcf()->utils->check_is_woo_required_page() ) {
				return;
			}

			$skip_notice = false;

			wp_localize_script( 'wcf-global-admin', 'cartflows_woo', array( 'show_update_post' => $skip_notice ) );

			$class = 'notice notice-warning';
			/* translators: %s: html tags */
			$message = sprintf( __( 'This %1$sCartFlows%2$s page requires %1$sWooCommerce%2$s plugin installed & activated.', 'cartflows' ), '<strong>', '</strong>' );

			$plugin = 'woocommerce/woocommerce.php';

			if ( _is_woo_installed() ) {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate WooCommerce', 'cartflows' );

			} else {
				if ( ! current_user_can( 'install_plugins' ) ) {
					return;
				}

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
				$button_label = __( 'Install WooCommerce', 'cartflows' );
			}

			$button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), $message, $button );
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset() {

			if ( ! defined( 'CARTFLOWS_LOG_DIR' ) ) {

				$upload_dir = wp_upload_dir( null, false );

				define( 'CARTFLOWS_LOG_DIR', $upload_dir['basedir'] . '/cartflows-logs/' );
			}

			$this->create_files();
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-helper.php';
			include_once CARTFLOWS_DIR . 'classes/class-cartflows-functions.php';
			include_once CARTFLOWS_DIR . 'modules/flow/classes/class-cartflows-flow-post-type.php';
			include_once CARTFLOWS_DIR . 'modules/flow/classes/class-cartflows-step-post-type.php';

			Cartflows_Flow_Post_Type::get_instance()->flow_post_type();
			Cartflows_Step_Post_Type::get_instance()->step_post_type();
			flush_rewrite_rules();
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {
		}

		/**
		 * Logger Class Instance
		 */
		public function logger() {
			return Cartflows_Logger::get_instance();
		}


	}

	/**
	 *  Prepare if class 'Cartflows_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Loader::get_instance();
}

/**
 * Get global class.
 *
 * @return object
 */
function wcf() {
	return Cartflows_Loader::get_instance();
}


if ( ! function_exists( '_is_woo_installed' ) ) {

	/**
	 * Is woocommerce plugin installed.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	function _is_woo_installed() {

		$path    = 'woocommerce/woocommerce.php';
		$plugins = get_plugins();

		return isset( $plugins[ $path ] );
	}
}
