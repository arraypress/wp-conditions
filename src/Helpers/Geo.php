<?php
/**
 * Geo Helper
 *
 * Geographic comparison helpers for fraud-detection conditions.
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
 * Class Geo
 *
 * Utilities for geo-mismatch conditions.
 */
class Geo {

	/**
	 * Whether the visitor IP country matches the billing country.
	 *
	 * Returns false when either value is missing — most callers want a
	 * mismatch to look like "no, they don't match" rather than skip.
	 *
	 * @param array $args Condition args (`ip_country`, `billing_country`).
	 *
	 * @return bool
	 */
	public static function ip_matches_billing( array $args ): bool {
		$ip      = self::normalize( $args['ip_country'] ?? '' );
		$billing = self::normalize( $args['billing_country'] ?? '' );

		if ( $ip === '' || $billing === '' ) {
			return false;
		}

		return $ip === $billing;
	}

	/**
	 * Whether the issuing-card country matches the billing country.
	 *
	 * @param array $args Condition args (`card_country`, `billing_country`).
	 *
	 * @return bool
	 */
	public static function card_matches_billing( array $args ): bool {
		$card    = self::normalize( $args['card_country'] ?? '' );
		$billing = self::normalize( $args['billing_country'] ?? '' );

		if ( $card === '' || $billing === '' ) {
			return false;
		}

		return $card === $billing;
	}

	/**
	 * Get the IP-to-billing distance in kilometres.
	 *
	 * Caller must pre-compute via a geo-IP service and pass through `$args`,
	 * since this library does not perform geo-IP lookups itself.
	 *
	 * @param array $args Condition args (`geo_distance_km`).
	 *
	 * @return float
	 */
	public static function get_distance_km( array $args ): float {
		return (float) ( $args['geo_distance_km'] ?? 0.0 );
	}

	/**
	 * Compute great-circle distance between two coordinate pairs.
	 *
	 * Handy for callers that want to derive `geo_distance_km` from raw lat/lng
	 * pairs returned by IPInfo / IPQS / ProxyCheck.
	 *
	 * @param float $lat1 Source latitude.
	 * @param float $lng1 Source longitude.
	 * @param float $lat2 Target latitude.
	 * @param float $lng2 Target longitude.
	 *
	 * @return float Distance in kilometres.
	 */
	public static function haversine( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
		$earth_km = 6371.0;
		$d_lat    = deg2rad( $lat2 - $lat1 );
		$d_lng    = deg2rad( $lng2 - $lng1 );

		$a = sin( $d_lat / 2 ) ** 2
			+ cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $d_lng / 2 ) ** 2;

		$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

		return round( $earth_km * $c, 2 );
	}

	/**
	 * Normalize an ISO-2 country code (uppercase, trimmed).
	 *
	 * @param mixed $value Raw value from args.
	 *
	 * @return string
	 */
	private static function normalize( $value ): string {
		return strtoupper( trim( (string) $value ) );
	}

}
