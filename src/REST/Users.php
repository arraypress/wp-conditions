<?php
/**
 * REST Users Endpoint
 *
 * Handles searching and retrieving users via REST API.
 *
 * @package     ArrayPress\Conditions\REST
 * @copyright   Copyright (c) 2026, ArrayPress Limited
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
use WP_User;

/**
 * Class Users
 *
 * REST endpoint for searching users.
 */
class Users {

	/**
	 * Search users.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function search( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$role    = $request->get_param( 'role' );
		$search  = $request->get_param( 'search' );
		$include = $request->get_param( 'include' );

		// Build query args
		$args = [
			'number'  => 20,
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		// Filter by role if specified and allowed
		if ( ! empty( $role ) ) {
			$roles = array_map( 'trim', explode( ',', $role ) );

			// Validate roles are allowed
			foreach ( $roles as $r ) {
				if ( ! Registry::is_role_allowed( $r ) ) {
					return new WP_Error(
						'forbidden',
						sprintf( __( 'Role "%s" not allowed.', 'arraypress' ), $r ),
						[ 'status' => 403 ]
					);
				}
			}

			$args['role__in'] = $roles;
		}

		// Search mode
		if ( ! empty( $search ) ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
		} // Include mode (lookup specific IDs)
		elseif ( ! empty( $include ) ) {
			$args['include'] = wp_parse_id_list( $include );
			$args['number']  = count( $args['include'] );
			$args['orderby'] = 'include';
		} else {
			// Default: return recent users
			$args['number']  = 10;
			$args['orderby'] = 'registered';
			$args['order']   = 'DESC';
		}

		$users = get_users( $args );

		$results = [];
		foreach ( $users as $user ) {
			$results[] = [
				'value' => (string) $user->ID,
				'label' => $user->display_name,
			];
		}

		return new WP_REST_Response( $results, 200 );
	}

}
