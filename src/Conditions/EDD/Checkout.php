<?php
/**
 * EDD Checkout Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Checkout as CheckoutHelper;
use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Operators;

/**
 * Class Checkout
 *
 * Provides EDD checkout-related conditions.
 */
class Checkout {

	/**
	 * Get all checkout conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Payment
			'edd_checkout_gateway'    => [
				'label'         => __( 'Gateway', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway selected at checkout.', 'arraypress' ),
				'options'       => fn() => Options::get_gateways(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_gateway( $args ),
				'required_args' => [],
			],

			// Customer
			'edd_checkout_email'      => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match checkout email against patterns. Supports: full email, @domain, .tld, or domain.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_email( $args ),
				'required_args' => [],
			],
			'edd_checkout_first_name' => [
				'label'         => __( 'First Name', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'The first name entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_first_name( $args ),
				'required_args' => [],
			],
			'edd_checkout_last_name'  => [
				'label'         => __( 'Last Name', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Doe', 'arraypress' ),
				'description'   => __( 'The last name entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_last_name( $args ),
				'required_args' => [],
			],

			// Address
			'edd_checkout_country'    => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country entered at checkout.', 'arraypress' ),
				'options'       => fn() => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_country( $args ),
				'required_args' => [],
			],
			'edd_checkout_region'     => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. CA, NY, TX', 'arraypress' ),
				'description'   => __( 'The billing region/state entered at checkout.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_region( $args ),
				'required_args' => [],
			],
			'edd_checkout_city'       => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. Los Angeles, New York', 'arraypress' ),
				'description'   => __( 'The billing city entered at checkout.', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_city( $args ),
				'required_args' => [],
			],
			'edd_checkout_postcode'   => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. 90210, SW1A, 902', 'arraypress' ),
				'description'   => __( 'The billing postal/zip code. Supports prefix matching (e.g., 902 matches 90210).', 'arraypress' ),
				'operators'     => Operators::tags(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_postcode( $args ),
				'required_args' => [],
			],
		];
	}

}