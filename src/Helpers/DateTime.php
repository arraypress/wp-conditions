<?php
/**
 * DateTime Helper
 *
 * Provides date and time calculation utilities.
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
 * Class DateTime
 *
 * Date and time calculation utilities for conditions.
 */
class DateTime {

	/**
	 * Get the seconds multiplier for a unit.
	 *
	 * @param string $unit The time unit.
	 *
	 * @return int Seconds in the unit.
	 */
	public static function get_multiplier( string $unit ): int {
		return match ( $unit ) {
			'minute', 'minutes' => MINUTE_IN_SECONDS,
			'hour', 'hours' => HOUR_IN_SECONDS,
			'week', 'weeks' => WEEK_IN_SECONDS,
			'month', 'months' => MONTH_IN_SECONDS,
			'year', 'years' => YEAR_IN_SECONDS,
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

	/**
	 * Get the current time of day period.
	 *
	 * @return string One of: early_morning, morning, afternoon, evening, night, late_night
	 */
	public static function get_time_of_day(): string {
		$hour = (int) current_time( 'G' );

		return match ( true ) {
			$hour >= 5 && $hour < 8 => 'early_morning',
			$hour >= 8 && $hour < 12 => 'morning',
			$hour >= 12 && $hour < 17 => 'afternoon',
			$hour >= 17 && $hour < 21 => 'evening',
			$hour >= 21 => 'night',
			default => 'late_night',
		};
	}

	/**
	 * Check if current time is business hours (Mon-Fri 9am-5pm).
	 *
	 * @return bool
	 */
	public static function is_business_hours(): bool {
		$day  = (int) current_time( 'N' );
		$hour = (int) current_time( 'G' );

		return $day <= 5 && $hour >= 9 && $hour < 17;
	}

	/**
	 * Check if today is a weekend (Saturday or Sunday).
	 *
	 * @return bool
	 */
	public static function is_weekend(): bool {
		return (int) current_time( 'N' ) >= 6;
	}

	/**
	 * Check if today is a weekday (Monday through Friday).
	 *
	 * @return bool
	 */
	public static function is_weekday(): bool {
		return (int) current_time( 'N' ) <= 5;
	}

	/**
	 * Get the current quarter (1-4).
	 *
	 * @return int
	 */
	public static function get_quarter(): int {
		return (int) ceil( (int) current_time( 'n' ) / 3 );
	}

}