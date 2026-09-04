<?php
/**
 * The WooCommerce condition definitions.
 *
 * A condition is a data structure pointing at a callback, and nothing in PHP
 * checks that the callback names a method that exists until the day it runs --
 * which, for a fraud rule, is on somebody's checkout. These tests call every
 * one of them.
 *
 * WooCommerce is deliberately absent here. That is the interesting case: the
 * conditions still have to answer, because a rule set is evaluated in contexts
 * WooCommerce has not booted -- REST, cron, an admin screen -- and a helper
 * that raises there takes the request down with it.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Conditions\WooCommerce\Conditions;
use ArrayPress\Conditions\Operators;
use PHPUnit\Framework\TestCase;

/**
 * Class WooCommerceRegistryTest
 */
final class WooCommerceRegistryTest extends TestCase {

	/**
	 * Every WooCommerce condition, keyed by name.
	 *
	 * @return array<string, array>
	 */
	private function conditions(): array {
		return Conditions::get_all();
	}

	public function test_the_integration_registers_conditions(): void {
		$this->assertNotEmpty( $this->conditions() );
	}

	/**
	 * Names are the storage key for a saved rule. A collision means two
	 * conditions silently share one saved value, and the loser is whichever
	 * one array_merge happened to overwrite.
	 */
	public function test_every_condition_is_namespaced_and_unique(): void {
		$names = array_keys( $this->conditions() );

		$this->assertSame(
			count( $names ),
			count( array_unique( $names ) ),
			'condition names must be unique'
		);

		foreach ( $names as $name ) {
			$this->assertStringStartsWith(
				'wc_',
				$name,
				"$name must carry the wc_ prefix so it cannot collide with a core or EDD condition"
			);
		}
	}

	/**
	 * The rule editor reads all four of these to render a row. A condition
	 * missing one renders as a blank line the admin cannot configure.
	 */
	public function test_every_condition_carries_what_the_editor_needs(): void {
		foreach ( $this->conditions() as $name => $config ) {
			$this->assertArrayHasKey( 'label', $config, "$name needs a label" );
			$this->assertNotSame( '', $config['label'], "$name needs a non-empty label" );
			$this->assertArrayHasKey( 'group', $config, "$name needs a group" );
			$this->assertArrayHasKey( 'type', $config, "$name needs a type" );
			$this->assertArrayHasKey( 'description', $config, "$name needs a description" );
		}
	}

	/**
	 * A type the operator resolver does not know falls through to text
	 * operators, which quietly turns a numeric threshold into a string
	 * comparison -- "9" would then read as greater than "100".
	 */
	public function test_every_type_resolves_to_operators(): void {
		$known = [
			'number',
			'number_unit',
			'text',
			'text_unit',
			'boolean',
			'date',
			'time',
			'ip',
			'email',
			'tags',
			'select',
			'post',
			'term',
			'ajax',
			'user',
		];

		foreach ( $this->conditions() as $name => $config ) {
			$this->assertContains( $config['type'], $known, "$name uses an unknown type" );

			$operators = Operators::for_type( $config['type'], ! empty( $config['multiple'] ) );

			$this->assertNotEmpty( $operators, "$name resolves to no operators" );
		}
	}

	/**
	 * required_args is what the matcher checks before evaluating a rule. A
	 * string where an array belongs makes that check pass on the wrong shape.
	 */
	public function test_required_args_is_always_a_list(): void {
		foreach ( $this->conditions() as $name => $config ) {
			$this->assertArrayHasKey( 'required_args', $config, "$name needs required_args" );
			$this->assertIsArray( $config['required_args'], "$name must declare required_args as an array" );
		}
	}

	/**
	 * Conditions naming a product or an order cannot be evaluated without one,
	 * and the matcher relies on required_args to skip them rather than
	 * comparing against a zero it mistook for real data.
	 */
	public function test_object_conditions_declare_the_object_they_need(): void {
		foreach ( $this->conditions() as $name => $config ) {
			if ( str_starts_with( $name, 'wc_product_' ) ) {
				$this->assertContains( 'product_id', $config['required_args'], "$name must require a product_id" );
			}

			if ( str_starts_with( $name, 'wc_order_' ) ) {
				$this->assertContains( 'order_id', $config['required_args'], "$name must require an order_id" );
			}
		}
	}

