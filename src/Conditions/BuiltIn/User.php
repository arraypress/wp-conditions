<?php
/**
 * User Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

use ArrayPress\Conditions\Helpers\DateTime as DateTimeHelper;
use ArrayPress\Conditions\Helpers\Parser;
use ArrayPress\Conditions\Helpers\Periods;
use ArrayPress\Conditions\Operators;
use WP_User;

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
		return array_merge(
			self::get_identity_conditions(),
			self::get_profile_conditions(),
			self::get_meta_conditions()
		);
	}

	/**
	 * Get identity-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_identity_conditions(): array {
		return [
			'is_logged_in' => [
				'label'         => __( 'Is Logged In', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is logged in.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_logged_in'] ?? is_user_logged_in(),
				'required_args' => [],
			],
			'user_id'      => [
				'label'         => __( 'Specific User', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'Match against specific user(s).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_id'] ?? get_current_user_id(),
				'required_args' => [],
			],
			'user_role'    => [
				'label'         => __( 'User Role', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
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
		];
	}

	/**
	 * Get profile-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_profile_conditions(): array {
		return [
			'user_email'      => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'User: Profile', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match against the user\'s email. Supports: full email, @domain.com, .edu, partial domain.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_email'] ?? wp_get_current_user()->user_email,
				'required_args' => [],
			],
			'email_domain'    => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'User: Profile', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain and press Enter...', 'arraypress' ),
				'description'   => __( 'Match if user email ends with any of these domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => fn( $args ) => $args['user_email'] ?? wp_get_current_user()->user_email,
				'required_args' => [],
			],
			'user_username'   => [
				'label'         => __( 'Username', 'arraypress' ),
				'group'         => __( 'User: Profile', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. johndoe', 'arraypress' ),
				'description'   => __( 'Match against the user\'s login username.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_username'] ?? wp_get_current_user()->user_login,
				'required_args' => [],
			],
			'user_registered' => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'User: Profile', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long the user has been registered.', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'compare_value' => function ( $args, $value ) {
					$user = self::get_user( $args );

					if ( ! $user ) {
						return 0;
					}

					return DateTimeHelper::get_age( $user->user_registered, $args['_unit'] ?? 'day' );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get meta-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_meta_conditions(): array {
		return [
			'user_meta_text' => [
				'label'         => __( 'User Meta (Text)', 'arraypress' ),
				'group'         => __( 'User: Meta', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Format: meta_key:value_to_match', 'arraypress' ),
				'compare_value' => function ( $args, $user_value ) {
					$user = self::get_user( $args );

					if ( ! $user ) {
						return '';
					}

					$parsed = Parser::parse_meta( $user_value ?? '' );

					return get_user_meta( $user->ID, $parsed['key'], true );
				},
				'required_args' => [],
			],
			'user_meta_number' => [
				'label'         => __( 'User Meta (Number)', 'arraypress' ),
				'group'         => __( 'User: Meta', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value to match...', 'arraypress' ),
				'description'   => __( 'Match against a numeric user meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => function ( $args, $user_value ) {
					$user = self::get_user( $args );

					if ( ! $user ) {
						return 0;
					}

					$parsed = Parser::parse_meta_typed( $user_value ?? '', 'number' );

					return (float) get_user_meta( $user->ID, $parsed['key'], true );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get user object from args or current user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WP_User|null
	 */
	private static function get_user( array $args ): ?WP_User {
		$user_id = $args['user_id'] ?? get_current_user_id();

		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return $user ?: null;
	}

	/**
	 * Get role options for select field.
	 *
	 * @return array<array{value: string, label: string}>
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

}