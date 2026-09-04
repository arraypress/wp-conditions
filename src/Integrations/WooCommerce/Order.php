<?php
/**
 * WooCommerce Order Helper
 *
 * Reads a placed order. Conditions pass an order_id in their args; anything
 * that cannot be resolved answers with a zero value so an order deleted between
 * queueing and evaluation does not raise.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use ArrayPress\Conditions\Integrations\WooCommerce\Product as ProductHelper;
use ArrayPress\Conditions\Integrations\WooCommerce\Cart as CartHelper;
use ArrayPress\Conditions\Helpers\Velocity;
use ArrayPress\Conditions\Integrations\WooCommerce\Customer as CustomerHelper;
use ArrayPress\Conditions\Helpers\Address;
use ArrayPress\Conditions\Helpers\DateTime;
use ArrayPress\Conditions\Helpers\Parse;
use WC_Order;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;

/**
 * Class Order
 *
 * Order utilities for WooCommerce conditions.
 */
class Order {

	/** -------------------------------------------------------------------------
	 * Resolution
	 * ------------------------------------------------------------------------ */

	/**
	 * Order ID from the condition args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_id( array $args ): int {
		return (int) ( $args['order_id'] ?? 0 );
	}

	/**
	 * The order object.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WC_Order|null
	 *
	 * @since 1.0.0
	 */
	public static function get( array $args ): ?WC_Order {
		$id = self::get_id( $args );

		if ( 0 === $id || ! function_exists( 'wc_get_order' ) ) {
			return null;
		}

		$order = wc_get_order( $id );

		return $order instanceof WC_Order ? $order : null;
	}

	/** -------------------------------------------------------------------------
	 * Money
	 * ------------------------------------------------------------------------ */

	/**
	 * Order total.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_total( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_total() : 0.0;
	}

	/**
	 * Order subtotal, before discounts, tax and shipping.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_subtotal( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_subtotal() : 0.0;
	}

	/**
	 * Total tax.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_tax( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_total_tax() : 0.0;
	}

	/**
	 * Total discount.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_discount( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_total_discount() : 0.0;
	}

	/**
	 * Shipping cost.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_total( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_shipping_total() : 0.0;
	}

	/**
	 * Amount refunded against the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_refunded_total( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_total_refunded() : 0.0;
	}

	/**
	 * Discount as a share of the subtotal.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float 0-100.
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_percentage( array $args ): float {
		$subtotal = self::get_subtotal( $args );

		if ( $subtotal <= 0 ) {
			return 0.0;
		}

		return round( ( self::get_discount( $args ) / $subtotal ) * 100, 2 );
	}

	/**
	 * Currency code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_currency( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_currency() : '';
	}

	/** -------------------------------------------------------------------------
	 * State
	 * ------------------------------------------------------------------------ */

	/**
	 * Order status, without the wc- prefix.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_status( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_status() : '';
	}

	/**
	 * Payment gateway ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_gateway( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_payment_method() : '';
	}

	/**
	 * Whether the order has been paid.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_paid( array $args ): bool {
		$order = self::get( $args );

		return $order ? (bool) $order->is_paid() : false;
	}

	/**
	 * Payment method title, as shown to the customer.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_gateway_title( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_payment_method_title() : '';
	}

	/**
	 * The gateway's transaction reference.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_transaction_id( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_transaction_id() : '';
	}

	/**
	 * How the order was created -- checkout, store-api, rest-api, admin.
	 *
	 * Worth a rule of its own: an order created through the REST API when the
	 * store only sells through its own checkout did not come from a customer.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_created_via( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_created_via() : '';
	}

	/**
	 * The user agent recorded when the order was placed.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_user_agent( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_customer_user_agent() : '';
	}

	/**
	 * How many refunds have been recorded against the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_count( array $args ): int {
		$order = self::get( $args );

		return $order ? count( (array) $order->get_refunds() ) : 0;
	}

	/**
	 * Whether the order needs a shipping address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function needs_shipping_address( array $args ): bool {
		$order = self::get( $args );

		return $order ? (bool) $order->needs_shipping_address() : false;
	}

	/**
	 * Whether anything on the order is downloadable.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_downloadable_item( array $args ): bool {
		$order = self::get( $args );

		return $order ? (bool) $order->has_downloadable_item() : false;
	}

	/**
	 * Whether the order was created by a logged-out customer.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_guest( array $args ): bool {
		$order = self::get( $args );

		return $order ? 0 === (int) $order->get_customer_id() : false;
	}

	/** -------------------------------------------------------------------------
	 * Items
	 * ------------------------------------------------------------------------ */

