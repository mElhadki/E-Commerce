<?php

namespace WeglotWP\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_Excluded_Type;
use WeglotWP\Helpers\Helper_Flag_Type;

class Schema_Option_Migration_V3 {


	/**
	 * @since 3.0.0
	 * @return array
	 */
	public static function get_schema_migration_options_v3() {
		$schema = [
			'api_key_private'         => 'api_key',
			'allowed'                 => 'allowed',
			'language_from'           => 'original_language',
			'languages'               => function( $options ) {
				$destinations = [];
				foreach ( $options['destination_language'] as $item ) {
					$destinations[] = [
						'language_to'                   => $item,
						'enabled'                       => isset( $options['private_mode'][ $item ] ) && ! $options['private_mode'][ $item ],
						'automatic_translation_enabled' => true,
					];
				}

				return $destinations;
			},
			'excluded_blocks' => (object) [
				'path' => 'exclude_blocks',
				'fn'   => function( $blocks ) {
					$objects = [];
					foreach ( $blocks as $item ) {
						$objects[] = [
							'value' => $item,
						];
					}
					return $objects;
				},
			],
			'excluded_paths' => (object) [
				'path' => 'exclude_urls',
				'fn'   => function( $urls ) {
					$objects = [];
					foreach ( $urls as $item ) {
						$objects[] = [
							'type'  => Helper_Excluded_Type::MATCH_REGEX,
							'value' => $item,
						];
					}
					return $objects;
				},
			],
			'auto_switch'          => (object) [
				'path' => 'auto_redirect',
				'fn'   => function( $auto_redirect ) {
					return (bool) $auto_redirect;
				},
			],
			'auto_switch_fallback' => 'original_language',
			'custom_settings'      => function( $options ) {
				return [
					'translate_email'  => (bool) $options['email_translate'],
					'translate_amp'    => (bool) $options['translate_amp'],
					'translate_search' => (bool) $options['active_search'],
					'button_style'     => [
						'is_dropdown' => (bool) $options['is_dropdown'],
						'full_name'   => (bool) $options['is_fullname'],
						'with_name'   => (bool) $options['with_name'],
						'with_flags'  => (bool) $options['with_flags'],
						'flag_type'   => Helper_Flag_Type::get_flag_type_with_number( $options['flag_type'] ),
						'custom_css'  => $options['override_css'],
					],
				];
			},

		];

		return $schema;
	}
}
