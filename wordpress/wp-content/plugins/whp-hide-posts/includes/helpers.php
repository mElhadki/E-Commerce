<?php
/**
 * Helper functions.
 *
 * @package  wordpress-hide-posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'whp_wc_exists' ) ) {
    function whp_wc_exists() {
        $plugin = 'woocommerce/woocommerce.php';
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
    }
}

if ( ! function_exists( 'whp_admin_wc_product' ) ) {
    function whp_admin_wc_product() {
        global $post;

        return $post->post_type === 'product';
    }
}

if ( ! function_exists( 'whp_hidden_posts_ids' ) ) {
    function whp_hidden_posts_ids( $post_type = 'post', $from = 'all' ) {
        $key = 'whp_' . $post_type . '_' . $from;
        
        $hidden_posts = wp_cache_get( $key );

        if ( $hidden_posts ) {
            return $hidden_posts;
        }

        switch ( $from ) {
            case 'all': $meta_query = array(
                            'relation' => 'OR',
                            array(
                                'key' => '_whp_hide_on_frontpage',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_blog_page',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_categories',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_search',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_tags',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_authors',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_date',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => '_whp_hide_on_post_navigation',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'front_page': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_frontpage',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'blog_page': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_blog_page',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'categories': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_categories',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'search': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_search',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'tags': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_tags',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'authors': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_authors',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'date': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_date',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            case 'post_navigation': $meta_query = array(
                            array(
                                'key' => '_whp_hide_on_post_navigation',
                                'compare' => 'EXISTS'
                            )
                        );
                        break;
            default: return [];
        }


        $hidden_posts = new WP_Query( array (
            'post_type'         => $post_type,
            'posts_per_page'    => -1,
            'fields' => 'ids',
            'meta_query' => $meta_query
        ) );

        
        $hidden_posts = $hidden_posts->posts;

        wp_cache_set( $key, $hidden_posts, 'whp' );

        return $hidden_posts;
    }
}