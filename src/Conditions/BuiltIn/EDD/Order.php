<?php
/**
 * EDD Order Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Formatting;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Order as OrderHelper;
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
		return [
			'edd_order_total'              => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order total amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return 0;
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? (float) $order->total : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_subtotal'           => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order subtotal before tax.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return 0;
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? (float) $order->subtotal : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tax'                => [
				'label'         => __( 'Tax', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order tax amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return 0;
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? (float) $order->tax : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_discount'           => [
				'label'         => __( 'Discount Amount', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The order discount amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return 0;
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? (float) $order->discount : 0;
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_status'             => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The order status.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_statuses' ) ? Formatting::format_options( edd_get_payment_statuses() ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->status : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_gateway'            => [
				'label'         => __( 'Payment Gateway', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway used for the order.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? Formatting::format_options( edd_get_payment_gateways(), 'admin_label' ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->gateway : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_currency'           => [
				'label'         => __( 'Currency', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select currency...', 'arraypress' ),
				'description'   => __( 'The order currency.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_currencies' ) ? Formatting::format_options( edd_get_currencies() ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->currency : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_products'           => [
				'label'         => __( 'Contains Products', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the order contains specific products.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => OrderHelper::get_product_ids( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_categories'         => [
				'label'         => __( 'Contains Categories', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products from specific categories.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => OrderHelper::get_term_ids( $args['order_id'] ?? 0, 'download_category' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_tags'               => [
				'label'         => __( 'Contains Tags', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the order contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => OrderHelper::get_term_ids( $args['order_id'] ?? 0, 'download_tag' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_country'            => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country for the order.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? Formatting::format_options( edd_get_country_list() ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order   = edd_get_order( $args['order_id'] );
					$address = $order ? $order->get_address() : null;

					return $address ? $address->country : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_region'             => [
				'label'         => __( 'Billing Region/State', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA, NY', 'arraypress' ),
				'description'   => __( 'The billing region/state for the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order   = edd_get_order( $args['order_id'] );
					$address = $order ? $order->get_address() : null;

					return $address ? $address->region : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_city'               => [
				'label'         => __( 'Billing City', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Los Angeles', 'arraypress' ),
				'description'   => __( 'The billing city for the order.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order   = edd_get_order( $args['order_id'] );
					$address = $order ? $order->get_address() : null;

					return $address ? $address->city : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_ip'                 => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'ip',
				'placeholder'   => __( 'e.g. 192.168.1.1 or 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'The IP address used for the order. Supports exact match, CIDR, and wildcards.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->ip : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_email_domain'       => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain, press Enter...', 'arraypress' ),
				'description'   => __( 'Match if order email ends with specified domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->email : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_email'              => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'Enter pattern, press Enter...', 'arraypress' ),
				'description'   => __( 'Match order email. Supports: full email, @domain.com, .edu, partial domain.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? $order->email : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_date_created'       => [
				'label'         => __( 'Date Created', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was created.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order ? wp_date( 'Y-m-d', strtotime( $order->date_created ) ) : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_date_completed'     => [
				'label'         => __( 'Date Completed', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the order was completed.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order' ) ) {
						return '';
					}

					$order = edd_get_order( $args['order_id'] );

					return $order && $order->date_completed ? wp_date( 'Y-m-d', strtotime( $order->date_completed ) ) : '';
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_is_renewal'         => [
				'label'         => __( 'Is Renewal', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is a subscription renewal.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_renewal( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_is_subscription'    => [
				'label'         => __( 'Is Initial Subscription', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is an initial subscription payment.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::is_subscription( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_subscription_count' => [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of subscriptions on the order.', 'arraypress' ),
				'compare_value' => fn( $args ) => OrderHelper::get_subscription_count( $args['order_id'] ?? 0 ),
				'required_args' => [ 'order_id' ],
			],
		];
	}

}