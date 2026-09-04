<?php
/**
 * The EDD checkout form, as a shopper may submit it.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\EDD\Checkout;
use PHPUnit\Framework\TestCase;

/**
 * Class EddCheckoutTest
 */
final class EddCheckoutTest extends TestCase {

	/**
	 * A posted value is read, unslashed and clean.
	 */
	public function test_a_posted_value_is_read(): void {
		$args = [
			'posted' => [
				'edd_email' => 'buyer@example.com',
				'billing_country' => 'GB',
				'edd_first' => "O\\'Brien",
			],
		];

		$this->assertSame( 'buyer@example.com', Checkout::get_email( $args ) );
		$this->assertSame( 'GB', Checkout::get_country( $args ) );
		$this->assertSame( "O'Brien", Checkout::get_first_name( $args ) );
		$this->assertSame( '', Checkout::get_city( $args ) );
	}

	/**
	 * A shopper submitting an array where a string belongs does not take the
	 * rule set down.
	 *
	 * `edd_email[]=x` made every helper here throw a TypeError -- a way to
	 * knock the fraud rules out from the checkout page.
	 */
	public function test_an_array_where_a_string_belongs_is_no_value(): void {
		$args = [
			'posted' => [
				'edd_email' => [ 'x' ],
				'billing_country' => [ 'US' ],
				'edd-gateway' => [ 'stripe' ],
				'edd-discount' => [ 'SAVE' ],
			],
		];

		$this->assertSame( '', Checkout::get_email( $args ) );
		$this->assertSame( '', Checkout::get_country( $args ) );
		$this->assertSame( '', Checkout::get_discount_code( $args ) );
		$this->assertFalse( Checkout::has_discount_code( $args ) );
	}
}
