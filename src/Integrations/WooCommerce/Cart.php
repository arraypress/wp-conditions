<?php
/**
 * WooCommerce Cart Helper
 *
 * Reads the current cart. Every method answers with a usable zero rather than
 * throwing when the cart is not available: conditions are evaluated in contexts
 * WooCommerce has not always finished booting -- REST, cron, an admin screen --
 * and a rule asking about the cart there should simply not match.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use ArrayPress\Conditions\Helpers\Parse;
use WC_Cart;
use WC_Product;

/**
 * Class Cart
 *
 * Cart utilities for WooCommerce conditions.
 */
class Cart {

	/** -------------------------------------------------------------------------
	 * Availability
	 * ------------------------------------------------------------------------ */

	/**
	 * The cart, when there is one.
	 *
	 * WC()->cart is null until WooCommerce initialises it, and on a request
	 * that never will -- cron, most REST routes -- it stays null.
	 *
	 * @return WC_Cart|null
	 *
	 * @since 1.0.0
	 */
	public static function get(): ?WC_Cart {
		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		$wc = WC();

		return isset( $wc->cart ) && $wc->cart instanceof WC_Cart ? $wc->cart : null;
	}

	/** -------------------------------------------------------------------------
	 * Money
	 * ------------------------------------------------------------------------ */

	/**
	 * Cart total, including tax, shipping, fees and discounts.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_total( 'edit' ) : 0.0;
	}

	/**
	 * Cart subtotal, before discounts.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_subtotal(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_subtotal() : 0.0;
	}

	/**
	 * Total discount applied by coupons.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_discount_total() : 0.0;
	}

	/**
	 * Total tax on the cart.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_total_tax() : 0.0;
	}

	/**
	 * Shipping cost.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_shipping_total() : 0.0;
	}

	/**
	 * Total fees added to the cart.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_fee_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_fee_total() : 0.0;
	}

	/**
	 * Tax charged on shipping.
	 *
	 * Separate from the shipping cost itself: a rule about delivery charges
	 * usually means the net figure, and a rule about tax exposure means this.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_tax(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_shipping_tax() : 0.0;
	}

	/**
	 * Shipping cost including its tax.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_total_inc_tax(): float {
		return self::get_shipping_total() + self::get_shipping_tax();
	}

	/**
	 * Tax charged on fees.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_fee_tax(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_fee_tax() : 0.0;
	}

	/**
	 * The fees on the cart.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function get_fees(): array {
		$cart = self::get();

		return $cart ? (array) $cart->get_fees() : [];
	}

	/**
	 * How many separate fees are on the cart.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_fee_count(): int {
		return count( self::get_fees() );
	}

	/**
	 * Shipping as a share of the subtotal.
	 *
	 * Delivery costing more than the goods is worth a rule of its own, and the
	 * ratio says that in a way an amount cannot.
	 *
	 * @return float 0-100, uncapped above.
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_percentage(): float {
		$subtotal = self::get_subtotal();

		if ( $subtotal <= 0 ) {
			return 0.0;
		}

		return round( ( self::get_shipping_total() / $subtotal ) * 100, 2 );
	}

	/**
	 * Discount as a share of the subtotal.
	 *
	 * A percentage says more than an amount when rules span price points: 90%
	 * off is the same signal on a £10 order as on a £1,000 one.
	 *
	 * @return float 0-100.
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_percentage(): float {
		$subtotal = self::get_subtotal();

		if ( $subtotal <= 0 ) {
			return 0.0;
		}

		return round( ( self::get_discount_total() / $subtotal ) * 100, 2 );
	}

	/** -------------------------------------------------------------------------
	 * Items
	 * ------------------------------------------------------------------------ */

