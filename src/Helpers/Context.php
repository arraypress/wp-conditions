<?php
/**
 * Context Helper
 *
 * Provides utilities for checking execution context.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class Context
 *
 * Utilities for checking execution context in conditions.
 */
class Context {

	/**
	 * Check if the current request is a REST API request.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_rest( array $args = [] ): bool {
		return $args['is_rest'] ?? ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Check if the current request is a CLI request.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_cli( array $args = [] ): bool {
		return $args['is_cli'] ?? ( defined( 'WP_CLI' ) && WP_CLI );
	}

	/**
	 * Check if WP_DEBUG is enabled.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_debug( array $args = [] ): bool {
		return $args['is_debug'] ?? ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}

	/**
	 * Check if this is a local/development environment.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_local( array $args = [] ): bool {
		if ( isset( $args['is_local'] ) ) {
			return $args['is_local'];
		}

		if ( function_exists( 'wp_get_environment_type' ) ) {
			return in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
		}

		return false;
	}

	/**
	 * Get the current environment type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string One of: local, development, staging, production
	 */
	public static function get_environment( array $args = [] ): string {
		if ( isset( $args['environment'] ) ) {
			return $args['environment'];
		}

		if ( function_exists( 'wp_get_environment_type' ) ) {
			return wp_get_environment_type();
		}

		return 'production';
	}

}