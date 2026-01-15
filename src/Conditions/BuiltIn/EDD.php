<?php
/**
 * EDD Built-in Conditions Aggregator
 *
 * Aggregates all Easy Digital Downloads conditions from separate classes.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Cart;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Checkout;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Commission;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Customer;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Order;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Product;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Recipient;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Store;

/**
 * Class EDD
 *
 * Provides all Easy Digital Downloads conditions.
 */
class EDD {

	/**
	 * Get all EDD conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			Cart::get_all(),
			Checkout::get_all(),
			Customer::get_all(),
			Order::get_all(),
			Product::get_all(),
			Store::get_all(),
			Commission::get_all(),
			Recipient::get_all()
		);
	}

	/**
	 * Get conditions by category.
	 *
	 * @param string $category The category name.
	 *
	 * @return array<string, array>
	 */
	public static function get_by_category( string $category ): array {
		return match ( strtolower( $category ) ) {
			'cart'       => Cart::get_all(),
			'checkout'   => Checkout::get_all(),
			'customer'   => Customer::get_all(),
			'order'      => Order::get_all(),
			'product'    => Product::get_all(),
			'store'      => Store::get_all(),
			'commission' => Commission::get_all(),
			'recipient'  => Recipient::get_all(),
			default      => [],
		};
	}

	/**
	 * Get available categories.
	 *
	 * @return array<string, string>
	 */
	public static function get_categories(): array {
		return [
			'cart'       => __( 'Cart', 'arraypress' ),
			'checkout'   => __( 'Checkout', 'arraypress' ),
			'customer'   => __( 'Customer', 'arraypress' ),
			'order'      => __( 'Order', 'arraypress' ),
			'product'    => __( 'Product', 'arraypress' ),
			'store'      => __( 'Store', 'arraypress' ),
			'commission' => __( 'Commission', 'arraypress' ),
			'recipient'  => __( 'Recipient', 'arraypress' ),
		];
	}

	/**
	 * Check if EDD Commissions add-on is active.
	 *
	 * @return bool
	 */
	public static function has_commissions(): bool {
		return function_exists( 'eddc_get_commission' );
	}

	/**
	 * Get all conditions, excluding add-on specific ones if not active.
	 *
	 * @return array<string, array>
	 */
	public static function get_available(): array {
		$conditions = array_merge(
			Cart::get_all(),
			Checkout::get_all(),
			Customer::get_all(),
			Order::get_all(),
			Product::get_all(),
			Store::get_all()
		);

		// Only include commission/recipient conditions if add-on is active
		if ( self::has_commissions() ) {
			$conditions = array_merge(
				$conditions,
				Commission::get_all(),
				Recipient::get_all()
			);
		}

		return $conditions;
	}

}