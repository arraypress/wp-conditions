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
		return Post::get_age( self::normalize_args( $args ) );
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
		return Post::get_terms( self::normalize_args( $args ), $taxonomy );
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

		$parsed = Parse::numb

		$unit   = $args['_unit'] ?? 'day';
		$number = (int) ( $args['_number'] ?? 1 );

		return Stats::get_product_sales( $product_id, null, $unit, $number );
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

		$unit   = $args['_unit'] ?? 'day';
		$number = (int) ( $args['_number'] ?? 1 );

		return Stats::get_product_earnings( $product_id, null, $unit, $number );
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

}