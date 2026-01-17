<?php
/**
 * Type Sanitizer
 *
 * Handles sanitization of values based on field type and configuration.
 *
 * @package     ArrayPress\Conditions\Admin
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Admin;

use ArrayPress\IPUtils\IP;
use ArrayPress\EmailUtils\Email;
use DateTime;
use Exception;

/**
 * Class TypeSanitizer
 *
 * Provides type-aware sanitization for condition values.
 */
class TypeSanitizer {

	/**
	 * Sanitize a value based on its field type and configuration.
	 *
	 * If a custom sanitize callback is provided in the config, it takes priority.
	 * Otherwise, type-specific sanitization is applied using field metadata
	 * like min, max, step, and allowed values.
	 *
	 * @param mixed $value  The value to sanitize.
	 * @param array $config The condition configuration.
	 *
	 * @return mixed The sanitized value.
	 */
	public static function sanitize( mixed $value, array $config = [] ): mixed {
		// Custom callback takes priority
		if ( ! empty( $config['sanitize'] ) && is_callable( $config['sanitize'] ) ) {
			return call_user_func( $config['sanitize'], $value, $config );
		}

		if ( is_null( $value ) ) {
			return null;
		}

		$type = $config['type'] ?? 'text';

		return match ( $type ) {
			'number' => self::number( $value, $config ),
			'number_unit' => self::number_unit( $value, $config ),
			'date' => self::date( $value ),
			'time' => self::time( $value ),
			'email' => self::email( $value ),
			'ip' => self::ip( $value ),
			'tags' => self::tags( $value ),
			'boolean' => null, // Boolean uses operator only, no value needed
			'select' => self::selection( $value, $config ),
			'post', 'term', 'user', 'ajax' => self::selection( $value, $config ),
			default => self::text( $value ),
		};
	}

	/**
	 * Sanitize a text value.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized text.
	 */
	public static function text( mixed $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Sanitize a number value with optional constraints.
	 *
	 * Determines whether to return an integer or float based on the step value.
	 * Applies min/max constraints if defined in the configuration.
	 *
	 * @param mixed $value  The value to sanitize.
	 * @param array $config The condition configuration with optional min, max, step.
	 *
	 * @return float|int|string The sanitized number, or empty string if no value.
	 */
	public static function number( mixed $value, array $config = [] ): float|int|string {
		// Allow empty values (rule will be skipped by is_empty check)
		if ( $value === '' || $value === null ) {
			return '';
		}

		// Non-numeric values default to min or 0
		if ( ! is_numeric( $value ) ) {
			return $config['min'] ?? 0;
		}

		// Determine if integer based on step
		$step       = $config['step'] ?? 1;
		$is_integer = ( $step == (int) $step && $step >= 1 );

		$value = $is_integer ? (int) $value : (float) $value;

		// Apply min constraint
		if ( isset( $config['min'] ) ) {
			$value = max( $value, $config['min'] );
		}

		// Apply max constraint
		if ( isset( $config['max'] ) ) {
			$value = min( $value, $config['max'] );
		}

		return $value;
	}

	/**
	 * Sanitize a number with unit value.
	 *
	 * Validates both the numeric portion and ensures the unit is from
	 * the allowed units list defined in the configuration.
	 *
	 * @param mixed $value  The value to sanitize (expected array with 'number' and 'unit').
	 * @param array $config The condition configuration with optional units array.
	 *
	 * @return array{number: float|int|string, unit: string} The sanitized number_unit value.
	 */
	public static function number_unit( mixed $value, array $config = [] ): array {
		if ( ! is_array( $value ) ) {
			return [ 'number' => '', 'unit' => '' ];
		}

		// Sanitize the number part using the same logic
		$number = self::number( $value['number'] ?? '', $config );

		// Sanitize the unit - must be from allowed units
		$unit          = sanitize_key( $value['unit'] ?? '' );
		$allowed_units = array_column( $config['units'] ?? [], 'value' );

		if ( ! empty( $allowed_units ) && ! in_array( $unit, $allowed_units, true ) ) {
			$unit = $allowed_units[0] ?? '';
		}

		return [
			'number' => $number,
			'unit'   => $unit,
		];
	}

	/**
	 * Sanitize a date value.
	 *
	 * Validates the date is in Y-m-d format and represents a valid calendar date
	 * using PHP's DateTime class for reliable parsing.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized date in Y-m-d format, or empty string if invalid.
	 */
	public static function date( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );

		if ( empty( $value ) ) {
			return '';
		}

		try {
			$date = DateTime::createFromFormat( 'Y-m-d', $value );

			// Ensure the date parsed correctly and matches the input
			// (prevents things like 2024-02-31 becoming 2024-03-02)
			if ( $date && $date->format( 'Y-m-d' ) === $value ) {
				return $value;
			}
		} catch ( Exception $e ) {
			// Invalid date
		}

		return '';
	}

