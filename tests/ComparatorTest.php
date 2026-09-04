<?php
/**
 * The comparison layer.
 *
 * Every condition in the library ends up here, so a mistake in this file is a
 * mistake in all of them at once.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Comparators\Comparator;
use PHPUnit\Framework\TestCase;

/**
 * Class ComparatorTest
 */
final class ComparatorTest extends TestCase {

	/**
	 * Compare an actual value against what an admin configured.
	 *
	 * @param string $type     Condition type.
	 * @param string $operator Operator.
	 * @param mixed  $rule     The value configured in the rule.
	 * @param mixed  $actual   The value observed at evaluation time.
	 * @param bool   $multiple Whether the rule accepts several values.
	 *
	 * @return bool
	 */
	private function compare( string $type, string $operator, mixed $rule, mixed $actual, bool $multiple = false ): bool {
		return ( new Comparator( $type, $multiple ) )->compare( $operator, $rule, $actual );
	}

	/**
	 * The direction matters: "cart total is over 100" asks whether the actual
	 * total exceeds the configured one, not the reverse. Inverting it turns
	 * every threshold rule into its own opposite.
	 */
	public function test_numeric_thresholds_read_actual_against_configured(): void {
		$this->assertTrue( $this->compare( 'number', '>', 100, 150 ), 'an actual 150 is over a configured 100' );
		$this->assertFalse( $this->compare( 'number', '>', 100, 50 ), 'an actual 50 is not over a configured 100' );

		$this->assertTrue( $this->compare( 'number', '<', 100, 50 ) );
		$this->assertFalse( $this->compare( 'number', '<', 100, 150 ) );
	}

	public function test_numeric_boundaries_are_inclusive_only_where_stated(): void {
		$this->assertFalse( $this->compare( 'number', '>', 100, 100 ) );
		$this->assertTrue( $this->compare( 'number', '>=', 100, 100 ) );
		$this->assertFalse( $this->compare( 'number', '<', 100, 100 ) );
		$this->assertTrue( $this->compare( 'number', '<=', 100, 100 ) );
	}

	public function test_numeric_equality(): void {
		$this->assertTrue( $this->compare( 'number', '==', 10, 10 ) );
		$this->assertFalse( $this->compare( 'number', '!=', 10, 10 ) );
		$this->assertTrue( $this->compare( 'number', '!=', 10, 11 ) );
	}

	/**
	 * Amounts arrive from post meta and request data as strings far more often
	 * than as floats, and "99.99" must not read as zero.
	 */
	public function test_numeric_strings_are_compared_as_numbers(): void {
		$this->assertTrue( $this->compare( 'number', '>', '100', '150.50' ) );
		$this->assertTrue( $this->compare( 'number', '>=', '99.99', '99.99' ) );
		$this->assertFalse( $this->compare( 'number', '>', '100.00', '99.99' ) );
	}

	public function test_an_unknown_operator_never_matches(): void {
		$this->assertFalse( $this->compare( 'number', 'sideways', 100, 150 ) );
		$this->assertFalse( $this->compare( 'text', 'sideways', 'a', 'a' ) );
	}

	public function test_text_equality_and_negation(): void {
		$this->assertTrue( $this->compare( 'text', '==', 'gmail.com', 'gmail.com' ) );
		$this->assertFalse( $this->compare( 'text', '==', 'gmail.com', 'outlook.com' ) );
		$this->assertTrue( $this->compare( 'text', '!=', 'gmail.com', 'outlook.com' ) );
	}

	/**
	 * Text matching is case-insensitive everywhere except equality, which is
	 * strict. A domain typed as "GMail.com" in a rule should still catch
	 * "gmail.com" on the contains-style operators.
	 */
	public function test_text_matching_ignores_case_except_for_equality(): void {
		$this->assertTrue( $this->compare( 'text', 'contains', 'GMAIL', 'buyer@gmail.com' ) );
		$this->assertTrue( $this->compare( 'text', 'starts_with', 'BUYER', 'buyer@gmail.com' ) );
		$this->assertTrue( $this->compare( 'text', 'ends_with', '.COM', 'buyer@gmail.com' ) );

		$this->assertFalse( $this->compare( 'text', '==', 'GMAIL.COM', 'gmail.com' ) );
	}

	public function test_emptiness_looks_at_the_observed_value(): void {
		$this->assertTrue( $this->compare( 'text', 'empty', '', '' ) );
		$this->assertFalse( $this->compare( 'text', 'empty', '', 'something' ) );
		$this->assertTrue( $this->compare( 'text', 'not_empty', '', 'something' ) );
	}

	/**
	 * A malformed pattern must fail closed rather than raise, or a typo in a
	 * rule takes the checkout down with it.
	 */
	public function test_an_invalid_regex_does_not_match_and_does_not_raise(): void {
		$this->assertFalse( $this->compare( 'text', 'regex', '/unterminated', 'anything' ) );
		$this->assertTrue( $this->compare( 'text', 'regex', '/^buyer@/', 'buyer@gmail.com' ) );
	}

