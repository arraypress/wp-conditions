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
		$posted = $args['posted'] ?? [];

		$gateway = Arr::get_first( $posted, [ 'edd-gateway' ], '' );

		if ( $gateway ) {
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
		return Arr::get_first( $args['posted'] ?? [], [
			'edd_email',
			'edd-email',
		], '' );
	}

	/**
	 * Get the checkout first name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_first_name( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'edd_first',
			'edd-first',
		], '' );
	}

	/**
	 * Get the checkout last name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_last_name( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'edd_last',
			'edd-last',
		], '' );
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
		return Arr::get_first( $args['posted'] ?? [], [
			'billing_country',
			'edd_address.country',
			'card_country',
		], '' );
	}

	/**
	 * Get the billing region/state.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_region( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'billing_state',
			'edd_address.state',
			'card_state',
		], '' );
	}

	/**
	 * Get the billing city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_city( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'billing_city',
			'edd_address.city',
			'card_city',
		], '' );
	}

	/**
	 * Get the billing postal/zip code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_postcode( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'billing_zip',
			'edd_address.zip',
			'card_zip',
		], '' );
	}

	/**
	 * Get the first line of the billing address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'card_address',
			'edd_address.line1',
			'billing_address_1',
		], '' );
	}

	/**
	 * Get the second line of the billing address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_address_2( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'card_address_2',
			'edd_address.line2',
			'billing_address_2',
		], '' );
	}

	/**
	 * Get the discount code entered at checkout.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_discount_code( array $args ): string {
		return Arr::get_first( $args['posted'] ?? [], [
			'edd-discount',
			'edd_discount',
		], '' );
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
}
