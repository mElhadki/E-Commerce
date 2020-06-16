<?php

namespace WeglotWP\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 3.0.0
 */
class Helper_Flag_Type {

	/**
	 * @var string
	 */
	const RECTANGLE_MAT = 'rectangle_mat';

	/**
	 * @var string
	 */
	const SHINY = 'shiny';

	/**
	 * @var string
	 */
	const SQUARE = 'square';

	/**
	 * @var string
	 */
	const CIRCLE = 'circle';

	/**
	 * @since 3.0.0
	 * @return array
	 */
	public static function get_flags_type() {
		return [
			RECTANGLE_MAT,
			SHINY,
			SQUARE,
			CIRCLE,
		];
	}

	/**
	 * @since 3.0.0
	 * @param string|int $number
	 * @return string
	 */
	public static function get_flag_type_with_number( $number ) {
		switch ( (int) $number ) {
			case 0:
				return self::RECTANGLE_MAT;
				break;
			case 1:
				return self::SHINY;
				break;
			case 2:
				return self::SQUARE;
				break;
			case 3:
				return self::CIRCLE;
				break;
		}
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public static function get_flag_number_with_type( $type ) {
		switch ( $type ) {
			case self::RECTANGLE_MAT:
				return 0;
			case self::SHINY:
				return 1;
			case self::SQUARE:
				return 2;
			case self::CIRCLE:
				return 3;
		}
	}
}
