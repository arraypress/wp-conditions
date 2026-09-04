<?php
/**
 * Coupon email restrictions.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\WooCommerce\Cart;
use PHPUnit\Framework\TestCase;

/**
 * Class CouponEmailTest
 */
final class CouponEmailTest extends TestCase {

	/**
	 * A restriction is satisfied by an exact address or its domain wildcard,
	 * whatever the case, and by nothing else.
	 */
	public function test_a_restriction_is_matched_exactly_or_by_domain(): void {
		$this->assertTrue( Cart::email_matches_restrictions( [ 'Jane@Example.com' ], [ 'jane@example.com' ] ) );
		$this->assertTrue( Cart::email_matches_restrictions( [ 'jane@example.com' ], [ '*@example.com' ] ) );
		$this->assertTrue( Cart::email_matches_restrictions( [ 'other@x.test', 'jane@example.com' ], [ 'jane@example.com' ] ), 'Any of the customer\'s emails may match.' );

		$this->assertFalse( Cart::email_matches_restrictions( [ 'john@example.com' ], [ 'jane@example.com' ] ) );
		$this->assertFalse( Cart::email_matches_restrictions( [ 'jane@example.com.evil.test' ], [ '*@example.com' ] ), 'The wildcard is the whole domain.' );
		$this->assertFalse( Cart::email_matches_restrictions( [ '' ], [ 'jane@example.com' ] ) );
		$this->assertFalse( Cart::email_matches_restrictions( [ 'jane@example.com' ], [ '' ] ), 'An empty restriction restricts to nobody.' );
	}
}
