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

use ArrayPress\Conditions\Helpers\Context as ContextHelper;
use ArrayPress\Conditions\Options\WordPress;

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
			self::get_request_conditions(),
			self::get_environment_conditions()
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
				'label'         => __( 'Front Page', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the site front page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_front_page'] ?? is_front_page(),
				'required_args' => [],
			],
			'is_home'       => [
				'label'         => __( 'Blog Home', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the blog posts index page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_home'] ?? is_home(),
				'required_args' => [],
			],
			'is_singular'   => [
				'label'         => __( 'Singular', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on any single post, page, or custom post type.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_singular'] ?? is_singular(),
				'required_args' => [],
			],
			'is_single'     => [
				'label'         => __( 'Single Post', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a single post page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_single'] ?? is_single(),
				'required_args' => [],
			],
			'is_page'       => [
				'label'         => __( 'Page', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a static page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_page'] ?? is_page(),
				'required_args' => [],
			],
			'is_attachment' => [
				'label'         => __( 'Attachment', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on an attachment page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_attachment'] ?? is_attachment(),
				'required_args' => [],
			],
			'is_search'     => [
				'label'         => __( 'Search Results', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on the search results page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_search'] ?? is_search(),
				'required_args' => [],
			],
			'is_404'        => [
				'label'         => __( '404 Page', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a 404 error page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_404'] ?? is_404(),
				'required_args' => [],
			],
			'is_preview'    => [
				'label'         => __( 'Preview', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if viewing a post preview.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_preview'] ?? is_preview(),
				'required_args' => [],
			],
			'is_paged'      => [
				'label'         => __( 'Paged', 'arraypress' ),
				'group'         => __( 'Page', 'arraypress' ),
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
				'label'         => __( 'Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on any archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_archive'] ?? is_archive(),
				'required_args' => [],
			],
			'is_post_type_archive' => [
				'label'         => __( 'Post Type Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a custom post type archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_post_type_archive'] ?? is_post_type_archive(),
				'required_args' => [],
			],
			'is_category'          => [
				'label'         => __( 'Category Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a category archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_category'] ?? is_category(),
				'required_args' => [],
			],
			'is_tag'               => [
				'label'         => __( 'Tag Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a tag archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_tag'] ?? is_tag(),
				'required_args' => [],
			],
			'is_tax'               => [
				'label'         => __( 'Taxonomy Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a custom taxonomy archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_tax'] ?? is_tax(),
				'required_args' => [],
			],
			'is_author'            => [
				'label'         => __( 'Author Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on an author archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_author'] ?? is_author(),
				'required_args' => [],
			],
			'is_date'              => [
				'label'         => __( 'Date Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a date-based archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_date'] ?? is_date(),
				'required_args' => [],
			],
			'is_year'              => [
				'label'         => __( 'Year Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a yearly archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_year'] ?? is_year(),
				'required_args' => [],
			],
			'is_month'             => [
				'label'         => __( 'Month Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a monthly archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_month'] ?? is_month(),
				'required_args' => [],
			],
			'is_day'               => [
				'label'         => __( 'Day Archive', 'arraypress' ),
				'group'         => __( 'Archive', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if on a daily archive page.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_day'] ?? is_day(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get request type conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_request_conditions(): array {
		return [
			'is_admin'              => [
				'label'         => __( 'Admin Area', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if in the WordPress admin area.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_admin'] ?? is_admin(),
				'required_args' => [],
			],
			'is_network_admin'      => [
				'label'         => __( 'Network Admin', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if in the network admin area (multisite).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_network_admin'] ?? is_network_admin(),
				'required_args' => [],
			],
			'is_ajax'               => [
				'label'         => __( 'AJAX Request', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an AJAX request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_ajax'] ?? wp_doing_ajax(),
				'required_args' => [],
			],
			'is_rest'               => [
				'label'         => __( 'REST Request', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a REST API request.', 'arraypress' ),
				'compare_value' => fn( $args ) => ContextHelper::is_rest( $args ),
				'required_args' => [],
			],
			'is_cron'               => [
				'label'         => __( 'Cron Job', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a cron job execution.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_cron'] ?? wp_doing_cron(),
				'required_args' => [],
			],
			'is_cli'                => [
				'label'         => __( 'WP-CLI', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if running from WP-CLI command line.', 'arraypress' ),
				'compare_value' => fn( $args ) => ContextHelper::is_cli( $args ),
				'required_args' => [],
			],
			'is_feed'               => [
				'label'         => __( 'Feed', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an RSS/Atom feed request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_feed'] ?? is_feed(),
				'required_args' => [],
			],
			'is_customizer_preview' => [
				'label'         => __( 'Customizer Preview', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if viewing the customizer preview.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_customizer_preview'] ?? is_customize_preview(),
				'required_args' => [],
			],
			'is_embed'              => [
				'label'         => __( 'Embed', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is an oEmbed request.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_embed'] ?? is_embed(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get environment conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_environment_conditions(): array {
		return [
			'is_ssl'       => [
				'label'         => __( 'SSL/HTTPS', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the connection is using SSL/HTTPS.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_ssl'] ?? is_ssl(),
				'required_args' => [],
			],
			'is_multisite' => [
				'label'         => __( 'Multisite', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is a multisite installation.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_multisite'] ?? is_multisite(),
				'required_args' => [],
			],
			'is_main_site' => [
				'label'         => __( 'Main Site', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if this is the main site in a multisite network.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_main_site'] ?? is_main_site(),
				'required_args' => [],
			],
			'is_debug'     => [
				'label'         => __( 'Debug Mode', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if WP_DEBUG is enabled.', 'arraypress' ),
				'compare_value' => fn( $args ) => ContextHelper::is_debug( $args ),
				'required_args' => [],
			],
			'environment'  => [
				'label'         => __( 'Environment Type', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'select',
				'options'       => WordPress::get_environment_types(),
				'description'   => __( 'Check the WordPress environment type.', 'arraypress' ),
				'compare_value' => fn( $args ) => ContextHelper::get_environment( $args ),
				'required_args' => [],
			],
			'is_local'     => [
				'label'         => __( 'Local Environment', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if running in a local/development environment.', 'arraypress' ),
				'compare_value' => fn( $args ) => ContextHelper::is_local( $args ),
				'required_args' => [],
			],
			'is_rtl'       => [
				'label'         => __( 'RTL Language', 'arraypress' ),
				'group'         => __( 'Environment', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the current locale is right-to-left.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_rtl'] ?? is_rtl(),
				'required_args' => [],
			],
		];
	}

}