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
use ReflectionMethod;

/**
 * Class MatcherGroupTest
 */
final class MatcherGroupTest extends TestCase {

	/**
	 * Run a group through the matcher.
	 *
	 * @param array $rules Rules in the group.
	 * @param array $args  Evaluation context.
	 *
	 * @return bool
	 */
	private function check( array $rules, array $args = [] ): bool {
		$matcher = new Matcher( 'unregistered_set', $args );
		$method = new ReflectionMethod( $matcher, 'check_group' );

		return (bool) $method->invoke( $matcher, [ 'rules' => $rules ] );
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
}
