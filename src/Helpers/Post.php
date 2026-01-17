<?php
/**
 * Post Helper
 *
 * Provides utilities for retrieving post data from condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

use WP_Post;

/**
 * Class Post
 *
 * Utilities for retrieving post data in conditions.
 */
class Post {

	/**
	 * Get post object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return WP_Post|null
	 */
	public static function get( array $args ): ?WP_Post {
		$post_id = $args['post_id'] ?? get_the_ID();

		if ( ! $post_id ) {
			return null;
		}

		return get_post( $post_id );
	}

	/**
	 * Get post terms for a taxonomy.
	 *
	 * @param array  $args     The arguments including post_id.
	 * @param string $taxonomy The taxonomy to get terms from.
	 *
	 * @return array<int>
	 */
	public static function get_terms( array $args, string $taxonomy ): array {
		$post_id = $args['post_id'] ?? get_the_ID();

		if ( ! $post_id ) {
			return [];
		}

		$terms = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		return $terms;
	}

	/**
	 * Get post age in the specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The age in the specified unit, or 0 if post not found.
	 */
	public static function get_age( array $args ): int {
		$post = self::get( $args );

		if ( ! $post ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $post->post_date, $parsed['unit'] );
	}

	/**
	 * Get post meta value as text.
	 *
	 * @param array  $args       The condition arguments.
	 * @param null|string $user_value The user value in format "meta_key:value".
	 *
	 * @return string The meta value or empty string if not found.
	 */
	public static function get_meta_text( array $args, ?string $user_value ): string {
		$post = self::get( $args );

		if ( ! $post ) {
			return '';
		}

		$parsed = Parse::meta( $user_value ?? '' );

		return (string) get_post_meta( $post->ID, $parsed['key'], true );
	}

	/**
	 * Get post meta value as number.
	 *
	 * @param array  $args       The condition arguments.
	 * @param null|string $user_value The user value in format "meta_key:value".
	 *
	 * @return float The meta value or 0 if not found.
	 */
	public static function get_meta_number( array $args, ?string $user_value ): float {
		$post = self::get( $args );

		if ( ! $post ) {
			return 0;
		}

		$parsed = Parse::meta_typed( $user_value ?? '', 'number' );

		return (float) get_post_meta( $post->ID, $parsed['key'], true );
	}

}