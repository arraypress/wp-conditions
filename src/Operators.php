<?php
/**
 * Operators Class
 *
 * Provides operator definitions for different field types.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
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

	/** -------------------------------------------------------------------------
	 * Core Comparison Operators
	 * ------------------------------------------------------------------------ */

	/**
	 * Equality operators (single value).
	 *
	 * @return array<string, string>
	 */
	public static function equality(): array {
		return [
			'==' => __( 'Is', 'arraypress' ),
			'!=' => __( 'Is not', 'arraypress' ),
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
			'>=' => __( 'Greater than or equal to', 'arraypress' ),
			'<=' => __( 'Less than or equal to', 'arraypress' ),
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

	/** -------------------------------------------------------------------------
	 * Text Operators
	 * ------------------------------------------------------------------------ */

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
	 * Text comparison operators with regex support.
	 *
	 * @return array<string, string>
	 */
	public static function text_advanced(): array {
		return array_merge( self::text(), [
			'regex' => __( 'Matches pattern', 'arraypress' ),
		] );
	}

	/** -------------------------------------------------------------------------
	 * Collection Operators (Arrays/Multi-select)
	 * ------------------------------------------------------------------------ */

	/**
	 * Collection operators for checking membership.
	 *
	 * Used for: select (multiple), post, term, user, ajax types.
	 *
	 * @return array<string, string>
	 */
	public static function collection(): array {
		return [
			'any'  => __( 'Is any of', 'arraypress' ),
			'none' => __( 'Is none of', 'arraypress' ),
			'all'  => __( 'Is all of', 'arraypress' ),
		];
	}

	/**
	 * Collection operators without "all" option.
	 *
	 * Used when "all" doesn't make logical sense.
	 *
	 * @return array<string, string>
	 */
	public static function collection_any_none(): array {
		return [
			'any'  => __( 'Is any of', 'arraypress' ),
			'none' => __( 'Is none of', 'arraypress' ),
		];
	}

	/** -------------------------------------------------------------------------
	 * Date/Time Operators
	 * ------------------------------------------------------------------------ */

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

	/** -------------------------------------------------------------------------
	 * Pattern Matching Operators
	 * ------------------------------------------------------------------------ */

	/**
	 * IP address operators.
	 *
	 * Supports exact match, CIDR notation, and wildcards.
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
	 * Tags/pattern operators - all matching modes.
	 *
	 * @return array<string, string>
	 */
	public static function tags(): array {
		return [
			'any_exact'     => __( 'Is any of', 'arraypress' ),
			'none_exact'    => __( 'Is none of', 'arraypress' ),
			'any_contains'  => __( 'Contains any of', 'arraypress' ),
			'none_contains' => __( 'Contains none of', 'arraypress' ),
			'any_starts'    => __( 'Starts with any of', 'arraypress' ),
			'none_starts'   => __( 'Starts with none of', 'arraypress' ),
			'any_ends'      => __( 'Ends with any of', 'arraypress' ),
			'none_ends'     => __( 'Ends with none of', 'arraypress' ),
		];
	}

	/**
	 * Tags operators - suffix matching only (ends with).
	 *
	 * Common use case for domain matching.
	 *
	 * @return array<string, string>
	 */
	public static function tags_ends(): array {
		return [
			'any_ends'  => __( 'Ends with any of', 'arraypress' ),
			'none_ends' => __( 'Ends with none of', 'arraypress' ),
		];
	}

	/** -------------------------------------------------------------------------
	 * Content Operators
	 * ------------------------------------------------------------------------ */

	/**
	 * Content presence operators.
	 *
	 * Used for checking if content contains something (shortcodes, blocks, etc.).
	 *
	 * @return array<string, string>
	 */
	public static function contains(): array {
		return [
			'==' => __( 'Contains', 'arraypress' ),
			'!=' => __( 'Does not contain', 'arraypress' ),
		];
	}

	/** -------------------------------------------------------------------------
	 * Type Resolution
	 * ------------------------------------------------------------------------ */

	/**
	 * Get operators for a specific field type.
	 *
	 * @param string $type     The field type.
	 * @param bool   $multiple Whether multiple selection is enabled.
	 *
	 * @return array<string, string>
	 */
	public static function for_type( string $type, bool $multiple = false ): array {
		return match ( $type ) {
			'number', 'number_unit' => self::numeric(),
			'text_unit' => self::text(),
			'boolean' => self::boolean(),
			'date' => self::date(),
			'time' => self::time(),
			'ip' => self::ip(),
			'email' => self::email(),
			'tags' => self::tags(),
			'select' => $multiple ? self::collection() : self::equality(),
			'post', 'term', 'user', 'ajax' => $multiple ? self::collection() : self::equality(),
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
			'text'             => self::text(),
			'text_advanced'    => self::text_advanced(),
			'text_unit'        => self::text(),
			'number'           => self::numeric(),
			'number_unit'      => self::numeric(),
			'boolean'          => self::boolean(),
			'date'             => self::date(),
			'time'             => self::time(),
			'ip'               => self::ip(),
			'email'            => self::email(),
			'tags'             => self::tags(),
			'tags_ends'        => self::tags_ends(),
			'equality'         => self::equality(),
			'contains'         => self::contains(),
			'collection'       => self::collection(),
			'collection_basic' => self::collection_any_none(),
		];
	}

}