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

use ArrayPress\Conditions\Operators;
use ArrayPress\IPUtils\IP;
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
			self::get_device_conditions()
		);
	}

	/**
	 * Get URL-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_url_conditions(): array {
		return [
			'current_url'     => [
				'label'         => __( 'Current URL', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. /checkout/', 'arraypress' ),
				'description'   => __( 'Match against the current URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['current_url'] ?? self::get_current_url(),
				'required_args' => [],
			],
			'referrer'        => [
				'label'         => __( 'Referrer URL', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'Match against the HTTP referrer URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer'] ?? wp_get_referer(),
				'required_args' => [],
			],
			'query_var'       => [
				'label'         => __( 'Query Parameter', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. utm_source', 'arraypress' ),
				'description'   => __( 'Match against a URL query parameter value.', 'arraypress' ),
				'arg'           => 'query_var_value',
				'required_args' => [ 'query_var_name', 'query_var_value' ],
			],
			'accept_language' => [
				'label'         => __( 'Accept Language', 'arraypress' ),
				'group'         => __( 'Request: URL', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. en-US, de', 'arraypress' ),
				'description'   => __( 'Match against the browser\'s accept language header.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['accept_language'] ?? self::get_accept_language(),
				'required_args' => [],
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
				'multiple'      => false,
				'placeholder'   => __( 'Select device type...', 'arraypress' ),
				'description'   => __( 'Match against the visitor device type.', 'arraypress' ),
				'options'       => self::get_device_type_options(),
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
				'options'       => self::get_browser_options(),
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
				'options'       => self::get_os_options(),
				'compare_value' => fn( $args ) => $args['operating_system'] ?? UserAgent::get_os(),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get device type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_device_type_options(): array {
		return [
			[ 'value' => 'mobile', 'label' => __( 'Mobile', 'arraypress' ) ],
			[ 'value' => 'desktop', 'label' => __( 'Desktop', 'arraypress' ) ],
			[ 'value' => 'tablet', 'label' => __( 'Tablet', 'arraypress' ) ],
			[ 'value' => 'bot', 'label' => __( 'Bot/Crawler', 'arraypress' ) ],
		];
	}

	/**
	 * Get browser options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_browser_options(): array {
		return [
			[ 'value' => 'Chrome', 'label' => 'Chrome' ],
			[ 'value' => 'Firefox', 'label' => 'Firefox' ],
			[ 'value' => 'Safari', 'label' => 'Safari' ],
			[ 'value' => 'Edge', 'label' => 'Edge' ],
			[ 'value' => 'Opera', 'label' => 'Opera' ],
			[ 'value' => 'Internet Explorer', 'label' => 'Internet Explorer' ],
			[ 'value' => 'Samsung Browser', 'label' => 'Samsung Browser' ],
			[ 'value' => 'Brave', 'label' => 'Brave' ],
			[ 'value' => 'Vivaldi', 'label' => 'Vivaldi' ],
		];
	}

	/**
	 * Get operating system options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_os_options(): array {
		return [
			[ 'value' => 'Windows', 'label' => 'Windows' ],
			[ 'value' => 'Windows 10', 'label' => 'Windows 10' ],
			[ 'value' => 'Windows 11', 'label' => 'Windows 11' ],
			[ 'value' => 'macOS', 'label' => 'macOS' ],
			[ 'value' => 'Linux', 'label' => 'Linux' ],
			[ 'value' => 'Ubuntu', 'label' => 'Ubuntu' ],
			[ 'value' => 'iOS', 'label' => 'iOS' ],
			[ 'value' => 'Android', 'label' => 'Android' ],
			[ 'value' => 'Chrome OS', 'label' => 'Chrome OS' ],
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

	/**
	 * Get the browser's accept language.
	 *
	 * Returns the primary language code from the Accept-Language header.
	 *
	 * @return string|null
	 */
	private static function get_accept_language(): ?string {
		if ( empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			return null;
		}

		$accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

		// Parse the first language in the list
		$languages = explode( ',', $accept_language );
		if ( empty( $languages[0] ) ) {
			return null;
		}

		// Get just the language code (strip quality values like ;q=0.9)
		$primary = explode( ';', trim( $languages[0] ) )[0];

		return trim( $primary );
	}

}