<?php
/**
 * WooCommerce Built-in Conditions Aggregator
 *
 * Aggregates all WooCommerce conditions from separate classes.
 *
 * @package     ArrayPress\Conditions\Conditions\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\WooCommerce;

/**
 * Class Conditions
 *
 * Provides all WooCommerce conditions.
 */
class Conditions {

	/**
	 * Get all WooCommerce conditions.
	 *
	 * @return array<string, array>
	 *
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		return array_merge(
			Cart::get_all(),
			Checkout::get_all(),
			Customer::get_all(),
			Order::get_all(),
			Product::get_all(),
			Store::get_all()
		);
	}

	/**
	 * Get conditions by category.
	 *
	 * @param string $category The category name.
	 *
	 * @return array<string, array>
	 *
	 * @since 1.0.0
	 */
	public static function get_by_category( string $category ): array {
		return match ( strtolower( $category ) ) {
			'cart'     => Cart::get_all(),
			'checkout' => Checkout::get_all(),
			'customer' => Customer::get_all(),
			'order'    => Order::get_all(),
			'product'  => Product::get_all(),
			'store'    => Store::get_all(),
			default    => [],
		};
	}

	/**
	 * Get available categories.
	 *
	 * @return array<string, string>
	 *
	 * @since 1.0.0
	 */
	public static function get_categories(): array {
		return [
			'cart'     => __( 'Cart', 'arraypress' ),
			'checkout' => __( 'Checkout', 'arraypress' ),
			'customer' => __( 'Customer', 'arraypress' ),
			'order'    => __( 'Order', 'arraypress' ),
			'product'  => __( 'Product', 'arraypress' ),
			'store'    => __( 'Store', 'arraypress' ),
		];
	}
}
