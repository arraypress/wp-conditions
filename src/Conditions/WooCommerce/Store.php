<?php
/**
 * WooCommerce Store Conditions
 *
 * Store-wide figures are computed from an order query and cached for a few
 * minutes, so a rule using one is cheap on the second evaluation but not the
 * first. Keep them out of rules that fire on every page load.
 *
 * @package     ArrayPress\Conditions\Conditions\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\WooCommerce;

use ArrayPress\Conditions\Integrations\WooCommerce\Options;
use ArrayPress\Conditions\Integrations\WooCommerce\Store as StoreHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class Store
 *
 * Provides WooCommerce store-wide conditions.
 */
class Store {

	/**
	 * Get all store conditions.
	 *
	 * @return array<string, array>
	 *
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		$revenue  = __( 'Store: Revenue', 'arraypress' );
		$settings = __( 'Store: Settings', 'arraypress' );

		return [
			// Revenue.
			'wc_store_earnings'          => [
				'label'         => __( 'Earnings', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Gross revenue from paid orders within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_earnings_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_net_earnings'      => [
				'label'         => __( 'Net Earnings', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 4500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Gross revenue less refunds within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_net_earnings_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_sales'             => [
				'label'         => __( 'Order Count', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 100', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'How many paid orders the store took within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_sales_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_avg_order_value'   => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Average order value within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_avg_order_value( $args ),
				'required_args' => [],
			],
			'wc_store_refunds'           => [
				'label'         => __( 'Refund Amount', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total refunded within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refunds_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_refund_count'      => [
				'label'         => __( 'Refund Count', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'How many orders within a period carry a refund.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refund_count( $args ),
				'required_args' => [],
			],
			'wc_store_refund_rate'       => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Share of orders within a period carrying a refund. A sharp rise is worth acting on even when no single order looks wrong.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_refund_rate( $args ),
				'required_args' => [],
			],
			'wc_store_tax'               => [
				'label'         => __( 'Tax Collected', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Tax collected within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_tax_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_shipping'          => [
				'label'         => __( 'Shipping Collected', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 250.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Shipping collected within a period.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_shipping_in_period( $args ),
				'required_args' => [],
			],
			'wc_store_discount_savings'  => [
				'label'         => __( 'Discounts Given', 'arraypress' ),
				'group'         => $revenue,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 750.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Total discounts given within a period. A coupon leaking outside its intended audience shows up here before it shows up anywhere else.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_discount_savings( $args ),
				'required_args' => [],
			],

			// Settings.
			'wc_store_currency'          => [
				'label'         => __( 'Currency', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select currency...', 'arraypress' ),
				'description'   => __( 'The store\'s configured currency.', 'arraypress' ),
				'options'       => Options::get_currencies(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => StoreHelper::get_currency(),
				'required_args' => [],
			],
			'wc_store_base_country'      => [
				'label'         => __( 'Base Country', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The store\'s base country.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => StoreHelper::get_base_country(),
				'required_args' => [],
			],
			'wc_store_taxes_enabled'     => [
				'label'         => __( 'Taxes Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether tax calculation is switched on.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_taxes_enabled(),
				'required_args' => [],
			],
			'wc_store_coupons_enabled'   => [
				'label'         => __( 'Coupons Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether coupons are switched on.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_coupons_enabled(),
				'required_args' => [],
			],
			'wc_store_guest_checkout'    => [
				'label'         => __( 'Guest Checkout Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether customers may check out without an account.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_guest_checkout_enabled(),
				'required_args' => [],
			],

			// Settings — units, tax and fulfilment.
			'wc_store_base_state'        => [
				'label'         => __( 'Base State/Region', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA', 'arraypress' ),
				'description'   => __( 'The store\'s base state or region.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => StoreHelper::get_base_state(),
				'required_args' => [],
			],
			'wc_store_weight_unit'       => [
				'label'         => __( 'Weight Unit', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. kg', 'arraypress' ),
				'description'   => __( 'The unit weights are entered in. Every weight condition reports in this unit, so a rule set moved between stores can need it as a guard.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_weight_unit(),
				'required_args' => [],
			],
			'wc_store_dimension_unit'    => [
				'label'         => __( 'Dimension Unit', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. cm', 'arraypress' ),
				'description'   => __( 'The unit dimensions are entered in.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_dimension_unit(),
				'required_args' => [],
			],
			'wc_store_prices_include_tax' => [
				'label'         => __( 'Prices Include Tax', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether prices are entered inclusive of tax. Changes what every other money figure means.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_prices_include_tax(),
				'required_args' => [],
			],
			'wc_store_tax_based_on'      => [
				'label'         => __( 'Tax Based On', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select basis...', 'arraypress' ),
				'description'   => __( 'Which address tax is calculated from — shipping, billing, or the shop base.', 'arraypress' ),
				'options'       => Options::get_tax_based_on_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => StoreHelper::get_tax_based_on(),
				'required_args' => [],
			],
			'wc_store_shipping_enabled'  => [
				'label'         => __( 'Shipping Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether shipping is switched on at all.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_shipping_enabled(),
				'required_args' => [],
			],
			'wc_store_stock_management'  => [
				'label'         => __( 'Stock Management Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether stock is managed store-wide.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_stock_management_enabled(),
				'required_args' => [],
			],
			'wc_store_low_stock_amount'  => [
				'label'         => __( 'Low Stock Threshold', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The store-wide low-stock threshold, used by any product that does not set its own.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::get_low_stock_amount(),
				'required_args' => [],
			],
			'wc_store_reviews_enabled'   => [
				'label'         => __( 'Reviews Enabled', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'boolean',
				'description'   => __( 'Whether product reviews are switched on.', 'arraypress' ),
				'compare_value' => fn( $args ) => StoreHelper::is_reviews_enabled(),
				'required_args' => [],
			],
			'wc_store_selling_countries' => [
				'label'         => __( 'Sells To', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'Countries the store sells to. An empty list means everywhere — WooCommerce only populates the setting when selling is restricted.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => StoreHelper::get_selling_countries(),
				'required_args' => [],
			],
			'wc_store_shipping_countries' => [
				'label'         => __( 'Ships To', 'arraypress' ),
				'group'         => $settings,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'Countries the store ships to.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => StoreHelper::get_shipping_countries(),
				'required_args' => [],
			],
		];
	}
}
