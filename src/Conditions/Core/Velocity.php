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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many orders this IP has placed in the last minute, hour, day or week, as chosen. Catches burst attacks where one IP rapidly submits multiple orders. Suggested thresholds: ≥3 in the last hour for review, ≥5 in the last hour to block. Counts every order on the EDD orders table — completed, pending, refunded.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many orders this email has placed in the last minute, hour, day or week, as chosen. Less sensitive than by-IP because legitimate customers do place repeat orders, but high values (e.g. ≥5 in the last day) suggest scripted abuse.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_orders_by_email( $args ),
				'required_args' => [ 'email' ],
			],
			'velocity_same_product_orders_by_ip' => [
				'label'         => __( 'Same-Product Orders by IP', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many recent orders from this IP contain the same product as the current cart. The classic card-testing fingerprint: fraudsters pick the cheapest item and hammer checkout with stolen cards. Pair with `Cart Total < $10` for a high-precision rule that almost never trips legitimate repeat-purchases. Suggested: ≥3 in the last hour = block.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_same_product_orders_by_ip( $args ),
				'required_args' => [ 'ip' ],
			],
			'velocity_same_product_orders_by_email' => [
				'label'         => __( 'Same-Product Orders by Email', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many recent orders from this email contain the same product as the current cart. Less sensitive than the by-IP variant since fraudsters often rotate emails, but useful for catching mid-volume abusers who reuse one address.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_same_product_orders_by_email( $args ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many DIFFERENT email addresses have ordered from this IP in the last minute, hour, day or week, as chosen. The strongest velocity signal — one machine cycling through multiple emails is the classic fraud-ring fingerprint. Suggested threshold: ≥3 distinct emails from one IP in the last day = block.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many DIFFERENT IPs have used this email in the last minute, hour, day or week, as chosen. High values suggest credential sharing or a stolen account being passed around. Less sensitive than the IP→emails direction because legitimate users do change networks (mobile/wifi/work). Suggested threshold: ≥5 in the last day.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many orders from this IP have FAILED in the last minute, hour, day or week, as chosen (failed / revoked / abandoned status). The classic card-testing signature: a fraudster trying stolen cards in rapid succession until one works. Suggested threshold: ≥3 in the last hour = block immediately.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many orders for this email have FAILED in the last minute, hour, day or week, as chosen. Catches the same card-testing pattern as the IP version, useful when fraudsters rotate IPs but reuse the email.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many checkout attempts from this IP were previously blocked by other rules. Lets you escalate repeat offenders — if an IP triggered a block 3 times in the last hour, the next attempt should also block (even if it would otherwise pass). Requires a consumer plugin (e.g. fraud filter) to provide the data source via the wp_conditions_velocity_blocked_attempts_by_ip filter.', 'arraypress' ),
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
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many checkout attempts for this email were previously blocked by other rules. Same idea as by-IP version but tracks the email — useful when fraudsters rotate networks but keep the same email handle.', 'arraypress' ),
				'compare_value' => fn( $args ) => VelocityHelper::count_blocked_attempts_by_email( $args ),
				'required_args' => [ 'email' ],
			],
		];
	}
}
