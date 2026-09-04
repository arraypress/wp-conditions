<?php
/**
 * IPInfo Conditions
 *
 * Geolocation and privacy detection conditions.
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
use ArrayPress\Conditions\Clients\IPInfo as IPInfoHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Helpers\Geo as GeoHelper;

/**
 * Class IPInfo
 *
 * Provides IPInfo.io geolocation and fraud detection conditions.
 */
class IPInfo {

	/**
	 * Get all IPInfo conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Location
			'ipinfo_country'       => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'ISO-2 country code from the IP geolocation (e.g. US, GB, DE). Useful for blocking high-fraud regions or matching against the customer\'s billing country.', 'arraypress' ),
				'options'       => fn() => GeoHelper::get_country_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_country( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_region'        => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g., California, Texas, England', 'arraypress' ),
				'description'   => __( 'Region/state name from the IP geolocation. Names vary by country (US states, UK home nations, German Länder, etc.). Geo-IP region data is approximate — usually accurate to the metro area.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_region( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_eu'         => [
				'label'         => __( 'EU Country', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP geolocates to an EU member state. Convenience for GDPR-aware rules or EU-only fulfilment.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_eu( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],

			// Network
			'ipinfo_asn'           => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., AS15169, AS13335', 'arraypress' ),
				'description'   => __( 'Autonomous System Number — uniquely identifies the network the IP belongs to (e.g. AS15169 = Google, AS16509 = AWS, AS13335 = Cloudflare). IPInfo returns the AS-prefixed format. Useful for blocking entire hosting networks at once.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_asn_type'      => [
				'label'         => __( 'Network Type', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'Coarse classification of the network — ISP (residential), Hosting (datacentre), Business (corporate), Education. "Hosting" on a checkout request is a strong fraud signal: legitimate customers don\'t buy from AWS / DigitalOcean.', 'arraypress' ),
				'options'       => Network::get_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn_type( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],

			// Privacy Detection
			'ipinfo_is_vpn'        => [
				'label'         => __( 'VPN', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is identified as a commercial VPN exit node. Privacy-conscious customers do use VPNs legitimately — pair with another signal (high cart total, new customer) before blocking outright.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_proxy'      => [
				'label'         => __( 'Proxy', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is on IPInfo\'s proxy list — public web proxies, anonymising relays, residential-proxy networks. Use "Is Suspicious" for the broader privacy/anonymity check.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_tor'        => [
				'label'         => __( 'Tor', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is a Tor exit node. Stronger fraud signal than VPN — Tor traffic on commerce checkouts is rare for legitimate buyers.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_relay'      => [
				'label'         => __( 'Privacy Relay', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP is part of an anonymising relay service — most commonly iCloud Private Relay used by Apple devices. Many legitimate iOS/macOS customers route through Private Relay; treat this as a soft signal, not a block trigger.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_relay( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_hosting'    => [
				'label'         => __( 'Hosting/Datacenter', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'True when the IP belongs to a hosting provider or datacentre (AWS, Google Cloud, OVH, DigitalOcean, etc.). Strong fraud signal on a checkout — legitimate customers don\'t buy from server IPs.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_hosting( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_suspicious' => [
				'label'         => __( 'Suspicious', 'arraypress' ),
				'group'         => __( 'IPInfo', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Convenience boolean — true when ANY of: VPN / proxy / Tor / hosting. Catches the most common evasion techniques in one check.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_suspicious( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}
}
