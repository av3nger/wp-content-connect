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
	private array $valid_objects = array( 'post' );

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

		$target_post_type = $request->get_param( 'post_type' );

		if ( 'post' === $object_type ) {
			$found_post_type = false;
			foreach ( (array) $target_post_type as $post_type ) {
				if ( post_type_exists( $post_type ) ) {
					$found_post_type = $post_type;
					break;
				}
			}

			if ( ! $found_post_type ) {
				return array();
			}

			$target_post_type = $found_post_type;
		}

		$current_post_id   = (int) $request->get_param( 'current_post_id' );
		$relationship_name = sanitize_text_field( $request->get_param( 'relationship_name' ) );
		$post_title        = sanitize_text_field( $request->get_param( 'title' ) );

		$results = array();
		if ( 'post' === $object_type ) {
			$results = $this->create_post( $current_post_id, $target_post_type, $relationship_name, $post_title );
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
	 * @param string $post_title         Title for the new post.
	 *
	 * @return array
	 */
	private function create_post( int $c_post_id, string $t_post_type, string $relationship_name, string $post_title ): array {
		$new_post_id = wp_insert_post(
			array(
				'post_title'  => empty( $post_title ) ? esc_html__( 'Draft post', 'tenup-content-connect' ) : $post_title,
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
			'added' => true,
			'ID'    => $new_post_id,
			'name'  => empty( $post_title ) ? esc_html__( 'Draft post', 'tenup-content-connect' ) : $post_title,
		);
	}
}
