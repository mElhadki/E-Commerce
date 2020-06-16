<?php
/**
 * Plugin Name: Wordpress Hide Posts
 * Description: Hides posts on home page, categories, search, tags page, authors page, RSS Feed as well as hiding Woocommere products
 * Author:      MartinCV
 * Author URI:  https://www.martincv.com
 * Version:     0.5.1
 * Text Domain: whp
 *
 * Wordpress Hide Posts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Wordpress Hide Posts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Custom Page Number Per Page. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    wordpress-hide-posts
 * @author     MartinCV
 * @since      0.0.1
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2020, MartinCV
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordpressHidePosts class
 */
final class WordpressHidePosts {
	/**
	 * Holds current plugin instance
	 *
	 * @var WordpressHidePosts instance
	 */
	private static $_instance;

	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	private $_version = '0.5.1';

	/**
	 * Initiate plugin
	 *
	 * @return Class instance WordpressHidePosts instance
	 */
	public static function instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof WordpressHidePosts ) ) {

			self::$_instance = new WordpressHidePosts;
			self::$_instance->constants();
			self::$_instance->includes();

			add_action( 'plugins_loaded', [ self::$_instance, 'objects' ], 10 );
			add_action( 'plugins_loaded', [ self::$_instance, 'load_textdomain'], 10 );
		}
		return self::$_instance;
	}

	/**
	 * Add required classes
	 *
	 * @return void
	 */
	private function includes() {
		// Includes.
		require_once WHP_PLUGIN_DIR . 'includes/helpers.php';
		
		// Classes.
		require_once WHP_PLUGIN_DIR . 'classes/WHP_Post_Hide.php';

		// Admin/Dashboard only includes.
		if ( is_admin() ) {
			require_once WHP_PLUGIN_DIR . 'classes/admin/WHP_Admin_Dashboard.php';
			require_once WHP_PLUGIN_DIR . 'classes/admin/WHP_Post_Hide_Metabox.php';
		}
	}

	/**
	 * Define global constants
	 *
	 * @return void
	 */
	private function constants() {
		// Plugin version.
		if ( ! defined( 'WHP_VERSION' ) ) {
			define( 'WHP_VERSION', $this->_version );
		}

		// Plugin Folder Path.
		if ( ! defined( 'WHP_PLUGIN_DIR' ) ) {
			define( 'WHP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'WHP_PLUGIN_URL' ) ) {
			define( 'WHP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WHP_PLUGIN_FILE' ) ) {
			define( 'WHP_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Instantiate class objects required in the plugin
	 *
	 * @return void
	 */
	public function objects() {
		// Global objects.
		if ( ! is_admin() ) {
			new WHP_Post_Hide;
		}

		// Init classes if is Admin/Dashboard.
		if( is_admin() ) {
			new WHP_Admin_Dashboard;
			new WHP_Post_Hide_Metabox;
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'whp', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
}

function whp() {
	return WordpressHidePosts::instance();
}
whp();
