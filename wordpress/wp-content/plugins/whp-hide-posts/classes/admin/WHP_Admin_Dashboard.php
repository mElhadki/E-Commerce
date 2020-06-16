<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WHP_Admin_Dashboard {
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'menu' ] );
	}

    /**
     * Register new submenu and Settings
     *
     * @return  void  
     */
	public function menu() {
		add_submenu_page(
			'options-general.php',
            __( 'Hide Posts', 'whp' ),
            __( 'Hide Posts', 'whp' ),
            'administrator',
            'whp-settings',
            [ $this, 'settings' ]
        );
	}

    /**
     * Show the settings form with options
     *
     * @return  void  
     */
	public function settings() {
        $post_types = get_post_types( [ 'public' => true ], 'object' );
        $enabled_post_types = get_option( 'whp_enabled_post_types' );

        @include_once WHP_PLUGIN_DIR . 'views/admin/template-admin-dashboard.php';
	}

    /**
     * Register plugin settings
     *
     * @return  void  
     */
	public function register_settings() {
		register_setting( 'whp-settings-group', 'whp_enabled_post_types' );
	}
}