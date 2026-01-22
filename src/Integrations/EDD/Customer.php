<?php
/**
 * EDD Customer Helper
 *
 * Provides customer-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

use ArrayPress\Conditions\Helpers\DateTime;
use ArrayPress\Conditions\Helpers\Parse;
use EDD_Customer;

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
	 * Get customer object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return EDD_Customer|null
	 */
	public static function get( array $args ): ?EDD_Customer {
		if ( ! function_exists( 'edd_get_customer' ) ) {
			return null;
		}

		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return null;
		}

		return edd_get_customer( $customer_id );
	}

	/** -------------------------------------------------------------------------
	 * Profile Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get customer segment/type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The customer segment identifier.
	 */
	public static function get_segment( array $args ): string {
		$customer = self::get( $args );

		if ( ! $customer ) {
			return '';
		}

		if ( (int) $customer->purchase_count === 1 ) {
			return 'first_time';
		}

		return 'returning';
	}

	/**
	 * Get customer segment options for select fields.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_segment_options(): array {
		return [
			[ 'value' => 'first_time', 'label' => __( 'First-Time Buyer', 'arraypress' ) ],
			[ 'value' => 'returning', 'label' => __( 'Returning Customer', 'arraypress' ) ],
		];
	}

	/**
	 * Get customer's email address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_email( array $args ): string {
		$customer = self::get( $args );

		return $customer ? $customer->email : '';
	}

	/**
	 * Get customer account age in specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_account_age( array $args ): int {
		$customer = self::get( $args );

		if ( ! $customer || empty( $customer->date_created ) ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $customer->date_created, $parsed['unit'] );
	}

	/** -------------------------------------------------------------------------
	 * Purchase History Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get total order count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_order_count( array $args ): int {
		$customer = self::get( $args );

		return $customer ? (int) $customer->purchase_count : 0;
	}

	/**
	 * Get total amount spent.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_total_spent( array $args ): float {
		$customer = self::get( $args );

		return $customer ? (float) $customer->purchase_value : 0.0;
	}

	/**
	 * Get average order value.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_average_order_value( array $args ): float {
		$customer = self::get( $args );

		if ( ! $customer || ! $customer->purchase_count ) {
			return 0.0;
		}

		return (float) $customer->purchase_value / (int) $customer->purchase_count;
	}

	/**
	 * Get customer's purchased product IDs.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_product_ids( array $args ): array {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
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
	 * @param array  $args     The condition arguments.
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array<int>
	 */
	public static function get_term_ids( array $args, string $taxonomy ): array {
		$product_ids = self::get_product_ids( $args );

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

	/** -------------------------------------------------------------------------
	 * Activity Methods (Period-Based)
	 * ------------------------------------------------------------------------ */

	/**
	 * Get order count within a date range preset.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_orders_in_period( array $args ): int {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args, 'this_month' );

		return Stats::get_customer_order_count( $customer_id, $parsed['unit'] );
	}

	/**
	 * Get spend within a date range preset.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_spend_in_period( array $args ): float {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return 0.0;
		}

		$parsed = Parse::number_unit( $args, 'this_month' );

		return Stats::get_customer_lifetime_value( $customer_id, $parsed['unit'] );
	}

	/** -------------------------------------------------------------------------
	 * Activity Methods (Lifetime)
	 * ------------------------------------------------------------------------ */

	/**
	 * Get count of unique IP addresses used.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_unique_ip_count( array $args ): int {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return 0;
		}

		$orders = edd_get_orders( [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
			'number'      => 999999,
		] );

		if ( empty( $orders ) ) {
			return 0;
		}

		$ips = array_unique( array_filter( array_column( $orders, 'ip' ) ) );

		return count( $ips );
	}

	/**
	 * Get refund count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_refund_count( array $args ): int {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return 0;
		}

		return edd_count_orders( [
			'customer_id' => $customer_id,
			'status'      => 'refunded',
		] );
	}

	/**
	 * Get refund rate as a percentage.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_refund_rate( array $args ): float {
		$customer_id = $args['customer_id'] ?? self::get_current_id();

		if ( ! $customer_id ) {
			return 0.0;
		}

		$total_orders = edd_count_orders( [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
		] );

		if ( ! $total_orders ) {
			return 0.0;
		}

		$refunded_orders = edd_count_orders( [
			'customer_id' => $customer_id,
			'status'      => 'refunded',
		] );

		return ( $refunded_orders / $total_orders ) * 100;
	}

}