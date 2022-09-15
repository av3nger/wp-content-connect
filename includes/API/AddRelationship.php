<?php
/**
 * Register REST API endpoints for adding a new relationship.
 *
 * @since 1.4.1
 *
 * @package TenUp\ContentConnect
 */

namespace TenUp\ContentConnect\API;

use WP_REST_Request;

/**
 * AddRelationship class.
 */
class AddRelationship extends API {
	/**
	 * Endpoint name.
	 *
	 * @since 1.4.1
	 * @var string
	 */
	public string $route = 'create-relationship';

	/**
	 * Handles calls to the create-relationship endpoint.
	 *
	 * @since 1.4.1
	 *
	 * @param WP_REST_Request $request  Request.
	 *
	 * @return array Array of posts or users that match the query
	 */
	public function process( WP_REST_Request $request ): array {
		return array();
	}
}
