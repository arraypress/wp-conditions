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

use ArrayPress\Conditions\Helpers\Request;
use ArrayPress\Conditions\Helpers\Address;
use ArrayPress\Conditions\Helpers\Parse;
use ArrayPress\Conditions\Helpers\DateTime;
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
	 * Statuses that count as paid, everywhere a history is read.
	 *
	 * On-hold is not paid. Counting it in the order count but not the spend
	 * made the average order value a division of one set by another.
	 */
	private const PAID_STATUSES = [ 'wc-processing', 'wc-completed' ];

	/**
	 * The query that selects this customer's own orders, or null for none.
	 *
	 * An account's history is looked up by its id. A guest's is looked up
	 * by the email they typed, but only among guest orders, and not at all
	 * when that email belongs to an account: the billing email at checkout
	 * is whatever the shopper wrote, and looking it up unconditionally let
	 * a fraudster type a loyal customer's address and borrow "five paid
	 * orders, ordered three days ago" -- defeating exactly the first-order
	 * rules this exists for.
	 *
	 * @param string $email An explicit email from the caller, or '' for the current customer.
	 *
	 * @return array|null
	 */
	private static function history_query( string $email = '' ): ?array {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return null;
		}

		if ( '' !== $email ) {
			return [ 'billing_email' => $email ];
		}

		if ( ! self::is_guest() ) {
			return [ 'customer_id' => self::get_user_id() ];
		}

		$email = self::get_email();

		if ( '' === $email || ( function_exists( 'email_exists' ) && email_exists( $email ) ) ) {
			return null;
		}

		return [
			'billing_email' => $email,
			'customer_id'   => 0,
		];
	}

	/**
	 * The customer's own orders, as objects.
	 *
	 * @param array $extra Further query arguments.
	 *
	 * @return WC_Order[]
	 */
	private static function get_history_orders( array $extra = [] ): array {
		$query = self::history_query();

		if ( null === $query ) {
			return [];
		}

		$orders = wc_get_orders( $query + $extra + [ 'limit' => -1 ] );

		return array_values( array_filter( (array) $orders, static fn( $order ) => $order instanceof WC_Order ) );
	}

	/**
	 * How many paid orders this customer has.
	 *
	 * A guest with a history under their own email is not treated as brand
	 * new -- which is the case a first-order rule is usually trying to catch.
	 *
	 * @param string $email Optional email to count for. Defaults to the current customer.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_order_count( string $email = '' ): int {
		$query = self::history_query( $email );

		if ( null === $query ) {
			return 0;
		}

		$orders = wc_get_orders( $query + [
			'status' => self::PAID_STATUSES,
			'limit'  => -1,
			'return' => 'ids',
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
		$query = self::history_query( $email );

		if ( null === $query ) {
			return 0.0;
		}

		$orders = wc_get_orders( $query + [
			'status' => self::PAID_STATUSES,
			'limit'  => -1,
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
		return self::get_account_age( [ '_unit' => 'day' ] );
	}

	/**
	 * How long the account has existed, in the unit the rule carries.
	 *
	 * The condition offers days, weeks, months and years, and this used to
	 * answer in days whatever was chosen -- so "Account Age < 6 weeks" was
	 * really "< 6 days".
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_account_age( array $args ): int {
		$user_id = self::get_user_id();

		if ( 0 === $user_id ) {
			return 0;
		}

		$user = get_userdata( $user_id );

		if ( ! $user || empty( $user->user_registered ) ) {
			return 0;
		}

		// user_registered is stored in UTC.
		$registered = strtotime( $user->user_registered . ' UTC' );

		if ( ! $registered ) {
			return 0;
		}

		return DateTime::from_seconds( max( 0, time() - $registered ), Parse::number_unit( $args )['unit'] );
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
		$query = self::history_query();

		if ( null === $query ) {
			return '';
		}

		$orders = wc_get_orders( $query + [
			'status'  => self::PAID_STATUSES,
			'limit'   => 1,
			'orderby' => 'date',
			'order'   => 'DESC',
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
	/** -------------------------------------------------------------------------
	 * History: what was bought, and what came back
	 * ------------------------------------------------------------------------ */

	/**
	 * Every product the customer has paid for, parents included.
	 *
	 * @return int[]
	 */
	public static function get_purchased_product_ids(): array {
		$ids = [];

		foreach ( self::get_history_orders( [ 'status' => self::PAID_STATUSES ] ) as $order ) {
			foreach ( $order->get_items() as $item ) {
				if ( ! is_object( $item ) || ! method_exists( $item, 'get_product_id' ) ) {
					continue;
				}

				$ids[] = (int) $item->get_product_id();

				if ( method_exists( $item, 'get_variation_id' ) && $item->get_variation_id() ) {
					$ids[] = (int) $item->get_variation_id();
				}
			}
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Term ids across every product the customer has paid for.
	 *
	 * Read from the parent product, since a variation carries no terms.
	 *
	 * @param string $taxonomy product_cat or product_tag.
	 *
	 * @return int[]
	 */
	public static function get_purchased_term_ids( string $taxonomy ): array {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return [];
		}

		$term_ids = [];
		$seen     = [];

		foreach ( self::get_purchased_product_ids() as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$source_id = $product->get_parent_id() ?: $product->get_id();

			if ( isset( $seen[ $source_id ] ) ) {
				continue;
			}

			$seen[ $source_id ] = true;
			$source             = $source_id === $product->get_id() ? $product : wc_get_product( $source_id );

			if ( ! $source instanceof WC_Product ) {
				continue;
			}

			$ids = match ( $taxonomy ) {
				'product_tag'   => $source->get_tag_ids(),
				'product_brand' => method_exists( $source, 'get_brand_ids' ) ? $source->get_brand_ids() : [],
				default         => $source->get_category_ids(),
			};
			$term_ids = array_merge( $term_ids, array_map( 'intval', (array) $ids ) );
		}

		return array_values( array_unique( $term_ids ) );
	}

	/**
	 * How many of the customer's orders have had money refunded.
	 *
	 * Fully refunded orders and partially refunded ones both count: what
	 * matters is that money went back.
	 *
	 * @return int
	 */
	public static function get_refund_count(): int {
		$count = 0;

		foreach ( self::get_history_orders( [ 'status' => array_merge( self::PAID_STATUSES, [ 'wc-refunded' ] ) ] ) as $order ) {
			if ( 'refunded' === $order->get_status() || (float) $order->get_total_refunded() > 0 ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * The share of the customer's orders that were refunded, as a percentage.
	 *
	 * @return float
	 */
	public static function get_refund_rate(): float {
		$orders = self::get_history_orders( [ 'status' => array_merge( self::PAID_STATUSES, [ 'wc-refunded' ] ) ] );

		if ( [] === $orders ) {
			return 0.0;
		}

		$refunded = 0;

		foreach ( $orders as $order ) {
			if ( 'refunded' === $order->get_status() || (float) $order->get_total_refunded() > 0 ) {
				++$refunded;
			}
		}

		return round( $refunded / count( $orders ) * 100, 2 );
	}

	/** -------------------------------------------------------------------------
	 * Relative spend
	 * ------------------------------------------------------------------------ */

	/**
	 * The customer's average paid order, leaving out the one being judged.
	 *
	 * @param int $except_order_id An order to leave out, or 0.
	 *
	 * @return float|null Null when there is no earlier order to average.
	 */
	public static function get_prior_average_order_value( int $except_order_id = 0 ): ?float {
		$total = 0.0;
		$count = 0;

		foreach ( self::get_history_orders( [ 'status' => self::PAID_STATUSES ] ) as $order ) {
			if ( $except_order_id && (int) $order->get_id() === $except_order_id ) {
				continue;
			}

			$total += (float) $order->get_total();
			++$count;
		}

		return $count > 0 && $total > 0 ? $total / $count : null;
	}

	/**
	 * How many times the customer's usual order a total is.
	 *
	 * @param float $total           The order or cart total.
	 * @param int   $except_order_id The order being judged, to leave out.
	 *
	 * @return float|null Null for a first-time customer, who has no usual.
	 */
	public static function get_total_to_average_ratio( float $total, int $except_order_id = 0 ): ?float {
		$average = self::get_prior_average_order_value( $except_order_id );

		return null === $average ? null : round( $total / $average, 2 );
	}

	/** -------------------------------------------------------------------------
	 * Subscriptions
	 * ------------------------------------------------------------------------ */

	/**
	 * Whether the customer has a subscription that is currently billing.
	 *
	 * @return bool|null Null without WooCommerce Subscriptions.
	 */
	public static function has_active_subscription(): ?bool {
		if ( ! function_exists( 'wcs_user_has_subscription' ) ) {
			return null;
		}

		$user_id = self::get_user_id();

		return 0 !== $user_id && (bool) wcs_user_has_subscription( $user_id, '', 'active' );
	}

	/**
	 * How many of the customer's subscriptions are currently billing.
	 *
	 * Active and pending-cancel both bill until the period ends.
	 *
	 * @return int|null Null without WooCommerce Subscriptions.
	 */
	public static function get_active_subscription_count(): ?int {
		if ( ! function_exists( 'wcs_get_users_subscriptions' ) ) {
			return null;
		}

		$user_id = self::get_user_id();

		if ( 0 === $user_id ) {
			return 0;
		}

		$count = 0;

		foreach ( (array) wcs_get_users_subscriptions( $user_id ) as $subscription ) {
			if ( is_object( $subscription ) && method_exists( $subscription, 'has_status' ) && $subscription->has_status( [ 'active', 'pending-cancel' ] ) ) {
				++$count;
			}
		}

		return $count;
	}
	/** -------------------------------------------------------------------------
	 * Drift: what changed between this customer's orders
	 * ------------------------------------------------------------------------ */

	/**
	 * How many different IP addresses the customer has ordered from.
	 *
	 * @return int
	 */
	public static function get_ip_count(): int {
		$ips = [];

		foreach ( self::get_history_orders() as $order ) {
			$ip = trim( (string) $order->get_customer_ip_address() );

			if ( '' !== $ip ) {
				$ips[ $ip ] = true;
			}
		}

		return count( $ips );
	}

	/**
	 * Whether this order's browser differs from every earlier order's.
	 *
	 * The current user agent is the request's, or the order's when there
	 * is one; the earlier ones are read off the customer's other orders.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null when there is no earlier order to differ from.
	 */
	public static function is_user_agent_changed( array $args ): ?bool {
		$current_id = (int) ( $args['order_id'] ?? 0 );
		$current    = '';

		if ( $current_id && function_exists( 'wc_get_order' ) ) {
			$order   = wc_get_order( $current_id );
			$current = $order instanceof WC_Order ? (string) $order->get_customer_user_agent() : '';
		}

		if ( '' === $current ) {
			$current = Request::get_user_agent( $args );
		}

		$previous = [];

		foreach ( self::get_history_orders() as $order ) {
			if ( (int) $order->get_id() === $current_id ) {
				continue;
			}

			$agent = trim( (string) $order->get_customer_user_agent() );

			if ( '' !== $agent ) {
				$previous[ $agent ] = true;
			}
		}

		if ( [] === $previous || '' === $current ) {
			return null;
		}

		return ! isset( $previous[ trim( $current ) ] );
	}

	/**
	 * How many different shipping addresses the customer has used.
	 *
	 * A reshipper collects packages at many doors.
	 *
	 * @return int
	 */
	public static function get_distinct_shipping_address_count(): int {
		$addresses = [];

		foreach ( self::get_history_orders() as $order ) {
			$key = strtolower( trim( (string) $order->get_shipping_address_1() ) ) . '|'
				. strtolower( trim( (string) $order->get_shipping_postcode() ) ) . '|'
				. strtoupper( trim( (string) $order->get_shipping_country() ) );

			if ( '||' !== $key ) {
				$addresses[ $key ] = true;
			}
		}

		return count( $addresses );
	}

	/**
	 * How many different billing countries the customer has used.
	 *
	 * @return int
	 */
	public static function get_distinct_billing_country_count(): int {
		$countries = [];

		foreach ( self::get_history_orders() as $order ) {
			$country = strtoupper( trim( (string) $order->get_billing_country() ) );

			if ( '' !== $country ) {
				$countries[ $country ] = true;
			}
		}

		return count( $countries );
	}

	/**
	 * How many of the customer's orders failed.
	 *
	 * @return int
	 */
	public static function get_failed_order_count(): int {
		return count( self::get_history_orders( [ 'status' => [ 'wc-failed' ] ] ) );
	}

	/**
	 * How many of the customer's orders were cancelled.
	 *
	 * @return int
	 */
	public static function get_cancelled_order_count(): int {
		return count( self::get_history_orders( [ 'status' => [ 'wc-cancelled' ] ] ) );
	}

	/** -------------------------------------------------------------------------
	 * Account details
	 * ------------------------------------------------------------------------ */

	/**
	 * Whether the customer has a payment method saved to their account.
	 *
	 * @return bool
	 */
	public static function has_saved_payment_method(): bool {
		$user_id = self::get_user_id();

		if ( 0 === $user_id || ! class_exists( 'WC_Payment_Tokens' ) ) {
			return false;
		}

		return [] !== array_filter( (array) \WC_Payment_Tokens::get_customer_tokens( $user_id ) );
	}

	/**
	 * The country WooCommerce will tax this customer in.
	 *
	 * @return string
	 */
	public static function get_taxable_country(): string {
		$customer = self::get();

		if ( ! $customer ) {
			return '';
		}

		$address = (array) $customer->get_taxable_address();

		return strtoupper( (string) ( $address[0] ?? '' ) );
	}

	/**
	 * Whether the customer's taxable address is outside the store's base.
	 *
	 * @return bool
	 */
	public static function is_outside_base(): bool {
		$customer = self::get();

		return $customer ? (bool) $customer->is_customer_outside_base() : false;
	}

	/**
	 * Whether the customer has a complete shipping address on file.
	 *
	 * @return bool
	 */
	public static function has_full_shipping_address(): bool {
		$customer = self::get();

		return $customer ? (bool) $customer->has_full_shipping_address() : false;
	}
}
