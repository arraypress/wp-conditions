<?php
/**
 * Conditions Sanitizer
 *
 * Sanitizes condition data before saving.
 *
 * @package     ArrayPress\Conditions\Admin
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Admin;

/**
 * Class Sanitizer
 *
 * Handles sanitization of condition data.
 */
class Sanitizer {

	/**
	 * Condition configurations for custom sanitization.
	 *
	 * @var array
	 */
	private static array $conditions = [];

	/**
	 * Sanitize conditions data.
	 *
	 * @param array|mixed $conditions        Raw conditions data.
	 * @param array       $condition_configs Optional condition configurations for custom sanitization.
	 *
	 * @return array
	 */
	public static function sanitize_conditions( mixed $conditions, array $condition_configs = [] ): array {
		if ( ! is_array( $conditions ) ) {
			return [];
		}

		self::$conditions = $condition_configs;
		$sanitized        = [];

		foreach ( $conditions as $group_id => $group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}

			$sanitized_group = self::sanitize_group( $group_id, $group );

			if ( ! empty( $sanitized_group['rules'] ) ) {
				$sanitized[] = $sanitized_group;
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize a condition group.
	 *
	 * @param string $group_id The group ID.
	 * @param array  $group    The group data.
	 *
	 * @return array
	 */
	private static function sanitize_group( string $group_id, array $group ): array {
		$sanitized = [
			'id'    => sanitize_key( $group_id ),
			'match' => 'all', // AND logic within group
			'rules' => [],
		];

		$rules = $group['rules'] ?? [];

		if ( ! is_array( $rules ) ) {
			return $sanitized;
		}

		$seen_signatures = [];

		foreach ( $rules as $rule_id => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			$sanitized_rule = self::sanitize_rule( $rule_id, $rule );

			// Skip if no condition selected
			if ( empty( $sanitized_rule['condition'] ) ) {
				continue;
			}

			// Get condition config to check type
			$config = self::$conditions[ $sanitized_rule['condition'] ] ?? [];
			$type   = $config['type'] ?? 'text';

			// Skip if value is empty (but allow 0/'0' and skip check for boolean type)
			if ( self::is_value_empty( $sanitized_rule['value'], $type ) ) {
				continue;
			}

			// Generate signature to detect duplicates
			$signature = self::get_rule_signature( $sanitized_rule );

			// Skip if we've already seen this exact rule in this group
			if ( in_array( $signature, $seen_signatures, true ) ) {
				continue;
			}

			$seen_signatures[]    = $signature;
			$sanitized['rules'][] = $sanitized_rule;
		}

		return $sanitized;
	}

	/**
	 * Generate a unique signature for a rule to detect duplicates.
	 *
	 * @param array $rule The sanitized rule.
	 *
	 * @return string
	 */
	private static function get_rule_signature( array $rule ): string {
		$condition = $rule['condition'] ?? '';
		$operator  = $rule['operator'] ?? '';
		$value     = $rule['value'] ?? '';

		return $condition . '|' . $operator . '|' . self::normalize_value_for_signature( $value );
	}

	/**
	 * Normalize a value for signature comparison.
	 *
	 * Arrays are sorted and deduped (order shouldn't matter for duplicates).
	 * Strings are kept case-sensitive (user may intentionally have different cases).
	 *
	 * @param mixed $value The value to normalize.
	 *
	 * @return string
	 */
	private static function normalize_value_for_signature( mixed $value ): string {
		if ( is_null( $value ) ) {
			return '';
		}

		// Handle number_unit type
		if ( is_array( $value ) && isset( $value['number'] ) ) {
			return $value['number'] . ':' . ( $value['unit'] ?? '' );
		}

		// Handle arrays (multi-select values)
		if ( is_array( $value ) ) {
			$normalized = array_unique( $value );
			sort( $normalized, SORT_STRING );

			return implode( ',', $normalized );
		}

		// Strings: keep case-sensitive
		return (string) $value;
	}

	/**
	 * Sanitize a single rule.
	 *
	 * @param string $rule_id The rule ID.
	 * @param array  $rule    The rule data.
	 *
	 * @return array
	 */
	private static function sanitize_rule( string $rule_id, array $rule ): array {
		$condition_id = sanitize_key( $rule['condition'] ?? '' );
		$operator     = $rule['operator'] ?? '';
		$value        = $rule['value'] ?? null;

		// Get condition config if available
		$config = self::$conditions[ $condition_id ] ?? [];

		// Apply custom sanitize callback if defined
		if ( ! empty( $config['sanitize'] ) && is_callable( $config['sanitize'] ) ) {
			$value = call_user_func( $config['sanitize'], $value, $operator, $config );
		} else {
			$value = self::sanitize_value( $value );
		}

		return [
			'id'        => sanitize_key( $rule_id ),
			'condition' => $condition_id,
			'operator'  => self::sanitize_operator( $operator ),
			'value'     => $value,
		];
	}

	/**
	 * Sanitize an operator.
	 *
	 * @param string $operator The operator to sanitize.
	 *
	 * @return string
	 */
	private static function sanitize_operator( string $operator ): string {
		$allowed = [
			// Comparison
			'==',
			'!=',
			'>',
			'<',
			'>=',
			'<=',
			// Text
			'contains',
			'not_contains',
			'starts_with',
			'ends_with',
			'empty',
			'not_empty',
			'regex',
			// Array
			'any',
			'none',
			'all',
			// Tags
			'any_ends',
			'none_ends',
			'any_starts',
			'none_starts',
			'any_contains',
			'none_contains',
			'any_exact',
			'none_exact',
			// IP
			'ip_match',
			'ip_not_match',
			// Email
			'email_match',
			'email_not_match',
			// Boolean
			'yes',
			'no',
		];

		return in_array( $operator, $allowed, true ) ? $operator : '';
	}

	/**
	 * Sanitize a value.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return mixed
	 */
	private static function sanitize_value( mixed $value ): mixed {
		if ( is_null( $value ) ) {
			return null;
		}

		// Handle number_unit type (array with number and unit)
		if ( is_array( $value ) && isset( $value['number'] ) ) {
			return [
				'number' => sanitize_text_field( (string) $value['number'] ),
				'unit'   => sanitize_key( $value['unit'] ?? '' ),
			];
		}

		// Handle arrays (multi-select values)
		if ( is_array( $value ) ) {
			return array_values( array_unique( array_map( 'sanitize_text_field', $value ) ) );
		}

		// Handle scalar values
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Check if a value should be considered empty.
	 *
	 * This allows 0 and '0' as valid values while rejecting truly empty values.
	 * Boolean types don't require a value (they use operator only).
	 *
	 * @param mixed  $value The value to check.
	 * @param string $type  The condition type.
	 *
	 * @return bool True if the value is empty and should be skipped.
	 */
	private static function is_value_empty( mixed $value, string $type = 'text' ): bool {
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
		if ( is_array( $value ) && isset( $value['number'] ) ) {
			// Allow 0 as valid
			if ( $value['number'] === '' || $value['number'] === null ) {
				return true;
			}

			return false;
		}

		// 0, '0', false are NOT empty (valid values)
		return false;
	}

}