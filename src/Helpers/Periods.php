<?php
/**
 * Time Periods Utility
 *
 * Provides standardized time period units for condition fields.
 *
 * @package     ArrayPress\Conditions
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

	/**
	 * Get the seconds multiplier for a unit.
	 *
	 * @param string $unit The time unit.
	 *
	 * @return int Seconds in the unit.
	 */
	public static function get_multiplier( string $unit ): int {
		return match ( $unit ) {
			'minute' => MINUTE_IN_SECONDS,
			'hour' => HOUR_IN_SECONDS,
			'day' => DAY_IN_SECONDS,
			'week' => WEEK_IN_SECONDS,
			'month' => MONTH_IN_SECONDS,
			'year' => YEAR_IN_SECONDS,
			default => DAY_IN_SECONDS,
		};
	}

	/**
	 * Convert unit and amount to seconds.
	 *
	 * @param string $unit   The time unit.
	 * @param int    $amount The number of units.
	 *
	 * @return int Number of seconds.
	 */
	public static function to_seconds( string $unit, int $amount ): int {
		return absint( $amount ) * self::get_multiplier( $unit );
	}

	/**
	 * Convert seconds to a specific unit.
	 *
	 * @param int    $seconds The number of seconds.
	 * @param string $unit    The target unit.
	 *
	 * @return int The value in the specified unit (floored).
	 */
	public static function from_seconds( int $seconds, string $unit ): int {
		return (int) floor( $seconds / self::get_multiplier( $unit ) );
	}

	/**
	 * Calculate age from a date string in specified units.
	 *
	 * @param string $date_string The date string (any format strtotime accepts).
	 * @param string $unit        The unit to return.
	 *
	 * @return int The age in the specified unit, or 0 if invalid date.
	 */
	public static function get_age( string $date_string, string $unit = 'day' ): int {
		if ( empty( $date_string ) ) {
			return 0;
		}

		$timestamp = strtotime( $date_string );

		if ( $timestamp === false ) {
			return 0;
		}

		return self::get_age_from_timestamp( $timestamp, $unit );
	}

	/**
	 * Calculate age from a timestamp in specified units.
	 *
	 * @param int    $timestamp The Unix timestamp.
	 * @param string $unit      The unit to return.
	 *
	 * @return int The age in the specified unit.
	 */
	public static function get_age_from_timestamp( int $timestamp, string $unit = 'day' ): int {
		$now  = current_time( 'timestamp' );
		$diff = $now - $timestamp;

		if ( $diff < 0 ) {
			return 0;
		}

		return self::from_seconds( $diff, $unit );
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