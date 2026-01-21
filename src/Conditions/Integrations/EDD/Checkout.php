<?php
/**
 * EDD Checkout Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Integrations\EDD;

use ArrayPress\Conditions\Helpers\EDD\Checkout as CheckoutHelper;
use ArrayPress\Conditions\Helpers\EDD\Options;
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
		return array_merge(
			self::get_payment_conditions(),
			self::get_customer_conditions(),
			self::get_address_conditions()
		);
	}

	/**
	 * Get payment-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_payment_conditions(): array {
		return [
			'edd_checkout_gateway' => [
				'label'         => __( 'Selected Gateway', 'arraypress' ),
				'group'         => __( 'Checkout: Payment', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway selected at checkout.', 'arraypress' ),
				'options'       => fn() => Options::get_gateways(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_gateway( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get customer info conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_customer_conditions(): array {
		return [
			'edd_checkout_email'      => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match checkout email against patterns. Supports: full email, @domain, .tld, or domain.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_email( $args ),
				'required_args' => [],
			],
			'edd_checkout_first_name' => [
				'label'         => __( 'First Name', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'The first name entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_first_name( $args ),
				'required_args' => [],
			],
			'edd_checkout_last_name'  => [
				'label'         => __( 'Last Name', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Doe', 'arraypress' ),
				'description'   => __( 'The last name entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_last_name( $args ),
				'required_args' => [],
			],
		];
	}

	/**
	 * Get address-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_address_conditions(): array {
		return [
			'edd_checkout_country'  => [
				'label'         => __( 'Country', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country entered at checkout.', 'arraypress' ),
				'options'       => fn() => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CheckoutHelper::get_country( $args ),
				'required_args' => [],
			],
			'edd_checkout_region'   => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA, NY', 'arraypress' ),
				'description'   => __( 'The billing region/state entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_region( $args ),
				'required_args' => [],
			],
			'edd_checkout_city'     => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Los Angeles', 'arraypress' ),
				'description'   => __( 'The billing city entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_city( $args ),
				'required_args' => [],
			],
			'edd_checkout_postcode' => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 90210, SW1A 1AA', 'arraypress' ),
				'description'   => __( 'The billing postal/zip code entered at checkout.', 'arraypress' ),
				'compare_value' => fn( $args ) => CheckoutHelper::get_postcode( $args ),
				'required_args' => [],
			],
		];
	}

}