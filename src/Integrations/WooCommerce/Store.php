<?php
/**
 * WooCommerce Store Helper
 *
 * Store-level settings and period aggregates. The period comes in as the unit
 * half of a number_unit field, the same way EDD's store conditions read it.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

/**
 * Class Store
 *
 * Store utilities for WooCommerce conditions.
 */
class Store {

	/**
	 * The period carried by the rule, defaulting to the current month.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private static function period( array $args ): string {
		$unit = $args['_unit'] ?? 'this_month';

		return is_string( $unit ) && '' !== $unit ? $unit : 'this_month';
	}

	/** -------------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------------ */

	/**
	 * Store currency code.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_currency(): string {
		return function_exists( 'get_woocommerce_currency' ) ? (string) get_woocommerce_currency() : '';
	}

	/**
	 * Store base country code.
	 *
	 * WooCommerce stores this as "GB" or as "GB:LND" when a state is set, so
	 * the state half is dropped here.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_base_country(): string {
		if ( ! function_exists( 'wc_get_base_location' ) ) {
			return '';
		}

		$base = wc_get_base_location();

		return (string) ( $base['country'] ?? '' );
	}

	/**
	 * Store base state code.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_base_state(): string {
		if ( ! function_exists( 'wc_get_base_location' ) ) {
			return '';
		}

		$base = wc_get_base_location();

		return (string) ( $base['state'] ?? '' );
	}

	/**
	 * Weight unit configured for the store.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_weight_unit(): string {
		return (string) get_option( 'woocommerce_weight_unit', '' );
	}

	/**
	 * Dimension unit configured for the store.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_dimension_unit(): string {
		return (string) get_option( 'woocommerce_dimension_unit', '' );
	}

	/**
	 * Whether tax calculation is enabled.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_taxes_enabled(): bool {
		return function_exists( 'wc_tax_enabled' ) ? (bool) wc_tax_enabled() : false;
	}

	/**
	 * Whether coupons are enabled.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_coupons_enabled(): bool {
		return function_exists( 'wc_coupons_enabled' ) ? (bool) wc_coupons_enabled() : false;
	}

	/**
	 * Whether guest checkout is allowed.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_guest_checkout_enabled(): bool {
		return 'yes' === get_option( 'woocommerce_enable_guest_checkout', 'no' );
	}

	/**
	 * Whether prices are entered inclusive of tax.
	 *
	 * Changes what every other money figure means, so a rule set moved between
	 * two stores can legitimately need this as a guard.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_prices_include_tax(): bool {
		return 'yes' === get_option( 'woocommerce_prices_include_tax', 'no' );
	}

	/**
	 * Which address tax is calculated from -- shipping, billing, or base.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_based_on(): string {
		return (string) get_option( 'woocommerce_tax_based_on', 'shipping' );
	}

	/**
	 * Whether shipping is enabled at all.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_shipping_enabled(): bool {
		return function_exists( 'wc_shipping_enabled' ) ? (bool) wc_shipping_enabled() : false;
	}

	/**
	 * Whether stock management is switched on store-wide.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_stock_management_enabled(): bool {
		return 'yes' === get_option( 'woocommerce_manage_stock', 'no' );
	}

	/**
	 * Whether reviews are enabled.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_reviews_enabled(): bool {
		return 'yes' === get_option( 'woocommerce_enable_reviews', 'no' );
	}

	/**
	 * The store-wide low stock threshold.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_low_stock_amount(): int {
		return (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
	}

	/**
	 * Countries the store sells to.
	 *
	 * An empty list means "everywhere" -- WooCommerce only populates the option
	 * when selling is restricted to specific countries.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_selling_countries(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->countries ) || ! is_object( $wc->countries ) ) {
			return [];
		}

		return array_map( 'strval', array_keys( (array) $wc->countries->get_allowed_countries() ) );
	}

	/**
	 * Countries the store ships to.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_countries(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->countries ) || ! is_object( $wc->countries ) ) {
			return [];
		}

		return array_map( 'strval', array_keys( (array) $wc->countries->get_shipping_countries() ) );
	}

	/** -------------------------------------------------------------------------
	 * Period aggregates
	 * ------------------------------------------------------------------------ */

	/**
	 * Gross revenue in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_earnings_in_period( array $args ): float {
		return Stats::get_earnings( self::period( $args ) );
	}

	/**
	 * Net revenue in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_net_earnings_in_period( array $args ): float {
		return Stats::get_net_earnings( self::period( $args ) );
	}

	/**
	 * Order count in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_sales_in_period( array $args ): int {
		return Stats::get_order_count( self::period( $args ) );
	}

	/**
	 * Average order value in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_avg_order_value( array $args ): float {
		return Stats::get_average_order_value( self::period( $args ) );
	}

	/**
	 * Amount refunded in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_refunds_in_period( array $args ): float {
		return Stats::get_refund_amount( self::period( $args ) );
	}

	/**
	 * Number of refunded orders in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_count( array $args ): int {
		return Stats::get_refund_count( self::period( $args ) );
	}

	/**
	 * Share of orders refunded in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_rate( array $args ): float {
		return Stats::get_refund_rate( self::period( $args ) );
	}

	/**
	 * Tax collected in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_in_period( array $args ): float {
		return Stats::get_tax( self::period( $args ) );
	}

	/**
	 * Shipping collected in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_in_period( array $args ): float {
		return Stats::get_shipping( self::period( $args ) );
	}

	/**
	 * Discounts given in the period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_savings( array $args ): float {
		return Stats::get_discount_savings( self::period( $args ) );
	}
}
