<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;

/**
 * @since 2.3.0
 */
class Email_Translate_Service_Weglot {

	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
		$this->parser_services           = weglot_get_service( 'Parser_Service_Weglot' );
	}


	/**
	 * Translate email with parser
	 * @version 2.3.0
	 * @param array $args
	 * @param string $language
	 * @return array
	 */
	public function translate_email( $args, $language ) {
		$api_key            = $this->option_services->get_option( 'api_key' );

        $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
        $toTranslateLanguageIso = ($key = array_search($language,$language_code_rewrited)) ? $key:$language;

		if ( ! $api_key ) {
			return $args;
		}

		try {
			$original_language  = weglot_get_original_language();
			$exclude_blocks     = $this->option_services->get_exclude_blocks();

			$config             = new ServerConfigProvider();
			$client             = $this->parser_services->get_client();
			$parser             = new Parser( $client, $config, $exclude_blocks );
			$translated_subject = $parser->translate( '<p>' . $args['subject'] . '</p>', $original_language, $toTranslateLanguageIso ); //phpcs:ignore

			$config             = new ServerConfigProvider();
			$client             = $this->parser_services->get_client();
			$parser             = new Parser( $client, $config, $exclude_blocks );
			$translated_message = $parser->translate( $args['message'], $original_language, $toTranslateLanguageIso ); //phpcs:ignore

			return [
				'subject' => $translated_subject,
				'message' => $translated_message,
			];
		} catch ( \Exception $e ) {
			return $args;
		}
	}
}



