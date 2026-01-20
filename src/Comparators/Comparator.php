<?php
/**
 * Comparator
 *
 * Handles comparison logic for different field types.
 *
 * @package     ArrayPress\Conditions\Comparators
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Comparators;

use ArrayPress\IPUtils\IP;
use ArrayPress\EmailUtils\Email;

/**
 * Class Comparator
 *
 * Performs comparisons based on field type.
 */
class Comparator {

	/**
	 * The field type.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Whether multiple selection is enabled.
	 *
	 * @var bool
	 */
	private bool $multiple;

	/**
	 * Constructor.
	 *
	 * @param string $type     The field type.
	 * @param bool   $multiple Whether multiple selection is enabled.
	 */
	public function __construct( string $type, bool $multiple = false ) {
		$this->type     = $type;
		$this->multiple = $multiple;
	}

	/**
	 * Compare values.
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The value configured by the user.
	 * @param mixed  $compare_value The actual value to compare against.
	 *
	 * @return bool
	 */
	public function compare( string $operator, mixed $user_value, mixed $compare_value ): bool {
		// Operator-based routing (allows flexible input types)
		if ( str_starts_with( $operator, 'email_' ) ) {
			return $this->compare_email( $operator, $user_value, $compare_value );
		}

		if ( str_starts_with( $operator, 'ip_' ) ) {
			return $this->compare_ip( $operator, $user_value, $compare_value );
		}

		// Type-based routing
		return match ( $this->type ) {
			'number', 'number_unit' => $this->compare_numeric( $operator, $user_value, $compare_value ),
			'text_unit' => $this->compare_text( $operator, $user_value, $compare_value ),
			'tags' => $this->compare_tags( $operator, $user_value, $compare_value ),
			'boolean' => $this->compare_boolean( $operator, $compare_value ),
			'date' => $this->compare_date( $operator, $user_value, $compare_value ),
			'time' => $this->compare_time( $operator, $user_value, $compare_value ),
			'select' => $this->multiple
				? $this->compare_collection( $operator, $user_value, $compare_value )
				: $this->compare_equality( $operator, $user_value, $compare_value ),
			'post', 'term', 'user', 'ajax' => $this->compare_collection( $operator, $user_value, $compare_value ),
			default => $this->compare_text( $operator, $user_value, $compare_value ),
		};
	}

	/**
	 * Compare numeric values.
	 *
	 * Operators: ==, !=, >, <, >=, <=
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_numeric( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$user_value    = (float) $user_value;
		$compare_value = (float) $compare_value;

		return match ( $operator ) {
			'==' => $compare_value == $user_value,
			'!=' => $compare_value != $user_value,
			'>' => $compare_value > $user_value,
			'<' => $compare_value < $user_value,
			'>=' => $compare_value >= $user_value,
			'<=' => $compare_value <= $user_value,
			default => false,
		};
	}

	/**
	 * Compare text values.
	 *
	 * Operators: ==, !=, contains, not_contains, starts_with, ends_with, empty, not_empty, regex
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_text( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$user_value    = (string) $user_value;
		$compare_value = (string) $compare_value;

		return match ( $operator ) {
			'==' => $compare_value === $user_value,
			'!=' => $compare_value !== $user_value,
			'contains' => str_contains( strtolower( $compare_value ), strtolower( $user_value ) ),
			'not_contains' => ! str_contains( strtolower( $compare_value ), strtolower( $user_value ) ),
			'starts_with' => str_starts_with( strtolower( $compare_value ), strtolower( $user_value ) ),
			'ends_with' => str_ends_with( strtolower( $compare_value ), strtolower( $user_value ) ),
			'empty' => empty( $compare_value ),
			'not_empty' => ! empty( $compare_value ),
			'regex' => (bool) @preg_match( $user_value, $compare_value ),
			default => false,
		};
	}

	/**
	 * Compare with simple equals/not equals.
	 *
	 * Operators: ==, !=
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_equality( string $operator, mixed $user_value, mixed $compare_value ): bool {
		return match ( $operator ) {
			'==' => $compare_value == $user_value,
			'!=' => $compare_value != $user_value,
			default => false,
		};
	}

	/**
	 * Compare collection/array values.
	 *
	 * Operators: ==, !=, any, none, all
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value (selected items).
	 * @param mixed  $compare_value The compare value (actual items).
	 *
	 * @return bool
	 */
	private function compare_collection( string $operator, mixed $user_value, mixed $compare_value ): bool {
		// Normalize to arrays
		$user_values    = (array) $user_value;
		$compare_values = (array) $compare_value;

		// Convert to strings for comparison
		$user_values    = array_map( 'strval', $user_values );
		$compare_values = array_map( 'strval', $compare_values );

		return match ( $operator ) {
			'==', 'any' => ! empty( array_intersect( $user_values, $compare_values ) ),
			'!=', 'none' => empty( array_intersect( $user_values, $compare_values ) ),
			'all' => empty( array_diff( $user_values, $compare_values ) ),
			default => false,
		};
	}

