<?php
/**
 * The save path.
 *
 * Everything an admin builds in the rule editor passes through here on its
 * way to post meta, and a sanitizer that drops, rewrites or crashes on a
 * legitimate rule is a rule that silently never fires.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Admin\Sanitizer;
use ArrayPress\Conditions\Admin\TypeSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Class SanitizerTest
 */
final class SanitizerTest extends TestCase {

	/**
	 * Condition configurations the rules below refer to.
	 *
	 * @return array<string, array>
	 */
	private function configs(): array {
		return [
			'email'      => [ 'type' => 'text' ],
			'total'      => [
				'type' => 'number',
				'min' => 0,
			],
			'orderTotal' => [ 'type' => 'number' ],
			'window'     => [
				'type' => 'text_unit',
				'units' => [ [ 'value' => 'days' ], [ 'value' => 'hours' ] ],
			],
			'size'       => [
				'type' => 'number_unit',
				'units' => [ [ 'value' => 'MB' ], [ 'value' => 'GB' ] ],
			],
			'note'       => [ 'type' => 'text' ],
			'radius'     => [
				'type' => 'number',
				'operators' => [ 'within' => 'Within' ],
			],
		];
	}

	/**
	 * Sanitize one group of rules, as the form would submit it.
	 *
	 * @param array $rules Rules, keyed by id.
	 *
	 * @return array The sanitized rules of the one group, or [] if the group was dropped.
	 */
	private function save( array $rules ): array {
		$saved = Sanitizer::sanitize_conditions( [ 'g1' => [ 'rules' => $rules ] ], $this->configs() );

		return $saved[0]['rules'] ?? [];
	}

	/**
	 * An all-digit id does not crash the save.
	 *
	 * PHP stores an all-digit array key as an int, and under strict types
	 * that was a TypeError inside save_post: a white screen, and nothing
	 * saved, for any id the editor happened to build from digits alone.
	 */
	public function test_numeric_ids_are_accepted(): void {
		$saved = Sanitizer::sanitize_conditions(
			[
				123456789 => [
					'rules' => [
						987654321 => [
							'condition' => 'email',
							'operator' => '==',
							'value' => 'a@b.c',
						],
					],
				],
			],
			$this->configs()
		);

		$this->assertSame( '123456789', $saved[0]['id'] );
		$this->assertSame( '987654321', $saved[0]['rules'][0]['id'] );
	}

