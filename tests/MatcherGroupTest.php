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
use ArrayPress\Conditions\Models\MatchResultCollection;
use ArrayPress\Conditions\Models\MatchResult;
use ArrayPress\Conditions\Registry;
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
			$this->check( [
				[
					'condition' => 'not_registered',
					'operator' => '==',
					'value' => 'x',
				],
			] ),
			'a single unevaluable rule must not satisfy the group'
		);

		$this->assertFalse(
			$this->check( [
				[
					'condition' => 'not_registered',
					'operator' => '==',
					'value' => 'x',
				],
				[
					'condition' => 'also_missing',
					'operator' => '==',
					'value' => 'y',
				],
			] ),
			'several unevaluable rules must not satisfy the group either'
		);
	}

	/**
	 * A rule with no condition or no operator is not a rule yet -- a half-built
	 * row in the admin UI should not make its group match everything.
	 */
	public function test_an_incomplete_rule_does_not_match(): void {
		$this->assertFalse( $this->check( [
			[
				'condition' => '',
				'operator' => '==',
				'value' => 'x',
			],
		] ) );
		$this->assertFalse( $this->check( [
			[
				'condition' => 'something',
				'operator' => '',
				'value' => 'x',
			],
		] ) );
		$this->assertFalse( $this->check( [ [] ] ) );
	}

	/**
	 * Groups are ORed. One satisfiable group is enough, even when the others
	 * cannot be evaluated at all.
	 */
	public function test_groups_are_ored(): void {
		$matcher = new Matcher( 'unregistered_set', [] );

		$unevaluable = [
			'rules' => [
				[
					'condition' => 'not_registered',
					'operator' => '==',
					'value' => 'x',
				],
			],
		];

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
			$matcher->first_matching_group( [
				[
					'rules' => [
						[
							'condition' => 'nope',
							'operator' => '==',
							'value' => 1,
						],
					],
				],
			] )
		);
	}
	/**
	 * A rule's unit does not leak into the next rule.
	 *
	 * The unit and number were written into the shared arguments and never
	 * cleared, so "orders in 3 hours" followed by a plain number condition
	 * read that condition in hours too.
	 */
	public function test_a_units_do_not_leak_between_rules(): void {
		Registry::reset();

		$seen = [];

		Registry::register_set( 'leak_test', [
			'conditions' => [
				'windowed' => [
					'type'          => 'number_unit',
					'units'         => [ [ 'value' => 'hour' ] ],
					'compare_value' => static function ( array $args ) use ( &$seen ): int {
						$seen['windowed'] = $args['_unit'] ?? 'none';

						return 5;
					},
				],
				'plain'    => [
					'type'          => 'number',
					'compare_value' => static function ( array $args ) use ( &$seen ): int {
						$seen['plain'] = $args['_unit'] ?? 'none';

						return 5;
					},
				],
			],
		] );

		$matched = ( new Matcher( 'leak_test', [] ) )->matches( [
			[
				'rules' => [
					[
						'condition' => 'windowed',
						'operator' => '>=',
						'value' => [
							'number' => 3,
							'unit' => 'hour',
						],
					],
					[
						'condition' => 'plain',
						'operator' => '>=',
						'value' => 3,
					],
				],
			],
		] );

		$this->assertTrue( $matched );
		$this->assertSame( 'hour', $seen['windowed'] );
		$this->assertSame( 'none', $seen['plain'], 'The previous rule\'s unit leaked into this one.' );

		Registry::reset();
	}

	/**
	 * Rules are fetched in a determined order.
	 *
	 * Every rule has a menu_order of nought until somebody sets one, and
	 * "ORDER BY menu_order" alone left the database to pick, so which rule
	 * check() returned first could change between servers.
	 */
	public function test_rules_are_fetched_in_a_determined_order(): void {
		$GLOBALS['wpc_posts'] = [];

		( new Matcher( 'ordered_set', [] ) )->check();

		$this->assertSame(
			[
				'menu_order' => 'ASC',
				'ID' => 'ASC',
			],
			$GLOBALS['wpc_last_query']['orderby'] ?? null
		);
		$this->assertSame( 'ordered_set', $GLOBALS['wpc_last_query']['post_type'] );
	}

	/**
	 * A filtered collection still knows its first and last.
	 *
	 * filter() handed back array_filter()'s result, which keeps the original
	 * keys, and get_first() read [0] -- so a filtered collection with one
	 * match said it had one and then answered null.
	 */
	public function test_a_filtered_collection_still_has_a_first_and_last(): void {
		$collection = new MatchResultCollection( [ new MatchResult( false ), new MatchResult( true ) ] );

		$filtered = $collection->filter( static fn( MatchResult $r ): bool => $r->matched() );

		$this->assertCount( 1, $filtered );
		$this->assertNotNull( $filtered->get_first() );
		$this->assertNotNull( $filtered->get_last() );
		$this->assertTrue( $filtered->get_first()->matched() );
	}
	/**
	 * A key:value rule compares the value half.
	 *
	 * The helper reads the key half to know what to fetch; the comparator
	 * wants the value half. Without the reshaping step the observed value
	 * was compared against the whole "key:value" string, so six conditions
	 * could never match -- and the number variants lost the key at save
	 * time, because "stock:5" is not a number.
	 */
	public function test_a_key_value_rule_compares_the_value_half(): void {
		Registry::reset();

		Registry::register_set( 'kv_test', [
			'conditions' => [
				'meta_text'   => [
					'type'          => 'text',
					'compare_value' => static fn( array $args, $rule ) => $args['meta'][ explode( ':', (string) $rule )[0] ] ?? null,
					'user_value'    => static fn( $rule ) => explode( ':', (string) $rule, 2 )[1] ?? '',
				],
				'meta_number' => [
					'type'          => 'text',
					'compare_as'    => 'number',
					'compare_value' => static fn( array $args, $rule ) => $args['meta'][ explode( ':', (string) $rule )[0] ] ?? null,
					'user_value'    => static fn( $rule ) => explode( ':', (string) $rule, 2 )[1] ?? '',
				],
			],
		] );

		$matcher = new Matcher( 'kv_test', [
			'meta' => [
				'company' => 'Acme',
				'stock' => 7,
			],
		] );

		$this->assertTrue( $matcher->matches( [
			[
				'rules' => [
					[
						'condition' => 'meta_text',
						'operator' => '==',
						'value' => 'company:Acme',
					],
				],
			],
		] ) );
		$this->assertFalse( $matcher->matches( [
			[
				'rules' => [
					[
						'condition' => 'meta_text',
						'operator' => '==',
						'value' => 'company:Other',
					],
				],
			],
		] ) );
		$this->assertTrue( $matcher->matches( [
			[
				'rules' => [
					[
						'condition' => 'meta_number',
						'operator' => '>=',
						'value' => 'stock:5',
					],
				],
			],
		] ) );
		$this->assertFalse( $matcher->matches( [
			[
				'rules' => [
					[
						'condition' => 'meta_number',
						'operator' => '<',
						'value' => 'stock:5',
					],
				],
			],
		] ) );

		Registry::reset();
	}
}
