<?php
/*
Plugin Name: Weglot Translate
Plugin URI: http://wordpress.org/plugins/weglot/
Description: Translate your website into multiple languages in minutes without doing any coding. Fully SEO compatible.
Author: Weglot Translate team
Author URI: https://weglot.com/
Text Domain: weglot
Domain Path: /languages/
Version: 3.1.7
*/

/**
 * This file need to be compatible with PHP 5.3
 * Example : Don't use short syntax for array()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WEGLOT_NAME', 'Weglot' );
define( 'WEGLOT_SLUG', 'weglot-translate' );
define( 'WEGLOT_OPTION_GROUP', 'group-weglot-translate' );
define( 'WEGLOT_VERSION', '3.1.7' );
define( 'WEGLOT_PHP_MIN', '5.4' );
define( 'WEGLOT_BNAME', plugin_basename( __FILE__ ) );
define( 'WEGLOT_DIR', __DIR__ );
define( 'WEGLOT_DIR_LANGUAGES', WEGLOT_DIR . '/languages' );
define( 'WEGLOT_DIR_DIST', WEGLOT_DIR . '/dist' );

define( 'WEGLOT_DIRURL', plugin_dir_url( __FILE__ ) );
define( 'WEGLOT_URL_DIST', WEGLOT_DIRURL . 'dist' );
define( 'WEGLOT_LATEST_VERSION', '2.7.0' );
define( 'WEGLOT_LIB_PARSER', '1' );
define( 'WEGLOT_DEBUG', false );
define( 'WEGLOT_DEV', false );

define( 'WEGLOT_TEMPLATES', WEGLOT_DIR . '/templates' );
define( 'WEGLOT_TEMPLATES_ADMIN', WEGLOT_TEMPLATES . '/admin' );
define( 'WEGLOT_TEMPLATES_ADMIN_METABOXES', WEGLOT_TEMPLATES_ADMIN . '/metaboxes' );
define( 'WEGLOT_TEMPLATES_ADMIN_NOTICES', WEGLOT_TEMPLATES_ADMIN . '/notices' );
define( 'WEGLOT_TEMPLATES_ADMIN_PAGES', WEGLOT_TEMPLATES_ADMIN . '/pages' );

// Compatibility Yoast premium Redirection
$dir_yoast_premium = plugin_dir_path( __DIR__ ) . 'wordpress-seo-premium';
if ( file_exists( $dir_yoast_premium . '/wp-seo-premium.php' ) ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! weglot_is_compatible() ) {
		return;
	}

	$yoast_plugin_data        = get_plugin_data( $dir_yoast_premium . '/wp-seo-premium.php' );
	$dir_yoast_premium_inside = $dir_yoast_premium . '/premium/';

	// Override yoast redirect
	if (
		! is_admin() &&
		version_compare( $yoast_plugin_data['Version'], '7.1.0', '>=' ) &&
		is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) &&
		file_exists( $dir_yoast_premium_inside ) &&
		file_exists( $dir_yoast_premium_inside . 'classes/redirect/redirect-handler.php' ) &&
		file_exists( $dir_yoast_premium_inside . 'classes/redirect/redirect-util.php' )
	) {
		require_once __DIR__ . '/weglot-autoload.php';
		require_once __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/bootstrap.php';
		require_once __DIR__ . '/weglot-functions.php';

		include_once __DIR__ . '/src/third/yoast/redirect-premium.php';
	}
}

/**
 * Check compatibility this Weglot with WordPress config.
 */
function weglot_is_compatible() {
	// Check php version.
	if ( version_compare( PHP_VERSION, WEGLOT_PHP_MIN ) < 0 ) {
		add_action( 'admin_notices', 'weglot_php_min_compatibility' );
		return false;
	}

	return true;
}

/**
 * Admin notices if weglot not compatible
 *
 * @return void
 */
function weglot_php_min_compatibility() {
	if ( ! file_exists( WEGLOT_TEMPLATES_ADMIN_NOTICES . '/php-min.php' ) ) {
		return;
	}

	include_once WEGLOT_TEMPLATES_ADMIN_NOTICES . '/php-min.php';
}

/**
 * Activate Weglot.
 *
 * @since 2.0
 */
function weglot_plugin_activate() {
	if ( ! weglot_is_compatible() ) {
		return;
	}

	require_once __DIR__ . '/weglot-autoload.php';
	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/weglot-compatibility.php';
	require_once __DIR__ . '/weglot-functions.php';
	require_once __DIR__ . '/bootstrap.php';

	Context_Weglot::weglot_get_context()->activate_plugin();
}

/**
 * Deactivate Weglot.
 *
 * @since 2.0
 */
function weglot_plugin_deactivate() {
	flush_rewrite_rules();

	require_once __DIR__ . '/weglot-autoload.php';
	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/weglot-compatibility.php';
	require_once __DIR__ . '/weglot-functions.php';
	require_once __DIR__ . '/bootstrap.php';

	Context_Weglot::weglot_get_context()->deactivate_plugin();
}

/**
 * Uninstall Weglot.
 *
 * @since 2.0
 */
function weglot_plugin_uninstall() {
	flush_rewrite_rules();
	delete_option( WEGLOT_SLUG );
}

/**
 * Rollback v2 => v1
 *
 * @return void
 */
function weglot_rollback() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'weglot_rollback' ) ) {
		wp_nonce_ays( '' );
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugin  = 'weglot';
	$title   = sprintf( __( '%s Update Rollback', 'weglot' ), WEGLOT_NAME );
	$nonce   = 'upgrade-plugin_' . $plugin;
	$url     = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
	$version = WEGLOT_LATEST_VERSION;

	$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin', 'version' ) );

	$rollback = new \WeglotWP\Helpers\Helper_Rollback_Weglot( $upgrader_skin );
	$rollback->rollback( $version );
}

/**
 * Load Weglot.
 *
 * @since 2.0
 */
function weglot_plugin_loaded() {
	require_once __DIR__ . '/weglot-autoload.php';
	require_once __DIR__ . '/weglot-compatibility.php';

	add_action( 'admin_post_weglot_rollback', 'weglot_rollback' );

	if ( weglot_is_compatible() ) {
		require_once __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/bootstrap.php';
		require_once __DIR__ . '/weglot-functions.php';

		weglot_init();
	}
}

register_activation_hook( __FILE__, 'weglot_plugin_activate' );
register_deactivation_hook( __FILE__, 'weglot_plugin_deactivate' );
register_uninstall_hook( __FILE__, 'weglot_plugin_uninstall' );

add_action( 'plugins_loaded', 'weglot_plugin_loaded' );
