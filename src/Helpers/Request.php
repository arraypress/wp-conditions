<?php
/**
 * Request Helper
 *
 * Provides utilities for retrieving request data from condition arguments.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

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

		$utm = Referrer::get_utm_parameters();

		return $utm[ $parsed['unit'] ] ?? '';
	}

}