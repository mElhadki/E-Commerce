<?php
/**
 * Manages hiding posts that have some hide option enabled.
 *
 * @package  wordpress-hide-posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WHP_Post_Hide class.
 */
class WHP_Post_Hide {
	/**
	 * Enabled post types
	 *
	 * @var array
	 */
	private $_enabled_post_types = [];

	/**
	 * Function: __construct
	 */
	public function __construct() {
		$this->_enabled_post_types = get_option( 'whp_enabled_post_types' );

		if ( empty( $this->_enabled_post_types ) ) {
			$this->_enabled_post_types = ['post'];
		}

		add_action( 'pre_get_posts', array( $this, 'exclude_posts') );
		add_action( 'parse_query', array( $this, 'parse_query') );
		add_filter( 'get_next_post_where', array( $this, 'hide_from_post_navigation' ), 10, 1 );
		add_filter( 'get_previous_post_where', array( $this, 'hide_from_post_navigation' ), 10, 1 );
	}

	/**
	 * A workaround for the is_front_page() check inside pre_get_posts and later hooks.
	 *
	 * Based on the patch from @mattonomics in #27015
	 *
	 * @see http://wordpress.stackexchange.com/a/188320/26350
	 */
	public function parse_query( $query ) {
		if( is_null( $query->queried_object ) && $query->get( 'page_id' ) )	{
			$query->queried_object    = get_post( $query->get( 'page_id' ) );
			$query->queried_object_id = (int) $query->get( 'page_id' );
		}
	}

	/**
	 * Exclude posts with enabled hide options
	 *
	 * @param  WP_Query $query Current query object.
	 *
	 * @return void
	 */
	public function exclude_posts( $query ) {
		if ( ! is_admin() && 'nav_menu_item' !== $query->get( 'post_type' ) ) {
			// Hide on homepage.
			if ( ( is_front_page() && is_home() ) || is_front_page() ) {
				$query->set( 'meta_key', '_whp_hide_on_frontpage' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			} else if ( is_home() ) {
				// Hide on static blog page.
				$query->set( 'meta_key', '_whp_hide_on_blog_page' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}
			// Hide on Categories.
			if ( is_category() ) {
				$query->set( 'meta_key', '_whp_hide_on_categories' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on Search.
			if ( is_search() ) {
				$query->set( 'meta_key', '_whp_hide_on_search' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on Tags.
			if ( is_tag() ) {
				$query->set( 'meta_key', '_whp_hide_on_tags' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on Authors.
			if ( is_author() ) {
				$query->set( 'meta_key', '_whp_hide_on_authors' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on Date.
			if ( is_date() ) {
				$query->set( 'meta_key', '_whp_hide_on_date' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on RSS Feed.
			if ( is_feed() ) {
				$query->set( 'meta_key', '_whp_hide_in_rss_feed' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide in Store.
			if ( whp_wc_exists() && is_shop() ) {
				$query->set( 'meta_key', '_whp_hide_on_store' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}

			// Hide on Product categories.
			if ( whp_wc_exists() && is_product_category() ) {
				$query->set( 'meta_key', '_whp_hide_on_product_category' );
				$query->set( 'meta_compare', 'NOT EXISTS' );
			}
		}
	}

	/**
	 * Hide post from post navigation
	 *
	 * @param   string  $where  
	 *
	 * @return  string          
	 */
	public function hide_from_post_navigation( $where ) {
		$hidden_on_post_navigation = whp_hidden_posts_ids( 'post', 'post_navigation' );

		if ( empty( $hidden_on_post_navigation ) ) {
			return $where;
		}
	
		$ids_placeholders = array_fill( 0, count( $hidden_on_post_navigation ), '%d' );
		$ids_placeholders = implode( ', ', $ids_placeholders );
		
		global $wpdb;
		
		$where .= $wpdb->prepare( " AND ID NOT IN ( $ids_placeholders )", ...$hidden_on_post_navigation );

		return $where;
	}
}
