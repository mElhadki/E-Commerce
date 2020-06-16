<?php

namespace WeglotWP\Actions\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Models\Hooks_Interface_Weglot;
use WeglotWP\Helpers\Helper_Post_Meta_Weglot;

/**
 *
 * @since 2.1.0
 */
class Metabox_Url_Translate_Weglot implements Hooks_Interface_Weglot {
	protected $old_post_name = null;
	protected $new_post_name = null;

	/**
	 * @since 2.1.0
	 */
	public function __construct() {
		$this->language_services   = weglot_get_service( 'Language_Service_Weglot' );
		$this->option_services     = weglot_get_service( 'Option_Service_Weglot' );
	}

	/**
	 * @see Hooks_Interface_Weglot
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes_url_translate' ] );
		add_action( 'save_post', [ $this, 'save_post_meta_boxes_url_translate' ], 10, 3 );
		add_action( 'wp_ajax_weglot_post_name', [ $this, 'weglot_post_name' ] );
		add_action( 'wp_ajax_weglot_reset_custom_url', [ $this, 'weglot_reset_custom_url' ] );

		add_filter( 'wp_insert_post_data', [ $this, 'weglot_wp_insert_post_data' ], 10, 2 );
		add_filter( 'wp_unique_post_slug', [ $this, 'weglot_wp_unique_post_slug' ] );
	}

	/**
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function weglot_reset_custom_url() {
		if (
			$_SERVER['REQUEST_METHOD'] !== 'POST' || //phpcs:ignore
			! isset( $_POST['code_lang'] ) || //phpcs:ignore
			! isset( $_POST['custom_url'] ) || //phpcs:ignore
			! isset( $_POST['id'] ) //phpcs:ignore
		) {
			wp_send_json_error( [
				'code'    => 'missing_parameter',
			] );
			return;
		}

		$code_lang          = sanitize_text_field( wp_unslash( $_POST['code_lang'] ) ); //phpcs:ignore
		$post_name_weglot   = sanitize_text_field( wp_unslash( $_POST['custom_url'] ) ); //phpcs:ignore

		$custom_urls  = $this->option_services->get_option( 'custom_urls' );

		if ( isset( $custom_urls[ $code_lang ] ) && isset( $custom_urls[ $code_lang ][ $post_name_weglot ] ) ) {
			unset( $custom_urls[ $code_lang ] [ $post_name_weglot ] );
			$this->option_services->set_option_by_key( 'custom_urls', $custom_urls );
		}

		$post = get_post( $_POST['id'] ); //phpcs:ignore

		wp_send_json_success( [
			'result' => [
				'slug' => $post->post_name,
			],
		] );
	}

	/**
	 * Improve wp_unique_post_slug with custom URLs weglot
	 * @since 2.1.0
	 * @param string $slug
	 * @param array $custom_urls
	 * @param integer $suffix
	 * @return string
	 */
	protected function search_unique_key_post_name( $slug, $custom_urls, $suffix = 2 ) {
		foreach ( $custom_urls as $key_code => $urls ) {
			if ( isset( $urls[ $slug ] ) ) {
				$alt_post_name      = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				return $this->search_unique_key_post_name( $alt_post_name, $custom_urls, ++$suffix );
			}
		}

		return $slug;
	}


	/**
	 * Filters the unique post slug.
	 *
	 * @since 2.1.0
	 *
	 * @param string $slug          The post slug.
	 */
	public function weglot_wp_unique_post_slug( $slug ) {
		$custom_urls = $this->option_services->get_option( 'custom_urls' );

		return $this->search_unique_key_post_name( $slug, $custom_urls );
	}

	/**
	 * @since 2.1.0
	 * @return void
	 */
	public function weglot_post_name() {
		$weglot_post_name = ( isset( $_POST['post_name'] ) && ! empty( $_POST['post_name'] ) ) ? sanitize_title( $_POST['post_name'] ) : null ; //phpcs:ignore
		$code_language    = ( isset( $_POST['lang'] ) && ! empty( $_POST['lang'] ) ) ? sanitize_text_field( $_POST['lang'] ) : null ; //phpcs:ignore
		$post_id          = ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) ? sanitize_text_field( $_POST['id'] ) : null ; //phpcs:ignore

		$custom_urls = $this->option_services->get_option( 'custom_urls' );

		if ( ! $weglot_post_name || ! $code_language || ! $post_id ) {
			wp_send_json_error( [
				'success' => false,
				'code'    => 'missing_parameter',
			] );
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			wp_send_json_error( [
				'success' => false,
			] );
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			wp_send_json_error( [
				'success' => false,
			] );
			return;
		}

