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
	 * Condition configurations for type-aware sanitization.
	 *
	 * @var array
	 */
	private static array $conditions = [];

	/**
	 * Sanitize conditions data.
	 *
	 * @param array|mixed $conditions        Raw conditions data.
	 * @param array       $condition_configs Optional condition configurations for type-aware sanitization.
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
			if ( TypeSanitizer::is_empty( $sanitized_rule['value'], $type ) ) {
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

		// Get condition config for type-aware sanitization
		$config = self::$conditions[ $condition_id ] ?? [];

		// Use TypeSanitizer for type-aware value sanitization
		$value = TypeSanitizer::sanitize( $value, $config );

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

}