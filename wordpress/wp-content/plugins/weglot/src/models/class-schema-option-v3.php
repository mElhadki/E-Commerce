<?php

namespace WeglotWP\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Flag_Type;

class Schema_Option_V3 {

	/**
	 * @since 3.0.0
	 * @return array
	 */
	public static function get_schema_options_v3_compatible() {
		$schema = [
			'api_key'                      => 'api_key',
			'api_key_private'              => 'api_key_private',
			'allowed'                      => 'allowed',
			'original_language'            => 'language_from',
			'translation_engine'           => 'translation_engine',
			'destination_language'         => (object) [
				'path' => 'languages',
				'fn'   => function( $languages ) {
					$destinations = [];
					if( !$languages ){
						return $destinations;
					}
					foreach ( $languages as $item ) {
						$destinations[] = $item['language_to'];
					}

					return $destinations;
				},
			],
			'private_mode'         => (object) [
				'path' => 'languages',
				'fn'   => function( $languages ) {
					$private = [ 'active' => false ];
					if( !$languages ){
						return $private;
					}
					foreach ( $languages as $item ) {
						if ( ! $item['enabled'] ) {
							$private[ $item['language_to'] ] = true;
							$private['active'] = true;
						} else {
							$private[ $item['language_to'] ] = false;
						}
					}

					return $private;
				},
			],
			'auto_redirect'                => 'auto_switch',
			'autoswitch_fallback'          => 'auto_switch_fallback',
			'exclude_urls'                 => 'excluded_paths',
			'exclude_blocks'               => (object) [
				'path' => 'excluded_blocks',
				'fn'   => function( $excluded_blocks ) {
					$excluded = [];
					if( !$excluded_blocks ){
						return $excluded;
					}
					foreach ( $excluded_blocks as $item ) {
						$excluded[] = $item['value'];
					}
					return $excluded;
				},
			],
			'custom_settings'    => 'custom_settings',
			'is_dropdown'        => 'custom_settings.button_style.is_dropdown',
			'is_fullname'        => 'custom_settings.button_style.full_name',
			'with_name'          => 'custom_settings.button_style.with_name',
			'with_flags'         => 'custom_settings.button_style.with_flags',
			'type_flags'         => (object) [
				'path' => 'custom_settings.button_style.flag_type',
				'fn'   => function( $flag_type ) {
					if ( $flag_type ) {
						return $flag_type;
					}

					return Helper_Flag_Type::RECTANGLE_MAT;
				},
			],
			'override_css'            => 'custom_settings.button_style.custom_css',
			'email_translate'         => 'custom_settings.translate_email',
			'active_search'           => 'custom_settings.translate_search',
			'translate_amp'           => 'custom_settings.translate_amp',
			'has_first_settings'      => 'has_first_settings',
			'show_box_first_settings' => 'show_box_first_settings',
			'custom_urls'             => (object) [
				'path' => 'custom_urls',
				'fn'   => function( $custom_urls ) {
					if ( $custom_urls ) {
						return $custom_urls;
					}

					return [];
				},
			],
			'flag_css'                => 'flag_css',
			'menu_switcher'           => 'menu_switcher',
			'active_wc_reload'        => 'active_wc_reload',
		];

		return $schema;
	}
}
