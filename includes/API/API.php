<?php
/**
 * An abstract class that acts as a base class for all REST API functionality.
 *
 * @since 1.4.1
 *
 * @package TenUp\ContentConnect
 */

namespace TenUp\ContentConnect\API;

use WP_REST_Request;

/**
 * Abstract API class.
 */
abstract class API {
	/**
	 * Endpoint name.
	 *
	 * @since 1.4.0
	 * @var string
	 */
	public string $route = '';

	/**
	 * Setup endpoints, localize data.
	 *
	 * @sice 1.4.1  Abstracted from Search class.
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'rest_api_init', array( $this, 'register_endpoint' ) );
		add_filter( 'tenup_content_connect_localize_data', array( $this, 'localize_endpoints' ) );
	}

	/**
	 * Register REST API endpoints.
	 *
	 * @since 1.4.1
	 *
	 * @return void
	 */
	public function register_endpoint() {
		register_rest_route(
			'content-connect/v1',
			$this->route,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'process' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);
	}

	/**
	 * Check endpoint permissions.
	 *
	 * @since 1.4.1  Abstracted from Search class.
	 *
	 * @param WP_REST_Request $request  Request.
	 *
	 * @return bool
	 */
	public function check_permission( $request ) {
		$user = wp_get_current_user();

		if ( 0 === $user->ID ) {
			return false;
		}

		$nonce = $request->get_param( 'nonce' );

		// If the user got the nonce, they were on the proper edit page.
		if ( ! wp_verify_nonce( $nonce, 'content-connect-api' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Localize endpoint data.
	 *
	 * @since 1.4.1
	 *
	 * @param array $data  Data array.
	 *
	 * @return array
	 */
	public function localize_endpoints( array $data ): array {
		$data['endpoints'][ $this->route ] = get_rest_url( get_current_blog_id(), 'content-connect/v1/' . $this->route );

		// TODO: This, most likely, can be safely removed in favor of the `wp_rest` nonce.
		if ( ! isset( $data['nonces']['api'] ) ) {
			$data['nonces']['api'] = wp_create_nonce( 'content-connect-api' );
		}

		return $data;
	}
}