		$post             = get_post( $post_id );
		if ( $post->post_name === $weglot_post_name ) {
			if ( in_array( $post->post_name, $custom_urls[ $code_language ] ) ) { // phpcs:ignore
				$key_post_name = array_search( $post->post_name, $custom_urls[ $code_language ] );// phpcs:ignore
				unset( $custom_urls[ $code_language ][ $key_post_name ] );

				$this->option_services->set_option_by_key( 'custom_urls', $custom_urls );

				wp_send_json_success( [
					'success' => true,
					'result'  => [
						'slug' => $weglot_post_name,
					],
				] );
				return;
			}

			wp_send_json_success( [
				'code'     => 'same_post_name',
			] );

			return;
		}

		if ( isset( $custom_urls[ $code_language ] ) ) {
			// Same use
			if ( isset( $custom_urls[ $code_language ][ $weglot_post_name ] ) && $custom_urls[ $code_language ][ $weglot_post_name ] === $post->post_name ) {
				wp_send_json_success( [
					'code'     => 'same_post_name',
				] );
				return;
			}

			if ( in_array( $post->post_name, $custom_urls[ $code_language ] ) ) { // phpcs:ignore
				$key_post_name = array_search( $post->post_name, $custom_urls[ $code_language ] );// phpcs:ignore
				unset( $custom_urls[ $code_language ][ $key_post_name ] );
			}
		}

		$weglot_unique_slug = wp_unique_post_slug( trim( $weglot_post_name ), $post->ID, $post->post_status, $post->post_type, $post->post_parent );

		$custom_urls[ $code_language ] [ $weglot_unique_slug ] = $post->post_name;
		$this->option_services->set_option_by_key( 'custom_urls', $custom_urls );

		wp_send_json_success( [
			'success' => true,
			'result'  => [
				'slug' => $weglot_unique_slug,
			],
		] );
	}

	/**
	 * @since 2.1.0
	 * @return void
	 */
	public function add_meta_boxes_url_translate() {
		global $post;
		if ( ! $post ) {
			return;
		}

		$post_type = get_post_type();

		$exclude_post_type_metabox = apply_filters( 'weglot_url_translate_metabox_post_type_exclude', [
			'attachment',
			'seopress_404',
			'product',
		] );

		if ( in_array( $post_type, $exclude_post_type_metabox ) ) { //phpcs:ignore
			return;
		}

		add_meta_box( 'weglot-url-translate', __( 'Weglot URL Translate', 'weglot' ), [ $this, 'weglot_url_translate_box' ] );
	}

	/**
	 * @since 2.1.0
	 * @return void
	 * @param mixed $post
	 */
	public function weglot_url_translate_box( $post ) {
		if ( ! $post ) {
			return;
		}

		$this->custom_urls = $this->option_services->get_option( 'custom_urls' );

		include_once WEGLOT_TEMPLATES_ADMIN_METABOXES . '/url-translate.php';
	}

	/**
	 * Prevent change post_name
	 * @since 2.1.0
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public function weglot_wp_insert_post_data( $data, $postarr ) {
		$post = get_post( $postarr['ID'] );
		if ( $post ) {
			$this->old_post_name = $post->post_name;
			$this->new_post_name = $data['post_name'];
		}
		return $data;
	}

	/**
	 * @since 2.1.0
	 *
	 * @param mixed $post_id
	 * @return void
	 */
	public function save_post_meta_boxes_url_translate( $post_id, $post, $update ) {

		// Add nonce for security and authentication.
		$post_name_weglot   = isset( $_POST[ Helper_Post_Meta_Weglot::POST_NAME_WEGLOT ] ) ? $_POST[ Helper_Post_Meta_Weglot::POST_NAME_WEGLOT ] : []; //phpcs:ignore

		if ( ! isset( $post_name_weglot ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) || $update ) {
			return;
		}

		$custom_urls   = $this->option_services->get_option( 'custom_urls' );

		// Update new post_name
		if ( $this->old_post_name !== $this->new_post_name ) {
			foreach ( $custom_urls as $key_code => $urls ) {
				$key_search = array_search( $this->old_post_name, $urls ); // phpcs:ignore
				if ( false === $key_search ) {
					continue;
				}

				$custom_urls[ $key_code ][ $key_search ] = $this->new_post_name;
			}
			$this->option_services->set_option_by_key( 'custom_urls', $custom_urls );
		}
	}
}
