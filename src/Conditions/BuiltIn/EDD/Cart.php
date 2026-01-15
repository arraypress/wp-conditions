<?php
/**
 * EDD Cart Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Cart as CartHelper;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Formatting;
use ArrayPress\Conditions\Operators;

/**
 * Class Cart
 *
 * Provides EDD cart-related conditions.
 */
class Cart {

	/**
	 * Get all cart conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
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
				'compare_value' => fn( $args ) => CartHelper::get_product_ids(),
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
				'compare_value' => fn( $args ) => CartHelper::get_term_ids( 'download_category' ),
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
				'compare_value' => fn( $args ) => CartHelper::get_term_ids( 'download_tag' ),
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
				'ajax'          => fn( ?string $search, ?array $ids ): array => Formatting::get_discount_options( $search, $ids ),
				'compare_value' => fn( $args ) => CartHelper::get_discount_ids(),
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
				'compare_value' => fn( $args ) => CartHelper::count_by_type( 'bundle' ),
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
				'compare_value' => fn( $args ) => CartHelper::count_subscriptions(),
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
				'compare_value' => fn( $args ) => CartHelper::count_licensed(),
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
				'compare_value' => fn( $args ) => CartHelper::count_renewals(),
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
				'compare_value' => fn( $args ) => CartHelper::count_free(),
				'required_args' => [],
			],
		];
	}

}