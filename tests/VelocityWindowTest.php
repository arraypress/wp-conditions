<?php
/**
 * The window a velocity rule counts within.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Helpers\Velocity;
use PHPUnit\Framework\TestCase;

/**
 * Class VelocityWindowTest
 */
final class VelocityWindowTest extends TestCase {

	/**
	 * The rule's number is the threshold, never the window.
	 *
	 * It used to be both, so "≥ 3 in [minute]" counted the last three
	 * minutes. The unit alone is the window: a preset is so many of it and a
	 * bare unit is one.
	 */
	public function test_the_window_comes_from_the_unit_alone(): void {
		$this->assertSame( [ 10, 'minute' ], Velocity::resolve_window( [
			'_unit' => '10_minutes',
			'_number' => 3,
		] ) );
		$this->assertSame( [ 6, 'hour' ], Velocity::resolve_window( [
			'_unit' => '6_hours',
			'_number' => 50,
		] ) );
		$this->assertSame( [ 1, 'hour' ], Velocity::resolve_window( [
			'_unit' => 'hour',
			'_number' => 5,
		] ) );
		$this->assertSame( [ 1, 'day' ], Velocity::resolve_window( [ '_unit' => 'day' ] ) );
		$this->assertSame( [ 1, 'hour' ], Velocity::resolve_window( [] ) );
		$this->assertSame( 'INTERVAL 10 MINUTE', Velocity::to_interval( ...Velocity::resolve_window( [ '_unit' => '10_minutes' ] ) ) );
	}

	/**
	 * A card count comes from the host, and no host means no answer.
	 *
	 * A count of nought for a card nobody could look up would satisfy every
	 * "fewer than" rule.
	 */
	public function test_a_card_count_comes_from_the_host(): void {
		$this->assertSame( 4, Velocity::count_orders_by_card_fingerprint( [
			'card_fingerprint' => 'abc',
			'velocity_orders_by_card_fingerprint' => 4,
		] ) );
		$this->assertNull( Velocity::count_orders_by_card_fingerprint( [ 'card_fingerprint' => 'abc' ] ) );
		$this->assertNull( Velocity::count_orders_by_card_fingerprint( [ 'velocity_orders_by_card_fingerprint' => 4 ] ), 'No fingerprint, no count.' );
	}
}
