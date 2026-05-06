<?php
/**
 * AbuseIPDB Helper
 *
 * Bridges the `arraypress/abuseipdb` Client into the conditions
 * runtime. Lazy-instantiates one Client per request (driven off
 * the `abuseipdb_api_key` arg the host plugin provides), then
 * memoises lookups per IP across condition evaluations in the same
 * pass.
 *
 * @package     ArrayPress\Conditions\Clients
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Clients;

use ArrayPress\AbuseIPDB\Client;
use ArrayPress\AbuseIPDB\Response\Check;

class AbuseIPDB {

	/**
	 * Cached client instance.
	 *
	 * @var Client|null
	 */
	private static ?Client $client = null;

	/**
	 * Cached Check responses keyed by IP.
	 *
	 * @var array<string, Check|null>
	 */
	private static array $results = [];

	/**
	 * Get (or build) the AbuseIPDB client.
	 *
	 * @param array $args Condition args.
	 *
	 * @return Client|null Null when no API key.
	 */
	public static function get_client( array $args ): ?Client {
		$key = (string) ( $args['abuseipdb_api_key'] ?? '' );
		if ( $key === '' ) {
			return null;
		}

		if ( self::$client === null ) {
			self::$client = new Client( $key );
		}

		return self::$client;
	}

	/**
	 * Run (or fetch cached) the Check for the request IP.
	 *
	 * @param array $args Condition args.
	 *
	 * @return Check|null
	 */
	public static function get_check( array $args ): ?Check {
		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return null;
		}

		if ( array_key_exists( $ip, self::$results ) ) {
			return self::$results[ $ip ];
		}

		$client = self::get_client( $args );
		if ( ! $client ) {
			self::$results[ $ip ] = null;

			return null;
		}

		$result = $client->check_ip( $ip );
		$check  = $result instanceof Check ? $result : null;

		self::$results[ $ip ] = $check;

		return $check;
	}

	/**
	 * Confidence score (0-100), or 0 when unavailable.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function get_confidence_score( array $args ): int {
		$check = self::get_check( $args );

		return $check ? $check->get_confidence_score() : 0;
	}

	/**
	 * Convenience boolean — true when score ≥ 75 (AbuseIPDB's
	 * documented "high confidence" cutoff).
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function is_abusive( array $args ): bool {
		$check = self::get_check( $args );

		return $check ? $check->is_abusive( 75 ) : false;
	}

	/**
	 * Total number of distinct community reports.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function get_total_reports( array $args ): int {
		$check = self::get_check( $args );

		return $check ? $check->get_total_reports() : 0;
	}

	/**
	 * ISO-2 country code for the IP, or empty.
	 *
	 * @param array $args Condition args.
	 *
	 * @return string
	 */
	public static function get_country( array $args ): string {
		$check = self::get_check( $args );

		return $check ? $check->get_country_code() : '';
	}

	/**
	 * Usage type — "Data Center/Web Hosting/Transit", "Fixed Line
	 * ISP", etc.
	 *
	 * @param array $args Condition args.
	 *
	 * @return string
	 */
	public static function get_usage_type( array $args ): string {
		$check = self::get_check( $args );

		return $check ? $check->get_usage_type() : '';
	}

	/**
	 * True when AbuseIPDB has the IP on a public allowlist (Google,
	 * Cloudflare, etc.).
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function is_whitelisted( array $args ): bool {
		$check = self::get_check( $args );

		return $check ? $check->is_whitelisted() : false;
	}

	/**
	 * True when AbuseIPDB classifies the IP as a Tor exit node.
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function is_tor( array $args ): bool {
		$check = self::get_check( $args );

		return $check ? $check->is_tor() : false;
	}

	/**
	 * Reset the in-process cache. Test seam.
	 *
	 * @return void
	 */
	public static function reset_cache(): void {
		self::$client  = null;
		self::$results = [];
	}
}
