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

namespace ArrayPress\Conditions\Conditions\Integrations\Services;

use ArrayPress\Conditions\Helpers\Network;
use ArrayPress\Conditions\Clients\IPInfo as IPInfoHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Countries\Countries;

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
		return array_merge(
			self::get_location_conditions(),
			self::get_privacy_conditions(),
			self::get_network_conditions()
		);
	}

	/**
	 * Get location-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_location_conditions(): array {
		return [
			'ipinfo_country' => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The country of the IP address.', 'arraypress' ),
				'options'       => fn() => Countries::get_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_country( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_region'  => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g., California, Texas, England', 'arraypress' ),
				'description'   => __( 'The region or state of the IP address. Useful for state-level rules.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_region( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_eu'   => [
				'label'         => __( 'Is EU', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is in the European Union. Useful for GDPR/VAT.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_eu( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

	/**
	 * Get privacy/fraud detection conditions.
	 *
	 * Requires Business or Premium IPInfo plan.
	 *
	 * @return array<string, array>
	 */
	private static function get_privacy_conditions(): array {
		return [
			'ipinfo_is_vpn'        => [
				'label'         => __( 'Is VPN', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a VPN.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_proxy'      => [
				'label'         => __( 'Is Proxy', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a proxy.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_tor'        => [
				'label'         => __( 'Is Tor', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a Tor exit node.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_relay'      => [
				'label'         => __( 'Is Relay', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a relay (e.g., iCloud Private Relay). Typically legitimate.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_relay( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_hosting'    => [
				'label'         => __( 'Is Hosting/Datacenter', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is from a hosting provider or datacenter.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_hosting( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_suspicious' => [
				'label'         => __( 'Is Suspicious', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if IP is suspicious (VPN, proxy, Tor, or datacenter). Excludes relays.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_suspicious( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

	/**
	 * Get network/ASN conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_network_conditions(): array {
		return [
			'ipinfo_asn'      => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g., AS15169, AS13335', 'arraypress' ),
				'description'   => __( 'The Autonomous System Number. Supports comma-separated list.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_asn_type' => [
				'label'         => __( 'Network Type', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'The type of network (ISP, hosting, business, education).', 'arraypress' ),
				'options'       => fn() => Network::get_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn_type( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

}