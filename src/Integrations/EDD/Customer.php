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

		// No customer record yet (brand-new email at checkout, before
		// the order is created) — they're a first-time buyer by
		// definition. Returning '' here would cause "Customer Type IS
		// first_time" to silently miss the very customers it's most
		// useful for.
		if ( ! $customer ) {
			return 'first_time';
		}

		// `purchase_count` is the count of completed orders. At REVIEW
		// pass the current order is already counted, so subtract it
		// to get "orders prior to this one".
		$count = (int) $customer->purchase_count;
		if ( ! empty( $args['order_id'] ) ) {
			$count = max( 0, $count - 1 );
		}

		return $count === 0 ? 'first_time' : 'returning';
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
	 * Whether the current request's User-Agent differs from the
	 * customer's most-recent orders' User-Agents.
	 *
	 * Returns false when there's no history to compare against, no UA
	 * captured on past orders, or the current UA isn't supplied — i.e.
	 * defaults to "no drift detected" rather than firing on missing
	 * data. Past UAs are read from the `_edd_user_agent` order meta
	 * that the host plugin persists at order-build time.
	 *
	 * Comparison is exact-string equality, so a Chrome version bump
	 * counts as a change. For coarser comparison (browser+OS only),
	 * read `_edd_user_agent_parsed` instead — but most fraud signals
	 * benefit from the strictness of raw-string comparison.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The condition arguments. Reads `user_agent`,
	 *                    `customer_id`. `customer_id` falls back to the
	 *                    current logged-in customer.
	 *
	 * @return bool True when the UA has drifted; false otherwise.
	 */
	public static function is_user_agent_changed( array $args ): bool {
		$current_ua = (string) ( $args['user_agent'] ?? '' );
		if ( $current_ua === '' ) {
			return false;
		}

		$customer_id = $args['customer_id'] ?? self::get_current_id();
		if ( ! $customer_id ) {
			return false;
		}

		$orders = edd_get_orders( [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
			'number'      => 5,
			'orderby'     => 'date_created',
			'order'       => 'DESC',
		] );

		if ( empty( $orders ) ) {
			return false;
		}

		$past_uas = [];
		foreach ( $orders as $order ) {
			$ua = (string) edd_get_order_meta( $order->id, '_edd_user_agent', true );
			if ( $ua !== '' ) {
				$past_uas[] = $ua;
			}
		}

		if ( empty( $past_uas ) ) {
			return false;
		}

		return ! in_array( $current_ua, $past_uas, true );
	}

	/**
	 * Whether the current request's IP country differs from the
	 * customer's most-recent orders' IP countries.
	 *
	 * IP country drift is a strong signal for account takeover or
	 * stolen-card use — most legitimate customers stay in one country
	 * across orders even when their raw IP changes (mobile carriers,
	 * coffee shops, VPN-toggled-off, etc.).
	 *
	 * Past country is resolved from `_edd_ip_country` order meta
	 * (written by the host plugin when available), falling back to the
	 * order's billing country. The fallback isn't perfect — fraudsters
	 * can fake the address — but it's the next-best signal we have for
	 * orders predating IP-country capture.
	 *
	 * Returns false when either the current or all past countries are
	 * unresolvable, defaulting to "no drift detected" on missing data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The condition arguments. Reads `ip_country`,
	 *                    `customer_id`. `customer_id` falls back to the
	 *                    current logged-in customer.
	 *
	 * @return bool True when the country has drifted; false otherwise.
	 */
	public static function is_ip_country_changed( array $args ): bool {
		$current_country = strtoupper( (string) ( $args['ip_country'] ?? '' ) );
		if ( $current_country === '' ) {
			return false;
		}

		$customer_id = $args['customer_id'] ?? self::get_current_id();
		if ( ! $customer_id ) {
			return false;
		}

		$orders = edd_get_orders( [
			'customer_id' => $customer_id,
			'status__in'  => edd_get_complete_order_statuses(),
			'number'      => 5,
			'orderby'     => 'date_created',
			'order'       => 'DESC',
		] );

		if ( empty( $orders ) ) {
			return false;
		}

		$past_countries = [];
		foreach ( $orders as $order ) {
			$country = (string) edd_get_order_meta( $order->id, '_edd_ip_country', true );
			if ( $country === '' ) {
				$country = (string) ( $order->address->country ?? '' );
			}
			$country = strtoupper( $country );
			if ( $country !== '' ) {
				$past_countries[] = $country;
			}
		}

		if ( empty( $past_countries ) ) {
			return false;
		}

		return ! in_array( $current_country, $past_countries, true );
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

	/**
	 * Get the customer creation date.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_date_created( array $args ): string {
		$customer = self::get( $args );

		if ( ! $customer || empty( $customer->date_created ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $customer->date_created ) );
	}

	/**
	 * Get the date of the customer's last order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_last_order_date( array $args ): string {
		$customer = self::get( $args );

		if ( ! $customer ) {
			return '';
		}

		$orders = edd_get_orders( [
			'customer_id' => $customer->id,
			'status__in'  => edd_get_complete_order_statuses(),
			'number'      => 1,
			'orderby'     => 'date_created',
			'order'       => 'DESC',
		] );

		if ( empty( $orders ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $orders[0]->date_created ) );
	}

	/**
	 * Get time since customer's last order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_days_since_last_order( array $args ): int {
		$last_order_date = self::get_last_order_date( $args );

		// No prior order — return a sentinel high value so "less
		// than" comparisons fail safe. A brand-new customer should
		// not match "Days Since Last Order < 7" just because the
		// helper returned 0; semantically the answer is "infinity
		// days, they've never had a last order".
		if ( empty( $last_order_date ) ) {
			return PHP_INT_MAX;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $last_order_date, $parsed['unit'] );
	}

}