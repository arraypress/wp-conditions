<?php
/**
 * User Helper
 *
 * Provides utilities for retrieving user data from condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers\Data
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Data;

use ArrayPress\Conditions\Helpers\Parse;
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
	 * Get the user ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The user ID, or 0 if not found.
	 */
	public static function get_id( array $args ): int {
		$user = self::get( $args );

		return $user?->ID ?? 0;
	}

	/**
	 * Get the user's roles.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<string> Array of role slugs.
	 */
	public static function get_roles( array $args ): array {
		$user = self::get( $args );

		return $user?->roles ?? [];
	}

	/**
	 * Get the user's email address.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The user's email, or empty string if not found.
	 */
	public static function get_email( array $args ): string {
		$user = self::get( $args );

		return $user?->user_email ?? '';
	}

	/**
	 * Get the user's username (login).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The username, or empty string if not found.
	 */
	public static function get_username( array $args ): string {
		$user = self::get( $args );

		return $user?->user_login ?? '';
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
	 * Get all capabilities the user has.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return array<string> Array of capability names.
	 */
	public static function get_capabilities( array $args ): array {
		$user = self::get( $args );

		if ( ! $user ) {
			return [];
		}

		return array_keys( array_filter( $user->allcaps ) );
	}

	/**
	 * Get the user's locale/language.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The user's locale, or site locale if not set.
	 */
	public static function get_locale( array $args ): string {
		$user = self::get( $args );

		if ( ! $user ) {
			return '';
		}

		$locale = get_user_locale( $user->ID );

		return $locale ?: get_locale();
	}

	/**
	 * Get the number of posts authored by the user.
	 *
	 * @param array  $args      The condition arguments.
	 * @param string $post_type The post type to count.
	 *
	 * @return int The post count.
	 */
	public static function get_post_count( array $args, string $post_type = 'post' ): int {
		$user = self::get( $args );

		if ( ! $user ) {
			return 0;
		}

		return (int) count_user_posts( $user->ID, $post_type, true );
	}

	/**
	 * Get the number of approved comments by the user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The comment count.
	 */
	public static function get_comment_count( array $args ): int {
		$user = self::get( $args );

		if ( ! $user ) {
			return 0;
		}

		return (int) get_comments( [
			'user_id' => $user->ID,
			'status'  => 'approve',
			'count'   => true,
		] );
	}

	/**
	 * Get user meta value as text.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
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
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
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