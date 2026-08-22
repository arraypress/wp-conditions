<?php
/**
 * The EDD condition definitions.
 *
 * The counterpart to WooCommerceRegistryTest, and it exists for the same
 * reason: a condition is a data structure pointing at a callback, and nothing
 * in PHP checks that the callback names a method that exists until the day it
 * runs -- which, for a fraud rule, is on somebody's checkout.
 *
 * EDD's own functions are stubbed, so what is being tested is the library:
 * that every definition is well formed, that every closure resolves, and that
 * a store with no data produces zeros rather than plausible-looking figures.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Tests;

use ArrayPress\Conditions\Conditions\EDD\Conditions;
use ArrayPress\Conditions\Operators;
use PHPUnit\Framework\TestCase;

/**
 * Class EddRegistryTest
 */
final class EddRegistryTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		wpc_edd_reset();
	}

	protected function tearDown(): void {
		wpc_edd_reset();

		parent::tearDown();
	}

	/**
	 * Every EDD condition, keyed by name.
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
	 * array_merge happened to overwrite.
	 */
	public function test_every_condition_is_namespaced_and_unique(): void {
		$names = array_keys( $this->conditions() );

		$this->assertSame( count( $names ), count( array_unique( $names ) ) );

		foreach ( $names as $name ) {
			$this->assertStringStartsWith(
				'edd_',
				$name,
				"$name must carry the edd_ prefix so it cannot collide with a core or WooCommerce condition"
			);
		}
	}

	/**
	 * Neither integration may claim a name the other already uses. They are
	 * merged into one registry whenever both plugins are active.
	 */
	public function test_edd_names_cannot_collide_with_woocommerce(): void {
		$edd = array_keys( $this->conditions() );
		$wc  = array_keys( \ArrayPress\Conditions\Conditions\WooCommerce\Conditions::get_all() );

		$this->assertSame( [], array_intersect( $edd, $wc ) );
	}

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
			$this->assertNotEmpty(
				Operators::for_type( $config['type'], ! empty( $config['multiple'] ) ),
				"$name resolves to no operators"
			);
		}
	}

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
			if ( str_starts_with( $name, 'edd_product_' ) ) {
				$this->assertContains( 'product_id', $config['required_args'], "$name must require a product_id" );
			}

			if ( str_starts_with( $name, 'edd_order_' ) ) {
				$this->assertContains( 'order_id', $config['required_args'], "$name must require an order_id" );
			}
		}
	}

	/**
	 * The regression this file is really for.
	 *
	 * Each compare_value closes over a helper method. Rename or drop that
	 * method and nothing complains until the closure runs. Calling every one of
	 * them turns a silent break into a failing test.
	 *
	 * Two arguments, because that is what Matcher::get_compare_value() passes.
	 */
	public function test_every_compare_value_runs_against_an_empty_store(): void {
		foreach ( $this->conditions() as $name => $config ) {
			$this->assertArrayHasKey( 'compare_value', $config, "$name needs a compare_value" );
			$this->assertIsCallable( $config['compare_value'], "$name must declare compare_value as a callable" );

			$value = ( $config['compare_value'] )( [], null );

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
	 * default.
	 */
	public function test_numeric_conditions_report_zero_against_an_empty_store(): void {
		$settings = __( 'Store: Settings', 'arraypress' );

		// "Days since last order" answers PHP_INT_MAX when there has never been
		// one. Zero would read as "ordered today", so "less than 7 days" would
		// match every first-time buyer -- the opposite of what the rule says.
		$sentinels = [ 'edd_customer_days_since_order' ];

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

			$this->assertEquals( 0, ( $config['compare_value'] )( [], null ), "$name should report 0 with no store data" );
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
	 * Downloads use their own taxonomies. "category" and "post_tag" are real
	 * WordPress taxonomies, just not the ones EDD products live in.
	 */
	public function test_term_conditions_name_an_edd_taxonomy(): void {
		foreach ( $this->conditions() as $name => $config ) {
			if ( 'term' !== $config['type'] ) {
				continue;
			}

			$this->assertArrayHasKey( 'taxonomy', $config, "$name needs a taxonomy" );
			$this->assertContains(
				$config['taxonomy'],
				[ 'download_category', 'download_tag' ],
				"$name names a taxonomy EDD does not register"
			);
		}
	}

	/**
	 * Every condition offering a fixed choice list has to actually offer one --
	 * an empty select cannot be configured, and the rule silently never fires.
	 */
	public function test_select_conditions_offer_choices(): void {
		foreach ( $this->conditions() as $name => $config ) {
			if ( 'select' !== $config['type'] || ! isset( $config['options'] ) ) {
				continue;
			}

			$options = is_callable( $config['options'] ) ? ( $config['options'] )() : $config['options'];

			$this->assertIsArray( $options, "$name must resolve options to an array" );

			foreach ( $options as $option ) {
				$this->assertArrayHasKey( 'value', $option, "$name has an option with no value" );
				$this->assertArrayHasKey( 'label', $option, "$name has an option with no label" );
			}
		}
	}
}
