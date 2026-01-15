<?php
/**
 * User Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

use ArrayPress\Conditions\Operators;

/**
 * Class User
 *
 * Provides user related conditions.
 */
class User {

	/**
	 * Get all user conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			'user_role'       => [
				'label'         => __( 'User Role', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select roles...', 'arraypress' ),
				'description'   => __( 'Match against the current user\'s role(s).', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Has any of', 'arraypress' ),
					'none' => __( 'Has none of', 'arraypress' ),
					'all'  => __( 'Has all of', 'arraypress' ),
				],
				'options'       => fn() => self::get_role_options(),
				'compare_value' => fn( $args ) => $args['user_roles'] ?? wp_get_current_user()->roles,
				'required_args' => [],
			],
			'is_logged_in'    => [
				'label'         => __( 'Is Logged In', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is logged in.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_logged_in'] ?? is_user_logged_in(),
				'required_args' => [],
			],
			'user_id'         => [
				'label'         => __( 'Specific User', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'Match against specific user(s).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_id'] ?? get_current_user_id(),
				'required_args' => [],
			],
			'user_email'      => [
				'label'         => __( 'User Email', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. @gmail.com', 'arraypress' ),
				'description'   => __( 'Match against the user\'s email address.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_email'] ?? wp_get_current_user()->user_email,
				'required_args' => [],
			],
			'email_domain'    => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain and press Enter...', 'arraypress' ),
				'description'   => __( 'Match if user email ends with any of these domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => fn( $args ) => $args['user_email'] ?? wp_get_current_user()->user_email,
				'required_args' => [],
			],
			'user_username'   => [
				'label'         => __( 'Username', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. johndoe', 'arraypress' ),
				'description'   => __( 'Match against the user\'s login username.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_username'] ?? wp_get_current_user()->user_login,
				'required_args' => [],
			],
			'user_registered' => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long the user has been registered.', 'arraypress' ),
				'min'           => 0,
				'units'         => [
					[ 'value' => 'days', 'label' => __( 'Days', 'arraypress' ) ],
					[ 'value' => 'weeks', 'label' => __( 'Weeks', 'arraypress' ) ],
					[ 'value' => 'months', 'label' => __( 'Months', 'arraypress' ) ],
					[ 'value' => 'years', 'label' => __( 'Years', 'arraypress' ) ],
				],
				'compare_value' => fn( $args ) => self::get_account_age( $args ),
				'required_args' => [],
			],
			'user_meta'       => [
				'label'         => __( 'User Meta', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'Meta value to match...', 'arraypress' ),
				'description'   => __( 'Match against a user meta field value.', 'arraypress' ),
				'arg'           => 'user_meta_value',
				'required_args' => [ 'user_meta_key', 'user_meta_value' ],
			],
		];
	}

	/**
	 * Get role options for select field.
	 *
	 * @return array
	 */
	private static function get_role_options(): array {
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
	 * Get user account age in specified unit.
	 *
	 * @param array $args The arguments including _unit.
	 *
	 * @return int
	 */
	private static function get_account_age( array $args ): int {
		if ( isset( $args['account_age'] ) ) {
			return (int) $args['account_age'];
		}

		$user_id = $args['user_id'] ?? get_current_user_id();
		if ( ! $user_id ) {
			return 0;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return 0;
		}

		$registered = strtotime( $user->user_registered );
		$now        = current_time( 'timestamp' );
		$diff       = $now - $registered;

		$unit = $args['_unit'] ?? 'days';

		return match ( $unit ) {
			'weeks' => (int) floor( $diff / WEEK_IN_SECONDS ),
			'months' => (int) floor( $diff / MONTH_IN_SECONDS ),
			'years' => (int) floor( $diff / YEAR_IN_SECONDS ),
			default => (int) floor( $diff / DAY_IN_SECONDS ),
		};
	}

}
