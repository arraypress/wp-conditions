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