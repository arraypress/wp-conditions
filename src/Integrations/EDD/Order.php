<?php
/**
 * EDD Order Helper
 *
 * Provides order-related utilities for EDD conditions.
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
use EDD\Orders\Order as EDD_Order;

/**
 * Class Order
 *
 * Order utilities for EDD conditions.
 */
class Order {

	/**
	 * Get order object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return EDD_Order|null
	 */
	public static function get( array $args ): ?EDD_Order {
		if ( ! function_exists( 'edd_get_order' ) ) {
			return null;
		}

		$order_id = $args['order_id'] ?? 0;

		if ( ! $order_id ) {
			return null;
		}

		return edd_get_order( $order_id );
	}

	/**
	 * Get order ID from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_id( array $args ): int {
		return (int) ( $args['order_id'] ?? 0 );
	}

	/** -------------------------------------------------------------------------
	 * Amount Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the order total.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_total( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->total : 0.0;
	}

	/**
	 * Get the order subtotal.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_subtotal( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->subtotal : 0.0;
	}

	/**
	 * Get the order tax amount.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_tax( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->tax : 0.0;
	}

	/**
	 * Get the order discount amount.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_discount( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->discount : 0.0;
	}

	/** -------------------------------------------------------------------------
	 * Detail Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the order status.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_status( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->status : '';
	}

	/**
	 * Get the payment gateway.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_gateway( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->gateway : '';
	}

	/**
	 * Get the order currency.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_currency( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->currency : '';
	}

	/**
	 * Get the order mode (live or test).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_mode( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->mode : '';
	}

	/**
	 * Get the order IP address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_ip( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->ip : '';
	}

	/** -------------------------------------------------------------------------
	 * Content Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get product IDs from order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_product_ids( array $args ): array {
		$order_id = self::get_id( $args );

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
	 * Get term IDs from order items.
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

	/**
	 * Get category IDs from order items.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_category_ids( array $args ): array {
		return self::get_term_ids( $args, 'download_category' );
	}

	/**
	 * Get tag IDs from order items.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_tag_ids( array $args ): array {
		return self::get_term_ids( $args, 'download_tag' );
	}

	/**
	 * Get item count for the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_item_count( array $args ): int {
		$order_id = self::get_id( $args );

		if ( ! $order_id || ! function_exists( 'edd_get_order_items' ) ) {
			return 0;
		}

		$items = edd_get_order_items( [
			'order_id' => $order_id,
			'number'   => 999,
		] );

		return count( $items );
	}

	/** -------------------------------------------------------------------------
	 * Address Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the billing country.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_country( array $args ): string {
		$order = self::get( $args );

		if ( ! $order ) {
			return '';
		}

		$address = $order->get_address();

		return $address ? $address->country : '';
	}

	/**
	 * Get the billing region/state.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_region( array $args ): string {
		$order = self::get( $args );

		if ( ! $order ) {
			return '';
		}

		$address = $order->get_address();

		return $address ? $address->region : '';
	}

	/**
	 * Get the billing city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_city( array $args ): string {
		$order = self::get( $args );

		if ( ! $order ) {
			return '';
		}

		$address = $order->get_address();

		return $address ? $address->city : '';
	}

	/**
	 * Get the billing postal/zip code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_postcode( array $args ): string {
		$order = self::get( $args );

		if ( ! $order ) {
			return '';
		}

		$address = $order->get_address();

		return $address ? $address->postal_code : '';
	}

	/** -------------------------------------------------------------------------
	 * Customer Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the order email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_email( array $args ): string {
		$order = self::get( $args );

		return $order ? $order->email : '';
	}

	/**
	 * Get the customer ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_customer_id( array $args ): int {
		$order = self::get( $args );

		return (int) ( $order?->customer_id ?? 0 );
	}

	/**
	 * Get the user ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_user_id( array $args ): int {
		$order = self::get( $args );

		return (int) ( $order?->user_id ?? 0 );
	}

	/** -------------------------------------------------------------------------
	 * Date Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the date created (formatted for comparison).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_date_created( array $args ): string {
		$order = self::get( $args );

		if ( ! $order || empty( $order->date_created ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $order->date_created ) );
	}

	/**
	 * Get the date completed (formatted for comparison).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_date_completed( array $args ): string {
		$order = self::get( $args );

		if ( ! $order || empty( $order->date_completed ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $order->date_completed ) );
	}

	/**
	 * Get the order age in specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_age( array $args ): int {
		$order = self::get( $args );

		if ( ! $order || empty( $order->date_created ) ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $order->date_created, $parsed['unit'] );
	}

	/** -------------------------------------------------------------------------
	 * Subscription Methods (requires EDD Recurring)
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if order is a renewal.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_renewal( array $args ): bool {
		$order_id = self::get_id( $args );

		if ( ! $order_id || ! function_exists( 'edd_get_order_meta' ) ) {
			return false;
		}

		return ! empty( edd_get_order_meta( $order_id, '_edd_sl_is_renewal', true ) ) ||
		       ! empty( edd_get_order_meta( $order_id, 'subscription_id', true ) );
	}

	/**
	 * Check if order is an initial subscription payment.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_subscription( array $args ): bool {
		$order_id = self::get_id( $args );

		if ( ! $order_id || ! function_exists( 'edd_get_order_meta' ) ) {
			return false;
		}

		return (bool) edd_get_order_meta( $order_id, '_edd_subscription_payment', true );
	}

	/**
	 * Get subscription count for order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_subscription_count( array $args ): int {
		$order_id = self::get_id( $args );

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

	/**
	 * Get mode options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_mode_options(): array {
		return [
			[ 'value' => 'live', 'label' => __( 'Live', 'arraypress' ) ],
			[ 'value' => 'test', 'label' => __( 'Test', 'arraypress' ) ],
		];
	}

	/**
	 * Get unique product count (ignoring quantities).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_unique_product_count( array $args ): int {
		$product_ids = self::get_product_ids( $args );

		return count( array_unique( $product_ids ) );
	}

	/**
	 * Count items by product type.
	 *
	 * @param array  $args The condition arguments.
	 * @param string $type The product type (bundle, service, default).
	 *
	 * @return int
	 */
	public static function count_by_type( array $args, string $type ): int {
		$product_ids = self::get_product_ids( $args );

		if ( empty( $product_ids ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $product_ids as $product_id ) {
			$product_type = edd_get_download_type( $product_id );

			if ( $product_type === $type ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Get discount IDs applied to order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_discount_ids( array $args ): array {
		$order = self::get( $args );

		if ( ! $order || ! function_exists( 'edd_get_order_adjustments' ) ) {
			return [];
		}

		$adjustments = edd_get_order_adjustments( [
			'object_id'   => $order->id,
			'object_type' => 'order',
			'type'        => 'discount',
			'number'      => 999,
		] );

		if ( empty( $adjustments ) ) {
			return [];
		}

		return array_filter( array_column( $adjustments, 'type_id' ) );
	}

	/**
	 * Check if order has any discount applied.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_discount( array $args ): bool {
		$order = self::get( $args );

		return $order && (float) $order->discount > 0;
	}

	/** -------------------------------------------------------------------------
	 * Identity and state
	 * ------------------------------------------------------------------------ */

	/**
	 * Order type -- sale or refund.
	 *
	 * EDD 3 stores refunds as orders in their own right, so a rule that does
	 * not check the type will happily evaluate against a refund as though it
	 * were a purchase.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_type( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->type : '';
	}

	/**
	 * The order number, which can differ from the ID when sequential numbering
	 * is switched on.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_number( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_number() : '';
	}

	/**
	 * The gateway's transaction reference.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_transaction_id( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_transaction_id() : '';
	}

	/**
	 * Whether the order is an abandoned one that can still be recovered.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_recoverable( array $args ): bool {
		$order = self::get( $args );

		return $order ? (bool) $order->is_recoverable() : false;
	}

	/**
	 * Whether the order carries unlimited download access.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_unlimited_downloads( array $args ): bool {
		$order = self::get( $args );

		return $order ? (bool) $order->has_unlimited_downloads() : false;
	}

	/** -------------------------------------------------------------------------
	 * Money
	 * ------------------------------------------------------------------------ */

	/**
	 * Total of the fees on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_fee_total( array $args ): float {
		$order = self::get( $args );

		if ( ! $order ) {
			return 0.0;
		}

		$total = 0.0;

		foreach ( (array) $order->get_fees() as $fee ) {
			$total += (float) ( $fee->total ?? 0 );
		}

		return $total;
	}

	/**
	 * How many fees are on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_fee_count( array $args ): int {
		$order = self::get( $args );

		return $order ? count( (array) $order->get_fees() ) : 0;
	}

	/**
	 * Discount as a share of the subtotal.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float 0-100.
	 */
	public static function get_discount_percentage( array $args ): float {
		$subtotal = self::get_subtotal( $args );

		if ( $subtotal <= 0 ) {
			return 0.0;
		}

		return round( ( self::get_discount( $args ) / $subtotal ) * 100, 2 );
	}

	/**
	 * How many refunds have been issued against the order.
	 *
	 * EDD 3 records each refund as its own order, so this counts them rather
	 * than reading a field.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_refund_count( array $args ): int {
		$order_id = self::get_id( $args );

		if ( ! $order_id || ! function_exists( 'edd_get_order_refunds' ) ) {
			return 0;
		}

		return count( (array) edd_get_order_refunds( $order_id ) );
	}

	/**
	 * Amount refunded against the order.
	 *
	 * Refund orders carry negative totals in EDD, so the sign is flipped here
	 * to give the positive figure a rule would be written against.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_refunded_total( array $args ): float {
		$order_id = self::get_id( $args );

		if ( ! $order_id || ! function_exists( 'edd_get_order_refunds' ) ) {
			return 0.0;
		}

		$total = 0.0;

		foreach ( (array) edd_get_order_refunds( $order_id ) as $refund ) {
			$total += abs( (float) ( $refund->total ?? 0 ) );
		}

		return $total;
	}

	/** -------------------------------------------------------------------------
	 * Items
	 * ------------------------------------------------------------------------ */

	/**
	 * Price option IDs across the order's items.
	 *
	 * A variable-priced download sells at several tiers, and which tier was
	 * bought is often the thing a rule cares about.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 */
	public static function get_price_ids( array $args ): array {
		$order = self::get( $args );

		if ( ! $order ) {
			return [];
		}

		$ids = [];

		foreach ( (array) $order->get_items() as $item ) {
			if ( null !== ( $item->price_id ?? null ) ) {
				$ids[] = (int) $item->price_id;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/** -------------------------------------------------------------------------
	 * Address
	 * ------------------------------------------------------------------------ */

	/**
	 * The order's address record.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	private static function address( array $args ): ?object {
		$order   = self::get( $args );
		$address = $order?->get_address();

		return is_object( $address ) ? $address : null;
	}

	/**
	 * First name on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_first_name( array $args ): string {
		return (string) ( self::address( $args )?->first_name ?? '' );
	}

	/**
	 * Last name on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_last_name( array $args ): string {
		return (string) ( self::address( $args )?->last_name ?? '' );
	}

	/**
	 * First line of the address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address( array $args ): string {
		return (string) ( self::address( $args )?->address ?? '' );
	}

	/**
	 * Second line of the address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address_2( array $args ): string {
		return (string) ( self::address( $args )?->address2 ?? '' );
	}

}
