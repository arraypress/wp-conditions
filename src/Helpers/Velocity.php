<?php
/**
 * Velocity Helper
 *
 * Counts orders/attempts by identifier within a time window. Used by velocity
 * conditions to detect card-testing, fraud rings, and burst attacks.
 *
 * Each counter first honours `$args['velocity_*']` (caller pre-computed value),
 * then a `wp_conditions_velocity_*` filter (lets consumer plugins inject their
 * own data source — e.g. a fraud-filter log table), and finally falls back to
 * counting against EDD's `edd_orders` table when EDD is active.
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
 * Class Velocity
 *
 * Time-windowed counters for fraud-detection conditions.
 */
class Velocity {

	/**
	 * Convert a number_unit pair to a SQL `INTERVAL` clause.
	 *
	 * @param int|float $number The amount.
	 * @param string    $unit   minute|hour|day|week|month|year.
	 *
	 * @return string e.g. "INTERVAL 60 MINUTE"
	 */
	public static function to_interval( int|float $number, string $unit ): string {
		$number = max( 1, (int) $number );
		$map    = [
			'minute' => 'MINUTE',
			'hour'   => 'HOUR',
			'day'    => 'DAY',
			'week'   => 'WEEK',
			'month'  => 'MONTH',
			'year'   => 'YEAR',
		];
		$sql    = $map[ $unit ] ?? 'HOUR';

		return sprintf( 'INTERVAL %d %s', $number, $sql );
	}

	/**
	 * Resolve the user-supplied number_unit value into [number, unit].
	 *
	 * Conditions store the value as ['number' => N, 'unit' => 'hour'] or via
	 * the magic `_number` / `_unit` args set by the Matcher.
	 *
	 * @param array $args The condition args.
	 *
	 * @return array{0:int,1:string}
	 */
	private static function resolve_window( array $args ): array {
		$number = (int) ( $args['_number'] ?? 1 );
		$unit   = (string) ( $args['_unit'] ?? 'hour' );

		return [ max( 1, $number ), $unit ];
	}

	/**
	 * Count orders placed from this IP within the time window.
	 *
	 * @param array $args Condition args (must contain 'ip').
	 *
	 * @return int
	 */
	public static function count_orders_by_ip( array $args ): int {
		if ( isset( $args['velocity_orders_by_ip'] ) ) {
			return (int) $args['velocity_orders_by_ip'];
		}

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		/**
		 * Filter the order-by-IP velocity count.
		 *
		 * Return a non-null value to override the default EDD-orders query.
		 *
		 * @param int|null $count  The count, or null to fall through.
		 * @param string   $ip     The IP address.
		 * @param int      $number Time window amount.
		 * @param string   $unit   Time window unit.
		 */
		$filtered = apply_filters( 'wp_conditions_velocity_orders_by_ip', null, $ip, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(*)',
			'ip = %s',
			[ $ip ],
			$number,
			$unit
		);
	}

	/**
	 * Count orders placed from this email within the time window.
	 *
	 * @param array $args Condition args (must contain 'email').
	 *
	 * @return int
	 */
	public static function count_orders_by_email( array $args ): int {
		if ( isset( $args['velocity_orders_by_email'] ) ) {
			return (int) $args['velocity_orders_by_email'];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_orders_by_email', null, $email, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(*)',
			'email = %s',
			[ $email ],
			$number,
			$unit
		);
	}

