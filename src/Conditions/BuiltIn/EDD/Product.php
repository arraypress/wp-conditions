<?php
/**
 * EDD Product Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Helpers\Formatting;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Stats;
use ArrayPress\Conditions\Helpers\Periods;
use ArrayPress\Conditions\Operators;
use EDD_Download;

/**
 * Class Product
 *
 * Provides EDD product-related conditions.
 */
class Product {

	/**
	 * Get all product conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$conditions = array_merge(
			self::get_detail_conditions(),
			self::get_taxonomy_conditions(),
			self::get_pricing_conditions(),
			self::get_stats_conditions()
		);

		// Licensing conditions (requires EDD Software Licensing)
		if ( class_exists( 'EDD_SL_Download' ) ) {
			$conditions = array_merge( $conditions, self::get_licensing_conditions() );
		}

		// Recurring conditions (requires EDD Recurring)
		if ( function_exists( 'EDD_Recurring' ) ) {
			$conditions = array_merge( $conditions, self::get_recurring_conditions() );
		}

		return $conditions;
	}

	/**
	 * Get detail-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detail_conditions(): array {
		return [
			'edd_product_type'   => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Product: Details', 'arraypress' ),
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
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_get_download_type' ) ) {
						return '';
					}

					return edd_get_download_type( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_status' => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Product: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The post status of the product.', 'arraypress' ),
				'options'       => fn() => Formatting::format_options( get_post_statuses() ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => fn( $args ) => isset( $args['product_id'] ) ? get_post_status( $args['product_id'] ) : '',
				'required_args' => [ 'product_id' ],
			],
			'edd_product_author' => [
				'label'         => __( 'Author', 'arraypress' ),
				'group'         => __( 'Product: Details', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The author of the product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return 0;
					}

					$post = get_post( $product_id );

					return $post ? (int) $post->post_author : 0;
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_age'    => [
				'label'         => __( 'Product Age', 'arraypress' ),
				'group'         => __( 'Product: Details', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long ago the product was published.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return 0;
					}

					$post = get_post( $product_id );

					if ( ! $post || empty( $post->post_date ) ) {
						return 0;
					}

					return Periods::get_age( $post->post_date, $args['_unit'] ?? 'day' );
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/**
	 * Get taxonomy-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_taxonomy_conditions(): array {
		return [
			'edd_product_categories' => [
				'label'         => __( 'Categories', 'arraypress' ),
				'group'         => __( 'Product: Taxonomies', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories assigned to the product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return [];
					}

					$terms = wp_get_object_terms( $product_id, 'download_category', [ 'fields' => 'ids' ] );

					return is_array( $terms ) && ! is_wp_error( $terms ) ? $terms : [];
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_tags'       => [
				'label'         => __( 'Tags', 'arraypress' ),
				'group'         => __( 'Product: Taxonomies', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags assigned to the product.', 'arraypress' ),
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return [];
					}

					$terms = wp_get_object_terms( $product_id, 'download_tag', [ 'fields' => 'ids' ] );

					return is_array( $terms ) && ! is_wp_error( $terms ) ? $terms : [];
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/**
	 * Get pricing-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_pricing_conditions(): array {
		return [
			'edd_product_price'               => [
				'label'         => __( 'Price', 'arraypress' ),
				'group'         => __( 'Product: Pricing', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 29.99', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The product price (or lowest price for variable pricing).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_get_download_price' ) ) {
						return 0;
					}

					return (float) edd_get_download_price( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_variable_prices' => [
				'label'         => __( 'Has Variable Prices', 'arraypress' ),
				'group'         => __( 'Product: Pricing', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has variable pricing enabled.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_has_variable_prices' ) ) {
						return false;
					}

					return edd_has_variable_prices( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_is_free'             => [
				'label'         => __( 'Is Free', 'arraypress' ),
				'group'         => __( 'Product: Pricing', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product is free.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_is_free_download' ) ) {
						return false;
					}

					return edd_is_free_download( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/**
	 * Get stats-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_stats_conditions(): array {
		return [
			'edd_product_sales'              => [
				'label'         => __( 'Total Sales', 'arraypress' ),
				'group'         => __( 'Product: Stats', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of sales for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_get_download_sales_stats' ) ) {
						return 0;
					}

					return (int) edd_get_download_sales_stats( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings'           => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'Product: Stats', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total earnings for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'edd_get_download_earnings_stats' ) ) {
						return 0;
					}

					return (float) edd_get_download_earnings_stats( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_sales_in_period'    => [
				'label'         => __( 'Sales in Period', 'arraypress' ),
				'group'         => __( 'Product: Stats', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Product sales within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_product_sales( $product_id, null, $unit, $number );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings_in_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'Product: Stats', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => Periods::get_units(),
				'description'   => __( 'Product earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_product_earnings( $product_id, null, $unit, $number );
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/**
	 * Get licensing-related conditions.
	 *
	 * Requires EDD Software Licensing add-on.
	 *
	 * @return array<string, array>
	 */
	private static function get_licensing_conditions(): array {
		return [
			'edd_product_licensing_enabled' => [
				'label'         => __( 'Licensing Enabled', 'arraypress' ),
				'group'         => __( 'Product: Licensing', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if licensing is enabled for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
						return false;
					}

					$download = new \EDD_SL_Download( $product_id );

					return $download->licensing_enabled();
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_license_limit'     => [
				'label'         => __( 'License Activation Limit', 'arraypress' ),
				'group'         => __( 'Product: Licensing', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The license activation limit for this product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
						return 0;
					}

					$download = new \EDD_SL_Download( $product_id );

					return (int) $download->get_activation_limit();
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_license_limit' => [
				'label'         => __( 'Has License Limit', 'arraypress' ),
				'group'         => __( 'Product: Licensing', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a license activation limit (not unlimited).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
						return false;
					}

					$download = new \EDD_SL_Download( $product_id );
					$limit    = $download->get_activation_limit();

					return $limit > 0;
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

	/**
	 * Get recurring/subscription-related conditions.
	 *
	 * Requires EDD Recurring Payments add-on.
	 *
	 * @return array<string, array>
	 */
	private static function get_recurring_conditions(): array {
		return [
			'edd_product_is_recurring'     => [
				'label'         => __( 'Is Recurring', 'arraypress' ),
				'group'         => __( 'Product: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product is a recurring/subscription product.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
						return false;
					}

					return EDD_Recurring()->is_recurring( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_recurring_period' => [
				'label'         => __( 'Billing Period', 'arraypress' ),
				'group'         => __( 'Product: Subscriptions', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select period...', 'arraypress' ),
				'description'   => __( 'The billing period for recurring products.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'day', 'label' => __( 'Daily', 'arraypress' ) ],
					[ 'value' => 'week', 'label' => __( 'Weekly', 'arraypress' ) ],
					[ 'value' => 'month', 'label' => __( 'Monthly', 'arraypress' ) ],
					[ 'value' => 'quarter', 'label' => __( 'Quarterly', 'arraypress' ) ],
					[ 'value' => 'semi-year', 'label' => __( 'Semi-Yearly', 'arraypress' ) ],
					[ 'value' => 'year', 'label' => __( 'Yearly', 'arraypress' ) ],
				],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
						return '';
					}

					return EDD_Recurring()->get_period_single( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_free_trial'   => [
				'label'         => __( 'Has Free Trial', 'arraypress' ),
				'group'         => __( 'Product: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product offers a free trial.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
						return false;
					}

					return EDD_Recurring()->has_free_trial( $product_id );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_signup_fee'   => [
				'label'         => __( 'Has Signup Fee', 'arraypress' ),
				'group'         => __( 'Product: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a signup fee.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$product_id = $args['product_id'] ?? 0;

					if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
						return false;
					}

					$fee = EDD_Recurring()->get_signup_fee_single( $product_id );

					return $fee > 0;
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

}