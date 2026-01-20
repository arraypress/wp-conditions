<?php
/**
 * EDD Product Helper
 *
 * Provides product-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\EDD;

use ArrayPress\Conditions\Helpers\DateTime;
use ArrayPress\Conditions\Helpers\Parse;
use ArrayPress\Conditions\Helpers\Post;
use WP_Post;

/**
 * Class Product
 *
 * Product utilities for EDD conditions.
 */
class Product {

	/**
	 * Normalize args to use post_id from product_id.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array Normalized args with post_id set.
	 */
	private static function normalize_args( array $args ): array {
		if ( isset( $args['product_id'] ) && ! isset( $args['post_id'] ) ) {
			$args['post_id'] = $args['product_id'];
		}

		return $args;
	}

	/**
	 * Get product post object.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WP_Post|null
	 */
	public static function get( array $args ): ?WP_Post {
		return Post::get( self::normalize_args( $args ) );
	}

	/**
	 * Get product ID from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_id( array $args ): int {
		return (int) ( $args['product_id'] ?? $args['post_id'] ?? 0 );
	}

	/**
	 * Get product author ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_author( array $args ): int {
		$post = self::get( $args );

		return (int) ( $post?->post_author ?? 0 );
	}

	/**
	 * Get product status.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_status( array $args ): string {
		$post = self::get( $args );

		return $post ? get_post_status( $post ) : '';
	}

	/**
	 * Get product age in specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_age( array $args ): int {
		$post = self::get( $args );

		if ( ! $post || empty( $post->post_date ) ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $post->post_date, $parsed['unit'] );
	}

	/**
	 * Get product term IDs for a taxonomy.
	 *
	 * @param array  $args     The condition arguments.
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array<int>
	 */
	public static function get_terms( array $args, string $taxonomy ): array {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return [];
		}

		$terms = wp_get_object_terms( $product_id, $taxonomy, [ 'fields' => 'ids' ] );

