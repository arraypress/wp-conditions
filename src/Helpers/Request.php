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

/**
 * Class Request
 *
 * Utilities for retrieving request data in conditions.
 */
class Request {

	/**
	 * Get current URL.
	 *
	 * @return string
	 */
	public static function get_current_url(): string {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host     = $_SERVER['HTTP_HOST'] ?? '';
		$uri      = $_SERVER['REQUEST_URI'] ?? '';

		return $protocol . $host . $uri;
	}

}