	/**
	 * Compare IP address values.
	 *
	 * Operators: ip_match, ip_not_match
	 *
	 * Supports exact match, CIDR notation (192.168.1.0/24), and wildcards (192.168.1.*).
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The IP pattern(s) entered by user.
	 * @param mixed  $compare_value The actual IP address to check.
	 *
	 * @return bool
	 */
	private function compare_ip( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$compare_value = (string) $compare_value;

		// Handle empty values
		if ( empty( $compare_value ) ) {
			return $operator === 'ip_not_match';
		}

		// Normalize patterns to array
		$patterns = array_map( 'trim', (array) $user_value );
		$patterns = array_filter( $patterns );

		// Use IP::is_match which handles exact, CIDR, and wildcard
		$matches = IP::is_match( $compare_value, $patterns );

		return match ( $operator ) {
			'ip_match' => $matches,
			'ip_not_match' => ! $matches,
			default => false,
		};
	}

	/**
	 * Compare email address values.
	 *
	 * Operators: email_match, email_not_match
	 *
	 * Supports full email, @domain.com, .tld, and partial domain matching.
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The email pattern(s) entered by user.
	 * @param mixed  $compare_value The actual email address to check.
	 *
	 * @return bool
	 */
	private function compare_email( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$compare_value = (string) $compare_value;

		// Handle empty values
		if ( empty( $compare_value ) ) {
			return $operator === 'email_not_match';
		}

		// Normalize patterns to array
		$patterns = array_map( 'trim', (array) $user_value );
		$patterns = array_filter( $patterns );

		if ( empty( $patterns ) ) {
			return $operator === 'email_not_match';
		}

		// Use Email::matches_any() for full pattern matching
		$email = Email::parse( $compare_value );

		if ( ! $email ) {
			return $operator === 'email_not_match';
		}

		$matches = $email->matches_any( $patterns );

		return match ( $operator ) {
			'email_match' => $matches,
			'email_not_match' => ! $matches,
			default => false,
		};
	}

	/**
	 * Compare tags values.
	 *
	 * Operators: any_exact, none_exact, any_contains, none_contains,
	 *            any_starts, none_starts, any_ends, none_ends
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The tags entered by user (array of patterns).
	 * @param mixed  $compare_value The actual value to check against.
	 *
	 * @return bool
	 */
	private function compare_tags( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$tags          = (array) $user_value;
		$compare_value = strtolower( (string) $compare_value );

		// Determine match type from operator
		$match_type = 'ends'; // default
		$want_match = true;   // 'any' = want match, 'none' = want no match

		if ( str_starts_with( $operator, 'none_' ) ) {
			$want_match = false;
			$match_type = substr( $operator, 5 ); // Remove 'none_'
		} elseif ( str_starts_with( $operator, 'any_' ) ) {
			$want_match = true;
			$match_type = substr( $operator, 4 ); // Remove 'any_'
		} else {
			// Legacy support: 'any' = 'any_ends', 'none' = 'none_ends'
			if ( $operator === 'none' ) {
				$want_match = false;
			}
		}

		// Check if compare_value matches any of the tags
		$matches_any = false;
		foreach ( $tags as $tag ) {
			$tag = strtolower( trim( $tag ) );
			if ( empty( $tag ) ) {
				continue;
			}

			$matched = match ( $match_type ) {
				'starts' => str_starts_with( $compare_value, $tag ),
				'ends' => str_ends_with( $compare_value, $tag ),
				'contains' => str_contains( $compare_value, $tag ),
				'exact' => $compare_value === $tag,
				default => str_ends_with( $compare_value, $tag ),
			};

			if ( $matched ) {
				$matches_any = true;
				break;
			}
		}

		return $want_match ? $matches_any : ! $matches_any;
	}

	/**
	 * Compare boolean values.
	 *
	 * Operators: yes, no
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_boolean( string $operator, mixed $compare_value ): bool {
		$is_true = filter_var( $compare_value, FILTER_VALIDATE_BOOLEAN );

		return match ( $operator ) {
			'yes' => $is_true === true,
			'no' => $is_true === false,
			default => false,
		};
	}

	/**
	 * Compare date values.
	 *
	 * Operators: ==, !=, >, <, >=, <=
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_date( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$user_date    = strtotime( (string) $user_value );
		$compare_date = strtotime( (string) $compare_value );

		if ( $user_date === false || $compare_date === false ) {
			return false;
		}

		// Normalize to start of day for date comparison
		$user_date    = strtotime( 'midnight', $user_date );
		$compare_date = strtotime( 'midnight', $compare_date );

		return match ( $operator ) {
			'==' => $compare_date === $user_date,
			'!=' => $compare_date !== $user_date,
			'>' => $compare_date > $user_date,
			'<' => $compare_date < $user_date,
			'>=' => $compare_date >= $user_date,
			'<=' => $compare_date <= $user_date,
			default => false,
		};
	}

	/**
	 * Compare time values.
	 *
	 * Operators: ==, !=, >,
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The user value.
	 * @param mixed  $compare_value The compare value.
	 *
	 * @return bool
	 */
	private function compare_time( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$user_time    = strtotime( '1970-01-01 ' . $user_value );
		$compare_time = strtotime( '1970-01-01 ' . $compare_value );

		if ( $user_time === false || $compare_time === false ) {
			return false;
		}

		return match ( $operator ) {
			'==' => $compare_time === $user_time,
			'!=' => $compare_time !== $user_time,
			'>' => $compare_time > $user_time,
			'<' => $compare_time < $user_time,
			default => false,
		};
	}

}