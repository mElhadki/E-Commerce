<?php

namespace WeglotWP\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @since 2.0
 */
abstract class Helper_Json_Inline_Weglot {

	/**
	 * @since 2.0
	 * @param string $string
	 * @return string
	 */
	public static function format_for_api( $string ) {
		$string = '"' . $string . '"';
		return \json_decode( str_replace( '\\/', '/', str_replace( '\\\\', '\\', $string ) ) );
	}

	/**
	 * @since 2.0
	 * @param string $string
	 * @return string
	 */
	public static function unformat_from_api( $string ) {
		$string = str_replace( '"', '', str_replace( '/', '\\\\/', str_replace( '\\u', '\\\\u', \json_encode( $string ) ) ) ); //phpcs:ignore
		return $string;
	}

	/**
	 * @since 2.5.0
	 * @param string $string
	 * @return string
	 */
	public static function need_json_encode_api( $string ) {
		if ( strip_tags( $string ) !== $string ) { // Is HTML
			$str = \json_encode( $string ); //phpcs:ignore
			return trim( $str, '"' );
		}

		return str_replace( '"', '', \json_encode( $string ) ); //phpcs:ignore
	}

	/**
	 * @since 2.3.0
	 *
	 * @param string $string
	 * @return boolean
	 */
	public static function is_json( $string ) {
		return is_string( $string ) && is_array( \json_decode( $string, true ) ) && ( JSON_ERROR_NONE === \json_last_error() ) ? true : false;
	}
}
