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
use ArrayPress\Countries\Countries;
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
				'description'   => __( 'The fraud score (0-100). Higher = more risky.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::get_fraud_score( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_high_risk'        => [
				'label'         => __( 'Is High Risk', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the fraud score is 75 or above.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_high_risk( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Detection
			'ipqs_is_proxy'            => [
				'label'         => __( 'Is Proxy', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a proxy.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_proxy( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_vpn'              => [
				'label'         => __( 'Is VPN', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a VPN.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_vpn( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_tor'              => [
				'label'         => __( 'Is Tor', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is a Tor exit node.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_tor( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_bot'              => [
				'label'         => __( 'Is Bot', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP is identified as a bot.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_bot( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_recent_abuse'        => [
				'label'         => __( 'Has Recent Abuse', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the IP has recent abuse reports.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::has_recent_abuse( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],
			'ipqs_is_suspicious'       => [
				'label'         => __( 'Is Suspicious', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if IP is suspicious (proxy, VPN, Tor, or recent abuse).', 'arraypress' ),
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
				'description'   => __( 'The country of the IP address.', 'arraypress' ),
				'options'       => Countries::get_options(),
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
				'description'   => __( 'The Autonomous System Number.', 'arraypress' ),
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
				'description'   => __( 'The connection type. Data Center is a strong fraud signal.', 'arraypress' ),
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
				'description'   => __( 'The Internet Service Provider name.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => IPQSHelper::get_isp( $args ),
				'required_args' => [ 'ip', 'ipqs_api_key' ],
			],

			// Email Validation
			'ipqs_is_disposable_email' => [
				'label'         => __( 'Is Disposable Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the email is from a disposable provider.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_disposable_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_valid_email'      => [
				'label'         => __( 'Is Valid Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the email address is valid.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_valid_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_leaked_email'     => [
				'label'         => __( 'Is Leaked Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the email has appeared in data breaches.', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_leaked_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
			'ipqs_is_risky_email'      => [
				'label'         => __( 'Is Risky Email', 'arraypress' ),
				'group'         => __( 'IPQualityScore', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if email is risky (disposable, invalid, or leaked).', 'arraypress' ),
				'compare_value' => fn( $args ) => IPQSHelper::is_risky_email( $args ),
				'required_args' => [ 'email', 'ipqs_api_key' ],
			],
		];
	}

}