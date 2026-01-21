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

/**
 * Class Geography
 *
 * Standardized geographic definitions for condition fields.
 */
class Geography {

	/**
	 * Continent codes mapping.
	 *
	 * IPInfo returns 2-letter codes, Countries library uses full names.
	 */
	private const CONTINENT_CODES = [
		'AF' => 'Africa',
		'AN' => 'Antarctica',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'NA' => 'North America',
		'OC' => 'Oceania',
		'SA' => 'South America',
	];

	/**
	 * Get continent options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_continents(): array {
		$options = [];

		foreach ( self::CONTINENT_CODES as $code => $name ) {
			$options[] = [
				'value' => $code,
				'label' => $name,
			];
		}

		return $options;
	}

	/**
	 * Get continent code from name.
	 *
	 * @param string $name Continent name.
	 *
	 * @return string|null Continent code or null if not found.
	 */
	public static function get_continent_code( string $name ): ?string {
		$flipped = array_flip( self::CONTINENT_CODES );

		return $flipped[ $name ] ?? null;
	}

	/**
	 * Get continent name from code.
	 *
	 * @param string $code Continent code.
	 *
	 * @return string|null Continent name or null if not found.
	 */
	public static function get_continent_name( string $code ): ?string {
		return self::CONTINENT_CODES[ strtoupper( $code ) ] ?? null;
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
		$countries = Countries::get_eu_countries();
		$options   = [];

		foreach ( $countries as $code => $name ) {
			$options[] = [
				'value' => $code,
				'label' => $name,
			];
		}

		return $options;
	}

	/**
	 * Get countries by continent.
	 *
	 * @param string $continent Continent name or code.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_countries_by_continent( string $continent ): array {
		// Convert code to name if needed
		$continent_name = self::CONTINENT_CODES[ strtoupper( $continent ) ] ?? $continent;

		$countries = Countries::get_by_continent( $continent_name );
		$options   = [];

		foreach ( $countries as $code => $name ) {
			$options[] = [
				'value' => $code,
				'label' => $name,
			];
		}

		return $options;
	}

	/**
	 * Get common high-risk country options for fraud detection.
	 *
	 * Countries commonly associated with higher fraud rates.
	 * This is not exhaustive and should be customized per business.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_high_risk_countries(): array {
		$high_risk_codes = [
			'BY', // Belarus
			'BD', // Bangladesh
			'BR', // Brazil
			'CN', // China
			'CO', // Colombia
			'EG', // Egypt
			'GH', // Ghana
			'ID', // Indonesia
			'IN', // India
			'MX', // Mexico
			'MY', // Malaysia
			'NG', // Nigeria
			'PH', // Philippines
			'PK', // Pakistan
			'RO', // Romania
			'RU', // Russia
			'TH', // Thailand
			'UA', // Ukraine
			'VE', // Venezuela
			'VN', // Vietnam
		];

		$options = [];

		foreach ( $high_risk_codes as $code ) {
			$name = Countries::get_name( $code );
			if ( $name !== $code ) {
				$options[] = [
					'value' => $code,
					'label' => $name,
				];
			}
		}

		return $options;
	}

	/**
	 * Get OFAC sanctioned country options.
	 *
	 * Countries under US Treasury OFAC comprehensive sanctions.
	 * Note: This list changes - verify against current OFAC SDN list.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_sanctioned_countries(): array {
		$sanctioned_codes = [
			'CU', // Cuba
			'IR', // Iran
			'KP', // North Korea
			'RU', // Russia (partial)
			'SY', // Syria
			'VE', // Venezuela (partial)
		];

		$options = [];

		foreach ( $sanctioned_codes as $code ) {
			$name = Countries::get_name( $code );
			if ( $name !== $code ) {
				$options[] = [
					'value' => $code,
					'label' => $name,
				];
			}
		}

		return $options;
	}

	/**
	 * Check if a country is in the EU.
	 *
	 * @param string $code Country code.
	 *
	 * @return bool
	 */
	public static function is_eu( string $code ): bool {
		return Countries::is_eu( $code );
	}

	/**
	 * Get country name by code.
	 *
	 * @param string $code Country code.
	 *
	 * @return string Country name or original code if not found.
	 */
	public static function get_country_name( string $code ): string {
		return Countries::get_name( $code );
	}

	/**
	 * Get country flag emoji.
	 *
	 * @param string $code Country code.
	 *
	 * @return string Flag emoji or empty string.
	 */
	public static function get_flag( string $code ): string {
		return Countries::get_flag( $code );
	}

	/**
	 * Get timezone options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_timezones(): array {
		$timezones = timezone_identifiers_list();
		$options   = [];

		foreach ( $timezones as $timezone ) {
			$options[] = [
				'value' => $timezone,
				'label' => str_replace( '_', ' ', $timezone ),
			];
		}

		return $options;
	}

	/**
	 * Get grouped timezone options (by region).
	 *
	 * @return array<string, array<array{value: string, label: string}>>
	 */
	public static function get_timezones_grouped(): array {
		$timezones = timezone_identifiers_list();
		$grouped   = [];

		foreach ( $timezones as $timezone ) {
			$parts  = explode( '/', $timezone, 2 );
			$region = $parts[0];
			$city   = isset( $parts[1] ) ? str_replace( '_', ' ', $parts[1] ) : $timezone;

			if ( ! isset( $grouped[ $region ] ) ) {
				$grouped[ $region ] = [];
			}

			$grouped[ $region ][] = [
				'value' => $timezone,
				'label' => $city,
			];
		}

		return $grouped;
	}

}