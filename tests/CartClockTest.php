<?php
/**
 * The cart clock.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Helpers\CartClock;
use PHPUnit\Framework\TestCase;

/**
 * Class CartClockTest
 */
final class CartClockTest extends TestCase {

	/**
	 * A start the host knows is believed, and no start is no answer.
	 *
	 * Nought would read as a checkout finished the instant it began, which
	 * is the bot the rule is looking for.
	 */
	public function test_seconds_since_start(): void {
		$this->assertNull( CartClock::seconds_since_start( [] ), 'With no shop and no argument there is nothing to count from.' );
		$this->assertNull( CartClock::seconds_since_start( [ 'cart_started_at' => 0 ] ) );

		$seconds = CartClock::seconds_since_start( [ 'cart_started_at' => time() - 90 ] );

		$this->assertGreaterThanOrEqual( 90, $seconds );
		$this->assertLessThan( 95, $seconds );
		$this->assertSame( 0, CartClock::seconds_since_start( [ 'cart_started_at' => time() + 60 ] ), 'A start in the future is now, not negative.' );
	}
}
