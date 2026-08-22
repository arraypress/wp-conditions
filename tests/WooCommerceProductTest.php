<?php
/**
 * What the product helper reports.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Integrations\WooCommerce\Product;
use PHPUnit\Framework\TestCase;
use WPC_Stub_Date;

/**
 * Class WooCommerceProductTest
 */
final class WooCommerceProductTest extends TestCase {

	/**
	 * The args a condition would be evaluated with.
	 */
	private const ARGS = [ 'product_id' => 42 ];

	protected function setUp(): void {
		parent::setUp();

		wpc_stub_reset();
	}

	protected function tearDown(): void {
		wpc_stub_reset();

		parent::tearDown();
	}

	/**
	 * Put product 42 in place.
	 *
	 * @param array $data Product data.
	 */
	private function product( array $data ): void {
		wpc_stub_product( 42, $data );
	}

	/**
	 * A product deleted between the rule being saved and the rule running is
	 * ordinary, not exceptional.
	 */
	public function test_a_missing_product_reports_zero_rather_than_raising(): void {
		$this->assertNull( Product::get( self::ARGS ) );
		$this->assertSame( '', Product::get_type( self::ARGS ) );
		$this->assertSame( '', Product::get_sku( self::ARGS ) );
		$this->assertSame( 0.0, Product::get_price( self::ARGS ) );
		$this->assertSame( 0.0, Product::get_weight( self::ARGS ) );
		$this->assertSame( 0, Product::get_stock_quantity( self::ARGS ) );
		$this->assertSame( [], Product::get_categories( self::ARGS ) );
		$this->assertFalse( Product::is_on_sale( self::ARGS ) );
		$this->assertFalse( Product::is_virtual( self::ARGS ) );
	}

	/**
	 * A condition can be handed either key. Reading only one of them would make
	 * the product conditions silently inert in half the contexts.
	 */
	public function test_the_product_resolves_from_either_id_key(): void {
		$this->product( [ 'sku' => 'TSHIRT-1' ] );

		$this->assertSame( 'TSHIRT-1', Product::get_sku( [ 'product_id' => 42 ] ) );
		$this->assertSame( 'TSHIRT-1', Product::get_sku( [ 'post_id' => 42 ] ) );
	}

	public function test_pricing_is_read_from_the_product(): void {
		$this->product( [
			'price'         => 29.99,
			'regular_price' => 49.99,
			'sale_price'    => 29.99,
			'on_sale'       => true,
		] );

		$this->assertSame( 29.99, Product::get_price( self::ARGS ) );
		$this->assertSame( 49.99, Product::get_regular_price( self::ARGS ) );
		$this->assertSame( 29.99, Product::get_sale_price( self::ARGS ) );
		$this->assertTrue( Product::is_on_sale( self::ARGS ) );
	}

	/**
	 * The discount is how far the active price sits below the regular one. Read
	 * the other way round it would report 167% on a 40%-off product.
	 */
	public function test_discount_percentage_measures_the_drop_from_regular(): void {
		$this->product( [ 'price' => 30.00, 'regular_price' => 50.00 ] );

		$this->assertSame( 40.0, Product::get_discount_percentage( self::ARGS ) );
	}

	public function test_a_product_not_on_sale_has_no_discount(): void {
		$this->product( [ 'price' => 50.00, 'regular_price' => 50.00 ] );

		$this->assertSame( 0.0, Product::get_discount_percentage( self::ARGS ) );
	}

	/**
	 * A price above the regular one is a data mistake, not a negative discount.
	 * Reporting -20% would make a "discount over 50%" rule read as satisfied on
	 * the wrong side of zero if the operator were ever inverted.
	 */
	public function test_a_price_above_regular_reports_no_discount(): void {
		$this->product( [ 'price' => 60.00, 'regular_price' => 50.00 ] );

		$this->assertSame( 0.0, Product::get_discount_percentage( self::ARGS ) );
	}

	public function test_a_product_with_no_regular_price_has_no_discount(): void {
		$this->product( [ 'price' => 10.00, 'regular_price' => 0.0 ] );

		$this->assertSame( 0.0, Product::get_discount_percentage( self::ARGS ) );
	}

	public function test_a_free_product_is_recognised(): void {
		$this->product( [ 'price' => 0.0 ] );

		$this->assertTrue( Product::is_free( self::ARGS ) );

		$this->product( [ 'price' => 0.01 ] );

		$this->assertFalse( Product::is_free( self::ARGS ) );
	}

	/**
	 * Carriers state oversize limits as a single longest side, so that is the
	 * figure a shipping rule needs -- not the length, whichever side it is on.
	 */
	public function test_the_longest_dimension_is_whichever_side_is_longest(): void {
		$this->product( [ 'length' => 30.0, 'width' => 120.0, 'height' => 10.0 ] );

		$this->assertSame( 120.0, Product::get_longest_dimension( self::ARGS ) );
	}