	/**
	 * Cart contents.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public static function get_items(): array {
		$cart = self::get();

		return $cart ? (array) $cart->get_cart() : [];
	}

	/**
	 * Number of distinct lines in the cart.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_item_count(): int {
		return count( self::get_items() );
	}

	/**
	 * Total quantity across every line.
	 *
	 * Distinct from the line count: one line of fifty is a different signal
	 * from fifty lines of one.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_quantity(): int {
		$cart = self::get();

		return $cart ? (int) $cart->get_cart_contents_count() : 0;
	}

	/**
	 * Largest quantity on any single line.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_max_line_quantity(): int {
		$max = 0;

		foreach ( self::get_items() as $item ) {
			$max = max( $max, (int) ( $item['quantity'] ?? 0 ) );
		}

		return $max;
	}

	/**
	 * Total weight of the cart, in the store's weight unit.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_weight(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_cart_contents_weight() : 0.0;
	}

	/**
	 * Product IDs in the cart.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_product_ids(): array {
		$ids = [];

		foreach ( self::get_items() as $item ) {
			if ( ! empty( $item['product_id'] ) ) {
				$ids[] = (int) $item['product_id'];
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Variation IDs in the cart.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_ids(): array {
		$ids = [];

		foreach ( self::get_items() as $item ) {
			if ( ! empty( $item['variation_id'] ) ) {
				$ids[] = (int) $item['variation_id'];
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Term IDs of a taxonomy across every product in the cart.
	 *
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_term_ids( string $taxonomy ): array {
		$terms = [];

		foreach ( self::get_product_ids() as $product_id ) {
			$found = get_the_terms( $product_id, $taxonomy );

			if ( ! is_array( $found ) ) {
				continue;
			}

			foreach ( $found as $term ) {
				$terms[] = (int) $term->term_id;
			}
		}

		return array_values( array_unique( $terms ) );
	}

	/**
	 * Product types present in the cart.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_product_types(): array {
		$types = [];

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product ) {
				$types[] = (string) $product->get_type();
			}
		}

		return array_values( array_unique( array_filter( $types ) ) );
	}

	/**
	 * Whether every line in the cart is virtual.
	 *
	 * An all-virtual cart has nothing to ship, which changes what an address
	 * mismatch means.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_virtual(): bool {
		$items = self::get_items();

		if ( empty( $items ) ) {
			return false;
		}

		foreach ( $items as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product || ! $product->is_virtual() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether every line in the cart is downloadable.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_downloadable(): bool {
		$items = self::get_items();

		if ( empty( $items ) ) {
			return false;
		}

		foreach ( $items as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product || ! $product->is_downloadable() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether the cart needs shipping.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function needs_shipping(): bool {
		$cart = self::get();

		return $cart ? (bool) $cart->needs_shipping() : false;
	}

	/**
	 * Whether the cart needs a shipping address collected.
	 *
	 * Narrower than needs_shipping(): a local-pickup-only cart still ships but
	 * never asks for an address, so an address rule must not fire on it.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function needs_shipping_address(): bool {
		$cart = self::get();

		return $cart ? (bool) $cart->needs_shipping_address() : false;
	}

	/**
	 * Whether the cart is empty.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_empty(): bool {
		return 0 === self::get_item_count();
	}

	/**
	 * Line total before discounts, tax and shipping.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_contents_total(): float {
		$cart = self::get();

		return $cart ? (float) $cart->get_cart_contents_total() : 0.0;
	}

	/** -------------------------------------------------------------------------
	 * Shipping and tax classes
	 * ------------------------------------------------------------------------ */

	/**
	 * Shipping class slugs across every line in the cart.
	 *
	 * Products with no shipping class contribute nothing rather than an empty
	 * string, so an "is none of" rule is not satisfied by their blank.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_classes(): array {
		$classes = [];

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product ) {
				$classes[] = (string) $product->get_shipping_class();
			}
		}

		return array_values( array_unique( array_filter( $classes ) ) );
	}

	/**
	 * Tax class slugs across every line in the cart.
	 *
	 * WooCommerce stores the standard class as an empty slug, so it is
	 * normalised to "standard" here -- an empty string in a rule reads as
	 * "unset", which is a different question.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_classes(): array {
		$classes = [];

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$class = (string) $product->get_tax_class();

			$classes[] = '' === $class ? 'standard' : $class;
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Whether anything in the cart is taxable.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_taxable_item(): bool {
		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product && 'taxable' === $product->get_tax_status() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether anything in the cart is on backorder.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_backordered_item(): bool {
		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product && 'onbackorder' === $product->get_stock_status() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether anything in the cart is on sale.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_on_sale_item(): bool {
		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product && $product->is_on_sale() ) {
				return true;
			}
		}

		return false;
	}

	/** -------------------------------------------------------------------------
	 * Size
	 * ------------------------------------------------------------------------ */

