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

use ArrayPress\Conditions\Helpers\CartClock;
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

			'edd_checkout_address'        => [
				'label'         => __( 'Address', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. PO Box', 'arraypress' ),
				'description'   => __( 'The first line of the billing address entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_address( $args ),
				'required_args' => [],
			],
			'edd_checkout_address_2'      => [
				'label'         => __( 'Address Line 2', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Apt', 'arraypress' ),
				'description'   => __( 'The second line of the billing address entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_address_2( $args ),
				'required_args' => [],
			],
			'edd_checkout_discount_code'  => [
				'label'         => __( 'Discount Code', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. SAVE10', 'arraypress' ),
				'description'   => __( 'The discount code entered at checkout, before it has been validated or applied.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_discount_code( $args ),
				'required_args' => [],
			],
			'edd_checkout_has_discount'   => [
				'label'         => __( 'Discount Entered', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether any discount code was entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::has_discount_code( $args ),
				'required_args' => [],
			],

			'edd_checkout_address_is_po_box'       => [
				'label'         => __( 'Address Is PO Box', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the billing address typed is a post office box rather than a door.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::is_po_box( $args ),
				'required_args' => [ 'posted' ],
			],
			'edd_checkout_address_has_street_number' => [
				'label'         => __( 'Address Has Street Number', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the billing address typed carries a number. An address with no number is usually a form filled in a hurry, by a script or a person who does not mean it.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::has_street_number( $args ),
				'required_args' => [ 'posted' ],
			],
			'edd_checkout_names_identical'         => [
				'label'         => __( 'First and Last Name Identical', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the first and last names typed are the same word. "John John" is what a form filler produces.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::names_identical( $args ),
				'required_args' => [ 'posted' ],
			],
			'edd_checkout_name_matches_email'      => [
				'label'         => __( 'Name Appears in Email', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the name typed appears in the email typed: "jane.doe", "jdoe", "doej". A real person\'s address tends to carry their name and a generated one does not. A legitimacy signal, so "is no" is the one to pair with other flags.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::name_matches_email( $args ),
				'required_args' => [ 'posted' ],
			],
			'edd_checkout_seconds_since_cart_started' => [
				'label'         => __( 'Seconds Since Cart Started', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 20', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many seconds passed between the first item going into the cart and this checkout. A bot fills a cart and pays in seconds; a person takes minutes. The start is noted in the shopper\'s session the moment something is added, or passed by the host as cart_started_at; when neither knows, the rule does not apply.', 'arraypress' ),
				'compare_value' => fn( $args ) => CartClock::seconds_since_start( $args ),
				'required_args' => [],
			],
		];
	}
}
