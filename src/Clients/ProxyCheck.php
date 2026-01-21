<?php
/**
 * ProxyCheck Helper
 *
 * Proxy/VPN detection and email validation for fraud prevention.
 *
 * @package     ArrayPress\Conditions\Helpers\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Clients;

use ArrayPress\ProxyCheck\Client;

/**
 * Class ProxyCheck
 *
 * ProxyCheck.io utilities for fraud detection.
 */
class ProxyCheck {

	/**
	 * Cached client instance.
	 *
	 * @var Client|null
	 */
	private static ?Client $client = null;

	/**
	 * Cached IP results.
	 *
	 * @var array
	 */
	private static array $ip_results = [];

	/**
	 * Cached email results.
	 *
	 * @var array
	 */
	private static array $email_results = [];

	/**
	 * Get the ProxyCheck client.
	 *
	 * @param array $args The condition arguments containing api_key.
	 *
	 * @return Client|null
	 */
	public static function get_client( array $args ): ?Client {
		$api_key = $args['proxycheck_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return null;
		}

		if ( self::$client === null ) {
			self::$client = new Client( $api_key, true, 600 );
		}

		return self::$client;
	}

	/**
	 * Get IP address from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_ip( array $args ): string {
		return $args['ip'] ?? $args['ip_address'] ?? '';
	}

	/**
	 * Get email address from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_email_address( array $args ): string {
		return $args['email'] ?? $args['email_address'] ?? '';
	}

	/**
	 * Get the IP check result (cached).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	public static function get_ip_result( array $args ): ?object {
		$ip = self::get_ip( $args );

		if ( empty( $ip ) ) {
			return null;
		}

		if ( isset( self::$ip_results[ $ip ] ) ) {
			return self::$ip_results[ $ip ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->check_ip( $ip, [
			'vpn'  => 1,
			'asn'  => 1,
			'risk' => 2,
		] );

		if ( is_wp_error( $result ) ) {
			return null;
		}

		self::$ip_results[ $ip ] = $result;

		return $result;
	}

	/**
	 * Get the email check result (cached).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	public static function get_email_result( array $args ): ?object {
		$email = self::get_email_address( $args );

		if ( empty( $email ) ) {
			return null;
		}

		if ( isset( self::$email_results[ $email ] ) ) {
			return self::$email_results[ $email ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->check_email( $email );

		if ( is_wp_error( $result ) ) {
			return null;
		}

		self::$email_results[ $email ] = $result;

		return $result;
	}

	/** -------------------------------------------------------------------------
	 * IP Detection Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if IP is a proxy.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_proxy( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? $result->is_proxy() : false;
	}

	/**
	 * Check if IP is a VPN.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_vpn( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->is_vpn() ?? false ) : false;
	}

	/**
	 * Check if IP should be blocked based on ProxyCheck's recommendation.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function should_block( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? $result->should_block() : false;
	}

	/**
	 * Check if IP is suspicious (proxy or VPN).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_suspicious( array $args ): bool {
		return self::is_proxy( $args ) || self::is_vpn( $args );
	}

	/**
	 * Get the proxy/VPN type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string e.g., VPN, TOR, SOCKS, SOCKS4, SOCKS5, HTTP, HTTPS, etc.
	 */
	public static function get_type( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_type() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Risk Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the risk score.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int Risk score 0-100 (higher = more risky).
	 */
	public static function get_risk_score( array $args ): int {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_risk_score() ?? 0 ) : 0;
	}

	/**
	 * Check if risk score exceeds threshold.
	 *
	 * @param array $args      The condition arguments.
	 * @param int   $threshold The risk threshold (default 50).
	 *
	 * @return bool
	 */
	public static function is_high_risk( array $args, int $threshold = 50 ): bool {
		return self::get_risk_score( $args ) >= $threshold;
	}

	/** -------------------------------------------------------------------------
	 * Location Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ISO 3166-1 alpha-2 country code.
	 */
	public static function get_country_code( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$country = $result->get_country();

		return $country['code'] ?? '';
	}

	/**
	 * Get the country name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Country name.
	 */
	public static function get_country_name( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$country = $result->get_country();

		return $country['name'] ?? '';
	}

	/**
	 * Check if IP is from an EU country.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_eu_country( array $args ): bool {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return false;
		}

		$country = $result->get_country();

		return $country['is_eu'] ?? false;
	}

	/**
	 * Get the continent code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Continent code (e.g., NA, EU, AS).
	 */
	public static function get_continent_code( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$continent = $result->get_continent();

		return $continent['code'] ?? '';
	}

	/**
	 * Get the continent name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Continent name.
	 */
	public static function get_continent_name( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$continent = $result->get_continent();

		return $continent['name'] ?? '';
	}

	/**
	 * Get the city name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string City name.
	 */
	public static function get_city( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_city() ?? '' ) : '';
	}

	/**
	 * Get the region/state code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Region code.
	 */
	public static function get_region_code( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$region = $result->get_region();

		return $region['code'] ?? '';
	}

	/**
	 * Get the region/state name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Region name.
	 */
	public static function get_region_name( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$region = $result->get_region();

		return $region['name'] ?? '';
	}

	/**
	 * Get the timezone.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Timezone string.
	 */
	public static function get_timezone( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_timezone() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Network Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the ASN.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ASN string.
	 */
	public static function get_asn( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_asn() ?? '' ) : '';
	}

	/**
	 * Get the provider name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Provider name.
	 */
	public static function get_provider( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_provider() ?? '' ) : '';
	}

	/**
	 * Get the organisation name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Organisation name.
	 */
	public static function get_organisation( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_organisation() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Email Validation Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if email is from a disposable provider.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_disposable_email( array $args ): bool {
		$result = self::get_email_result( $args );

		return $result ? $result->is_disposable() : false;
	}

	/** -------------------------------------------------------------------------
	 * Utility Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Clear the cached results.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$ip_results    = [];
		self::$email_results = [];
		self::$client        = null;
	}

}