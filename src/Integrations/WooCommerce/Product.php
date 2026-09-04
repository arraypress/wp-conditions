<?php
/**
 * WooCommerce Product Helper
 *
 * Reads a single product. Conditions pass a product_id in their args, so every
 * method here takes the same $args array the condition callback receives and
 * resolves the product itself -- a missing or deleted ID answers with a zero
 * value rather than raising.
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
use ArrayPress\Conditions\Helpers\Post;
use WC_Product;
use WC_Product_Variable;

/**
 * Class Product
 *
 * Product utilities for WooCommerce conditions.
 */
class Product {

	/** -------------------------------------------------------------------------
	 * Resolution
	 * ------------------------------------------------------------------------ */

	/**
	 * Product ID from the condition args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_id( array $args ): int {
		return (int) ( $args['product_id'] ?? $args['post_id'] ?? 0 );
	}

	/**
	 * The product object.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WC_Product|null
	 *
	 * @since 1.0.0
	 */
	public static function get( array $args ): ?WC_Product {
		$id = self::get_id( $args );

		if ( 0 === $id || ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$product = wc_get_product( $id );

		return $product instanceof WC_Product ? $product : null;
	}

	/**
	 * Normalize product_id into post_id for the shared post helpers.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	private static function normalize_args( array $args ): array {
		if ( isset( $args['product_id'] ) && ! isset( $args['post_id'] ) ) {
			$args['post_id'] = $args['product_id'];
		}

		return $args;
	}

	/** -------------------------------------------------------------------------
	 * Details
	 * ------------------------------------------------------------------------ */

	/**
	 * Product type -- simple, variable, grouped, external, or a custom type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_type( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_type() : '';
	}

	/**
	 * Post status.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_status( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_status() : '';
	}

	/**
	 * SKU.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_sku( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_sku() : '';
	}

	/**
	 * Author (post_author) of the product.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_author( array $args ): int {
		return Post::get_author( self::normalize_args( $args ) );
	}

	/**
	 * Publish date, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_date_created( array $args ): string {
		return Post::get_date_created( self::normalize_args( $args ) );
	}

	/**
	 * Last modified date, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_date_modified( array $args ): string {
		return Post::get_date_modified( self::normalize_args( $args ) );
	}

	/**
	 * Age of the product, in the unit carried by the rule.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_age( array $args ): int {
		return Post::get_age( self::normalize_args( $args ) );
	}

	/** -------------------------------------------------------------------------
	 * Pricing
	 * ------------------------------------------------------------------------ */

	/**
	 * Active price -- the sale price when one is running, otherwise the regular.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_price( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_price( 'edit' ) : 0.0;
	}

	/**
	 * Regular price, ignoring any sale.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_regular_price( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_regular_price( 'edit' ) : 0.0;
	}

	/**
	 * Sale price, or 0 when the product is not on sale.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_sale_price( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_sale_price( 'edit' ) : 0.0;
	}

	/**
	 * Discount as a share of the regular price.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float 0-100.
	 *
	 * @since 1.0.0
	 */
	public static function get_discount_percentage( array $args ): float {
		$regular = self::get_regular_price( $args );
		$price   = self::get_price( $args );

		if ( $regular <= 0 || $price >= $regular ) {
			return 0.0;
		}

		return round( ( ( $regular - $price ) / $regular ) * 100, 2 );
	}

	/**
	 * Whether the product is currently on sale.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_on_sale( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_on_sale() : false;
	}

	/**
	 * Whether the product is free.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_free( array $args ): bool {
		$product = self::get( $args );

		if ( ! $product ) {
			return false;
		}

		return 0.0 === (float) $product->get_price( 'edit' );
	}

	/**
	 * Tax class slug. An empty slug is WooCommerce's "standard" class.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_class( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_tax_class( 'edit' ) : '';
	}

	/**
	 * Tax status -- taxable, shipping, or none.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_status( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_tax_status( 'edit' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Stock
	 * ------------------------------------------------------------------------ */

	/**
	 * Stock quantity. Products that do not manage stock report 0.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_stock_quantity( array $args ): int {
		$product = self::get( $args );

		if ( ! $product ) {
			return 0;
		}

		return (int) $product->get_stock_quantity( 'edit' );
	}

	/**
	 * Stock status -- instock, outofstock, or onbackorder.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_stock_status( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_stock_status() : '';
	}

	/**
	 * Whether the product is in stock.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_in_stock( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_in_stock() : false;
	}

	/**
	 * Whether stock is managed at product level.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_managing_stock( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->managing_stock() : false;
	}

	/**
	 * Backorder policy -- no, notify, or yes.
	 *
	 * Distinct from the stock status: a product set to allow backorders is only
	 * "on backorder" once its stock has actually run out.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_backorders( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_backorders() : '';
	}

	/**
	 * Whether backorders are allowed at all.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_backorders_allowed( array $args ): bool {
		return in_array( self::get_backorders( $args ), [ 'notify', 'yes' ], true );
	}

	/**
	 * The low-stock threshold set on the product.
	 *
	 * Products with no threshold of their own report 0 and fall back to the
	 * store setting, which is not read here.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_low_stock_amount( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_low_stock_amount() : 0;
	}

	/**
	 * Whether the product is on backorder.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_on_backorder( array $args ): bool {
		$product = self::get( $args );

		return $product ? 'onbackorder' === $product->get_stock_status() : false;
	}

	/** -------------------------------------------------------------------------
	 * Shipping -- weight and dimensions
	 * ------------------------------------------------------------------------ */

	/**
	 * Weight, in the store's configured weight unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_weight( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_weight( 'edit' ) : 0.0;
	}

	/**
	 * Length, in the store's configured dimension unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_length( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_length( 'edit' ) : 0.0;
	}

	/**
	 * Width, in the store's configured dimension unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_width( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_width( 'edit' ) : 0.0;
	}

	/**
	 * Height, in the store's configured dimension unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_height( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_height( 'edit' ) : 0.0;
	}

	/**
	 * Longest single dimension.
	 *
	 * Oversize rules usually care about the longest side rather than volume --
	 * a carrier's limit is expressed the same way.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_longest_dimension( array $args ): float {
		return max(
			self::get_length( $args ),
			self::get_width( $args ),
			self::get_height( $args )
		);
	}

	/**
	 * Volume -- length x width x height.
	 *
	 * Zero when any dimension is unset, since a product missing one has no
	 * meaningful volume to compare.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_volume( array $args ): float {
		$length = self::get_length( $args );
		$width  = self::get_width( $args );
		$height = self::get_height( $args );

		if ( $length <= 0 || $width <= 0 || $height <= 0 ) {
			return 0.0;
		}

		return round( $length * $width * $height, 4 );
	}

	/**
	 * Shipping class term ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_class_id( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_shipping_class_id() : 0;
	}

	/**
	 * Shipping class slug.
	 *
	 * The slug is what a shipping zone's rates are keyed on, so it reads more
	 * clearly in a rule than the term ID does.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_class( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_shipping_class() : '';
	}

	/**
	 * Whether the product needs shipping.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function needs_shipping( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->needs_shipping() : false;
	}

	/** -------------------------------------------------------------------------
	 * Flags
	 * ------------------------------------------------------------------------ */

	/**
	 * Whether the product is virtual.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_virtual( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_virtual() : false;
	}

	/**
	 * Whether the product is downloadable.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_downloadable( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_downloadable() : false;
	}

	/**
	 * Whether the product is featured.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_featured( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_featured() : false;
	}

	/**
	 * Whether the product sells individually (one per order).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_sold_individually( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_sold_individually() : false;
	}

	/**
	 * Catalog visibility -- visible, catalog, search, or hidden.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_catalog_visibility( array $args ): string {
		$product = self::get( $args );

		return $product ? (string) $product->get_catalog_visibility() : '';
	}

	/**
	 * Whether the product can actually be bought.
	 *
	 * A product can be published, in stock, and still not purchasable -- a
	 * variable product with no price set is the usual case.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_purchasable( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->is_purchasable() : false;
	}

	/**
	 * Whether reviews are open on the product.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_reviews_allowed( array $args ): bool {
		$product = self::get( $args );

		return $product ? (bool) $product->get_reviews_allowed() : false;
	}

	/**
	 * Parent product ID. Non-zero only for a variation.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_parent_id( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_parent_id() : 0;
	}

	/**
	 * Whether the product is a variation of another.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function is_variation( array $args ): bool {
		return 'variation' === self::get_type( $args );
	}

	/**
	 * How many variations a variable product has.
	 *
	 * Anything that is not a variable product reports 0 rather than 1 -- a
	 * simple product has no variations, it is not its own single variation.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_count( array $args ): int {
		$product = self::get( $args );

		if ( ! $product || ! $product instanceof WC_Product_Variable ) {
			return 0;
		}

		return count( (array) $product->get_children() );
	}

	/**
	 * How many downloadable files the product carries.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_file_count( array $args ): int {
		$product = self::get( $args );

		if ( ! $product ) {
			return 0;
		}

		return count( (array) $product->get_downloads() );
	}

	/**
	 * Download limit. -1 means unlimited, which is WooCommerce's own default.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_download_limit( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_download_limit() : 0;
	}

	/** -------------------------------------------------------------------------
	 * Taxonomies
	 * ------------------------------------------------------------------------ */

	/**
	 * Category term IDs.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_categories( array $args ): array {
		$product = self::get_taxonomy_source( $args );

		return $product ? array_map( 'intval', (array) $product->get_category_ids() ) : [];
	}

	/**
	 * The product whose categories and tags count: a variation's parent.
	 *
	 * A variation carries no terms of its own -- WooCommerce never reads
	 * product_cat or product_tag for one -- so with a variation id, which is
	 * what an add-to-cart or an order line hands over, every category and
	 * tag rule silently failed.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WC_Product|null
	 */
	private static function get_taxonomy_source( array $args ): ?WC_Product {
		$product = self::get( $args );

		if ( ! $product ) {
			return null;
		}

		$parent_id = (int) $product->get_parent_id();

		if ( $parent_id > 0 && function_exists( 'wc_get_product' ) ) {
			$parent = wc_get_product( $parent_id );

			if ( $parent instanceof WC_Product ) {
				return $parent;
			}
		}

		return $product;
	}

	/**
	 * The product's id and, for a variation, its parent's.
	 *
	 * "Product is any of [X]" names the product the admin sees in the
	 * catalogue, which is the parent; the cart holds the variation.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 */
	public static function get_id_and_parent( array $args ): array {
		$product = self::get( $args );

		if ( ! $product ) {
			return [];
		}

		return array_values( array_filter( [ (int) $product->get_id(), (int) $product->get_parent_id() ] ) );
	}

	/**
	 * Tag term IDs.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 *
	 * @since 1.0.0
	 */
	public static function get_tags( array $args ): array {
		$product = self::get_taxonomy_source( $args );

		return $product ? array_map( 'intval', (array) $product->get_tag_ids() ) : [];
	}

	/**
	 * Attribute values for a given attribute name.
	 *
	 * @param array  $args The condition arguments.
	 * @param string $name Attribute name, with or without the pa_ prefix.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_attribute( array $args, string $name ): array {
		$product = self::get( $args );

		if ( ! $product || '' === $name ) {
			return [];
		}

		$value = $product->get_attribute( $name );

		if ( '' === $value ) {
			return [];
		}

		// Taxonomy attributes come back comma-separated; a custom attribute's
		// options are joined with " | ", so both are split.
		return array_values( array_filter( array_map( 'trim', preg_split( '/[,|]/', $value ) ?: [] ) ) );
	}

	/**
	 * Attribute values, read from a rule written as "attribute:value".
	 *
	 * The rule carries both halves in one field -- which attribute to look at
	 * and what to compare it against -- the same shape the post meta conditions
	 * use. Only the name half is used here; the comparator handles the rest.
	 *
	 * @param array $args  The condition arguments.
	 * @param mixed $value The rule value, as "pa_size:Large".
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_attribute_from_rule( array $args, $value = null ): array {
		$name = self::attribute_name( $value );

		return '' === $name ? [] : self::get_attribute( $args, $name );
	}

	/**
	 * The attribute name half of an "attribute:value" rule.
	 *
	 * @param mixed $value The rule value.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private static function attribute_name( $value ): string {
		if ( ! is_string( $value ) || ! str_contains( $value, ':' ) ) {
			return '';
		}

		// Not strtok(): for a value of ":" it answers false, which trim()
		// refuses under strict types -- on the checkout page.
		return Parse::meta( $value )['key'];
	}

	/**
	 * The attribute taxonomies the product uses.
	 *
	 * Names are returned as stored -- global attributes keep their pa_ prefix,
	 * custom ones do not.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string[]
	 *
	 * @since 1.0.0
	 */
	public static function get_attribute_names( array $args ): array {
		$product = self::get( $args );

		if ( ! $product ) {
			return [];
		}

		return array_values( array_map( 'strval', array_keys( (array) $product->get_attributes() ) ) );
	}

	/**
	 * How many attributes the product carries.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_attribute_count( array $args ): int {
		return count( self::get_attribute_names( $args ) );
	}

	/**
	 * The date a scheduled sale starts, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_sale_date_from( array $args ): string {
		$product = self::get( $args );
		$date    = $product?->get_date_on_sale_from();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * The date a scheduled sale ends, as Y-m-d.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function get_sale_date_to( array $args ): string {
		$product = self::get( $args );
		$date    = $product?->get_date_on_sale_to();

		return $date ? $date->date( 'Y-m-d' ) : '';
	}

	/**
	 * Days a download stays available. 0 means it never expires.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_download_expiry( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_download_expiry() : 0;
	}

	/** -------------------------------------------------------------------------
	 * Performance
	 * ------------------------------------------------------------------------ */

	/**
	 * Lifetime sales count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_total_sales( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_total_sales() : 0;
	}

	/**
	 * Average review rating, 0-5.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 *
	 * @since 1.0.0
	 */
	public static function get_average_rating( array $args ): float {
		$product = self::get( $args );

		return $product ? (float) $product->get_average_rating() : 0.0;
	}

	/**
	 * Number of reviews.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public static function get_review_count( array $args ): int {
		$product = self::get( $args );

		return $product ? (int) $product->get_review_count() : 0;
	}
	/** -------------------------------------------------------------------------
	 * Brands and cost
	 * ------------------------------------------------------------------------ */

	/**
	 * Brand term ids, read from a variation's parent.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int[]
	 */
	public static function get_brands( array $args ): array {
		$product = self::get_taxonomy_source( $args );

		if ( ! $product || ! method_exists( $product, 'get_brand_ids' ) ) {
			return [];
		}

		return array_map( 'intval', (array) $product->get_brand_ids() );
	}

	/**
	 * Whether the store tracks cost of goods.
	 *
	 * A feature behind a flag, and its getters complain when it is off, so
	 * the flag is read once rather than the getters guessed at.
	 *
	 * @return bool
	 */
	public static function cogs_enabled(): bool {
		static $enabled = null;

		if ( null !== $enabled ) {
			return $enabled;
		}

		$enabled = false;

		if ( function_exists( 'wc_get_container' ) && class_exists( 'Automattic\\WooCommerce\\Internal\\CostOfGoodsSold\\CostOfGoodsSoldController' ) ) {
			try {
				$controller = wc_get_container()->get( \Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController::class );
				$enabled    = is_object( $controller ) && method_exists( $controller, 'feature_is_enabled' ) && $controller->feature_is_enabled();
			} catch ( \Throwable $e ) {
				$enabled = false;
			}
		}

		return $enabled;
	}

	/**
	 * What the product costs the store, per unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null when cost of goods is not tracked.
	 */
	public static function get_cost( array $args ): ?float {
		$product = self::get( $args );

		if ( ! $product || ! self::cogs_enabled() || ! method_exists( $product, 'get_cogs_effective_value' ) ) {
			return null;
		}

		return (float) $product->get_cogs_effective_value();
	}

	/**
	 * The product's margin, as a percentage of its price.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float|null Null when cost is not tracked or there is no price.
	 */
	public static function get_margin_percentage( array $args ): ?float {
		$cost  = self::get_cost( $args );
		$price = self::get_price( $args );

		if ( null === $cost || $price <= 0 ) {
			return null;
		}

		return round( ( $price - $cost ) / $price * 100, 2 );
	}
}
