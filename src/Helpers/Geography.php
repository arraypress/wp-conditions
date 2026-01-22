<?php
/**
 * Geography Helper
 *
 * Provides standardized geographic options for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

use ArrayPress\Countries\Countries;
use ArrayPress\Timezones\Timezones;

/**
 * Class Geography
 *
 * Standardized geographic definitions for condition fields.
 */
class Geography {

	/**
	 * Get continent options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_continents(): array {
		return Countries::get_continent_options();
	}

	/**
	 * Get country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_countries(): array {
		return Countries::get_value_label_options();
	}

	/**
	 * Get timezone options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_timezones(): array {
		return Timezones::get_value_label_options();
	}

}