	/**
	 * Line items.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function get_items( array $args ): array {
		$order = self::get( $args );

		return $order ? (array) $order->get_items() : [];
	}

	/**
	 * Total quantity across every line.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_item_count( array $args ): int {
		$order = self::get( $args );

		return $order ? (int) $order->get_item_count() : 0;
	}

	/**
	 * Number of distinct lines.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_unique_product_count( array $args ): int {
		return count( self::get_product_ids( $args ) );
	}

	/**
	 * Product IDs in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_product_ids( array $args ): array {
		$ids = [];

		foreach ( self::get_items( $args ) as $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$ids[] = (int) $item->get_product_id();
			}
		}

		return array_values( array_filter( array_unique( $ids ) ) );
	}

	/**
	 * Variation IDs in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_ids( array $args ): array {
		$ids = [];

		foreach ( self::get_items( $args ) as $item ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$ids[] = (int) $item->get_variation_id();
			}
		}

		return array_values( array_filter( array_unique( $ids ) ) );
	}

	/**
	 * Term IDs of a taxonomy across every product in the order.
	 *
	 * @param array  $args     The condition arguments.
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_term_ids( array $args, string $taxonomy ): array {
		$terms = [];

		foreach ( self::get_product_ids( $args ) as $product_id ) {
			$found = get_the_terms( $product_id, $taxonomy );

			if ( ! is_array( $found ) ) {
				continue;
			}

			foreach ( $found as $term ) {
				$terms[] = (int) $term->term_id;
			}
		}

		return array_values( array_unique( $terms ) );
	}

	/**
	 * Coupon codes applied to the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_coupons( array $args ): array {
		$order = self::get( $args );

		if ( ! $order ) {
			return [];
		}

		return array_map( 'strval', (array) $order->get_coupon_codes() );
	}

	/**
	 * Shipping method IDs used on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_methods( array $args ): array {
		$order = self::get( $args );

		if ( ! $order ) {
			return [];
		}

		$methods = [];

		foreach ( (array) $order->get_shipping_methods() as $method ) {
			if ( $method instanceof WC_Order_Item_Shipping ) {
				$methods[] = (string) $method->get_method_id();
			}
		}

		return array_values( array_filter( array_unique( $methods ) ) );
	}

	/** -------------------------------------------------------------------------
	 * Customer and addresses
	 * ------------------------------------------------------------------------ */

	/**
	 * The user ID behind the order, or 0 for a guest.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_customer_id( array $args ): int {
		$order = self::get( $args );

		return $order ? (int) $order->get_customer_id() : 0;
	}

	/**
	 * Billing email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_email( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_email() : '';
	}

	/**
	 * Billing country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_country( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_country() : '';
	}

	/**
	 * Billing state or region code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_region( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_state() : '';
	}

	/**
	 * Billing city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_city( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_city() : '';
	}

	/**
	 * Billing postcode.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_postcode( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_postcode() : '';
	}

	/**
	 * Shipping country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_country( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_shipping_country() : '';
	}

	/**
	 * Whether billing and shipping countries differ.
	 *
	 * An order with no shipping country is not a mismatch -- a virtual order
	 * never collects one.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_country_mismatch( array $args ): bool {
		$billing  = self::get_country( $args );
		$shipping = self::get_shipping_country( $args );

		if ( '' === $billing || '' === $shipping ) {
			return false;
		}

		return $billing !== $shipping;
	}

	/**
	 * Shipping class slugs across the order's products.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_classes( array $args ): array {
		$classes = [];

		foreach ( self::get_product_ids( $args ) as $product_id ) {
			$class = Product::get_shipping_class( [ 'product_id' => $product_id ] );

			if ( '' !== $class ) {
				$classes[] = $class;
			}
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Tax class slugs across the order's products.
	 *
	 * The standard class is normalised from WooCommerce's empty slug so a rule
	 * can name it.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_classes( array $args ): array {
		$classes = [];

		foreach ( self::get_product_ids( $args ) as $product_id ) {
			$class = Product::get_tax_class( [ 'product_id' => $product_id ] );

			$classes[] = '' === $class ? 'standard' : $class;
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Billing phone.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_phone( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_phone() : '';
	}

	/**
	 * Billing company.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_company( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_company() : '';
	}

	/**
	 * Billing address, first line.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_address( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_billing_address_1() : '';
	}

	/**
	 * Shipping state or region.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_region( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_shipping_state() : '';
	}

	/**
	 * Shipping city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_city( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_shipping_city() : '';
	}

	/**
	 * Shipping postcode.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_postcode( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_shipping_postcode() : '';
	}

	/**
	 * Customer IP address recorded at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_ip( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_customer_ip_address() : '';
	}

	/**
	 * Customer note left at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_customer_note( array $args ): string {
		$order = self::get( $args );

		return $order ? (string) $order->get_customer_note() : '';
	}

	/** -------------------------------------------------------------------------
	 * Dates
	 * ------------------------------------------------------------------------ */

