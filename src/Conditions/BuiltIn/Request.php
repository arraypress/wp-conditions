<?php
/**
 * Request Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

use ArrayPress\AcceptLanguageUtils\AcceptLanguage;
use ArrayPress\Conditions\Operators;
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
		return array_merge(
			self::get_url_conditions(),
			self::get_visitor_conditions(),
			self::get_device_conditions(),
			self::get_language_conditions(),
			self::get_referrer_conditions()
		);
	}

	/**
	 * Get URL-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_url_conditions(): array {
		return [
			'current_url' => [
				'label'         => __( 'Current URL', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. /checkout/', 'arraypress' ),
				'description'   => __( 'Match against the current URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['current_url'] ?? self::get_current_url(),
				'required_args' => [],
			],
			'query_var'   => [
				'label'         => __( 'Query Parameter', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. utm_source', 'arraypress' ),
				'description'   => __( 'Match against a URL query parameter value.', 'arraypress' ),
				'arg'           => 'query_var_value',
				'required_args' => [ 'query_var_name', 'query_var_value' ],
			],
		];
	}

	/**
	 * Get visitor-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_visitor_conditions(): array {
		return [
			'ip_address' => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'Request: Visitor', 'arraypress' ),
				'type'          => 'ip',
				'placeholder'   => __( 'e.g. 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'Match against the visitor IP address. Supports exact match, CIDR notation, and wildcards.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['ip_address'] ?? IP::get(),
				'required_args' => [],
			],
			'country'    => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Request: Visitor', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match against the visitor country (requires Cloudflare or geo-IP service).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => function_exists( 'get_country_options' ) ? get_country_options() : [],
				'compare_value' => fn( $args ) => $args['country'] ?? IP::get_country(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get device-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_device_conditions(): array {
		return [
			'device_type'      => [
				'label'         => __( 'Device Type', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select device types...', 'arraypress' ),
				'description'   => __( 'Match against the visitor device type.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_device_types( true ),
				'compare_value' => fn( $args ) => $args['device_type'] ?? UserAgent::get_device_type(),
				'required_args' => [],
			],
			'browser'          => [
				'label'         => __( 'Browser', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select browsers...', 'arraypress' ),
				'description'   => __( 'Match against the visitor browser.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_browsers( true ),
				'compare_value' => fn( $args ) => $args['browser'] ?? UserAgent::get_browser(),
				'required_args' => [],
			],
			'operating_system' => [
				'label'         => __( 'Operating System', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select operating systems...', 'arraypress' ),
				'description'   => __( 'Match against the visitor operating system.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => UserAgent::get_operating_systems( true ),
				'compare_value' => fn( $args ) => $args['operating_system'] ?? UserAgent::get_os(),
				'required_args' => [],
			],
			'is_mobile'        => [
				'label'         => __( 'Is Mobile', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor is using a mobile phone (not tablet).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_mobile'] ?? UserAgent::is_mobile(),
				'required_args' => [],
			],
			'is_tablet'        => [
				'label'         => __( 'Is Tablet', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor is using a tablet device.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_tablet'] ?? UserAgent::is_tablet(),
				'required_args' => [],
			],
			'is_desktop'       => [
				'label'         => __( 'Is Desktop', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor is using a desktop computer.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_desktop'] ?? UserAgent::is_desktop(),
				'required_args' => [],
			],
			'is_bot'           => [
				'label'         => __( 'Is Bot/Crawler', 'arraypress' ),
				'group'         => __( 'Request: Device', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor is a bot or web crawler.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_bot'] ?? UserAgent::is_bot(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get language-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_language_conditions(): array {
		return [
			'accept_language'         => [
				'label'         => __( 'Preferred Language', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select languages...', 'arraypress' ),
				'description'   => __( 'Match against the browser\'s primary preferred language.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => AcceptLanguage::get_common_languages( true ),
				'compare_value' => fn( $args ) => $args['accept_language'] ?? AcceptLanguage::get_primary(),
				'required_args' => [],
			],
			'accept_language_base'    => [
				'label'         => __( 'Preferred Language (Base)', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select languages...', 'arraypress' ),
				'description'   => __( 'Match against the browser\'s primary language code without region (e.g., "en" instead of "en-US").', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => AcceptLanguage::get_base_languages( true ),
				'compare_value' => fn( $args ) => $args['accept_language_base'] ?? AcceptLanguage::get_primary_language(),
				'required_args' => [],
			],
			'accepts_language'        => [
				'label'         => __( 'Accepts Language', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select languages...', 'arraypress' ),
				'description'   => __( 'Check if any of the selected languages appear in the browser\'s accepted languages list.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => AcceptLanguage::get_common_languages( true ),
				'compare_value' => fn( $args ) => $args['accepted_languages'] ?? AcceptLanguage::get_all(),
				'required_args' => [],
			],
			'accept_language_region'  => [
				'label'         => __( 'Language Region', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. US, GB, DE', 'arraypress' ),
				'description'   => __( 'Match against the region code from the preferred language (e.g., "US" from "en-US").', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['accept_language_region'] ?? AcceptLanguage::get_primary_region(),
				'required_args' => [],
			],
			'is_rtl_language'         => [
				'label'         => __( 'Is RTL Language', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor prefers a right-to-left language (Arabic, Hebrew, etc.).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_rtl_language'] ?? AcceptLanguage::is_rtl(),
				'required_args' => [],
			],
			'accept_language_count'   => [
				'label'         => __( 'Accepted Languages Count', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of languages in the browser\'s Accept-Language header.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['accept_language_count'] ?? count( AcceptLanguage::get_all() ),
				'required_args' => [],
			],
			'accept_language_quality' => [
				'label'         => __( 'Language Quality Value', 'arraypress' ),
				'group'         => __( 'Request: Language', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 0.8', 'arraypress' ),
				'min'           => 0,
				'max'           => 1,
				'step'          => 0.1,
				'description'   => __( 'The quality value (0-1) of the primary language preference.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$primary = AcceptLanguage::get_primary();

					return $primary ? ( AcceptLanguage::get_quality( $primary ) ?? 1.0 ) : 0;
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get referrer-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_referrer_conditions(): array {
		return [
			'referrer_url'             => [
				'label'         => __( 'Referrer URL', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. example.com/page', 'arraypress' ),
				'description'   => __( 'Match against the full HTTP referrer URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer_url'] ?? Referrer::get(),
				'required_args' => [],
			],
			'referrer_domain'          => [
				'label'         => __( 'Referrer Domain', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'Match against the referrer domain (e.g., "www.google.com").', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer_domain'] ?? Referrer::get_domain(),
				'required_args' => [],
			],
			'referrer_root_domain'     => [
				'label'         => __( 'Referrer Root Domain', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'Match against the referrer root domain without subdomains (e.g., "google.com").', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer_root_domain'] ?? Referrer::get_root_domain(),
				'required_args' => [],
			],
			'traffic_source'           => [
				'label'         => __( 'Traffic Source Type', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select traffic sources...', 'arraypress' ),
				'description'   => __( 'Match against the type of traffic source.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_traffic_source_options( true ),
				'compare_value' => fn( $args ) => $args['traffic_source'] ?? Referrer::get_traffic_source(),
				'required_args' => [],
			],
			'search_engine'            => [
				'label'         => __( 'Search Engine', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select search engines...', 'arraypress' ),
				'description'   => __( 'Match against the search engine the visitor came from.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_search_engine_options( true ),
				'compare_value' => fn( $args ) => $args['search_engine'] ?? Referrer::get_search_engine(),
				'required_args' => [],
			],
			'social_platform'          => [
				'label'         => __( 'Social Platform', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select social platforms...', 'arraypress' ),
				'description'   => __( 'Match against the social media platform the visitor came from.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Referrer::get_social_platform_options( true ),
				'compare_value' => fn( $args ) => $args['social_platform'] ?? Referrer::get_social_platform(),
				'required_args' => [],
			],
			'search_terms'             => [
				'label'         => __( 'Search Terms', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. wordpress plugins', 'arraypress' ),
				'description'   => __( 'Match against the search terms used to find your site (when available).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['search_terms'] ?? Referrer::get_search_terms(),
				'required_args' => [],
			],
			'is_external_referrer'     => [
				'label'         => __( 'Is External Referrer', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor came from an external website.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_external_referrer'] ?? Referrer::is_external(),
				'required_args' => [],
			],
			'is_search_engine'         => [
				'label'         => __( 'Is From Search Engine', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor came from any search engine.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_search_engine'] ?? Referrer::is_search_engine(),
				'required_args' => [],
			],
			'is_social_referrer'       => [
				'label'         => __( 'Is From Social Media', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor came from any social media platform.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['is_social_referrer'] ?? Referrer::is_social(),
				'required_args' => [],
			],
			'has_referrer'             => [
				'label'         => __( 'Has Referrer', 'arraypress' ),
				'group'         => __( 'Request: Referrer', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the visitor has any referrer (not direct traffic).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['has_referrer'] ?? Referrer::is_valid(),
				'required_args' => [],
			],
			'utm_source'               => [
				'label'         => __( 'UTM Source', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. newsletter, google', 'arraypress' ),
				'description'   => __( 'Match against the utm_source parameter.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['utm_source'] ) ) {
						return $args['utm_source'];
					}
					$utm = Referrer::get_utm_parameters();

					return $utm['source'];
				},
				'required_args' => [],
			],
			'utm_medium'               => [
				'label'         => __( 'UTM Medium', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. email, cpc, social', 'arraypress' ),
				'description'   => __( 'Match against the utm_medium parameter.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['utm_medium'] ) ) {
						return $args['utm_medium'];
					}
					$utm = Referrer::get_utm_parameters();

					return $utm['medium'];
				},
				'required_args' => [],
			],
			'utm_campaign'             => [
				'label'         => __( 'UTM Campaign', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. spring_sale, product_launch', 'arraypress' ),
				'description'   => __( 'Match against the utm_campaign parameter.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['utm_campaign'] ) ) {
						return $args['utm_campaign'];
					}
					$utm = Referrer::get_utm_parameters();

					return $utm['campaign'];
				},
				'required_args' => [],
			],
			'utm_term'                 => [
				'label'         => __( 'UTM Term', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. running+shoes', 'arraypress' ),
				'description'   => __( 'Match against the utm_term parameter (paid search keywords).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['utm_term'] ) ) {
						return $args['utm_term'];
					}
					$utm = Referrer::get_utm_parameters();

					return $utm['term'];
				},
				'required_args' => [],
			],
			'utm_content'              => [
				'label'         => __( 'UTM Content', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. logolink, textlink', 'arraypress' ),
				'description'   => __( 'Match against the utm_content parameter (A/B testing).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['utm_content'] ) ) {
						return $args['utm_content'];
					}
					$utm = Referrer::get_utm_parameters();

					return $utm['content'];
				},
				'required_args' => [],
			],
			'has_utm_parameters'       => [
				'label'         => __( 'Has UTM Parameters', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the referrer has any UTM tracking parameters.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['has_utm_parameters'] ) ) {
						return $args['has_utm_parameters'];
					}
					$utm = Referrer::get_utm_parameters();

					return ! empty( $utm['source'] ) || ! empty( $utm['medium'] ) || ! empty( $utm['campaign'] );
				},
				'required_args' => [],
			],
			'campaign_source'          => [
				'label'         => __( 'Campaign Source', 'arraypress' ),
				'group'         => __( 'Request: UTM Parameters', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google, facebook', 'arraypress' ),
				'description'   => __( 'Match against the campaign source (utm_source).', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['campaign_source'] ?? Referrer::get_campaign_source(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	private static function get_current_url(): string {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host     = $_SERVER['HTTP_HOST'] ?? '';
		$uri      = $_SERVER['REQUEST_URI'] ?? '';

		return $protocol . $host . $uri;
	}

}