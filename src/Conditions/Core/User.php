<?php
/**
 * User Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\User as UserHelper;
use ArrayPress\Conditions\Options\WordPress;
use ArrayPress\Conditions\Options\Periods;
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
			// Identity
			'is_logged_in'       => [
				'label'         => __( 'Logged In', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is logged in.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_logged_in'] ?? is_user_logged_in(),
				'required_args' => [],
			],
			'user_id'            => [
				'label'         => __( 'Specific User', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'Match against specific user(s).', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_id( $args ),
				'required_args' => [],
			],
			'user_role'          => [
				'label'         => __( 'Role', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select roles...', 'arraypress' ),
				'description'   => __( 'Match against the user\'s role(s).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => WordPress::get_roles(),
				'compare_value' => fn( $args ) => UserHelper::get_roles( $args ),
				'required_args' => [],
			],
			'has_capability'     => [
				'label'         => __( 'Has Capability', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select capabilities...', 'arraypress' ),
				'description'   => __( 'Check if the user has specific capabilities.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'options'       => fn() => WordPress::get_capabilities(),
				'compare_value' => fn( $args ) => UserHelper::get_capabilities( $args ),
				'required_args' => [],
			],

			// Profile
			'user_email'         => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match against email patterns. Supports: full email, @domain, .tld, or domain.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_email( $args ),
				'required_args' => [],
			],
			'user_username'      => [
				'label'         => __( 'Username', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. johndoe', 'arraypress' ),
				'description'   => __( 'Match against the user\'s login username.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_username( $args ),
				'required_args' => [],
			],
			'user_display_name'  => [
				'label'         => __( 'Display Name', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John Doe', 'arraypress' ),
				'description'   => __( 'Match against the user\'s display name.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_display_name( $args ),
				'required_args' => [],
			],

			'user_first_name'    => [
				'label'         => __( 'First Name', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'Match against the first name on the user\'s profile.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_first_name( $args ),
				'required_args' => [],
			],
			'user_last_name'     => [
				'label'         => __( 'Last Name', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Doe', 'arraypress' ),
				'description'   => __( 'Match against the last name on the user\'s profile.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_last_name( $args ),
				'required_args' => [],
			],
			'user_nicename'      => [
				'label'         => __( 'Nicename (Slug)', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. john-doe', 'arraypress' ),
				'description'   => __( 'The URL-safe slug WordPress derives from the username. Distinct from the login, which is what the Username condition matches.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_nicename( $args ),
				'required_args' => [],
			],
			'user_website'       => [
				'label'         => __( 'Website', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. example.com', 'arraypress' ),
				'description'   => __( 'The website URL on the user\'s profile. Spam registrations fill this in far more often than real ones leave it blank.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => UserHelper::get_website( $args ),
				'required_args' => [],
			],
			'user_locale'        => [
				'label'         => __( 'Locale', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. en_GB', 'arraypress' ),
				'description'   => __( 'The user\'s admin language, falling back to the site locale when they have not chosen one.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_locale( $args ),
				'required_args' => [],
			],
			'is_super_admin'     => [
				'label'         => __( 'Super Admin', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the user is a network super admin. Always false on a single site — use the Role condition there.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::is_super_admin( $args ),
				'required_args' => [],
			],

			// Dates
			'user_date_registered' => [
				'label'         => __( 'Date Registered', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'Match against the user registration date.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_date_registered( $args ),
				'required_args' => [],
			],
			'user_registered'    => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long the user has been registered.', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'compare_value' => fn( $args ) => UserHelper::get_age( $args ),
				'required_args' => [],
			],

			// Activity
			'user_post_count'    => [
				'label'         => __( 'Post Count', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
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
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 25', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of approved comments by the user.', 'arraypress' ),
				'compare_value' => fn( $args ) => UserHelper::get_comment_count( $args ),
				'required_args' => [],
			],

			// Meta
			'user_meta_text'     => [
				'label'         => __( 'User Meta (Text)', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Format: meta_key:value_to_match', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => UserHelper::get_meta_text( $args, $user_value ),
				'required_args' => [],
			],
			'user_meta_number'   => [
				'label'         => __( 'User Meta (Number)', 'arraypress' ),
				'group'         => __( 'User', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a numeric user meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => UserHelper::get_meta_number( $args, $user_value ),
				'required_args' => [],
			],
		];
	}

}