	/**
	 * Date the order was created, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_date_created( array $args ): string {
		$order = self::get( $args );
		$date  = $order?->get_date_created();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * Date the order was paid, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_date_paid( array $args ): string {
		$order = self::get( $args );
		$date  = $order?->get_date_paid();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * Age of the order, in the unit carried by the rule.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_age( array $args ): int {
		$order = self::get( $args );
		$date  = $order?->get_date_created();

		if ( ! $date ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age_from_timestamp( $date->getTimestamp(), $parsed['unit'] );
	}
	/** -------------------------------------------------------------------------
	 * Subscriptions, relative spend and form heuristics
	 * ------------------------------------------------------------------------ */

	/**
	 * Whether the order is a subscription renewal.
	 *
	 * Renewals are placed by the scheduler, not a person, and belong outside
	 * every fraud rule.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null without WooCommerce Subscriptions.
	 */
	public static function is_renewal( array $args ): ?bool {
		if ( ! function_exists( 'wcs_order_contains_renewal' ) ) {
			return null;
		}

		$order = self::get( $args );

		return $order ? (bool) wcs_order_contains_renewal( $order ) : false;
	}

	/**
	 * Whether the order starts a subscription.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null without WooCommerce Subscriptions.
	 */
	public static function contains_subscription( array $args ): ?bool {
		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return null;
		}

		$order = self::get( $args );

