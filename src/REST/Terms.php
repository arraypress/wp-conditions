<?php
/**
 * REST Terms Endpoint
 *
 * Handles searching and retrieving terms via REST API.
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

/**
 * Class Terms
 *
 * REST endpoint for searching terms.
 */
class Terms {

	/**
	 * Search terms.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function search( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$taxonomy = $request->get_param( 'taxonomy' );
		$search   = $request->get_param( 'search' );
		$include  = $request->get_param( 'include' );

		// Validate taxonomy is allowed
		if ( ! Registry::is_taxonomy_allowed( $taxonomy ) ) {
			return new WP_Error(
				'forbidden',
				__( 'Taxonomy not allowed.', 'arraypress' ),
				[ 'status' => 403 ]
			);
		}

		// Validate taxonomy exists
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error(
				'not_found',
				__( 'Taxonomy not found.', 'arraypress' ),
				[ 'status' => 404 ]
			);
		}

		// Build query args
		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => 20,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];

		// Search mode
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		} // Include mode (lookup specific IDs)
		elseif ( ! empty( $include ) ) {
			$args['include'] = wp_parse_id_list( $include );
			$args['number']  = 0; // No limit when looking up specific IDs
			$args['orderby'] = 'include';
		} else {
			// Default: return some terms
			$args['number'] = 10;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return new WP_Error(
				'query_error',
				$terms->get_error_message(),
				[ 'status' => 500 ]
			);
		}

		$results = [];
		foreach ( $terms as $term ) {
			$results[] = [
				'value' => (string) $term->term_id,
				'label' => self::get_term_label( $term ),
			];
		}

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * Get a label for a term.
	 *
	 * @param \WP_Term $term The term object.
	 *
	 * @return string
	 */
	private static function get_term_label( \WP_Term $term ): string {
		$label = $term->name;

		// Add hierarchy indicator if term has parent
		if ( $term->parent ) {
			$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
			$depth     = count( $ancestors );
			$label     = str_repeat( '— ', $depth ) . $label;
		}

		// Add count if available
		if ( $term->count > 0 ) {
			$label .= ' (' . $term->count . ')';
		}

		return $label;
	}

}
