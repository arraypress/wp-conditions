<?php
/**
 * Payment Conditions
 *
 * Card-fingerprint velocity, card funding type, and gateway-provided risk
 * signals (e.g. Stripe Radar). All values are post-tokenization, so these
 * conditions only fire during a post-payment "review" pass — pre-checkout
 * the data does not exist.
 *
 * Callers MUST pass `card_fingerprint`, `card_funding`, `stripe_risk_level`,
 * etc. through the args array. The library performs no gateway calls itself.
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\Velocity as VelocityHelper;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Options\Periods;

/**
 * Class Payment
 *
 * Provides card-fingerprint velocity and gateway-risk conditions.
 */
class Payment {

	/**
	 * Get all payment conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'Payment', 'arraypress' );

		return [
			'card_funding_type'                      => [
				'label'         => __( 'Card Funding Type', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select funding types...', 'arraypress' ),
				'description'   => __( 'Funding type returned by the gateway (credit, debit, prepaid, unknown). Prepaid is heavily abused.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_funding_types(),
				'compare_value' => fn( $args ) => strtolower( (string) ( $args['card_funding'] ?? '' ) ),
				'required_args' => [ 'card_funding' ],
			],
			'card_brand'                             => [
				'label'         => __( 'Card Brand', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select brands...', 'arraypress' ),
				'description'   => __( 'Card brand returned by the gateway.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_card_brands(),
				'compare_value' => fn( $args ) => strtolower( (string) ( $args['card_brand'] ?? '' ) ),
				'required_args' => [ 'card_brand' ],
			],
			'stripe_risk_level'                      => [
				'label'         => __( 'Stripe Risk Level', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select risk levels...', 'arraypress' ),
				'description'   => __( 'Stripe Radar risk classification (normal, elevated, highest).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_stripe_risk_levels(),
				'compare_value' => fn( $args ) => strtolower( (string) ( $args['stripe_risk_level'] ?? '' ) ),
				'required_args' => [ 'stripe_risk_level' ],
			],
			'stripe_risk_score'                      => [
				'label'         => __( 'Stripe Risk Score', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 65', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'Stripe Radar numeric risk score (0–100). Higher is riskier.', 'arraypress' ),
				'compare_value' => fn( $args ) => (int) ( $args['stripe_risk_score'] ?? 0 ),
				'required_args' => [ 'stripe_risk_score' ],
			],
			'velocity_orders_by_card_fingerprint'    => [
				'label'         => __( 'Orders by Card Fingerprint', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Number of orders made with the same card fingerprint within the time window. Card-testing signal. Caller pre-computes via Velocity::compute_card_fingerprint().', 'arraypress' ),
				'compare_value' => fn( $args ) => (int) ( $args['velocity_orders_by_card_fingerprint'] ?? 0 ),
				'required_args' => [ 'card_fingerprint', 'velocity_orders_by_card_fingerprint' ],
			],
			'velocity_distinct_emails_by_card_fingerprint' => [
				'label'         => __( 'Distinct Emails per Card', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_units(),
				'description'   => __( 'Distinct emails using the same card fingerprint. Multiple emails on one card is a strong stolen-card signal.', 'arraypress' ),
				'compare_value' => fn( $args ) => (int) ( $args['velocity_distinct_emails_by_card_fingerprint'] ?? 0 ),
				'required_args' => [ 'card_fingerprint', 'velocity_distinct_emails_by_card_fingerprint' ],
			],
		];
	}

	/**
	 * Card funding type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_funding_types(): array {
		return [
			[ 'value' => 'credit', 'label' => __( 'Credit', 'arraypress' ) ],
			[ 'value' => 'debit', 'label' => __( 'Debit', 'arraypress' ) ],
			[ 'value' => 'prepaid', 'label' => __( 'Prepaid', 'arraypress' ) ],
			[ 'value' => 'unknown', 'label' => __( 'Unknown', 'arraypress' ) ],
		];
	}

	/**
	 * Common card brand options (Stripe-style identifiers).
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_card_brands(): array {
		return [
			[ 'value' => 'visa', 'label' => __( 'Visa', 'arraypress' ) ],
			[ 'value' => 'mastercard', 'label' => __( 'Mastercard', 'arraypress' ) ],
			[ 'value' => 'amex', 'label' => __( 'American Express', 'arraypress' ) ],
			[ 'value' => 'discover', 'label' => __( 'Discover', 'arraypress' ) ],
			[ 'value' => 'diners', 'label' => __( 'Diners Club', 'arraypress' ) ],
			[ 'value' => 'jcb', 'label' => __( 'JCB', 'arraypress' ) ],
			[ 'value' => 'unionpay', 'label' => __( 'UnionPay', 'arraypress' ) ],
			[ 'value' => 'unknown', 'label' => __( 'Unknown', 'arraypress' ) ],
		];
	}

	/**
	 * Stripe Radar risk level options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_stripe_risk_levels(): array {
		return [
			[ 'value' => 'normal', 'label' => __( 'Normal', 'arraypress' ) ],
			[ 'value' => 'elevated', 'label' => __( 'Elevated', 'arraypress' ) ],
			[ 'value' => 'highest', 'label' => __( 'Highest', 'arraypress' ) ],
			[ 'value' => 'not_assessed', 'label' => __( 'Not Assessed', 'arraypress' ) ],
		];
	}

}
