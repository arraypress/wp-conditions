<?php
/**
 * WordPress Context Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

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
		return [
			'is_front_page' => [
				'label'         => __( 'Is Front Page', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the site front page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_front_page'] ?? is_front_page(),
				'required_args' => [],
			],
			'is_home'       => [
				'label'         => __( 'Is Blog Home', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the blog posts index page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_home'] ?? is_home(),
				'required_args' => [],
			],
			'is_single'     => [
				'label'         => __( 'Is Single Post', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a single post page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_single'] ?? is_single(),
				'required_args' => [],
			],
			'is_page'       => [
				'label'         => __( 'Is Page', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a static page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_page'] ?? is_page(),
				'required_args' => [],
			],
			'is_archive'    => [
				'label'         => __( 'Is Archive', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on an archive page (category, tag, date, author).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_archive'] ?? is_archive(),
				'required_args' => [],
			],
			'is_search'     => [
				'label'         => __( 'Is Search Results', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the search results page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_search'] ?? is_search(),
				'required_args' => [],
			],
			'is_404'        => [
				'label'         => __( 'Is 404 Page', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a 404 error page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_404'] ?? is_404(),
				'required_args' => [],
			],
			'is_admin'      => [
				'label'         => __( 'Is Admin Area', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if in the WordPress admin area.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_admin'] ?? is_admin(),
				'required_args' => [],
			],
			'is_ajax'       => [
				'label'         => __( 'Is AJAX Request', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an AJAX request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_ajax'] ?? wp_doing_ajax(),
				'required_args' => [],
			],
			'is_rest'       => [
				'label'         => __( 'Is REST Request', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a REST API request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_rest'] ?? ( defined( 'REST_REQUEST' ) && REST_REQUEST ),
				'required_args' => [],
			],
			'is_cron'       => [
				'label'         => __( 'Is Cron Job', 'arraypress' ),
				'group'         => __( 'WordPress', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a cron job execution.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_cron'] ?? wp_doing_cron(),
				'required_args' => [],
			],
		];
	}

}
