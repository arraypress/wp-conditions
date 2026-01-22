<?php
/**
 * Common Options Helper
 *
 * Provides generic, reusable option arrays for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers\Options
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Options;

/**
 * Class Common
 *
 * Generic option arrays for common select field patterns.
 */
class Common {

	/**
	 * Get yes/no options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_yes_no(): array {
		return [
			[ 'value' => 'yes', 'label' => __( 'Yes', 'arraypress' ) ],
			[ 'value' => 'no', 'label' => __( 'No', 'arraypress' ) ],
		];
	}

	/**
	 * Get true/false options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_true_false(): array {
		return [
			[ 'value' => '1', 'label' => __( 'True', 'arraypress' ) ],
			[ 'value' => '0', 'label' => __( 'False', 'arraypress' ) ],
		];
	}

	/**
	 * Get enabled/disabled options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_enabled_disabled(): array {
		return [
			[ 'value' => 'enabled', 'label' => __( 'Enabled', 'arraypress' ) ],
			[ 'value' => 'disabled', 'label' => __( 'Disabled', 'arraypress' ) ],
		];
	}

	/**
	 * Get active/inactive options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_active_inactive(): array {
		return [
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'inactive', 'label' => __( 'Inactive', 'arraypress' ) ],
		];
	}

	/**
	 * Get open/closed options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_open_closed(): array {
		return [
			[ 'value' => 'open', 'label' => __( 'Open', 'arraypress' ) ],
			[ 'value' => 'closed', 'label' => __( 'Closed', 'arraypress' ) ],
		];
	}

	/**
	 * Get public/private options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_public_private(): array {
		return [
			[ 'value' => 'public', 'label' => __( 'Public', 'arraypress' ) ],
			[ 'value' => 'private', 'label' => __( 'Private', 'arraypress' ) ],
		];
	}

	/**
	 * Get visibility options (public/private/hidden).
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_visibility(): array {
		return [
			[ 'value' => 'public', 'label' => __( 'Public', 'arraypress' ) ],
			[ 'value' => 'private', 'label' => __( 'Private', 'arraypress' ) ],
			[ 'value' => 'hidden', 'label' => __( 'Hidden', 'arraypress' ) ],
		];
	}

	/**
	 * Get WordPress post visibility options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_visibility(): array {
		return [
			[ 'value' => 'public', 'label' => __( 'Public', 'arraypress' ) ],
			[ 'value' => 'private', 'label' => __( 'Private', 'arraypress' ) ],
			[ 'value' => 'password', 'label' => __( 'Password Protected', 'arraypress' ) ],
		];
	}

	/**
	 * Get approval status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_approval_statuses(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'approved', 'label' => __( 'Approved', 'arraypress' ) ],
			[ 'value' => 'rejected', 'label' => __( 'Rejected', 'arraypress' ) ],
		];
	}

	/**
	 * Get menu order options (commonly used ranges).
	 *
	 * @param int $min  Minimum value.
	 * @param int $max  Maximum value.
	 * @param int $step Step increment.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_order_range( int $min = 0, int $max = 100, int $step = 10 ): array {
		$options = [];

		for ( $i = $min; $i <= $max; $i += $step ) {
			$options[] = [
				'value' => (string) $i,
				'label' => (string) $i,
			];
		}

		return $options;
	}

	/**
	 * Get priority level options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_priorities(): array {
		return [
			[ 'value' => 'low', 'label' => __( 'Low', 'arraypress' ) ],
			[ 'value' => 'normal', 'label' => __( 'Normal', 'arraypress' ) ],
			[ 'value' => 'high', 'label' => __( 'High', 'arraypress' ) ],
			[ 'value' => 'urgent', 'label' => __( 'Urgent', 'arraypress' ) ],
		];
	}

	/**
	 * Get comparison direction options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_directions(): array {
		return [
			[ 'value' => 'asc', 'label' => __( 'Ascending', 'arraypress' ) ],
			[ 'value' => 'desc', 'label' => __( 'Descending', 'arraypress' ) ],
		];
	}

}