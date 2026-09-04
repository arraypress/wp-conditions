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
 * counting against the shop's own orders: EDD's `edd_orders` table, or
 * WooCommerce's orders under either storage.
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
	 * The time window a velocity rule counts within.
	 *
	 * The rule's number is the threshold -- "at least 3 orders" -- and used
	 * to double as the window, so "≥ 3 in [minute]" counted the last three
	 * minutes and "≥ 5 in [hour]" the last five hours. The window is the
	 * unit alone now: a preset such as "10_minutes" is ten of that unit, and
	 * a bare unit such as "hour" is one of it.
	 *
	 * @param array $args The condition args.
	 *
	 * @return array{0:int,1:string} Amount and unit.
	 */
	public static function resolve_window( array $args ): array {
		$unit = (string) ( $args['_unit'] ?? 'hour' );

		if ( preg_match( '/^(\d+)_(minute|hour|day|week|month|year)s?$/', $unit, $m ) ) {
			return [ max( 1, (int) $m[1] ), $m[2] ];
		}

		return [ 1, rtrim( $unit, 's' ) ];
	}

	/**
	 * Count orders that used this card within the time window.
	 *
	 * A card fingerprint is a gateway's, not EDD's: there is no column for
	 * it on the orders table, so the count comes from the host. The filter
	 * is asked first, because it is handed the rule's window and can honour
	 * it; a count precomputed in the arguments cannot know the window and is
	 * read as it is, for hosts that still do it that way.
	 *
	 * @param array $args Condition args (must contain 'card_fingerprint').
	 *
	 * @return int|null The count, or null when nothing can answer.
	 */
	public static function count_orders_by_card_fingerprint( array $args ): ?int {
		return self::count_by_card_fingerprint( 'wp_conditions_velocity_orders_by_card_fingerprint', 'velocity_orders_by_card_fingerprint', $args );
	}

	/**
	 * Count distinct emails that used this card within the time window.
	 *
	 * @param array $args Condition args (must contain 'card_fingerprint').
	 *
	 * @return int|null The count, or null when nothing can answer.
	 */
	public static function count_distinct_emails_by_card_fingerprint( array $args ): ?int {
		return self::count_by_card_fingerprint( 'wp_conditions_velocity_distinct_emails_by_card_fingerprint', 'velocity_distinct_emails_by_card_fingerprint', $args );
	}

	/**
	 * One card-fingerprint counter: the filter, then the precomputed argument.
	 *
	 * @param string $filter The filter that may answer with the count.
	 * @param string $arg    The argument a host may have precomputed.
	 * @param array  $args   Condition args.
	 *
	 * @return int|null
	 */
	private static function count_by_card_fingerprint( string $filter, string $arg, array $args ): ?int {
		$fingerprint = (string) ( $args['card_fingerprint'] ?? '' );

		if ( '' === $fingerprint ) {
			return null;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		/**
		 * Filter a card-fingerprint velocity count.
		 *
		 * Return a non-null value to answer; the window is the rule's own.
		 *
		 * @param int|null $count       The count, or null to fall through.
		 * @param string   $fingerprint The card fingerprint.
		 * @param int      $number      Time window amount.
		 * @param string   $unit        Time window unit.
		 * @param array    $args        Every condition argument.
		 */
		$filtered = apply_filters( $filter, null, $fingerprint, $number, $unit, $args );

		if ( null !== $filtered ) {
			return (int) $filtered;
		}

		return isset( $args[ $arg ] ) ? (int) $args[ $arg ] : null;
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

		return self::count_orders( 'orders', 'ip', $ip, $number, $unit );
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

		return self::count_orders( 'orders', 'email', $email, $number, $unit );
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

		return self::count_orders( 'distinct_email', 'ip', $ip, $number, $unit );
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

		return self::count_orders( 'distinct_ip', 'email', $email, $number, $unit );
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

		return self::count_orders( 'failed', 'ip', $ip, $number, $unit );
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

		return self::count_orders( 'failed', 'email', $email, $number, $unit );
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
	 * Count distinct orders containing the same product as the current
	 * cart, placed from this IP within the window.
	 *
	 * Targets the classic card-testing pattern: a fraudster picks the
	 * cheapest product on the store and hammers checkout with stolen
	 * cards. Returns the **maximum** count across products in the cart
	 * — so a cart containing a single test product compares against
	 * just that product's history, while a multi-item cart triggers if
	 * any one item has been bought repeatedly from the same IP.
	 *
	 * Args:
	 *  - ip          (required) — current request's IP.
	 *  - product_ids (optional) — pre-resolved list. Falls back to
	 *                             `edd_get_cart_contents()` when absent.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_same_product_orders_by_ip( array $args ): int {
		if ( isset( $args['velocity_same_product_orders_by_ip'] ) ) {
			return (int) $args['velocity_same_product_orders_by_ip'];
		}

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip === '' ) {
			return 0;
		}

		$product_ids = self::resolve_product_ids( $args );
		if ( empty( $product_ids ) ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_same_product_orders_by_ip', null, $ip, $product_ids, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::count_same_product( 'ip', $ip, $product_ids, $number, $unit );
	}

	/**
	 * Count distinct orders containing the same product as the current
	 * cart, placed by this email within the window. See
	 * {@see count_same_product_orders_by_ip()} for the underlying signal.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function count_same_product_orders_by_email( array $args ): int {
		if ( isset( $args['velocity_same_product_orders_by_email'] ) ) {
			return (int) $args['velocity_same_product_orders_by_email'];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email === '' ) {
			return 0;
		}

		$product_ids = self::resolve_product_ids( $args );
		if ( empty( $product_ids ) ) {
			return 0;
		}

		[ $number, $unit ] = self::resolve_window( $args );

		$filtered = apply_filters( 'wp_conditions_velocity_same_product_orders_by_email', null, $email, $product_ids, $number, $unit );
		if ( $filtered !== null ) {
			return (int) $filtered;
		}

		return self::count_same_product( 'email', $email, $product_ids, $number, $unit );
	}

	/**
	 * Resolve product IDs from `$args['product_ids']` first, then fall
	 * back to the live cart. Returns an empty array when neither yields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Condition args.
	 *
	 * @return array<int>
	 */
	private static function resolve_product_ids( array $args ): array {
		$ids = $args['product_ids'] ?? null;

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			if ( function_exists( 'edd_get_cart_contents' ) ) {
				$contents = edd_get_cart_contents() ?: [];
				$ids      = array_unique( array_column( $contents, 'id' ) );
			}
		}

		return array_filter( array_map( 'intval', (array) $ids ) );
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
	/*
	 * The two counters below query EDD's orders table directly and deliberately
	 * do not cache.
	 *
	 * Direct, because there is no EDD API for "how many orders share this
	 * fingerprint in the last N minutes" -- edd_get_orders() would pull every
	 * matching row into objects to count them.
	 *
	 * Uncached, because a velocity check exists to notice what happened in the
	 * last few minutes. A cached count is a count of the past, and serving one
	 * is the same as not running the check: the fourth card attempt in ninety
	 * seconds would read as the first.
	 */
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
	/**
	 * Whether a table exists, remembered per table for the request.
	 *
	 * Keyed by the full name, so a switch_to_blog() that changes the prefix
	 * is a different answer rather than a stale one.
	 *
	 * @param string $table The table name, prefix included.
	 *
	 * @return bool
	 */
	private static function has_table( string $table ): bool {
		global $wpdb;

		static $known = [];

		if ( ! isset( $wpdb ) ) {
			return false;
		}

		if ( ! array_key_exists( $table, $known ) ) {
			$known[ $table ] = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		}

		return $known[ $table ];
	}

	/**
	 * Which shop's orders to count against.
	 *
	 * EDD's table when it is there, otherwise WooCommerce; the velocity
	 * conditions used to count against EDD alone, so on a WooCommerce store
	 * every one of them answered 0 unless the host answered the filter.
	 *
	 * @return string 'edd', 'wc' or ''.
	 */
	private static function store(): string {
		global $wpdb;

		if ( ! isset( $wpdb ) ) {
			return '';
		}

		if ( function_exists( 'EDD' ) && self::has_table( $wpdb->prefix . 'edd_orders' ) ) {
			return 'edd';
		}

		return function_exists( 'WC' ) ? 'wc' : '';
	}

	/**
	 * Count orders by one identifier within the window, on whichever shop is here.
	 *
	 * @param string $kind   'orders', 'distinct_email', 'distinct_ip' or 'failed'.
	 * @param string $column 'ip' or 'email'.
	 * @param string $value  The identifier.
	 * @param int    $number Window amount.
	 * @param string $unit   Window unit.
	 *
	 * @return int
	 */
	private static function count_orders( string $kind, string $column, string $value, int $number, string $unit ): int {
		$store = self::store();

		if ( 'edd' === $store ) {
			[ $select, $where ] = match ( $kind ) {
				'distinct_email' => [ 'COUNT(DISTINCT email)', $column . ' = %s' ],
				'distinct_ip'    => [ 'COUNT(DISTINCT ip)', $column . ' = %s' ],
				'failed'         => [ 'COUNT(*)', $column . " = %s AND status IN ('failed','revoked','abandoned')" ],
				default          => [ 'COUNT(*)', $column . ' = %s' ],
			};

			return self::edd_count( $select, $where, [ $value ], $number, $unit );
		}

		return 'wc' === $store ? self::wc_count( $kind, $column, $value, $number, $unit ) : 0;
	}

	/**
	 * Same-product count, on whichever shop is here.
	 *
	 * @param string     $column      'ip' or 'email'.
	 * @param string     $value       The identifier.
	 * @param array<int> $product_ids Candidate product IDs.
	 * @param int        $number      Window amount.
	 * @param string     $unit        Window unit.
	 *
	 * @return int
	 */
	private static function count_same_product( string $column, string $value, array $product_ids, int $number, string $unit ): int {
		$store = self::store();

		if ( 'edd' === $store ) {
			return self::edd_count_same_product( $column, $value, $product_ids, $number, $unit );
		}

		return 'wc' === $store ? self::wc_count_same_product( $column, $value, $product_ids, $number, $unit ) : 0;
	}

	/**
	 * Count against WooCommerce's orders.
	 *
	 * With HPOS the orders table carries the email and IP as columns; with
	 * the legacy storage they are post meta on a shop_order post. Either way
	 * the date compared is the UTC one.
	 *
	 * @param string $kind   'orders', 'distinct_email', 'distinct_ip' or 'failed'.
	 * @param string $column 'ip' or 'email'.
	 * @param string $value  The identifier.
	 * @param int    $number Window amount.
	 * @param string $unit   Window unit.
	 *
	 * @return int
	 */
	private static function wc_count( string $kind, string $column, string $value, int $number, string $unit ): int {
		global $wpdb;

		$interval = self::to_interval( $number, $unit );
		$failed   = "('wc-failed','wc-cancelled')";

		if ( self::wc_uses_orders_table() ) {
			$table = $wpdb->prefix . 'wc_orders';

			if ( ! self::has_table( $table ) ) {
				return 0;
			}

			$identity = 'ip' === $column ? 'ip_address' : 'billing_email';
			$select   = match ( $kind ) {
				'distinct_email' => 'COUNT(DISTINCT billing_email)',
				'distinct_ip'    => 'COUNT(DISTINCT ip_address)',
				default          => 'COUNT(*)',
			};
			$status   = 'failed' === $kind ? " AND status IN {$failed}" : '';

			$sql = "SELECT {$select} FROM {$table} WHERE type = 'shop_order' AND {$identity} = %s{$status} AND date_created_gmt >= ( UTC_TIMESTAMP() - {$interval} )";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $value ) );
		}

		$key    = 'ip' === $column ? '_customer_ip_address' : '_billing_email';
		$other  = 'distinct_email' === $kind ? '_billing_email' : '_customer_ip_address';
		$select = in_array( $kind, [ 'distinct_email', 'distinct_ip' ], true ) ? 'COUNT(DISTINCT other.meta_value)' : 'COUNT(*)';
		$join   = in_array( $kind, [ 'distinct_email', 'distinct_ip' ], true )
			? " INNER JOIN {$wpdb->postmeta} other ON other.post_id = p.ID AND other.meta_key = '{$other}'"
			: '';
		$status = 'failed' === $kind ? " AND p.post_status IN {$failed}" : '';

		$sql = "SELECT {$select} FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '{$key}'{$join} WHERE p.post_type = 'shop_order' AND m.meta_value = %s{$status} AND p.post_date_gmt >= ( UTC_TIMESTAMP() - {$interval} )";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Same-product count against WooCommerce's orders.
	 *
	 * The product lookup table exists under both storages, so only the
	 * orders side differs.
	 *
	 * @param string     $column      'ip' or 'email'.
	 * @param string     $value       The identifier.
	 * @param array<int> $product_ids Candidate product IDs.
	 * @param int        $number      Window amount.
	 * @param string     $unit        Window unit.
	 *
	 * @return int
	 */
	private static function wc_count_same_product( string $column, string $value, array $product_ids, int $number, string $unit ): int {
		global $wpdb;

		$lookup = $wpdb->prefix . 'wc_order_product_lookup';

		if ( ! self::has_table( $lookup ) ) {
			return 0;
		}

		$product_ids = array_filter( array_map( 'intval', $product_ids ) );

		if ( empty( $product_ids ) ) {
			return 0;
		}

		$product_ids_sql = implode( ',', $product_ids );
		$interval        = self::to_interval( $number, $unit );

		if ( self::wc_uses_orders_table() ) {
			$table = $wpdb->prefix . 'wc_orders';

			if ( ! self::has_table( $table ) ) {
				return 0;
			}

			$identity = 'ip' === $column ? 'ip_address' : 'billing_email';

			$sql = "SELECT MAX(c) FROM (
				SELECT COUNT(DISTINCT o.id) AS c
				FROM {$table} o
				INNER JOIN {$lookup} l ON l.order_id = o.id
				WHERE o.type = 'shop_order'
				  AND o.{$identity} = %s
				  AND l.product_id IN ({$product_ids_sql})
				  AND o.date_created_gmt >= ( UTC_TIMESTAMP() - {$interval} )
				GROUP BY l.product_id
			) sub";
		} else {
			$key = 'ip' === $column ? '_customer_ip_address' : '_billing_email';

			$sql = "SELECT MAX(c) FROM (
				SELECT COUNT(DISTINCT p.ID) AS c
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '{$key}'
				INNER JOIN {$lookup} l ON l.order_id = p.ID
				WHERE p.post_type = 'shop_order'
				  AND m.meta_value = %s
				  AND l.product_id IN ({$product_ids_sql})
				  AND p.post_date_gmt >= ( UTC_TIMESTAMP() - {$interval} )
				GROUP BY l.product_id
			) sub";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Whether WooCommerce keeps orders in its own table (HPOS).
	 *
	 * @return bool
	 */
	private static function wc_uses_orders_table(): bool {
		return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	private static function edd_count( string $select, string $where, array $bindings, int $number, string $unit ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'edd_orders';

		if ( ! self::has_table( $table ) ) {
			return 0;
		}

		$interval = self::to_interval( $number, $unit );

		$sql = "SELECT {$select} FROM {$table} WHERE {$where} AND date_created >= ( UTC_TIMESTAMP() - {$interval} )";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$bindings ) );
	}

	/**
	 * Count distinct orders containing any of the given product IDs that
	 * match the identifier (ip/email) within the time window. Returns
	 * the MAX count across products — so the result is "the most
	 * frequently bought product matches X distinct orders".
	 *
	 * Joins `wp_edd_orders` with `wp_edd_order_items`. Bails to zero if
	 * either table is missing.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $column      The orders-table column to filter on (`ip` / `email`).
	 * @param string     $value       Identifier value.
	 * @param array<int> $product_ids Candidate product IDs.
	 * @param int        $number      Window amount.
	 * @param string     $unit        Window unit.
	 *
	 * @return int
	 */
	private static function edd_count_same_product( string $column, string $value, array $product_ids, int $number, string $unit ): int {
		global $wpdb;

		$orders_table = $wpdb->prefix . 'edd_orders';
		$items_table  = $wpdb->prefix . 'edd_order_items';

		if ( ! self::has_table( $orders_table ) || ! self::has_table( $items_table ) ) {
			return 0;
		}

		// IDs were already cast to int in resolve_product_ids(); a final
		// implode is safe to inline into SQL.
		$product_ids = array_filter( array_map( 'intval', $product_ids ) );
		if ( empty( $product_ids ) ) {
			return 0;
		}
		$product_ids_sql = implode( ',', $product_ids );

		$interval = self::to_interval( $number, $unit );

		$sql = "SELECT MAX(c) FROM (
			SELECT COUNT(DISTINCT o.id) AS c
			FROM {$orders_table} o
			INNER JOIN {$items_table} oi ON oi.order_id = o.id
			WHERE o.{$column} = %s
			  AND oi.product_id IN ({$product_ids_sql})
			  AND o.date_created >= ( UTC_TIMESTAMP() - {$interval} )
			GROUP BY oi.product_id
		) sub";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $value ) );
	}
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
}
