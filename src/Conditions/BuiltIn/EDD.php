<?php
/**
 * EDD Built-in Conditions
 *
 * Provides Easy Digital Downloads conditions for cart, customer, order, product, and commission contexts.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

use ArrayPress\Conditions\Operators;
use EDD\Stats;

/**
 * Class EDD
 *
 * Provides Easy Digital Downloads conditions.
 */
class EDD {

	/**
	 * Standard time period units for number_unit fields.
	 *
	 * @return array
	 */
	private static function get_period_units(): array {
		return [
			[ 'value' => 'hour', 'label' => __( 'Hour(s)', 'arraypress' ) ],
			[ 'value' => 'day', 'label' => __( 'Day(s)', 'arraypress' ) ],
			[ 'value' => 'week', 'label' => __( 'Week(s)', 'arraypress' ) ],
			[ 'value' => 'month', 'label' => __( 'Month(s)', 'arraypress' ) ],
			[ 'value' => 'year', 'label' => __( 'Year(s)', 'arraypress' ) ],
		];
	}

	/**
	 * Standard age units for number_unit fields.
	 *
	 * @return array
	 */
	private static function get_age_units(): array {
		return [
			[ 'value' => 'day', 'label' => __( 'Day(s)', 'arraypress' ) ],
			[ 'value' => 'week', 'label' => __( 'Week(s)', 'arraypress' ) ],
			[ 'value' => 'month', 'label' => __( 'Month(s)', 'arraypress' ) ],
			[ 'value' => 'year', 'label' => __( 'Year(s)', 'arraypress' ) ],
		];
	}

	/**
	 * Convert period unit to seconds.
	 *
	 * @param string $unit   The unit (hour, day, week, month, year).
	 * @param int    $amount The number of units.
	 *
	 * @return int Seconds.
	 */
	private static function period_to_seconds( string $unit, int $amount ): int {
		$multipliers = [
			'hour'  => HOUR_IN_SECONDS,
			'day'   => DAY_IN_SECONDS,
			'week'  => WEEK_IN_SECONDS,
			'month' => MONTH_IN_SECONDS,
			'year'  => YEAR_IN_SECONDS,
		];

		return $amount * ( $multipliers[ $unit ] ?? DAY_IN_SECONDS );
	}

	/**
	 * Get date range for Stats query based on period.
	 *
	 * @param string $unit   The unit.
	 * @param int    $amount The amount.
	 *
	 * @return array Array with start and end dates.
	 */
	private static function get_period_date_range( string $unit, int $amount ): array {
		$seconds = self::period_to_seconds( $unit, $amount );
		$end     = current_time( 'mysql' );
		$start   = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $seconds );

