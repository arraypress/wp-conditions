<?php
/**
 * EDD Store Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Stats;
use ArrayPress\Conditions\Helpers\Parser;
use ArrayPress\Conditions\Helpers\Periods;

/**
 * Class Store
 *
 * Provides EDD store-wide conditions.
 */
class Store {

	/**
	 * Get all store conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_revenue_conditions(),
			self::get_order_conditions(),
			self::get_tax_conditions()
		);
	}

	/**
	 * Get revenue-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_revenue_conditions(): array {
		return [
			'edd_store_earnings_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'Store: Revenue', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total store earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$period = Parser::get_number_unit( $args );

					return Stats::get_order_earnings( $period['unit'], $period['number'] );
				},
				'required_args' => [],
			],
			'edd_store_refunds_period'  => [
				'label'         => __( 'Refunds in Period', 'arraypress' ),
				'group'         => __( 'Store: Revenue', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total refund amount within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$period = Parser::get_number_unit( $args );

					return Stats::get_refund_amount( $period['unit'], $period['number'] );
				},
				'required_args' => [],
			],
			'edd_store_refund_rate'     => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'Store: Revenue', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Store refund rate percentage within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$period = Parser::get_number_unit( $args );

					return Stats::get_refund_rate( $period['unit'], $period['number'] );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get order-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_order_conditions(): array {
		return [
			'edd_store_sales_period' => [
				'label'         => __( 'Sales in Period', 'arraypress' ),
				'group'         => __( 'Store: Orders', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total store sales count within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$period = Parser::get_number_unit( $args );

					return Stats::get_order_count( $period['unit'], $period['number'] );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get tax-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_tax_conditions(): array {
		return [
			'edd_store_tax_period' => [
				'label'         => __( 'Tax Collected in Period', 'arraypress' ),
				'group'         => __( 'Store: Tax', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total tax collected within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$period = Parser::get_number_unit( $args );

					return Stats::get_tax( $period['unit'], $period['number'] );
				},
				'required_args' => [],
			],
		];
	}

}