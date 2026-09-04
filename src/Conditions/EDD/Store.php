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

use ArrayPress\Conditions\Helpers\Parse;
use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Integrations\EDD\Store as StoreHelper;
use ArrayPress\Conditions\Operators;

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

			// Settings.
			'edd_store_currency'          => [
				'label'         => __( 'Currency', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select currency...', 'arraypress' ),
				'description'   => __( 'The store\'s configured currency.', 'arraypress' ),
				'options'       => Options::get_currencies(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => StoreHelper::get_currency(),
				'required_args' => [],
			],
			'edd_store_base_country'      => [
				'label'         => __( 'Base Country', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The store\'s base country.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => StoreHelper::get_base_country(),
				'required_args' => [],
			],
			'edd_store_taxes_enabled'     => [
				'label'         => __( 'Taxes Enabled', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether tax calculation is switched on.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_taxes_enabled(),
				'required_args' => [],
			],
			'edd_store_test_mode'         => [
				'label'         => __( 'Test Mode', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the store is in test mode. Worth a guard on any blocking rule — a store in test mode is not taking real money, and a rule that refuses customers there is refusing the owner\'s own smoke test.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_test_mode(),
				'required_args' => [],
			],
			'edd_store_guest_checkout'    => [
				'label'         => __( 'Guest Checkout Enabled', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether customers may check out without an account.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_guest_checkout_enabled(),
				'required_args' => [],
			],
			'edd_store_item_quantities'   => [
				'label'         => __( 'Item Quantities Enabled', 'arraypress' ),
				'group'         => __( 'Store: Settings', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether customers may buy more than one of an item.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_item_quantities_enabled(),
				'required_args' => [],
			],

			// Activity.
			'edd_store_gateway_sales'     => [
				'label'         => __( 'Gateway Sales', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'text_unit',
				'compare_as'    => 'number',
				'operators'     => Operators::numeric(),
				'placeholder'   => __( 'stripe:100', 'arraypress' ),
				'user_value'    => fn( $value ) => Parse::meta( (string) $value )['value'],
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Sales through one gateway within a period. Format: gateway:count — for example `stripe:100`. The gateway is the ID, not its display name. The count is what is compared.', 'arraypress' ),
				'compare_value' => fn( $args, $value ) => StoreHelper::get_gateway_sales( $args, $value ),
				'required_args' => [],
			],
			'edd_store_gateway_earnings'  => [
				'label'         => __( 'Gateway Earnings', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'text_unit',
				'compare_as'    => 'number',
				'operators'     => Operators::numeric(),
				'placeholder'   => __( 'stripe:5000.00', 'arraypress' ),
				'user_value'    => fn( $value ) => Parse::meta( (string) $value )['value'],
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Earnings through one gateway within a period. Format: gateway:amount — for example `stripe:5000.00`.', 'arraypress' ),
				'compare_value' => fn( $args, $value ) => StoreHelper::get_gateway_earnings( $args, $value ),
				'required_args' => [],
			],
			'edd_store_discount_usage'    => [
				'label'         => __( 'Discount Usage', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'text_unit',
				'compare_as'    => 'number',
				'operators'     => Operators::numeric(),
				'placeholder'   => __( 'e.g. 100 or SAVE10:100', 'arraypress' ),
				'user_value'    => fn( $value ) => Parse::meta( (string) $value )['value'],
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'How many times discounts were used within a period. Prefix with a code to narrow it to one — `SAVE10:100` — or leave the code off to count every discount, which is what a store-wide rule about coupon abuse is asking for.', 'arraypress' ),
				'compare_value' => fn( $args, $value ) => StoreHelper::get_discount_usage( $args, $value ),
				'required_args' => [],
			],
			'edd_store_file_downloads'    => [
				'label'         => __( 'File Downloads', 'arraypress' ),
				'group'         => __( 'Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 1000', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'File downloads recorded across the store within a period. A spike here without a matching spike in sales is the shape of a leaked download link.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_file_downloads( $args ),
				'required_args' => [],
			],
		];
	}
}
