<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use Weglot\Client\Endpoint\Languages;
use Weglot\Client\Client;
use Weglot\Client\Api\LanguageCollection;
use Weglot\Client\Factory\Languages as LanguagesFactory;

/**
 * Language service
 *
 * @since 2.0
 */
class Language_Service_Weglot {
	protected $languages = null;

	/**
	 * @since 2.0
	 */
	public function __construct() {
		$this->option_services = weglot_get_service( 'Option_Service_Weglot' );
	}

	/**
	 * @since 2.0.6
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	protected function compare_language( $a, $b ) {
		return strcmp( $a['english'], $b['english'] );
	}

	/**
	 * Get languages available from API
	 * @since 2.0
	 * @version 2.0.6
	 * @param array $params
	 * @return array
	 */
	public function get_languages_available( $params = [] ) {
		if ( null !== $this->languages ) {
			return $this->languages;
		}

		$client           = weglot_get_service( 'Parser_Service_Weglot' )->get_client();

		$languages        = new Languages( $client );
		$this->languages  = $languages->handle();

		if ( isset( $params['sort'] ) && $params['sort'] ) {
			$this->languages = $this->languages->jsonSerialize();
			usort( $this->languages, [ $this, 'compare_language' ] );

			$language_collection = new LanguageCollection();

			foreach ( $this->languages as $language ) {
				$factory = new LanguagesFactory( $language );
				$language_collection->addOne( $factory->handle() );
			}

			$this->languages = $language_collection;
		}

		return $this->languages;
	}

	/**
	 * Get language entry
	 * @since 2.0
	 * @param string $key_code
	 * @return array
	 */
	public function get_language( $key_code ) {

        $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
		$language = ($key = array_search($key_code,$language_code_rewrited)) ? $this->get_languages_available()[ $key ]:$this->get_languages_available()[ $key_code ];
		return $language;
	}

	/**
	 * @since 2.0
	 * @return array
	 * @param null|string $type
	 */
	public function get_languages_configured( $type = null ) {
		$languages = [];
		$original_language = weglot_get_original_language();

		if( $original_language ){
			$languages[] = $original_language;
		}
		$languages        = array_merge( $languages, weglot_get_destination_languages() );

		$languages_object = [];
		foreach ( $languages as $language ) {
			switch ( $type ) {
				case 'code':
					$languages_object[] = $this->get_language( $language )->getIso639();
					break;
				default:
					$languages_object[] = $this->get_language( $language );
					break;
			}
		}
		return $languages_object;
	}

	/**
	 * @since 2.3.0
	 *
	 * @param string $key_code
	 * @return LanguageEntry
	 */
	public function get_current_language_entry_from_key( $key_code ) {

		$languages = $this->get_languages_available();
        $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
		if($key = array_search($key_code,$language_code_rewrited))
            $key_code = $key;

		if ( isset( $languages[ $key_code ] ) ) {
			$current_language_entry = $languages[ $key_code ];
		} else {
			$current_language_entry = apply_filters( 'weglot_current_language_entry', $key_code );
			if ( $current_language_entry === $key_code ) {
				throw new \Exception( 'You need create a language entry' );
			}
		}

		return $current_language_entry;
	}
}
