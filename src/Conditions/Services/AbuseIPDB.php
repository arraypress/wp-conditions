<?php
/**
 * AbuseIPDB Conditions
 *
 * AbuseIPDB is a community-sourced IP-abuse database. Free tier is
 * generous (~1k checks/day). Useful as an ASN/IP-reputation cross-
 * check alongside ProxyCheck / IPQS / IPInfo — different data
 * sources tend to disagree on the long tail, and combining them
 * tightens precision.
 *
 * Callers MUST pass `abuseipdb_api_key` and `ip` in `$args`.
 *
 * @package     ArrayPress\Conditions\Conditions\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Services;

use ArrayPress\Conditions\Clients\AbuseIPDB as AbuseIPDBHelper;
use ArrayPress\Conditions\Helpers\Geo as GeoHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class AbuseIPDB
 *
 * AbuseIPDB condition catalogue.
 */
class AbuseIPDB {

	/**
	 * Get all AbuseIPDB conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'AbuseIPDB', 'arraypress' );

		return [
			'abuseipdb_confidence_score' => [
				'label'         => __( 'Confidence Score', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 75', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'AbuseIPDB abuse-confidence score (0-100). Documented thresholds: ≥75 high confidence, ≥90 very high. Most production deployments use ≥75 as the block trigger.', 'arraypress' ),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::get_confidence_score( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
			'abuseipdb_is_abusive'       => [
				'label'         => __( 'Abusive', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when AbuseIPDB confidence score is 75 or above.', 'arraypress' ),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::is_abusive( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
			'abuseipdb_total_reports'    => [
				'label'         => __( 'Total Reports', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many distinct community reports AbuseIPDB has on file for this IP. Distinct from confidence score — score weights recent reports higher, count is the raw lifetime total. ≥10 reports is a strong "this IP keeps showing up" signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::get_total_reports( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
			'abuseipdb_country'          => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'ISO-2 country code from AbuseIPDB\'s geolocation. Useful when you want a second-source country check separate from your primary IP-reputation provider.', 'arraypress' ),
				'options'       => fn() => GeoHelper::get_country_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::get_country( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
			'abuseipdb_is_tor'           => [
				'label'         => __( 'Tor', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'True when AbuseIPDB classifies the IP as a Tor exit node.', 'arraypress' ),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
			'abuseipdb_is_whitelisted'   => [
				'label'         => __( 'Public Allowlist', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is on AbuseIPDB\'s public allowlist (Google, Cloudflare, Apple, etc.). Useful as an inverse signal so legitimate crawler traffic doesn\'t trip rules — combine with `is no` to scope rules to non-allowlisted IPs.', 'arraypress' ),
				'compare_value' => fn( $args ) => AbuseIPDBHelper::is_whitelisted( $args ),
				'required_args' => [ 'ip', 'abuseipdb_api_key' ],
			],
		];
	}
}
