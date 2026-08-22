<?php
/**
 * Geo Conditions
 *
 * Convenience conditions for the most common geo-mismatch fraud signals.
 * The library does not perform geo-IP lookups itself — callers must pass
 * `ip_country`, `billing_country`, and (optionally) `card_country` /
 * `geo_distance_km` through the args array.
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\Geo as GeoHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class Geo
 *
 * Provides geographic-mismatch conditions.
 */
class Geo {

	/**
	 * Get all geo conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'Geo', 'arraypress' );

		return [
			'geo_ip_matches_billing_country'   => [
				'label'         => __( 'IP Country Matches Billing', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'True when the visitor\'s IP geolocates to the same country they\'re billing to. Mismatch is a classic fraud signal — fraudsters often shop from one country with billing details for another. Combine with operator "no" to flag mismatches. Note: legitimate travellers and VPN users will trip this, so pair with another signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::ip_matches_billing( $args ),
				'required_args' => [ 'ip_country', 'billing_country' ],
			],
			'geo_card_matches_billing_country' => [
				'label'         => __( 'Card Country Matches Billing', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'True when the card-issuing country matches the billing country. Stronger fraud signal than IP-vs-billing because the card country is set when the bank issued the card and isn\'t affected by VPNs or travel. Only available after the gateway tokenises the card (Stripe / PayPal capture).', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::card_matches_billing( $args ),
				'required_args' => [ 'card_country', 'billing_country' ],
			],
			'geo_billing_country_in_list'      => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match the customer\'s billing-address country against a list. Use "Is any of" to allow only specific countries; "Is none of" to block specific countries. Useful for blocking historically high-fraud regions or restricting fulfilment to a list of supported countries.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => GeoHelper::get_country_options(),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['billing_country'] ?? '' ) ),
				'required_args' => [ 'billing_country' ],
			],
			'geo_ip_country_in_list'           => [
				'label'         => __( 'IP Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match the IP-derived country against a list. Different from billing country — the IP is where the visitor is connecting from right now (which can be a VPN exit). Use "Is none of" to block traffic from specific regions, or "Is any of" to allow only specific regions.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => GeoHelper::get_country_options(),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['ip_country'] ?? '' ) ),
				'required_args' => [ 'ip_country' ],
			],
			'geo_distance_km'                  => [
				'label'         => __( 'IP-to-Billing Distance (km)', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Great-circle distance in kilometres between the IP geolocation and the billing address. Useful for catching fraudsters using a VPN exit on the wrong continent. Note: legitimate travellers and remote workers can hit this, so set thresholds high (e.g. ≥ 5000 km) and pair with another signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::get_distance_km( $args ),
				'required_args' => [ 'geo_distance_km' ],
			],
		];
	}
}
