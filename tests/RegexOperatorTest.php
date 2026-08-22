<?php
/**
 * Regex rules and the slashes that break them.
 *
 * WordPress adds slashes to every value in $_POST. A regex is the condition
 * type where that is fatal rather than cosmetic: the pattern is made of
 * backslashes, so an unslashed save doubles them, and the next save doubles
 * them again, until a rule that used to catch fraud silently catches nothing.
 *
 * MetaBox::save_meta() unslashes before sanitizing. These tests pin what that
 * has to protect.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Comparators\Comparator;
use PHPUnit\Framework\TestCase;

/**
 * Class RegexOperatorTest
 */
final class RegexOperatorTest extends TestCase {

	/**
	 * Run a pattern against a value.
	 *
	 * @param string $pattern The pattern configured in the rule.
	 * @param string $actual  The value observed at evaluation time.
	 *
	 * @return bool
	 */
	private function pattern_matches( string $pattern, string $actual ): bool {
		return ( new Comparator( 'text' ) )->compare( 'regex', $pattern, $actual );
	}

	public function test_a_pattern_matches_what_it_describes(): void {
		$this->assertTrue( $this->pattern_matches( '/^buyer@/', 'buyer@example.com' ) );
		$this->assertFalse( $this->pattern_matches( '/^buyer@/', 'seller@example.com' ) );
	}

	/**
	 * The escape is the whole point of the pattern. A dot that stops being
	 * escaped matches any character, so `example\.com` quietly widens to
	 * `exampleXcom` -- a broader rule than the admin wrote.
	 */
	public function test_an_escaped_pattern_survives_intact(): void {
		$this->assertTrue( $this->pattern_matches( '/@example\.com$/', 'buyer@example.com' ) );
		$this->assertFalse( $this->pattern_matches( '/@example\.com$/', 'buyer@exampleXcom' ) );
	}

	/**
	 * What a doubly-slashed save produces. `\\.` is a literal backslash
	 * followed by any character, so the pattern stops matching the address it
	 * was written for -- and nothing reports an error, because it is still a
	 * valid pattern.
	 */
	public function test_a_slashed_pattern_stops_matching_and_says_nothing(): void {
		$intact  = '/@example\.com$/';
		$slashed = addslashes( $intact );

		$this->assertNotSame( $intact, $slashed, 'this test is meaningless if the slashes change nothing' );

		$this->assertTrue( $this->pattern_matches( $intact, 'buyer@example.com' ) );
		$this->assertFalse(
			$this->pattern_matches( $slashed, 'buyer@example.com' ),
			'a slashed pattern silently stops matching -- which is why the save path has to unslash'
		);
	}

	/**
	 * Slashes accumulate. Two saves is worse than one, and neither raises.
	 */
	public function test_slashes_accumulate_across_saves(): void {
		$pattern = '/@example\.com$/';

		$once  = addslashes( $pattern );
		$twice = addslashes( $once );

		$this->assertNotSame( $once, $twice );
		$this->assertFalse( $this->pattern_matches( $twice, 'buyer@example.com' ) );
	}

	/**
	 * Unslashing gets the pattern back. This is what save_meta() does before
	 * handing the value to the sanitizer.
	 */
	public function test_unslashing_restores_the_pattern(): void {
		$pattern = '/@example\.com$/';

		$this->assertSame( $pattern, stripslashes( addslashes( $pattern ) ) );
		$this->assertTrue( $this->pattern_matches( stripslashes( addslashes( $pattern ) ), 'buyer@example.com' ) );
	}

	/**
	 * A pattern an admin typed wrong must not take the checkout down with it.
	 * preg_match returns false and emits a warning; the comparator has to
	 * answer "no match" without the warning reaching the customer.
	 */
	public function test_a_malformed_pattern_fails_closed_without_raising(): void {
		$this->assertFalse( $this->pattern_matches( '/unterminated', 'anything' ) );
		$this->assertFalse( $this->pattern_matches( '', 'anything' ) );
		$this->assertFalse( $this->pattern_matches( 'no-delimiters', 'anything' ) );
	}
}
