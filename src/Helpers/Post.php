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
	 * Get the parent post ID.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The parent post ID, or 0 if no parent.
	 */
	public static function get_parent( array $args ): int {
		$post = self::get( $args );

		return (int) ( $post?->post_parent ?? 0 );
	}

	/**
	 * Get the page template slug.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The template slug, or empty string if not found.
	 */
	public static function get_template( array $args ): string {
		$post = self::get( $args );

		if ( ! $post ) {
			return '';
		}

		return get_page_template_slug( $post->ID ) ?: '';
	}

	/**
	 * Get the post format.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The post format, or 'standard' if none set.
	 */
	public static function get_format( array $args ): string {
		$post = self::get( $args );

		if ( ! $post ) {
			return '';
		}

		$format = get_post_format( $post->ID );

		return $format ?: 'standard';
	}

	/**
	 * Check if post has a featured image.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool True if post has featured image.
	 */
	public static function has_featured_image( array $args ): bool {
		$post = self::get( $args );

		return $post && has_post_thumbnail( $post->ID );
	}

	/**
	 * Get the comment count for a post.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The comment count.
	 */
	public static function get_comment_count( array $args ): int {
		$post = self::get( $args );

		return (int) $post?->comment_count;
	}

	/**
	 * Get the comment status for a post.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The comment status ('open' or 'closed').
	 */
	public static function get_comment_status( array $args ): string {
		$post = self::get( $args );

		return $post ? $post->comment_status : '';
	}

	/**
	 * Check if post is sticky.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool True if post is sticky.
	 */
	public static function is_sticky( array $args ): bool {
		$post = self::get( $args );

		return $post && is_sticky( $post->ID );
	}

	/**
	 * Check if post has a manual excerpt.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool True if post has an excerpt.
	 */
	public static function has_excerpt( array $args ): bool {
		$post = self::get( $args );

		return $post && has_excerpt( $post->ID );
	}

	/**
	 * Get the word count of post content.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int The word count.
	 */
	public static function get_word_count( array $args ): int {
		$post = self::get( $args );

		if ( ! $post ) {
			return 0;
		}

		$content = wp_strip_all_tags( $post->post_content );

		return str_word_count( $content );
	}

	/**
	 * Check if post contains a specific shortcode.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The shortcode tag to check for.
	 *
	 * @return string The shortcode tag if found, empty string otherwise.
	 */
	public static function has_shortcode( array $args, ?string $user_value ): string {
		$post = self::get( $args );

		if ( ! $post || empty( $user_value ) ) {
			return '';
		}

		return has_shortcode( $post->post_content, $user_value ) ? $user_value : '';
	}

	/**
	 * Check if post contains a specific Gutenberg block.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The block name to check for (e.g., 'core/image').
	 *
	 * @return string The block name if found, empty string otherwise.
	 */
	public static function has_block( array $args, ?string $user_value ): string {
		$post = self::get( $args );

		if ( ! $post || empty( $user_value ) ) {
			return '';
		}

		return has_block( $user_value, $post ) ? $user_value : '';
	}

	/**
	 * Get post meta value as text.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
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
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The user value in format "meta_key:value".
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