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

}