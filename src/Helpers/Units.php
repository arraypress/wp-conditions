<?php
/**
 * Units Helper
 *
 * Provides standardized unit definitions for number_unit fields.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class Units
 *
 * Standardized unit definitions for number_unit condition fields.
 */
class Units {

	/**
	 * Get weight units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function weight(): array {
		return [
			[ 'value' => 'g', 'label' => __( 'Grams (g)', 'arraypress' ) ],
			[ 'value' => 'kg', 'label' => __( 'Kilograms (kg)', 'arraypress' ) ],
			[ 'value' => 'oz', 'label' => __( 'Ounces (oz)', 'arraypress' ) ],
			[ 'value' => 'lb', 'label' => __( 'Pounds (lb)', 'arraypress' ) ],
		];
	}

	/**
	 * Get length/dimension units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function length(): array {
		return [
			[ 'value' => 'mm', 'label' => __( 'Millimeters (mm)', 'arraypress' ) ],
			[ 'value' => 'cm', 'label' => __( 'Centimeters (cm)', 'arraypress' ) ],
			[ 'value' => 'm', 'label' => __( 'Meters (m)', 'arraypress' ) ],
			[ 'value' => 'in', 'label' => __( 'Inches (in)', 'arraypress' ) ],
			[ 'value' => 'ft', 'label' => __( 'Feet (ft)', 'arraypress' ) ],
		];
	}

	/**
	 * Get distance units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function distance(): array {
		return [
			[ 'value' => 'm', 'label' => __( 'Meters (m)', 'arraypress' ) ],
			[ 'value' => 'km', 'label' => __( 'Kilometers (km)', 'arraypress' ) ],
			[ 'value' => 'mi', 'label' => __( 'Miles (mi)', 'arraypress' ) ],
		];
	}

	/**
	 * Get volume units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function volume(): array {
		return [
			[ 'value' => 'ml', 'label' => __( 'Milliliters (ml)', 'arraypress' ) ],
			[ 'value' => 'l', 'label' => __( 'Liters (L)', 'arraypress' ) ],
			[ 'value' => 'fl_oz', 'label' => __( 'Fluid Ounces (fl oz)', 'arraypress' ) ],
			[ 'value' => 'gal', 'label' => __( 'Gallons (gal)', 'arraypress' ) ],
		];
	}

	/**
	 * Get data storage units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function data(): array {
		return [
			[ 'value' => 'kb', 'label' => __( 'Kilobytes (KB)', 'arraypress' ) ],
			[ 'value' => 'mb', 'label' => __( 'Megabytes (MB)', 'arraypress' ) ],
			[ 'value' => 'gb', 'label' => __( 'Gigabytes (GB)', 'arraypress' ) ],
			[ 'value' => 'tb', 'label' => __( 'Terabytes (TB)', 'arraypress' ) ],
		];
	}

	/**
	 * Get percentage units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function percentage(): array {
		return [
			[ 'value' => 'percent', 'label' => __( 'Percent (%)', 'arraypress' ) ],
		];
	}

	/**
	 * Get quantity/count units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function quantity(): array {
		return [
			[ 'value' => 'unit', 'label' => __( 'Units', 'arraypress' ) ],
			[ 'value' => 'piece', 'label' => __( 'Pieces', 'arraypress' ) ],
			[ 'value' => 'item', 'label' => __( 'Items', 'arraypress' ) ],
		];
	}

}