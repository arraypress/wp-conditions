<?php
/**
 * EDD Stats Helper
 *
 * Provides simplified access to EDD Stats for condition comparisons.
 * Uses EDD's date infrastructure for proper timezone handling.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers;

use EDD\Stats as EDD_Stats;
use Exception;

/**
 * Class Stats
 *
 * Wrapper around EDD\Stats for condition compare_value callbacks.
 */
class Stats {

	/**
	 * Get date range using EDD's date utilities.
	 *
	 * @param string $unit   The time unit (hour, day, week, month, year).
	 * @param int    $amount The number of units.
	 *
	 * @return array{start: string, end: string} Date range in MySQL format.
	 * @throws Exception
	 */
	public static function get_date_range( string $unit, int $amount ): array {
		$date = EDD()->utils->date( 'now', edd_get_timezone_id(), true );

		$start = match ( $unit ) {
			'hour' => $date->copy()->subHours( $amount )->startOfMinute(),
			'week' => $date->copy()->subWeeks( $amount )->startOfDay(),
			'month' => $date->copy()->subMonths( $amount )->startOfDay(),
			'year' => $date->copy()->subYears( $amount )->startOfDay(),
			default => $date->copy()->subDays( $amount )->startOfDay(),
		};

		$end = $date->copy()->endOfDay();

		// Convert to UTC for database queries
		return [
			'start' => edd_get_utc_equivalent_date( $start )->format( 'Y-m-d H:i:s' ),
			'end'   => edd_get_utc_equivalent_date( $end )->format( 'Y-m-d H:i:s' ),
		];
	}

	/**
	 * Create a Stats instance with optional date range.
	 *
	 * @param array $args Stats arguments.
	 *
	 * @return EDD_Stats
	 */
	private static function create( array $args = [] ): EDD_Stats {
		$defaults = [
			'output' => 'raw',
		];

		return new EDD_Stats( wp_parse_args( $args, $defaults ) );
	}

	/** Customer Stats *********************************************************/

	/**
	 * Get customer lifetime value.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $unit        Optional time unit for period filtering.
	 * @param int         $amount      Optional number of units.
	 *
	 * @return float
	 */
	public static function get_customer_lifetime_value( int $customer_id, ?string $unit = null, int $amount = 1 ): float {
		$args = [
			'customer' => $customer_id,
		];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_customer_lifetime_value();
	}

	/**
	 * Get customer order count.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $unit        Optional time unit for period filtering.
	 * @param int         $amount      Optional number of units.
	 *
	 * @return int
	 */
	public static function get_customer_order_count( int $customer_id, ?string $unit = null, int $amount = 1 ): int {
		$args = [
			'customer' => $customer_id,
		];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_customer_order_count();
	}

	/**
	 * Get customer refund count.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $unit        Optional time unit.
	 * @param int         $amount      Optional number of units.
	 *
	 * @return int
	 */
	public static function get_customer_refund_count( int $customer_id, ?string $unit = null, int $amount = 1 ): int {
		$args = [
			'customer' => $customer_id,
		];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_order_refund_count();
	}

	/** Order Stats ************************************************************/

