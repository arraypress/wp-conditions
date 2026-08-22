<?php
/**
 * WooCommerce Customer Helper
 *
 * Reads the customer behind the current request, whether they are logged in or
 * checking out as a guest. A guest has no order history to speak of, which is
 * itself the signal several conditions are built on.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use WC_Customer;
use WC_Order;

/**
 * Class Customer
 *
 * Customer utilities for WooCommerce conditions.
 */
class Customer {

	/**
	 * The customer object for this request.
	 *
	 * @return WC_Customer|null
	 *
	 * @since 1.0.0
	 */
	public static function get(): ?WC_Customer {
		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		$wc = WC();

		return isset( $wc->customer ) && $wc->customer instanceof WC_Customer ? $wc->customer : null;
	}

	/**
	 * The customer's user ID, or 0 for a guest.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_user_id(): int {
		$customer = self::get();

		if ( $customer ) {
			return (int) $customer->get_id();
		}

		return get_current_user_id();
	}

	/**
	 * Whether this is a guest checkout.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_guest(): bool {
		return 0 === self::get_user_id();
	}

	/**
	 * The customer's billing email.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_email(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_email() : '';
	}

	/**
	 * How many paid orders this customer has.
	 *
	 * Counted by email rather than user ID so a guest with a history is not
	 * treated as brand new -- which is the case a first-order rule is usually
	 * trying to catch.
	 *
	 * @param string $email Optional email to count for. Defaults to the current customer.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_order_count( string $email = '' ): int {
		$email = '' !== $email ? $email : self::get_email();

		if ( '' === $email || ! function_exists( 'wc_get_orders' ) ) {
			return 0;
		}

		$orders = wc_get_orders( [
			'billing_email' => $email,
			'status'        => [ 'wc-processing', 'wc-completed', 'wc-on-hold' ],
			'limit'         => -1,
			'return'        => 'ids',
		] );

		return is_array( $orders ) ? count( $orders ) : 0;
	}

	/**
	 * Whether this would be the customer's first order.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_first_order(): bool {
		return 0 === self::get_order_count();
	}

	/**
	 * Lifetime spend across paid orders.
	 *
	 * @param string $email Optional email. Defaults to the current customer.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_total_spent( string $email = '' ): float {
		$email = '' !== $email ? $email : self::get_email();

		if ( '' === $email || ! function_exists( 'wc_get_orders' ) ) {
			return 0.0;
		}

		$orders = wc_get_orders( [
			'billing_email' => $email,
			'status'        => [ 'wc-processing', 'wc-completed' ],
			'limit'         => -1,
		] );

		$total = 0.0;

		foreach ( (array) $orders as $order ) {
			if ( $order instanceof WC_Order ) {
				$total += (float) $order->get_total();
			}
		}

		return $total;
	}

	/**
	 * Average order value across paid orders.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_average_order_value(): float {
		$count = self::get_order_count();

		if ( 0 === $count ) {
			return 0.0;
		}

		return round( self::get_total_spent() / $count, 2 );
	}

	/**
	 * Days since the account was registered.
	 *
	 * A guest has no account, so this reports 0 -- the same answer as an
	 * account created today, which is the direction a rule would want anyway.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_account_age_days(): int {
		$user_id = self::get_user_id();

		if ( 0 === $user_id ) {
			return 0;
		}

		$user = get_userdata( $user_id );

		if ( ! $user || empty( $user->user_registered ) ) {
			return 0;
		}

		$registered = strtotime( $user->user_registered );

		if ( ! $registered ) {
			return 0;
		}

		return (int) floor( ( time() - $registered ) / DAY_IN_SECONDS );
	}

	/**
	 * The customer's roles.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_roles(): array {
		$user_id = self::get_user_id();

		if ( 0 === $user_id ) {
			return [];
		}

		$user = get_userdata( $user_id );

		return $user ? array_map( 'strval', (array) $user->roles ) : [];
	}

	/**
	 * Whether the billing and shipping countries differ.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_country_mismatch(): bool {
		$customer = self::get();

		if ( ! $customer ) {
			return false;
		}

		$billing  = (string) $customer->get_billing_country();
		$shipping = (string) $customer->get_shipping_country();

		// No shipping country is not a mismatch -- a virtual cart never
		// collects one.
		if ( '' === $billing || '' === $shipping ) {
			return false;
		}

		return $billing !== $shipping;
	}

	/**
	 * Whether WooCommerce has the customer marked as having paid before.
	 *
	 * Its own flag, kept on the user record -- cheaper than counting orders,
	 * but only ever set for logged-in customers.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_paying_customer(): bool {
		$customer = self::get();

		return $customer ? (bool) $customer->get_is_paying_customer() : false;
	}

	/**
	 * Whether the customer is exempt from VAT.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_vat_exempt(): bool {
		$customer = self::get();

		return $customer ? (bool) $customer->get_is_vat_exempt() : false;
	}

	/**
	 * Date of the customer's most recent paid order, as Y-m-d.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_last_order_date(): string {
		$email = self::get_email();

		if ( '' === $email || ! function_exists( 'wc_get_orders' ) ) {
			return '';
		}

		$orders = wc_get_orders( [
			'billing_email' => $email,
			'status'        => [ 'wc-processing', 'wc-completed' ],
			'limit'         => 1,
			'orderby'       => 'date',
			'order'         => 'DESC',
		] );

		$order = is_array( $orders ) ? reset( $orders ) : null;

		if ( ! $order instanceof WC_Order ) {
			return '';
		}

		$date = $order->get_date_created();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * Days since the customer's most recent paid order.
	 *
	 * A customer who has never ordered reports PHP_INT_MAX, not 0. Zero would
	 * read as "ordered today", so a rule written as "days since last order < 7"
	 * would match every first-time buyer -- the opposite of what it says.
	 * Semantically the answer is that they have no last order to count from.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_days_since_last_order(): int {
		$date = self::get_last_order_date();

		if ( '' === $date ) {
			return PHP_INT_MAX;
		}

		$timestamp = strtotime( $date );

		if ( ! $timestamp ) {
			return PHP_INT_MAX;
		}

		return (int) max( 0, floor( ( (int) current_time( 'timestamp' ) - $timestamp ) / DAY_IN_SECONDS ) );
	}

	/**
	 * The customer's billing phone.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_phone(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_phone() : '';
	}

	/**
	 * The customer's billing company.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_company(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_company() : '';
	}

	/**
	 * The customer's billing state or region.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_billing_state(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_state() : '';
	}

	/**
	 * The customer's billing city.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_billing_city(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_city() : '';
	}

	/**
	 * The customer's billing postcode.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_billing_postcode(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_postcode() : '';
	}

	/**
	 * The customer's shipping state or region.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_state(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_shipping_state() : '';
	}

	/**
	 * The customer's shipping postcode.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_postcode(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_shipping_postcode() : '';
	}

	/**
	 * The customer's billing country.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_billing_country(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_billing_country() : '';
	}

	/**
	 * The customer's shipping country.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_country(): string {
		$customer = self::get();

		return $customer ? (string) $customer->get_shipping_country() : '';
	}
}
