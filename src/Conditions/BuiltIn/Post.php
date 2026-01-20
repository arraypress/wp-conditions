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

use ArrayPress\Conditions\Helpers\Post as PostHelper;
use ArrayPress\Conditions\Helpers\Options;
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
			self::get_content_conditions(),
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
			'post_status'   => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select statuses...', 'arraypress' ),
				'description'   => __( 'Match against the post status.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Options::get_post_statuses(),
				'arg'           => 'post_status',
				'required_args' => [ 'post_status' ],
			],
			'post_type'     => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select post types...', 'arraypress' ),
				'description'   => __( 'Match against the post type.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Options::get_post_types(),
				'arg'           => 'post_type',
				'required_args' => [ 'post_type' ],
			],
			'post_author'   => [
				'label'         => __( 'Author', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search authors...', 'arraypress' ),
				'description'   => __( 'Match against the post author.', 'arraypress' ),
				'arg'           => 'post_author',
				'required_args' => [ 'post_author' ],
			],
			'post_age'      => [
				'label'         => __( 'Age', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long since the post was published.', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'compare_value' => fn( $args ) => PostHelper::get_age( $args ),
				'required_args' => [ 'post_id' ],
			],
			'post_parent'   => [
				'label'         => __( 'Parent', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'page',
				'multiple'      => true,
				'placeholder'   => __( 'Search parent pages...', 'arraypress' ),
				'description'   => __( 'Match against the parent post/page.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return (int) $post?->post_parent;
				},
				'required_args' => [ 'post_id' ],
			],
			'post_template' => [
				'label'         => __( 'Page Template', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select template...', 'arraypress' ),
				'description'   => __( 'Match against the page template.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Options::get_page_templates(),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post ? get_page_template_slug( $post->ID ) : '';
				},
				'required_args' => [ 'post_id' ],
			],
			'post_format'   => [
				'label'         => __( 'Post Format', 'arraypress' ),
				'group'         => __( 'Post: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select format...', 'arraypress' ),
				'description'   => __( 'Match against the post format.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Options::get_post_formats(),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					if ( ! $post ) {
						return '';
					}

					$format = get_post_format( $post->ID );

					return $format ?: 'standard';
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
				'compare_value' => fn( $args ) => PostHelper::get_terms( $args, 'category' ),
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
				'compare_value' => fn( $args ) => PostHelper::get_terms( $args, 'post_tag' ),
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
	 * Get content-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_content_conditions(): array {
		return [
			'has_featured_image'    => [
				'label'         => __( 'Has Featured Image', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the post has a featured image.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post ? has_post_thumbnail( $post->ID ) : false;
				},
				'required_args' => [ 'post_id' ],
			],
			'post_comment_count'    => [
				'label'         => __( 'Comment Count', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of comments on the post.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post ? (int) $post->comment_count : 0;
				},
				'required_args' => [ 'post_id' ],
			],
			'post_comment_status'   => [
				'label'         => __( 'Comment Status', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'Whether comments are open or closed.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'open', 'label' => __( 'Open', 'arraypress' ) ],
					[ 'value' => 'closed', 'label' => __( 'Closed', 'arraypress' ) ],
				],
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post ? $post->comment_status : '';
				},
				'required_args' => [ 'post_id' ],
			],
			'is_sticky'             => [
				'label'         => __( 'Is Sticky', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the post is sticky.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post ? is_sticky( $post->ID ) : false;
				},
				'required_args' => [ 'post_id' ],
			],
			'has_excerpt'           => [
				'label'         => __( 'Has Excerpt', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the post has a manual excerpt.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					return $post && has_excerpt( $post->ID );
				},
				'required_args' => [ 'post_id' ],
			],
			'post_word_count'       => [
				'label'         => __( 'Word Count', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The approximate word count of the post content.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$post = PostHelper::get( $args );

					if ( ! $post ) {
						return 0;
					}

					$content = wp_strip_all_tags( $post->post_content );

					return str_word_count( $content );
				},
				'required_args' => [ 'post_id' ],
			],
			'has_shortcode'         => [
				'label'         => __( 'Has Shortcode', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. gallery', 'arraypress' ),
				'description'   => __( 'Check if the post contains a specific shortcode.', 'arraypress' ),
				'operators'     => [
					'==' => __( 'Contains', 'arraypress' ),
					'!=' => __( 'Does not contain', 'arraypress' ),
				],
				'compare_value' => function ( $args, $user_value ) {
					$post = PostHelper::get( $args );

					if ( ! $post || empty( $user_value ) ) {
						return '';
					}

					return has_shortcode( $post->post_content, $user_value ) ? $user_value : '';
				},
				'required_args' => [ 'post_id' ],
			],
			'has_block'             => [
				'label'         => __( 'Has Block', 'arraypress' ),
				'group'         => __( 'Post: Content', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. core/image', 'arraypress' ),
				'description'   => __( 'Check if the post contains a specific Gutenberg block.', 'arraypress' ),
				'operators'     => [
					'==' => __( 'Contains', 'arraypress' ),
					'!=' => __( 'Does not contain', 'arraypress' ),
				],
				'compare_value' => function ( $args, $user_value ) {
					$post = PostHelper::get( $args );

					if ( ! $post || empty( $user_value ) ) {
						return '';
					}

					return has_block( $user_value, $post ) ? $user_value : '';
				},
				'required_args' => [ 'post_id' ],
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
				'compare_value' => fn( $args, $user_value ) => PostHelper::get_meta_text( $args, $user_value ),
				'required_args' => [ 'post_id' ],
			],
			'post_meta_number' => [
				'label'         => __( 'Post Meta (Number)', 'arraypress' ),
				'group'         => __( 'Post: Meta', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a numeric post meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => PostHelper::get_meta_number( $args, $user_value ),
				'required_args' => [ 'post_id' ],
			],
		];
	}

}