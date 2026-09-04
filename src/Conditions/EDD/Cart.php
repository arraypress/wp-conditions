<?php
/**
 * EDD Cart Conditions
 *
 * Sub-grouped under "Cart: Money / Items / Subscriptions / Licensing"
 * so the rule editor stays scannable. Group strings live in locals so
 * future re-organisation is one edit per section, not 20+.
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
		$money         = __( 'Cart: Money', 'arraypress' );
		$items         = __( 'Cart: Items', 'arraypress' );
		$subscriptions = __( 'Cart: Subscriptions', 'arraypress' );
		$licensing     = __( 'Cart: Licensing', 'arraypress' );

		$conditions = [
			// Money — totals + discounts.
			'edd_cart_total'           => [
				'label'         => __( 'Total', 'arraypress' ),
				'group'         => $money,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Final cart total including tax, fees, and discounts. Most fraud-relevant cart signal — cart total is the typical AND-clause in compound rules ("VPN AND cart total ≥ 100"). Currency is whatever EDD has configured for the store.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_total(),
				'required_args' => [],
			],
			'edd_cart_subtotal'        => [
				'label'         => __( 'Subtotal', 'arraypress' ),
				'group'         => $money,
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
				'group'         => $money,
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
				'group'         => $money,
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
				'group'         => $money,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total fees amount in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_fee_total(),
				'required_args' => [],
			],
			'edd_cart_discounts'       => [
				'label'         => __( 'Discounts', 'arraypress' ),
				'group'         => $money,
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
				'label'         => __( 'Discount Applied', 'arraypress' ),
				'group'         => $money,
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart has any discount applied.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_discounts(),
				'required_args' => [],
			],

			// Items — counts + contents.
			'edd_cart_quantity'        => [
				'label'         => __( 'Total Items', 'arraypress' ),
				'group'         => $items,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of items in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_quantity(),
				'required_args' => [],
			],
			'edd_cart_unique_products' => [
				'label'         => __( 'Distinct Products', 'arraypress' ),
				'group'         => $items,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of distinct products in the cart, ignoring quantities. A cart with 5x of one product = 1 unique product. High counts (≥10) are unusual for legitimate buyers and can indicate scripted bulk-purchase abuse.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::get_unique_product_count(),
				'required_args' => [],
			],
			'edd_cart_bundle_count'    => [
				'label'         => __( 'Bundle Count', 'arraypress' ),
				'group'         => $items,
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
				'group'         => $items,
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
				'group'         => $items,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of free items in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_free(),
				'required_args' => [],
			],
			'edd_cart_products'        => [
				'label'         => __( 'Products', 'arraypress' ),
				'group'         => $items,
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
				'group'         => $items,
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
				'group'         => $items,
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the cart contains products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CartHelper::get_term_ids( 'download_tag' ),
				'required_args' => [],
			],
		];

		// Subscription conditions (requires EDD Recurring).
		if ( function_exists( 'edd_recurring' ) ) {
			$conditions['edd_cart_subscription_count'] = [
				'label'         => __( 'Subscription Count', 'arraypress' ),
				'group'         => $subscriptions,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of subscription products in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_subscriptions(),
				'required_args' => [],
			];
			$conditions['edd_cart_has_subscription']   = [
				'label'         => __( 'Subscription in Cart', 'arraypress' ),
				'group'         => $subscriptions,
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart contains any subscription products.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_subscriptions(),
				'required_args' => [],
			];
		}

		// Licensing conditions (requires EDD Software Licensing).
		if ( class_exists( 'EDD_SL_Download' ) ) {
			$conditions['edd_cart_license_count'] = [
				'label'         => __( 'Licensed Product Count', 'arraypress' ),
				'group'         => $licensing,
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
				'group'         => $licensing,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of license renewals in the cart.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::count_renewals(),
				'required_args' => [],
			];
			$conditions['edd_cart_has_renewal']   = [
				'label'         => __( 'Renewal in Cart', 'arraypress' ),
				'group'         => $licensing,
				'type'          => 'boolean',
				'description'   => __( 'Check if the cart contains any license renewals.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartHelper::has_renewals(),
				'required_args' => [],
			];
		}

		$conditions['edd_cart_is_empty'] = [
			'label'         => __( 'Empty', 'arraypress' ),
			'group'         => $items,
			'type'          => 'boolean',
			'description'   => __( 'Whether the cart has nothing in it.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::is_empty(),
			'required_args' => [],
		];

		$conditions['edd_cart_discount_percentage'] = [
			'label'         => __( 'Discount (%)', 'arraypress' ),
			'group'         => $money,
			'type'          => 'number',
			'placeholder'   => __( 'e.g. 50', 'arraypress' ),
			'min'           => 0,
			'max'           => 100,
			'step'          => 0.1,
			'description'   => __( 'Discount as a share of the subtotal. A percentage travels across price points where an amount does not — 90% off is the same signal on a small cart as on a large one.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::get_discount_percentage(),
			'required_args' => [],
		];

		$conditions['edd_cart_fee_count'] = [
			'label'         => __( 'Fee Count', 'arraypress' ),
			'group'         => $money,
			'type'          => 'number',
			'placeholder'   => __( 'e.g. 1', 'arraypress' ),
			'min'           => 0,
			'step'          => 1,
			'description'   => __( 'How many separate fees have been added to the cart.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::get_fee_count(),
			'required_args' => [],
		];

		$conditions['edd_cart_price_ids'] = [
			'label'         => __( 'Price Options', 'arraypress' ),
			'group'         => $items,
			'type'          => 'tags',
			'placeholder'   => __( 'e.g. 1', 'arraypress' ),
			'description'   => __( 'The price option IDs chosen across the cart. A variable-priced download sells at several tiers, and which tier was bought is often the thing a rule cares about.', 'arraypress' ),
			'operators'     => Operators::tags_exact(),
			'compare_value' => fn( $args ) => CartHelper::get_price_ids(),
			'required_args' => [],
		];

		$conditions['edd_cart_average_product_age'] = [
			'label'         => __( 'Average Product Age', 'arraypress' ),
			'group'         => $items,
			'type'          => 'number',
			'placeholder'   => __( 'e.g. 30', 'arraypress' ),
			'min'           => 0,
			'step'          => 1,
			'description'   => __( 'Mean age in days of the products in the cart. A basket of brand-new listings is a different thing from a basket of catalogue staples.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::get_average_product_age(),
			'required_args' => [],
		];

		$conditions['edd_cart_newest_product_age'] = [
			'label'         => __( 'Newest Product Age', 'arraypress' ),
			'group'         => $items,
			'type'          => 'number',
			'placeholder'   => __( 'e.g. 1', 'arraypress' ),
			'min'           => 0,
			'step'          => 1,
			'description'   => __( 'Age in days of the most recently published product in the cart.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::get_newest_product_age(),
			'required_args' => [],
		];

		$conditions['edd_cart_oldest_product_age'] = [
			'label'         => __( 'Oldest Product Age', 'arraypress' ),
			'group'         => $items,
			'type'          => 'number',
			'placeholder'   => __( 'e.g. 365', 'arraypress' ),
			'min'           => 0,
			'step'          => 1,
			'description'   => __( 'Age in days of the longest-published product in the cart.', 'arraypress' ),
			'compare_value' => fn( $args ) => CartHelper::get_oldest_product_age(),
			'required_args' => [],
		];

		return $conditions;
	}
}
