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
use ArrayPress\Countries\Countries;

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
		return array_merge(
			self::get_detection_conditions(),
			self::get_risk_conditions(),
			self::get_location_conditions(),
			self::get_network_conditions(),
			self::get_email_conditions()
		);
	}

	/**
	 * Get proxy/VPN detection conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detection_conditions(): array {
		return [
			'proxycheck_is_proxy'      => [
				'label'         => __( 'Is Proxy', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a proxy.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_vpn'        => [
				'label'         => __( 'Is VPN', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a VPN.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_suspicious' => [
				'label'         => __( 'Is Suspicious', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is suspicious (proxy or VPN).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_suspicious( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_should_block'  => [
				'label'         => __( 'Should Block', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if ProxyCheck recommends blocking this IP.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::should_block( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_type'          => [
				'label'         => __( 'Proxy Type', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Detection', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select types...', 'arraypress' ),
				'description'   => __( 'The detected proxy/VPN type.', 'arraypress' ),
				'options'       => fn() => Network::get_proxy_types(),
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
			'proxycheck_risk_score'   => [
				'label'         => __( 'Risk Score', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Risk', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g., 50', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'The risk score (0-100). Higher = more risky.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_risk_score( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_high_risk' => [
				'label'         => __( 'Is High Risk', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Risk', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the risk score is 50 or above.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_high_risk( $args ),
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
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The country of the IP address.', 'arraypress' ),
				'options'       => fn() => Countries::get_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_country_code( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_continent' => [
				'label'         => __( 'Continent', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select continents...', 'arraypress' ),
				'description'   => __( 'The continent of the IP address.', 'arraypress' ),
				'options'       => fn() => Countries::get_continent_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_continent_code( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_is_eu'     => [
				'label'         => __( 'Is EU Country', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is from an EU country.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_eu_country( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_city'      => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Location', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g., London', 'arraypress' ),
				'description'   => __( 'The city of the IP address.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_city( $args ),
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
			'proxycheck_asn'          => [
				'label'         => __( 'ASN', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Network', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., AS15169', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'The Autonomous System Number.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_asn( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_provider'     => [
				'label'         => __( 'Provider', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Network', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Google LLC', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'The network provider name.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_provider( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
			'proxycheck_organisation' => [
				'label'         => __( 'Organisation', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Network', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g., Google LLC', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'The organisation name.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::get_organisation( $args ),
				'required_args' => [ 'ip', 'proxycheck_api_key' ],
			],
		];
	}

	/**
	 * Get email validation conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_email_conditions(): array {
		return [
			'proxycheck_is_disposable_email' => [
				'label'         => __( 'Is Disposable Email', 'arraypress' ),
				'group'         => __( 'ProxyCheck: Email', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the email is from a disposable provider.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProxyCheckHelper::is_disposable_email( $args ),
				'required_args' => [ 'email', 'proxycheck_api_key' ],
			],
		];
	}

}