		return [
			'start' => $start,
			'end'   => $end,
		];
	}

	/** Cart Conditions ********************************************************/

	/**
	 * Get all cart conditions.
	 *
	 * @return array
	 */
	public static function cart(): array {
		return [
			'edd_cart_total'              => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The cart total including tax and fees.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_total' ) ? (float) edd_get_cart_total() : 0,
				'required_args' => [],
			],
			'edd_cart_subtotal'           => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The cart subtotal before tax and fees.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_subtotal' ) ? (float) edd_get_cart_subtotal() : 0,
				'required_args' => [],
			],
			'edd_cart_tax'                => [
				'label'         => __( 'Tax Amount', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total tax amount in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_tax' ) ? (float) edd_get_cart_tax() : 0,
				'required_args' => [],
			],
			'edd_cart_discount_amount'    => [
				'label'         => __( 'Discount Amount', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total discount amount applied to the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_discounted_amount' ) ? (float) edd_get_cart_discounted_amount() : 0,
				'required_args' => [],
			],
			'edd_cart_fee_total'          => [
				'label'         => __( 'Fee Total', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total fees amount in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_fee_total' ) ? (float) edd_get_cart_fee_total() : 0,
				'required_args' => [],
			],
			'edd_cart_quantity'           => [
				'label'         => __( 'Item Quantity', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of items in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_get_cart_quantity' ) ? (int) edd_get_cart_quantity() : 0,
				'required_args' => [],
			],
			'edd_cart_products'           => [
				'label'         => __( 'Contains Products', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains specific products.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! function_exists( 'edd_get_cart_contents' ) ) {
						return [];
					}
					$contents = edd_get_cart_contents();

					return $contents ? array_unique( array_column( $contents, 'id' ) ) : [];
				},
				'required_args' => [],
			],
			'edd_cart_categories'         => [
				'label'         => __( 'Contains Categories', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains products from specific categories.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => self::get_cart_term_ids( 'download_category' ),
				'required_args' => [],
			],
			'edd_cart_tags'               => [
				'label'         => __( 'Contains Tags', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => self::get_cart_term_ids( 'download_tag' ),
				'required_args' => [],
			],
			'edd_cart_has_discount'       => [
				'label'         => __( 'Has Discount Applied', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart has any discount applied.', 'arraypress' ),
				'compare_value' => fn( $args ) => function_exists( 'edd_cart_has_discounts' ) && edd_cart_has_discounts(),
				'required_args' => [],
			],
			'edd_cart_discounts'          => [
				'label'         => __( 'Discounts Applied', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search discounts...', 'arraypress' ),
				'description'   => __( 'Check if specific discounts are applied to the cart.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => self::get_discount_options( $search, $ids ),
				'compare_value' => function ( $args ) {
					if ( ! function_exists( 'edd_get_cart_discounts' ) ) {
						return [];
					}
					$codes = edd_get_cart_discounts();
					if ( empty( $codes ) ) {
						return [];
					}
					$discount_ids = [];
					foreach ( $codes as $code ) {
						$discount = edd_get_discount_by_code( $code );
						if ( $discount ) {
							$discount_ids[] = $discount->id;
						}
					}

					return $discount_ids;
				},
				'required_args' => [],
			],
			'edd_cart_bundle_count'       => [
				'label'         => __( 'Bundle Count', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of bundle products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => self::count_cart_by_type( 'bundle' ),
				'required_args' => [],
			],
			'edd_cart_subscription_count' => [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of subscription products in the cart.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! function_exists( 'EDD_Recurring' ) ) {
						return 0;
					}
					$count    = 0;
					$contents = edd_get_cart_contents();
					if ( empty( $contents ) ) {
						return 0;
					}
					foreach ( $contents as $item ) {
						if ( EDD_Recurring()->is_recurring( $item['id'] ) ) {
							$count += $item['quantity'] ?? 1;
						}
					}

					return $count;
				},
				'required_args' => [],
			],
			'edd_cart_license_count'      => [
				'label'         => __( 'License Product Count', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of licensed products in the cart.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! class_exists( 'EDD_SL_Download' ) ) {
						return 0;
					}
					$count    = 0;
					$contents = edd_get_cart_contents();
					if ( empty( $contents ) ) {
						return 0;
					}
					foreach ( $contents as $item ) {
						$download = new \EDD_SL_Download( $item['id'] );
						if ( $download->licensing_enabled() ) {
							$count += $item['quantity'] ?? 1;
						}
					}

					return $count;
				},
				'required_args' => [],
			],
			'edd_cart_renewal_count'      => [
				'label'         => __( 'License Renewal Count', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of license renewals in the cart.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! class_exists( 'EDD_SL_Download' ) ) {
						return 0;
					}
					$count    = 0;
					$contents = edd_get_cart_contents();
					if ( empty( $contents ) ) {
						return 0;
					}
					foreach ( $contents as $item ) {
						$options = $item['options'] ?? [];
						if ( ! empty( $options['is_renewal'] ) && isset( $options['license_id'] ) ) {
							$count += $item['quantity'] ?? 1;
						}
					}

					return $count;
				},
				'required_args' => [],
			],
			'edd_cart_free_count'         => [
				'label'         => __( 'Free Item Count', 'arraypress' ),
				'group'         => __( 'EDD Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of free items in the cart.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! function_exists( 'edd_get_cart_content_details' ) ) {
						return 0;
					}
					$count   = 0;
					$details = edd_get_cart_content_details();
					if ( empty( $details ) ) {
						return 0;
					}
					foreach ( $details as $item ) {
						if ( (float) $item['price'] === 0.0 ) {
							$count += $item['quantity'] ?? 1;
						}
					}

					return $count;
				},
				'required_args' => [],
			],
		];
	}

	/** Customer Conditions ****************************************************/

	/**
	 * Get all customer conditions.
	 *
	 * @return array
	 */
	public static function customer(): array {
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
				'units'         => self::get_period_units(),
				'description'   => __( 'Number of orders placed within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id ) {
						return 0;
					}
					$range  = self::get_period_date_range( $args['_unit'] ?? 'day', 1 );
					$orders = edd_get_orders( [
						'customer_id'  => $customer_id,
						'status__in'   => edd_get_complete_order_statuses(),
						'date_created' => [
							'after'  => $range['start'],
							'before' => $range['end'],
						],
						'number'       => 999999,
					] );

					return count( $orders );
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
				'units'         => self::get_period_units(),
				'description'   => __( 'Amount spent within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id ) {
						return 0;
					}
					$range  = self::get_period_date_range( $args['_unit'] ?? 'day', 1 );
					$orders = edd_get_orders( [
						'customer_id'  => $customer_id,
						'status__in'   => edd_get_complete_order_statuses(),
						'date_created' => [
							'after'  => $range['start'],
							'before' => $range['end'],
						],
						'number'       => 999999,
					] );
					$total  = 0;
					foreach ( $orders as $order ) {
						$total += (float) $order->total;
					}

					return $total;
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id ) {
						return [];
					}

					return self::get_customer_product_ids( $customer_id );
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id ) {
						return [];
					}

					return self::get_customer_term_ids( $customer_id, 'download_category' );
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id ) {
						return [];
					}

					return self::get_customer_term_ids( $customer_id, 'download_tag' );
				},
				'required_args' => [],
			],
			'edd_customer_age'                  => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'EDD Customer', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => self::get_age_units(),
				'description'   => __( 'How long the customer has been registered.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
					if ( ! $customer_id || ! function_exists( 'edd_get_customer' ) ) {
						return 0;
					}
					$customer = edd_get_customer( $customer_id );
					if ( ! $customer || empty( $customer->date_created ) ) {
						return 0;
					}
					$created = strtotime( $customer->date_created );

					return $created ? floor( ( current_time( 'timestamp' ) - $created ) / DAY_IN_SECONDS ) : 0;
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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
					$customer_id = $args['customer_id'] ?? self::get_current_customer_id();
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

	/** Order Conditions *******************************************************/

	/**
	 * Get all order conditions.
	 *
	 * @return array
	 */
	public static function order(): array {
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
				'options'       => fn() => function_exists( 'edd_get_payment_statuses' ) ? self::format_options( edd_get_payment_statuses() ) : [],
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
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? self::format_options( edd_get_payment_gateways(), 'admin_label' ) : [],
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
				'options'       => fn() => function_exists( 'edd_get_currencies' ) ? self::format_options( edd_get_currencies() ) : [],
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
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! function_exists( 'edd_get_order_items' ) ) {
						return [];
					}
					$items = edd_get_order_items( [ 'order_id' => $args['order_id'], 'number' => 999 ] );

					return $items ? array_unique( array_column( $items, 'product_id' ) ) : [];
				},
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
				'compare_value' => fn( $args ) => self::get_order_term_ids( $args['order_id'] ?? 0, 'download_category' ),
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
				'compare_value' => fn( $args ) => self::get_order_term_ids( $args['order_id'] ?? 0, 'download_tag' ),
				'required_args' => [ 'order_id' ],
			],
			'edd_order_country'            => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country for the order.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? self::format_options( edd_get_country_list() ) : [],
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
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) ) {
						return false;
					}

					return ! empty( edd_get_order_meta( $args['order_id'], '_edd_sl_is_renewal', true ) ) ||
					       ! empty( edd_get_order_meta( $args['order_id'], 'subscription_id', true ) );
				},
				'required_args' => [ 'order_id' ],
			],
			'edd_order_is_subscription'    => [
				'label'         => __( 'Is Initial Subscription', 'arraypress' ),
				'group'         => __( 'EDD Order', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the order is an initial subscription payment.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) ) {
						return false;
					}

					return (bool) edd_get_order_meta( $args['order_id'], '_edd_subscription_payment', true );
				},
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
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['order_id'] ) || ! class_exists( 'EDD_Subscriptions_DB' ) ) {
						return 0;
					}
					$subs_db = new \EDD_Subscriptions_DB();
					$subs    = $subs_db->get_subscriptions( [
						'parent_payment_id' => $args['order_id'],
						'number'            => 999
					] );

					return count( $subs );
				},
				'required_args' => [ 'order_id' ],
			],
		];
	}

	/** Product Conditions *****************************************************/

	/**
	 * Get all product conditions.
	 *
	 * @return array
	 */
	public static function product(): array {
		return [
			'edd_product_type'            => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'The type of product.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'default', 'label' => __( 'Default', 'arraypress' ) ],
					[ 'value' => 'bundle', 'label' => __( 'Bundle', 'arraypress' ) ],
					[ 'value' => 'service', 'label' => __( 'Service', 'arraypress' ) ],
				],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) || ! function_exists( 'edd_get_download_type' ) ) {
						return '';
					}

					return edd_get_download_type( $args['product_id'] );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_status'          => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The post status of the product.', 'arraypress' ),
				'options'       => fn() => self::format_options( get_post_statuses() ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => isset( $args['product_id'] ) ? get_post_status( $args['product_id'] ) : '',
				'required_args' => [ 'product_id' ],
			],
			'edd_product_categories'      => [
				'label'         => __( 'Categories', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories assigned to the product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) ) {
						return [];
					}
					$terms = wp_get_object_terms( $args['product_id'], 'download_category', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_tags'            => [
				'label'         => __( 'Tags', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags assigned to the product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) ) {
						return [];
					}
					$terms = wp_get_object_terms( $args['product_id'], 'download_tag', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_sales'           => [
				'label'         => __( 'Total Sales', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of sales for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) || ! function_exists( 'edd_get_download_sales_stats' ) ) {
						return 0;
					}

					return (int) edd_get_download_sales_stats( $args['product_id'] );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings'        => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total earnings for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) || ! function_exists( 'edd_get_download_earnings_stats' ) ) {
						return 0;
					}

					return (float) edd_get_download_earnings_stats( $args['product_id'] );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => self::get_period_units(),
				'description'   => __( 'Product earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) || ! class_exists( 'EDD\Stats' ) ) {
						return 0;
					}
					$range = self::get_period_date_range( $args['_unit'] ?? 'day', 1 );
					$stats = new Stats( [
						'product_id' => $args['product_id'],
						'start'      => $range['start'],
						'end'        => $range['end'],
						'status'     => edd_get_complete_order_statuses(),
						'output'     => 'raw',
					] );

					return (float) $stats->get_order_item_earnings();
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/** Store Conditions *******************************************************/

	/**
	 * Get all store conditions.
	 *
	 * @return array
	 */
	public static function store(): array {
		return [
			'edd_store_earnings_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'EDD Store', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => self::get_period_units(),
				'description'   => __( 'Total store earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! class_exists( 'EDD\Stats' ) ) {
						return 0;
					}
					$range = self::get_period_date_range( $args['_unit'] ?? 'day', 1 );
					$stats = new Stats( [
						'start'  => $range['start'],
						'end'    => $range['end'],
						'status' => edd_get_complete_order_statuses(),
						'output' => 'raw',
					] );

					return (float) $stats->get_order_earnings();
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
				'units'         => self::get_period_units(),
				'description'   => __( 'Total store sales count within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! class_exists( 'EDD\Stats' ) ) {
						return 0;
					}
					$range = self::get_period_date_range( $args['_unit'] ?? 'day', 1 );
					$stats = new Stats( [
						'start'  => $range['start'],
						'end'    => $range['end'],
						'status' => edd_get_complete_order_statuses(),
						'output' => 'raw',
					] );

					return (int) $stats->get_order_count();
				},
				'required_args' => [],
			],
		];
	}

	/** Checkout Conditions ****************************************************/

	/**
	 * Get all checkout conditions.
	 *
	 * @return array
	 */
	public static function checkout(): array {
		return [
			'edd_checkout_gateway' => [
				'label'         => __( 'Selected Gateway', 'arraypress' ),
				'group'         => __( 'EDD Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway selected at checkout.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? self::format_options( edd_get_payment_gateways(), 'admin_label' ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( isset( $args['posted']['edd-gateway'] ) ) {
						return $args['posted']['edd-gateway'];
					}

					return function_exists( 'edd_get_chosen_gateway' ) ? edd_get_chosen_gateway() : '';
				},
				'required_args' => [],
			],
			'edd_checkout_country' => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => __( 'EDD Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country entered at checkout.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? self::format_options( edd_get_country_list() ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( isset( $args['posted']['billing_country'] ) ) {
						return $args['posted']['billing_country'];
					}
					if ( isset( $args['posted']['edd_address']['country'] ) ) {
						return $args['posted']['edd_address']['country'];
					}

					return '';
				},
				'required_args' => [],
			],
		];
	}

	/** Commission Conditions **************************************************/

	/**
	 * Get all commission conditions.
	 *
	 * @return array
	 */
	public static function commission(): array {
		return [
			'edd_commission_amount'     => [
				'label'         => __( 'Amount', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The commission amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return 0;
					}
					$commission = eddc_get_commission( $args['commission_id'] );

					return $commission ? (float) $commission->amount : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_rate'       => [
				'label'         => __( 'Rate (%)', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'The commission rate percentage.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return 0;
					}
					$commission = eddc_get_commission( $args['commission_id'] );

					return $commission ? (float) $commission->rate : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_status'     => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The commission status.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'unpaid', 'label' => __( 'Unpaid', 'arraypress' ) ],
					[ 'value' => 'paid', 'label' => __( 'Paid', 'arraypress' ) ],
					[ 'value' => 'revoked', 'label' => __( 'Revoked', 'arraypress' ) ],
				],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return '';
					}
					$commission = eddc_get_commission( $args['commission_id'] );

					return $commission ? $commission->status : '';
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_product'    => [
				'label'         => __( 'Product', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'The product the commission is for.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return 0;
					}
					$commission = eddc_get_commission( $args['commission_id'] );

					return $commission ? (int) $commission->download_id : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_categories' => [
				'label'         => __( 'Product Categories', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories of the commission product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return [];
					}
					$commission = eddc_get_commission( $args['commission_id'] );
					if ( ! $commission || ! $commission->download_id ) {
						return [];
					}
					$terms = wp_get_object_terms( $commission->download_id, 'download_category', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_tags'       => [
				'label'         => __( 'Product Tags', 'arraypress' ),
				'group'         => __( 'EDD Commission', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags of the commission product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
						return [];
					}
					$commission = eddc_get_commission( $args['commission_id'] );
					if ( ! $commission || ! $commission->download_id ) {
						return [];
					}
					$terms = wp_get_object_terms( $commission->download_id, 'download_tag', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'commission_id' ],
			],
		];
	}

	/** Recipient Conditions ***************************************************/

	/**
	 * Get all recipient conditions.
	 *
	 * @return array
	 */
	public static function recipient(): array {
		return [
			'edd_recipient_total_earnings'  => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'EDD Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total earnings for the recipient (all time).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = $args['user_id'] ?? get_current_user_id();
					if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
						return 0;
					}
					$paid   = (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
					$unpaid = (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );

					return $paid + $unpaid;
				},
				'required_args' => [],
			],
			'edd_recipient_paid_earnings'   => [
				'label'         => __( 'Paid Earnings', 'arraypress' ),
				'group'         => __( 'EDD Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total paid earnings for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = $args['user_id'] ?? get_current_user_id();
					if ( ! $user_id || ! function_exists( 'eddc_get_paid_totals' ) ) {
						return 0;
					}

					return (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
				},
				'required_args' => [],
			],
			'edd_recipient_unpaid_earnings' => [
				'label'         => __( 'Unpaid Earnings', 'arraypress' ),
				'group'         => __( 'EDD Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 250.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total unpaid earnings for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = $args['user_id'] ?? get_current_user_id();
					if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
						return 0;
					}

					return (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );
				},
				'required_args' => [],
			],
			'edd_recipient_total_sales'     => [
				'label'         => __( 'Total Sales Count', 'arraypress' ),
				'group'         => __( 'EDD Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Total number of sales for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = $args['user_id'] ?? get_current_user_id();
					if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
						return 0;
					}

					return (int) eddc_count_user_commissions( $user_id );
				},
				'required_args' => [],
			],
		];
	}

	/** Helper Methods *********************************************************/

	/**
	 * Get all EDD conditions.
	 *
	 * @return array
	 */
	public static function get_all(): array {
		return array_merge(
			self::cart(),
			self::customer(),
			self::order(),
			self::product(),
			self::store(),
			self::checkout(),
			self::commission(),
			self::recipient()
		);
	}

	/**
	 * Get the current customer ID.
	 *
	 * @return int
	 */
	private static function get_current_customer_id(): int {
		if ( ! function_exists( 'edd_get_customer_by' ) || ! is_user_logged_in() ) {
			return 0;
		}
		$customer = edd_get_customer_by( 'user_id', get_current_user_id() );

		return $customer ? (int) $customer->id : 0;
	}

	/**
	 * Get term IDs from cart contents.
	 *
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array
	 */
	private static function get_cart_term_ids( string $taxonomy ): array {
		if ( ! function_exists( 'edd_get_cart_contents' ) ) {
			return [];
		}
		$contents = edd_get_cart_contents();
		if ( empty( $contents ) ) {
			return [];
		}
		$term_ids = [];
		foreach ( $contents as $item ) {
			$terms    = wp_get_object_terms( $item['id'], $taxonomy, [ 'fields' => 'ids' ] );
			$term_ids = array_merge( $term_ids, is_array( $terms ) ? $terms : [] );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Get term IDs from order items.
	 *
	 * @param int    $order_id The order ID.
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array
	 */
	private static function get_order_term_ids( int $order_id, string $taxonomy ): array {
		if ( ! $order_id || ! function_exists( 'edd_get_order_items' ) ) {
			return [];
		}
		$items = edd_get_order_items( [ 'order_id' => $order_id, 'number' => 999 ] );
		if ( empty( $items ) ) {
			return [];
		}
		$term_ids = [];
		foreach ( $items as $item ) {
			$terms    = wp_get_object_terms( $item->product_id, $taxonomy, [ 'fields' => 'ids' ] );
			$term_ids = array_merge( $term_ids, is_array( $terms ) ? $terms : [] );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Get customer's purchased product IDs.
	 *
	 * @param int $customer_id The customer ID.
	 *
	 * @return array
	 */
	private static function get_customer_product_ids( int $customer_id ): array {
		if ( ! function_exists( 'edd_get_customer' ) ) {
			return [];
		}
		$customer = edd_get_customer( $customer_id );
		if ( ! $customer ) {
			return [];
		}
		$order_ids = $customer->get_order_ids( edd_get_complete_order_statuses() );
		if ( empty( $order_ids ) ) {
			return [];
		}
		$items = edd_get_order_items( [
			'order_id__in' => $order_ids,
			'status__in'   => edd_get_deliverable_order_item_statuses(),
			'number'       => 999999,
		] );
		if ( empty( $items ) ) {
			return [];
		}

		return array_unique( array_column( $items, 'product_id' ) );
	}

	/**
	 * Get customer's purchased term IDs.
	 *
	 * @param int    $customer_id The customer ID.
	 * @param string $taxonomy    The taxonomy.
	 *
	 * @return array
	 */
	private static function get_customer_term_ids( int $customer_id, string $taxonomy ): array {
		$product_ids = self::get_customer_product_ids( $customer_id );
		if ( empty( $product_ids ) ) {
			return [];
		}
		$term_ids = [];
		foreach ( $product_ids as $product_id ) {
			$terms    = wp_get_object_terms( $product_id, $taxonomy, [ 'fields' => 'ids' ] );
			$term_ids = array_merge( $term_ids, is_array( $terms ) ? $terms : [] );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Count cart items by product type.
	 *
	 * @param string $type The product type.
	 *
	 * @return int
	 */
	private static function count_cart_by_type( string $type ): int {
		if ( ! function_exists( 'edd_get_cart_contents' ) ) {
			return 0;
		}
		$contents = edd_get_cart_contents();
		if ( empty( $contents ) ) {
			return 0;
		}
		$count = 0;
		foreach ( $contents as $item ) {
			$download = edd_get_download( $item['id'] );
			if ( $download && strtolower( $download->get_type() ) === strtolower( $type ) ) {
				$count += $item['quantity'] ?? 1;
			}
		}

		return $count;
	}

	/**
	 * Get discount options for AJAX select.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $ids    Specific IDs to retrieve.
	 *
	 * @return array
	 */
	private static function get_discount_options( ?string $search, ?array $ids ): array {
		if ( ! function_exists( 'edd_get_discounts' ) ) {
			return [];
		}
		$args = [ 'number' => 20, 'status' => [ 'active', 'inactive' ] ];
		if ( $ids ) {
			$args['id__in'] = array_map( 'intval', $ids );
		} elseif ( $search ) {
			$args['search'] = $search;
		}
		$discounts = edd_get_discounts( $args );
		if ( empty( $discounts ) ) {
			return [];
		}

		return array_map( function ( $discount ) {
			return [
				'value' => (string) $discount->id,
				'label' => $discount->code . ' (' . $discount->name . ')',
			];
		}, $discounts );
	}

	/**
	 * Format options array for select fields.
	 *
	 * @param array  $options   The raw options array.
	 * @param string $label_key The key to use for the label (for nested arrays).
	 *
	 * @return array
	 */
	private static function format_options( array $options, string $label_key = '' ): array {
		$formatted = [];
		foreach ( $options as $value => $label ) {
			if ( is_array( $label ) && $label_key ) {
				$label = $label[ $label_key ] ?? $value;
			}
			$formatted[] = [ 'value' => $value, 'label' => $label ];
		}

		return $formatted;
	}

}