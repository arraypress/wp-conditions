<?php
/**
 * What the cart helper reports.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\WooCommerce\Cart;
use PHPUnit\Framework\TestCase;
use WC_Product;
use WPC_Stub_Date;

/**
 * Class WooCommerceCartTest
 */
final class WooCommerceCartTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		wpc_stub_reset();
	}

	protected function tearDown(): void {
		wpc_stub_reset();

		parent::tearDown();
	}

	/**
	 * Build a cart line.
	 *
	 * @param array $product  Product data.
	 * @param int   $quantity Line quantity.
	 *
	 * @return array
	 */
	private function line( array $product, int $quantity = 1 ): array {
		return [
			'product_id'   => $product['id'] ?? 0,
			'variation_id' => $product['variation_id'] ?? 0,
			'quantity'     => $quantity,
			'data'         => new WC_Product( $product ),
		];
	}

	/**
	 * Rules are evaluated on requests WooCommerce never initialises a cart for
	 * -- cron, most REST routes. Raising there would take the request with it.
	 */
	public function test_an_absent_cart_reports_zero_rather_than_raising(): void {
		$this->assertNull( Cart::get() );
		$this->assertSame( 0.0, Cart::get_total() );
		$this->assertSame( 0.0, Cart::get_subtotal() );
		$this->assertSame( 0.0, Cart::get_discount_total() );
		$this->assertSame( 0.0, Cart::get_weight() );
		$this->assertSame( 0, Cart::get_quantity() );
		$this->assertSame( 0, Cart::get_item_count() );
		$this->assertSame( [], Cart::get_items() );
		$this->assertSame( [], Cart::get_product_ids() );
		$this->assertSame( [], Cart::get_coupons() );
		$this->assertFalse( Cart::needs_shipping() );
	}

	public function test_money_is_read_from_the_cart(): void {
		wpc_stub_cart( [
			'total'          => 132.00,
			'subtotal'       => 120.00,
			'discount_total' => 12.00,
			'total_tax'      => 20.00,
			'shipping_total' => 4.95,
			'fee_total'      => 2.50,
		] );

		$this->assertSame( 132.00, Cart::get_total() );
		$this->assertSame( 120.00, Cart::get_subtotal() );
		$this->assertSame( 12.00, Cart::get_discount_total() );
		$this->assertSame( 20.00, Cart::get_tax_total() );
		$this->assertSame( 4.95, Cart::get_shipping_total() );
		$this->assertSame( 2.50, Cart::get_fee_total() );
	}

	/**
	 * A percentage is what makes a discount rule portable across price points.
	 * Reading it off the total rather than the subtotal would move the figure
	 * every time tax or shipping changed.
	 */
	public function test_discount_percentage_is_taken_against_the_subtotal(): void {
		wpc_stub_cart( [
			'subtotal'       => 100.00,
			'discount_total' => 90.00,
			'total'          => 10.00,
		] );

		$this->assertSame( 90.0, Cart::get_discount_percentage() );
	}

	/**
	 * An empty cart has no subtotal to divide by, and a division by zero here
	 * would be raised on a page as ordinary as an empty basket.
	 */
	public function test_discount_percentage_on_an_empty_cart_is_zero(): void {
		wpc_stub_cart( [ 'subtotal' => 0.0, 'discount_total' => 0.0 ] );

		$this->assertSame( 0.0, Cart::get_discount_percentage() );
	}

	/**
	 * Line count and quantity answer different questions: fifty of one item is
	 * a bulk-buy, fifty different items is something else.
	 */
	public function test_line_count_and_quantity_are_distinct(): void {
		wpc_stub_cart( [
			'cart'                 => [
				$this->line( [ 'id' => 10 ], 50 ),
				$this->line( [ 'id' => 11 ], 1 ),
			],
			'cart_contents_count'  => 51,
		] );

		$this->assertSame( 2, Cart::get_item_count(), 'two lines' );
		$this->assertSame( 51, Cart::get_quantity(), 'fifty-one units' );
		$this->assertSame( 50, Cart::get_max_line_quantity() );
	}

	public function test_product_ids_are_deduplicated(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10 ] ),
				$this->line( [ 'id' => 10 ] ),
				$this->line( [ 'id' => 11 ] ),
			],
		] );

		$this->assertSame( [ 10, 11 ], Cart::get_product_ids() );
	}

	public function test_variation_ids_skip_lines_that_have_none(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'variation_id' => 0 ] ),
				$this->line( [ 'id' => 11, 'variation_id' => 55 ] ),
			],
		] );

		$this->assertSame( [ 55 ], Cart::get_variation_ids() );
	}

	/**
	 * All-virtual is an all-or-nothing question. One physical item in the cart
	 * means an address is collected, and a rule keyed on "no shipping address"
	 * must not fire.
	 */
	public function test_a_cart_is_only_virtual_when_every_line_is(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'virtual' => true ] ),
				$this->line( [ 'id' => 11, 'virtual' => true ] ),
			],
		] );

		$this->assertTrue( Cart::is_virtual() );

		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'virtual' => true ] ),
				$this->line( [ 'id' => 11, 'virtual' => false ] ),
			],
		] );

		$this->assertFalse( Cart::is_virtual(), 'one physical line is enough to make the cart physical' );
	}

	/**
	 * An empty cart is not virtual. Answering true would make a virtual-only
	 * rule match every visitor who has not added anything yet.
	 */
	public function test_an_empty_cart_is_not_virtual(): void {
		wpc_stub_cart( [ 'cart' => [] ] );

		$this->assertFalse( Cart::is_virtual() );
		$this->assertFalse( Cart::is_downloadable() );
	}

	public function test_a_cart_is_only_downloadable_when_every_line_is(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'downloadable' => true ] ),
				$this->line( [ 'id' => 11, 'downloadable' => true ] ),
			],
		] );

		$this->assertTrue( Cart::is_downloadable() );

		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'downloadable' => true ] ),
				$this->line( [ 'id' => 11, 'downloadable' => false ] ),
			],
		] );

		$this->assertFalse( Cart::is_downloadable() );
	}

	public function test_product_types_are_collected_from_the_lines(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'type' => 'simple' ] ),
				$this->line( [ 'id' => 11, 'type' => 'variable' ] ),
				$this->line( [ 'id' => 12, 'type' => 'simple' ] ),
			],
		] );

		$this->assertSame( [ 'simple', 'variable' ], Cart::get_product_types() );
	}

	/** ---------------------------------------------------------------------
	 * Fees and shipping
	 * -------------------------------------------------------------------- */

	public function test_shipping_and_fee_tax_are_reported_separately(): void {
		wpc_stub_cart( [
			'shipping_total' => 4.95,
			'shipping_tax'   => 0.99,
			'fee_total'      => 5.00,
			'fee_tax'        => 1.00,
		] );

		$this->assertSame( 4.95, Cart::get_shipping_total() );
		$this->assertSame( 0.99, Cart::get_shipping_tax() );
		$this->assertSame( 1.00, Cart::get_fee_tax() );
	}

	/**
	 * The net figure is what a "free shipping over X" rule means; the gross is
	 * what the customer is charged. Conflating them moves every threshold by
	 * the tax rate.
	 */
	public function test_shipping_inclusive_of_tax_adds_the_two(): void {
		wpc_stub_cart( [ 'shipping_total' => 4.95, 'shipping_tax' => 0.99 ] );

		$this->assertSame( 5.94, Cart::get_shipping_total_inc_tax() );
	}

	public function test_shipping_percentage_is_taken_against_the_subtotal(): void {
		wpc_stub_cart( [ 'subtotal' => 10.00, 'shipping_total' => 15.00 ] );

		$this->assertSame( 150.0, Cart::get_shipping_percentage(), 'delivery can legitimately exceed the goods' );
	}

	public function test_fees_are_counted(): void {
		wpc_stub_cart( [ 'fees' => [ (object) [ 'name' => 'Handling' ], (object) [ 'name' => 'Gift wrap' ] ] ] );

		$this->assertSame( 2, Cart::get_fee_count() );
	}

	/** ---------------------------------------------------------------------
	 * Size
	 * -------------------------------------------------------------------- */

	/**
	 * Volume has to count quantities: two of the same box take twice the space,
	 * and a rule about van capacity is asking about the total.
	 */
	public function test_volume_counts_quantities(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'length' => 10.0, 'width' => 10.0, 'height' => 10.0 ], 2 ),
			],
		] );

		$this->assertSame( 2000.0, Cart::get_volume() );
	}

	/**
	 * One product with a missing dimension must not collapse the whole figure.
	 * A full pallet does not become empty because somebody left a height blank.
	 */
	public function test_a_line_missing_a_dimension_is_skipped_not_zeroed(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'length' => 10.0, 'width' => 10.0, 'height' => 10.0 ] ),
				$this->line( [ 'id' => 11, 'length' => 50.0, 'width' => 50.0, 'height' => 0.0 ] ),
			],
		] );

		$this->assertSame( 1000.0, Cart::get_volume() );
	}

	/**
	 * Oversize is priced on one item's longest side, not on the total, so this
	 * takes the maximum rather than summing.
	 */
	public function test_the_longest_dimension_is_the_maximum_across_the_cart(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'length' => 30.0, 'width' => 20.0, 'height' => 10.0 ] ),
				$this->line( [ 'id' => 11, 'length' => 15.0, 'width' => 120.0, 'height' => 5.0 ] ),
			],
		] );

		$this->assertSame( 120.0, Cart::get_max_dimension() );
	}

	public function test_the_heaviest_item_is_reported_per_unit(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'weight' => 2.0 ], 10 ),
				$this->line( [ 'id' => 11, 'weight' => 8.0 ], 1 ),
			],
		] );

		$this->assertSame( 8.0, Cart::get_max_weight(), 'ten light items do not make a heavy one' );
	}

	/** ---------------------------------------------------------------------
	 * Age
	 * -------------------------------------------------------------------- */

	public function test_product_age_is_averaged_across_the_cart(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'date_created' => new WPC_Stub_Date( time() - ( 10 * DAY_IN_SECONDS ) ) ] ),
				$this->line( [ 'id' => 11, 'date_created' => new WPC_Stub_Date( time() - ( 20 * DAY_IN_SECONDS ) ) ] ),
			],
		] );

		$this->assertEqualsWithDelta( 15.0, Cart::get_average_product_age(), 0.01 );
		$this->assertEqualsWithDelta( 10.0, Cart::get_newest_product_age(), 0.01 );
		$this->assertEqualsWithDelta( 20.0, Cart::get_oldest_product_age(), 0.01 );
	}

	/**
	 * An empty cart has no mean to take, and dividing by the count would raise
	 * on a page as ordinary as an empty basket.
	 */
	public function test_an_empty_cart_has_no_product_age(): void {
		wpc_stub_cart( [ 'cart' => [] ] );

		$this->assertSame( 0.0, Cart::get_average_product_age() );
		$this->assertSame( 0.0, Cart::get_oldest_product_age() );
	}

	/** ---------------------------------------------------------------------
	 * Shipping and tax classes
	 * -------------------------------------------------------------------- */

	/**
	 * A product with no shipping class must contribute nothing rather than an
	 * empty string -- otherwise an "is none of" rule is satisfied by its blank.
	 */
	public function test_products_without_a_shipping_class_contribute_nothing(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'shipping_class' => 'bulky' ] ),
				$this->line( [ 'id' => 11, 'shipping_class' => '' ] ),
			],
		] );

		$this->assertSame( [ 'bulky' ], Cart::get_shipping_classes() );
	}

	/**
	 * WooCommerce stores the standard tax class as a blank slug. Left blank it
	 * cannot be named in a rule at all, so it is reported as "standard".
	 */
	public function test_the_standard_tax_class_is_named_rather_than_blank(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'tax_class' => '' ] ),
				$this->line( [ 'id' => 11, 'tax_class' => 'reduced-rate' ] ),
			],
		] );

		$this->assertSame( [ 'standard', 'reduced-rate' ], Cart::get_tax_classes() );
	}

	public function test_a_taxable_item_is_found_anywhere_in_the_cart(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'tax_status' => 'none' ] ),
				$this->line( [ 'id' => 11, 'tax_status' => 'taxable' ] ),
			],
		] );

		$this->assertTrue( Cart::has_taxable_item() );

		wpc_stub_cart( [ 'cart' => [ $this->line( [ 'id' => 10, 'tax_status' => 'none' ] ) ] ] );

		$this->assertFalse( Cart::has_taxable_item() );
	}

	public function test_backordered_and_on_sale_items_are_found_anywhere_in_the_cart(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'stock_status' => 'instock', 'on_sale' => false ] ),
				$this->line( [ 'id' => 11, 'stock_status' => 'onbackorder', 'on_sale' => true ] ),
			],
		] );

		$this->assertTrue( Cart::has_backordered_item() );
		$this->assertTrue( Cart::has_on_sale_item() );
	}

	/** ---------------------------------------------------------------------
	 * Variations
	 * -------------------------------------------------------------------- */

	public function test_variation_attributes_are_read_from_the_chosen_values(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10 ] ) + [ 'variation' => [ 'attribute_pa_size' => 'large' ] ],
				$this->line( [ 'id' => 11 ] ) + [ 'variation' => [ 'attribute_pa_size' => 'small' ] ],
			],
		] );

		$this->assertSame( [ 'large', 'small' ], Cart::get_variation_attribute( 'pa_size' ) );
		$this->assertSame( [ 'large', 'small' ], Cart::get_variation_attribute( 'attribute_pa_size' ) );
	}

	/**
	 * The rule carries "attribute:value" in one field, and only the name half
	 * selects which attribute to read. Passing the whole string through as the
	 * name would look up an attribute that does not exist.
	 */
	public function test_a_rule_selects_the_attribute_by_its_name_half(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10 ] ) + [ 'variation' => [ 'attribute_pa_size' => 'large' ] ],
			],
		] );

		$this->assertSame( [ 'large' ], Cart::get_variation_attribute_from_rule( 'pa_size:large' ) );
		$this->assertSame( [ 'large' ], Cart::get_variation_attribute_from_rule( ' pa_size : large ' ) );
		$this->assertSame( [], Cart::get_variation_attribute_from_rule( 'pa_size' ), 'a rule with no colon selects nothing' );
		$this->assertSame( [], Cart::get_variation_attribute_from_rule( null ) );
	}

	public function test_variations_are_counted_separately_from_products(): void {
		wpc_stub_cart( [
			'cart' => [
				$this->line( [ 'id' => 10, 'variation_id' => 0 ] ),
				$this->line( [ 'id' => 11, 'variation_id' => 55 ] ),
			],
		] );

		$this->assertSame( 1, Cart::get_variation_count() );
		$this->assertTrue( Cart::has_variations() );
	}

	public function test_coupons_are_read_from_the_cart(): void {
		wpc_stub_cart( [ 'applied_coupons' => [ 'SAVE10', 'FREESHIP' ] ] );

		$this->assertSame( [ 'SAVE10', 'FREESHIP' ], Cart::get_coupons() );
		$this->assertSame( 2, Cart::get_coupon_count() );
	}
}
