<?php
/**
 * Time Periods Utility
 *
 * Provides standardized time period units for condition fields.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions;

/**
 * Class Periods
 *
 * Standardized time period definitions for number_unit fields.
 */
class Periods {

	/**
	 * Get time period units (includes hours).
	 *
	 * Used for conditions like "Orders in last X hours/days/weeks".
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_units(): array {
		return [
			[ 'value' => 'hour', 'label' => __( 'Hour(s)', 'arraypress' ) ],
			[ 'value' => 'day', 'label' => __( 'Day(s)', 'arraypress' ) ],
			[ 'value' => 'week', 'label' => __( 'Week(s)', 'arraypress' ) ],
			[ 'value' => 'month', 'label' => __( 'Month(s)', 'arraypress' ) ],
			[ 'value' => 'year', 'label' => __( 'Year(s)', 'arraypress' ) ],
		];
	}

	/**
	 * Get age units (excludes hours).
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
	 * Convert unit and amount to seconds.
	 *
	 * @param string $unit   The time unit (hour, day, week, month, year).
	 * @param int    $amount The number of units.
	 *
	 * @return int Number of seconds.
	 */
	public static function to_seconds( string $unit, int $amount ): int {
		$multipliers = [
			'hour'  => HOUR_IN_SECONDS,
			'day'   => DAY_IN_SECONDS,
			'week'  => WEEK_IN_SECONDS,
			'month' => MONTH_IN_SECONDS,
			'year'  => YEAR_IN_SECONDS,
		];

		return absint( $amount ) * ( $multipliers[ $unit ] ?? DAY_IN_SECONDS );
	}

	/**
	 * Get a date range from now back to X units ago.
	 *
	 * @param string $unit   The time unit.
	 * @param int    $amount The number of units.
	 *
	 * @return array{start: string, end: string} MySQL formatted dates.
	 */
	public static function get_date_range( string $unit, int $amount ): array {
		$seconds = self::to_seconds( $unit, $amount );

		return [
			'start' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $seconds ),
			'end'   => current_time( 'mysql' ),
		];
	}

}