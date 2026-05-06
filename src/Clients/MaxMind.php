<?php
/**
 * MaxMind minFraud Helper
 *
 * Bridges the `arraypress/maxmind-minfraud` Client into the
 * conditions runtime. Lazy-instantiates one Client per request
 * (driven off the `minfraud_account_id` + `minfraud_license_key`
 * args provided by the host plugin via ContextBuilder), then
 * memoises score lookups across condition evaluations in the same
 * pass — so multiple minFraud-using conditions in one rule (e.g.
 * `Risk Score > 70 AND High Risk == yes`) only cost one API call.
 *
 * Pulls the request payload out of the standard fraud-filter
 * context: IP, email, billing, gateway. Falls back gracefully when
 * fields are missing — minFraud doesn't require them all.
 *
 * @package     ArrayPress\Conditions\Clients
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Clients;

use ArrayPress\MaxMind\MinFraud\Client;
use ArrayPress\MaxMind\MinFraud\Response\Score;

class MaxMind {

	/**
	 * Cached client instance.
	 *
	 * @var Client|null
	 */
	private static ?Client $client = null;

	/**
	 * Cached Score responses keyed by request hash, so multiple
	 * conditions referring to the same payload don't re-hit the API.
	 *
	 * @var array<string, Score|null>
	 */
	private static array $score_results = [];

	/**
	 * Get (or build) the minFraud client.
	 *
	 * @param array $args Condition args.
	 *
	 * @return Client|null Null when credentials are missing.
	 */
	public static function get_client( array $args ): ?Client {
		$account = (string) ( $args['minfraud_account_id'] ?? '' );
		$license = (string) ( $args['minfraud_license_key'] ?? '' );

		if ( $account === '' || $license === '' ) {
			return null;
		}

		if ( self::$client === null ) {
			self::$client = new Client( $account, $license );
		}

		return self::$client;
	}

	/**
	 * Build the Score request payload from condition args.
	 *
	 * Maps the standard fraud-filter context shape (`ip`, `email`,
	 * `billing_country`, `billing_state`, `billing_city`,
	 * `billing_zip`, `gateway`) to MaxMind's spec. Empty values are
	 * dropped — MaxMind is happy with a partial payload.
	 *
	 * @param array $args Condition args.
	 *
	 * @return array
	 */
	private static function build_payload( array $args ): array {
		$payload = [];

		$ip = (string) ( $args['ip'] ?? '' );
		if ( $ip !== '' ) {
			$payload['device'] = [ 'ip_address' => $ip ];
		}

		$email = (string) ( $args['email'] ?? '' );
		if ( $email !== '' ) {
			$payload['email'] = [ 'address' => $email ];
		}

		$billing = array_filter( [
			'country'     => strtoupper( (string) ( $args['billing_country'] ?? '' ) ),
			'region'      => (string) ( $args['billing_state'] ?? '' ),
			'city'        => (string) ( $args['billing_city'] ?? '' ),
			'postal'      => (string) ( $args['billing_zip'] ?? '' ),
		] );
		if ( ! empty( $billing ) ) {
			$payload['billing'] = $billing;
		}

		$gateway = (string) ( $args['gateway'] ?? '' );
		if ( $gateway !== '' ) {
			$payload['payment'] = [ 'processor' => $gateway ];
		}

		return $payload;
	}

	/**
	 * Get the Score response (memoised).
	 *
	 * @param array $args Condition args.
	 *
	 * @return Score|null
	 */
	public static function get_score( array $args ): ?Score {
		$client = self::get_client( $args );
		if ( ! $client ) {
			return null;
		}

		$payload = self::build_payload( $args );
		if ( empty( $payload ) ) {
			return null;
		}

		$key = md5( wp_json_encode( $payload ) );
		if ( array_key_exists( $key, self::$score_results ) ) {
			return self::$score_results[ $key ];
		}

		$result = $client->check_score( $payload );
		$score  = $result instanceof Score ? $result : null;

		self::$score_results[ $key ] = $score;

		return $score;
	}

	/**
	 * 0-99 risk score, or 0 when MaxMind isn't reachable.
	 *
	 * @param array $args Condition args.
	 *
	 * @return float
	 */
	public static function get_risk_score( array $args ): float {
		$score = self::get_score( $args );

		return $score ? $score->get_risk_score() : 0.0;
	}

	/**
	 * Convenience boolean — true when the risk score is ≥ MaxMind's
	 * documented "high risk" threshold (75).
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function is_high_risk( array $args ): bool {
		$score = self::get_score( $args );

		return $score ? $score->is_high_risk( 75.0 ) : false;
	}

	/**
	 * Reset the in-process cache. Test seam.
	 *
	 * @return void
	 */
	public static function reset_cache(): void {
		self::$client        = null;
		self::$score_results = [];
	}
}
