<?php
/**
 * DateTime Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\DateTime as DateTimeHelper;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Operators;

/**
 * Class DateTime
 *
 * Provides date and time related conditions.
 */
class DateTime {

	/**
	 * Get all datetime conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_date_conditions(),
			self::get_time_conditions(),
			self::get_period_conditions(),
			self::get_convenience_conditions()
		);
	}

	/**
	 * Get date-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_date_conditions(): array {
		return [
			'current_date'  => [
				'label'         => __( 'Date', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'Match against the current date.', 'arraypress' ),
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_date( $args ),
				'required_args' => [],
			],
			'current_year'  => [
				'label'         => __( 'Year', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2026', 'arraypress' ),
				'description'   => __( 'Match against the current year.', 'arraypress' ),
				'min'           => 2000,
				'max'           => 2100,
				'step'          => 1,
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_year( $args ),
				'required_args' => [],
			],
			'current_month' => [
				'label'         => __( 'Month', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select months...', 'arraypress' ),
				'description'   => __( 'Match against the current month.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Periods::get_months(),
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_month( $args ),
				'required_args' => [],
			],
			'day_of_month'  => [
				'label'         => __( 'Day of Month', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1-31', 'arraypress' ),
				'description'   => __( 'Match against the current day of the month (1-31).', 'arraypress' ),
				'min'           => 1,
				'max'           => 31,
				'step'          => 1,
				'compare_value' => fn( $args ) => DateTimeHelper::get_day_of_month( $args ),
				'required_args' => [],
			],
			'day_of_week'   => [
				'label'         => __( 'Day of Week', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select days...', 'arraypress' ),
				'description'   => __( 'Match against the current day of the week.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Periods::get_days_of_week(),
				'compare_value' => fn( $args ) => DateTimeHelper::get_day_of_week( $args ),
				'required_args' => [],
			],
			'day_of_year'   => [
				'label'         => __( 'Day of Year', 'arraypress' ),
				'group'         => __( 'Date & Time: Date', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1-365', 'arraypress' ),
				'description'   => __( 'Match against the current day of the year (1-365).', 'arraypress' ),
				'min'           => 1,
				'max'           => 366,
				'step'          => 1,
				'compare_value' => fn( $args ) => DateTimeHelper::get_day_of_year( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get time-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_time_conditions(): array {
		return [
			'current_time' => [
				'label'         => __( 'Time', 'arraypress' ),
				'group'         => __( 'Date & Time: Time', 'arraypress' ),
				'type'          => 'time',
				'description'   => __( 'Match against the current time.', 'arraypress' ),
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_time( $args ),
				'required_args' => [],
			],
			'time_of_day'  => [
				'label'         => __( 'Time of Day', 'arraypress' ),
				'group'         => __( 'Date & Time: Time', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select time of day...', 'arraypress' ),
				'description'   => __( 'Match against the current part of the day.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Periods::get_times_of_day(),
				'compare_value' => fn( $args ) => DateTimeHelper::get_time_of_day( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get period-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_period_conditions(): array {
		return [
			'quarter'      => [
				'label'         => __( 'Quarter', 'arraypress' ),
				'group'         => __( 'Date & Time: Periods', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select quarters...', 'arraypress' ),
				'description'   => __( 'Match against the current quarter.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Periods::get_quarters(),
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_quarter( $args ),
				'required_args' => [],
			],
			'week_of_year' => [
				'label'         => __( 'Week of Year', 'arraypress' ),
				'group'         => __( 'Date & Time: Periods', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1-52', 'arraypress' ),
				'description'   => __( 'Match against the current week number (1-53).', 'arraypress' ),
				'min'           => 1,
				'max'           => 53,
				'step'          => 1,
				'compare_value' => fn( $args ) => DateTimeHelper::get_current_week( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get convenience conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_convenience_conditions(): array {
		return [
			'is_weekend'        => [
				'label'         => __( 'Is Weekend', 'arraypress' ),
				'group'         => __( 'Date & Time: Convenience', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if today is Saturday or Sunday.', 'arraypress' ),
				'compare_value' => fn( $args ) => DateTimeHelper::is_weekend( $args ),
				'required_args' => [],
			],
			'is_weekday'        => [
				'label'         => __( 'Is Weekday', 'arraypress' ),
				'group'         => __( 'Date & Time: Convenience', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if today is Monday through Friday.', 'arraypress' ),
				'compare_value' => fn( $args ) => DateTimeHelper::is_weekday( $args ),
				'required_args' => [],
			],
			'is_business_hours' => [
				'label'         => __( 'Is Business Hours', 'arraypress' ),
				'group'         => __( 'Date & Time: Convenience', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if current time is Monday-Friday 9am-5pm.', 'arraypress' ),
				'compare_value' => fn( $args ) => DateTimeHelper::is_business_hours( $args ),
				'required_args' => [],
			],
		];
	}

}