<?php
/**
 * Posted Data Utility
 *
 * Provides helper functions for extracting values from POST data.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class PostedData
 *
 * Utilities for extracting values from posted form data.
 */
class PostedData {

	/**
	 * Get a value from posted data with fallback keys.
	 *
	 * Supports dot notation for nested arrays (e.g., 'edd_address.country').
	 *
	 * @param array         $posted  The posted data array.
	 * @param array<string> $keys    Keys to try in order.
	 * @param mixed         $default Default value if not found.
	 *
	 * @return mixed The value or default.
	 */
	public static function get( array $posted, array $keys, mixed $default = '' ): mixed {
		foreach ( $keys as $key ) {
			// Handle dot notation for nested arrays
			if ( str_contains( $key, '.' ) ) {
				$value = self::get_nested( $posted, $key );
			} else {
				$value = $posted[ $key ] ?? null;
			}

			if ( $value !== null ) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Get nested value using dot notation.
	 *
	 * @param array  $array The array to search.
	 * @param string $key   Dot-notation key (e.g., 'edd_address.country').
	 *
	 * @return mixed|null The value or null if not found.
	 */
	private static function get_nested( array $array, string $key ): mixed {
		$keys = explode( '.', $key );

		foreach ( $keys as $segment ) {
			if ( ! is_array( $array ) || ! isset( $array[ $segment ] ) ) {
				return null;
			}
			$array = $array[ $segment ];
		}

		return $array;
	}

}