	/**
	 * Total volume of the cart, quantities included.
	 *
	 * Lines missing a dimension contribute nothing rather than collapsing the
	 * whole figure to zero -- one product with no height set should not make a
	 * full pallet read as empty.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_volume(): float {
		$volume = 0.0;

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$length = (float) $product->get_length();
			$width  = (float) $product->get_width();
			$height = (float) $product->get_height();

			if ( $length <= 0 || $width <= 0 || $height <= 0 ) {
				continue;
			}

			$volume += $length * $width * $height * max( 1, (int) ( $item['quantity'] ?? 1 ) );
		}

		return round( $volume, 4 );
	}

	/**
	 * The longest single dimension anywhere in the cart.
	 *
	 * Carriers price oversize on the longest side of any one item, not on the
	 * total, so this is the figure an oversize rule wants.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_max_dimension(): float {
		$max = 0.0;

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$max = max( $max, (float) $product->get_length(), (float) $product->get_width(), (float) $product->get_height() );
		}

		return $max;
	}

	/**
	 * The heaviest single line in the cart, per unit.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_max_weight(): float {
		$max = 0.0;

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( $product instanceof WC_Product ) {
				$max = max( $max, (float) $product->get_weight() );
			}
		}

		return $max;
	}

	/** -------------------------------------------------------------------------
	 * Age
	 * ------------------------------------------------------------------------ */

	/**
	 * Ages, in days, of every product in the cart.
	 *
	 * @return float[]
	 *
	 * @since 1.0.0
	 */
	private static function get_product_ages(): array {
		$ages = [];
		// time(), not current_time( 'timestamp' ): the product's date is a
		// real epoch, and the site's shifted wall clock is off by its offset.
		$now  = time();

		foreach ( self::get_items() as $item ) {
			$product = $item['data'] ?? null;

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$created = $product->get_date_created();

			if ( ! $created ) {
				continue;
			}

			$ages[] = max( 0, ( $now - $created->getTimestamp() ) / DAY_IN_SECONDS );
		}

		return $ages;
	}

	/**
	 * Mean age of the products in the cart, in days.
	 *
	 * A basket of brand-new listings is a different thing from a basket of
	 * catalogue staples, and the mean separates the two without needing a rule
	 * per product.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_average_product_age(): float {
		$ages = self::get_product_ages();

		if ( empty( $ages ) ) {
			return 0.0;
		}

		return round( array_sum( $ages ) / count( $ages ), 2 );
	}

	/**
	 * Age of the newest product in the cart, in days.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_newest_product_age(): float {
		$ages = self::get_product_ages();

		return empty( $ages ) ? 0.0 : round( min( $ages ), 2 );
	}

	/**
	 * Age of the oldest product in the cart, in days.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_oldest_product_age(): float {
		$ages = self::get_product_ages();

		return empty( $ages ) ? 0.0 : round( max( $ages ), 2 );
	}

	/** -------------------------------------------------------------------------
	 * Variations
	 * ------------------------------------------------------------------------ */

	/**
	 * How many lines in the cart are variations.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_count(): int {
		return count( self::get_variation_ids() );
	}

	/**
	 * Whether anything in the cart is a variation.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function has_variations(): bool {
		return ! empty( self::get_variation_ids() );
	}

	/**
	 * Variation attribute values, read from a rule written as "attribute:value".
	 *
	 * The rule carries both halves in one field. Only the name half selects
	 * which attribute to read; the comparator handles the value.
	 *
	 * @param mixed $value The rule value, as "pa_size:large".
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_attribute_from_rule( $value = null ): array {
		if ( ! is_string( $value ) || ! str_contains( $value, ':' ) ) {
			return [];
		}

		// Not strtok(): for a value of ":" it answers false, which trim()
		// refuses under strict types.
		return self::get_variation_attribute( Parse::meta( $value )['key'] );
	}

	/**
	 * Values of one variation attribute across the cart.
	 *
	 * WooCommerce keys a line's variation data as "attribute_pa_size", so the
	 * name is normalised to that shape before looking it up.
	 *
	 * @param string $name Attribute name, with or without the attribute_ prefix.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_attribute( string $name ): array {
		if ( '' === $name ) {
			return [];
		}

		$key    = str_starts_with( $name, 'attribute_' ) ? $name : 'attribute_' . $name;
		$values = [];

		foreach ( self::get_items() as $item ) {
			$variation = $item['variation'] ?? [];

			if ( is_array( $variation ) && ! empty( $variation[ $key ] ) ) {
				$values[] = (string) $variation[ $key ];
			}
		}

		return array_values( array_unique( $values ) );
	}

	/** -------------------------------------------------------------------------
	 * Coupons
	 * ------------------------------------------------------------------------ */

	/**
	 * Coupon codes applied to the cart.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_coupons(): array {
		$cart = self::get();

		return $cart ? array_map( 'strval', (array) $cart->get_applied_coupons() ) : [];
	}

	/**
	 * How many coupons are applied.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_coupon_count(): int {
		return count( self::get_coupons() );
	}
}
