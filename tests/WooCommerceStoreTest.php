<?php
/**
 * Store-wide aggregates, date ranges, and the option lists behind the fields.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\WooCommerce\Options;
use ArrayPress\Conditions\Integrations\WooCommerce\Stats;
use ArrayPress\Conditions\Integrations\WooCommerce\Store;
use PHPUnit\Framework\TestCase;

/**
 * Class WooCommerceStoreTest
 */
final class WooCommerceStoreTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		wpc_stub_reset();
	}

	protected function tearDown(): void {
		wpc_stub_reset();

		parent::tearDown();
	}

	/** ---------------------------------------------------------------------
	 * Date ranges
	 * -------------------------------------------------------------------- */

	/**
	 * A range whose end precedes its start matches nothing, and a store rule
	 * that silently matches nothing looks exactly like a store with no orders.
	 */
	public function test_every_range_ends_after_it_starts(): void {
		foreach ( array_column( Options::get_date_ranges(), 'value' ) as $range ) {
			$dates = Stats::get_date_range( $range );

			$this->assertLessThan(
				strtotime( $dates['end'] ),
				strtotime( $dates['start'] ),
				"$range ends before it starts"
			);
		}
	}

	/**
	 * Every preset the editor offers has to be one the resolver knows. An
	 * unknown one falls through to today, which reports a plausible figure for
	 * the wrong period -- the kind of wrong that never looks wrong.
	 */
	public function test_every_offered_range_is_one_the_resolver_knows(): void {
		$today = Stats::get_date_range( 'today' );

		foreach ( array_column( Options::get_date_ranges(), 'value' ) as $range ) {
			if ( 'today' === $range ) {
				continue;
			}

			$this->assertNotSame(
				$today,
				Stats::get_date_range( $range ),
				"$range resolves to today's dates, so the resolver does not know it"
			);
		}
	}

	public function test_today_covers_the_current_moment(): void {
		$dates = Stats::get_date_range( 'today' );
		$now   = (int) current_time( 'timestamp' );

		$this->assertLessThanOrEqual( $now, strtotime( $dates['start'] ) );
		$this->assertGreaterThanOrEqual( $now, strtotime( $dates['end'] ) );
	}

	public function test_yesterday_ends_before_today_begins(): void {
		$yesterday = Stats::get_date_range( 'yesterday' );
		$today     = Stats::get_date_range( 'today' );

		$this->assertLessThan( strtotime( $today['start'] ), strtotime( $yesterday['end'] ) );
	}

	public function test_last_month_ends_before_this_month_begins(): void {
		$last = Stats::get_date_range( 'last_month' );
		$this_month = Stats::get_date_range( 'this_month' );

		$this->assertLessThan( strtotime( $this_month['start'] ), strtotime( $last['end'] ) );
	}

	public function test_a_month_range_starts_on_the_first(): void {
		$this->assertStringEndsWith( '-01 00:00:00', Stats::get_date_range( 'this_month' )['start'] );
		$this->assertStringEndsWith( '-01 00:00:00', Stats::get_date_range( 'last_month' )['start'] );
	}

	public function test_all_time_reaches_back_to_the_epoch(): void {
		$this->assertSame( '1970-01-01 00:00:00', Stats::get_date_range( 'all_time' )['start'] );
	}

	/**
	 * A quarter starts in January, April, July or October -- never anywhere
	 * else. Off-by-one arithmetic on the month number is the usual way this
	 * lands a month early or late.
	 */
	public function test_a_quarter_starts_on_a_quarter_boundary(): void {
		$start = Stats::get_date_range( 'this_quarter' )['start'];

		$this->assertContains( (int) gmdate( 'n', strtotime( $start ) ), [ 1, 4, 7, 10 ] );
		$this->assertStringEndsWith( '-01 00:00:00', $start );
	}

	/** ---------------------------------------------------------------------
	 * Aggregates
	 * -------------------------------------------------------------------- */

	public function test_a_store_with_no_orders_reports_zero(): void {
		$totals = Stats::get_totals( 'this_month' );

		$this->assertSame( 0, $totals['orders'] );
		$this->assertSame( 0.0, $totals['gross'] );
		$this->assertSame( 0.0, Stats::get_earnings( 'this_month' ) );
		$this->assertSame( 0.0, Stats::get_average_order_value( 'this_month' ) );
		$this->assertSame( 0.0, Stats::get_refund_rate( 'this_month' ) );
	}

	public function test_totals_are_summed_across_the_orders(): void {
		wpc_stub_order_results( [
			[ 'total' => 100.0, 'total_tax' => 20.0, 'shipping_total' => 5.0, 'total_discount' => 10.0, 'total_refunded' => 0.0 ],
			[ 'total' => 50.0, 'total_tax' => 10.0, 'shipping_total' => 0.0, 'total_discount' => 0.0, 'total_refunded' => 50.0 ],
		] );

		$totals = Stats::get_totals( 'this_month' );

		$this->assertSame( 2, $totals['orders'] );
		$this->assertSame( 150.0, $totals['gross'] );
		$this->assertSame( 30.0, $totals['tax'] );
		$this->assertSame( 5.0, $totals['shipping'] );
		$this->assertSame( 10.0, $totals['discount'] );
		$this->assertSame( 50.0, $totals['refunded'] );
		$this->assertSame( 1, $totals['refunds'] );
	}

	/**
	 * Net is gross less refunds. Reporting gross as net would let a store that
	 * refunded everything it took still read as a good month.
	 */
	public function test_net_earnings_take_refunds_off_the_gross(): void {
		wpc_stub_order_results( [
			[ 'total' => 100.0, 'total_refunded' => 40.0 ],
		] );

		$this->assertSame( 100.0, Stats::get_earnings( 'this_month' ) );
		$this->assertSame( 60.0, Stats::get_net_earnings( 'this_month' ) );
	}

	public function test_average_order_value_divides_gross_by_count(): void {
		wpc_stub_order_results( [
			[ 'total' => 100.0 ],
			[ 'total' => 50.0 ],
		] );

		$this->assertSame( 75.0, Stats::get_average_order_value( 'this_month' ) );
	}

	/**
	 * The refund rate counts orders carrying a refund, not the money refunded.
	 * A single large refund on a busy month is a different signal from a
	 * quarter of orders coming back.
	 */
	public function test_refund_rate_counts_orders_not_amounts(): void {
		wpc_stub_order_results( [
			[ 'total' => 10.0, 'total_refunded' => 10.0 ],
			[ 'total' => 10.0, 'total_refunded' => 0.0 ],
			[ 'total' => 10.0, 'total_refunded' => 0.0 ],
			[ 'total' => 1000.0, 'total_refunded' => 0.0 ],
		] );

		$this->assertSame( 25.0, Stats::get_refund_rate( 'this_month' ) );
		$this->assertSame( 1, Stats::get_refund_count( 'this_month' ) );
	}

	/**
	 * The period is the unit half of a number_unit field. A rule saved before
	 * the unit list existed has none, and defaulting to nothing would query a
	 * range the resolver does not know.
	 */
	public function test_a_missing_period_falls_back_to_this_month(): void {
		wpc_stub_order_results( [ [ 'total' => 42.0 ] ] );

		$this->assertSame( Stats::get_earnings( 'this_month' ), Store::get_earnings_in_period( [] ) );
		$this->assertSame( Stats::get_earnings( 'this_month' ), Store::get_earnings_in_period( [ '_unit' => '' ] ) );
	}

	/** ---------------------------------------------------------------------
	 * Option lists
	 * -------------------------------------------------------------------- */

	/**
	 * Statuses are stored prefixed and compared bare. Offering the prefixed
	 * form would save rules that can never match.
	 */
	public function test_order_status_options_drop_the_storage_prefix(): void {
		$values = array_column( Options::get_order_statuses(), 'value' );

		$this->assertContains( 'completed', $values );
		$this->assertContains( 'processing', $values );

		foreach ( $values as $value ) {
			$this->assertStringStartsNotWith( 'wc-', $value, 'a stored prefix would never match get_status()' );
		}
	}

	/**
	 * WooCommerce's standard tax class is implicit: it is stored as an empty
	 * slug and never appears in WC_Tax::get_tax_classes(). A list built only
	 * from that call cannot express "products on the standard rate" at all.
	 */
	public function test_the_standard_tax_class_is_added_to_the_list(): void {
		$values = array_column( Options::get_tax_classes(), 'value' );

		$this->assertSame( '', $values[0], 'the standard class is the empty slug, and comes first' );
		$this->assertContains( 'reduced-rate', $values );
		$this->assertContains( 'zero-rate', $values );
	}

	/**
	 * Products store the slug, not the display name. Offering "Reduced rate"
	 * as the value would save a rule that never matches a product.
	 */
	public function test_tax_class_options_carry_slugs_not_names(): void {
		foreach ( Options::get_tax_classes() as $option ) {
			$this->assertSame( sanitize_title( $option['value'] ), $option['value'] );
		}
	}

	public function test_option_lists_are_empty_rather_than_broken_without_woocommerce(): void {
		$this->assertIsArray( Options::get_countries() );
		$this->assertIsArray( Options::get_gateways() );
		$this->assertIsArray( Options::get_shipping_methods() );
		$this->assertIsArray( Options::get_shipping_classes() );
		$this->assertIsArray( Options::get_product_options( 'test', null ) );
	}

	public function test_the_lookup_lists_are_shaped_for_a_select_field(): void {
		foreach ( [ Options::get_product_types(), Options::get_stock_statuses(), Options::get_currencies(), Options::get_date_ranges() ] as $list ) {
			$this->assertNotEmpty( $list );

			foreach ( $list as $option ) {
				$this->assertArrayHasKey( 'value', $option );
				$this->assertArrayHasKey( 'label', $option );
			}
		}
	}

	/** ---------------------------------------------------------------------
	 * Settings
	 * -------------------------------------------------------------------- */

	public function test_store_settings_degrade_to_empty_without_woocommerce(): void {
		$this->assertSame( '', Store::get_currency() );
		$this->assertSame( '', Store::get_base_country() );
		$this->assertSame( '', Store::get_base_state() );
		$this->assertFalse( Store::is_taxes_enabled() );
		$this->assertFalse( Store::is_coupons_enabled() );
		$this->assertFalse( Store::is_guest_checkout_enabled() );
	}
}
