<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}
 
delete_option( 'whp_enabled_post_types' );

global $wpdb;
$query = "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_whp_hide_on_%'";
$wpdb->query( $query );