<?php
/**
 * Request Built-in Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn;

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
		return [
			'current_url'      => [
				'label'         => __( 'Current URL', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. /checkout/', 'arraypress' ),
				'description'   => __( 'Match against the current URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['current_url'] ?? self::get_current_url(),
				'required_args' => [],
			],
			'referrer'         => [
				'label'         => __( 'Referrer URL', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'Match against the HTTP referrer URL.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['referrer'] ?? wp_get_referer(),
				'required_args' => [],
			],
			'query_var'        => [
				'label'         => __( 'Query Parameter', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. utm_source', 'arraypress' ),
				'description'   => __( 'Match against a URL query parameter value.', 'arraypress' ),
				'arg'           => 'query_var_value',
				'required_args' => [ 'query_var_name', 'query_var_value' ],
			],
			'accept_language'  => [
				'label'         => __( 'Accept Language', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. en-US, de', 'arraypress' ),
				'description'   => __( 'Match against the browser\'s accept language header.', 'arraypress' ),
				'compare_value' => fn( $args ) => $args['accept_language'] ?? self::get_accept_language(),
				'required_args' => [],
			],
			'ip_address'       => [
				'label'         => __( 'IP Address', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 192.168.1.0/24', 'arraypress' ),
				'description'   => __( 'Match against the visitor IP address. Supports CIDR notation.', 'arraypress' ),
				'operators'     => [
					'=='       => __( 'Is', 'arraypress' ),
					'!='       => __( 'Is not', 'arraypress' ),
					'contains' => __( 'In range', 'arraypress' ),
				],
				'compare_value' => fn( $args ) => $args['ip_address'] ?? ( class_exists( IP::class ) ? IP::get() : self::get_ip_fallback() ),
				'required_args' => [],
			],
			'country'          => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match against the visitor country (requires Cloudflare or geo-IP service).', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Is any of', 'arraypress' ),
					'none' => __( 'Is none of', 'arraypress' ),
				],
				'options'       => fn() => function_exists( 'get_country_options' ) ? get_country_options() : [],
				'compare_value' => fn( $args ) => $args['country'] ?? ( class_exists( IP::class ) ? IP::get_country() : null ),
				'required_args' => [],
			],
			'device_type'      => [
				'label'         => __( 'Device Type', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => false,
				'placeholder'   => __( 'Select device type...', 'arraypress' ),
				'description'   => __( 'Match against the visitor device type.', 'arraypress' ),
				'operators'     => [
					'==' => __( 'Is', 'arraypress' ),
					'!=' => __( 'Is not', 'arraypress' ),
				],
				'options'       => [
					[ 'value' => 'mobile', 'label' => __( 'Mobile', 'arraypress' ) ],
					[ 'value' => 'desktop', 'label' => __( 'Desktop', 'arraypress' ) ],
					[ 'value' => 'bot', 'label' => __( 'Bot/Crawler', 'arraypress' ) ],
				],
				'compare_value' => fn( $args ) => $args['device_type'] ?? ( class_exists( UserAgent::class ) ? UserAgent::get_device_type() : self::get_device_type_fallback() ),
				'required_args' => [],
			],
			'browser'          => [
				'label'         => __( 'Browser', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select browsers...', 'arraypress' ),
				'description'   => __( 'Match against the visitor browser.', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Is any of', 'arraypress' ),
					'none' => __( 'Is none of', 'arraypress' ),
				],
				'options'       => [
					[ 'value' => 'Chrome', 'label' => 'Chrome' ],
					[ 'value' => 'Firefox', 'label' => 'Firefox' ],
					[ 'value' => 'Safari', 'label' => 'Safari' ],
					[ 'value' => 'Edge', 'label' => 'Edge' ],
					[ 'value' => 'Opera', 'label' => 'Opera' ],
					[ 'value' => 'Internet Explorer', 'label' => 'Internet Explorer' ],
					[ 'value' => 'Samsung Browser', 'label' => 'Samsung Browser' ],
				],
				'compare_value' => fn( $args ) => $args['browser'] ?? ( class_exists( UserAgent::class ) ? UserAgent::get_browser() : null ),
				'required_args' => [],
			],
			'operating_system' => [
				'label'         => __( 'Operating System', 'arraypress' ),
				'group'         => __( 'Request', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select operating systems...', 'arraypress' ),
				'description'   => __( 'Match against the visitor operating system.', 'arraypress' ),
				'operators'     => [
					'any'  => __( 'Is any of', 'arraypress' ),
					'none' => __( 'Is none of', 'arraypress' ),
				],
				'options'       => [
					[ 'value' => 'Windows', 'label' => 'Windows' ],
					[ 'value' => 'Windows 10', 'label' => 'Windows 10' ],
					[ 'value' => 'Windows 11', 'label' => 'Windows 11' ],
					[ 'value' => 'macOS', 'label' => 'macOS' ],
					[ 'value' => 'Linux', 'label' => 'Linux' ],
					[ 'value' => 'iOS', 'label' => 'iOS' ],
					[ 'value' => 'Android', 'label' => 'Android' ],
					[ 'value' => 'Chrome OS', 'label' => 'Chrome OS' ],
				],
				'compare_value' => fn( $args ) => $args['operating_system'] ?? ( class_exists( UserAgent::class ) ? UserAgent::get_os() : null ),
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

	/**
	 * Fallback IP detection if IP utils library not available.
	 *
	 * @return string|null
	 */
	private static function get_ip_fallback(): ?string {
		$headers = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_REAL_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'REMOTE_ADDR',
		];

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = $_SERVER[ $header ];
				if ( $header === 'HTTP_X_FORWARDED_FOR' ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return null;
	}

	/**
	 * Fallback device type detection if UserAgent library not available.
	 *
	 * @return string
	 */
	private static function get_device_type_fallback(): string {
		if ( wp_is_mobile() ) {
			return 'mobile';
		}

		return 'desktop';
	}

}
