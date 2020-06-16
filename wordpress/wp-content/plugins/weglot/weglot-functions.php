<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a service Weglot
 * @since 2.0
 *
 * @param string $service
 * @return object
 */
function weglot_get_service( $service ) {
	return Context_Weglot::weglot_get_context()->get_service( $service );
}

/**
 * Get all options
 * @since 2.0
 *
 * @return array
 */
function weglot_get_options() {
	return Context_Weglot::weglot_get_context()->get_service( 'Option_Service_Weglot' )->get_options();
}

/**
 * Get option
 * @since 2.0
 * @param string $key
 * @return any
 */
function weglot_get_option( $key ) {
	return Context_Weglot::weglot_get_context()->get_service( 'Option_Service_Weglot' )->get_option( $key );
}

/**
 * Get original language
 * @since 2.0
 * @return string
 */
function weglot_get_original_language() {
	return weglot_get_option( 'original_language' );
}

/**
 * Get current language
 * @since 2.0
 * @return string
 */
function weglot_get_current_language() {
	return Context_Weglot::weglot_get_context()->get_service( 'Request_Url_Service_Weglot' )->get_current_language();
}

/**
 * Get destination language with filters
 * @since 2.0
 * @return string
 */
function weglot_get_destination_languages() {
	return Context_Weglot::weglot_get_context()->get_service( 'Option_Service_Weglot' )->get_destination_languages();
}

/**
 * @since 2.3.0
 * @return array
 */
function weglot_get_all_languages_configured() {
	$destinations   = weglot_get_destination_languages();
	$original       = weglot_get_original_language();
	array_unshift( $destinations, $original );
	return $destinations;
}

/**
 * Get Request Url Service
 * @since 2.0
 * @return Request_Url_Service_Weglot
 */
function weglot_get_request_url_service() {
	return Context_Weglot::weglot_get_context()->get_service( 'Request_Url_Service_Weglot' );
}

/**
 * Get an array with current and original language
 * @since 2.0
 * @return array
 */
function weglot_get_current_and_original_language() {
	return [
		'current'  => weglot_get_current_language(),
		'original' => weglot_get_original_language(),
	];
}

/**
 * Get languages available on Weglot
 * @since 2.0
 * @return array
 */
function weglot_get_languages_available() {
	return Context_Weglot::weglot_get_context()->get_service( 'Language_Service_Weglot' )->get_languages_available();
}

/**
 * @since 2.0
 *
 * @param null|string $type
 * @return array
 */
function weglot_get_languages_configured( $type = null ) {
	return Context_Weglot::weglot_get_context()->get_service( 'Language_Service_Weglot' )->get_languages_configured( $type );
}

/**
 * Get button selector HTML
 * @since 2.0
 * @param string $add_class
 * @return string
 */
function weglot_get_button_selector_html( $add_class = '' ) {
	return Context_Weglot::weglot_get_context()->get_service( 'Button_Service_Weglot' )->get_html( $add_class );
}


/**
 * Get exclude urls
 * @since 2.0
 * @return array
 */
function weglot_get_exclude_urls() {
	return Context_Weglot::weglot_get_context()->get_service( 'Option_Service_Weglot' )->get_exclude_urls();
}

/**
 * Get translate AMP option
 * @since 2.0
 * @return bool
 */
function weglot_get_translate_amp_translation() {
	return Context_Weglot::weglot_get_context()->get_service( 'Option_Service_Weglot' )->get_option_custom_settings( 'translate_amp' );
}

/**
 * Get current full url
 * @since 2.0
 * @return string
 */
function weglot_get_current_full_url() {
	return weglot_create_url_object( weglot_get_request_url_service()->get_full_url() )->getForLanguage( weglot_get_current_language() );
}

/**
 * Is eligible url
 * @since 2.0
 * @param string $url
 * @return boolean
 */
function weglot_is_eligible_url( $url ) {
	return Context_Weglot::weglot_get_context()->get_service( 'Request_Url_Service_Weglot' )->is_eligible_url( $url );
}

/**
 * Get API KEY Weglot
 * @since 2.0
 * @version 3.0.0
 * @return string
 */
function weglot_get_api_key() {
	return weglot_get_option( 'api_key_private' );
}

/**
 * Get auto redirect option
 * @since 2.0
 * @return boolean
 */
function weglot_has_auto_redirect() {
	return weglot_get_option( 'auto_redirect' );
}

/**
 * @since 2.0.2
 * @return boolean
 */
function weglot_current_url_is_eligible() {
	$full_url = weglot_get_full_url_no_language();
	return weglot_is_eligible_url( $full_url );
}


/**
 * @since 2.0.4
 * @return string
 */
function weglot_get_full_url_no_language() {
	return weglot_get_request_url_service()->get_full_url_no_language();
}

/**
 * @since 2.0.4
 * @param string $url
 * @return Weglot\Util\Url
 */
function weglot_create_url_object( $url ) {
	return weglot_get_request_url_service()->create_url_object( $url );
}

/**
 * @since 2.0.4
 * @return int
 */
function weglot_get_postid_from_url() {
	return url_to_postid( weglot_get_full_url_no_language() ); //phpcs:ignore
}

/**
 * Get option destination language
 * @since 2.0
 * @version 2.0.4
 * @return array
 */
function weglot_get_destination_language() {
	return weglot_get_option( 'destination_language' );
}

/**
 * @since 2.3.0
 *
 * @return string
 */
function weglot_get_private_languages() {
	return weglot_get_option( 'private_mode' );
}


/**
 * @since 2.4.0
 * @return string
 */
function weglot_get_rest_current_url_path() {
	$prefix      = rest_get_url_prefix();
	$current_url = wp_parse_url( add_query_arg( [] ) );
	return apply_filters( 'weglot_get_rest_current_url_path', $current_url['path'] );
}
