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

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Helpers\Format;
use ArrayPress\Conditions\Helpers\PostedData;
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
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? Format::options( edd_get_payment_gateways(), 'admin_label' ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					$posted = $args['posted'] ?? [];

					$gateway = PostedData::get( $posted, [ 'edd-gateway' ] );

					if ( $gateway ) {
						return $gateway;
					}

					return function_exists( 'edd_get_chosen_gateway' ) ? edd_get_chosen_gateway() : '';
				},
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
			'edd_checkout_email'        => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'The email address entered at checkout. Supports: full email, @domain.com, .edu, partial domain.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'edd_email',
						'edd-email',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_email_domain' => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'tags',
				'placeholder'   => __( 'Type domain, press Enter...', 'arraypress' ),
				'description'   => __( 'Match if checkout email ends with specified domains.', 'arraypress' ),
				'operators'     => Operators::tags_ends(),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'edd_email',
						'edd-email',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_first_name'   => [
				'label'         => __( 'First Name', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. John', 'arraypress' ),
				'description'   => __( 'The first name entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'edd_first',
						'edd-first',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_last_name'    => [
				'label'         => __( 'Last Name', 'arraypress' ),
				'group'         => __( 'Checkout: Customer', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Doe', 'arraypress' ),
				'description'   => __( 'The last name entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'edd_last',
						'edd-last',
					] );
				},
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
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? Format::options( edd_get_country_list() ) : [],
				'operators'     => Operators::collection_any_none(),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'billing_country',
						'edd_address.country',
						'card_country',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_region'   => [
				'label'         => __( 'Region/State', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA, NY', 'arraypress' ),
				'description'   => __( 'The billing region/state entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'billing_state',
						'edd_address.state',
						'card_state',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_city'     => [
				'label'         => __( 'City', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Los Angeles', 'arraypress' ),
				'description'   => __( 'The billing city entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'billing_city',
						'edd_address.city',
						'card_city',
					] );
				},
				'required_args' => [],
			],
			'edd_checkout_postcode' => [
				'label'         => __( 'Postal Code', 'arraypress' ),
				'group'         => __( 'Checkout: Address', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 90210, SW1A 1AA', 'arraypress' ),
				'description'   => __( 'The billing postal/zip code entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					return PostedData::get( $args['posted'] ?? [], [
						'billing_zip',
						'edd_address.zip',
						'card_zip',
					] );
				},
				'required_args' => [],
			],
		];
	}

}