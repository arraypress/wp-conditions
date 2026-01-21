<?php
/**
 * ProxyCheck Conditions
 *
 * Provides conditions for ProxyCheck.io IP analysis.
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Integrations\Services;

use ArrayPress\Conditions\Helpers\ProxyCheck as ProxyCheckHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class ProxyCheck
 *
 * Provides ProxyCheck.io IP analysis conditions.
 */
class ProxyCheck {

	/**
	 * Get all ProxyCheck conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_detection_conditions(),
			self::get_risk_conditions(),
			self::get_location_conditions(),
			self::get_network_conditions()
		);
	}

	/**
	 * Get detection-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detection_conditions(): array {
		return [
			'proxycheck_is_proxy'     => [
				'label'         => __( 'Is Proxy', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a proxy.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_vpn'       => [
				'label'         => __( 'Is VPN', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a VPN.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_should_block' => [
				'label'         => __( 'Should Block', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP should be blocked based on ProxyCheck analysis.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::should_block( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_type'         => [
				'label'         => __( 'Proxy Type', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'The detected proxy/VPN type.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'VPN', 'label' => __( 'VPN', 'arraypress' ) ],
					[ 'value' => 'TOR', 'label' => __( 'TOR', 'arraypress' ) ],
					[ 'value' => 'SOCKS', 'label' => __( 'SOCKS', 'arraypress' ) ],
					[ 'value' => 'SOCKS4', 'label' => __( 'SOCKS4', 'arraypress' ) ],
					[ 'value' => 'SOCKS4A', 'label' => __( 'SOCKS4A', 'arraypress' ) ],
					[ 'value' => 'SOCKS5', 'label' => __( 'SOCKS5', 'arraypress' ) ],
					[ 'value' => 'SOCKS5H', 'label' => __( 'SOCKS5H', 'arraypress' ) ],
					[ 'value' => 'Shadowsocks', 'label' => __( 'Shadowsocks', 'arraypress' ) ],
					[ 'value' => 'HTTP', 'label' => __( 'HTTP', 'arraypress' ) ],
					[ 'value' => 'HTTPS', 'label' => __( 'HTTPS', 'arraypress' ) ],
					[ 'value' => 'Compromised Server', 'label' => __( 'Compromised Server', 'arraypress' ) ],
					[ 'value' => 'Inference Engine', 'label' => __( 'Inference Engine', 'arraypress' ) ],
					[ 'value' => 'OpenVPN', 'label' => __( 'OpenVPN', 'arraypress' ) ],
					[ 'value' => 'WireGuard', 'label' => __( 'WireGuard', 'arraypress' ) ],
				],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_type( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
		];
	}

	/**
	 * Get risk-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_risk_conditions(): array {
		return [
			'proxycheck_risk_score'     => [
				'label'         => __( 'Risk Score', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Risk', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'The risk score for the IP (0-100).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_risk_score( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_attack_history' => [
				'label'         => __( 'Attack History Count', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Risk', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of recorded attacks from this IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_attack_history( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
		];
	}

	/**
	 * Get location-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_location_conditions(): array {
		return [
			'proxycheck_country'   => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. US, GB, DE', 'arraypress' ),
				'description'   => __( 'The country code of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_country( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_continent' => [
				'label'         => __( 'Continent', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select continents...', 'arraypress' ),
				'description'   => __( 'The continent of the IP.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'Africa', 'label' => __( 'Africa', 'arraypress' ) ],
					[ 'value' => 'Antarctica', 'label' => __( 'Antarctica', 'arraypress' ) ],
					[ 'value' => 'Asia', 'label' => __( 'Asia', 'arraypress' ) ],
					[ 'value' => 'Europe', 'label' => __( 'Europe', 'arraypress' ) ],
					[ 'value' => 'North America', 'label' => __( 'North America', 'arraypress' ) ],
					[ 'value' => 'Oceania', 'label' => __( 'Oceania', 'arraypress' ) ],
					[ 'value' => 'South America', 'label' => __( 'South America', 'arraypress' ) ],
				],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_continent( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_city'      => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. London, New York', 'arraypress' ),
				'description'   => __( 'The city of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_city( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_region'    => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. California, England', 'arraypress' ),
				'description'   => __( 'The region or state of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_region( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_timezone'  => [
				'label'         => __( 'Timezone', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. America/New_York', 'arraypress' ),
				'description'   => __( 'The timezone of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_timezone( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
		];
	}

	/**
	 * Get network-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_network_conditions(): array {
		return [
			'proxycheck_operator' => [
				'label'         => __( 'Operator/ISP', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Network', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Cloudflare, Amazon', 'arraypress' ),
				'description'   => __( 'The network operator or ISP of the IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_operator( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_port'     => [
				'label'         => __( 'Port', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Network', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 8080', 'arraypress' ),
				'min'           => 0,
				'max'           => 65535,
				'step'          => 1,
				'description'   => __( 'The detected port for the proxy.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_port( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
		];
	}

}