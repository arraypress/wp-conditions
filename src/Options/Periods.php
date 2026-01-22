<?php
/**
 * Time Periods Options Helper
 *
 * Provides standardized time period units and options for condition fields.
 *
 * @package     ArrayPress\Conditions\Options
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Options;

/**
 * Class Periods
 *
 * Standardized time period definitions for number_unit fields and select options.
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

	/**
	 * Get month options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_months(): array {
		return [
			[ 'value' => '1', 'label' => __( 'January', 'arraypress' ) ],
			[ 'value' => '2', 'label' => __( 'February', 'arraypress' ) ],
			[ 'value' => '3', 'label' => __( 'March', 'arraypress' ) ],
			[ 'value' => '4', 'label' => __( 'April', 'arraypress' ) ],
			[ 'value' => '5', 'label' => __( 'May', 'arraypress' ) ],
			[ 'value' => '6', 'label' => __( 'June', 'arraypress' ) ],
			[ 'value' => '7', 'label' => __( 'July', 'arraypress' ) ],
			[ 'value' => '8', 'label' => __( 'August', 'arraypress' ) ],
			[ 'value' => '9', 'label' => __( 'September', 'arraypress' ) ],
			[ 'value' => '10', 'label' => __( 'October', 'arraypress' ) ],
			[ 'value' => '11', 'label' => __( 'November', 'arraypress' ) ],
			[ 'value' => '12', 'label' => __( 'December', 'arraypress' ) ],
		];
	}

	/**
	 * Get day of week options.
	 *
	 * Values use ISO-8601 numeric representation (1 = Monday, 7 = Sunday).
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_days_of_week(): array {
		return [
			[ 'value' => '1', 'label' => __( 'Monday', 'arraypress' ) ],
			[ 'value' => '2', 'label' => __( 'Tuesday', 'arraypress' ) ],
			[ 'value' => '3', 'label' => __( 'Wednesday', 'arraypress' ) ],
			[ 'value' => '4', 'label' => __( 'Thursday', 'arraypress' ) ],
			[ 'value' => '5', 'label' => __( 'Friday', 'arraypress' ) ],
			[ 'value' => '6', 'label' => __( 'Saturday', 'arraypress' ) ],
			[ 'value' => '7', 'label' => __( 'Sunday', 'arraypress' ) ],
		];
	}

	/**
	 * Get time of day options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_times_of_day(): array {
		return [
			[ 'value' => 'early_morning', 'label' => __( 'Early Morning (5am - 8am)', 'arraypress' ) ],
			[ 'value' => 'morning', 'label' => __( 'Morning (8am - 12pm)', 'arraypress' ) ],
			[ 'value' => 'afternoon', 'label' => __( 'Afternoon (12pm - 5pm)', 'arraypress' ) ],
			[ 'value' => 'evening', 'label' => __( 'Evening (5pm - 9pm)', 'arraypress' ) ],
			[ 'value' => 'night', 'label' => __( 'Night (9pm - 12am)', 'arraypress' ) ],
			[ 'value' => 'late_night', 'label' => __( 'Late Night (12am - 5am)', 'arraypress' ) ],
		];
	}

	/**
	 * Get quarter options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_quarters(): array {
		return [
			[ 'value' => '1', 'label' => __( 'Q1 (Jan - Mar)', 'arraypress' ) ],
			[ 'value' => '2', 'label' => __( 'Q2 (Apr - Jun)', 'arraypress' ) ],
			[ 'value' => '3', 'label' => __( 'Q3 (Jul - Sep)', 'arraypress' ) ],
			[ 'value' => '4', 'label' => __( 'Q4 (Oct - Dec)', 'arraypress' ) ],
		];
	}

}