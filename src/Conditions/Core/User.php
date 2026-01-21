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

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\User as UserHelper;
use ArrayPress\Conditions\Helpers\Options;
use ArrayPress\Conditions\Helpers\Periods;
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
		return array_merge(
			self::get_identity_conditions(),
			self::get_profile_conditions(),
			self::get_activity_conditions(),
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
			'is_logged_in'   => [
				'label'         => __( 'Is Logged In', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is logged in.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_logged_in'] ?? is_user_logged_in(),
				'required_args' => [],
			],
			'user_id'        => [
				'label'         => __( 'Specific User', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'Match against specific user(s).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['user_id'] ?? get_current_user_id(),
				'required_args' => [],
			],
			'user_role'      => [
				'label'         => __( 'User Role', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select roles...', 'arraypress' ),
				'description'   => __( 'Match against the current user\'s role(s).', 'arraypress' ),
				'operators'     => Operators::collection(),
				'options'       => Options::get_roles(),
				'compare_value' => fn( $args ) => $args['user_roles'] ?? wp_get_current_user()->roles,
				'required_args' => [],
			],
			'has_capability' => [
				'label'         => __( 'Has Capability', 'arraypress' ),
				'group'         => __( 'User: Identity', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select capabilities...', 'arraypress' ),
				'description'   => __( 'Check if the user has specific capabilities.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'options'       => fn() => Options::get_capabilities(),
				'compare_value' => fn( $args ) => UserHelper::get_capabilities( $args ),
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
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match against email patterns. Supports: full email, @domain, .tld, or domain.', 'arraypress' ),
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
				'compare_value' => fn( $args ) => UserHelper::get_age( $args ),
				'required_args' => [],
			],
			'user_locale'     => [
				'label'         => __( 'User Locale', 'arraypress' ),
				'group'         => __( 'User: Profile', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select locale...', 'arraypress' ),
				'description'   => __( 'The user\'s configured locale/language.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Options::get_available_locales(),
				'compare_value' => fn( $args ) => UserHelper::get_locale( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get activity-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_activity_conditions(): array {
		return [
			'user_post_count'    => [
				'label'         => __( 'Post Count', 'arraypress' ),
				'group'         => __( 'User: Activity', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of posts authored by the user.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_post_count( $args ),
				'required_args' => [],
			],
			'user_comment_count' => [
				'label'         => __( 'Comment Count', 'arraypress' ),
				'group'         => __( 'User: Activity', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 25', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of approved comments by the user.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_comment_count( $args ),
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
			'user_meta_text'   => [
				'label'         => __( 'User Meta (Text)', 'arraypress' ),
				'group'         => __( 'User: Meta', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Format: meta_key:value_to_match', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => UserHelper::get_meta_text( $args, $user_value ),
				'required_args' => [],
			],
			'user_meta_number' => [
				'label'         => __( 'User Meta (Number)', 'arraypress' ),
				'group'         => __( 'User: Meta', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a numeric user meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => UserHelper::get_meta_number( $args, $user_value ),
				'required_args' => [],
			],
		];
	}

}