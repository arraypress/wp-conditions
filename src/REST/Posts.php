<?php
/**
 * REST Posts Endpoint
 *
 * Handles searching and retrieving posts via REST API.
 *
 * @package     ArrayPress\Conditions\REST
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\REST;

use ArrayPress\Conditions\Registry;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Posts
 *
 * REST endpoint for searching posts.
 */
class Posts {

	/**
	 * Search posts.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function search( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$post_type = $request->get_param( 'post_type' );
		$search    = $request->get_param( 'search' );
		$include   = $request->get_param( 'include' );

		// Validate post type is allowed
		if ( ! Registry::is_post_type_allowed( $post_type ) ) {
			return new WP_Error(
				'forbidden',
				__( 'Post type not allowed.', 'arraypress' ),
				[ 'status' => 403 ]
			);
		}

		// Build query args
		$args = [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		// Search mode
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		} // Include mode (lookup specific IDs)
		elseif ( ! empty( $include ) ) {
			$args['post__in']       = wp_parse_id_list( $include );
			$args['posts_per_page'] = count( $args['post__in'] );
			$args['orderby']        = 'post__in';
		} else {
			// Default: return recent items
			$args['posts_per_page'] = 10;
			$args['orderby']        = 'date';
			$args['order']          = 'DESC';
		}

		$query = new \WP_Query( $args );

		$results = [];
		foreach ( $query->posts as $post ) {
			$results[] = [
				'value' => (string) $post->ID,
				'label' => self::get_post_label( $post ),
			];
		}

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * Get a label for a post.
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return string
	 */
	private static function get_post_label( \WP_Post $post ): string {
		$label = $post->post_title;

		// Add post type for clarity if needed
		if ( $post->post_type !== 'post' && $post->post_type !== 'page' ) {
			$post_type_obj = get_post_type_object( $post->post_type );
			if ( $post_type_obj ) {
				$label .= ' (' . $post_type_obj->labels->singular_name . ')';
			}
		}

		return $label;
	}

}
