<?php
/**
 * IPInfo Conditions
 *
 * Provides conditions for IPInfo.io IP geolocation and analysis.
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\Services
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Integrations\Services;

use ArrayPress\Conditions\Helpers\Geography;
use ArrayPress\Conditions\Helpers\Network;
use ArrayPress\Conditions\Helpers\Services\IPInfo as IPInfoHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class IPInfo
 *
 * Provides IPInfo.io IP geolocation and analysis conditions.
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
			self::get_network_conditions(),
			self::get_company_conditions()
		);
	}

	/**
	 * Get location-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_location_conditions(): array {
		return [
			'ipinfo_country'   => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The country of the IP address.', 'arraypress' ),
				'options'       => fn() => Geography::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_country( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_continent' => [
				'label'         => __( 'Continent', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select continents...', 'arraypress' ),
				'description'   => __( 'The continent of the IP address.', 'arraypress' ),
				'options'       => fn() => Geography::get_continents(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_continent( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_region'    => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. California, England', 'arraypress' ),
				'description'   => __( 'The region or state of the IP address.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_region( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_city'      => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. London, New York', 'arraypress' ),
				'description'   => __( 'The city of the IP address.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_city( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_postal'    => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 90210, SW1A 1AA', 'arraypress' ),
				'description'   => __( 'The postal code of the IP address.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_postal( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_timezone'  => [
				'label'         => __( 'Timezone', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select timezones...', 'arraypress' ),
				'description'   => __( 'The timezone of the IP address.', 'arraypress' ),
				'options'       => fn() => Geography::get_timezones(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_timezone( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_eu'     => [
				'label'         => __( 'Is EU', 'arraypress' ),
				'group'         => __( 'IPInfo: Location', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is located in the European Union.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_eu( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

	/**
	 * Get privacy detection conditions.
	 *
	 * Requires Business or Premium IPInfo plan.
	 *
	 * @return array<string, array>
	 */
	private static function get_privacy_conditions(): array {
		return [
			'ipinfo_is_vpn'          => [
				'label'         => __( 'Is VPN', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a VPN. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_proxy'        => [
				'label'         => __( 'Is Proxy', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a proxy. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_tor'          => [
				'label'         => __( 'Is Tor', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a Tor exit node. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_relay'        => [
				'label'         => __( 'Is Relay', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a relay (e.g., iCloud Private Relay). Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_relay( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_hosting'      => [
				'label'         => __( 'Is Hosting', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP belongs to a hosting provider. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_hosting( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_privacy_service' => [
				'label'         => __( 'Privacy Service', 'arraypress' ),
				'group'         => __( 'IPInfo: Privacy', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. NordVPN, ExpressVPN', 'arraypress' ),
				'description'   => __( 'The detected VPN/proxy service name. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_privacy_service( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

	/**
	 * Get network/ASN conditions.
	 *
	 * Requires Basic plan or above.
	 *
	 * @return array<string, array>
	 */
	private static function get_network_conditions(): array {
		return [
			'ipinfo_asn'        => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. AS15169', 'arraypress' ),
				'description'   => __( 'The Autonomous System Number of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_asn_name'   => [
				'label'         => __( 'ASN Name', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Google LLC', 'arraypress' ),
				'description'   => __( 'The organization name associated with the ASN.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn_name( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_asn_domain' => [
				'label'         => __( 'ASN Domain', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'The domain associated with the ASN.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_asn_domain( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_asn_type'   => [
				'label'         => __( 'ASN Type', 'arraypress' ),
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
			'ipinfo_hostname'   => [
				'label'         => __( 'Hostname', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. dns.google', 'arraypress' ),
				'description'   => __( 'The hostname of the IP address.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_hostname( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_is_anycast' => [
				'label'         => __( 'Is Anycast', 'arraypress' ),
				'group'         => __( 'IPInfo: Network', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is an anycast address.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::is_anycast( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

	/**
	 * Get company conditions.
	 *
	 * Requires Business or Premium IPInfo plan.
	 *
	 * @return array<string, array>
	 */
	private static function get_company_conditions(): array {
		return [
			'ipinfo_company_name'   => [
				'label'         => __( 'Company Name', 'arraypress' ),
				'group'         => __( 'IPInfo: Company', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Google LLC', 'arraypress' ),
				'description'   => __( 'The company name associated with the IP. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_company_name( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_company_domain' => [
				'label'         => __( 'Company Domain', 'arraypress' ),
				'group'         => __( 'IPInfo: Company', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. google.com', 'arraypress' ),
				'description'   => __( 'The company domain associated with the IP. Requires Business plan.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPInfoHelper::get_company_domain( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
			'ipinfo_company_type'   => [
				'label'         => __( 'Company Type', 'arraypress' ),
				'group'         => __( 'IPInfo: Company', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'The type of company. Requires Business plan.', 'arraypress' ),
				'options'       => fn() => Network::get_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => IPInfoHelper::get_company_type( $args ),
				'required_args' => [ 'ip', 'ipinfo_api_key' ],
			],
		];
	}

}