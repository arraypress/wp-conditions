<?php
/**
 * Request Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\AcceptLanguageUtils\AcceptLanguage;
use ArrayPress\Conditions\Helpers\Request as RequestHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Options\Network;
use ArrayPress\IPUtils\IP;
use ArrayPress\ReferrerUtils\Referrer;
use ArrayPress\UserAgentUtils\UserAgent;

/**
 * Class Request
 *
 * Provides request/environment related conditions.
 */
class Request {

	/**
	 * Get all request conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// URL
			'current_url'          => [
				'label'         => __( 'Current URL', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. /checkout/', 'arraypress' ),
				'description'   => __( 'Match against the current URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => RequestHelper::get_current_url( $args ),
				'required_args' => [],
			],
			'query_var'            => [
				'label'         => __( 'Query Parameter', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. utm_source', 'arraypress' ),
				'description'   => __( 'Match against a URL query parameter value.', 'arraypress' ),
				'arg'           => 'query_var_value',
				'required_args' => [ 'query_var_name', 'query_var_value' ],
			],

			// Connection
			'request_method'       => [
				'label'         => __( 'Request Method', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select methods...', 'arraypress' ),
				'description'   => __( 'The HTTP request method.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => Network::get_request_methods(),
				'compare_value' => fn( $args ) => RequestHelper::get_method( $args ),
				'required_args' => [],
			],
			'cookie_exists'        => [
				'label'         => __( 'Cookie Exists', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. my_cookie_name', 'arraypress' ),
				'description'   => __( 'Check if a specific cookie exists.', 'arraypress' ),
				'operators'     => Operators::contains(),
				'compare_value' => fn( $args, $user_value ) => RequestHelper::cookie_exists( $args, $user_value ) ? $user_value : '',
				'required_args' => [],
			],
			'cookie_value'         => [
				'label'         => __( 'Cookie Value', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'cookie_name:expected_value', 'arraypress' ),
				'description'   => __( 'Match against a cookie value. Format: cookie_name:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => RequestHelper::get_cookie_value( $args, $user_value ),
				'required_args' => [],
			],
			'header_value'         => [
				'label'         => __( 'HTTP Header', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'Header-Name:expected_value', 'arraypress' ),
				'description'   => __( 'Match against an HTTP header value. Format: Header-Name:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => RequestHelper::get_header_value( $args, $user_value ),
				'required_args' => [],
			],

			// Visitor
			'ip_address'           => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'ip',
				'placeholder'   => __( 'e.g. 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'Match against the visitor IP address. Supports exact match, CIDR notation, and wildcards.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['ip_address'] ?? IP::get(),
				'required_args' => [],
			],
			'country'              => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match against the visitor country (requires Cloudflare or geo-IP service).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => function_exists( 'get_country_options' ) ? get_country_options() : [],
				'compare_value' => fn( $args ) => $args['country'] ?? IP::get_country(),
				'required_args' => [],
			],
			'accept_language'      => [
				'label'         => __( 'Browser Language', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select languages...', 'arraypress' ),
				'description'   => __( 'Match against the browser\'s preferred language.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => AcceptLanguage::get_common_languages( true ),
				'compare_value' => fn( $args ) => $args['accept_language'] ?? AcceptLanguage::get_primary(),
				'required_args' => [],
			],

			// Device
			'device_type'          => [
				'label'         => __( 'Device Type', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select device types...', 'arraypress' ),
				'description'   => __( 'Match against the visitor device type.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_device_types( true ),
				'compare_value' => fn( $args ) => $args['device_type'] ?? UserAgent::get_device_type(),
				'required_args' => [],
			],
			'browser'              => [
				'label'         => __( 'Browser', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select browsers...', 'arraypress' ),
				'description'   => __( 'Match against the visitor browser.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_browsers( true ),
				'compare_value' => fn( $args ) => $args['browser'] ?? UserAgent::get_browser(),
				'required_args' => [],
			],
			'operating_system'     => [
				'label'         => __( 'Operating System', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select operating systems...', 'arraypress' ),
				'description'   => __( 'Match against the visitor operating system.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_operating_systems( true ),
				'compare_value' => fn( $args ) => $args['operating_system'] ?? UserAgent::get_os(),
				'required_args' => [],
			],
			'is_bot'               => [
				'label'         => __( 'Is Bot/Crawler', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor is a bot or web crawler.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_bot'] ?? UserAgent::is_bot(),
				'required_args' => [],
			],

			// Referrer
			'referrer_url'         => [
				'label'         => __( 'Referrer URL', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. example.com/page', 'arraypress' ),
				'description'   => __( 'Match against the full HTTP referrer URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer_url'] ?? Referrer::get(),
				'required_args' => [],
			],
			'referrer_domain'      => [
				'label'         => __( 'Referrer Domain', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'Match against the referrer root domain.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer_domain'] ?? Referrer::get_root_domain(),
				'required_args' => [],
			],
			'traffic_source'       => [
				'label'         => __( 'Traffic Source', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select traffic sources...', 'arraypress' ),
				'description'   => __( 'Match against the type of traffic source.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_traffic_source_options( true ),
				'compare_value' => fn( $args ) => $args['traffic_source'] ?? Referrer::get_traffic_source(),
				'required_args' => [],
			],
			'search_engine'        => [
				'label'         => __( 'Search Engine', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select search engines...', 'arraypress' ),
				'description'   => __( 'Match against the search engine the visitor came from.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_search_engine_options( true ),
				'compare_value' => fn( $args ) => $args['search_engine'] ?? Referrer::get_search_engine(),
				'required_args' => [],
			],
			'social_platform'      => [
				'label'         => __( 'Social Platform', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select social platforms...', 'arraypress' ),
				'description'   => __( 'Match against the social media platform the visitor came from.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_social_platform_options( true ),
				'compare_value' => fn( $args ) => $args['social_platform'] ?? Referrer::get_social_platform(),
				'required_args' => [],
			],
			'search_terms'         => [
				'label'         => __( 'Search Terms', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. wordpress plugins', 'arraypress' ),
				'description'   => __( 'Match against the search terms used to find your site.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['search_terms'] ?? Referrer::get_search_terms(),
				'required_args' => [],
			],
			'has_referrer'         => [
				'label'         => __( 'Has Referrer', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor has any referrer (not direct traffic).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['has_referrer'] ?? Referrer::is_valid(),
				'required_args' => [],
			],
			'is_external_referrer' => [
				'label'         => __( 'Is External Referrer', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor came from an external website.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_external_referrer'] ?? Referrer::is_external(),
				'required_args' => [],
			],

			// UTM
			'utm_parameter'        => [
				'label'         => __( 'UTM Parameter', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text_unit',
				'placeholder'   => __( 'e.g. newsletter, google, spring_sale', 'arraypress' ),
				'description'   => __( 'Match against a UTM parameter value.', 'arraypress' ),
				'units'         => fn() => Referrer::get_utm_parameter_options( true ),
				'compare_value' => fn( $args ) => RequestHelper::get_utm_parameter( $args ),
				'required_args' => [],
			],
		];
	}

}