	/**
	 * Count distinct emails associated with this IP within the window.
	 *
	 * Detects fraud rings: same machine cycling through email addresses.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_distinct_emails_by_ip( array $args ): int {
		if ( isset( $args['velocity_distinct_emails_by_ip'] ) ) {
			return (int) $args['velocity_distinct_emails_by_ip'];
		}

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_distinct_emails_by_ip', null, $ip, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(DISTINCT email)',
			'ip = %s',
			[ $ip ],
			$number,
			$unit
		);
	}

	/**
	 * Count distinct IPs associated with this email within the window.
	 *
	 * Detects credential sharing or stolen-account use.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_distinct_ips_by_email( array $args ): int {
		if ( isset( $args['velocity_distinct_ips_by_email'] ) ) {
			return (int) $args['velocity_distinct_ips_by_email'];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_distinct_ips_by_email', null, $email, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(DISTINCT ip)',
			'email = %s',
			[ $email ],
			$number,
			$unit
		);
	}

	/**
	 * Count failed/revoked orders from this IP within the window.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_failed_orders_by_ip( array $args ): int {
		if ( isset( $args['velocity_failed_orders_by_ip'] ) ) {
			return (int) $args['velocity_failed_orders_by_ip'];
		}

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_failed_orders_by_ip', null, $ip, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(*)',
			"ip = %s AND status IN ('failed','revoked','abandoned')",
			[ $ip ],
			$number,
			$unit
		);
	}

	/**
	 * Count failed/revoked orders for this email within the window.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_failed_orders_by_email( array $args ): int {
		if ( isset( $args['velocity_failed_orders_by_email'] ) ) {
			return (int) $args['velocity_failed_orders_by_email'];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_failed_orders_by_email', null, $email, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::edd_count(
			'COUNT(*)',
			"email = %s AND status IN ('failed','revoked','abandoned')",
			[ $email ],
			$number,
			$unit
		);
	}

	/**
	 * Count blocked checkout attempts from this IP within the window.
	 *
	 * No core-data fallback — this counter only works when a consumer plugin
	 * (e.g. fraud filter) registers a data source via the filter.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_blocked_attempts_by_ip( array $args ): int {
		if ( isset( $args['velocity_blocked_attempts_by_ip'] ) ) {
			return (int) $args['velocity_blocked_attempts_by_ip'];
		}

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_blocked_attempts_by_ip', null, $ip, $number, $unit );

		return (int) ( $filtered ?? 0 );
	}

	/**
	 * Count blocked checkout attempts for this email within the window.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_blocked_attempts_by_email( array $args ): int {
		if ( isset( $args['velocity_blocked_attempts_by_email'] ) ) {
			return (int) $args['velocity_blocked_attempts_by_email'];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email === '' ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_blocked_attempts_by_email', null, $email, $number, $unit );

		return (int) ( $filtered ?? 0 );
	}

	/**
	 * Compute a stable card fingerprint from gateway-provided card metadata.
	 *
	 * The result never contains a raw PAN or last4 — it is a salted hash
	 * suitable for storage and velocity comparisons.
	 *
	 * @param string      $brand     visa|mastercard|amex|...
	 * @param string      $last4     Last four digits.
	 * @param string|null $exp_month Two-digit expiry month.
	 * @param string|null $exp_year  Four-digit expiry year.
	 * @param string|null $country   ISO-2 issuing country.
	 *
	 * @return string 40-char SHA-1 hex digest.
	 */
	public static function compute_card_fingerprint(
		string $brand,
		string $last4,
		?string $exp_month = null,
		?string $exp_year = null,
		?string $country = null
	): string {
		$payload = strtolower( implode( '|', [
			trim( $brand ),
			trim( $last4 ),
			(string) $exp_month,
			(string) $exp_year,
			(string) $country,
		] ) );

		$salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : '';

		return sha1( $salt . '|' . $payload );
	}

	/**
	 * Run a windowed COUNT against the EDD orders table.
	 *
	 * @param string $select   The aggregate (e.g. "COUNT(*)").
	 * @param string $where    Additional WHERE clause (with %s placeholders).
	 * @param array  $bindings Values for the WHERE placeholders.
	 * @param int    $number   Window amount.
	 * @param string $unit     Window unit.
	 *
	 * @return int
	 */
	private static function edd_count( string $select, string $where, array $bindings, int $number, string $unit ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'edd_orders';

		// Sanity: only proceed if EDD's table exists.
		static $has_table = null;
		if ( $has_table === null ) {
			$has_table = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		}
		if ( ! $has_table ) {
			return 0;
		}

		$interval = self::to_interval( $number, $unit );

		$sql = "SELECT {$select} FROM {$table} WHERE {$where} AND date_created >= ( UTC_TIMESTAMP() - {$interval} )";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$bindings ) );
	}

}
