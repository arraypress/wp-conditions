<?php
/**
 * EDD Order Helper
 *
 * Provides order-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers;

/**
 * Class Order
 *
 * Order utilities for EDD conditions.
 */
class Order {

	/**
	 * Get term IDs from order items.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array<int>
	 */
	public static function get_term_ids( int $order_id, string $taxonomy ): array {
		if ( ! $order_id || ! function_exists( 'edd_get_order_items' ) ) {
			return [];
		}

		$items = edd_get_order_items( [
			'order_id' => $order_id,
			'number'   => 999,
		] );

		if ( empty( $items ) ) {
			return [];
		}

		$term_ids = [];

		foreach ( $items as $item ) {
			$terms    = wp_get_object_terms( $item->product_id, $taxonomy, [ 'fields' => 'ids' ] );
			$term_ids = array_merge( $term_ids, is_array( $terms ) ? $terms : [] );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Get product IDs from order.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return array<int>
	 */
	public static function get_product_ids( int $order_id ): array {
		if ( ! $order_id || ! function_exists( 'edd_get_order_items' ) ) {
			return [];
		}

		$items = edd_get_order_items( [
			'order_id' => $order_id,
			'number'   => 999,
		] );

		if ( empty( $items ) ) {
			return [];
		}

		return array_unique( array_column( $items, 'product_id' ) );
	}

	/**
	 * Check if order is a renewal.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return bool
	 */
	public static function is_renewal( int $order_id ): bool {
		if ( ! $order_id ) {
			return false;
		}

		return ! empty( edd_get_order_meta( $order_id, '_edd_sl_is_renewal', true ) ) ||
		       ! empty( edd_get_order_meta( $order_id, 'subscription_id', true ) );
	}

	/**
	 * Check if order is an initial subscription payment.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return bool
	 */
	public static function is_subscription( int $order_id ): bool {
		if ( ! $order_id ) {
			return false;
		}

		return (bool) edd_get_order_meta( $order_id, '_edd_subscription_payment', true );
	}

	/**
	 * Get subscription count for order.
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return int
	 */
	public static function get_subscription_count( int $order_id ): int {
		if ( ! $order_id || ! class_exists( 'EDD_Subscriptions_DB' ) ) {
			return 0;
		}

		$subs_db = new \EDD_Subscriptions_DB();
		$subs    = $subs_db->get_subscriptions( [
			'parent_payment_id' => $order_id,
			'number'            => 999,
		] );

		return count( $subs );
	}

}