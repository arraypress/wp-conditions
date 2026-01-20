<?php
/**
 * Argument Parser Utility
 *
 * Provides utilities for parsing condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class Parse
 *
 * Utilities for parsing condition arguments.
 */
class Parse {

	/**
	 * Extract number_unit values from args.
	 *
	 * These values are injected by the Matcher during evaluation
	 * (prefixed with underscore to avoid collision with user args).
	 *
	 * @param array  $args           The condition arguments.
	 * @param string $default_unit   Default unit if not specified.
	 * @param int    $default_number Default number if not specified.
	 *
	 * @return array{unit: string, number: int}
	 */
	public static function number_unit( array $args, string $default_unit = 'day', int $default_number = 1 ): array {
		return [
			'unit'   => $args['_unit'] ?? $default_unit,
			'number' => (int) ( $args['_number'] ?? $default_number ),
		];
	}

	/**
	 * Extract text_unit values from args.
	 *
	 * @param array  $args         The condition arguments.
	 * @param string $default_unit Default unit if not specified.
	 *
	 * @return array{unit: string, text: string}
	 */
	public static function text_unit( array $args, string $default_unit = '' ): array {
		return [
			'unit' => $args['_unit'] ?? $default_unit,
			'text' => $args['_text'] ?? '',
		];
	}

	/**
	 * Parse a meta field value string.
	 *
	 * Extracts meta key and value from format "meta_key:value".
	 *
	 * @param string $input   The input string (e.g., "company_name:Acme Corp").
	 * @param string $default_key Default key if not in expected format.
	 *
	 * @return array{key: string, value: string}
	 */
	public static function meta( string $input, string $default_key = '' ): array {
		// Check for key:value format
		if ( str_contains( $input, ':' ) ) {
			$parts = explode( ':', $input, 2 );

			return [
				'key'   => trim( $parts[0] ),
				'value' => trim( $parts[1] ?? '' ),
			];
		}

		// No separator - treat entire input as value
		return [
			'key'   => $default_key,
			'value' => trim( $input ),
		];
	}

	/**
	 * Parse a meta field value and cast to appropriate type.
	 *
	 * @param string $input The input string.
	 * @param string $type  Expected type ('text' or 'number').
	 *
	 * @return array{key: string, value: mixed}
	 */
	public static function meta_typed( string $input, string $type = 'text' ): array {
		$parsed = self::meta( $input );

		if ( $type === 'number' ) {
			$parsed['value'] = is_numeric( $parsed['value'] ) ? (float) $parsed['value'] : 0;
		}

		return $parsed;
	}

}