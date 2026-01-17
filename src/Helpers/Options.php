<?php
/**
 * Options Helper
 *
 * Provides standardized option arrays for select fields.
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
 * Class Options
 *
 * Standardized option arrays for select condition fields.
 */
class Options {

	/**
	 * Get role options for select field.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_roles(): array {
		$roles   = wp_roles()->get_names();
		$options = [];

		foreach ( $roles as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Get post status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_statuses(): array {
		$statuses = get_post_stati( [ 'show_in_admin_status_list' => true ], 'objects' );
		$options  = [];

		foreach ( $statuses as $status ) {
			$options[] = [
				'value' => $status->name,
				'label' => $status->label,
			];
		}

		return $options;
	}

	/**
	 * Get post type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_types(): array {
		$types   = get_post_types( [ 'public' => true ], 'objects' );
		$options = [];

		foreach ( $types as $type ) {
			$options[] = [
				'value' => $type->name,
				'label' => $type->labels->singular_name,
			];
		}

		return $options;
	}

}