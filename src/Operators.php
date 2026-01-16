<?php
/**
 * Operators Class
 *
 * Provides operator definitions for different field types.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions;

/**
 * Class Operators
 *
 * Manages operators for different condition types.
 */
class Operators {

	/**
	 * Text comparison operators.
	 *
	 * @return array<string, string>
	 */
	public static function text(): array {
		return [
			'=='           => __( 'Equals', 'arraypress' ),
			'!='           => __( 'Does not equal', 'arraypress' ),
			'contains'     => __( 'Contains', 'arraypress' ),
			'not_contains' => __( 'Does not contain', 'arraypress' ),
			'starts_with'  => __( 'Starts with', 'arraypress' ),
			'ends_with'    => __( 'Ends with', 'arraypress' ),
			'empty'        => __( 'Is empty', 'arraypress' ),
			'not_empty'    => __( 'Is not empty', 'arraypress' ),
		];
	}

	/**
	 * Numeric comparison operators.
	 *
	 * @return array<string, string>
	 */
	public static function numeric(): array {
		return [
			'==' => __( 'Equal to', 'arraypress' ),
			'!=' => __( 'Not equal to', 'arraypress' ),
			'>'  => __( 'Greater than', 'arraypress' ),
			'<'  => __( 'Less than', 'arraypress' ),
			'>=' => __( 'Greater or equal to', 'arraypress' ),
			'<=' => __( 'Less or equal to', 'arraypress' ),
		];
	}

	/**
	 * Single select operators.
	 *
	 * @return array<string, string>
	 */
	public static function select(): array {
		return [
			'==' => __( 'Is', 'arraypress' ),
			'!=' => __( 'Is not', 'arraypress' ),
		];
	}

	/**
	 * Multiple select operators.
	 *
	 * @return array<string, string>
	 */
	public static function select_multiple(): array {
		return [
			'any'  => __( 'Is any of', 'arraypress' ),
			'none' => __( 'Is none of', 'arraypress' ),
			'all'  => __( 'Is all of', 'arraypress' ),
		];
	}

	/**
	 * Array/multi-value operators (for post, term, user types).
	 *
	 * @return array<string, string>
	 */
	public static function array_single(): array {
		return [
			'==' => __( 'Is', 'arraypress' ),
			'!=' => __( 'Is not', 'arraypress' ),
		];
	}

	/**
	 * Array/multi-value operators for multiple selection.
	 *
	 * @return array<string, string>
	 */
	public static function array_multiple(): array {
		return [
			'any'  => __( 'Contains any of', 'arraypress' ),
			'none' => __( 'Contains none of', 'arraypress' ),
			'all'  => __( 'Contains all of', 'arraypress' ),
		];
	}

	/**
	 * Boolean operators.
	 *
	 * @return array<string, string>
	 */
	public static function boolean(): array {
		return [
			'yes' => __( 'Yes', 'arraypress' ),
			'no'  => __( 'No', 'arraypress' ),
		];
	}

	/**
	 * Date comparison operators.
	 *
	 * @return array<string, string>
	 */
	public static function date(): array {
		return [
			'==' => __( 'Is', 'arraypress' ),
			'!=' => __( 'Is not', 'arraypress' ),
			'>'  => __( 'Is after', 'arraypress' ),
			'<'  => __( 'Is before', 'arraypress' ),
			'>=' => __( 'Is on or after', 'arraypress' ),
			'<=' => __( 'Is on or before', 'arraypress' ),
		];
	}

	/**
	 * Time comparison operators.
	 *
	 * @return array<string, string>
	 */
	public static function time(): array {
		return [
			'==' => __( 'Is', 'arraypress' ),
			'!=' => __( 'Is not', 'arraypress' ),
			'>'  => __( 'Is after', 'arraypress' ),
			'<'  => __( 'Is before', 'arraypress' ),
		];
	}

	/**
	 * IP address operators.
	 *
	 * Supports exact match, CIDR notation (192.168.1.0/24), and wildcards (192.168.1.*).
	 *
	 * @return array<string, string>
	 */
	public static function ip(): array {
		return [
			'ip_match'     => __( 'Matches', 'arraypress' ),
			'ip_not_match' => __( 'Does not match', 'arraypress' ),
		];
	}

	/**
	 * Email address operators.
	 *
	 * Supports full email, @domain.com, .tld, and partial domain matching.
	 *
	 * @return array<string, string>
	 */
	public static function email(): array {
		return [
			'email_match'     => __( 'Matches', 'arraypress' ),
			'email_not_match' => __( 'Does not match', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - suffix matching (ends with).
	 *
	 * @return array<string, string>
	 */
	public static function tags_ends(): array {
		return [
			'any_ends'  => __( 'Ends with any of', 'arraypress' ),
			'none_ends' => __( 'Ends with none of', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - prefix matching (starts with).
	 *
	 * @return array<string, string>
	 */
	public static function tags_starts(): array {
		return [
			'any_starts'  => __( 'Starts with any of', 'arraypress' ),
			'none_starts' => __( 'Starts with none of', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - contains matching.
	 *
	 * @return array<string, string>
	 */
	public static function tags_contains(): array {
		return [
			'any_contains'  => __( 'Contains any of', 'arraypress' ),
			'none_contains' => __( 'Contains none of', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - exact matching.
	 *
	 * @return array<string, string>
	 */
	public static function tags_exact(): array {
		return [
			'any_exact'  => __( 'Is any of', 'arraypress' ),
			'none_exact' => __( 'Is none of', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - all matching types combined.
	 *
	 * @return array<string, string>
	 */
	public static function tags(): array {
		return array_merge(
			self::tags_exact(),
			self::tags_contains(),
			self::tags_starts(),
			self::tags_ends()
		);
	}

	/**
	 * Get operators for a specific type.
	 *
	 * @param string $type     The field type.
	 * @param bool   $multiple Whether multiple selection is enabled.
	 *
	 * @return array<string, string>
	 */
	public static function for_type( string $type, bool $multiple = false ): array {
		return match ( $type ) {
			'number', 'number_unit' => self::numeric(),
			'ip' => self::ip(),
			'email' => self::email(),
			'select' => $multiple ? self::select_multiple() : self::select(),
			'post', 'term', 'user' => $multiple ? self::array_multiple() : self::array_single(),
			'tags' => self::tags(),
			'boolean' => self::boolean(),
			'date' => self::date(),
			'time' => self::time(),
			default => self::text(),
		};
	}

	/**
	 * Get all operators grouped by type.
	 *
	 * Used for passing to JavaScript.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_all(): array {
		return [
			'text'            => self::text(),
			'number'          => self::numeric(),
			'number_unit'     => self::numeric(),
			'ip'              => self::ip(),
			'email'           => self::email(),
			'select'          => self::select(),
			'select_multiple' => self::select_multiple(),
			'array'           => self::array_single(),
			'array_multiple'  => self::array_multiple(),
			'tags'            => self::tags(),
			'tags_ends'       => self::tags_ends(),
			'tags_starts'     => self::tags_starts(),
			'tags_contains'   => self::tags_contains(),
			'tags_exact'      => self::tags_exact(),
			'boolean'         => self::boolean(),
			'date'            => self::date(),
			'time'            => self::time(),
		];
	}

}