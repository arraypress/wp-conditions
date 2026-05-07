<?php
/**
 * ProxyCheck Conditions
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

use ArrayPress\Conditions\Options\Network;
use ArrayPress\Conditions\Clients\ProxyCheck as ProxyCheckHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Helpers\Geo as GeoHelper;

/**
 * Class ProxyCheck
 *
 * Provides ProxyCheck.io fraud detection conditions.
 */
class ProxyCheck {

	/**
	 * Get all ProxyCheck conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Risk Score
			'proxycheck_risk_score'          => [
				'label'         => __( 'Risk Score', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g., 50', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'ProxyCheck risk score (0-100). Common thresholds: ≥50 suspicious, ≥80 likely fraud, ≥90 almost certainly fraud. Combines proxy/VPN detection, abuse history, and disposable-email signals into one number.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_risk_score( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_high_risk'        => [
				'label'         => __( 'High Risk', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when ProxyCheck\'s risk score is 50 or above. Equivalent to writing "Risk Score >= 50" but easier to read in rules.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_high_risk( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],

			// Detection
			'proxycheck_is_proxy'            => [
				'label'         => __( 'Proxy', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is on ProxyCheck\'s proxy list — typically datacentre / hosting / public proxy IPs. Use "Is Suspicious" if you want to catch both proxies and VPNs in one check.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_vpn'              => [
				'label'         => __( 'VPN', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is identified as a commercial VPN exit node (NordVPN, ExpressVPN, etc.). Many legitimate privacy-conscious customers use VPNs, so consider pairing with another signal (high cart total, new customer) before blocking outright.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_suspicious'       => [
				'label'         => __( 'Suspicious', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when the IP is either a proxy OR a VPN. Catches the most common evasion attempts in one check.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_suspicious( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_should_block'        => [
				'label'         => __( 'Should Block', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'ProxyCheck\'s own recommendation — true when their algorithm thinks this IP should be blocked. Combines risk score, proxy/VPN flags, and abuse history. Stricter than "Is Suspicious".', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::should_block( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_type'                => [
				'label'         => __( 'Proxy Type', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'Type of proxy/VPN detected — VPN, TOR, SOCKS, public proxy, etc. Useful when you want to allow some types (e.g. legitimate corporate VPNs) but block others (Tor, public proxies).', 'arraypress' ),
				'options'       => Network::get_proxy_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_type( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],

			// Location
			'proxycheck_country'             => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'ISO-2 country code of the IP geolocation (e.g. US, GB, DE). Useful for blocking high-fraud regions or matching against the customer\'s billing country.', 'arraypress' ),
				'options'       => GeoHelper::get_country_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_country_code( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_continent'           => [
				'label'         => __( 'Continent', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select continents...', 'arraypress' ),
				'description'   => __( 'Continent code of the IP geolocation (AF, AS, EU, NA, OC, SA, AN). Useful for broad geographic blocks.', 'arraypress' ),
				'options'       => GeoHelper::get_continent_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_continent_code( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_eu'               => [
				'label'         => __( 'EU Country', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP geolocates to an EU member state. Convenience for GDPR-aware rules or EU-only fulfilment.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_eu_country( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_city'                => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g., London', 'arraypress' ),
				'description'   => __( 'City name from the IP geolocation (e.g. London, New York). Geo-IP city data is approximate — usually accurate to the metro area, not a specific neighbourhood.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_city( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],

			// Network
			'proxycheck_asn'                 => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., AS15169', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Autonomous System Number — uniquely identifies the network the IP belongs to (e.g. AS15169 = Google, AS16509 = AWS). Useful for blocking entire hosting networks at once. Bulk-add ASNs by network operator.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_provider'            => [
				'label'         => __( 'Provider', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Google LLC', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'ISP / network provider name (e.g. Comcast, Deutsche Telekom). Match against a watchlist of known-fraudulent or hosting-only providers.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_provider( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_organisation'        => [
				'label'         => __( 'Organisation', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Google LLC', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Organisation name registered to the IP block (often the same as Provider, but not always — e.g. corporate networks). Match against a watchlist.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_organisation( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_network_type'        => [
				'label'         => __( 'Network Type', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select network types...', 'arraypress' ),
				'description'   => __( 'Underlying connection type of the IP — Hosting (datacentre), Residential, Mobile, Business. Distinct from "Proxy Type": a residential connection on a VPN reports network=Residential and type=VPN. Hosting traffic at a checkout is almost always automated.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'Hosting', 'label' => __( 'Hosting', 'arraypress' ) ],
					[ 'value' => 'Residential', 'label' => __( 'Residential', 'arraypress' ) ],
					[ 'value' => 'Mobile', 'label' => __( 'Mobile', 'arraypress' ) ],
					[ 'value' => 'Business', 'label' => __( 'Business', 'arraypress' ) ],
				],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_network_type( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],

			// Operator (VPN provider)
			'proxycheck_operator'            => [
				'label'         => __( 'VPN Operator', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Mullvad, NordVPN', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Commercial VPN provider name (Mullvad, NordVPN, ExpressVPN, etc.). Matches both the primary operator AND any sibling operators sharing the same exit infrastructure — e.g. a rule for "Mullvad" still matches IPs whose primary record is Hide.me with Mullvad listed in additional_operators.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_operator_names( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_no_logs_policy'      => [
				'label'         => __( 'No-Logs Policy', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the VPN operator advertises a no-logs policy. Strong privacy-VPN signal — a no-logs operator is the preferred choice for anyone trying to avoid being identified after the fact, including fraudsters.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_no_logs_policy( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_accepts_crypto'      => [
				'label'         => __( 'Accepts Crypto Payments', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the VPN operator accepts crypto payments. Combined with no-logs and a high cart total, this is a textbook stolen-card-from-a-privacy-VPN pattern.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::accepts_crypto_payments( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_accepts_anonymous'   => [
				'label'         => __( 'Accepts Anonymous Payments', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the VPN operator accepts cash / gift-card / other untraceable payment methods. Same fraud-correlation reasoning as crypto — no payment audit trail tying the user to an identity.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::accepts_anonymous_payments( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_free_vpn'         => [
				'label'         => __( 'Free VPN', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the VPN is free-to-use (no paid tier required). Free VPNs over-index for fraud — there is no payment card on file with the operator that could tie the user to a real identity.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_free_vpn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],

			// Email
			'proxycheck_is_disposable_email' => [
				'label'         => __( 'Disposable Email', 'arraypress' ),
				'group'         => __( 'ProxyCheck', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the email domain is on ProxyCheck\'s disposable-provider list (Mailinator, 10MinuteMail, Guerrilla Mail, etc.). Strong fraud signal — legitimate customers rarely use throwaway addresses.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_disposable_email( $args ),
				'required_args' => [ 'email', 'proxycheck_api_key' ],
			],
		];
	}

}