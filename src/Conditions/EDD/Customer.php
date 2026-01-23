<?php
/**
 * EDD Customer Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Customer as CustomerHelper;
use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Operators;

/**
 * Class Customer
 *
 * Provides EDD customer-related conditions.
 */
class Customer {

	/**
	 * Get all customer conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Profile
			'edd_customer_segment'              => [
				'label'         => __( 'Segment', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select segment...', 'arraypress' ),
				'description'   => __( 'Whether the customer is a first-time or returning buyer.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => CustomerHelper::get_segment_options(),
				'compare_value' => fn( $args ) => CustomerHelper::get_segment( $args ),
				'required_args' => [],
			],
			'edd_customer_email'                => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match customer email against patterns.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_email( $args ),
				'required_args' => [],
			],
			'edd_customer_date_created'         => [
				'label'         => __( 'Date Registered', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the customer account was created.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_date_created( $args ),
				'required_args' => [],
			],
			'edd_customer_account_age'          => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the customer has been registered.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_account_age( $args ),
				'required_args' => [],
			],

			// Purchase History
			'edd_customer_order_count'          => [
				'label'         => __( 'Order Count', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of orders placed.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_order_count( $args ),
				'required_args' => [],
			],
			'edd_customer_total_spent'          => [
				'label'         => __( 'Total Spent', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total amount spent.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_total_spent( $args ),
				'required_args' => [],
			],
			'edd_customer_avg_order_value'      => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The average value of orders.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_average_order_value( $args ),
				'required_args' => [],
			],
			'edd_customer_purchased_products'   => [
				'label'         => __( 'Purchased Products', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_product_ids( $args ),
				'required_args' => [],
			],
			'edd_customer_purchased_categories' => [
				'label'         => __( 'Purchased Categories', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_term_ids( $args, 'download_category' ),
				'required_args' => [],
			],
			'edd_customer_purchased_tags'       => [
				'label'         => __( 'Purchased Tags', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_term_ids( $args, 'download_tag' ),
				'required_args' => [],
			],

			// Activity
			'edd_customer_last_order_date'      => [
				'label'         => __( 'Last Order Date', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date of the customer\'s most recent order.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_last_order_date( $args ),
				'required_args' => [],
			],
			'edd_customer_days_since_order'     => [
				'label'         => __( 'Days Since Last Order', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'Time since the customer\'s last order.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_days_since_last_order( $args ),
				'required_args' => [],
			],
			'edd_customer_orders_in_period'     => [
				'label'         => __( 'Orders in Period', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Number of orders placed within a date range.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_orders_in_period( $args ),
				'required_args' => [],
			],
			'edd_customer_spend_in_period'      => [
				'label'         => __( 'Spend in Period', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Amount spent within a date range.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_spend_in_period( $args ),
				'required_args' => [],
			],
			'edd_customer_ip_count'             => [
				'label'         => __( 'Unique IP Count', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of unique IP addresses used.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_unique_ip_count( $args ),
				'required_args' => [],
			],
			'edd_customer_refund_count'         => [
				'label'         => __( 'Refund Count', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Total number of refunded orders.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_count( $args ),
				'required_args' => [],
			],
			'edd_customer_refund_rate'          => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'Percentage of orders that have been refunded.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_rate( $args ),
				'required_args' => [],
			],
		];
	}

}