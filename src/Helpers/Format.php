<?php
/**
 * Formatting Helper Functions
 *
 * Provides formatting utilities for condition options.
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
 * Class Format
 *
 * Formatting utilities for conditions.
 */
class Format {

	/**
	 * Format options array for select fields.
	 *
	 * Converts associative arrays to the standard value/label format.
	 *
	 * @param array  $options   The raw options array.
	 * @param string $label_key The key to use for the label (for nested arrays).
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function options( array $options, string $label_key = '' ): array {
		$formatted = [];

		foreach ( $options as $value => $label ) {
			if ( is_array( $label ) && $label_key ) {
				$label = $label[ $label_key ] ?? $value;
			}
			$formatted[] = [
				'value' => (string) $value,
				'label' => (string) $label,
			];
		}

		return $formatted;
	}

	/**
	 * Format a simple key => value array to options.
	 *
	 * @param array $items The items array.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function simple( array $items ): array {
		return self::options( $items );
	}

	/**
	 * Create options from an indexed array (values become both value and label).
	 *
	 * @param array $items The indexed array of values.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function from_values( array $items ): array {
		$options = [];

		foreach ( $items as $item ) {
			$options[] = [
				'value' => (string) $item,
				'label' => (string) $item,
			];
		}

		return $options;
	}

	/**
	 * Create options from objects with specified property names.
	 *
	 * @param array  $objects       Array of objects.
	 * @param string $value_prop    Property name for value.
	 * @param string $label_prop    Property name for label.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function from_objects( array $objects, string $value_prop, string $label_prop ): array {
		$options = [];

		foreach ( $objects as $object ) {
			$options[] = [
				'value' => (string) ( $object->$value_prop ?? '' ),
				'label' => (string) ( $object->$label_prop ?? '' ),
			];
		}

		return $options;
	}

}