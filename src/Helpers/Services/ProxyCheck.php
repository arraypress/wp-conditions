<?php
/**
 * ProxyCheck Helper
 *
 * Provides ProxyCheck.io integration utilities for conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Services;

use ArrayPress\ProxyCheck\Client;

/**
 * Class ProxyCheck
 *
 * ProxyCheck.io utilities for conditions.
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
			'port' => 1,
			'seen' => 1,
			'days' => 7,
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

		return $result ? $result->is_vpn() : false;
	}

	/**
	 * Check if IP should be blocked.
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
	 * Get the proxy/VPN type.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_type( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_type() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * IP Risk Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the risk score.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_risk_score( array $args ): int {
		$result = self::get_ip_result( $args );

		return $result ? (int) ( $result->get_risk_score() ?? 0 ) : 0;
	}

	/**
	 * Get the attack history count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_attack_history( array $args ): int {
		$result = self::get_ip_result( $args );

		if ( ! $result ) {
			return 0;
		}

		$history = $result->get_attack_history();

		return is_array( $history ) ? count( $history ) : 0;
	}

	/** -------------------------------------------------------------------------
	 * IP Location Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the country code.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_country( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_country() ?? '' ) : '';
	}

	/**
	 * Get the continent.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_continent( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_continent() ?? '' ) : '';
	}

	/**
	 * Get the city.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_city( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_city() ?? '' ) : '';
	}

	/**
	 * Get the region/state.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_region( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_region() ?? '' ) : '';
	}

	/**
	 * Get the timezone.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_timezone( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_timezone() ?? '' ) : '';
	}

	/** -------------------------------------------------------------------------
	 * IP Network Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the operator/ISP name.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return string
	 */
	public static function get_operator( array $args ): string {
		$result = self::get_ip_result( $args );

		return $result ? ( $result->get_operator() ?? '' ) : '';
	}

	/**
	 * Get the port.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_port( array $args ): int {
		$result = self::get_ip_result( $args );

		return $result ? (int) ( $result->get_port() ?? 0 ) : 0;
	}

	/** -------------------------------------------------------------------------
	 * Email Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Check if email is disposable.
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
	 * Check if email is from a free provider.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_free_email( array $args ): bool {
		$result = self::get_email_result( $args );

		return $result ? $result->is_free() : false;
	}

	/**
	 * Check if email has been compromised/leaked.
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
	 * Get the email risk score.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_email_risk_score( array $args ): int {
		$result = self::get_email_result( $args );

		return $result ? (int) ( $result->get_risk_score() ?? 0 ) : 0;
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