<?php
/**
 * EDD Order Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Helpers\EDD\Options;
use ArrayPress\Conditions\Helpers\EDD\Order as OrderHelper;
use ArrayPress\Conditions\Helpers\DateTime as DateTimeHelper;
use ArrayPress\Conditions\Helpers\Periods;
use ArrayPress\Conditions\Helpers\Format;
use ArrayPress\Conditions\Operators;
use EDD\Orders\Order as EDD_Order;

/**
 * Class Order
 *
 * Provides EDD order-related conditions.
 */
class Order {

	/**
	 * Get all order conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$conditions = array_merge(
			self::get_amount_conditions(),
			self::get_detail_conditions(),
			self::get_content_conditions(),
			self::get_address_conditions(),
			self::get_customer_conditions(),
			self::get_date_conditions()
		);

		// Subscription conditions (requires EDD Recurring)
		if ( function_exists( 'EDD_Recurring' ) || class_exists( 'EDD_Subscriptions_DB' ) ) {
			$conditions = array_merge( $conditions, self::get_subscription_conditions() );
		}

		return $conditions;
	}

	/**
	 * Get amount-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_amount_conditions(): array {
		return [
			'edd_order_total'    => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => __( 'Order: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order total amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? (float) $order->total : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_subtotal' => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => __( 'Order: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order subtotal before tax.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? (float) $order->subtotal : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tax'      => [
				'label'         => __( 'Tax', 'arraypress' ),
				'group'         => __( 'Order: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order tax amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? (float) $order->tax : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_discount' => [
				'label'         => __( 'Discount Amount', 'arraypress' ),
				'group'         => __( 'Order: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order discount amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? (float) $order->discount : 0;
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get detail-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detail_conditions(): array {
		return [
			'edd_order_status'   => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Order: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The order status.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_statuses' ) ? Format::options( edd_get_payment_statuses() ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? $order->status : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_gateway'  => [
				'label'         => __( 'Payment Gateway', 'arraypress' ),
				'group'         => __( 'Order: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway used for the order.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? Format::options( edd_get_payment_gateways(), 'admin_label' ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? $order->gateway : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_currency' => [
				'label'         => __( 'Currency', 'arraypress' ),
				'group'         => __( 'Order: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select currency...', 'arraypress' ),
				'description'   => __( 'The order currency.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_currencies' ) ? Format::options( edd_get_currencies() ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? $order->currency : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_mode'     => [
				'label'         => __( 'Mode', 'arraypress' ),
				'group'         => __( 'Order: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select mode...', 'arraypress' ),
				'description'   => __( 'Whether the order was placed in live or test mode.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'live', 'label' => __( 'Live', 'arraypress' ) ],
					[ 'value' => 'test', 'label' => __( 'Test', 'arraypress' ) ],
				],
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? $order->mode : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_ip'       => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'Order: Details', 'arraypress' ),
				'type'          => 'ip',
				'placeholder'   => __( 'e.g. 192.168.1.1 or 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'The IP address used for the order. Supports exact match, CIDR, and wildcards.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? $order->ip : '';
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get content-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_content_conditions(): array {
		return [
			'edd_order_products'   => [
				'label'         => __( 'Contains Products', 'arraypress' ),
				'group'         => __( 'Order: Contents', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the order contains specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_product_ids( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_categories' => [
				'label'         => __( 'Contains Categories', 'arraypress' ),
				'group'         => __( 'Order: Contents', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_term_ids( $args['order_id'] ?? 0, 'download_category' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tags'       => [
				'label'         => __( 'Contains Tags', 'arraypress' ),
				'group'         => __( 'Order: Contents', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_term_ids( $args['order_id'] ?? 0, 'download_tag' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_item_count' => [
				'label'         => __( 'Item Count', 'arraypress' ),
				'group'         => __( 'Order: Contents', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of items in the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order_id = $args['order_id'] ?? 0;

					if ( ! $order_id || ! function_exists( 'edd_get_order_items' ) ) {
						return 0;
					}

					$items = edd_get_order_items( [
						'order_id' => $order_id,
						'number'   => 999,
					] );

					return count( $items );
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get address-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_address_conditions(): array {
		return [
			'edd_order_country'  => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Order: Address', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country for the order.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? Format::options( edd_get_country_list() ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					if ( ! $order ) {
						return '';
					}

					$address = $order->get_address();

					return $address ? $address->country : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_region'   => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'Order: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA, NY', 'arraypress' ),
				'description'   => __( 'The billing region/state for the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					if ( ! $order ) {
						return '';
					}

					$address = $order->get_address();

					return $address ? $address->region : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_city'     => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'Order: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Los Angeles', 'arraypress' ),
				'description'   => __( 'The billing city for the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					if ( ! $order ) {
						return '';
					}

					$address = $order->get_address();

					return $address ? $address->city : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_postcode' => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'Order: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 90210', 'arraypress' ),
				'description'   => __( 'The billing postal/zip code for the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					if ( ! $order ) {
						return '';
					}

					$address = $order->get_address();

					return $address ? $address->postal_code : '';
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get customer-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_customer_conditions(): array {
		return [
			'edd_order_email' => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Order: Customer', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match order email against patterns. Supports: full email, @domain, .tld, or domain.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );
					return $order ? $order->email : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_customer_id'  => [
				'label'         => __( 'Customer', 'arraypress' ),
				'group'         => __( 'Order: Customer', 'arraypress' ),
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search customers...', 'arraypress' ),
				'description'   => __( 'The customer who placed the order.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => Options::get_customer_options( $search, $ids ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return (int) $order?->customer_id;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_user_id'      => [
				'label'         => __( 'User', 'arraypress' ),
				'group'         => __( 'Order: Customer', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The WordPress user who placed the order.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return (int) $order?->user_id;
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get date-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_date_conditions(): array {
		return [
			'edd_order_date_created'   => [
				'label'         => __( 'Date Created', 'arraypress' ),
				'group'         => __( 'Order: Dates', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was created.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order ? wp_date( 'Y-m-d', strtotime( $order->date_created ) ) : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_date_completed' => [
				'label'         => __( 'Date Completed', 'arraypress' ),
				'group'         => __( 'Order: Dates', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was completed.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					return $order && $order->date_completed ? wp_date( 'Y-m-d', strtotime( $order->date_completed ) ) : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_age'            => [
				'label'         => __( 'Order Age', 'arraypress' ),
				'group'         => __( 'Order: Dates', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long ago the order was placed.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$order = self::get_order( $args );

					if ( ! $order || empty( $order->date_created ) ) {
						return 0;
					}

					return DateTimeHelper::get_age( $order->date_created, $args['_unit'] ?? 'day' );
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get subscription-related conditions.
	 *
	 * Requires EDD Recurring Payments add-on.
	 *
	 * @return array<string, array>
	 */
	private static function get_subscription_conditions(): array {
		return [
			'edd_order_is_renewal'         => [
				'label'         => __( 'Is Renewal', 'arraypress' ),
				'group'         => __( 'Order: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is a subscription renewal.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_renewal( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_is_subscription'    => [
				'label'         => __( 'Is Initial Subscription', 'arraypress' ),
				'group'         => __( 'Order: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is an initial subscription payment.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_subscription( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_subscription_count' => [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => __( 'Order: Subscriptions', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of subscriptions created from this order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_subscription_count( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/**
	 * Get order object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return EDD_Order|null
	 */
	private static function get_order( array $args ): ?EDD_Order {
		if ( ! function_exists( 'edd_get_order' ) ) {
			return null;
		}

		$order_id = $args['order_id'] ?? 0;

		if ( ! $order_id ) {
			return null;
		}

		return edd_get_order( $order_id );
	}

}