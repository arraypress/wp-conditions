<?php
/**
 * EDD Cart Helper
 *
 * Provides cart-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

/**
 * Class Cart
 *
 * Cart utilities for EDD conditions.
 */
class Cart {

	/** -------------------------------------------------------------------------
	 * Amount Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the cart total including tax and fees.
	 *
	 * @return float
	 */
	public static function get_total(): float {
		if ( ! function_exists( 'edd_get_cart_total' ) ) {
			return 0.0;
		}

		return (float) edd_get_cart_total();
	}

	/**
	 * Get the cart subtotal before tax and fees.
	 *
	 * @return float
	 */
	public static function get_subtotal(): float {
		if ( ! function_exists( 'edd_get_cart_subtotal' ) ) {
			return 0.0;
		}

		return (float) edd_get_cart_subtotal();
	}

	/**
	 * Get the total tax amount in the cart.
	 *
	 * @return float
	 */
	public static function get_tax(): float {
		if ( ! function_exists( 'edd_get_cart_tax' ) ) {
			return 0.0;
		}

		return (float) edd_get_cart_tax();
	}

	/**
	 * Get the total discount amount applied to the cart.
	 *
	 * @return float
	 */
	public static function get_discount_amount(): float {
		if ( ! function_exists( 'edd_get_cart_discounted_amount' ) ) {
			return 0.0;
		}

		return (float) edd_get_cart_discounted_amount();
	}

	/**
	 * Get the total fees amount in the cart.
	 *
	 * @return float
	 */
	public static function get_fee_total(): float {
		if ( ! function_exists( 'edd_get_cart_fee_total' ) ) {
			return 0.0;
		}

		return (float) edd_get_cart_fee_total();
	}

	/**
	 * Get the total quantity of items in the cart.
	 *
	 * @return int
	 */
	public static function get_quantity(): int {
		if ( ! function_exists( 'edd_get_cart_quantity' ) ) {
			return 0;
		}

		return (int) edd_get_cart_quantity();
	}

	/**
	 * Check if the cart has any discount applied.
	 *
	 * @return bool
	 */
	public static function has_discounts(): bool {
		if ( ! function_exists( 'edd_cart_has_discounts' ) ) {
			return false;
		}

		return edd_cart_has_discounts();
	}

	/** -------------------------------------------------------------------------
	 * Content Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get term IDs from cart contents.
	 *
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array<int>
	 */
	public static function get_term_ids( string $taxonomy ): array {
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
	 * Get product IDs from cart contents.
	 *
	 * @return array<int>
	 */
	public static function get_product_ids(): array {
		if ( ! function_exists( 'edd_get_cart_contents' ) ) {
			return [];
		}

		$contents = edd_get_cart_contents();

		if ( empty( $contents ) ) {
			return [];
		}

		return array_unique( array_column( $contents, 'id' ) );
	}

	/** -------------------------------------------------------------------------
	 * Count Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Count cart items by product type.
	 *
	 * @param string $type The product type.
	 *
	 * @return int
	 */
	public static function count_by_type( string $type ): int {
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
	 * Count subscription items in cart.
	 *
	 * @return int
	 */
	public static function count_subscriptions(): int {
		if ( ! function_exists( 'EDD_Recurring' ) || ! function_exists( 'edd_get_cart_contents' ) ) {
			return 0;
		}

		$contents = edd_get_cart_contents();

		if ( empty( $contents ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $contents as $item ) {
			if ( EDD_Recurring()->is_recurring( $item['id'] ) ) {
				$count += $item['quantity'] ?? 1;
			}
		}

		return $count;
	}

	/**
	 * Count licensed products in cart.
	 *
	 * @return int
	 */
	public static function count_licensed(): int {
		if ( ! class_exists( 'EDD_SL_Download' ) || ! function_exists( 'edd_get_cart_contents' ) ) {
			return 0;
		}

		$contents = edd_get_cart_contents();

		if ( empty( $contents ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $contents as $item ) {
			$download = new \EDD_SL_Download( $item['id'] );

			if ( $download->licensing_enabled() ) {
				$count += $item['quantity'] ?? 1;
			}
		}

		return $count;
	}

	/**
	 * Count license renewals in cart.
	 *
	 * @return int
	 */
	public static function count_renewals(): int {
		if ( ! class_exists( 'EDD_SL_Download' ) || ! function_exists( 'edd_get_cart_contents' ) ) {
			return 0;
		}

		$contents = edd_get_cart_contents();

		if ( empty( $contents ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $contents as $item ) {
			$options = $item['options'] ?? [];

			if ( ! empty( $options['is_renewal'] ) && isset( $options['license_id'] ) ) {
				$count += $item['quantity'] ?? 1;
			}
		}

		return $count;
	}

	/**
	 * Count free items in cart.
	 *
	 * @return int
	 */
	public static function count_free(): int {
		if ( ! function_exists( 'edd_get_cart_content_details' ) ) {
			return 0;
		}

		$details = edd_get_cart_content_details();

		if ( empty( $details ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $details as $item ) {
			if ( (float) $item['price'] === 0.0 ) {
				$count += $item['quantity'] ?? 1;
			}
		}

		return $count;
	}

	/** -------------------------------------------------------------------------
	 * Discount Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get applied discount IDs from cart.
	 *
	 * @return array<int>
	 */
	public static function get_discount_ids(): array {
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
	}

	/**
	 * Get unique product count (ignoring quantities).
	 *
	 * @return int
	 */
	public static function get_unique_product_count(): int {
		$product_ids = self::get_product_ids();
		return count( array_unique( $product_ids ) );
	}

	/**
	 * Check if cart has any subscriptions.
	 *
	 * @return bool
	 */
	public static function has_subscriptions(): bool {
		return self::count_subscriptions() > 0;
	}

	/**
	 * Check if cart has any renewals.
	 *
	 * @return bool
	 */
	public static function has_renewals(): bool {
		return self::count_renewals() > 0;
	}

}