<?php
/**
 * Helper class for font settings.
 *
 * @package     CartFlows
 * @author      CartFlows
 * @copyright   Copyright (c) 2018, CartFlows
 * @link        https://cartflows.com/
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font info class for System and Google fonts.
 */
if ( ! class_exists( 'CartFlows_Font_Families' ) ) :

	/**
	 * Font info class for System and Google fonts.
	 */
	final class CartFlows_Font_Families {

		/**
		 * System Fonts
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public static $system_fonts = array();

		/**
		 * Google Fonts
		 *
		 * @since 1.0.0
		 * @var array
		 */
		public static $google_fonts = array();

		/**
		 * Get System Fonts
		 *
		 * @since 1.0.0
		 *
		 * @return Array All the system fonts in CartFlows
		 */
		public static function get_system_fonts() {
			if ( empty( self::$system_fonts ) ) {
				self::$system_fonts = array(
					'Helvetica' => array(
						'fallback' => 'Verdana, Arial, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Verdana'   => array(
						'fallback' => 'Helvetica, Arial, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Arial'     => array(
						'fallback' => 'Helvetica, Verdana, sans-serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Times'     => array(
						'fallback' => 'Georgia, serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Georgia'   => array(
						'fallback' => 'Times, serif',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
					'Courier'   => array(
						'fallback' => 'monospace',
						'variants' => array(
							'300',
							'400',
							'700',
						),
					),
				);
			}

			return apply_filters( 'cartflows_system_fonts', self::$system_fonts );
		}

		/**
		 * Custom Fonts
		 *
		 * @since 1.0.0
		 *
		 * @return Array All the custom fonts in CartFlows
		 */
		public static function get_custom_fonts() {
			$custom_fonts = array();

			return apply_filters( 'cartflows_custom_fonts', $custom_fonts );
		}

		/**
		 * Google Fonts used in CartFlows.
		 * Array is generated from the google-fonts.json file.
		 *
		 * @since 1.0.0
		 *
		 * @return Array Array of Google Fonts.
		 */
		public static function get_google_fonts() {

			if ( empty( self::$google_fonts ) ) {

				$google_fonts_file = CARTFLOWS_DIR . 'classes/fields/typography/google-fonts.json';

				if ( ! file_exists( $google_fonts_file ) ) {
					return array();
				}

				global $wp_filesystem;
				if ( empty( $wp_filesystem ) ) {
					require_once ABSPATH . '/wp-admin/includes/file.php';
					WP_Filesystem();
				}

				$file_contants     = $wp_filesystem->get_contents( $google_fonts_file );
				$google_fonts_json = json_decode( $file_contants, 1 );

				if ( is_array( $google_fonts_json ) || is_object( $google_fonts_json ) ) {

					foreach ( $google_fonts_json as $key => $font ) {
						$name = key( $font );
						foreach ( $font[ $name ] as $font_key => $single_font ) {

							if ( 'variants' === $font_key ) {

								foreach ( $single_font as $variant_key => $variant ) {

									if ( 'regular' == $variant ) {
										$font[ $name ][ $font_key ][ $variant_key ] = '400';
									}
								}
							}

							self::$google_fonts[ $name ] = array_values( $font[ $name ] );
						}
					}
				}
			}

			return apply_filters( 'cartflows_google_fonts', self::$google_fonts );
		}

		/**
		 * Render Fonts
		 *
		 * @param array $post_id  post ID.
		 * @return void
		 */
		public static function render_fonts( $post_id ) {

			$google_font_url = get_post_meta( $post_id, 'wcf-field-google-font-url', true );

			// @todo Avoid the URL generator from the JS and remove the below static URL check condition.
			if ( empty( $google_font_url ) || '//fonts.googleapis.com/css?family=' == $google_font_url ) {
				return;
			}

			wp_enqueue_style( 'cartflows-google-fonts', esc_url( $google_font_url ), array(), CARTFLOWS_VER, 'all' );
		}

		/**
		 * Get string between
		 *
		 * @param  string $string Input string.
		 * @param  string $start  First string.
		 * @param  string $end    Last string.
		 * @return string         string.
		 */
		public static function get_string_between( $string, $start, $end ) {
			$string = ' ' . $string;
			$ini    = strpos( $string, $start );
			if ( 0 == $ini ) {
				return '';
			}
			$ini += strlen( $start );
			$len  = strpos( $string, $end, $ini ) - $ini;
			return substr( $string, $ini, $len );
		}

		/**
		 * Google Font URL
		 * Combine multiple google font in one URL
		 *
		 * @link https://shellcreeper.com/?p=1476
		 * @param array $fonts      Google Fonts array.
		 * @param array $subsets    Font's Subsets array.
		 *
		 * @return string
		 */
		public static function google_fonts_url( $fonts, $subsets = array() ) {

			/* URL */
			$base_url  = '//fonts.googleapis.com/css';
			$font_args = array();
			$family    = array();

			$fonts = apply_filters( 'cartflows_google_fonts', $fonts );

			/* Format Each Font Family in Array */
			foreach ( $fonts as $font_name => $font_weight ) {
				$font_name = str_replace( ' ', '+', $font_name );
				if ( ! empty( $font_weight ) ) {
					if ( is_array( $font_weight ) ) {
						$font_weight = implode( ',', $font_weight );
					}
					$font_family = explode( ',', $font_name );
					$font_family = str_replace( "'", '', wcf_get_prop( $font_family, 0 ) );
					$family[]    = trim( $font_family . ':' . urlencode( trim( $font_weight ) ) );//phpcs:ignore
				} else {
					$family[] = trim( $font_name );
				}
			}

			/* Only return URL if font family defined. */
			if ( ! empty( $family ) ) {

				/* Make Font Family a String */
				$family = implode( '|', $family );

				/* Add font family in args */
				$font_args['family'] = $family;

				/* Add font subsets in args */
				if ( ! empty( $subsets ) ) {

					/* format subsets to string */
					if ( is_array( $subsets ) ) {
						$subsets = implode( ',', $subsets );
					}

					$font_args['subset'] = urlencode( trim( $subsets ) );//phpcs:ignore
				}
				return add_query_arg( $font_args, $base_url );
			}

			return '';
		}

		/**
		 * Generate Google Font URL from the post meta.
		 *
		 * @param  integer $post_id Post ID.
		 * @return string            Google URL if post meta is set.
		 */
		public function generate_google_url( $post_id ) {

			$font_weight = array();
			$fields      = get_post_meta( $post_id );

			foreach ( $fields as $key => $value ) {
				if ( false !== strpos( $key, 'font-family' ) ) {
					$font_family               = ! empty( $value[0] ) ? self::get_string_between( $value[0], '\'', '\'' ) : '';
					$font_list[ $font_family ] = array();
				}
			}

			$google_fonts = array();
			$font_subset  = array();

			$system_fonts     = self::get_system_fonts();
			$get_google_fonts = self::get_google_fonts();

			$variants = array( 'variants' => array( 400 ) );
			foreach ( $font_list as $name => $font ) {
				if ( ! empty( $name ) && ! isset( $system_fonts[ $name ] ) ) {

					if ( isset( $get_google_fonts[ $name ] ) ) {
						$variants = $get_google_fonts[ $name ][0];
					}

					// Add font variants.
					$google_fonts[ $name ] = $variants;

					// Add Subset.
					$subset = apply_filters( 'cartflows_font_subset', '', $name );
					if ( ! empty( $subset ) ) {
						$font_subset[] = $subset;
					}
				}
			}

			return self::google_fonts_url( $google_fonts, $font_subset );
		}
	}

endif;
