<?php
/**
 * EDD Stats Helper
 *
 * Provides simplified access to EDD Stats for condition comparisons.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

use EDD\Reports;
use EDD\Stats as EDD_Stats;

/**
 * Class Stats
 *
 * Wrapper around EDD\Stats for condition compare_value callbacks.
 */
class Stats {

	/**
	 * Get date range from EDD preset.
	 *
	 * @param string $range The range key (today, this_week, last_month, etc.).
	 *
	 * @return array{start: string, end: string} Date range in MySQL format (UTC).
	 */
	public static function get_date_range( string $range ): array {
		$dates = Reports\parse_dates_for_range( $range );

		return [
			'start' => $dates['start']->format( 'Y-m-d H:i:s' ),
			'end'   => $dates['end']->format( 'Y-m-d H:i:s' ),
		];
	}

	/**
	 * Create a Stats instance.
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

	/**
	 * Apply date range to args if provided.
	 *
	 * @param array       $args  The stats arguments.
	 * @param string|null $range Optional date range preset.
	 *
	 * @return array
	 */
	private static function apply_date_range( array $args, ?string $range ): array {
		if ( $range ) {
			$dates         = self::get_date_range( $range );
			$args['start'] = $dates['start'];
			$args['end']   = $dates['end'];
		}

		return $args;
	}

	/** -------------------------------------------------------------------------
	 * Customer Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get customer lifetime value.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $range       Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_customer_lifetime_value( int $customer_id, ?string $range = null ): float {
		$args  = self::apply_date_range( [ 'customer' => $customer_id ], $range );
		$stats = self::create( $args );

		return (float) $stats->get_customer_lifetime_value();
	}

	/**
	 * Get customer order count.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $range       Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_customer_order_count( int $customer_id, ?string $range = null ): int {
		$args  = self::apply_date_range( [ 'customer' => $customer_id ], $range );
		$stats = self::create( $args );

		return (int) $stats->get_customer_order_count();
	}

	/**
	 * Get customer refund count.
	 *
	 * @param int         $customer_id The customer ID.
	 * @param string|null $range       Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_customer_refund_count( int $customer_id, ?string $range = null ): int {
		$args  = self::apply_date_range( [ 'customer' => $customer_id ], $range );
		$stats = self::create( $args );

		return (int) $stats->get_order_refund_count();
	}

	/** -------------------------------------------------------------------------
	 * Order Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get total order earnings.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_order_earnings( ?string $range = null ): float {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get total order count.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_order_count( ?string $range = null ): int {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (int) $stats->get_order_count();
	}

	/**
	 * Get average order value.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_average_order_value( ?string $range = null ): float {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( array_merge( $args, [ 'function' => 'AVG' ] ) );

		return (float) $stats->get_order_earnings();
	}

	/**
	 * Get refund amount.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_refund_amount( ?string $range = null ): float {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (float) $stats->get_order_refund_amount();
	}

	/**
	 * Get refund count.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_refund_count( ?string $range = null ): int {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (int) $stats->get_order_refund_count();
	}

	/**
	 * Get refund rate percentage.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_refund_rate( ?string $range = null ): float {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (float) $stats->get_refund_rate();
	}

	/** -------------------------------------------------------------------------
	 * Product Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get product earnings.
	 *
	 * @param int         $product_id The product/download ID.
	 * @param int|null    $price_id   Optional price ID for variable products.
	 * @param string|null $range      Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_product_earnings( int $product_id, ?int $price_id = null, ?string $range = null ): float {
		$args = [ 'product_id' => $product_id ];

		if ( $price_id !== null ) {
			$args['price_id'] = $price_id;
		}

		$args  = self::apply_date_range( $args, $range );
		$stats = self::create( $args );

		return (float) $stats->get_order_item_earnings();
	}

	/**
	 * Get product sales count.
	 *
	 * @param int         $product_id The product/download ID.
	 * @param int|null    $price_id   Optional price ID for variable products.
	 * @param string|null $range      Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_product_sales( int $product_id, ?int $price_id = null, ?string $range = null ): int {
		$args = [ 'product_id' => $product_id ];

		if ( $price_id !== null ) {
			$args['price_id'] = $price_id;
		}

		$args  = self::apply_date_range( $args, $range );
		$stats = self::create( $args );

		return (int) $stats->get_order_item_count();
	}

	/** -------------------------------------------------------------------------
	 * Discount Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get discount usage count.
	 *
	 * @param string      $discount_code The discount code.
	 * @param string|null $range         Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_discount_usage_count( string $discount_code, ?string $range = null ): int {
		$args  = self::apply_date_range( [ 'discount_code' => $discount_code ], $range );
		$stats = self::create( $args );

		return (int) $stats->get_discount_usage_count();
	}

	/**
	 * Get discount savings amount.
	 *
	 * @param string|null $discount_code Optional discount code (null for all discounts).
	 * @param string|null $range         Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_discount_savings( ?string $discount_code = null, ?string $range = null ): float {
		$args = [];

		if ( $discount_code ) {
			$args['discount_code'] = $discount_code;
		}

		$args  = self::apply_date_range( $args, $range );
		$stats = self::create( $args );

		return (float) $stats->get_discount_savings();
	}

	/** -------------------------------------------------------------------------
	 * Tax Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get total tax collected.
	 *
	 * @param string|null $range Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_tax( ?string $range = null ): float {
		$args  = self::apply_date_range( [], $range );
		$stats = self::create( $args );

		return (float) $stats->get_tax();
	}

	/** -------------------------------------------------------------------------
	 * Gateway Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get gateway sales count.
	 *
	 * @param string      $gateway The gateway ID.
	 * @param string|null $range   Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_gateway_sales( string $gateway, ?string $range = null ): int {
		$args  = self::apply_date_range( [ 'gateway' => $gateway ], $range );
		$stats = self::create( $args );

		return (int) $stats->get_gateway_sales();
	}

	/**
	 * Get gateway earnings.
	 *
	 * @param string      $gateway The gateway ID.
	 * @param string|null $range   Optional date range preset.
	 *
	 * @return float
	 */
	public static function get_gateway_earnings( string $gateway, ?string $range = null ): float {
		$args  = self::apply_date_range( [ 'gateway' => $gateway ], $range );
		$stats = self::create( $args );

		return (float) $stats->get_gateway_earnings();
	}

	/** -------------------------------------------------------------------------
	 * File Download Stats
	 * ------------------------------------------------------------------------ */

	/**
	 * Get file download count.
	 *
	 * @param int|null    $product_id Optional product ID.
	 * @param string|null $range      Optional date range preset.
	 *
	 * @return int
	 */
	public static function get_file_download_count( ?int $product_id = null, ?string $range = null ): int {
		$args = [];

		if ( $product_id ) {
			$args['download_id'] = $product_id;
		}

		$args  = self::apply_date_range( $args, $range );
		$stats = self::create( $args );

		return (int) $stats->get_file_download_count();
	}

}