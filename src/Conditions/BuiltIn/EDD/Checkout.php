<?php
/**
 * EDD Checkout Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\BuiltIn\EDD
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\BuiltIn\EDD;

use ArrayPress\Conditions\Conditions\BuiltIn\EDD\Helpers\Formatting;
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
			'edd_checkout_gateway' => [
				'label'         => __( 'Selected Gateway', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select gateway...', 'arraypress' ),
				'description'   => __( 'The payment gateway selected at checkout.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_payment_gateways' ) ? Formatting::format_options( edd_get_payment_gateways(), 'admin_label' ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( isset( $args['posted']['edd-gateway'] ) ) {
						return $args['posted']['edd-gateway'];
					}

					return function_exists( 'edd_get_chosen_gateway' ) ? edd_get_chosen_gateway() : '';
				},
				'required_args' => [],
			],
			'edd_checkout_country' => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The billing country entered at checkout.', 'arraypress' ),
				'options'       => fn() => function_exists( 'edd_get_country_list' ) ? Formatting::format_options( edd_get_country_list() ) : [],
				'operators'     => Operators::array_multiple(),
				'compare_value' => function ( $args ) {
					if ( isset( $args['posted']['billing_country'] ) ) {
						return $args['posted']['billing_country'];
					}

					if ( isset( $args['posted']['edd_address']['country'] ) ) {
						return $args['posted']['edd_address']['country'];
					}

					return '';
				},
				'required_args' => [],
			],
			'edd_checkout_region'  => [
				'label'         => __( 'Billing Region/State', 'arraypress' ),
				'group'         => __( 'Checkout', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA, NY', 'arraypress' ),
				'description'   => __( 'The billing region/state entered at checkout.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					if ( isset( $args['posted']['billing_state'] ) ) {
						return $args['posted']['billing_state'];
					}

					if ( isset( $args['posted']['edd_address']['state'] ) ) {
						return $args['posted']['edd_address']['state'];
					}

					if ( isset( $args['posted']['card_state'] ) ) {
						return $args['posted']['card_state'];
					}

					return '';
				},
				'required_args' => [],
			],
		];
	}

}