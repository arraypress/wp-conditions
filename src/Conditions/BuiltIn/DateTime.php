<?php
/**
 * DateTime Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

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
		return [
			'day_of_week'   => [
				'label'         => __( 'Day of Week', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select days...', 'arraypress' ),
				'description'   => __( 'Match against the current day of the week.', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Is any of', 'arraypress' ),
					'none' => __( 'Is none of', 'arraypress' ),
				],
				'options'       => [
					[ 'value' => '1', 'label' => __( 'Monday', 'arraypress' ) ],
					[ 'value' => '2', 'label' => __( 'Tuesday', 'arraypress' ) ],
					[ 'value' => '3', 'label' => __( 'Wednesday', 'arraypress' ) ],
					[ 'value' => '4', 'label' => __( 'Thursday', 'arraypress' ) ],
					[ 'value' => '5', 'label' => __( 'Friday', 'arraypress' ) ],
					[ 'value' => '6', 'label' => __( 'Saturday', 'arraypress' ) ],
					[ 'value' => '7', 'label' => __( 'Sunday', 'arraypress' ) ],
				],
				'compare_value' => fn( $args ) => $args['day_of_week'] ?? current_time( 'N' ),
				'required_args' => [],
			],
			'week_of_year'  => [
				'label'         => __( 'Week of Year', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1-52', 'arraypress' ),
				'description'   => __( 'Match against the current week number (1-52).', 'arraypress' ),
				'min'           => 1,
				'max'           => 52,
				'step'          => 1,
				'compare_value' => fn( $args ) => $args['week_of_year'] ?? (int) current_time( 'W' ),
				'required_args' => [],
			],
			'day_of_month'  => [
				'label'         => __( 'Day of Month', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1-31', 'arraypress' ),
				'description'   => __( 'Match against the current day of the month (1-31).', 'arraypress' ),
				'min'           => 1,
				'max'           => 31,
				'step'          => 1,
				'compare_value' => fn( $args ) => $args['day_of_month'] ?? (int) current_time( 'j' ),
				'required_args' => [],
			],
			'current_month' => [
				'label'         => __( 'Month', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select months...', 'arraypress' ),
				'description'   => __( 'Match against the current month.', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Is any of', 'arraypress' ),
					'none' => __( 'Is none of', 'arraypress' ),
				],
				'options'       => [
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
				],
				'compare_value' => fn( $args ) => $args['current_month'] ?? current_time( 'n' ),
				'required_args' => [],
			],
			'current_date'  => [
				'label'         => __( 'Date', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'Match against the current date.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['current_date'] ?? current_time( 'Y-m-d' ),
				'required_args' => [],
			],
			'current_time'  => [
				'label'         => __( 'Time', 'arraypress' ),
				'group'         => __( 'Date & Time', 'arraypress' ),
				'type'          => 'time',
				'description'   => __( 'Match against the current time.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['current_time'] ?? current_time( 'H:i' ),
				'required_args' => [],
			],
		];
	}

}
