<?php
/**
 * EDD Product Conditions
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
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Stats;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Helpers\Periods;

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
				'options'       => fn() => Formatting::format_options( get_post_statuses() ),
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
				'units'         => Periods::get_units(),
				'description'   => __( 'Product earnings within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_product_earnings( $args['product_id'], null, $unit, $number );
				},
				'required_args' => [ 'product_id' ],
			],
			'edd_product_sales_period'    => [
				'label'         => __( 'Sales in Period', 'arraypress' ),
				'group'         => __( 'EDD Product', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Product sales within a time period.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( ! isset( $args['product_id'] ) ) {
						return 0;
					}

					$unit   = $args['_unit'] ?? 'day';
					$number = (int) ( $args['_number'] ?? 1 );

					return Stats::get_product_sales( $args['product_id'], null, $unit, $number );
				},
				'required_args' => [ 'product_id' ],
			],
		];
	}

}