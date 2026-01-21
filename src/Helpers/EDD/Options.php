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

namespace ArrayPress\Conditions\Helpers\EDD;

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

	/**
	 * Get country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_countries(): array {
		if ( ! function_exists( 'edd_get_country_list' ) ) {
			return [];
		}

		$countries = edd_get_country_list();
		$options   = [];

		foreach ( $countries as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
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

		$options = [];
		foreach ( $edd_options as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Get order status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_order_statuses(): array {
		$statuses = edd_get_payment_statuses();
		$options  = [];

		foreach ( $statuses as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Get gateway options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_gateways(): array {
		$gateways = edd_get_payment_gateways();
		$options  = [];

		foreach ( $gateways as $value => $data ) {
			$options[] = [
				'value' => $value,
				'label' => $data['admin_label'] ?? $value,
			];
		}

		return $options;
	}

}