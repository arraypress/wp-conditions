<?php
/**
 * IPQualityScore Conditions
 *
 * Proxy/VPN detection and email validation conditions.
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Services;

use ArrayPress\Conditions\Clients\IPQualityScore as IPQSHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Helpers\Geo as GeoHelper;
use ArrayPress\Conditions\Options\Network;

/**
 * Class IPQualityScore
 *
 * Provides IPQualityScore fraud detection conditions.
 */
class IPQualityScore {

	/**
	 * Get all IPQualityScore conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Risk Score
			'ipqs_fraud_score'         => [
				'label'         => __( 'Fraud Score', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g., 75', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'IPQS fraud score (0-100). IPQS publishes recommended thresholds: ≥75 high risk, ≥85 very high risk, ≥90 likely fraud. Score weighs proxy/VPN/Tor flags, abuse history, ASN reputation, and geo-IP signals.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::get_fraud_score( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_high_risk'        => [
				'label'         => __( 'High Risk', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when IPQS fraud score is 75 or above. Equivalent to writing "Fraud Score >= 75".', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_high_risk( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Detection
			'ipqs_is_proxy'            => [
				'label'         => __( 'Proxy', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is on IPQS\'s proxy list — datacentre, hosting, or public proxy IPs. Use "Is Suspicious" for the broader proxy + VPN + Tor + abuse check.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_vpn'              => [
				'label'         => __( 'VPN', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is identified as a commercial VPN exit node. Privacy-conscious customers do use VPNs legitimately — pair with another signal (high cart total, new customer) before blocking outright.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_tor'              => [
				'label'         => __( 'Tor', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is a Tor exit node. Stronger fraud signal than VPN — Tor traffic on commerce checkouts is rare for legitimate buyers.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_bot'              => [
				'label'         => __( 'Bot/Automation', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP exhibits bot/automation traffic patterns (scraping, credential stuffing, automated checkout attempts). Strong block signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_bot( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_recent_abuse'        => [
				'label'         => __( 'Recent Abuse', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when IPQS has seen abuse reports for this IP in the recent past (typically last 90 days) — chargebacks, spam, account takeovers reported by other sites in their network.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::has_recent_abuse( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_suspicious'       => [
				'label'         => __( 'Suspicious', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when ANY of: proxy / VPN / Tor / recent abuse. Catches the most common evasion techniques in one check.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_suspicious( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Location
			'ipqs_country'             => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'ISO-2 country code from the IP geolocation (e.g. US, GB, DE). Useful for blocking high-fraud regions or matching against the customer\'s billing country.', 'arraypress' ),
				'options'       => GeoHelper::get_country_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPQSHelper::get_country( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Network
			'ipqs_asn'                 => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., 15169, 13335', 'arraypress' ),
				'description'   => __( 'Autonomous System Number — uniquely identifies the network the IP belongs to (e.g. 15169 = Google, 16509 = AWS, 13335 = Cloudflare). IPQS returns the bare number without an "AS" prefix. Useful for blocking entire hosting networks at once.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => IPQSHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_connection_type'     => [
				'label'         => __( 'Connection Type', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select connection types...', 'arraypress' ),
				'description'   => __( 'How the IP connects to the internet — Residential, Mobile, Corporate, Data Center, etc. "Data Center" on a checkout request is a strong fraud signal: legitimate customers don\'t buy from AWS or DigitalOcean IPs.', 'arraypress' ),
				'options'       => Network::get_connection_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPQSHelper::get_connection_type( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_isp'                 => [
				'label'         => __( 'ISP', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Comcast, Verizon', 'arraypress' ),
				'description'   => __( 'ISP / network provider name. Match against a watchlist of known-fraudulent or hosting-only providers. For blocking entire hosting networks, ASN is more precise.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => IPQSHelper::get_isp( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Email Validation
			'ipqs_is_disposable_email' => [
				'label'         => __( 'Disposable Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the email domain is on IPQS\'s disposable-provider list (Mailinator, 10MinuteMail, Guerrilla Mail, etc.). Strong fraud signal — legitimate customers rarely use throwaway addresses.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_disposable_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_valid_email'      => [
				'label'         => __( 'Valid Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the email passes IPQS validation (good MX records, deliverable mailbox, not a syntax error). Combine with the boolean operator "no" to catch malformed/undeliverable addresses.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_valid_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_leaked_email'     => [
				'label'         => __( 'Leaked Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the email has appeared in known data breaches (HaveIBeenPwned-style). Useful for ATO (account takeover) detection — leaked emails on new high-value orders deserve extra scrutiny.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_leaked_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_risky_email'      => [
				'label'         => __( 'Risky Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when ANY of: disposable, invalid, or leaked. Catches the broadest set of bad-email signals in one check.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_risky_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
		];
	}
}