		return $order ? (bool) wcs_order_contains_subscription( $order, 'parent' ) : false;
	}

	/**
	 * How many times the customer's usual order this one is.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null for a first order.
	 */
	public static function get_total_to_average_ratio( array $args ): ?float {
		$order = self::get( $args );

		if ( ! $order ) {
			return null;
		}

		return CustomerHelper::get_total_to_average_ratio( (float) $order->get_total(), (int) $order->get_id() );
	}

	/**
	 * Whether the billing address is a post office box.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_po_box( array $args ): bool {
		return Address::is_po_box( self::get_address( $args ) );
	}

	/**
	 * Whether the billing address carries a street number.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null when there is no address to read.
	 */
	public static function has_street_number( array $args ): ?bool {
		$address = self::get_address( $args );

		return '' === $address ? null : Address::has_street_number( $address );
	}

	/**
	 * Whether the first and last names are the same word.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function names_identical( array $args ): bool {
		$order = self::get( $args );

		return $order ? Address::names_identical( (string) $order->get_billing_first_name(), (string) $order->get_billing_last_name() ) : false;
	}

	/**
	 * Whether the customer's name appears in their email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null when there is no name or email to compare.
	 */
	public static function name_matches_email( array $args ): ?bool {
		$order = self::get( $args );

		if ( ! $order ) {
			return null;
		}

		$first = (string) $order->get_billing_first_name();
		$last  = (string) $order->get_billing_last_name();
		$email = (string) $order->get_billing_email();

		if ( '' === $email || ( '' === $first && '' === $last ) ) {
			return null;
		}

		return Address::name_matches_email( $first, $last, $email );
	}
	/** -------------------------------------------------------------------------
	 * Weight and size
	 * ------------------------------------------------------------------------ */

	/**
	 * The product and quantity of every line, for the physical measures.
	 *
	 * Read from the product as it is now: an order does not store weight or
	 * dimensions, and a product deleted since contributes nothing.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int, array{0: WC_Product, 1: int}>
	 */
	private static function get_physical_lines( array $args ): array {
		$lines = [];

		foreach ( self::get_items( $args ) as $item ) {
			if ( ! is_object( $item ) || ! method_exists( $item, 'get_product' ) ) {
				continue;
			}

			$product = $item->get_product();

			if ( ! $product instanceof WC_Product || $product->is_virtual() ) {
				continue;
			}

			$quantity = method_exists( $item, 'get_quantity' ) ? max( 1, (int) $item->get_quantity() ) : 1;
			$lines[]  = [ $product, $quantity ];
		}

		return $lines;
	}

	/**
	 * Total weight of the order, quantities counted.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float In the store's weight unit.
	 */
	public static function get_weight( array $args ): float {
		$weight = 0.0;

		foreach ( self::get_physical_lines( $args ) as [ $product, $quantity ] ) {
			$weight += (float) $product->get_weight() * $quantity;
		}

		return round( $weight, 4 );
	}

	/**
	 * Total volume of the order, quantities counted.
	 *
	 * Lines missing a dimension contribute nothing rather than collapsing
	 * the figure to zero, the same as the cart.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float In the store's dimension unit cubed.
	 */
	public static function get_volume( array $args ): float {
		$volume = 0.0;

		foreach ( self::get_physical_lines( $args ) as [ $product, $quantity ] ) {
			$length = (float) $product->get_length();
			$width  = (float) $product->get_width();
			$height = (float) $product->get_height();

			if ( $length <= 0 || $width <= 0 || $height <= 0 ) {
				continue;
			}

			$volume += $length * $width * $height * $quantity;
		}

		return round( $volume, 4 );
	}

	/**
	 * The longest single side anywhere in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_max_dimension( array $args ): float {
		$max = 0.0;

		foreach ( self::get_physical_lines( $args ) as [ $product ] ) {
			$max = max( $max, (float) $product->get_length(), (float) $product->get_width(), (float) $product->get_height() );
		}

		return $max;
	}

	/**
	 * Per-unit weight of the heaviest item in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_max_weight( array $args ): float {
		$max = 0.0;

		foreach ( self::get_physical_lines( $args ) as [ $product ] ) {
			$max = max( $max, (float) $product->get_weight() );
		}

		return $max;
	}
	/** -------------------------------------------------------------------------
	 * Attribution
	 * ------------------------------------------------------------------------ */

	/**
	 * One order attribution field, as WooCommerce recorded it.
	 *
	 * @param array  $args  The condition arguments.
	 * @param string $field source_type, utm_source, utm_medium, utm_campaign, referrer,
	 *                      device_type, session_entry, session_pages or session_count.
	 *
	 * @return string '' when nothing was recorded.
	 */
	public static function get_attribution( array $args, string $field ): string {
		$order = self::get( $args );

		if ( ! $order ) {
			return '';
		}

		$value = $order->get_meta( '_wc_order_attribution_' . $field, true );

		return is_scalar( $value ) ? trim( (string) $value ) : '';
	}

	/**
	 * Whether WooCommerce recorded any attribution for the order.
	 *
	 * Attribution is collected by a script on the storefront, so an order
	 * with none was placed by something that did not run scripts, or from
	 * outside the storefront altogether.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_attribution( array $args ): bool {
		return '' !== self::get_attribution( $args, 'source_type' );
	}

	/**
	 * An attribution number, or null when it was not recorded.
	 *
	 * @param array  $args  The condition arguments.
	 * @param string $field session_pages or session_count.
	 *
	 * @return int|null
	 */
	public static function get_attribution_number( array $args, string $field ): ?int {
		$value = self::get_attribution( $args, $field );

		return is_numeric( $value ) ? (int) $value : null;
	}

	/** -------------------------------------------------------------------------
	 * Card
	 * ------------------------------------------------------------------------ */

	/**
	 * The card the order was paid with, as far as the gateway told WooCommerce.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array{brand: string, last4: string}
	 */
	public static function get_card_info( array $args ): array {
		$order = self::get( $args );
		$info  = [
			'brand' => '',
			'last4' => '',
		];

		if ( ! $order || ! method_exists( $order, 'get_payment_card_info' ) ) {
			return $info;
		}

		$card = (array) $order->get_payment_card_info();

		$info['brand'] = strtolower( trim( (string) ( $card['brand'] ?? '' ) ) );
		$info['last4'] = preg_replace( '/\D/', '', (string) ( $card['last4'] ?? '' ) ) ?? '';

		return $info;
	}

	/**
	 * The card brand, lower-case, or '' when the gateway did not say.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_card_brand( array $args ): string {
		return self::get_card_info( $args )['brand'];
	}

	/**
	 * The card's last four digits, or '' when the gateway did not say.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_card_last4( array $args ): string {
		return self::get_card_info( $args )['last4'];
	}

	/**
	 * A fingerprint of the card, for the card-velocity conditions.
	 *
	 * The same salted hash the library computes for a host, so an order's
	 * fingerprint and one computed at checkout agree.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string '' when the gateway did not say.
	 */
	public static function get_card_fingerprint( array $args ): string {
		$card = self::get_card_info( $args );

		if ( '' === $card['brand'] || '' === $card['last4'] ) {
			return '';
		}

		return Velocity::compute_card_fingerprint( $card['brand'], $card['last4'], null, null, self::get_country( $args ) );
	}

	/**
	 * Whether the order was paid with a saved payment method.
	 *
	 * A fraudster with a stolen card does not have it saved on an account.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function used_saved_payment_method( array $args ): bool {
		$order = self::get( $args );

		return $order ? [] !== array_filter( (array) $order->get_payment_tokens() ) : false;
	}

	/** -------------------------------------------------------------------------
	 * Items, money and dates the cart already reports
	 * ------------------------------------------------------------------------ */

	/**
	 * Unit prices of every line, as charged, before discounts.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float[]
	 */
	private static function get_item_unit_prices( array $args ): array {
		$prices = [];

		foreach ( self::get_items( $args ) as $item ) {
			if ( ! is_object( $item ) || ! method_exists( $item, 'get_subtotal' ) ) {
				continue;
			}

			$quantity = method_exists( $item, 'get_quantity' ) ? max( 1, (int) $item->get_quantity() ) : 1;
			$prices[] = (float) $item->get_subtotal() / $quantity;
		}

		return $prices;
	}

	/**
	 * The cheapest unit price in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null for an order with no lines.
	 */
	public static function get_min_item_price( array $args ): ?float {
		$prices = self::get_item_unit_prices( $args );

		return [] === $prices ? null : round( min( $prices ), 4 );
	}

	/**
	 * The dearest unit price in the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null for an order with no lines.
	 */
	public static function get_max_item_price( array $args ): ?float {
		$prices = self::get_item_unit_prices( $args );

		return [] === $prices ? null : round( max( $prices ), 4 );
	}

	/**
	 * Total of the order's fees.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_fee_total( array $args ): float {
		$order = self::get( $args );

		return $order ? (float) $order->get_total_fees() : 0.0;
	}

	/**
	 * How many units have been refunded from the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_refunded_item_count( array $args ): int {
		$order = self::get( $args );

		return $order ? abs( (int) $order->get_total_qty_refunded() ) : 0;
	}

	/**
	 * The date the order was completed, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string '' until it is.
	 */
	public static function get_date_completed( array $args ): string {
		$order = self::get( $args );
		$date  = $order?->get_date_completed();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * What the order's contents cost the store.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null when cost of goods is not tracked.
	 */
	public static function get_cost_total( array $args ): ?float {
		$order = self::get( $args );

		if ( ! $order || ! ProductHelper::cogs_enabled() || ! method_exists( $order, 'get_cogs_total_value' ) ) {
			return null;
		}

		return (float) $order->get_cogs_total_value();
	}

	/**
	 * The order's margin after discounts, as a percentage of the item revenue.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null when cost is not tracked or nothing was charged.
	 */
	public static function get_margin_percentage( array $args ): ?float {
		$order = self::get( $args );
		$cost  = self::get_cost_total( $args );

		if ( ! $order || null === $cost ) {
			return null;
		}

		$revenue = (float) $order->get_subtotal() - (float) $order->get_discount_total();

		return $revenue > 0 ? round( ( $revenue - $cost ) / $revenue * 100, 2 ) : null;
	}

	/**
	 * The order's coupons as objects.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object[]
	 */
	private static function get_coupon_objects( array $args ): array {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			return [];
		}

		$coupons = [];

		foreach ( self::get_coupons( $args ) as $code ) {
			$coupon = new \WC_Coupon( $code );

			if ( method_exists( $coupon, 'get_id' ) && $coupon->get_id() ) {
				$coupons[] = $coupon;
			}
		}

		return $coupons;
	}

	/**
	 * Discount types of the coupons used on the order.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 */
	public static function get_coupon_types( array $args ): array {
		$types = [];

		foreach ( self::get_coupon_objects( $args ) as $coupon ) {
			$types[] = (string) $coupon->get_discount_type();
		}

		return array_values( array_unique( array_filter( $types ) ) );
	}

	/**
	 * Whether a coupon on the order was restricted to somebody else's email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_coupon_email_mismatch( array $args ): bool {
		$emails = array_values( array_filter( [ self::get_email( $args ) ] ) );

		foreach ( self::get_coupon_objects( $args ) as $coupon ) {
			$restrictions = array_values( array_filter( array_map( 'strval', (array) $coupon->get_email_restrictions() ) ) );

			if ( [] !== $restrictions && ! CartHelper::email_matches_restrictions( $emails, $restrictions ) ) {
				return true;
			}
		}

		return false;
	}
}
