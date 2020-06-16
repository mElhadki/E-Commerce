<?php

namespace WeglotWP\Third\Amp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @since 2.0
 */
class Amp_Service_Weglot {

	/**
	 * @since 2.0
	 * @param mixed $with_escape
	 */
	public function get_regex( $with_escape = false ) {
		$regex = '([&\?/])amp(/)?$';
		if ( $with_escape ) {
			$regex = str_replace( '/', '\/', $regex );
		}

		return apply_filters( 'weglot_regex_amp', $regex );
	}
}
