<?php
/**
 * What the order helper reports.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\WooCommerce\Order;
use PHPUnit\Framework\TestCase;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;
use WPC_Stub_Date;

/**
 * Class WooCommerceOrderTest
 */
final class WooCommerceOrderTest extends TestCase {

	/**
	 * The args a condition would be evaluated with.
	 */
	private const ARGS = [ 'order_id' => 100 ];

	protected function setUp(): void {
		parent::setUp();

		wpc_stub_reset();
	}

	protected function tearDown(): void {
		wpc_stub_reset();

		parent::tearDown();
	}

	/**
	 * Put order 100 in place.
	 *
	 * @param array $data Order data.
	 */
	private function order( array $data ): void {
		wpc_stub_order( 100, $data );
	}

	/**
	 * Rules are re-evaluated after the fact -- from a queued job, from the
	 * admin -- and by then the order may be gone.
	 */
	public function test_a_missing_order_reports_zero_rather_than_raising(): void {
		$this->assertNull( Order::get( self::ARGS ) );
		$this->assertSame( 0.0, Order::get_total( self::ARGS ) );
		$this->assertSame( '', Order::get_status( self::ARGS ) );
		$this->assertSame( '', Order::get_email( self::ARGS ) );
		$this->assertSame( [], Order::get_product_ids( self::ARGS ) );
		$this->assertSame( [], Order::get_coupons( self::ARGS ) );
		$this->assertSame( '', Order::get_date_created( self::ARGS ) );
		$this->assertSame( 0, Order::get_age( self::ARGS ) );
		$this->assertFalse( Order::has_country_mismatch( self::ARGS ) );
	}

	/**
	 * A condition with no order_id has nothing to resolve, and guessing at the
	 * current order would evaluate a rule against a stranger's purchase.
	 */
	public function test_no_order_id_resolves_to_no_order(): void {
		$this->order( [ 'total' => 100.0 ] );

		$this->assertNull( Order::get( [] ) );
		$this->assertSame( 0, Order::get_id( [] ) );
	}

	public function test_money_is_read_from_the_order(): void {
		$this->order( [
			'total'          => 132.00,
			'subtotal'       => 120.00,
			'total_tax'      => 20.00,
			'total_discount' => 12.00,
			'shipping_total' => 4.95,
			'total_refunded' => 30.00,
			'currency'       => 'GBP',
		] );

		$this->assertSame( 132.00, Order::get_total( self::ARGS ) );
		$this->assertSame( 120.00, Order::get_subtotal( self::ARGS ) );
		$this->assertSame( 20.00, Order::get_tax( self::ARGS ) );
		$this->assertSame( 12.00, Order::get_discount( self::ARGS ) );
		$this->assertSame( 4.95, Order::get_shipping_total( self::ARGS ) );
		$this->assertSame( 30.00, Order::get_refunded_total( self::ARGS ) );
		$this->assertSame( 'GBP', Order::get_currency( self::ARGS ) );
	}

	public function test_discount_percentage_is_taken_against_the_subtotal(): void {
		$this->order( [ 'subtotal' => 200.00, 'total_discount' => 50.00 ] );

		$this->assertSame( 25.0, Order::get_discount_percentage( self::ARGS ) );
	}

	public function test_a_zero_subtotal_yields_no_discount_percentage(): void {
		$this->order( [ 'subtotal' => 0.0, 'total_discount' => 0.0 ] );

		$this->assertSame( 0.0, Order::get_discount_percentage( self::ARGS ) );
	}

	/**
	 * WooCommerce stores statuses prefixed and returns them bare. A rule saved
	 * against "wc-completed" would never match what get_status() hands back.
	 */
	public function test_status_is_compared_without_the_storage_prefix(): void {
		$this->order( [ 'status' => 'completed' ] );

		$this->assertSame( 'completed', Order::get_status( self::ARGS ) );
	}

