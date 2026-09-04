<?php
/**
 * IPQualityScore Helper
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

use ArrayPress\IPQualityScore\Client;

/**
 * Class IPQualityScore
 *
 * IPQualityScore utilities for fraud detection.
 */
class IPQualityScore {

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
	private static array $ip_results = [];

	/**
	 * Cached email results.
	 *
	 * @var array
	 */
	private static array $email_results = [];

	/**
	 * Get the IPQualityScore client.
	 *
	 * @param array $args The condition arguments containing api_key.
	 *
	 * @return Client|null
	 */
	public static function get_client( array $args ): ?Client {
		$api_key = $args['ipqs_api_key'] ?? '';

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

		if ( array_key_exists( $ip, self::$ip_results ) ) {
			return self::$ip_results[ $ip ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->check_ip( $ip );

		// A failure is remembered for the request too. Without this every
		// condition in the same pass asked the provider again.
		self::$ip_results[ $ip ] = is_wp_error( $result ) ? null : $result;

		return self::$ip_results[ $ip ];
	}

	/**
	 * Get the email validation result (cached).
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

		if ( array_key_exists( $email, self::$email_results ) ) {
			return self::$email_results[ $email ];
		}

		$client = self::get_client( $args );

		if ( ! $client ) {
			return null;
		}

		$result = $client->validate_email( $email );

		// A failure is remembered for the request too. Without this every
		// condition in the same pass asked the provider again.
		self::$email_results[ $email ] = is_wp_error( $result ) ? null : $result;

		return self::$email_results[ $email ];
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

		return $result ? $result->is_vpn() : false;
	}

	/**
	 * Check if IP is a Tor exit node.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_tor( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? $result->is_tor() : false;
	}

	/**
	 * Check if IP is a bot.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_bot( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? $result->is_bot() : false;
	}

	/**
	 * Check if IP has recent abuse.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function has_recent_abuse( array $args ): bool {
		$result = self::get_ip_result( $args );

		return $result ? $result->has_recent_abuse() : false;
	}

	/**
	 * Check if IP is suspicious (proxy, VPN, Tor, or has recent abuse).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_suspicious( array $args ): bool {
		return self::is_proxy( $args )
				|| self::is_vpn( $args )
				|| self::is_tor( $args )
				|| self::has_recent_abuse( $args );
	}

	/** -------------------------------------------------------------------------
	 * IP Risk Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the fraud score.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int Fraud score 0-100 (higher = more risky).
	 */
	public static function get_fraud_score( array $args ): int {
		$result = self::get_ip_result( $args );

		return $result ? (int) ( $result->get_fraud_score() ?? 0 ) : 0;
	}

	/**
	 * Check if fraud score exceeds threshold.
	 *
	 * @param array $args      The condition arguments.
	 * @param int   $threshold The fraud threshold (default 75).
	 *
	 * @return bool
	 */
	public static function is_high_risk( array $args, int $threshold = 75 ): bool {
		return self::get_fraud_score( $args ) >= $threshold;
	}

	/** -------------------------------------------------------------------------
	 * IP Location Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ISO 3166-1 alpha-2 country code.
	 */
	public static function get_country( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_country_code() ?? '' ) : '';
	}

	/**
	 * Get the ASN.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ASN as string (for tag matching).
	 */
	public static function get_asn( array $args ): string {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return '';
		}

		$asn = $result->get_asn();

		return $asn !== null ? (string) $asn : '';
	}

	/**
	 * Get the connection type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string Connection type (Residential, Corporate, Education, Mobile, Data Center).
	 */
	public static function get_connection_type( array $args ): string {
		$result = self::get_ip_result( $args );

		// The option values are lower-case with no spaces ("datacenter");
		// the API answers in title case ("Data Center"), so the two never met.
		return $result ? strtolower( str_replace( ' ', '', (string) ( $result->get_connection_type() ?? '' ) ) ) : '';
	}

	/**
	 * Get the ISP.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string ISP name.
	 */
	public static function get_isp( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_isp() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * Email Validation Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if email is valid.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_valid_email( array $args ): bool {
		$result = self::get_email_result( $args );

		return $result ? $result->is_valid() : false;
	}

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

	/**
	 * Check if email has been leaked in data breaches.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_leaked_email( array $args ): bool {
		$result = self::get_email_result( $args );

		return $result ? $result->is_leaked() : false;
	}

	/**
	 * Get email fraud score.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int Fraud score 0-100.
	 */
	public static function get_email_fraud_score( array $args ): int {
		$result = self::get_email_result( $args );

		return $result ? (int) ( $result->get_fraud_score() ?? 0 ) : 0;
	}

	/**
	 * Check if email is risky (disposable, invalid, or leaked).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_risky_email( array $args ): bool {
		$result = self::get_email_result( $args );

		// No result is no verdict. This used to read "not valid" as risky, so
		// a timeout or an exhausted quota made every address risky and a
		// "Risky Email = yes → block" rule refused every checkout until the
		// provider came back.
		if ( ! $result ) {
			return false;
		}

		return $result->is_disposable() || ! $result->is_valid() || $result->is_leaked();
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
		self::$clients = [];
	}
}
