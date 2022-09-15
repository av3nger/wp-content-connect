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
use function TenUp\ContentConnect\Helpers\get_registry;

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
	 * Valid relationship objects.
	 *
	 * @since 1.4.1
	 * @var array|string[]
	 */
	private array $valid_objects = array( 'post', 'user' );

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
		$object_type = $request->get_param( 'object_type' );

		if ( ! in_array( $object_type, $this->valid_objects, true ) ) {
			return array();
		}

		$current_post_id   = (int) $request->get_param( 'current_post_id' );
		$target_post_type  = sanitize_text_field( $request->get_param( 'post_type' ) );
		$relationship_name = sanitize_text_field( $request->get_param( 'relationship_name' ) );

		$results = array();
		switch ( $object_type ) {
			case 'user':
				//$results = $this->search_users( $search_text, $search_args );
				break;
			case 'post':
				$results = $this->create_post( $current_post_id, $target_post_type, $relationship_name );
				break;
		}

		return $results;
	}

	/**
	 * Create a new draft post and assign relationship from current to target.
	 *
	 * @since 1.4.1
	 *
	 * @param int    $c_post_id          Current post ID.
	 * @param string $t_post_type        Target post type.
	 * @param string $relationship_name  Relationship name.
	 *
	 * @return array
	 */
	private function create_post( int $c_post_id, string $t_post_type, string $relationship_name ): array {
		$new_post_id = wp_insert_post(
			array(
				'post_status' => 'draft',
				'post_type'   => $t_post_type,
			)
		);

		$c_post_type  = get_post_type( $c_post_id );
		$relationship = get_registry()->get_post_to_post_relationship( $c_post_type, $t_post_type, $relationship_name );

		if ( ! empty( $relationship ) ) {
			$relationship->add_relationship( $c_post_id, $new_post_id );
		}

		return array(
			'postID' => $new_post_id,
		);
	}
}
