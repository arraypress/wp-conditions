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
		// A rule without a number cannot be judged.
		if ( ! is_numeric( $user_value ) ) {
			return false;
		}

		// An observed value that is not a number -- missing, null, "abc" --
		// used to cast to nought, so "order total < 10" fired for every
		// request whose caller forgot to pass the total. No number is not
		// equal to any number, and is neither above nor below one.
		if ( ! is_numeric( $compare_value ) ) {
			return '!=' === $operator;
		}

		$user_value    = (float) $user_value;
		$compare_value = (float) $compare_value;

		// Both sides are already cast to float, so == and === agree. Loose is
		// kept because a strict float comparison invites the reader to think
		// type juggling is happening here when it is not.
		// phpcs:disable Universal.Operators.StrictComparisons
		return match ( $operator ) {
			'==' => $compare_value == $user_value,
			'!=' => $compare_value != $user_value,
			'>' => $compare_value > $user_value,
			'<' => $compare_value < $user_value,
			'>=' => $compare_value >= $user_value,
			'<=' => $compare_value <= $user_value,
			default => false,
			// phpcs:enable Universal.Operators.StrictComparisons
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
			// Not empty(): "0" is a value.
			'empty' => '' === $compare_value,
			'not_empty' => '' !== $compare_value,
			// Silenced deliberately: a malformed pattern is an admin typo in a
			// rule, and the answer is "does not match", not a warning on the
			// customer's checkout page. preg_match returns false either way,
			// so the result is already correct -- @ only suppresses the notice.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
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
			'==' => self::same( $user_value, $compare_value ),
			'!=' => ! self::same( $user_value, $compare_value ),
			default => false,
		};
	}

	/**
	 * Whether a configured value and an observed one are the same value.
	 *
	 * A rule saved from the admin holds strings; the value it is compared
	 * against can be an int, a float or a bool depending on the condition, so
	 * a select storing "1" has to match a helper returning 1. PHP's loose
	 * comparison did that, and also decided that true == "no", so a boolean
	 * helper matched whatever the rule said. Numbers are compared as numbers,
	 * booleans as "1" and "0", and everything else as text.
	 *
	 * @param mixed $a One value.
	 * @param mixed $b The other.
	 *
	 * @return bool
	 */
	private static function same( mixed $a, mixed $b ): bool {
		// Nothing is not the same as anything, the empty option included.
		if ( null === $a || null === $b ) {
			return false;
		}

		if ( is_bool( $a ) ) {
			$a = $a ? '1' : '0';
		}

		if ( is_bool( $b ) ) {
			$b = $b ? '1' : '0';
		}

		if ( ! is_scalar( $a ) || ! is_scalar( $b ) ) {
			return false;
		}

		if ( is_numeric( $a ) && is_numeric( $b ) ) {
			// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual -- both sides are floats.
			return (float) $a == (float) $b;
		}

		return (string) $a === (string) $b;
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
		$tags = (array) $user_value;

		// Compare values can be a scalar (one provider/operator name) OR
		// an array (e.g. ProxyCheck's primary operator name PLUS its
		// `additional_operators` siblings — Mullvad is registered as
		// `additional_operators` of Hide.me's exit nodes, so a rule
		// targeting "Mullvad" needs to match either side).
		$compare_values = is_array( $compare_value ) ? $compare_value : [ $compare_value ];
		$compare_values = array_map( fn( $v ) => strtolower( (string) $v ), $compare_values );
		$compare_values = array_filter( $compare_values, fn( $v ) => $v !== '' );

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

		// Match if ANY tag matches ANY compare-value entry.
		$matches_any = false;
		foreach ( $tags as $tag ) {
			// A tag saved programmatically for an ASN or an id is an int, and
			// under strict types that was a TypeError in trim(). And "0" is a
			// tag, not an absence.
			$tag = strtolower( trim( (string) $tag ) );
			if ( '' === $tag ) {
				continue;
			}

			foreach ( $compare_values as $value ) {
				$matched = match ( $match_type ) {
					'starts' => str_starts_with( $value, $tag ),
					'ends' => str_ends_with( $value, $tag ),
					'contains' => str_contains( $value, $tag ),
					'exact' => $value === $tag,
					default => str_ends_with( $value, $tag ),
				};

				if ( $matched ) {
					$matches_any = true;
					break 2;
				}
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
		// No answer is not "no". A provider that could not be reached, or a
		// helper that returned something that is not a boolean at all, used to
		// satisfy every "is X = No" rule, so "Is VPN = No" passed whenever the
		// lookup failed.
		if ( null === $compare_value ) {
			return false;
		}

		$is_true = filter_var( $compare_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

		if ( null === $is_true ) {
			return false;
		}

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
		$user_date    = self::to_timestamp( $user_value );
		$compare_date = self::to_timestamp( $compare_value );

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
		// "1970-01-01 " followed by nothing parses as midnight, so a missing
		// time used to equal 00:00.
		if ( ! is_scalar( $user_value ) || ! is_scalar( $compare_value )
			|| '' === (string) $user_value || '' === (string) $compare_value ) {
			return false;
		}

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

	/**
	 * A date value as a timestamp.
	 *
	 * Helpers hand over timestamps as often as strings, and strtotime() of a
	 * timestamp is false -- so a date rule never matched one.
	 *
	 * @param mixed $value A date string or a timestamp.
	 *
	 * @return int|false
	 */
	private static function to_timestamp( mixed $value ): int|false {
		if ( is_int( $value ) || is_float( $value ) ) {
			return (int) $value;
		}

		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return false;
		}

		return is_numeric( $value ) ? (int) $value : strtotime( $value );
	}
}
