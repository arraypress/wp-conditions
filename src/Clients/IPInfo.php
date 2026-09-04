<?php
/**
 * IPInfo Helper
 *
 * Geolocation and privacy detection for fraud prevention and regional rules.
 *
 * @package     ArrayPress\Conditions\Helpers\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Clients;

use ArrayPress\IPInfo\Client;

/**
 * Class IPInfo
 *
 * IPInfo.io utilities for geolocation and fraud detection.
 */
class IPInfo {

	/**
	 * Cached client instance.
	 *
	 * @var Client|null
	 */
	private static array $clients = [];

	/**
	 * Cached IP results.
	 *
	 * @var array
	 */
	private static array $results = [];

	/**
	 * Get the IPInfo client.
	 *
	 * @param array $args The condition arguments containing api_key.
	 *
	 * @return Client|null
	 */
	public static function get_client( array $args ): ?Client {
		$api_key = $args['ipinfo_api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return null;
		}

		// One client per key: a second condition set, or another blog on a
		// network, may not share the first one's key.
		return self::$clients[ $api_key ] ??= new Client( $api_key, true, 3600 );
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
	 * Get the IP info result (cached).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	public static function get_result( array $args ): ?object {
		$ip = self::get_ip( $args );

		if ( empty( $ip ) ) {
			return null;
		}

		if ( array_key_exists( $ip, self::$results ) ) {
			return self::$results[ $ip ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->get_ip_info( $ip );

		// A failure is remembered for the request too. Without this every
		// condition in the same pass asked the provider again.
		self::$results[ $ip ] = is_wp_error( $result ) ? null : $result;

		return self::$results[ $ip ];
	}

	/** -------------------------------------------------------------------------
	 * Location Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ISO 3166-1 alpha-2 country code (e.g., "US", "GB").
	 */
	public static function get_country( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_country() ?? '' ) : '';
	}

	/**
	 * Get the region/state code.
	 *
	 * Useful for state-level tax rules, shipping restrictions.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Region/state name (e.g., "California", "England").
	 */
	public static function get_region( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_region() ?? '' ) : '';
	}

	/**
	 * Check if IP is in the European Union.
	 *
	 * Useful for GDPR compliance and VAT handling.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_eu( array $args ): bool {
		$result = self::get_result( $args );

		return $result ? $result->is_eu() : false;
	}

	/** -------------------------------------------------------------------------
	 * Privacy/Fraud Detection Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if IP is a VPN.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_vpn( array $args ): bool {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return false;
		}

		$privacy = $result->get_privacy();

		return $privacy ? $privacy->is_vpn() : false;
	}

	/**
	 * Check if IP is a proxy.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_proxy( array $args ): bool {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return false;
		}

		$privacy = $result->get_privacy();

		return $privacy ? $privacy->is_proxy() : false;
	}

	/**
	 * Check if IP is a Tor exit node.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_tor( array $args ): bool {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return false;
		}

		$privacy = $result->get_privacy();

		return $privacy ? $privacy->is_tor() : false;
	}

	/**
	 * Check if IP is a relay (e.g., iCloud Private Relay).
	 *
	 * Relays are generally legitimate privacy tools, unlike VPNs used for fraud.
	 * Consider NOT flagging these as suspicious.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_relay( array $args ): bool {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return false;
		}

		$privacy = $result->get_privacy();

		return $privacy ? $privacy->is_relay() : false;
	}

	/**
	 * Check if IP is from a hosting/datacenter provider.
	 *
	 * Datacenter IPs are often bots, scrapers, or automated fraud.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_hosting( array $args ): bool {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return false;
		}

		$privacy = $result->get_privacy();

		return $privacy ? $privacy->is_hosting() : false;
	}

	/**
	 * Check if IP is suspicious (VPN, proxy, Tor, or hosting).
	 *
	 * Excludes relays as they're typically legitimate (iCloud Private Relay).
	 * This is a convenience method for common fraud detection.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_suspicious( array $args ): bool {
		return self::is_vpn( $args )
				|| self::is_proxy( $args )
				|| self::is_tor( $args )
				|| self::is_hosting( $args );
	}

	/** -------------------------------------------------------------------------
	 * Network/ASN Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the ASN number.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ASN in format "AS12345".
	 */
	public static function get_asn( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$asn = $result->get_asn();

		return $asn ? ( $asn->get_asn() ?? '' ) : '';
	}

	/**
	 * Get the ASN type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string One of: isp, hosting, business, education.
	 */
	public static function get_asn_type( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$asn = $result->get_asn();

		return $asn ? ( $asn->get_type() ?? '' ) : '';
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
		self::$results = [];
		self::$clients = [];
	}
}