	/**
	 * Sanitize a time value.
	 *
	 * Validates the time is in H:i or H:i:s format with valid hour/minute/second ranges
	 * using PHP's DateTime class for reliable parsing.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized time, or empty string if invalid.
	 */
	public static function time( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );

		if ( empty( $value ) ) {
			return '';
		}

		// Try H:i:s format first
		try {
			$time = DateTime::createFromFormat( 'H:i:s', $value );
			if ( $time && $time->format( 'H:i:s' ) === $value ) {
				return $value;
			}
		} catch ( Exception $e ) {
			// Try next format
		}

		// Try H:i format
		try {
			$time = DateTime::createFromFormat( 'H:i', $value );
			if ( $time && $time->format( 'H:i' ) === $value ) {
				return $value;
			}
		} catch ( Exception $e ) {
			// Invalid time
		}

		return '';
	}

	/**
	 * Sanitize an IP address or pattern.
	 *
	 * Supports single IP addresses, CIDR notation (192.168.1.0/24),
	 * and wildcard patterns (192.168.1.*).
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized IP pattern, or empty string if invalid.
	 */
	public static function ip( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );

		if ( empty( $value ) ) {
			return '';
		}

		// Use IP library for validation
		if ( class_exists( IP::class ) ) {
			// Single IP address
			if ( IP::is_valid( $value ) ) {
				return $value;
			}

			// CIDR range
			if ( IP::is_valid_range( $value ) ) {
				return $value;
			}

			// Wildcard pattern - use library's wildcard validation
			if ( str_contains( $value, '*' ) ) {
				// The IP class has matches_wildcard which validates the pattern
				// We can test against a known IP to see if pattern is valid
				$test_ip = '192.168.1.1';
				try {
					IP::matches_wildcard( $test_ip, $value );

					// If no exception, pattern syntax is valid
					return $value;
				} catch ( Exception $e ) {
					// Invalid pattern
				}
			}
		}

		// Fallback without library
		if ( filter_var( $value, FILTER_VALIDATE_IP ) !== false ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize an email address or pattern.
	 *
	 * Supports full email addresses, domain patterns (@domain.com),
	 * TLD patterns (.edu), and partial domain matching using the Email class.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string The sanitized email pattern, or empty string if invalid.
	 */
	public static function email( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );

		if ( empty( $value ) ) {
			return '';
		}

		// Use Email library for full email validation
		$email = Email::parse( $value );
		if ( $email && $email->valid() ) {
			return $email->normalized();
		}

		// Handle pattern formats (not full emails)
		$lower = strtolower( $value );

		// Domain pattern with @ prefix (@domain.com, @gmail.com)
		if ( str_starts_with( $lower, '@' ) && self::is_valid_domain_pattern( substr( $lower, 1 ) ) ) {
			return $lower;
		}

		// TLD pattern (.edu, .gov, .co.uk)
		if ( str_starts_with( $lower, '.' ) && self::is_valid_tld_pattern( substr( $lower, 1 ) ) ) {
			return $lower;
		}

		// Domain without @ (domain.com, gmail.com)
		if ( self::is_valid_domain_pattern( $lower ) ) {
			return $lower;
		}

		return '';
	}

	/**
	 * Sanitize a tags array.
	 *
	 * Filters and sanitizes an array of string tags, removing empty values.
	 *
	 * @param mixed $value The value to sanitize (expected array).
	 *
	 * @return array<string> The sanitized tags array.
	 */
	public static function tags( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map( 'sanitize_text_field', $value )
			)
		);
	}

	/**
	 * Sanitize a selection value (single or multiple).
	 *
	 * Handles both single-select and multi-select fields based on
	 * the multiple flag in the configuration.
	 *
	 * @param mixed $value  The value to sanitize.
	 * @param array $config The condition configuration with optional 'multiple' flag.
	 *
	 * @return array<string>|string The sanitized selection value(s).
	 */
	public static function selection( mixed $value, array $config = [] ): array|string {
		$multiple = $config['multiple'] ?? false;

		if ( $multiple ) {
			if ( ! is_array( $value ) ) {
				return [];
			}

			return array_values(
				array_unique(
					array_map( 'sanitize_text_field', $value )
				)
			);
		}

		return sanitize_text_field( (string) $value );
	}

	/**
	 * Check if a value should be considered empty for a given type.
	 *
	 * This allows 0 and '0' as valid values while rejecting truly empty values.
	 * Boolean types don't require a value (they use operator only).
	 *
	 * @param mixed  $value The value to check.
	 * @param string $type  The condition type.
	 *
	 * @return bool True if the value is empty and should be skipped.
	 */
	public static function is_empty( mixed $value, string $type = 'text' ): bool {
		// Boolean types don't require a value - they use operator only (yes/no)
		if ( $type === 'boolean' ) {
			return false;
		}

		// Null is empty
		if ( is_null( $value ) ) {
			return true;
		}

		// Empty string is empty
		if ( $value === '' ) {
			return true;
		}

		// Empty array is empty
		if ( is_array( $value ) && empty( $value ) ) {
			return true;
		}

		// Handle number_unit type - check if number part is empty
		if ( is_array( $value ) && array_key_exists( 'number', $value ) ) {
			if ( $value['number'] === '' || $value['number'] === null ) {
				return true;
			}

			return false;
		}

		// 0, '0', false are NOT empty (valid values)
		return false;
	}

	/**
	 * Check if a string is a valid domain pattern.
	 *
	 * @param string $domain The domain to check.
	 *
	 * @return bool True if valid domain pattern.
	 */
	private static function is_valid_domain_pattern( string $domain ): bool {
		// Must have at least one dot and valid characters
		if ( ! str_contains( $domain, '.' ) ) {
			return false;
		}

		$parts = explode( '.', $domain );

		// Each part must be non-empty and contain only valid chars
		foreach ( $parts as $part ) {
			if ( empty( $part ) ) {
				return false;
			}

			// Allow alphanumeric and hyphens (standard domain rules)
			if ( ! ctype_alnum( str_replace( '-', '', $part ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if a string is a valid TLD pattern.
	 *
	 * @param string $tld The TLD to check (without leading dot).
	 *
	 * @return bool True if valid TLD pattern.
	 */
	private static function is_valid_tld_pattern( string $tld ): bool {
		// TLD must be 2+ characters, only letters (and dots for compound TLDs like co.uk)
		if ( strlen( $tld ) < 2 ) {
			return false;
		}

		// Allow compound TLDs like co.uk
		$parts = explode( '.', $tld );
		foreach ( $parts as $part ) {
			if ( empty( $part ) || ! ctype_alpha( $part ) ) {
				return false;
			}
		}

		return true;
	}

}