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

			// PHP stores an all-digit key as an int, and under strict types
			// that was a TypeError on save -- a white screen and nothing
			// saved -- for any id the editor happened to build from digits.
			$sanitized_group = self::sanitize_group( (string) $group_id, $group );

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

			$sanitized_rule = self::sanitize_rule( (string) $rule_id, $rule );

			// A rule without a condition or an operator is not a rule yet.
			if ( '' === $sanitized_rule['condition'] || '' === $sanitized_rule['operator'] ) {
				continue;
			}

			// Get condition config to check type
			$config = self::$conditions[ $sanitized_rule['condition'] ] ?? [];
			$type   = $config['type'] ?? 'text';

			// Skip if value is empty (but allow 0/'0' and skip check for boolean
			// type). The emptiness operators ask about the observed value and
			// carry none of their own, so they are exempt -- they could never be
			// saved otherwise.
			if ( ! in_array( $sanitized_rule['operator'], [ 'empty', 'not_empty' ], true )
				&& TypeSanitizer::is_empty( $sanitized_rule['value'], $type ) ) {
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

		// Handle number_unit and text_unit types. Without the second branch a
		// text_unit value fell through to the sorted-array case, where
		// { text: days, unit: hours } and { text: hours, unit: days } collide
		// and the second rule was dropped as a duplicate.
		if ( is_array( $value ) && isset( $value['number'] ) ) {
			return $value['number'] . ':' . ( $value['unit'] ?? '' );
		}

		if ( is_array( $value ) && isset( $value['text'] ) ) {
			return $value['text'] . ':' . ( $value['unit'] ?? '' );
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
		$raw = $rule['condition'] ?? '';
		$raw = is_scalar( $raw ) ? (string) $raw : '';

		// A registered id is taken as written. sanitize_key() lowercases, so
		// a developer's `orderTotal` was saved as `ordertotal`, matched no
		// configuration, and never resolved at runtime.
		$condition_id = isset( self::$conditions[ $raw ] ) ? $raw : sanitize_key( $raw );
		$config       = self::$conditions[ $condition_id ] ?? [];
		$operator     = self::sanitize_operator( $rule['operator'] ?? '', $config );
		$value        = $rule['value'] ?? null;

		// A regular expression is kept as written, if it compiles. Every
		// other value is sanitized for its type.
		$value = 'regex' === $operator
			? self::sanitize_pattern( $value )
			: TypeSanitizer::sanitize( $value, $config );

		return [
			'id'        => sanitize_key( $rule_id ),
			'condition' => $condition_id,
			'operator'  => $operator,
			'value'     => $value,
		];
	}

	/**
	 * Keep a regular expression as it was written, if it compiles.
	 *
	 * sanitize_text_field() rewrites `<`, strips anything shaped like a tag,
	 * collapses whitespace and removes %xx sequences -- so a lookbehind, a
	 * named group or a literal `<script` came back as a different pattern
	 * that quietly matched something else. A pattern that does not compile
	 * is dropped rather than stored: it would fail closed at runtime anyway.
	 *
	 * @param mixed $value The pattern as submitted.
	 *
	 * @return string The pattern, or '' if it is not one.
	 */
	private static function sanitize_pattern( mixed $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = trim( wp_check_invalid_utf8( (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a malformed pattern is an admin typo, not a warning to log.
		return false === @preg_match( $value, '' ) ? '' : $value;
	}

	/**
	 * Sanitize an operator.
	 *
	 * The condition's own operators are accepted alongside the built-in
	 * list: a condition that declares `operators` renders them in the editor,
	 * and a save that then refused them threw the rule away.
	 *
	 * @param mixed $operator The operator to sanitize.
	 * @param array $config   The condition configuration.
	 *
	 * @return string
	 */
	private static function sanitize_operator( mixed $operator, array $config = [] ): string {
		if ( ! is_string( $operator ) ) {
			return '';
		}

		$custom = $config['operators'] ?? null;

		if ( is_array( $custom ) && array_key_exists( $operator, $custom ) ) {
			return $operator;
		}

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