	public function test_volume_multiplies_the_three_dimensions(): void {
		$this->product( [ 'length' => 30.0, 'width' => 20.0, 'height' => 10.0 ] );

		$this->assertSame( 6000.0, Product::get_volume( self::ARGS ) );
	}

	/**
	 * A product missing a dimension has no volume worth comparing. Treating the
	 * blank as zero and multiplying would give 0 anyway; treating it as 1 -- the
	 * tempting shortcut -- would report a plausible figure that is wrong.
	 */
	public function test_a_product_missing_a_dimension_has_no_volume(): void {
		$this->product( [ 'length' => 30.0, 'width' => 20.0, 'height' => 0.0 ] );

		$this->assertSame( 0.0, Product::get_volume( self::ARGS ) );

		$this->product( [ 'length' => 30.0 ] );

		$this->assertSame( 0.0, Product::get_volume( self::ARGS ) );
	}

	public function test_stock_is_read_from_the_product(): void {
		$this->product( [
			'stock_quantity' => 7,
			'stock_status'   => 'instock',
			'in_stock'       => true,
			'managing_stock' => true,
		] );

		$this->assertSame( 7, Product::get_stock_quantity( self::ARGS ) );
		$this->assertSame( 'instock', Product::get_stock_status( self::ARGS ) );
		$this->assertTrue( Product::is_in_stock( self::ARGS ) );
		$this->assertTrue( Product::is_managing_stock( self::ARGS ) );
		$this->assertFalse( Product::is_on_backorder( self::ARGS ) );
	}

	/**
	 * Backorder is a stock status, not a separate flag -- reading it off
	 * is_in_stock() would answer the wrong question, since a backordered
	 * product is still purchasable.
	 */
	public function test_backorder_is_read_from_the_stock_status(): void {
		$this->product( [ 'stock_status' => 'onbackorder', 'in_stock' => true ] );

		$this->assertTrue( Product::is_on_backorder( self::ARGS ) );
		$this->assertTrue( Product::is_in_stock( self::ARGS ) );
	}

	public function test_taxonomies_are_read_as_term_ids(): void {
		$this->product( [ 'category_ids' => [ 3, 7 ], 'tag_ids' => [ 11 ] ] );

		$this->assertSame( [ 3, 7 ], Product::get_categories( self::ARGS ) );
		$this->assertSame( [ 11 ], Product::get_tags( self::ARGS ) );
	}

	/**
	 * WooCommerce hands attribute values back as one comma-joined string, and a
	 * rule listing sizes has to compare against them one at a time.
	 */
	public function test_attribute_values_are_split_and_trimmed(): void {
		$this->product( [ 'attribute' => 'Small, Medium , Large' ] );

		$this->assertSame( [ 'Small', 'Medium', 'Large' ], Product::get_attribute( self::ARGS, 'pa_size' ) );
	}

	public function test_an_unset_attribute_is_an_empty_list(): void {
		$this->product( [ 'attribute' => '' ] );

		$this->assertSame( [], Product::get_attribute( self::ARGS, 'pa_size' ) );
		$this->assertSame( [], Product::get_attribute( self::ARGS, '' ) );
	}

	/** ---------------------------------------------------------------------
	 * Attributes
	 * -------------------------------------------------------------------- */

	/**
	 * A rule carries "attribute:value" in one field, and only the name half
	 * says which attribute to read. Passing the whole string through as the
	 * name looks up an attribute nothing has.
	 */
	public function test_a_rule_selects_the_attribute_by_its_name_half(): void {
		$this->product( [ 'attribute' => 'Large' ] );

		$this->assertSame( [ 'Large' ], Product::get_attribute_from_rule( self::ARGS, 'pa_size:Large' ) );
		$this->assertSame( [ 'Large' ], Product::get_attribute_from_rule( self::ARGS, ' pa_size : Large ' ) );
	}

	public function test_a_rule_with_no_attribute_name_selects_nothing(): void {
		$this->product( [ 'attribute' => 'Large' ] );

		$this->assertSame( [], Product::get_attribute_from_rule( self::ARGS, 'Large' ), 'no colon, no attribute' );
		$this->assertSame( [], Product::get_attribute_from_rule( self::ARGS, null ) );
		$this->assertSame( [], Product::get_attribute_from_rule( self::ARGS, [ 'pa_size' => 'Large' ] ) );
	}

	public function test_attribute_names_are_read_from_the_product(): void {
		$this->product( [ 'attributes' => [ 'pa_size' => null, 'pa_colour' => null ] ] );

		$this->assertSame( [ 'pa_size', 'pa_colour' ], Product::get_attribute_names( self::ARGS ) );
		$this->assertSame( 2, Product::get_attribute_count( self::ARGS ) );
	}

	/** ---------------------------------------------------------------------
	 * Type and variations
	 * -------------------------------------------------------------------- */

