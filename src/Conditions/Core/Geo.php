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
use ArrayPress\Countries\Countries;

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
				'description'   => __( 'Whether the IP-derived country matches the billing country. Mismatch is a classic fraud signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::ip_matches_billing( $args ),
				'required_args' => [ 'ip_country', 'billing_country' ],
			],
			'geo_card_matches_billing_country' => [
				'label'         => __( 'Card Country Matches Billing', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Whether the card-issuing country matches the billing country. Available post-tokenization (e.g. Stripe).', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::card_matches_billing( $args ),
				'required_args' => [ 'card_country', 'billing_country' ],
			],
			'geo_billing_country_in_list'      => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match the billing country against a list (e.g. high-risk-country watchlist).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Countries::get_options(),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['billing_country'] ?? '' ) ),
				'required_args' => [ 'billing_country' ],
			],
			'geo_ip_country_in_list'           => [
				'label'         => __( 'IP Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'Match the IP-derived country against a list (e.g. high-risk-country watchlist).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Countries::get_options(),
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
				'description'   => __( 'Great-circle distance between the IP geolocation and the billing address. Caller computes via Geo::haversine() and passes through args.', 'arraypress' ),
				'compare_value' => fn( $args ) => GeoHelper::get_distance_km( $args ),
				'required_args' => [ 'geo_distance_km' ],
			],
		];
	}

}
