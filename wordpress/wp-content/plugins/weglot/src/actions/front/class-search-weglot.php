<?php

namespace WeglotWP\Actions\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Is_Admin;
use WeglotWP\Models\Hooks_Interface_Weglot;

/**
 * @since 2.4.0
 */
class Search_Weglot implements Hooks_Interface_Weglot {
	protected $old_search = null;

	protected $new_search = null;

	/**
	 * @since 2.4.0
	 */
	public function __construct() {
		$this->option_services  = weglot_get_service( 'Option_Service_Weglot' );
		$this->parser_services  = weglot_get_service( 'Parser_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function hooks() {

        if( Helper_Is_Admin::is_wp_admin()) {
            return;
        }

		$search_active = $this->option_services->get_option( 'active_search' );

		if ( $search_active ) {
			add_action( 'pre_get_posts', [ $this, 'pre_get_posts_translate' ] );
			add_filter( 'get_search_query', [ $this, 'get_search_query_translate' ] );
		}
	}

	/**
	 * @since 2.4.0
	 * @param WP_Query $query
	 * @return void
	 */
	public function pre_get_posts_translate( $query ) {
		if ( ! $query->is_search() || ! $query->is_main_query() ) {
			return;
		}

		$query_vars_check = apply_filters( 'weglot_query_vars_check', 's' );
		if ( empty( $query->query_vars[ $query_vars_check ] ) ) {
			return;
		}
		$original_language = weglot_get_original_language();
		$current_language  = weglot_get_current_language();

		if ( $original_language === $current_language ) {
			return;
		}

		try {
            $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
            $toTranslateLanguageIso = ($key = array_search($current_language,$language_code_rewrited)) ? $key:$current_language;

			$parser           = $this->parser_services->get_parser();
			$this->old_search = $query->query_vars['s'];
			$this->new_search = $parser->translate( $query->query_vars[ 's' ], $toTranslateLanguageIso, $original_language ); //phpcs:ignore

			if ( empty( $this->new_search ) ) {
				return;
			}

			set_query_var( $query_vars_check, $this->new_search );
		} catch ( \Exception $th ) {
			return;
		}
	}

	/**
	 * @since 2.4.0
	 * @param string $string
	 * @return string
	 */
	public function get_search_query_translate( $string ) {
		return ($this->old_search) ? $this->old_search : $string;
	}
}
