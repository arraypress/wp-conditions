<?php
/**
 * EDD Cart Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Cart as CartHelper;
use ArrayPress\Conditions\Integrations\EDD\Options;
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
		$conditions = [
			// Amounts
			'edd_cart_total'           => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The cart total including tax and fees.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_total(),
				'required_args' => [],
			],
			'edd_cart_subtotal'        => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The cart subtotal before tax and fees.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_subtotal(),
				'required_args' => [],
			],
			'edd_cart_tax'             => [
				'label'         => __( 'Tax Amount', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total tax amount in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_tax(),
				'required_args' => [],
			],
			'edd_cart_discount_amount' => [
				'label'         => __( 'Discount Amount', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total discount amount applied to the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_discount_amount(),
				'required_args' => [],
			],
			'edd_cart_fee_total'       => [
				'label'         => __( 'Fee Total', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total fees amount in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_fee_total(),
				'required_args' => [],
			],

			// Item Counts
			'edd_cart_quantity'        => [
				'label'         => __( 'Item Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of items in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_quantity(),
				'required_args' => [],
			],
			'edd_cart_unique_products' => [
				'label'         => __( 'Unique Product Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of unique products in the cart (ignoring quantities).', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_unique_product_count(),
				'required_args' => [],
			],
			'edd_cart_bundle_count'    => [
				'label'         => __( 'Bundle Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of bundle products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_by_type( 'bundle' ),
				'required_args' => [],
			],
			'edd_cart_service_count'   => [
				'label'         => __( 'Service Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of service products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_by_type( 'service' ),
				'required_args' => [],
			],
			'edd_cart_free_count'      => [
				'label'         => __( 'Free Item Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of free items in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_free(),
				'required_args' => [],
			],

			// Contents
			'edd_cart_products'        => [
				'label'         => __( 'Products', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CartHelper::get_product_ids(),
				'required_args' => [],
			],
			'edd_cart_categories'      => [
				'label'         => __( 'Categories', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains products from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CartHelper::get_term_ids( 'download_category' ),
				'required_args' => [],
			],
			'edd_cart_tags'            => [
				'label'         => __( 'Tags', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CartHelper::get_term_ids( 'download_tag' ),
				'required_args' => [],
			],

			// Discounts
			'edd_cart_discounts'       => [
				'label'         => __( 'Discounts', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search discounts...', 'arraypress' ),
				'description'   => __( 'Check if specific discounts are applied to the cart.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => Options::get_discount_options( $search, $ids ),
				'compare_value' => fn( $args ) => CartHelper::get_discount_ids(),
				'required_args' => [],
			],
			'edd_cart_has_discount'    => [
				'label'         => __( 'Has Discount', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart has any discount applied.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_discounts(),
				'required_args' => [],
			],
		];

		// Subscription conditions (requires EDD Recurring)
		if ( function_exists( 'edd_recurring' ) ) {
			$conditions['edd_cart_subscription_count'] = [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of subscription products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_subscriptions(),
				'required_args' => [],
			];
			$conditions['edd_cart_has_subscription']   = [
				'label'         => __( 'Has Subscription', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart contains any subscription products.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_subscriptions(),
				'required_args' => [],
			];
		}

		// Licensing conditions (requires EDD Software Licensing)
		if ( class_exists( 'EDD_SL_Download' ) ) {
			$conditions['edd_cart_license_count'] = [
				'label'         => __( 'Licensed Product Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of licensed products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_licensed(),
				'required_args' => [],
			];
			$conditions['edd_cart_renewal_count'] = [
				'label'         => __( 'Renewal Count', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of license renewals in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_renewals(),
				'required_args' => [],
			];
			$conditions['edd_cart_has_renewal']   = [
				'label'         => __( 'Has Renewal', 'arraypress' ),
				'group'         => __( 'Cart', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart contains any license renewals.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_renewals(),
				'required_args' => [],
			];
		}

		return $conditions;
	}

}