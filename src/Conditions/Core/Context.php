<?php
/**
 * WordPress Context Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

/**
 * Class Context
 *
 * Provides WordPress context related conditions.
 */
class Context {

	/**
	 * Get all context conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_page_conditions(),
			self::get_archive_conditions(),
			self::get_request_type_conditions()
		);
	}

	/**
	 * Get page context conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_page_conditions(): array {
		return [
			'is_front_page' => [
				'label'         => __( 'Is Front Page', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the site front page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_front_page'] ?? is_front_page(),
				'required_args' => [],
			],
			'is_home'       => [
				'label'         => __( 'Is Blog Home', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the blog posts index page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_home'] ?? is_home(),
				'required_args' => [],
			],
			'is_singular'   => [
				'label'         => __( 'Is Singular', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on any single post, page, or custom post type.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_singular'] ?? is_singular(),
				'required_args' => [],
			],
			'is_single'     => [
				'label'         => __( 'Is Single Post', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a single post page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_single'] ?? is_single(),
				'required_args' => [],
			],
			'is_page'       => [
				'label'         => __( 'Is Page', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a static page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_page'] ?? is_page(),
				'required_args' => [],
			],
			'is_attachment' => [
				'label'         => __( 'Is Attachment', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on an attachment page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_attachment'] ?? is_attachment(),
				'required_args' => [],
			],
			'is_search'     => [
				'label'         => __( 'Is Search Results', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the search results page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_search'] ?? is_search(),
				'required_args' => [],
			],
			'is_404'        => [
				'label'         => __( 'Is 404 Page', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a 404 error page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_404'] ?? is_404(),
				'required_args' => [],
			],
			'is_preview'    => [
				'label'         => __( 'Is Preview', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if viewing a post preview.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_preview'] ?? is_preview(),
				'required_args' => [],
			],
			'is_paged'      => [
				'label'         => __( 'Is Paged', 'arraypress' ),
				'group'         => __( 'WordPress: Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a paginated page (page 2+).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_paged'] ?? is_paged(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get archive context conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_archive_conditions(): array {
		return [
			'is_archive'           => [
				'label'         => __( 'Is Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on any archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_archive'] ?? is_archive(),
				'required_args' => [],
			],
			'is_post_type_archive' => [
				'label'         => __( 'Is Post Type Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a custom post type archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_post_type_archive'] ?? is_post_type_archive(),
				'required_args' => [],
			],
			'is_category'          => [
				'label'         => __( 'Is Category Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a category archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_category'] ?? is_category(),
				'required_args' => [],
			],
			'is_tag'               => [
				'label'         => __( 'Is Tag Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a tag archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_tag'] ?? is_tag(),
				'required_args' => [],
			],
			'is_tax'               => [
				'label'         => __( 'Is Taxonomy Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a custom taxonomy archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_tax'] ?? is_tax(),
				'required_args' => [],
			],
			'is_author'            => [
				'label'         => __( 'Is Author Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on an author archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_author'] ?? is_author(),
				'required_args' => [],
			],
			'is_date'              => [
				'label'         => __( 'Is Date Archive', 'arraypress' ),
				'group'         => __( 'WordPress: Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a date-based archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_date'] ?? is_date(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get request type conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_request_type_conditions(): array {
		return [
			'is_admin'                => [
				'label'         => __( 'Is Admin Area', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if in the WordPress admin area.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_admin'] ?? is_admin(),
				'required_args' => [],
			],
			'is_ajax'                 => [
				'label'         => __( 'Is AJAX Request', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an AJAX request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_ajax'] ?? wp_doing_ajax(),
				'required_args' => [],
			],
			'is_rest'                 => [
				'label'         => __( 'Is REST Request', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a REST API request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_rest'] ?? ( defined( 'REST_REQUEST' ) && REST_REQUEST ),
				'required_args' => [],
			],
			'is_cron'                 => [
				'label'         => __( 'Is Cron Job', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a cron job execution.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_cron'] ?? wp_doing_cron(),
				'required_args' => [],
			],
			'is_feed'                 => [
				'label'         => __( 'Is Feed', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an RSS/Atom feed request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_feed'] ?? is_feed(),
				'required_args' => [],
			],
			'is_customizer_preview'   => [
				'label'         => __( 'Is Customizer Preview', 'arraypress' ),
				'group'         => __( 'WordPress: Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if viewing the customizer preview.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_customizer_preview'] ?? is_customize_preview(),
				'required_args' => [],
			],
		];
	}

}