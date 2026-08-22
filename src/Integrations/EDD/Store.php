<?php
/**
 * EDD Store Helper
 *
 * Provides store-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

/**
 * Class Store
 *
 * Store utilities for EDD conditions.
 */
class Store {

	/** -------------------------------------------------------------------------
	 * Revenue Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get store earnings within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_earnings_in_period( array $args ): float {
		return Stats::get_order_earnings( $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Get store refund amount within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_refunds_in_period( array $args ): float {
		return Stats::get_refund_amount( $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Get store refund rate within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_refund_rate( array $args ): float {
		return Stats::get_refund_rate( $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Get average order value within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_avg_order_value( array $args ): float {
		return Stats::get_average_order_value( $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Get total discount savings within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_discount_savings( array $args ): float {
		return Stats::get_discount_savings( null, $args['_unit'] ?? 'this_month' );
	}

	/** -------------------------------------------------------------------------
	 * Order Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get store sales count within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_sales_in_period( array $args ): int {
		return Stats::get_order_count( $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Get store refund count within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_refund_count( array $args ): int {
		return Stats::get_refund_count( $args['_unit'] ?? 'this_month' );
	}

	/** -------------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------------ */

	/**
	 * Store currency code.
	 *
	 * @return string
	 */
	public static function get_currency(): string {
		return function_exists( 'edd_get_currency' ) ? (string) edd_get_currency() : '';
	}

	/**
	 * Store base country code.
	 *
	 * @return string
	 */
	public static function get_base_country(): string {
		return function_exists( 'edd_get_shop_country' ) ? (string) edd_get_shop_country() : '';
	}

	/**
	 * Whether tax calculation is enabled.
	 *
	 * @return bool
	 */
	public static function is_taxes_enabled(): bool {
		return function_exists( 'edd_use_taxes' ) && (bool) edd_use_taxes();
	}

	/**
	 * Whether the store is in test mode.
	 *
	 * Worth a guard on any blocking rule: a store still in test mode is not
	 * taking real money, and a rule that refuses customers there is refusing
	 * the shop owner's own smoke test.
	 *
	 * @return bool
	 */
	public static function is_test_mode(): bool {
		return function_exists( 'edd_is_test_mode' ) && (bool) edd_is_test_mode();
	}

	/**
	 * Whether guests may check out.
	 *
	 * EDD stores the inverse -- edd_no_guest_checkout() is true when an account
	 * is required -- so the answer is flipped here to read the way a rule does.
	 *
	 * @return bool
	 */
	public static function is_guest_checkout_enabled(): bool {
		return function_exists( 'edd_no_guest_checkout' ) && ! edd_no_guest_checkout();
	}

	/**
	 * Whether item quantities are enabled at checkout.
	 *
	 * @return bool
	 */
	public static function is_item_quantities_enabled(): bool {
		return function_exists( 'edd_item_quantities_enabled' ) && (bool) edd_item_quantities_enabled();
	}

	/** -------------------------------------------------------------------------
	 * Gateway Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Sales through one gateway within a period.
	 *
	 * The gateway comes from the text half of the rule, the period from the
	 * unit half -- "PayPal sales this month" is one field, not two.
	 *
	 * @param array $args  The condition arguments.
	 * @param mixed $value The rule value, carrying the gateway.
	 *
	 * @return int
	 */
	public static function get_gateway_sales( array $args, $value = null ): int {
		$gateway = self::gateway_from_rule( $value );

		if ( '' === $gateway ) {
			return 0;
		}

		return Stats::get_gateway_sales( $gateway, $args['_unit'] ?? 'this_month' );
	}

	/**
	 * Earnings through one gateway within a period.
	 *
	 * @param array $args  The condition arguments.
	 * @param mixed $value The rule value, carrying the gateway.
	 *
	 * @return float
	 */
	public static function get_gateway_earnings( array $args, $value = null ): float {
		$gateway = self::gateway_from_rule( $value );

		if ( '' === $gateway ) {
			return 0.0;
		}

		return Stats::get_gateway_earnings( $gateway, $args['_unit'] ?? 'this_month' );
	}

	/**
	 * The gateway half of a "gateway:amount" rule.
	 *
	 * @param mixed $value The rule value.
	 *
	 * @return string
	 */
	private static function gateway_from_rule( $value ): string {
		if ( ! is_string( $value ) || ! str_contains( $value, ':' ) ) {
			return '';
		}

		return trim( strtok( $value, ':' ) );
	}

	/** -------------------------------------------------------------------------
	 * Discount and Download Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * How many times discounts were used within a period.
	 *
	 * An empty code counts every discount, which is what a store-wide rule
	 * about coupon abuse is asking for.
	 *
	 * @param array $args  The condition arguments.
	 * @param mixed $value The rule value, optionally carrying a code.
	 *
	 * @return int
	 */
	public static function get_discount_usage( array $args, $value = null ): int {
		$code = self::gateway_from_rule( $value );

		return Stats::get_discount_usage_count( $code, $args['_unit'] ?? 'this_month' );
	}

	/**
	 * File downloads recorded within a period.
	 *
	 * A spike here without a matching spike in sales is the shape of a leaked
	 * download link.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_file_downloads( array $args ): int {
		return Stats::get_file_download_count( null, $args['_unit'] ?? 'this_month' );
	}

	/** -------------------------------------------------------------------------
	 * Tax Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get store tax collected within a period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_tax_in_period( array $args ): float {
		return Stats::get_tax( $args['_unit'] ?? 'this_month' );
	}
}
