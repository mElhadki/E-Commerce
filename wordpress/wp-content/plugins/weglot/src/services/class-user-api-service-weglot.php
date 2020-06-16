<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WeglotWP\Helpers\Helper_API;

/**
 * @since 2.0
 */
class User_Api_Service_Weglot {
	protected $user_info = null;

	/**
	 * @since 2.0
	 * @return array
	 */
	public function get_plans() {
		return [
			'starter_free' => [
				'ids'            => [ 2, 3 ],
				'limit_language' => 1,
			],
			'business' => [
				'ids'            => [ 4, 5 ],
				'limit_language' => 5,
			],
		];
	}

	/**
	 * @since 2.0
	 * @version 3.0.0
	 * @return array
	 * @param null|string $api_key
	 */
	public function get_user_info( $api_key = null ) {
		if ( null !== $this->user_info ) {
			return $this->user_info;
		}

		if ( null === $api_key ) {
			$api_key = \weglot_get_api_key();
		}

		try {
			$results   = $this->do_request( Helper_API::get_api_url() . '/projects/owner?api_key=' . $api_key, null );
			$json      = \json_decode( $results, true );

			if ( \json_last_error() !== JSON_ERROR_NONE ) {
				throw new \Exception( 'Unknown error with Weglot Api (0001) : ' . \json_last_error() );
			}

			if ( isset( $json['succeeded'] ) && ( 0 === $json['succeeded'] || 1 === $json['succeeded'] ) ) {
				if ( 1 !== $json['succeeded'] ) {
					$error = isset( $json['error'] ) ? $json['error'] : 'Unknown error with Weglot Api (0003)';
					throw new \Exception( $error );
				}

				if ( ! isset( $json['answer'] ) ) {
					throw new \Exception( 'Unknown error with Weglot Api (0004)' );
				}

				$answer          = $json['answer'];
				$this->user_info = $answer;
				return $this->user_info;
			}

			throw new \Exception( 'Unknown error with Weglot Api (0002) : ' . $json );
		} catch ( \Exception $e ) {
			return [
				'not_exist' => false,
			];
		}
	}

	/**
	 * @since 2.0.6
	 *
	 * @return int
	 */
	public function get_limit_destination_language() {
		$user_info        = $this->get_user_info();
		$plans            = $this->get_plans();
		$limit            = 1000;
		if (
			$user_info['plan_id'] <= 1 ||
			in_array( $user_info['plan_id'], $plans['starter_free']['ids'] ) // phpcs:ignore
		) {
			$limit = $plans['starter_free']['limit_language'];
		} elseif (
			in_array( $user_info['plan_id'], $plans['business']['ids'] ) // phpcs:ignore
		) {
			$limit = $plans['business']['limit_language'];
		}

		return $limit;
	}

	/**
	 *
	 * @param string $url
	 * @param array $parameters
	 * @return array
	 */
	public function do_request( $url, $parameters ) {
		if ( $parameters ) {
			$payload = json_encode( $parameters ); //phpcs:ignore
			if ( \json_last_error() === JSON_ERROR_NONE ) {
				$response = wp_remote_post(
					$url,
					array(
						'method'      => 'POST',
						'timeout'     => 45,
						'redirection' => 5,
						'blocking'    => true,
						'headers'     => array(
							'Content-type' => 'application/json',
						),
						'body'      => $payload,
						'cookies'   => array(),
						'sslverify' => false,
					)
				);
			} else {
				throw new \Exception( 'Cannot json encode parameters: ' . \json_last_error() );
			}
		} else {
			$response = wp_remote_get( //phpcs:ignore
				$url,
				[
					'method'      => 'GET',
					'timeout'     => 45,
					'redirection' => 5,
					'blocking'    => true,
					'headers'     => [
						'Content-type' => 'application/json',
					],
					'body'      => null,
					'cookies'   => [],
					'sslverify' => false,
				]
			);
		}

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			throw new \Exception( 'Error doing the external request to ' . $url . ': ' . $error_message );
		} else {
			return $response['body'];
		}
	}
}



