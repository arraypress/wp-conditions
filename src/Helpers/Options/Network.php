<?php
/**
 * Network Options Helper
 *
 * Provides standardized network-related options for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers\Options
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Options;

/**
 * Class Network
 *
 * Standardized network definitions for condition fields.
 */
class Network {

	/**
	 * Get network/ASN type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_types(): array {
		return [
			[ 'value' => 'isp', 'label' => __( 'ISP', 'arraypress' ) ],
			[ 'value' => 'hosting', 'label' => __( 'Hosting', 'arraypress' ) ],
			[ 'value' => 'business', 'label' => __( 'Business', 'arraypress' ) ],
			[ 'value' => 'education', 'label' => __( 'Education', 'arraypress' ) ],
		];
	}

	/**
	 * Get connection type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_connection_types(): array {
		return [
			[ 'value' => 'residential', 'label' => __( 'Residential', 'arraypress' ) ],
			[ 'value' => 'corporate', 'label' => __( 'Corporate', 'arraypress' ) ],
			[ 'value' => 'education', 'label' => __( 'Education', 'arraypress' ) ],
			[ 'value' => 'mobile', 'label' => __( 'Mobile', 'arraypress' ) ],
			[ 'value' => 'datacenter', 'label' => __( 'Data Center', 'arraypress' ) ],
		];
	}

	/**
	 * Get proxy type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_proxy_types(): array {
		return [
			[ 'value' => 'VPN', 'label' => __( 'VPN', 'arraypress' ) ],
			[ 'value' => 'TOR', 'label' => __( 'Tor', 'arraypress' ) ],
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
		];
	}

	/**
	 * Get abuse velocity options.
	 *
	 * Used by IPQualityScore.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_abuse_velocities(): array {
		return [
			[ 'value' => 'none', 'label' => __( 'None', 'arraypress' ) ],
			[ 'value' => 'low', 'label' => __( 'Low', 'arraypress' ) ],
			[ 'value' => 'medium', 'label' => __( 'Medium', 'arraypress' ) ],
			[ 'value' => 'high', 'label' => __( 'High', 'arraypress' ) ],
		];
	}

}