	/**
	 * A rule listing several countries should match any one of them, and a
	 * "not in" rule should match everything else.
	 */
	public function test_a_multi_value_rule_matches_any_of_its_values(): void {
		$countries = [ 'GB', 'IE', 'FR' ];

		$this->assertTrue( $this->compare( 'select', 'any', $countries, 'IE', true ) );
		$this->assertFalse( $this->compare( 'select', 'any', $countries, 'US', true ) );
		$this->assertTrue( $this->compare( 'select', 'none', $countries, 'US', true ) );
		$this->assertFalse( $this->compare( 'select', 'none', $countries, 'GB', true ) );

		// "all" asks the other question: every configured value must be present.
		$this->assertTrue( $this->compare( 'select', 'all', [ 'GB', 'IE' ], [ 'GB', 'IE', 'FR' ], true ) );
		$this->assertFalse( $this->compare( 'select', 'all', [ 'GB', 'US' ], [ 'GB', 'IE', 'FR' ], true ) );
	}

	public function test_a_single_value_select_compares_directly(): void {
		$this->assertTrue( $this->compare( 'select', '==', 'stripe', 'stripe' ) );
		$this->assertFalse( $this->compare( 'select', '==', 'stripe', 'paypal' ) );
	}
	/**
	 * No answer is not "no".
	 *
	 * A provider that could not be reached, or a helper that returned
	 * something that is not a boolean at all, used to satisfy every
	 * "is X = No" rule -- so "Is VPN = No" passed whenever the lookup failed.
	 */
	public function test_a_boolean_without_an_answer_matches_neither_way(): void {
		foreach ( [ null, 'unknown', 2, 'maybe' ] as $unanswered ) {
			$this->assertFalse( $this->compare( 'boolean', 'yes', null, $unanswered ) );
			$this->assertFalse( $this->compare( 'boolean', 'no', null, $unanswered ) );
		}

		$this->assertTrue( $this->compare( 'boolean', 'no', null, false ) );
		$this->assertTrue( $this->compare( 'boolean', 'no', null, '0' ) );
		$this->assertTrue( $this->compare( 'boolean', 'yes', null, 'yes' ) );
	}

	/**
	 * A missing number is not nought.
	 *
	 * It used to cast to 0.0, so "order total < 10" fired for every request
	 * whose caller forgot to pass the total.
	 */
	public function test_a_missing_number_is_not_zero(): void {
		foreach ( [ null, '', 'abc', [] ] as $missing ) {
			$this->assertFalse( $this->compare( 'number', '<', 10, $missing ) );
			$this->assertFalse( $this->compare( 'number', '<=', 0, $missing ) );
			$this->assertFalse( $this->compare( 'number', '==', 0, $missing ) );
			$this->assertTrue( $this->compare( 'number', '!=', 0, $missing ) );
		}

		$this->assertFalse( $this->compare( 'number', '<', 'abc', 5 ), 'A rule without a number cannot be judged.' );
	}

	/**
	 * A tag that arrives as a number is compared, and "0" is a tag.
	 */
	public function test_tags_may_be_numbers(): void {
		$this->assertTrue( $this->compare( 'tags', 'any_exact', [ 13335 ], '13335' ) );
		$this->assertTrue( $this->compare( 'tags', 'any_exact', [ '0' ], '0' ) );
		$this->assertFalse( $this->compare( 'tags', 'any_exact', [ '' ], '' ), 'An empty tag matches nothing.' );
	}

	/**
	 * A single select compares values, not PHP's idea of truthiness.
	 *
	 * Loose comparison decided that true == "no", so a boolean helper
	 * matched whatever the rule said.
	 */
	public function test_a_single_select_is_not_fooled_by_a_boolean(): void {
		$this->assertFalse( $this->compare( 'select', '==', 'no', true ) );
		$this->assertTrue( $this->compare( 'select', '==', '1', true ) );
		$this->assertTrue( $this->compare( 'select', '==', '1', 1 ) );
		$this->assertTrue( $this->compare( 'select', '==', '1.50', 1.5 ) );
		$this->assertFalse( $this->compare( 'select', '==', '', null ), 'Nothing is not the empty option.' );
	}

	/**
	 * "0" is not empty.
	 */
	public function test_a_zero_is_not_empty(): void {
		$this->assertFalse( $this->compare( 'text', 'empty', null, '0' ) );
		$this->assertTrue( $this->compare( 'text', 'not_empty', null, '0' ) );
	}

	/**
	 * A date rule matches a timestamp.
	 *
	 * Helpers hand over timestamps as often as strings, and strtotime() of a
	 * timestamp is false.
	 */
	public function test_a_date_rule_matches_a_timestamp(): void {
		$this->assertTrue( $this->compare( 'date', '==', '2024-01-15', 1705276800 ) );
		$this->assertTrue( $this->compare( 'date', '==', '2024-01-15', '1705276800' ) );
		$this->assertTrue( $this->compare( 'date', '>', '2024-01-14', 1705276800 ) );
		$this->assertFalse( $this->compare( 'date', '==', '2024-01-15', null ) );
	}

	/**
	 * A missing time is not midnight.
	 */
	public function test_a_missing_time_is_not_midnight(): void {
		$this->assertFalse( $this->compare( 'time', '==', '00:00', null ) );
		$this->assertFalse( $this->compare( 'time', '==', '00:00', '' ) );
		$this->assertTrue( $this->compare( 'time', '==', '09:30', '09:30' ) );
	}
}