	public function test_a_guest_order_is_one_with_no_user_behind_it(): void {
		$this->order( [ 'customer_id' => 0 ] );

		$this->assertTrue( Order::is_guest( self::ARGS ) );
		$this->assertSame( 0, Order::get_customer_id( self::ARGS ) );

		$this->order( [ 'customer_id' => 5 ] );

		$this->assertFalse( Order::is_guest( self::ARGS ) );
		$this->assertSame( 5, Order::get_customer_id( self::ARGS ) );
	}

	public function test_product_ids_come_from_the_line_items(): void {
		$this->order( [
			'items' => [
				new WC_Order_Item_Product( [ 'product_id' => 10, 'variation_id' => 0 ] ),
				new WC_Order_Item_Product( [ 'product_id' => 10, 'variation_id' => 0 ] ),
				new WC_Order_Item_Product( [ 'product_id' => 11, 'variation_id' => 55 ] ),
			],
		] );

		$this->assertSame( [ 10, 11 ], Order::get_product_ids( self::ARGS ) );
		$this->assertSame( [ 55 ], Order::get_variation_ids( self::ARGS ) );
		$this->assertSame( 2, Order::get_unique_product_count( self::ARGS ) );
	}

	public function test_shipping_methods_come_from_the_shipping_lines(): void {
		$this->order( [
			'shipping_methods' => [
				new WC_Order_Item_Shipping( [ 'method_id' => 'flat_rate' ] ),
				new WC_Order_Item_Shipping( [ 'method_id' => 'flat_rate' ] ),
				new WC_Order_Item_Shipping( [ 'method_id' => 'local_pickup' ] ),
			],
		] );

		$this->assertSame( [ 'flat_rate', 'local_pickup' ], Order::get_shipping_methods( self::ARGS ) );
	}

	public function test_a_country_mismatch_is_two_different_countries(): void {
		$this->order( [ 'billing_country' => 'GB', 'shipping_country' => 'NG' ] );

		$this->assertTrue( Order::has_country_mismatch( self::ARGS ) );
	}

	public function test_matching_countries_are_not_a_mismatch(): void {
		$this->order( [ 'billing_country' => 'GB', 'shipping_country' => 'GB' ] );

		$this->assertFalse( Order::has_country_mismatch( self::ARGS ) );
	}

	/**
	 * A virtual order never collects a shipping country. Counting the blank as
	 * a mismatch would fire the rule on every download sale on the store.
	 */
	public function test_a_missing_shipping_country_is_not_a_mismatch(): void {
		$this->order( [ 'billing_country' => 'GB', 'shipping_country' => '' ] );

		$this->assertFalse( Order::has_country_mismatch( self::ARGS ) );
	}

	public function test_dates_are_formatted_for_comparison(): void {
		$this->order( [
			'date_created' => new WPC_Stub_Date( mktime( 12, 0, 0, 6, 15, 2026 ) ),
			'date_paid'    => new WPC_Stub_Date( mktime( 12, 0, 0, 6, 16, 2026 ) ),
		] );

		$this->assertSame( '2026-06-15', Order::get_date_created( self::ARGS ) );
		$this->assertSame( '2026-06-16', Order::get_date_paid( self::ARGS ) );
	}

	/**
	 * An unpaid order has no paid date. Falling back to the created date would
	 * make "paid within the last hour" true for an order that was never paid.
	 */
	public function test_an_unpaid_order_has_no_paid_date(): void {
		$this->order( [ 'date_created' => new WPC_Stub_Date( time() ), 'date_paid' => null ] );

		$this->assertSame( '', Order::get_date_paid( self::ARGS ) );
	}

	public function test_age_counts_back_from_the_created_date(): void {
		$this->order( [ 'date_created' => new WPC_Stub_Date( time() - ( 3 * DAY_IN_SECONDS ) ) ] );

		$this->assertSame( 3, Order::get_age( self::ARGS + [ '_unit' => 'day' ] ) );
	}
}
