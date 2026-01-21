<?php
/**
 * EDD Options Helper
 *
 * Provides utilities for EDD condition options.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\EDD;

use ArrayPress\Conditions\Helpers\Format;
use EDD\Reports;

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

	/**
	 * Get country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_countries(): array {
		return Format::options( edd_get_country_list() );
	}

	/**
	 * Get date range options for period-based conditions.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_date_ranges(): array {
		$edd_options = Reports\get_dates_filter_options();

		// Remove 'other' (custom) as it doesn't make sense for conditions
		unset( $edd_options['other'] );

		return Format::options( $edd_options );
	}

	/**
	 * Get order status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_order_statuses(): array {
		return Format::options( edd_get_payment_statuses() );
	}

	/**
	 * Get gateway options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_gateways(): array {
		return Format::options( edd_get_payment_gateways(), 'admin_label' );
	}

	/**
	 * Get currency options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_currencies(): array {
		return Format::options( edd_get_currencies() );
	}

}