<?php
/**
 * WooCommerce Checkout Helper
 *
 * Reads what the customer has entered on the checkout form. The posted array is
 * passed in by the caller rather than read from $_POST here, so the same rules
 * can be replayed against a stored payload -- a test run, a queued job, a
 * re-evaluation after the fact.
 *
 * Where the form has not been posted yet, values fall back to the session and
 * the customer object, which is what a rule evaluated mid-checkout sees.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use ArrayPress\ArrayUtils\Arr;

/**
 * Class Checkout
 *
 * Checkout utilities for WooCommerce conditions.
 */
class Checkout {

	/** -------------------------------------------------------------------------
	 * Session
	 * ------------------------------------------------------------------------ */

	/**
	 * Read a value from the WooCommerce session.
	 *
	 * @param string $key     Session key.
	 * @param mixed  $default Value to return when the session is unavailable.
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	private static function session( string $key, $default = '' ) {
		if ( ! function_exists( 'WC' ) ) {
			return $default;
		}

		$wc = WC();

		if ( ! isset( $wc->session ) || ! is_object( $wc->session ) ) {
			return $default;
		}

		$value = $wc->session->get( $key );

		return null === $value ? $default : $value;
	}

	/** -------------------------------------------------------------------------
	 * Payment and shipping
	 * ------------------------------------------------------------------------ */

	/**
	 * The chosen payment gateway.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_gateway( array $args ): string {
		$gateway = Arr::get_first( $args['posted'] ?? [], [ 'payment_method' ] );

		if ( $gateway ) {
			return (string) $gateway;
		}

		return (string) self::session( 'chosen_payment_method' );
	}

	/**
	 * The chosen shipping method IDs.
	 *
	 * WooCommerce keys these by shipping package, so a cart split across two
	 * packages reports both.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_methods( array $args ): array {
		$posted = $args['posted']['shipping_method'] ?? null;

		if ( is_array( $posted ) && ! empty( $posted ) ) {
			return array_values( array_filter( array_map( 'strval', $posted ) ) );
		}

		$chosen = self::session( 'chosen_shipping_methods', [] );

		if ( ! is_array( $chosen ) ) {
			return [];
		}

		return array_values( array_filter( array_map( 'strval', $chosen ) ) );
	}

	/**
	 * Whether the customer is shipping to a different address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_shipping_to_different_address( array $args ): bool {
		$posted = $args['posted'] ?? [];

		// An unticked checkbox is absent from the form, not present and
		// false, and WooCommerce reads it the same way. Checking for the
		// key meant an unticked box fell through to the session's country
		// mismatch, which could say yes.
		if ( ! empty( $posted ) ) {
			return ! empty( $posted['ship_to_different_address'] );
		}

		return Customer::has_country_mismatch();
	}

	/**
	 * Whether the customer asked to create an account.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_creating_account( array $args ): bool {
		return (bool) ( $args['posted']['createaccount'] ?? false );
	}

	/** -------------------------------------------------------------------------
	 * Customer fields
	 * ------------------------------------------------------------------------ */

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
		$email = Arr::get_first( $args['posted'] ?? [], [ 'billing_email' ] );

		return $email ? (string) $email : Customer::get_email();
	}

	/**
	 * Billing first name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_first_name( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_first_name' ] );
	}

	/**
	 * Billing last name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_last_name( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_last_name' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_company' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_phone' ] );
	}

	/**
	 * The order note left at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_note( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'order_comments' ] );
	}

	/** -------------------------------------------------------------------------
	 * Billing address
	 * ------------------------------------------------------------------------ */

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
		$value = Arr::get_first( $args['posted'] ?? [], [ 'billing_country' ] );

		return $value ? (string) $value : Customer::get_billing_country();
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_state' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_city' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_postcode' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_address_1' ] );
	}

	/**
	 * Billing address, second line.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_address_2( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'billing_address_2' ] );
	}

	/**
	 * Whether the customer accepted the terms.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_accepted_terms( array $args ): bool {
		return (bool) ( $args['posted']['terms'] ?? false );
	}

	/** -------------------------------------------------------------------------
	 * Shipping address
	 * ------------------------------------------------------------------------ */

	/**
	 * Shipping first name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_first_name( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_first_name' ] );
	}

	/**
	 * Shipping last name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_last_name( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_last_name' ] );
	}

	/**
	 * Shipping address, first line.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_address( array $args ): string {
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_address_1' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_city' ] );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_state' ] );
	}

	/**
	 * Shipping country code.
	 *
	 * Falls back to the billing country, which is what WooCommerce itself does
	 * when the customer has not asked to ship elsewhere.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_country( array $args ): string {
		// When the shopper is not shipping elsewhere WooCommerce ships to the
		// billing address, whatever a shipping field or the session still
		// holds from an earlier visit.
		if ( ! empty( $args['posted'] ) && ! self::is_shipping_to_different_address( $args ) ) {
			return self::get_country( $args );
		}

		$value = Arr::get_first( $args['posted'] ?? [], [ 'shipping_country' ] );

		if ( $value ) {
			return (string) $value;
		}

		$customer = Customer::get_shipping_country();

		return '' !== $customer ? $customer : self::get_country( $args );
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
		return (string) Arr::get_first( $args['posted'] ?? [], [ 'shipping_postcode' ] );
	}

	/**
	 * Whether the billing and shipping countries differ.
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
}
