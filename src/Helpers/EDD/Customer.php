<?php
/**
 * EDD Customer Helper
 *
 * Provides customer-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\EDD;

/**
 * Class Customer
 *
 * Customer utilities for EDD conditions.
 */
class Customer {

	/**
	 * Get the current customer ID.
	 *
	 * @return int
	 */
	public static function get_current_id(): int {
		if ( ! function_exists( 'edd_get_customer_by' ) || ! is_user_logged_in() ) {
			return 0;
		}

		$customer = edd_get_customer_by( 'user_id', get_current_user_id() );

		return $customer ? (int) $customer->id : 0;
	}

	/**
	 * Get customer's purchased product IDs.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return array<int>
	 */
	public static function get_product_ids( int $customer_id ): array {
		if ( ! function_exists( 'edd_get_customer' ) ) {
			return [];
		}

		$customer = edd_get_customer( $customer_id );

		if ( ! $customer ) {
			return [];
		}

		$order_ids = $customer->get_order_ids( edd_get_complete_order_statuses() );

		if ( empty( $order_ids ) ) {
			return [];
		}

		$items = edd_get_order_items( [
			'order_id__in' => $order_ids,
			'status__in'   => edd_get_deliverable_order_item_statuses(),
			'number'       => 999999,
		] );

		if ( empty( $items ) ) {
			return [];
		}

		return array_unique( array_column( $items, 'product_id' ) );
	}

	/**
	 * Get customer's purchased term IDs.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $taxonomy    The taxonomy.
	 *
	 * @return array<int>
	 */
	public static function get_term_ids( int $customer_id, string $taxonomy ): array {
		$product_ids = self::get_product_ids( $customer_id );

		if ( empty( $product_ids ) ) {
			return [];
		}

		$term_ids = [];

		foreach ( $product_ids as $product_id ) {
			$terms    = wp_get_object_terms( $product_id, $taxonomy, [ 'fields' => 'ids' ] );
			$term_ids = array_merge( $term_ids, is_array( $terms ) ? $terms : [] );
		}

		return array_unique( $term_ids );
	}

}