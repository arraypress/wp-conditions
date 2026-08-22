<?php
/**
 * WooCommerce Checkout Conditions
 *
 * These read the form as it stands, which is what a rule evaluated during
 * checkout sees. The equivalent Order conditions read the same fields once the
 * order exists -- use those for anything evaluated after the fact.
 *
 * @package     ArrayPress\Conditions\Conditions\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\WooCommerce;

use ArrayPress\Conditions\Integrations\WooCommerce\Checkout as CheckoutHelper;
use ArrayPress\Conditions\Integrations\WooCommerce\Options;
use ArrayPress\Conditions\Operators;

/**
 * Class Checkout
 *
 * Provides WooCommerce checkout conditions.
 */
class Checkout {

	/**
	 * Get all checkout conditions.
	 *
	 * @return array<string, array>
	 *
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		$payment = __( 'Checkout: Payment', 'arraypress' );
		$details = __( 'Checkout: Details', 'arraypress' );
		$address = __( 'Checkout: Address', 'arraypress' );

		return [
			// Payment and delivery.
			'wc_checkout_gateway'          => [
				'label'         => __( 'Payment Method', 'arraypress' ),
				'group'         => $payment,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment method chosen at checkout. Every registered gateway is listed, not only the enabled ones, so a rule keeps its meaning while a gateway is switched off.', 'arraypress' ),
				'options'       => Options::get_gateways(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_gateway( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_method'  => [
				'label'         => __( 'Shipping Method', 'arraypress' ),
				'group'         => $payment,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select shipping method...', 'arraypress' ),
				'description'   => __( 'The shipping methods chosen at checkout. A cart split across packages reports one per package.', 'arraypress' ),
				'options'       => Options::get_shipping_methods(),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_methods( $args ),
				'required_args' => [],
			],
			'wc_checkout_creating_account' => [
				'label'         => __( 'Creating Account', 'arraypress' ),
				'group'         => $payment,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer asked to create an account during checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::is_creating_account( $args ),
				'required_args' => [],
			],

			// Details.
			'wc_checkout_email'            => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => $details,
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'The billing email entered at checkout. Falls back to the customer session when the form has not been submitted yet.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_email( $args ),
				'required_args' => [],
			],
			'wc_checkout_first_name'       => [
				'label'         => __( 'First Name', 'arraypress' ),
				'group'         => $details,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'The billing first name entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_first_name( $args ),
				'required_args' => [],
			],
			'wc_checkout_last_name'        => [
				'label'         => __( 'Last Name', 'arraypress' ),
				'group'         => $details,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Smith', 'arraypress' ),
				'description'   => __( 'The billing last name entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_last_name( $args ),
				'required_args' => [],
			],
			'wc_checkout_company'          => [
				'label'         => __( 'Company', 'arraypress' ),
				'group'         => $details,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Acme Ltd', 'arraypress' ),
				'description'   => __( 'The billing company entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_company( $args ),
				'required_args' => [],
			],
			'wc_checkout_phone'            => [
				'label'         => __( 'Phone', 'arraypress' ),
				'group'         => $details,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. +44', 'arraypress' ),
				'description'   => __( 'The billing phone entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_phone( $args ),
				'required_args' => [],
			],
			'wc_checkout_note'             => [
				'label'         => __( 'Order Note', 'arraypress' ),
				'group'         => $details,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. urgent', 'arraypress' ),
				'description'   => __( 'The note left in the order comments field.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_note( $args ),
				'required_args' => [],
			],

			// Address.
			'wc_checkout_country'          => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => $address,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The billing country entered at checkout.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_country( $args ),
				'required_args' => [],
			],
			'wc_checkout_region'           => [
				'label'         => __( 'Billing State/Region', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA', 'arraypress' ),
				'description'   => __( 'The billing state or region entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_region( $args ),
				'required_args' => [],
			],
			'wc_checkout_city'             => [
				'label'         => __( 'Billing City', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. London', 'arraypress' ),
				'description'   => __( 'The billing city entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_city( $args ),
				'required_args' => [],
			],
			'wc_checkout_postcode'         => [
				'label'         => __( 'Billing Postcode', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. SW1A', 'arraypress' ),
				'description'   => __( 'The billing postcode entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_postcode( $args ),
				'required_args' => [],
			],
			'wc_checkout_address'          => [
				'label'         => __( 'Billing Address', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. PO Box', 'arraypress' ),
				'description'   => __( 'The first line of the billing address.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_address( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_country' => [
				'label'         => __( 'Shipping Country', 'arraypress' ),
				'group'         => $address,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The shipping country. Falls back to the billing country when the customer has not asked to ship elsewhere, which is what WooCommerce itself does.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_country( $args ),
				'required_args' => [],
			],
			'wc_checkout_ships_elsewhere'  => [
				'label'         => __( 'Ships To Different Address', 'arraypress' ),
				'group'         => $address,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer ticked "ship to a different address".', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::is_shipping_to_different_address( $args ),
				'required_args' => [],
			],
			'wc_checkout_country_mismatch' => [
				'label'         => __( 'Billing/Shipping Country Mismatch', 'arraypress' ),
				'group'         => $address,
				'type'          => 'boolean',
				'description'   => __( 'Whether the billing and shipping countries differ. A missing shipping country is not a mismatch — an all-virtual cart never collects one.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::has_country_mismatch( $args ),
				'required_args' => [],
			],

			// Details — the remaining billing fields.
			'wc_checkout_address_2'          => [
				'label'         => __( 'Billing Address Line 2', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Apt', 'arraypress' ),
				'description'   => __( 'The second line of the billing address.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_address_2( $args ),
				'required_args' => [],
			],
			'wc_checkout_accepted_terms'     => [
				'label'         => __( 'Accepted Terms', 'arraypress' ),
				'group'         => $details,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer ticked the terms and conditions box.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::has_accepted_terms( $args ),
				'required_args' => [],
			],

			// Shipping address.
			'wc_checkout_shipping_first_name' => [
				'label'         => __( 'Shipping First Name', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'The shipping first name entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_first_name( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_last_name'  => [
				'label'         => __( 'Shipping Last Name', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Smith', 'arraypress' ),
				'description'   => __( 'The shipping last name entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_last_name( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_address'    => [
				'label'         => __( 'Shipping Address', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. PO Box', 'arraypress' ),
				'description'   => __( 'The first line of the shipping address.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_address( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_city'       => [
				'label'         => __( 'Shipping City', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. London', 'arraypress' ),
				'description'   => __( 'The shipping city entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_city( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_region'     => [
				'label'         => __( 'Shipping State/Region', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA', 'arraypress' ),
				'description'   => __( 'The shipping state or region entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_region( $args ),
				'required_args' => [],
			],
			'wc_checkout_shipping_postcode'   => [
				'label'         => __( 'Shipping Postcode', 'arraypress' ),
				'group'         => $address,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. SW1A', 'arraypress' ),
				'description'   => __( 'The shipping postcode entered at checkout.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_shipping_postcode( $args ),
				'required_args' => [],
			],
		];
	}
}
