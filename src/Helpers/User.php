<?php
/**
 * User Helper
 *
 * Provides utilities for retrieving user data from condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

use WP_User;

/**
 * Class User
 *
 * Utilities for retrieving user data in conditions.
 */
class User {

	/**
	 * Get user object from args or current user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WP_User|null
	 */
	public static function get( array $args ): ?WP_User {
		$user_id = $args['user_id'] ?? get_current_user_id();

		if ( ! $user_id ) {
			return null;
		}

		$user = get_userdata( $user_id );

		return $user ?: null;
	}

	/**
	 * Get user account age in the specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The age in the specified unit, or 0 if user not found.
	 */
	public static function get_age( array $args ): int {
		$user = self::get( $args );

		if ( ! $user ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $user->user_registered, $parsed['unit'] );
	}

	/**
	 * Get user meta value as text.
	 *
	 * @param array  $args       The condition arguments.
	 * @param null|string $user_value The user value in format "meta_key:value".
	 *
	 * @return string The meta value or empty string if not found.
	 */
	public static function get_meta_text( array $args, ?string $user_value ): string {
		$user = self::get( $args );

		if ( ! $user ) {
			return '';
		}

		$parsed = Parse::meta( $user_value ?? '' );

		return (string) get_user_meta( $user->ID, $parsed['key'], true );
	}

	/**
	 * Get user meta value as number.
	 *
	 * @param array  $args       The condition arguments.
	 * @param null|string $user_value The user value in format "meta_key:value".
	 *
	 * @return float The meta value or 0 if not found.
	 */
	public static function get_meta_number( array $args, ?string $user_value ): float {
		$user = self::get( $args );

		if ( ! $user ) {
			return 0;
		}

		$parsed = Parse::meta_typed( $user_value ?? '', 'number' );

		return (float) get_user_meta( $user->ID, $parsed['key'], true );
	}

}