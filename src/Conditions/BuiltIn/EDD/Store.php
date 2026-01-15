<?php
/**
 * EDD Store Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Stats;
use ArrayPress\Conditions\Periods;

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
		return [
			'edd_store_earnings_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total store earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_order_earnings( $unit, $number );
				},
				'required_args' => [],
			],
			'edd_store_sales_period'    => [
				'label'         => __( 'Sales in Period', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total store sales count within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_order_count( $unit, $number );
				},
				'required_args' => [],
			],
			'edd_store_refunds_period'  => [
				'label'         => __( 'Refunds in Period', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total refund amount within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_refund_amount( $unit, $number );
				},
				'required_args' => [],
			],
			'edd_store_refund_rate'     => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Store refund rate percentage within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_refund_rate( $unit, $number );
				},
				'required_args' => [],
			],
			'edd_store_tax_period'      => [
				'label'         => __( 'Tax Collected in Period', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Total tax collected within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_tax( $unit, $number );
				},
				'required_args' => [],
			],
		];
	}

}