	/**
	 * A simple product has no variations. Reporting 1 -- treating it as its own
	 * single variation -- would make "has more than 0 variations" true for the
	 * entire catalogue.
	 */
	public function test_a_non_variable_product_has_no_variations(): void {
		$this->product( [ 'type' => 'simple', 'children' => [ 1, 2, 3 ] ] );

		$this->assertSame( 0, Product::get_variation_count( self::ARGS ) );
		$this->assertFalse( Product::is_variation( self::ARGS ) );
	}

	public function test_a_variation_is_recognised_by_its_type(): void {
		$this->product( [ 'type' => 'variation', 'parent_id' => 7 ] );

		$this->assertTrue( Product::is_variation( self::ARGS ) );
		$this->assertSame( 7, Product::get_parent_id( self::ARGS ) );
	}

	/** ---------------------------------------------------------------------
	 * Backorder policy
	 * -------------------------------------------------------------------- */

	/**
	 * The policy and the status answer different questions: a product set to
	 * allow backorders is only "on backorder" once its stock has actually run
	 * out, so a rule about policy must not read the status.
	 */
	public function test_backorder_policy_is_distinct_from_backorder_status(): void {
		$this->product( [ 'backorders' => 'notify', 'stock_status' => 'instock' ] );

		$this->assertSame( 'notify', Product::get_backorders( self::ARGS ) );
		$this->assertTrue( Product::is_backorders_allowed( self::ARGS ) );
		$this->assertFalse( Product::is_on_backorder( self::ARGS ), 'policy allows it; stock has not run out' );
	}

	public function test_backorders_set_to_no_are_not_allowed(): void {
		$this->product( [ 'backorders' => 'no' ] );

		$this->assertFalse( Product::is_backorders_allowed( self::ARGS ) );

		$this->product( [ 'backorders' => 'yes' ] );

		$this->assertTrue( Product::is_backorders_allowed( self::ARGS ) );
	}

	/** ---------------------------------------------------------------------
	 * Tax and shipping classes
	 * -------------------------------------------------------------------- */

	/**
	 * On a product the standard class really is blank -- it is only the cart
	 * and order helpers that normalise it, because a rule there has to be able
	 * to name it alongside the others.
	 */
	public function test_a_product_reports_the_standard_tax_class_as_blank(): void {
		$this->product( [ 'tax_class' => '', 'tax_status' => 'taxable' ] );

		$this->assertSame( '', Product::get_tax_class( self::ARGS ) );
		$this->assertSame( 'taxable', Product::get_tax_status( self::ARGS ) );
	}

	public function test_the_shipping_class_is_available_as_both_slug_and_id(): void {
		$this->product( [ 'shipping_class' => 'bulky', 'shipping_class_id' => 15 ] );

		$this->assertSame( 'bulky', Product::get_shipping_class( self::ARGS ) );
		$this->assertSame( 15, Product::get_shipping_class_id( self::ARGS ) );
	}

	/** ---------------------------------------------------------------------
	 * Catalogue
	 * -------------------------------------------------------------------- */

	/**
	 * Published, in stock, and still not purchasable is a real state -- a
	 * variable product with no price set. A rule keyed on stock alone misses
	 * it.
	 */
	public function test_purchasable_is_separate_from_in_stock(): void {
		$this->product( [ 'in_stock' => true, 'purchasable' => false ] );

		$this->assertTrue( Product::is_in_stock( self::ARGS ) );
		$this->assertFalse( Product::is_purchasable( self::ARGS ) );
	}

	public function test_catalog_visibility_is_read_from_the_product(): void {
		$this->product( [ 'catalog_visibility' => 'hidden' ] );

		$this->assertSame( 'hidden', Product::get_catalog_visibility( self::ARGS ) );
	}

	public function test_a_scheduled_sale_reports_its_window(): void {
		$this->product( [
			'date_on_sale_from' => new WPC_Stub_Date( mktime( 0, 0, 0, 6, 1, 2026 ) ),
			'date_on_sale_to'   => new WPC_Stub_Date( mktime( 0, 0, 0, 6, 30, 2026 ) ),
		] );

		$this->assertSame( '2026-06-01', Product::get_sale_date_from( self::ARGS ) );
		$this->assertSame( '2026-06-30', Product::get_sale_date_to( self::ARGS ) );
	}

	/**
	 * A sale with no schedule runs until somebody ends it. Reporting today's
	 * date would make "sale ends before X" true for every unscheduled sale.
	 */
	public function test_an_unscheduled_sale_has_no_window(): void {
		$this->product( [ 'on_sale' => true, 'date_on_sale_from' => null, 'date_on_sale_to' => null ] );

		$this->assertSame( '', Product::get_sale_date_from( self::ARGS ) );
		$this->assertSame( '', Product::get_sale_date_to( self::ARGS ) );
	}

	/**
	 * WooCommerce stores "unlimited downloads" as -1, not as 0 or null. Reading
	 * it as 0 would make "download limit under 3" match every unlimited
	 * product.
	 */
	public function test_an_unlimited_download_limit_stays_negative_one(): void {
		$this->product( [ 'download_limit' => -1 ] );

		$this->assertSame( -1, Product::get_download_limit( self::ARGS ) );
	}
}
