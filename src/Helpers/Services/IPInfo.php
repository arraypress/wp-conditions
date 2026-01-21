<?php
/**
 * IPInfo Helper
 *
 * Provides IPInfo.io integration utilities for conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Services;

use ArrayPress\IPInfo\Client;

/**
 * Class IPInfo
 *
 * IPInfo.io utilities for conditions.
 */
class IPInfo {

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

		if ( self::$client === null ) {
			self::$client = new Client( $api_key, true, 3600 );
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

		if ( isset( self::$results[ $ip ] ) ) {
			return self::$results[ $ip ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->get_ip_info( $ip );

		if ( is_wp_error( $result ) ) {
			return null;
		}

		self::$results[ $ip ] = $result;

		return $result;
	}

	/** -------------------------------------------------------------------------
	 * Location Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_country( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_country() ?? '' ) : '';
	}

	/**
	 * Get the country name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_country_name( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_country_name() ?? '' ) : '';
	}

	/**
	 * Get the continent code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_continent( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$continent = $result->get_continent();

		return $continent ? ( $continent->get_code() ?? '' ) : '';
	}

	/**
	 * Get the continent name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_continent_name( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$continent = $result->get_continent();

		return $continent ? ( $continent->get_name() ?? '' ) : '';
	}

	/**
	 * Get the region/state.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_region( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_region() ?? '' ) : '';
	}

	/**
	 * Get the city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_city( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_city() ?? '' ) : '';
	}

	/**
	 * Get the postal code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_postal( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_postal() ?? '' ) : '';
	}

	/**
	 * Get the timezone.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_timezone( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_timezone() ?? '' ) : '';
	}

	/**
	 * Get the latitude.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_latitude( array $args ): float {
		$result = self::get_result( $args );

		return $result ? (float) ( $result->get_latitude() ?? 0 ) : 0.0;
	}

	/**
	 * Get the longitude.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_longitude( array $args ): float {
		$result = self::get_result( $args );

		return $result ? (float) ( $result->get_longitude() ?? 0 ) : 0.0;
	}

	/**
	 * Check if IP is in the EU.
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
	 * Privacy Detection Methods (Business/Premium Plans)
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
	 * Check if IP is from a hosting provider.
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
	 * Get the privacy service name (if detected).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_privacy_service( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$privacy = $result->get_privacy();

		return $privacy ? ( $privacy->get_service() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * ASN/Network Methods (Basic Plan and Above)
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the ASN number.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
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
	 * Get the ASN name/organization.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_asn_name( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$asn = $result->get_asn();

		return $asn ? ( $asn->get_name() ?? '' ) : '';
	}

	/**
	 * Get the ASN domain.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_asn_domain( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$asn = $result->get_asn();

		return $asn ? ( $asn->get_domain() ?? '' ) : '';
	}

	/**
	 * Get the ASN type (isp, hosting, business, education).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
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
	 * Company Methods (Business/Premium Plans)
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the company name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_company_name( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$company = $result->get_company();

		return $company ? ( $company->get_name() ?? '' ) : '';
	}

	/**
	 * Get the company domain.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_company_domain( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$company = $result->get_company();

		return $company ? ( $company->get_domain() ?? '' ) : '';
	}

	/**
	 * Get the company type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_company_type( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$company = $result->get_company();

		return $company ? ( $company->get_type() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Currency Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country's currency code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_currency_code( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$currency = $result->get_country_currency();

		return $currency ? ( $currency->get_code() ?? '' ) : '';
	}

	/**
	 * Get the country's currency symbol.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_currency_symbol( array $args ): string {
		$result = self::get_result( $args );

		if ( ! $result ) {
			return '';
		}

		$currency = $result->get_country_currency();

		return $currency ? ( $currency->get_symbol() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Utility Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if IP is anycast.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_anycast( array $args ): bool {
		$result = self::get_result( $args );

		return $result ? $result->is_anycast() : false;
	}

	/**
	 * Get the hostname.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_hostname( array $args ): string {
		$result = self::get_result( $args );

		return $result ? ( $result->get_hostname() ?? '' ) : '';
	}

	/**
	 * Clear the cached results.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$results = [];
		self::$client  = null;
	}

}