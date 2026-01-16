<?php
/**
 * EDD Customer Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Customer as CustomerHelper;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Stats;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Helpers\Periods;

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
			'edd_customer_type'                 => [
				'label'         => __( 'Customer Type', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'Whether the customer is new or returning.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'new', 'label' => __( 'New Customer', 'arraypress' ) ],
					[ 'value' => 'returning', 'label' => __( 'Returning Customer', 'arraypress' ) ],
				],
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return 'new';
					}

					$customer = edd_get_customer( $customer_id );

					return $customer && $customer->purchase_count > 0 ? 'returning' : 'new';
				},
				'required_args' => [],
			],
			'edd_customer_order_count'          => [
				'label'         => __( 'Total Order Count', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of orders placed by the customer.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return 0;
					}

					$customer = edd_get_customer( $customer_id );

					return $customer ? (int) $customer->purchase_count : 0;
				},
				'required_args' => [],
			],
			'edd_customer_total_spent'          => [
				'label'         => __( 'Total Spent', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total amount spent by the customer.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return 0;
					}

					$customer = edd_get_customer( $customer_id );

					return $customer ? (float) $customer->purchase_value : 0;
				},
				'required_args' => [],
			],
			'edd_customer_order_count_period'   => [
				'label'         => __( 'Orders in Period', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Number of orders placed within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_customer_order_count( $customer_id, $unit, $number );
				},
				'required_args' => [],
			],
			'edd_customer_spend_period'         => [
				'label'         => __( 'Spend in Period', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Amount spent within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_customer_lifetime_value( $customer_id, $unit, $number );
				},
				'required_args' => [],
			],
			'edd_customer_email_domain'         => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain, press Enter...', 'arraypress' ),
				'description'   => __( 'Match if customer email ends with specified domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return '';
					}

					$customer = edd_get_customer( $customer_id );

					return $customer ? $customer->email : '';
				},
				'required_args' => [],
			],
			'edd_customer_email'                => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'Enter pattern, press Enter...', 'arraypress' ),
				'description'   => __( 'Match customer email. Supports: full email, @domain.com, .edu, partial domain.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return '';
					}

					$customer = edd_get_customer( $customer_id );

					return $customer ? $customer->email : '';
				},
				'required_args' => [],
			],
			'edd_customer_purchased_products'   => [
				'label'         => __( 'Purchased Products', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased specific products.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return [];
					}

					return CustomerHelper::get_product_ids( $customer_id );
				},
				'required_args' => [],
			],
			'edd_customer_purchased_categories' => [
				'label'         => __( 'Purchased Categories', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased from specific categories.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return [];
					}

					return CustomerHelper::get_term_ids( $customer_id, 'download_category' );
				},
				'required_args' => [],
			],
			'edd_customer_purchased_tags'       => [
				'label'         => __( 'Purchased Tags', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased products with specific tags.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return [];
					}

					return CustomerHelper::get_term_ids( $customer_id, 'download_tag' );
				},
				'required_args' => [],
			],
			'edd_customer_age'                  => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the customer has been registered.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return 0;
					}

					$customer = edd_get_customer( $customer_id );

					if ( ! $customer || empty( $customer->date_created ) ) {
						return 0;
					}

					$created = strtotime( $customer->date_created );
					$now     = current_time( 'timestamp' );
					$diff    = $now - $created;

					$unit = $args['_unit'] ?? 'day';

					return match ( $unit ) {
						'week'  => (int) floor( $diff / WEEK_IN_SECONDS ),
						'month' => (int) floor( $diff / MONTH_IN_SECONDS ),
						'year'  => (int) floor( $diff / YEAR_IN_SECONDS ),
						default => (int) floor( $diff / DAY_IN_SECONDS ),
					};
				},
				'required_args' => [],
			],
			'edd_customer_ip_count'             => [
				'label'         => __( 'Unique IP Count', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of unique IP addresses used by the customer.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return 0;
					}

					$orders = edd_get_orders( [
						'customer_id' => $customer_id,
						'status__in'  => edd_get_complete_order_statuses(),
						'number'      => 999999,
					] );

					if ( empty( $orders ) ) {
						return 0;
					}

					$ips = array_unique( array_filter( array_column( $orders, 'ip' ) ) );

					return count( $ips );
				},
				'required_args' => [],
			],
			'edd_customer_refund_rate'          => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'Percentage of orders that have been refunded.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return 0;
					}

					$total_orders = edd_count_orders( [
						'customer_id' => $customer_id,
						'status__in'  => edd_get_complete_order_statuses(),
					] );

					if ( ! $total_orders ) {
						return 0;
					}

					$refunded_orders = edd_count_orders( [
						'customer_id' => $customer_id,
						'status'      => 'refunded',
					] );

					return ( $refunded_orders / $total_orders ) * 100;
				},
				'required_args' => [],
			],
		];
	}

}