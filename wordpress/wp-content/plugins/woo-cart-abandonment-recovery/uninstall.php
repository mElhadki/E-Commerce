<?php
/**
 * Woocommerce Cart Abandonment Recovery
 * Unscheduling the events.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


wp_clear_scheduled_hook( 'cartflows_ca_update_order_status_action' );
