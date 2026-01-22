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
	 * Get EU country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_eu_countries(): array {
		return Countries::get_eu_options();
	}

	/**
	 * Get countries by continent.
	 *
	 * @param string $continent Continent name or code.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_countries_by_continent( string $continent ): array {
		return Countries::get_by_continent_options( $continent );
	}

	/**
	 * Get high-risk country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_high_risk_countries(): array {
		return Countries::get_high_risk_options();
	}

	/**
	 * Get sanctioned country options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_sanctioned_countries(): array {
		return Countries::get_sanctioned_options();
	}

	/**
	 * Get timezone options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_timezones(): array {
		return Timezones::get_value_label_options();
	}

	/**
	 * Get timezone options with UTC offset.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_timezones_with_offset(): array {
		return Timezones::get_options_with_offset();
	}

	/**
	 * Get grouped timezone options (by region).
	 *
	 * @return array<string, array<array{value: string, label: string}>>
	 */
	public static function get_timezones_grouped(): array {
		return Timezones::get_grouped_options();
	}

	/**
	 * Get timezone region options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_timezone_regions(): array {
		return Timezones::get_region_options();
	}

}