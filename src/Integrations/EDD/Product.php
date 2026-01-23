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

namespace ArrayPress\Conditions\Integrations\EDD;

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

	/** -------------------------------------------------------------------------
	 * Core Product Methods
	 * ---------------------------------------------------------------------- */

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
		return Post::get_author( self::normalize_args( $args ) );
	}

	/**
	 * Get product status.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_status( array $args ): string {
		return Post::get_status( self::normalize_args( $args ) );
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
	 * Check if product has a featured image.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_featured_image( array $args ): bool {
		return Post::has_featured_image( self::normalize_args( $args ) );
	}

	/**
	 * Get word count of product description.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_word_count( array $args ): int {
		return Post::get_word_count( self::normalize_args( $args ) );
	}

	/**
	 * Check if product has an excerpt.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_excerpt( array $args ): bool {
		return Post::has_excerpt( self::normalize_args( $args ) );
	}

	/**
	 * Check if product contains a specific shortcode.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The shortcode tag to check for.
	 *
	 * @return bool
	 */
	public static function has_shortcode( array $args, ?string $user_value ): bool {
		return Post::has_shortcode( self::normalize_args( $args ), $user_value );
	}

	/**
	 * Check if product contains a specific Gutenberg block.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The block name to check for.
	 *
	 * @return bool
	 */
	public static function has_block( array $args, ?string $user_value ): bool {
		return Post::has_block( self::normalize_args( $args ), $user_value );
	}

	/**
	 * Get product meta value as text.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
	 *
	 * @return string
	 */
	public static function get_meta_text( array $args, ?string $user_value ): string {
		return Post::get_meta_text( self::normalize_args( $args ), $user_value );
	}

	/**
	 * Get product meta value as number.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
	 *
	 * @return float
	 */
	public static function get_meta_number( array $args, ?string $user_value ): float {
		return Post::get_meta_number( self::normalize_args( $args ), $user_value );
	}

	/** -------------------------------------------------------------------------
	 * EDD Product Type & Pricing
	 * ---------------------------------------------------------------------- */

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
	 * Get price option count for variable priced products.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_price_option_count( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_variable_prices' ) ) {
			return 0;
		}

		$prices = edd_get_variable_prices( $product_id );

		return is_array( $prices ) ? count( $prices ) : 0;
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

	/** -------------------------------------------------------------------------
	 * Bundle Methods
	 * ---------------------------------------------------------------------- */

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
	 * Get bundle product count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_bundle_count( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_bundled_products' ) ) {
			return 0;
		}

		$bundled = edd_get_bundled_products( $product_id );

		return is_array( $bundled ) ? count( $bundled ) : 0;
	}

	/** -------------------------------------------------------------------------
	 * File & Download Methods
	 * ---------------------------------------------------------------------- */

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
	 * Get file count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_file_count( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_get_download_files' ) ) {
			return 0;
		}

		$files = edd_get_download_files( $product_id );

		return is_array( $files ) ? count( $files ) : 0;
	}

	/**
	 * Check if product has files.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_files( array $args ): bool {
		return self::get_file_count( $args ) > 0;
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

	/** -------------------------------------------------------------------------
	 * Sales & Earnings Methods
	 * ---------------------------------------------------------------------- */

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

		return Stats::get_product_sales( $product_id, null, $parsed['unit'] );
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

		return Stats::get_product_earnings( $product_id, null, $parsed['unit'] );
	}

	/** -------------------------------------------------------------------------
	 * Taxonomy Methods
	 * ---------------------------------------------------------------------- */

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

	/** -------------------------------------------------------------------------
	 * Software Licensing Methods (requires EDD Software Licensing)
	 * ---------------------------------------------------------------------- */

	/**
	 * Check if licensing is enabled.
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
	 * Get license activation limit.
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
	 * Check if product has a license limit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_license_limit( array $args ): bool {
		return self::get_license_limit( $args ) > 0;
	}

	/**
	 * Check if license is lifetime (never expires).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_lifetime_license( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return false;
		}

		$download = new \EDD_SL_Download( $product_id );

		return $download->is_lifetime();
	}

	/**
	 * Get license expiration length (number).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The expiration length number (e.g., 1 for "1 year").
	 */
	public static function get_license_exp_length( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return 0;
		}

		$download = new \EDD_SL_Download( $product_id );

		return (int) $download->get_expiration_length();
	}

	/**
	 * Get license expiration unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The expiration unit (years, months, weeks, days).
	 */
	public static function get_license_exp_unit( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return '';
		}

		$download = new \EDD_SL_Download( $product_id );

		return (string) $download->get_expiration_unit();
	}

	/**
	 * Check if product has beta releases enabled.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_beta( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return false;
		}

		$download = new \EDD_SL_Download( $product_id );

		return $download->has_beta();
	}

	/**
	 * Get the current stable version.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_version( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! class_exists( 'EDD_SL_Download' ) ) {
			return '';
		}

		$download = new \EDD_SL_Download( $product_id );

		return (string) $download->get_version();
	}

	/** -------------------------------------------------------------------------
	 * Recurring/Subscription Methods (requires EDD Recurring)
	 * ---------------------------------------------------------------------- */

	/**
	 * Check if product is recurring.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_recurring( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return false;
		}

		return edd_recurring()->is_recurring( $product_id );
	}

	/**
	 * Get recurring billing period.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The billing period (day, week, month, quarter, semi-year, year).
	 */
	public static function get_billing_period( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return '';
		}

		return (string) edd_recurring()->get_period_single( $product_id );
	}

	/**
	 * Get billing times/limit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int 0 for unlimited, otherwise the number of times.
	 */
	public static function get_billing_times( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return 0;
		}

		return (int) edd_recurring()->get_times_single( $product_id );
	}

	/**
	 * Check if product has free trial.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_free_trial( array $args ): bool {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return false;
		}

		return edd_recurring()->has_free_trial( $product_id );
	}

	/**
	 * Get trial period quantity.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The trial period quantity (e.g., 14 for "14 days").
	 */
	public static function get_trial_quantity( array $args ): int {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return 0;
		}

		$trial = edd_recurring()->get_trial_period( $product_id );

		if ( empty( $trial ) || ! is_array( $trial ) ) {
			return 0;
		}

		return (int) ( $trial['quantity'] ?? 0 );
	}

	/**
	 * Get trial period unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The trial period unit (day, week, month, year).
	 */
	public static function get_trial_unit( array $args ): string {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return '';
		}

		$trial = edd_recurring()->get_trial_period( $product_id );

		if ( empty( $trial ) || ! is_array( $trial ) ) {
			return '';
		}

		return (string) ( $trial['unit'] ?? '' );
	}

	/**
	 * Check if product has signup fee.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_signup_fee( array $args ): bool {
		return self::get_signup_fee( $args ) > 0;
	}

	/**
	 * Get signup fee amount.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_signup_fee( array $args ): float {
		$product_id = self::get_id( $args );

		if ( ! $product_id || ! function_exists( 'edd_recurring' ) ) {
			return 0.0;
		}

		return (float) edd_recurring()->get_signup_fee_single( $product_id );
	}

}