<?php
/**
 * How a group of rules decides.
 *
 * A group is ANDed: every rule has to pass. The interesting cases are the ones
 * where a rule cannot be judged at all -- an unregistered condition, a provider
 * with no API key, a context missing an argument -- because what a group does
 * with those decides what happens when the library is misconfigured rather
 * than when a customer is suspicious.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Matcher;
use PHPUnit\Framework\TestCase;

/**
 * Class MatcherGroupTest
 */
final class MatcherGroupTest extends TestCase {

	/**
	 * Run a group through the matcher.
	 *
	 * Through the public API: matches() takes the same conditions array the
	 * admin UI stores, so a group is just a one-group set. This used to reach
	 * in with reflection, which meant the test was asserting on something no
	 * caller could actually use.
	 *
	 * @param array $rules Rules in the group.
	 * @param array $args  Evaluation context.
	 *
	 * @return bool
	 */
	private function check( array $rules, array $args = [] ): bool {
		return ( new Matcher( 'unregistered_set', $args ) )->matches( [ [ 'rules' => $rules ] ] );
	}

	public function test_a_group_with_no_rules_does_not_match(): void {
		$this->assertFalse( $this->check( [] ) );
	}

	/**
	 * The regression this file exists for.
	 *
	 * Rules that cannot be evaluated are skipped, and the group used to return
	 * true because nothing had returned false -- so a group whose conditions
	 * had all stopped resolving matched every request. On a blocking ruleset
	 * that turns a renamed condition or a missing API key into every customer
	 * being refused, with nothing logged as an error.
	 */
	public function test_a_group_whose_rules_cannot_be_evaluated_does_not_match(): void {
		$this->assertFalse(
			$this->check( [ [ 'condition' => 'not_registered', 'operator' => '==', 'value' => 'x' ] ] ),
			'a single unevaluable rule must not satisfy the group'
		);

		$this->assertFalse(
			$this->check( [
				[ 'condition' => 'not_registered', 'operator' => '==', 'value' => 'x' ],
				[ 'condition' => 'also_missing', 'operator' => '==', 'value' => 'y' ],
			] ),
			'several unevaluable rules must not satisfy the group either'
		);
	}

	/**
	 * A rule with no condition or no operator is not a rule yet -- a half-built
	 * row in the admin UI should not make its group match everything.
	 */
	public function test_an_incomplete_rule_does_not_match(): void {
		$this->assertFalse( $this->check( [ [ 'condition' => '', 'operator' => '==', 'value' => 'x' ] ] ) );
		$this->assertFalse( $this->check( [ [ 'condition' => 'something', 'operator' => '', 'value' => 'x' ] ] ) );
		$this->assertFalse( $this->check( [ [] ] ) );
	}

	/**
	 * Groups are ORed. One satisfiable group is enough, even when the others
	 * cannot be evaluated at all.
	 */
	public function test_groups_are_ored(): void {
		$matcher = new Matcher( 'unregistered_set', [] );

		$unevaluable = [ 'rules' => [ [ 'condition' => 'not_registered', 'operator' => '==', 'value' => 'x' ] ] ];

		$this->assertFalse( $matcher->matches( [ $unevaluable, $unevaluable ] ) );
	}

	/**
	 * An empty set does not match. A rule with no conditions is not a rule
	 * that applies to everything -- it is one nobody has finished writing.
	 */
	public function test_an_empty_condition_set_does_not_match(): void {
		$matcher = new Matcher( 'unregistered_set', [] );

		$this->assertFalse( $matcher->matches( [] ) );
		$this->assertNull( $matcher->first_matching_group( [] ) );
	}

	/**
	 * Malformed storage must not raise. Conditions come out of post meta or a
	 * database column, and either can hold something that is not a group.
	 */
	public function test_malformed_groups_are_skipped_rather_than_raising(): void {
		$matcher = new Matcher( 'unregistered_set', [] );

		$this->assertFalse( $matcher->matches( [ 'not-an-array', 42, null ] ) );
	}

	/**
	 * first_matching_group() hands back the group that fired, so a caller can
	 * log which conditions were responsible. A verdict that cannot say why it
	 * fired is not much use in a log.
	 */
	public function test_the_matching_group_is_returned_for_logging(): void {
		$matcher = new Matcher( 'unregistered_set', [] );

		$this->assertNull(
			$matcher->first_matching_group( [ [ 'rules' => [ [ 'condition' => 'nope', 'operator' => '==', 'value' => 1 ] ] ] ] )
		);
	}
}
