<?php
/**
 * Request Helper
 *
 * Provides utilities for retrieving request data from condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers\Data
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

use ArrayPress\Conditions\Helpers\Parse;
use ArrayPress\ReferrerUtils\Referrer;

/**
 * Class Request
 *
 * Utilities for retrieving request data in conditions.
 */
class Request {

	/**
	 * Get current URL.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_current_url( array $args = [] ): string {
		if ( isset( $args['current_url'] ) ) {
			return $args['current_url'];
		}

		$protocol = is_ssl() ? 'https://' : 'http://';
		$host     = $_SERVER['HTTP_HOST'] ?? '';
		$uri      = $_SERVER['REQUEST_URI'] ?? '';

		return $protocol . $host . $uri;
	}

	/**
	 * Check if the connection is using SSL/HTTPS.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_ssl( array $args = [] ): bool {
		return $args['is_ssl'] ?? is_ssl();
	}

	/**
	 * Get the HTTP request method.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_method( array $args = [] ): string {
		return $args['request_method'] ?? ( $_SERVER['REQUEST_METHOD'] ?? 'GET' );
	}

	/**
	 * Check if a cookie exists.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The cookie name to check.
	 *
	 * @return string The cookie name if it exists, empty string otherwise.
	 */
	public static function cookie_exists( array $args, ?string $user_value ): string {
		if ( empty( $user_value ) ) {
			return '';
		}

		return isset( $_COOKIE[ $user_value ] ) ? $user_value : '';
	}

	/**
	 * Get a cookie value.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The input in format "cookie_name:expected_value".
	 *
	 * @return string The cookie value or empty string.
	 */
	public static function get_cookie_value( array $args, ?string $user_value ): string {
		if ( empty( $user_value ) || ! str_contains( $user_value, ':' ) ) {
			return '';
		}

		$parsed = Parse::meta( $user_value );

		return $_COOKIE[ $parsed['key'] ] ?? '';
	}

	/**
	 * Get an HTTP header value.
	 *
	 * @param array       $args       The condition arguments.
	 * @param string|null $user_value The input in format "Header-Name:expected_value".
	 *
	 * @return string The header value or empty string.
	 */
	public static function get_header_value( array $args, ?string $user_value ): string {
		if ( empty( $user_value ) || ! str_contains( $user_value, ':' ) ) {
			return '';
		}

		$parsed      = Parse::meta( $user_value );
		$header_name = $parsed['key'];

		// Convert header name to $_SERVER format (e.g., Content-Type -> HTTP_CONTENT_TYPE)
		$server_key = 'HTTP_' . strtoupper( str_replace( '-', '_', $header_name ) );

		// Some headers don't have HTTP_ prefix
		if ( strtolower( $header_name ) === 'content-type' ) {
			$server_key = 'CONTENT_TYPE';
		} elseif ( strtolower( $header_name ) === 'content-length' ) {
			$server_key = 'CONTENT_LENGTH';
		}

		return $_SERVER[ $server_key ] ?? '';
	}

	/**
	 * Get a UTM parameter value.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string The UTM parameter value or empty string.
	 */
	public static function get_utm_parameter( array $args ): string {
		$parsed = Parse::text_unit( $args, 'source' );

		return Referrer::get_utm_parameter( $parsed['unit'] ) ?? '';
	}

	/**
	 * Get the raw user-agent string.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_user_agent( array $args = [] ): string {
		if ( isset( $args['user_agent'] ) ) {
			return (string) $args['user_agent'];
		}

		return (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' );
	}

	/**
	 * Whether the user-agent header is missing or empty.
	 *
	 * Legitimate browsers always send one. Empty UAs are usually scripts.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_user_agent_empty( array $args = [] ): bool {
		return trim( self::get_user_agent( $args ) ) === '';
	}

	/**
	 * Whether the user-agent looks like a headless browser or automation tool.
	 *
	 * Detects HeadlessChrome, PhantomJS, Selenium, Puppeteer, Playwright,
	 * Cypress, Nightmare, and similar automation framework signatures.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_user_agent_headless( array $args = [] ): bool {
		$ua = self::get_user_agent( $args );
		if ( $ua === '' ) {
			return false;
		}

		$signatures = [
			'HeadlessChrome',
			'PhantomJS',
			'SlimerJS',
			'Selenium',
			'WebDriver',
			'puppeteer',
			'Playwright',
			'Cypress',
			'Nightmare',
			'electron',
			'jsdom',
			'Lighthouse',
		];

		foreach ( $signatures as $sig ) {
			if ( stripos( $ua, $sig ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the user-agent advertises an outdated browser version.
	 *
	 * Heuristic only — version thresholds were chosen to flag genuinely
	 * abandoned releases without snagging modern enterprise builds.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_user_agent_outdated_browser( array $args = [] ): bool {
		$ua = self::get_user_agent( $args );
		if ( $ua === '' ) {
			return false;
		}

		// Internet Explorer (any version is now outdated).
		if ( preg_match( '/MSIE \d|Trident\//i', $ua ) ) {
			return true;
		}

		// Chrome < 90.
		if ( preg_match( '/Chrome\/(\d+)/i', $ua, $m ) && (int) $m[1] < 90 ) {
			// Edge and other Chromium variants advertise Chrome too — only
			// match when there's no Edg/OPR/Brave token alongside.
			if ( ! preg_match( '/Edg(e|A|iOS)?\/|OPR\/|YaBrowser\//i', $ua ) ) {
				return true;
			}
		}

		// Firefox < 78.
		if ( preg_match( '/Firefox\/(\d+)/i', $ua, $m ) && (int) $m[1] < 78 ) {
			return true;
		}

		// Safari < 13 (read the WebKit-style version token).
		if ( preg_match( '/Version\/(\d+).*Safari/i', $ua, $m ) && (int) $m[1] < 13 ) {
			return true;
		}

		return false;
	}

}