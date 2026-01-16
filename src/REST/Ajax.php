<?php
/**
 * REST API Ajax Handler
 *
 * Handles custom AJAX search/hydrate requests for conditions.
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
use Exception;
use WP_Error;
use WP_REST_Request;

/**
 * Class Ajax
 *
 * Handles AJAX requests for custom condition types.
 */
class Ajax {

	/**
	 * Handle search/hydrate request.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array|WP_Error
	 */
	public static function handle( WP_REST_Request $request ) {
		$set_id       = $request->get_param( 'set_id' );
		$condition_id = $request->get_param( 'condition_id' );
		$search       = $request->get_param( 'search' );
		$include      = $request->get_param( 'include' );

		// Get the condition configuration
		$conditions = Registry::get_conditions_raw( $set_id );

		if ( ! isset( $conditions[ $condition_id ] ) ) {
			return new WP_Error(
				'invalid_condition',
				__( 'Invalid condition ID.', 'arraypress' ),
				[ 'status' => 400 ]
			);
		}

		$condition = $conditions[ $condition_id ];

		// Ensure it's an ajax type with a callback
		if ( ( $condition['type'] ?? '' ) !== 'ajax' || ! isset( $condition['ajax'] ) ) {
			return new WP_Error(
				'invalid_ajax_type',
				__( 'Condition is not an AJAX type.', 'arraypress' ),
				[ 'status' => 400 ]
			);
		}

		$callback = $condition['ajax'];

		if ( ! is_callable( $callback ) ) {
			return new WP_Error(
				'invalid_callback',
				__( 'Invalid AJAX callback.', 'arraypress' ),
				[ 'status' => 500 ]
			);
		}

		// Parse include IDs if provided
		$ids = null;
		if ( ! empty( $include ) ) {
			$ids = array_map( 'trim', explode( ',', $include ) );
			$ids = array_filter( $ids );
		}

		// Call the callback
		try {
			$results = call_user_func( $callback, $search, $ids );

			// Ensure results are in the correct format
			if ( ! is_array( $results ) ) {
				return [];
			}

			// Normalize results to ensure value/label format
			return array_map( function ( $item ) {
				if ( is_array( $item ) && isset( $item['value'] ) ) {
					return [
						'value' => $item['value'],
						'label' => $item['label'] ?? $item['value'],
					];
				}

				// Handle simple value => label arrays
				return [
					'value' => $item,
					'label' => (string) $item,
				];
			}, $results );

		} catch ( Exception $e ) {
			return new WP_Error(
				'callback_error',
				$e->getMessage(),
				[ 'status' => 500 ]
			);
		}
	}

}
