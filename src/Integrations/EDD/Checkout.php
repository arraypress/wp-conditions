<?php
/**
 * EDD Checkout Helper
 *
 * Provides checkout-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

use ArrayPress\Conditions\Helpers\Address;
use ArrayPress\ArrayUtils\Arr;

/**
 * Class Checkout
 *
 * Checkout utilities for EDD conditions.
 */
class Checkout {

	/** -------------------------------------------------------------------------
	 * Payment Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the selected payment gateway.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_gateway( array $args ): string {
		$gateway = self::posted( $args, [ 'edd-gateway' ] );

		if ( '' !== $gateway ) {
			return $gateway;
		}

		if ( ! function_exists( 'edd_get_chosen_gateway' ) ) {
			return '';
		}

		return edd_get_chosen_gateway();
	}

	/** -------------------------------------------------------------------------
	 * Customer Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the checkout email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_email( array $args ): string {
		return self::posted( $args, [
			'edd_email',
			'edd-email',
		] );
	}

	/**
	 * Get the checkout first name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_first_name( array $args ): string {
		return self::posted( $args, [
			'edd_first',
			'edd-first',
		] );
	}

	/**
	 * Get the checkout last name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_last_name( array $args ): string {
		return self::posted( $args, [
			'edd_last',
			'edd-last',
		] );
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
		return self::posted( $args, [
			'billing_country',
			'edd_address.country',
			'card_country',
		] );
	}

	/**
	 * Get the billing region/state.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_region( array $args ): string {
		return self::posted( $args, [
			'billing_state',
			'edd_address.state',
			'card_state',
		] );
	}

	/**
	 * Get the billing city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_city( array $args ): string {
		return self::posted( $args, [
			'billing_city',
			'edd_address.city',
			'card_city',
		] );
	}

	/**
	 * Get the billing postal/zip code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_postcode( array $args ): string {
		return self::posted( $args, [
			'billing_zip',
			'edd_address.zip',
			'card_zip',
		] );
	}

	/**
	 * Get the first line of the billing address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address( array $args ): string {
		return self::posted( $args, [
			'card_address',
			'edd_address.line1',
			'billing_address_1',
		] );
	}

	/**
	 * Get the second line of the billing address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address_2( array $args ): string {
		return self::posted( $args, [
			'card_address_2',
			'edd_address.line2',
			'billing_address_2',
		] );
	}

	/**
	 * Get the discount code entered at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_discount_code( array $args ): string {
		return self::posted( $args, [
			'edd-discount',
			'edd_discount',
		] );
	}

	/**
	 * Whether a discount code was entered at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_discount_code( array $args ): bool {
		return '' !== self::get_discount_code( $args );
	}

	/**
	 * One posted checkout value, as a clean string.
	 *
	 * The form is the shopper's to fill in, and a shopper who submits
	 * `edd_email[]=x` used to make every helper here throw a TypeError --
	 * a way to knock the whole rule set out from the checkout page. An array
	 * is no value, and a string is unslashed and sanitized like any other
	 * request field.
	 *
	 * @param array    $args The condition arguments.
	 * @param string[] $keys The posted keys to try, in order.
	 *
	 * @return string
	 */
	private static function posted( array $args, array $keys ): string {
		$value = Arr::get_first( (array) ( $args['posted'] ?? [] ), $keys, '' );

		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( (string) $value ) );
	}
	/** -------------------------------------------------------------------------
	 * Form heuristics
	 * ------------------------------------------------------------------------ */

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
	 * @return bool|null Null when the form has no address.
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
		return Address::names_identical( self::get_first_name( $args ), self::get_last_name( $args ) );
	}

	/**
	 * Whether the name typed appears in the email typed.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool|null Null when the form has no name or no email.
	 */
	public static function name_matches_email( array $args ): ?bool {
		$first = self::get_first_name( $args );
		$last  = self::get_last_name( $args );
		$email = self::get_email( $args );

		if ( '' === $email || ( '' === $first && '' === $last ) ) {
			return null;
		}

		return Address::name_matches_email( $first, $last, $email );
	}
}