		return is_array( $terms ) && ! is_wp_error( $terms ) ? $terms : [];
	}

	/**
	 * Get product type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_type( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_type' ) ) {
			return '';
		}

		return edd_get_download_type( $product_id );
	}

	/**
	 * Get product price.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_price( array $args ): float {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_price' ) ) {
			return 0.0;
		}

		return (float) edd_get_download_price( $product_id );
	}

	/**
	 * Check if product has variable prices.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_variable_prices( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_has_variable_prices' ) ) {
			return false;
		}

		return edd_has_variable_prices( $product_id );
	}

	/**
	 * Get variable prices array.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array
	 */
	public static function get_variable_prices( array $args ): array {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_variable_prices' ) ) {
			return [];
		}

		$prices = edd_get_variable_prices( $product_id );

		return is_array( $prices ) ? $prices : [];
	}

	/**
	 * Get price option count for variable priced products.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_price_option_count( array $args ): int {
		return count( self::get_variable_prices( $args ) );
	}

	/**
	 * Check if product is free.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_free( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_is_free_download' ) ) {
			return false;
		}

		return edd_is_free_download( $product_id );
	}

	/**
	 * Check if product is a bundle.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_bundle( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_is_bundled_product' ) ) {
			return false;
		}

		return edd_is_bundled_product( $product_id );
	}

	/**
	 * Get bundled products.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array
	 */
	public static function get_bundled_products( array $args ): array {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_bundled_products' ) ) {
			return [];
		}

		$bundled = edd_get_bundled_products( $product_id );

		return is_array( $bundled ) ? $bundled : [];
	}

	/**
	 * Get bundle product count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_bundle_count( array $args ): int {
		return count( self::get_bundled_products( $args ) );
	}

	/**
	 * Check if product has purchase notes.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_notes( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_product_notes' ) ) {
			return false;
		}

		$notes = edd_get_product_notes( $product_id );

		return ! empty( $notes );
	}

	/**
	 * Get downloadable files.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array
	 */
	public static function get_files( array $args ): array {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_files' ) ) {
			return [];
		}

		$files = edd_get_download_files( $product_id );

		return is_array( $files ) ? $files : [];
	}

	/**
	 * Get file count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_file_count( array $args ): int {
		return count( self::get_files( $args ) );
	}

	/**
	 * Check if product has files.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_files( array $args ): bool {
		return ! empty( self::get_files( $args ) );
	}

	/**
	 * Get file download limit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_download_limit( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_file_download_limit' ) ) {
			return 0;
		}

		return (int) edd_get_file_download_limit( $product_id );
	}

	/**
	 * Check if product has a download limit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_download_limit( array $args ): bool {
		return self::get_download_limit( $args ) > 0;
	}

	/**
	 * Get total sales count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_sales( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_sales_stats' ) ) {
			return 0;
		}

		return (int) edd_get_download_sales_stats( $product_id );
	}

	/**
	 * Get total earnings.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_earnings( array $args ): float {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_earnings_stats' ) ) {
			return 0.0;
		}

		return (float) edd_get_download_earnings_stats( $product_id );
	}

	/**
	 * Get sales within a time period.
	 *
	 * @param array $args The condition arguments (includes _unit and _number).
	 *
	 * @return int
	 */
	public static function get_sales_in_period( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return Stats::get_product_sales( $product_id, null, $parsed['unit'], $parsed['number'] );
	}

	/**
	 * Get earnings within a time period.
	 *
	 * @param array $args The condition arguments (includes _unit and _number).
	 *
	 * @return float
	 */
	public static function get_earnings_in_period( array $args ): float {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return 0.0;
		}

		$parsed = Parse::number_unit( $args );

		return Stats::get_product_earnings( $product_id, null, $parsed['unit'], $parsed['number'] );
	}

	/**
	 * Check if licensing is enabled (requires EDD Software Licensing).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_licensing( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return false;
		}

		$download = new \EDD_SL_Download( $product_id );

		return $download->licensing_enabled();
	}

	/**
	 * Get license activation limit (requires EDD Software Licensing).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_license_limit( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return 0;
		}

		$download = new \EDD_SL_Download( $product_id );

		return (int) $download->get_activation_limit();
	}

	/**
	 * Check if product has a license limit (requires EDD Software Licensing).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_license_limit( array $args ): bool {
		return self::get_license_limit( $args ) > 0;
	}

	/**
	 * Check if product is recurring (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_recurring( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return false;
		}

		return EDD_Recurring()->is_recurring( $product_id );
	}

	/**
	 * Get recurring billing period (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_billing_period( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return '';
		}

		return EDD_Recurring()->get_period_single( $product_id );
	}

	/**
	 * Check if product has free trial (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_free_trial( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return false;
		}

		return EDD_Recurring()->has_free_trial( $product_id );
	}

	/**
	 * Check if product has signup fee (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_signup_fee( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return false;
		}

		$fee = EDD_Recurring()->get_signup_fee_single( $product_id );

		return $fee > 0;
	}

	/**
	 * Get signup fee amount (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_signup_fee( array $args ): float {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return 0.0;
		}

		return (float) EDD_Recurring()->get_signup_fee_single( $product_id );
	}

	/**
	 * Get trial period duration (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array{quantity: int, unit: string}
	 */
	public static function get_trial_period( array $args ): array {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return [ 'quantity' => 0, 'unit' => '' ];
		}

		return [
			'quantity' => (int) EDD_Recurring()->get_trial_quantity_single( $product_id ),
			'unit'     => EDD_Recurring()->get_trial_unit_single( $product_id ),
		];
	}

	/**
	 * Get billing times/limit (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int 0 for unlimited, otherwise the number of times.
	 */
	public static function get_billing_times( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'EDD_Recurring' ) ) {
			return 0;
		}

		return (int) EDD_Recurring()->get_times_single( $product_id );
	}

	/**
	 * Check if product has unlimited billing (requires EDD Recurring).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_unlimited_billing( array $args ): bool {
		return self::get_billing_times( $args ) === 0;
	}

	/**
	 * Get product categories.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_categories( array $args ): array {
		return self::get_terms( $args, 'download_category' );
	}

	/**
	 * Get product tags.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_tags( array $args ): array {
		return self::get_terms( $args, 'download_tag' );
	}

	/**
	 * Check if product has specific category.
	 *
	 * @param array    $args        The condition arguments.
	 * @param int|string $category_id The category ID or slug.
	 *
	 * @return bool
	 */
	public static function has_category( array $args, int|string $category_id ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return false;
		}

		return has_term( $category_id, 'download_category', $product_id );
	}

	/**
	 * Check if product has specific tag.
	 *
	 * @param array    $args   The condition arguments.
	 * @param int|string $tag_id The tag ID or slug.
	 *
	 * @return bool
	 */
	public static function has_tag( array $args, int|string $tag_id ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return false;
		}

		return has_term( $tag_id, 'download_tag', $product_id );
	}

	/**
	 * Get product SKU (if using an SKU extension).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_sku( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return '';
		}

		// Check common SKU meta keys
		$sku = get_post_meta( $product_id, 'edd_sku', true );

		if ( empty( $sku ) ) {
			$sku = get_post_meta( $product_id, '_edd_sku', true );
		}

		return (string) $sku;
	}

	/**
	 * Check if product is featured.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_featured( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return false;
		}

		// Check common featured meta keys
		$featured = get_post_meta( $product_id, 'edd_feature_download', true );

		if ( empty( $featured ) ) {
			$featured = get_post_meta( $product_id, '_edd_feature_download', true );
		}

		return ! empty( $featured );
	}

	/**
	 * Get product meta value.
	 *
	 * @param array  $args     The condition arguments.
	 * @param string $meta_key The meta key.
	 * @param bool   $single   Whether to return a single value.
	 *
	 * @return mixed
	 */
	public static function get_meta( array $args, string $meta_key, bool $single = true ): mixed {
		$product_id = self::get_id( $args );

		if ( ! $product_id ) {
			return $single ? '' : [];
		}

		return get_post_meta( $product_id, $meta_key, $single );
	}

}