<?php
/**
 * Velocity Conditions
 *
 * Time-windowed counters used to detect card-testing, fraud rings, burst
 * attacks, and credential sharing. Each condition pre-honours a caller-
 * provided `velocity_*` arg, then a filter, then falls back to counting
 * against EDD's orders table when EDD is active.
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\Velocity as VelocityHelper;
use ArrayPress\Conditions\Options\Periods;

/**
 * Class Velocity
 *
 * Provides order-velocity conditions for fraud detection.
 */
class Velocity {

	/**
	 * Get all velocity conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'Order Velocity', 'arraypress' );

		return [
			// Volume by identity
			'velocity_orders_by_ip'             => [
				'label'         => __( 'Orders by IP', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Number of orders placed from this IP within the time window.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_orders_by_ip( $args ),
				'required_args' => [ 'ip' ],
			],
			'velocity_orders_by_email'          => [
				'label'         => __( 'Orders by Email', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Number of orders placed for this email within the time window.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_orders_by_email( $args ),
				'required_args' => [ 'email' ],
			],

			// Cross-entity (fraud rings)
			'velocity_distinct_emails_by_ip'    => [
				'label'         => __( 'Distinct Emails per IP', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Distinct email addresses associated with this IP within the time window. High counts indicate fraud rings.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_distinct_emails_by_ip( $args ),
				'required_args' => [ 'ip' ],
			],
			'velocity_distinct_ips_by_email'    => [
				'label'         => __( 'Distinct IPs per Email', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Distinct IP addresses associated with this email within the time window. High counts indicate credential sharing or stolen accounts.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_distinct_ips_by_email( $args ),
				'required_args' => [ 'email' ],
			],

			// Failed/cancelled signals
			'velocity_failed_orders_by_ip'      => [
				'label'         => __( 'Failed Orders by IP', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Failed, revoked, or abandoned orders from this IP within the time window. Common card-testing signature.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_failed_orders_by_ip( $args ),
				'required_args' => [ 'ip' ],
			],
			'velocity_failed_orders_by_email'   => [
				'label'         => __( 'Failed Orders by Email', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Failed, revoked, or abandoned orders for this email within the time window.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_failed_orders_by_email( $args ),
				'required_args' => [ 'email' ],
			],

			// Blocked attempts (relies on consumer-registered data source)
			'velocity_blocked_attempts_by_ip'   => [
				'label'         => __( 'Blocked Attempts by IP', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Checkout attempts previously blocked from this IP. Requires a fraud-filter plugin to provide the data source.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_blocked_attempts_by_ip( $args ),
				'required_args' => [ 'ip' ],
			],
			'velocity_blocked_attempts_by_email' => [
				'label'         => __( 'Blocked Attempts by Email', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Checkout attempts previously blocked for this email. Requires a fraud-filter plugin to provide the data source.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_blocked_attempts_by_email( $args ),
				'required_args' => [ 'email' ],
			],
		];
	}

}
