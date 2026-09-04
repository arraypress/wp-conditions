<?php
/**
 * The billing-form heuristics.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Helpers\Address;
use PHPUnit\Framework\TestCase;

/**
 * Class AddressTest
 */
final class AddressTest extends TestCase {

	public function test_a_post_office_box_is_recognised(): void {
		foreach ( [ 'PO Box 123', 'P.O. Box 44', 'P O Box 9', 'Post Office Box 5', 'Postfach 12', 'POBox 7', 'Boîte postale 3' ] as $box ) {
			$this->assertTrue( Address::is_po_box( $box ), "$box was not seen as a box." );
		}

		foreach ( [ '12 Boxwood Lane', '4 Postbox Cottages', 'Apartment 3, 9 Main St' ] as $door ) {
			$this->assertFalse( Address::is_po_box( $door ), "$door was seen as a box." );
		}
	}

	public function test_a_street_number_is_a_digit_anywhere(): void {
		$this->assertTrue( Address::has_street_number( '12 High Street' ) );
		$this->assertTrue( Address::has_street_number( 'Flat 4b, Rose Court' ) );
		$this->assertFalse( Address::has_street_number( 'Rose Cottage, High Street' ) );
	}

	public function test_identical_names_are_a_signal(): void {
		$this->assertTrue( Address::names_identical( 'John', 'john' ) );
		$this->assertTrue( Address::names_identical( 'Ann-Marie', 'ann marie' ) );
		$this->assertFalse( Address::names_identical( 'John', 'Smith' ) );
		$this->assertFalse( Address::names_identical( '', '' ), 'Two blanks are not a match.' );
	}

	public function test_a_name_in_the_email_is_recognised(): void {
		$this->assertTrue( Address::name_matches_email( 'Jane', 'Doe', 'jane.doe@example.com' ) );
		$this->assertTrue( Address::name_matches_email( 'Jane', 'Doe', 'jdoe88@example.com' ) );
		$this->assertTrue( Address::name_matches_email( 'Jane', 'Doe', 'doej@example.com' ) );
		$this->assertTrue( Address::name_matches_email( 'José', 'Núñez', 'josé@example.com' ) );
		$this->assertFalse( Address::name_matches_email( 'Jane', 'Doe', 'xk29dj1@example.com' ) );
		$this->assertFalse( Address::name_matches_email( 'Al', 'Wu', 'al.wu@example.com' ), 'Two letters match too much to count.' );
		$this->assertFalse( Address::name_matches_email( 'Jane', 'Doe', '' ) );
	}
}
