<?php
/**
 * Logger.
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Logger {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var logger
	 */
	public $logger;

	/**
	 *  Initiator
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

		/* Load WC Logger */
		add_action( 'init', array( $this, 'init_wc_logger' ), 99 );

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( CARTFLOWS_SETTINGS === $page ) {
			add_filter( 'admin_footer_text', array( $this, 'add_footer_link' ), 99 );
		}

	}

	/**
	 *  Add footer link.
	 */
	public function add_footer_link() {
		$settings_url = add_query_arg(
			array(
				'page'                => CARTFLOWS_SETTINGS,
				'cartflows-error-log' => 1,
			),
			admin_url( '/admin.php' )
		);

		echo '<span id="footer-thankyou"> Thank you for using <a href="https://cartflows.com/">CartFlows</a></span> | <a href="' . $settings_url . '"> View Logs </a>';
	}

	/**
	 * Inint Logger.
	 *
	 * @since 1.0.0
	 */
	public function init_wc_logger() {
		if ( class_exists( 'CartFlows_WC_Logger' ) ) {
			$this->logger = new CartFlows_WC_Logger();
		}
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	public function log( $message, $level = 'info' ) {

		$enable_log = apply_filters( 'cartflows_enable_log', 'enable' );

		if ( 'enable' === $enable_log &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows' ) );
		}
	}

	/**
	 * Write log
	 *
	 * @param string $message log message.
	 * @param string $level type of log.
	 * @since 1.0.0
	 */
	public function import_log( $message, $level = 'info' ) {

		if ( defined( 'WP_DEBUG' ) &&
			WP_DEBUG &&
			is_a( $this->logger, 'CartFlows_WC_Logger' ) &&
			did_action( 'plugins_loaded' )
		) {

			$this->logger->log( $level, $message, array( 'source' => 'cartflows-import' ) );
		}
	}

	/**
	 * Get all log files in the log directory.
	 *
	 * @return array
	 */
	public static function get_log_files() {
		$files  = scandir( CARTFLOWS_LOG_DIR );
		$result = array();

		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Return the log file handle.
	 *
	 * @param string $filename Filename to get the handle for.
	 * @return string
	 */
	public static function get_log_file_handle( $filename ) {
		return substr( $filename, 0, strlen( $filename ) > 48 ? strlen( $filename ) - 48 : strlen( $filename ) - 4 );
	}

	/**
	 * Show the log page contents for file log handler.
	 */
	public static function status_logs_file() {

		if ( ! empty( $_REQUEST['handle'] ) ) {

			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'remove_log' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cartflows' ) );
			}
			wp_delete_file( CARTFLOWS_LOG_DIR . rtrim( $_REQUEST['handle'], '-log' ) . '.log' ); //phpcs:ignore
			echo "<div style='padding: 15px;' class='updated inline'> Log deleted successfully! </div>";
		}

		$logs = self::get_log_files();
		if ( ! empty( $_REQUEST['log_file'] ) && isset( $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ] ) ) {
			$viewed_log = $logs[ sanitize_title( wp_unslash( $_REQUEST['log_file'] ) ) ];
		} elseif ( ! empty( $logs ) ) {
			$viewed_log = current( $logs );
		}
		$handle = ! empty( $viewed_log ) ? self::get_log_file_handle( $viewed_log ) : '';

		include_once CARTFLOWS_DIR . 'includes/admin/cartflows-error-log.php';
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Logger::get_instance();
