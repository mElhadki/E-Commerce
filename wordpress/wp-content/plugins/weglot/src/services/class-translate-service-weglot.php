<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Client\Api\Exception\ApiError;
use WeglotWP\Helpers\Helper_Json_Inline_Weglot;
use WeglotWP\Helpers\Helper_Keys_Json_Weglot;


/**
 * @since 2.3.0
 */
class Translate_Service_Weglot {


	/**
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->option_services           = weglot_get_service( 'Option_Service_Weglot' );
		$this->request_url_services      = weglot_get_service( 'Request_Url_Service_Weglot' );
		$this->replace_url_services      = weglot_get_service( 'Replace_Url_Service_Weglot' );
		$this->replace_link_services     = weglot_get_service( 'Replace_Link_Service_Weglot' );
		$this->parser_services           = weglot_get_service( 'Parser_Service_Weglot' );
		$this->generate_switcher_service = weglot_get_service( 'Generate_Switcher_Service_Weglot' );
	}


	/**
	 * @since 2.3.0
	 * @return void
	 */
	public function weglot_translate() {
		$this->set_original_language( weglot_get_original_language() );
		$this->set_current_language( $this->request_url_services->get_current_language() );

		ob_start( [ $this, 'weglot_treat_page' ] );
	}

	/**
	 * @since 2.3.0
	 * @param string $current_language
	 */
	public function set_current_language( $current_language ) {
		$this->current_language = $current_language;
		return $this;
	}

	/**
	 * @since 2.3.0
	 * @param string $original_language
	 */
	public function set_original_language( $original_language ) {
		$this->original_language = $original_language;
		return $this;
	}

	/**
	 * @see weglot_init / ob_start
	 * @since 2.3.0
	 * @param string $content
	 * @return string
	 */
	public function weglot_treat_page( $content ) {
		$this->set_current_language( $this->request_url_services->get_current_language() ); // Need to reset

		// Choose type translate
		$type = ( Helper_Json_Inline_Weglot::is_json( $content ) ) ? 'json' : 'html';
		$type = apply_filters( 'weglot_type_treat_page', $type );

		$active_translation = apply_filters( 'weglot_active_translation', true );

		// No need to translate but prepare new dom with button
		if ( $this->current_language === $this->original_language || ! $active_translation ) {
			return $this->weglot_render_dom( $content );
		}

		$parser = $this->parser_services->get_parser();

		try {

		    $language_code_rewrited = apply_filters('weglot_language_code_replace' ,  array());
            $toTranslateLanguageIso = ($key = array_search($this->current_language,$language_code_rewrited)) ? $key:$this->current_language;


			switch ( $type ) {
				case 'json':
					$extraKeys          = apply_filters( 'weglot_add_json_keys', array() );
					$translated_content = $parser->translate( $content, $this->original_language, $toTranslateLanguageIso, $extraKeys );
					$translated_content = json_encode( $this->replace_url_services->replace_link_in_json( json_decode( $translated_content, true ) ) );
					$translated_content = apply_filters( 'weglot_json_treat_page', $translated_content );
					return $translated_content;
				case 'html':
				    $translated_content = $parser->translate( $content, $this->original_language, $toTranslateLanguageIso ); // phpcs:ignore
					$translated_content = apply_filters( 'weglot_html_treat_page', $translated_content );
					return $this->weglot_render_dom( $translated_content );
				default:
					$name_filter = sprintf( 'weglot_%s_treat_page', $type );
					return apply_filters( $name_filter, $content, $parser, $this->original_language, $this->current_language );

			}
		} catch ( ApiError $e ) {
			if ( 'json' !== $type ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', 1 );
				}
				nocache_headers();
				$content .= '<!--Weglot error API : ' . $this->remove_comments( $e->getMessage() ) . '-->';
			}
			return $content;
		} catch ( \Exception $e ) {
			if ( 'json' !== $type ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', 1 );
				}
				nocache_headers();
				$content .= '<!--Weglot error : ' . $this->remove_comments( $e->getMessage() ) . '-->';
			}
			return $content;
		}
	}


	/**
	 * @since 2.3.0
	 *
	 * @param string $html
	 * @return string
	 */
	private function remove_comments( $html ) {
		return preg_replace( '/<!--(.*)-->/Uis', '', $html );
	}


	/**
	 * @since 2.3.0
	 * @param string $dom
	 * @return string
	 */
	public function weglot_render_dom( $dom ) {
		$dom = $this->generate_switcher_service->generate_switcher_from_dom( $dom );

		// We only need this on translated page
		if ( $this->current_language !== $this->original_language ) {
			$dom = $this->replace_url_services->replace_link_in_dom( $dom );
		}

		return apply_filters( 'weglot_render_dom', $dom );
	}
}



