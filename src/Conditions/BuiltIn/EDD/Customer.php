<?php
/**
 * EDD Customer Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
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
		return array_merge(
			self::get_profile_conditions(),
			self::get_history_conditions(),
			self::get_activity_conditions()
		);
	}

	/**
	 * Get profile-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_profile_conditions(): array {
		return [
			'edd_customer_type'         => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Customer: Profile', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'Whether the customer is new or returning.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'new', 'label' => __( 'New Customer', 'arraypress' ) ],
					[ 'value' => 'returning', 'label' => __( 'Returning Customer', 'arraypress' ) ],
				],
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					if ( ! $customer ) {
						return 'new';
					}

					return $customer->purchase_count > 0 ? 'returning' : 'new';
				},
				'required_args' => [],
			],
			'edd_customer_email'        => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Customer: Profile', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match customer email. Supports: full email, @domain.com, .edu, partial domain.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					return $customer ? $customer->email : '';
				},
				'required_args' => [],
			],
			'edd_customer_email_domain' => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'Customer: Profile', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain, press Enter...', 'arraypress' ),
				'description'   => __( 'Match if customer email ends with specified domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					return $customer ? $customer->email : '';
				},
				'required_args' => [],
			],
			'edd_customer_account_age'  => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'Customer: Profile', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the customer has been registered.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					if ( ! $customer || empty( $customer->date_created ) ) {
						return 0;
					}

					return Periods::get_age( $customer->date_created, $args['_unit'] ?? 'day' );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get purchase history conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_history_conditions(): array {
		return [
			'edd_customer_order_count'          => [
				'label'         => __( 'Total Orders', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of orders placed by the customer.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					return $customer ? (int) $customer->purchase_count : 0;
				},
				'required_args' => [],
			],
			'edd_customer_total_spent'          => [
				'label'         => __( 'Total Spent', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total amount spent by the customer.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					return $customer ? (float) $customer->purchase_value : 0;
				},
				'required_args' => [],
			],
			'edd_customer_avg_order_value'      => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The average value of customer orders.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer = self::get_customer( $args );

					if ( ! $customer || ! $customer->purchase_count ) {
						return 0;
					}

					return (float) $customer->purchase_value / (int) $customer->purchase_count;
				},
				'required_args' => [],
			],
			'edd_customer_purchased_products'   => [
				'label'         => __( 'Purchased Products', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					return $customer_id ? CustomerHelper::get_product_ids( $customer_id ) : [];
				},
				'required_args' => [],
			],
			'edd_customer_purchased_categories' => [
				'label'         => __( 'Purchased Categories', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					return $customer_id ? CustomerHelper::get_term_ids( $customer_id, 'download_category' ) : [];
				},
				'required_args' => [],
			],
			'edd_customer_purchased_tags'       => [
				'label'         => __( 'Purchased Tags', 'arraypress' ),
				'group'         => __( 'Customer: Purchase History', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					return $customer_id ? CustomerHelper::get_term_ids( $customer_id, 'download_tag' ) : [];
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get activity-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_activity_conditions(): array {
		return [
			'edd_customer_orders_in_period' => [
				'label'         => __( 'Orders in Period', 'arraypress' ),
				'group'         => __( 'Customer: Activity', 'arraypress' ),
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
			'edd_customer_spend_in_period'  => [
				'label'         => __( 'Spend in Period', 'arraypress' ),
				'group'         => __( 'Customer: Activity', 'arraypress' ),
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
			'edd_customer_ip_count'         => [
				'label'         => __( 'Unique IP Count', 'arraypress' ),
				'group'         => __( 'Customer: Activity', 'arraypress' ),
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
			'edd_customer_refund_count'     => [
				'label'         => __( 'Refund Count', 'arraypress' ),
				'group'         => __( 'Customer: Activity', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Total number of refunded orders.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

					if ( ! $customer_id ) {
						return 0;
					}

					return edd_count_orders( [
						'customer_id' => $customer_id,
						'status'      => 'refunded',
					] );
				},
				'required_args' => [],
			],
			'edd_customer_refund_rate'      => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => __( 'Customer: Activity', 'arraypress' ),
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

	/**
	 * Get customer object from args or current user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return \EDD_Customer|null
	 */
	private static function get_customer( array $args ): ?\EDD_Customer {
		if ( ! function_exists( 'edd_get_customer' ) ) {
			return null;
		}

		$customer_id = $args['customer_id'] ?? CustomerHelper::get_current_id();

		if ( ! $customer_id ) {
			return null;
		}

		return edd_get_customer( $customer_id );
	}

}