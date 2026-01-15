<?php
/**
 * EDD Formatting Helper
 *
 * Provides formatting utilities for EDD condition options.
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers;

/**
 * Class Formatting
 *
 * Formatting utilities for EDD conditions.
 */
class Formatting {

	/**
	 * Format options array for select fields.
	 *
	 * @param array  $options   The raw options array.
	 * @param string $label_key The key to use for the label (for nested arrays).
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function format_options( array $options, string $label_key = '' ): array {
		$formatted = [];

		foreach ( $options as $value => $label ) {
			if ( is_array( $label ) && $label_key ) {
				$label = $label[ $label_key ] ?? $value;
			}
			$formatted[] = [
				'value' => (string) $value,
				'label' => (string) $label,
			];
		}

		return $formatted;
	}

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

}