	/**
	 * The regression this file is really for.
	 *
	 * Each compare_value is a closure over a helper method. Rename or drop that
	 * method and nothing complains until the closure runs. Calling every one of
	 * them here turns a silent break into a failing test.
	 *
	 * Called with two arguments because that is what Matcher::get_compare_value()
	 * does -- the args and the value the admin configured. A closure declaring
	 * only one still accepts the call; one declaring two and never being handed
	 * the second would raise on the first real evaluation.
	 */
	public function test_every_compare_value_runs_without_woocommerce(): void {
		$this->assertFalse(
			class_exists( 'WooCommerce' ),
			'this test only means something while WooCommerce is absent'
		);

		foreach ( $this->conditions() as $name => $config ) {
			$this->assertArrayHasKey( 'compare_value', $config, "$name needs a compare_value" );
			$this->assertIsCallable( $config['compare_value'], "$name must declare compare_value as a callable" );

			$value = ( $config['compare_value'] )( [], null );

			$this->assertNotInstanceOf( \Throwable::class, $value );
			$this->assertTrue(
				is_scalar( $value ) || is_array( $value ) || null === $value,
				"$name returned a value the comparator cannot handle"
			);
		}
	}

	/**
	 * A helper that reported a plausible-looking figure with no store behind it
	 * would let a threshold rule fire on nothing at all.
	 *
	 * Settings are excluded: a store setting legitimately has a non-zero
	 * default, and WooCommerce's low-stock threshold is 2 whether or not a
	 * store exists.
	 */
	public function test_numeric_conditions_report_zero_with_no_store(): void {
		$settings = __( 'Store: Settings', 'arraypress' );

		// "Days since last order" answers PHP_INT_MAX when there has never been
		// one. Zero would read as "ordered today", so "less than 7 days" would
		// match every first-time buyer -- the opposite of what the rule says.
		$sentinels = [ 'wc_customer_days_since_order' ];

		foreach ( $this->conditions() as $name => $config ) {
			if ( 'number' !== $config['type'] && 'number_unit' !== $config['type'] ) {
				continue;
			}

			if ( ! empty( $config['required_args'] ) || $settings === $config['group'] ) {
				continue;
			}

			if ( in_array( $name, $sentinels, true ) ) {
				$this->assertSame( PHP_INT_MAX, ( $config['compare_value'] )( [], null ), "$name should answer with the sentinel" );
				continue;
			}

			$this->assertEquals( 0, ( $config['compare_value'] )( [], null ), "$name should report 0 with no store" );
		}
	}

	/**
	 * An AJAX field with no callback renders a search box that returns nothing,
	 * which looks like "no results" rather than a broken field.
	 */
	public function test_ajax_conditions_declare_a_search_callback(): void {
		foreach ( $this->conditions() as $name => $config ) {
			if ( 'ajax' !== $config['type'] ) {
				continue;
			}

			$this->assertArrayHasKey( 'ajax', $config, "$name needs an ajax callback" );
			$this->assertIsCallable( $config['ajax'], "$name must declare ajax as a callable" );
			$this->assertIsArray( ( $config['ajax'] )( 'test', null ), "$name must return an array of options" );
		}
	}

	/**
	 * A term field with no taxonomy searches nothing.
	 */
	public function test_term_conditions_name_a_taxonomy(): void {
		foreach ( $this->conditions() as $name => $config ) {
			if ( 'term' !== $config['type'] ) {
				continue;
			}

			$this->assertArrayHasKey( 'taxonomy', $config, "$name needs a taxonomy" );
			// Brands became a core taxonomy in WooCommerce 9.6.
			$this->assertContains(
				$config['taxonomy'],
				[ 'product_cat', 'product_tag', 'product_brand' ],
				"$name names a taxonomy WooCommerce does not register"
			);
		}
	}

	/**
	 * Categories are the obvious place to reach for the wrong taxonomy --
	 * "category" is a real WordPress taxonomy, just not the products one.
	 */
	public function test_categories_use_the_product_taxonomy(): void {
		$conditions = $this->conditions();

		$this->assertSame( 'product_cat', $conditions['wc_product_categories']['taxonomy'] );
		$this->assertSame( 'product_tag', $conditions['wc_product_tags']['taxonomy'] );
		$this->assertSame( 'product_cat', $conditions['wc_cart_categories']['taxonomy'] );
		$this->assertSame( 'product_cat', $conditions['wc_order_categories']['taxonomy'] );
	}
}
