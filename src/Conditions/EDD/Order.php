<?php
/**
 * EDD Order Conditions
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
use ArrayPress\Conditions\Integrations\EDD\Order as OrderHelper;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Operators;

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
		$conditions = [
			// Amounts
			'edd_order_total'           => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order total amount.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_total( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_subtotal'        => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order subtotal before tax.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_subtotal( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tax'             => [
				'label'         => __( 'Tax', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order tax amount.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_tax( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_discount'        => [
				'label'         => __( 'Discount Amount', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order discount amount.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_discount( $args ),
				'required_args' => [ 'order_id' ],
			],

			// Details
			'edd_order_status'          => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The order status.', 'arraypress' ),
				'options'       => fn() => Options::get_order_statuses(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => OrderHelper::get_status( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_gateway'         => [
				'label'         => __( 'Gateway', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway used.', 'arraypress' ),
				'options'       => fn() => Options::get_gateways(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => OrderHelper::get_gateway( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_currency'        => [
				'label'         => __( 'Currency', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select currency...', 'arraypress' ),
				'description'   => __( 'The order currency.', 'arraypress' ),
				'options'       => fn() => Options::get_currencies(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => OrderHelper::get_currency( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_mode'            => [
				'label'         => __( 'Mode', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select mode...', 'arraypress' ),
				'description'   => __( 'Whether the order was placed in live or test mode.', 'arraypress' ),
				'options'       => OrderHelper::get_mode_options(),
				'compare_value' => fn( $args ) => OrderHelper::get_mode( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_ip'              => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'ip',
				'placeholder'   => __( 'e.g. 192.168.1.1 or 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'The IP address used for the order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_ip( $args ),
				'required_args' => [ 'order_id' ],
			],

			// Contents
			'edd_order_products'        => [
				'label'         => __( 'Products', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the order contains specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_product_ids( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_categories'      => [
				'label'         => __( 'Categories', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_category_ids( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tags'            => [
				'label'         => __( 'Tags', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => OrderHelper::get_tag_ids( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_item_count'      => [
				'label'         => __( 'Item Count', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of items in the order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_item_count( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_unique_products' => [
				'label'         => __( 'Unique Product Count', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of unique products (ignoring quantities).', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_unique_product_count( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_bundle_count'    => [
				'label'         => __( 'Bundle Count', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of bundle products in the order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::count_by_type( $args, 'bundle' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_service_count'   => [
				'label'         => __( 'Service Count', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of service products in the order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::count_by_type( $args, 'service' ),
				'required_args' => [ 'order_id' ],
			],

			// Discounts
			'edd_order_discounts'       => [
				'label'         => __( 'Discounts', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search discounts...', 'arraypress' ),
				'description'   => __( 'Check if specific discounts were applied to the order.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => Options::get_discount_options( $search, $ids ),
				'compare_value' => fn( $args ) => OrderHelper::get_discount_ids( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_has_discount'    => [
				'label'         => __( 'Has Discount', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order has any discount applied.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::has_discount( $args ),
				'required_args' => [ 'order_id' ],
			],

			// Address
			'edd_order_country'         => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country for the order.', 'arraypress' ),
				'options'       => fn() => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => OrderHelper::get_country( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_region'          => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. CA, NY, TX', 'arraypress' ),
				'description'   => __( 'The billing region/state for the order.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => OrderHelper::get_region( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_city'            => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. Los Angeles, New York', 'arraypress' ),
				'description'   => __( 'The billing city for the order.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => OrderHelper::get_city( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_postcode'        => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. 90210, SW1A, 902', 'arraypress' ),
				'description'   => __( 'The billing postal/zip code. Supports prefix matching.', 'arraypress' ),
				'operators'     => Operators::tags(),
				'compare_value' => fn( $args ) => OrderHelper::get_postcode( $args ),
				'required_args' => [ 'order_id' ],
			],

			// Customer
			'edd_order_email'           => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match order email against patterns.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_email( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_customer_id'     => [
				'label'         => __( 'Customer', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search customers...', 'arraypress' ),
				'description'   => __( 'The customer who placed the order.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => Options::get_customer_options( $search, $ids ),
				'compare_value' => fn( $args ) => OrderHelper::get_customer_id( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_user_id'         => [
				'label'         => __( 'User', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The WordPress user who placed the order.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => OrderHelper::get_user_id( $args ),
				'required_args' => [ 'order_id' ],
			],

			// Dates
			'edd_order_date_created'    => [
				'label'         => __( 'Date Created', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was created.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_date_created( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_date_completed'  => [
				'label'         => __( 'Date Completed', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was completed.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_date_completed( $args ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_age'             => [
				'label'         => __( 'Age', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long ago the order was placed.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_age( $args ),
				'required_args' => [ 'order_id' ],
			],
		];

		// Subscription conditions (requires EDD Recurring)
		if ( function_exists( 'edd_recurring' ) || class_exists( 'EDD_Subscriptions_DB' ) ) {
			$conditions['edd_order_is_renewal']         = [
				'label'         => __( 'Is Renewal', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is a subscription renewal.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_renewal( $args ),
				'required_args' => [ 'order_id' ],
			];
			$conditions['edd_order_is_subscription']    = [
				'label'         => __( 'Is Initial Subscription', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is an initial subscription payment.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_subscription( $args ),
				'required_args' => [ 'order_id' ],
			];
			$conditions['edd_order_subscription_count'] = [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => __( 'Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of subscriptions created from this order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_subscription_count( $args ),
				'required_args' => [ 'order_id' ],
			];
		}

		return $conditions;
	}

}