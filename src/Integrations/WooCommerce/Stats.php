<?php
/**
 * WooCommerce Stats Helper
 *
 * Store-wide aggregates over a date range. WooCommerce has no equivalent of
 * EDD's Stats class outside the Analytics package, so these are computed from
 * an order query and cached briefly -- a condition can be evaluated on every
 * request, and re-summing a year of orders each time would be the slowest thing
 * in the rule set by a wide margin.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use WC_Order;

/**
 * Class Stats
 *
 * Aggregate order figures for WooCommerce conditions.
 */
class Stats {

	/**
	 * How long an aggregate is reused before being recomputed.
	 */
	private const CACHE_TTL = 5 * MINUTE_IN_SECONDS;

	/**
	 * Statuses counted as revenue.
	 */
	private const PAID_STATUSES = [ 'wc-processing', 'wc-completed' ];

	/** -------------------------------------------------------------------------
	 * Ranges
	 * ------------------------------------------------------------------------ */

	/**
	 * Resolve a range preset into start and end datetimes.
	 *
	 * Site time, not UTC: a store owner writing "today" means their own day.
	 *
	 * @param string $range Range preset.
	 *
	 * @return array{start: string, end: string}
	 *
	 * @since 1.0.0
	 */
	public static function get_date_range( string $range ): array {
		$now   = (int) current_time( 'timestamp' );
		$day   = static fn( int $ts ): string => gmdate( 'Y-m-d 00:00:00', $ts );
		$end   = static fn( int $ts ): string => gmdate( 'Y-m-d 23:59:59', $ts );
		$start = $day( $now );
		$stop  = $end( $now );

		switch ( $range ) {
			case 'yesterday':
				$start = $day( $now - DAY_IN_SECONDS );
				$stop  = $end( $now - DAY_IN_SECONDS );
				break;

			case 'this_week':
				$start = $day( strtotime( 'monday this week', $now ) );
				break;

			case 'last_week':
				$start = $day( strtotime( 'monday last week', $now ) );
				$stop  = $end( strtotime( 'sunday last week', $now ) );
				break;

			case 'this_month':
				$start = gmdate( 'Y-m-01 00:00:00', $now );
				break;

			case 'last_month':
				$first = strtotime( 'first day of last month', $now );
				$start = gmdate( 'Y-m-01 00:00:00', $first );
				$stop  = $end( strtotime( 'last day of last month', $now ) );
				break;

			case 'this_quarter':
				$quarter = (int) ceil( (int) gmdate( 'n', $now ) / 3 );
				$start   = gmdate( 'Y-', $now ) . str_pad( (string) ( ( $quarter - 1 ) * 3 + 1 ), 2, '0', STR_PAD_LEFT ) . '-01 00:00:00';
				break;

			case 'this_year':
				$start = gmdate( 'Y-01-01 00:00:00', $now );
				break;

			case 'last_year':
				$start = gmdate( 'Y-01-01 00:00:00', strtotime( '-1 year', $now ) );
				$stop  = gmdate( 'Y-12-31 23:59:59', strtotime( '-1 year', $now ) );
				break;

			case 'last_7_days':
				$start = $day( $now - ( 6 * DAY_IN_SECONDS ) );
				break;

			case 'last_30_days':
				$start = $day( $now - ( 29 * DAY_IN_SECONDS ) );
				break;

			case 'last_90_days':
				$start = $day( $now - ( 89 * DAY_IN_SECONDS ) );
				break;

			case 'all_time':
				$start = '1970-01-01 00:00:00';
				break;
		}

		return [
			'start' => $start,
			'end'   => $stop,
		];
	}

	/** -------------------------------------------------------------------------
	 * Aggregates
	 * ------------------------------------------------------------------------ */

	/**
	 * Every figure for a range, computed in one pass.
	 *
	 * @param string $range Range preset.
	 *
	 * @return array{orders: int, gross: float, tax: float, shipping: float, discount: float, refunded: float, refunds: int}
	 *
	 * @since 1.0.0
	 */
	public static function get_totals( string $range = 'this_month' ): array {
		$empty = [
			'orders'   => 0,
			'gross'    => 0.0,
			'tax'      => 0.0,
			'shipping' => 0.0,
			'discount' => 0.0,
			'refunded' => 0.0,
			'refunds'  => 0,
		];

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return $empty;
		}

		$key    = 'wpc_wc_stats_' . md5( $range );
		$cached = get_transient( $key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$dates = self::get_date_range( $range );

		$orders = wc_get_orders( [
			'limit'        => -1,
			'status'       => self::PAID_STATUSES,
			'date_created' => $dates['start'] . '...' . $dates['end'],
		] );

		$totals = $empty;

		foreach ( (array) $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$refunded = (float) $order->get_total_refunded();

			++$totals['orders'];
			$totals['gross']    += (float) $order->get_total();
			$totals['tax']      += (float) $order->get_total_tax();
			$totals['shipping'] += (float) $order->get_shipping_total();
			$totals['discount'] += (float) $order->get_total_discount();
			$totals['refunded'] += $refunded;

			if ( $refunded > 0 ) {
				++$totals['refunds'];
			}
		}

		set_transient( $key, $totals, self::CACHE_TTL );

		return $totals;
	}

	/**
	 * Gross revenue in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_earnings( string $range = 'this_month' ): float {
		return (float) self::get_totals( $range )['gross'];
	}

	/**
	 * Net revenue in a range -- gross less refunds.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_net_earnings( string $range = 'this_month' ): float {
		$totals = self::get_totals( $range );

		return (float) $totals['gross'] - (float) $totals['refunded'];
	}

	/**
	 * Order count in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_order_count( string $range = 'this_month' ): int {
		return (int) self::get_totals( $range )['orders'];
	}

	/**
	 * Average order value in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_average_order_value( string $range = 'this_month' ): float {
		$totals = self::get_totals( $range );

		if ( 0 === (int) $totals['orders'] ) {
			return 0.0;
		}

		return round( (float) $totals['gross'] / (int) $totals['orders'], 2 );
	}

	/**
	 * Amount refunded in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_amount( string $range = 'this_month' ): float {
		return (float) self::get_totals( $range )['refunded'];
	}

	/**
	 * How many orders in a range carry a refund.
	 *
	 * @param string $range Range preset.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_count( string $range = 'this_month' ): int {
		return (int) self::get_totals( $range )['refunds'];
	}

	/**
	 * Share of orders in a range carrying a refund.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float 0-100.
	 *
	 * @since 1.0.0
	 */
	public static function get_refund_rate( string $range = 'this_month' ): float {
		$totals = self::get_totals( $range );

		if ( 0 === (int) $totals['orders'] ) {
			return 0.0;
		}

		return round( ( (int) $totals['refunds'] / (int) $totals['orders'] ) * 100, 2 );
	}

	/**
	 * Tax collected in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_tax( string $range = 'this_month' ): float {
		return (float) self::get_totals( $range )['tax'];
	}

	/**
	 * Shipping collected in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping( string $range = 'this_month' ): float {
		return (float) self::get_totals( $range )['shipping'];
	}

	/**
	 * Discounts given in a range.
	 *
	 * @param string $range Range preset.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_savings( string $range = 'this_month' ): float {
		return (float) self::get_totals( $range )['discount'];
	}
}
