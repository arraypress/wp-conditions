<?php
/**
 * Post Built-in Conditions
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
use ArrayPress\Conditions\Helpers\Parse;
use ArrayPress\Conditions\Helpers\Periods;
use ArrayPress\Conditions\Operators;

/**
 * Class Post
 *
 * Provides post related conditions.
 */
class Post {

	/**
	 * Get all post conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_detail_conditions(),
			self::get_taxonomy_conditions(),
			self::get_meta_conditions()
		);
	}

	/**
	 * Get detail-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detail_conditions(): array {
		return [
			'post_status' => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select statuses...', 'arraypress' ),
				'description'   => __( 'Match against the post status.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_post_status_options(),
				'arg'           => 'post_status',
				'required_args' => [ 'post_status' ],
			],
			'post_type'   => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select post types...', 'arraypress' ),
				'description'   => __( 'Match against the post type.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_post_type_options(),
				'arg'           => 'post_type',
				'required_args' => [ 'post_type' ],
			],
			'post_author' => [
				'label'         => __( 'Author', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search authors...', 'arraypress' ),
				'description'   => __( 'Match against the post author.', 'arraypress' ),
				'arg'           => 'post_author',
				'required_args' => [ 'post_author' ],
			],
			'post_age'    => [
				'label'         => __( 'Age', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long since the post was published.', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'compare_value' => function ( $args ) {
					$post = self::get_post( $args );

					if ( ! $post ) {
						return 0;
					}

					return DateTimeHelper::get_age( $post->post_date, $args['_unit'] ?? 'day' );
				},
				'required_args' => [ 'post_id' ],
			],
		];
	}

	/**
	 * Get taxonomy-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_taxonomy_conditions(): array {
		return [
			'post_category' => [
				'label'         => __( 'Category', 'arraypress' ),
				'group'         => __( 'Post: Taxonomies', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Match against post categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => self::get_post_terms( $args, 'category' ),
				'required_args' => [ 'post_id' ],
			],
			'post_tag'      => [
				'label'         => __( 'Tag', 'arraypress' ),
				'group'         => __( 'Post: Taxonomies', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'post_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Match against post tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => self::get_post_terms( $args, 'post_tag' ),
				'required_args' => [ 'post_id' ],
			],
			'has_term'      => [
				'label'         => __( 'Has Term', 'arraypress' ),
				'group'         => __( 'Post: Taxonomies', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'category',
				'multiple'      => true,
				'placeholder'   => __( 'Search terms...', 'arraypress' ),
				'description'   => __( 'Check if post has specific terms (specify taxonomy when registering).', 'arraypress' ),
				'arg'           => 'post_terms',
				'required_args' => [ 'post_id', 'post_terms' ],
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
			'post_meta_text'   => [
				'label'         => __( 'Post Meta (Text)', 'arraypress' ),
				'group'         => __( 'Post: Meta', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Format: meta_key:value_to_match', 'arraypress' ),
				'compare_value' => function ( $args, $user_value ) {
					$post = self::get_post( $args );

					if ( ! $post ) {
						return '';
					}

					$parsed = Parse::meta( $user_value ?? '' );

					return get_post_meta( $post->ID, $parsed['key'], true );
				},
				'required_args' => [ 'post_id' ],
			],
			'post_meta_number' => [
				'label'         => __( 'Post Meta (Number)', 'arraypress' ),
				'group'         => __( 'Post: Meta', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a numeric post meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => function ( $args, $user_value ) {
					$post = self::get_post( $args );

					if ( ! $post ) {
						return 0;
					}

					$parsed = Parse::meta_typed( $user_value ?? '', 'number' );

					return (float) get_post_meta( $post->ID, $parsed['key'], true );
				},
				'required_args' => [ 'post_id' ],
			],
		];
	}

	/**
	 * Get post object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return \WP_Post|null
	 */
	private static function get_post( array $args ): ?\WP_Post {
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
	private static function get_post_terms( array $args, string $taxonomy ): array {
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
	 * Get post status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_post_status_options(): array {
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
	private static function get_post_type_options(): array {
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