	/**
	 * An operator that is not a string is refused, not fatal.
	 */
	public function test_a_malformed_operator_drops_the_rule(): void {
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'email',
				'operator' => [ '==' ],
				'value' => 'x',
			],
		] ) );
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'email',
				'operator' => 'sideways',
				'value' => 'x',
			],
		] ) );
	}

	/**
	 * A regular expression is stored as written.
	 *
	 * sanitize_text_field() rewrites `<`, strips anything shaped like a tag
	 * and collapses whitespace, so a lookbehind, a named group or a literal
	 * `<script` came back as a different pattern that quietly matched
	 * something else.
	 */
	public function test_a_regex_survives_the_save_intact(): void {
		foreach ( [ '/(?<=@)example\.com$/', '/^(?<user>[a-z]+)@/', '/<script/i', '/^a  b$/' ] as $pattern ) {
			$saved = $this->save( [
				'r1' => [
					'condition' => 'email',
					'operator' => 'regex',
					'value' => $pattern,
				],
			] );

			$this->assertSame( $pattern, $saved[0]['value'] ?? null, "The pattern $pattern was rewritten." );
		}

		// The control: the same string through the text path is mangled.
		$this->assertNotSame( '/(?<=@)x/', TypeSanitizer::text( '/(?<=@)x/' ) );
	}

	/**
	 * A regular expression that does not compile is not stored.
	 */
	public function test_a_malformed_regex_is_dropped(): void {
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'email',
				'operator' => 'regex',
				'value' => '/unterminated',
			],
		] ) );
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'email',
				'operator' => 'regex',
				'value' => 'no delimiters',
			],
		] ) );
	}

	/**
	 * The emptiness operators carry no value and can still be saved.
	 */
	public function test_emptiness_rules_survive_without_a_value(): void {
		$saved = $this->save( [
			'r1' => [
				'condition' => 'note',
				'operator' => 'empty',
				'value' => '',
			],
		] );

		$this->assertSame( 'empty', $saved[0]['operator'] ?? null );
	}

	/**
	 * A condition's own operators are accepted.
	 *
	 * A condition that declares `operators` renders them in the editor, and
	 * a save that then refused them threw the rule away.
	 */
	public function test_a_conditions_own_operator_is_accepted(): void {
		$saved = $this->save( [
			'r1' => [
				'condition' => 'radius',
				'operator' => 'within',
				'value' => '5',
			],
		] );

		$this->assertSame( 'within', $saved[0]['operator'] ?? null );
	}

	/**
	 * A number that is not a number is no rule.
	 *
	 * It used to become the minimum, or nought, so "total > abc" was saved
	 * and evaluated as "total > 0".
	 */
	public function test_a_non_numeric_number_drops_the_rule(): void {
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'total',
				'operator' => '>',
				'value' => 'abc',
			],
		] ) );
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'total',
				'operator' => '>',
				'value' => '1,000',
			],
		] ) );

		$saved = $this->save( [
			'r1' => [
				'condition' => 'total',
				'operator' => '>',
				'value' => '0',
			],
		] );

		$this->assertSame( 0, $saved[0]['value'] ?? null, 'Zero is a number.' );
	}

	/**
	 * A registered condition id keeps its case.
	 *
	 * sanitize_key() lowercases, so a developer's `orderTotal` was saved as
	 * `ordertotal`, matched no configuration, and never resolved at runtime.
	 */
	public function test_a_registered_condition_id_keeps_its_case(): void {
		$saved = $this->save( [
			'r1' => [
				'condition' => 'orderTotal',
				'operator' => '>',
				'value' => '10',
			],
		] );

		$this->assertSame( 'orderTotal', $saved[0]['condition'] ?? null );
	}

	/**
	 * A unit keeps its case, and is still checked against the allow-list.
	 */
	public function test_a_unit_keeps_its_case(): void {
		$saved = $this->save( [
			'r1' => [
				'condition' => 'size',
				'operator' => '>',
				'value' => [
					'number' => '2',
					'unit' => 'GB',
				],
			],
		] );

		$this->assertSame( 'GB', $saved[0]['value']['unit'] ?? null );

		$saved = $this->save( [
			'r1' => [
				'condition' => 'size',
				'operator' => '>',
				'value' => [
					'number' => '2',
					'unit' => 'TB',
				],
			],
		] );

		$this->assertSame( 'MB', $saved[0]['value']['unit'] ?? null, 'An unknown unit falls back to the first.' );
	}

	/**
	 * Two text_unit rules with swapped halves are two rules.
	 *
	 * The duplicate check sorted the halves, so { days, hours } and
	 * { hours, days } collided and the second was dropped.
	 */
	public function test_swapped_text_unit_rules_are_not_duplicates(): void {
		$saved = $this->save( [
			'r1' => [
				'condition' => 'window',
				'operator' => '==',
				'value' => [
					'text' => 'days',
					'unit' => 'hours',
				],
			],
			'r2' => [
				'condition' => 'window',
				'operator' => '==',
				'value' => [
					'text' => 'hours',
					'unit' => 'days',
				],
			],
		] );

		$this->assertCount( 2, $saved );
	}

	/**
	 * A value that is not text where text is expected is dropped, not "Array".
	 */
	public function test_an_array_where_text_is_expected_is_dropped(): void {
		$this->assertSame( [], $this->save( [
			'r1' => [
				'condition' => 'note',
				'operator' => '==',
				'value' => [ 'a', 'b' ],
			],
		] ) );
		$this->assertSame( '', TypeSanitizer::text( [ 'a' ] ) );
	}

	/**
	 * The tag "0" is a tag.
	 */
	public function test_a_zero_tag_is_kept(): void {
		$this->assertSame( [ '0', 'a' ], TypeSanitizer::tags( [ '0', '', 'a' ] ) );
	}
}
