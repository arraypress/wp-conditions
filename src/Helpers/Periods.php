<?php
/**
 * Time Periods Utility
 *
 * Provides standardized time period units for condition fields.
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
 * Class Periods
 *
 * Standardized time period definitions for number_unit fields.
 */
class Periods {

	/**
	 * Get time period units (includes minutes and hours).
	 *
	 * Used for conditions like "Orders in last X hours/days/weeks".
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_units(): array {
		return [
			[ 'value' => 'minute', 'label' => __( 'Minute(s)', 'arraypress' ) ],
			[ 'value' => 'hour', 'label' => __( 'Hour(s)', 'arraypress' ) ],
			[ 'value' => 'day', 'label' => __( 'Day(s)', 'arraypress' ) ],
			[ 'value' => 'week', 'label' => __( 'Week(s)', 'arraypress' ) ],
			[ 'value' => 'month', 'label' => __( 'Month(s)', 'arraypress' ) ],
			[ 'value' => 'year', 'label' => __( 'Year(s)', 'arraypress' ) ],
		];
	}

	/**
	 * Get age units (excludes minutes/hours).
	 *
	 * Used for conditions like "Account age > X days".
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_age_units(): array {
		return [
			[ 'value' => 'day', 'label' => __( 'Day(s)', 'arraypress' ) ],
			[ 'value' => 'week', 'label' => __( 'Week(s)', 'arraypress' ) ],
			[ 'value' => 'month', 'label' => __( 'Month(s)', 'arraypress' ) ],
			[ 'value' => 'year', 'label' => __( 'Year(s)', 'arraypress' ) ],
		];
	}

}