<?php
/**
 * EDD Store Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Integrations\EDD\Store as StoreHelper;

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
			// Revenue
			'edd_store_earnings'         => [
				'label'         => __( 'Earnings', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total store earnings within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_earnings_in_period( $args ),
				'required_args' => [],
			],
			'edd_store_refunds'          => [
				'label'         => __( 'Refund Amount', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total refund amount within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refunds_in_period( $args ),
				'required_args' => [],
			],
			'edd_store_refund_rate'      => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Store refund rate percentage within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refund_rate( $args ),
				'required_args' => [],
			],
			'edd_store_avg_order_value'  => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Average order value within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_avg_order_value( $args ),
				'required_args' => [],
			],
			'edd_store_discount_savings' => [
				'label'         => __( 'Discount Savings', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total discount savings given within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_discount_savings( $args ),
				'required_args' => [],
			],

			// Orders
			'edd_store_sales'            => [
				'label'         => __( 'Sales Count', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total sales count within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_sales_in_period( $args ),
				'required_args' => [],
			],
			'edd_store_refund_count'     => [
				'label'         => __( 'Refund Count', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total number of refunds within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refund_count( $args ),
				'required_args' => [],
			],

			// Tax
			'edd_store_tax'              => [
				'label'         => __( 'Tax Collected', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total tax collected within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_tax_in_period( $args ),
				'required_args' => [],
			],
		];
	}

}