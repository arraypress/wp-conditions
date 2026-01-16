<?php
/**
 * EDD Options Helper
 *
 * Provides utilities for EDD condition options.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers;

/**
 * Class Options
 *
 * Option utilities for EDD conditions.
 */
class Options {

	/**
	 * Get discount options for AJAX select.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $ids    Specific IDs to retrieve.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_discount_options( ?string $search, ?array $ids ): array {
		if ( ! function_exists( 'edd_get_discounts' ) ) {
			return [];
		}

		$args = [
			'number' => 20,
			'status' => [ 'active', 'inactive' ],
		];

		if ( $ids ) {
			$args['id__in'] = array_map( 'intval', $ids );
		} elseif ( $search ) {
			$args['search'] = $search;
		}

		$discounts = edd_get_discounts( $args );

		if ( empty( $discounts ) ) {
			return [];
		}

		return array_map( function ( $discount ) {
			return [
				'value' => (string) $discount->id,
				'label' => $discount->code . ' (' . $discount->name . ')',
			];
		}, $discounts );
	}

	/**
	 * Get customer options for AJAX select.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $ids    Specific IDs to retrieve.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_customer_options( ?string $search, ?array $ids ): array {
		if ( ! function_exists( 'edd_get_customers' ) ) {
			return [];
		}

		$args = [
			'number' => 20,
		];

		if ( $ids ) {
			$args['id__in'] = array_map( 'intval', $ids );
		} elseif ( $search ) {
			$args['search']         = $search;
			$args['search_columns'] = [ 'name', 'email' ];
		}

		$customers = edd_get_customers( $args );

		if ( empty( $customers ) ) {
			return [];
		}

		return array_map( function ( $customer ) {
			return [
				'value' => (string) $customer->id,
				'label' => $customer->name . ' (' . $customer->email . ')',
			];
		}, $customers );
	}

}