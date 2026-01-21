<?php
/**
 * Network Helper
 *
 * Provides standardized network-related options for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

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

	/**
	 * Get fraud score risk level options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_risk_levels(): array {
		return [
			[ 'value' => 'low', 'label' => __( 'Low (0-25)', 'arraypress' ) ],
			[ 'value' => 'medium', 'label' => __( 'Medium (26-50)', 'arraypress' ) ],
			[ 'value' => 'high', 'label' => __( 'High (51-75)', 'arraypress' ) ],
			[ 'value' => 'critical', 'label' => __( 'Critical (76-100)', 'arraypress' ) ],
		];
	}

	/**
	 * Get email deliverability options.
	 *
	 * Used by IPQualityScore email validation.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_email_deliverability(): array {
		return [
			[ 'value' => 'high', 'label' => __( 'High', 'arraypress' ) ],
			[ 'value' => 'medium', 'label' => __( 'Medium', 'arraypress' ) ],
			[ 'value' => 'low', 'label' => __( 'Low', 'arraypress' ) ],
		];
	}

	/**
	 * Get phone line type options.
	 *
	 * Used by IPQualityScore phone validation.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_phone_line_types(): array {
		return [
			[ 'value' => 'landline', 'label' => __( 'Landline', 'arraypress' ) ],
			[ 'value' => 'wireless', 'label' => __( 'Wireless/Mobile', 'arraypress' ) ],
			[ 'value' => 'voip', 'label' => __( 'VoIP', 'arraypress' ) ],
			[ 'value' => 'toll_free', 'label' => __( 'Toll Free', 'arraypress' ) ],
			[ 'value' => 'premium', 'label' => __( 'Premium', 'arraypress' ) ],
			[ 'value' => 'satellite', 'label' => __( 'Satellite', 'arraypress' ) ],
			[ 'value' => 'pager', 'label' => __( 'Pager', 'arraypress' ) ],
		];
	}

}