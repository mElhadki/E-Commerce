<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Parser\Parser;
use Weglot\Util\SourceType;
use WeglotWP\Helpers\Helper_Replace_Url_Weglot;

/**
 * Replace URL
 *
 * @since 2.0
 */
class Replace_Url_Service_Weglot {

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->request_url_services = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->replace_link_service = weglot_get_service( 'Replace_Link_Service_Weglot' );
	}

	/**
	 * @since 2.3.0
	 *
	 * @param string $dom
	 * @return string
	 */
	public function replace_link_in_dom( $dom ) {
		$data = Helper_Replace_Url_Weglot::get_replace_modify_link();

		foreach ( $data as $key => $value ) {
			$dom = $this->modify_link( $value, $dom, $key );
		}

		$current_language       = weglot_get_current_language();
		$language_code_rewrited = array_search( $current_language, apply_filters( 'weglot_language_code_replace', array() ) );

		if ( ! empty( $language_code_rewrited ) ) {
			$dom = preg_replace( '/<html (.*?)?lang=(\"|\')(\S*)(\"|\')/', '<html $1lang=$2' . $current_language . '$4 weglot-lang=$2' . $language_code_rewrited . '$4', $dom );
		} else {
			$dom = preg_replace( '/<html (.*?)?lang=(\"|\')(\S*)(\"|\')/', '<html $1lang=$2' . $current_language . '$4', $dom );
		}

		$dom = preg_replace( '/property="og:locale" content=(\"|\')(\S*)(\"|\')/', 'property="og:locale" content=$1' . $current_language . '$3', $dom );

		return apply_filters( 'weglot_replace_link', $dom );
	}

	public function replace_link_in_json( $json ) {

		$replace_urls = apply_filters( 'weglot_ajax_replace_urls', [ 'redirecturl', 'url', 'link' ] );
		foreach ( $json as $key => $val ) {
			if ( is_array( $val ) ) {
				$json[ $key ] = $this->replace_link_in_json( $val );
			} else {
				if ( Parser::getSourceType( $val ) == SourceType::SOURCE_HTML ) {
					$json[ $key ] = $this->replace_link_in_dom( $val );
				} else {
					if ( in_array( $key, $replace_urls, true ) && $this->check_link( $val ) ) {
						$json[ $key ] = $this->replace_link_service->replace_url( $val );
					}
				}
			}
		}

		return $json;
	}

	/**
	 * Replace link
	 *
	 * @param string $pattern
	 * @param string $translated_page
	 * @param string $type
	 * @return string
	 */
	public function modify_link( $pattern, $translated_page, $type ) {
		$current_language = weglot_get_current_language();
		preg_match_all( $pattern, $translated_page, $out, PREG_PATTERN_ORDER );
		$count_out_0 = count( $out[0] );
		for ( $i = 0;$i < $count_out_0; $i++ ) {
			$sometags    = ( isset( $out[1] ) ) ? $out[1][ $i ] : null;
			$quote1      = ( isset( $out[2] ) ) ? $out[2][ $i ] : null;
			$current_url = ( isset( $out[3] ) ) ? $out[3][ $i ] : null;
			$quote2      = ( isset( $out[4] ) ) ? $out[4][ $i ] : null;
			$sometags2   = ( isset( $out[5] ) ) ? $out[5][ $i ] : null;

			$length_link = apply_filters( 'weglot_length_replace_a', 1500 ); // Prevent error on long URL (preg_match_all Compilation failed: regular expression is too large at offset)
			if ( strlen( $current_url ) >= $length_link ) {
				continue;
			}

			if ( ! $this->check_link( $current_url, $sometags, $sometags2 ) ) {
				continue;
			}

			$function_name = apply_filters( 'weglot_modify_link_replace_function', 'replace_' . $type, $type );

			if ( method_exists( $this->replace_link_service, $function_name ) ) {
				$translated_page = $this->replace_link_service->$function_name(
					$translated_page,
					$current_url,
					$quote1,
					$quote2,
					$sometags,
					$sometags2
				);
			} else {
				if ( function_exists( $function_name ) ) {
					$translated_page = $function_name( $translated_page, $current_url, $quote1, $quote2, $sometags, $sometags2 );
				}
			}
		}

		return $translated_page;
	}

	/**
	 * @since 2.0
	 * @param string $current_url
	 * @param string $sometags
	 * @param string $sometags2
	 * @return string
	 */
	public function check_link( $current_url, $sometags = null, $sometags2 = null ) {
		$admin_url   = admin_url();
		$parsed_url  = wp_parse_url( $current_url );
		$server_host = apply_filters( 'weglot_check_link_server_host', $_SERVER['HTTP_HOST'] ); //phpcs:ignore

		return (
			(
				( 'h' === $current_url[0] && $parsed_url['host'] === $server_host ) ||
				( isset( $current_url[0] ) && $current_url[0] === '/' && ( !isset( $current_url[1]) || ( isset( $current_url[1] ) ) && '/' !== $current_url[1] )) //phpcs:ignore
			) &&
			strpos( $current_url, $admin_url ) === false
			&& strpos( $current_url, 'wp-login' ) === false
			&& ! $this->is_link_a_file( $current_url )
			&& $this->request_url_services->is_eligible_url( $current_url )
			&& strpos( $sometags, 'data-wg-notranslate' ) === false
			&& strpos( $sometags2, 'data-wg-notranslate' ) === false
		);
	}

	/**
	 * @since 2.0
	 *
	 * @param string $current_url
	 * @return boolean
	 */
	public function is_link_a_file( $current_url ) {
		$files = [
			'pdf',
			'rar',
			'doc',
			'docx',
			'jpg',
			'jpeg',
			'png',
			'ppt',
			'pptx',
			'xls',
			'zip',
			'mp4',
			'xlsx',
		];

		foreach ( $files as $file ) {
			if ( self::ends_with( $current_url, '.' . $file ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * search forward starting from end minus needle length characters
	 * @since 2.0
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public function ends_with( $haystack, $needle ) {
		$temp       = strlen( $haystack );
		$len_needle = strlen( $needle );

		return '' === $needle ||
		(
			( $temp - $len_needle ) >= 0 && strpos( $haystack, $needle, $temp - $len_needle ) !== false
		);
	}
}
