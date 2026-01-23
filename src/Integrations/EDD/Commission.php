<?php
/**
 * EDD Commission Helper
 *
 * Provides commission-related utilities for EDD conditions.
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

/**
 * Class Commission
 *
 * Commission utilities for EDD conditions.
 */
class Commission {

	/**
	 * Get commission object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	public static function get( array $args ): ?object {
		if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
			return null;
		}

		return eddc_get_commission( $args['commission_id'] );
	}

	/**
	 * Get commission ID from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_id( array $args ): int {
		return (int) ( $args['commission_id'] ?? 0 );
	}

	/** -------------------------------------------------------------------------
	 * Amount Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the commission amount.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_amount( array $args ): float {
		$commission = self::get( $args );

		return $commission ? (float) $commission->amount : 0.0;
	}

	/**
	 * Get the commission rate.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_rate( array $args ): float {
		$commission = self::get( $args );

		return $commission ? (float) $commission->rate : 0.0;
	}

	/** -------------------------------------------------------------------------
	 * Detail Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the commission status.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_status( array $args ): string {
		$commission = self::get( $args );

		return $commission ? $commission->status : '';
	}

	/**
	 * Get the commission type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_type( array $args ): string {
		$commission = self::get( $args );

		return $commission ? ( $commission->type ?? 'percentage' ) : '';
	}

	/**
	 * Get commission status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_status_options(): array {
		return [
			[ 'value' => 'unpaid', 'label' => __( 'Unpaid', 'arraypress' ) ],
			[ 'value' => 'paid', 'label' => __( 'Paid', 'arraypress' ) ],
			[ 'value' => 'revoked', 'label' => __( 'Revoked', 'arraypress' ) ],
		];
	}

	/**
	 * Get commission type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_type_options(): array {
		return [
			[ 'value' => 'percentage', 'label' => __( 'Percentage', 'arraypress' ) ],
			[ 'value' => 'flat', 'label' => __( 'Flat Amount', 'arraypress' ) ],
		];
	}

	/** -------------------------------------------------------------------------
	 * Product Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the product ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_product_id( array $args ): int {
		$commission = self::get( $args );

		return $commission ? (int) $commission->download_id : 0;
	}

	/**
	 * Get the price ID (for variable pricing).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_price_id( array $args ): int {
		$commission = self::get( $args );

		return $commission ? (int) ( $commission->price_id ?? 0 ) : 0;
	}

	/**
	 * Get product category IDs.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_category_ids( array $args ): array {
		$product_id = self::get_product_id( $args );

		if ( ! $product_id ) {
			return [];
		}

		$terms = wp_get_object_terms( $product_id, 'download_category', [ 'fields' => 'ids' ] );

		return is_array( $terms ) ? $terms : [];
	}

	/**
	 * Get product tag IDs.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<int>
	 */
	public static function get_tag_ids( array $args ): array {
		$product_id = self::get_product_id( $args );

		if ( ! $product_id ) {
			return [];
		}

		$terms = wp_get_object_terms( $product_id, 'download_tag', [ 'fields' => 'ids' ] );

		return is_array( $terms ) ? $terms : [];
	}

	/** -------------------------------------------------------------------------
	 * Recipient Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the recipient user ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_user_id( array $args ): int {
		$commission = self::get( $args );

		return $commission ? (int) $commission->user_id : 0;
	}

	/**
	 * Get the recipient's email.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_user_email( array $args ): string {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id ) {
			return '';
		}

		$user = get_userdata( $user_id );

		return $user ? $user->user_email : '';
	}

	/** -------------------------------------------------------------------------
	 * Order Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the order ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_order_id( array $args ): int {
		$commission = self::get( $args );

		return $commission ? (int) $commission->payment_id : 0;
	}

	/** -------------------------------------------------------------------------
	 * Date Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the date created.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_date_created( array $args ): string {
		$commission = self::get( $args );

		if ( ! $commission || empty( $commission->date_created ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $commission->date_created ) );
	}

	/**
	 * Get the date paid.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Y-m-d formatted date or empty string.
	 */
	public static function get_date_paid( array $args ): string {
		$commission = self::get( $args );

		if ( ! $commission || empty( $commission->date_paid ) ) {
			return '';
		}

		return wp_date( 'Y-m-d', strtotime( $commission->date_paid ) );
	}

	/**
	 * Get the commission age in specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_age( array $args ): int {
		$commission = self::get( $args );

		if ( ! $commission || empty( $commission->date_created ) ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $commission->date_created, $parsed['unit'] );
	}

}