	/**
	 * Get total order earnings.
	 *
	 * @param string|null $unit   Optional time unit.
	 * @param int         $amount Optional number of units.
	 *
	 * @return float
	 */
	public static function get_order_earnings( ?string $unit = null, int $amount = 1 ): float {
		$args = [];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get total order count.
	 *
	 * @param string|null $unit   Optional time unit.
	 * @param int         $amount Optional number of units.
	 *
	 * @return int
	 */
	public static function get_order_count( ?string $unit = null, int $amount = 1 ): int {
		$args = [];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_order_count();
	}

	/**
	 * Get refund amount.
	 *
	 * @param string|null $unit   Optional time unit.
	 * @param int         $amount Optional number of units.
	 *
	 * @return float
	 */
	public static function get_refund_amount( ?string $unit = null, int $amount = 1 ): float {
		$args = [];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_order_refund_amount();
	}

	/**
	 * Get refund rate percentage.
	 *
	 * @param string|null $unit   Optional time unit.
	 * @param int         $amount Optional number of units.
	 *
	 * @return float
	 */
	public static function get_refund_rate( ?string $unit = null, int $amount = 1 ): float {
		$args = [];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_refund_rate();
	}

	/** Product Stats **********************************************************/

	/**
	 * Get product earnings.
	 *
	 * @param int         $product_id The product/download ID.
	 * @param int|null    $price_id   Optional price ID for variable products.
	 * @param string|null $unit       Optional time unit.
	 * @param int         $amount     Optional number of units.
	 *
	 * @return float
	 */
	public static function get_product_earnings( int $product_id, ?int $price_id = null, ?string $unit = null, int $amount = 1 ): float {
		$args = [
			'product_id' => $product_id,
		];

		if ( $price_id !== null ) {
			$args['price_id'] = $price_id;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_order_item_earnings();
	}

	/**
	 * Get product sales count.
	 *
	 * @param int         $product_id The product/download ID.
	 * @param int|null    $price_id   Optional price ID for variable products.
	 * @param string|null $unit       Optional time unit.
	 * @param int         $amount     Optional number of units.
	 *
	 * @return int
	 */
	public static function get_product_sales( int $product_id, ?int $price_id = null, ?string $unit = null, int $amount = 1 ): int {
		$args = [
			'product_id' => $product_id,
		];

		if ( $price_id !== null ) {
			$args['price_id'] = $price_id;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_order_item_count();
	}

	/** Discount Stats *********************************************************/

	/**
	 * Get discount usage count.
	 *
	 * @param string      $discount_code The discount code.
	 * @param string|null $unit          Optional time unit.
	 * @param int         $amount        Optional number of units.
	 *
	 * @return int
	 */
	public static function get_discount_usage_count( string $discount_code, ?string $unit = null, int $amount = 1 ): int {
		$args = [
			'discount_code' => $discount_code,
		];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_discount_usage_count();
	}

	/**
	 * Get discount savings amount.
	 *
	 * @param string|null $discount_code Optional discount code (null for all discounts).
	 * @param string|null $unit          Optional time unit.
	 * @param int         $amount        Optional number of units.
	 *
	 * @return float
	 */
	public static function get_discount_savings( ?string $discount_code = null, ?string $unit = null, int $amount = 1 ): float {
		$args = [];

		if ( $discount_code ) {
			$args['discount_code'] = $discount_code;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_discount_savings();
	}

	/** Tax Stats **************************************************************/

	/**
	 * Get total tax collected.
	 *
	 * @param string|null $unit   Optional time unit.
	 * @param int         $amount Optional number of units.
	 *
	 * @return float
	 */
	public static function get_tax( ?string $unit = null, int $amount = 1 ): float {
		$args = [];

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_tax();
	}

	/**
	 * Get tax by location.
	 *
	 * @param string      $country The country code.
	 * @param string|null $region  Optional region/state code.
	 * @param string|null $unit    Optional time unit.
	 * @param int         $amount  Optional number of units.
	 *
	 * @return float
	 */
	public static function get_tax_by_location( string $country, ?string $region = null, ?string $unit = null, int $amount = 1 ): float {
		$args = [
			'country' => $country,
		];

		if ( $region ) {
			$args['region'] = $region;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (float) $stats->get_tax_by_location();
	}

	/** Gateway Stats **********************************************************/

	/**
	 * Get gateway sales count.
	 *
	 * @param string|null $gateway Optional gateway ID (null for all).
	 * @param string|null $unit    Optional time unit.
	 * @param int         $amount  Optional number of units.
	 *
	 * @return int|array Int if gateway specified, array of gateway => count otherwise.
	 */
	public static function get_gateway_sales( ?string $gateway = null, ?string $unit = null, int $amount = 1 ): int|array {
		$args = [];

		if ( $gateway ) {
			$args['gateway'] = $gateway;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return $stats->get_gateway_sales();
	}

	/**
	 * Get gateway earnings.
	 *
	 * @param string|null $gateway Optional gateway ID (null for all).
	 * @param string|null $unit    Optional time unit.
	 * @param int         $amount  Optional number of units.
	 *
	 * @return float|array Float if gateway specified, array otherwise.
	 */
	public static function get_gateway_earnings( ?string $gateway = null, ?string $unit = null, int $amount = 1 ): float|array {
		$args = [];

		if ( $gateway ) {
			$args['gateway'] = $gateway;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return $stats->get_gateway_earnings();
	}

	/** File Download Stats ****************************************************/

	/**
	 * Get file download count.
	 *
	 * @param int|null    $product_id Optional product ID.
	 * @param string|null $unit       Optional time unit.
	 * @param int         $amount     Optional number of units.
	 *
	 * @return int
	 */
	public static function get_file_download_count( ?int $product_id = null, ?string $unit = null, int $amount = 1 ): int {
		$args = [];

		if ( $product_id ) {
			$args['download_id'] = $product_id;
		}

		if ( $unit ) {
			$range         = self::get_date_range( $unit, $amount );
			$args['start'] = $range['start'];
			$args['end']   = $range['end'];
		}

		$stats = self::create( $args );

		return (int) $stats->get_file_download_count();
	}

}