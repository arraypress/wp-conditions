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

			// PayPal Orders v2 capture-time signals. Available post-payment
			// only — callers are expected to populate these from a PayPal
			// webhook handler (PAYMENT.CAPTURE.COMPLETED) on the order.
			'paypal_seller_protection'   => [
				'label'         => __( 'PayPal Seller Protection', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select levels...', 'arraypress' ),
				'description'   => __( 'PayPal\'s own verdict on whether the seller is protected (ELIGIBLE / PARTIALLY_ELIGIBLE / NOT_ELIGIBLE). Strongest single signal PayPal exposes post-capture.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => self::get_paypal_seller_protection_levels(),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['paypal_seller_protection'] ?? '' ) ),
				'required_args' => [ 'paypal_seller_protection' ],
			],
			'paypal_avs_code'            => [
				'label'         => __( 'PayPal AVS Code', 'arraypress' ),
				'group'         => $group,
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. N, A, Z (mismatch codes)', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Single-letter Address Verification System response. Match codes (Y/D/F/M/X) indicate a verified billing address; mismatches (N/A/Z) correlate with stolen cards.', 'arraypress' ),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['paypal_avs_code'] ?? '' ) ),
				'required_args' => [ 'paypal_avs_code' ],
			],
			'paypal_cvv_code'            => [
				'label'         => __( 'PayPal CVV Code', 'arraypress' ),
				'group'         => $group,
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. N, P (no match / not processed)', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Single-letter CVV verification response. M = match, N = no match, P = not processed, S = should have been provided, U = unavailable.', 'arraypress' ),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['paypal_cvv_code'] ?? '' ) ),
				'required_args' => [ 'paypal_cvv_code' ],
			],
			'paypal_payer_country'       => [
				'label'         => __( 'PayPal Payer Country', 'arraypress' ),
				'group'         => $group,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'ISO-2 country of the PayPal account that placed the order.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => \ArrayPress\Conditions\Helpers\Geo::get_country_options(),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['paypal_payer_country'] ?? '' ) ),
				'required_args' => [ 'paypal_payer_country' ],
			],
			'paypal_payer_country_matches_billing' => [
				'label'         => __( 'PayPal Payer Country Matches Billing', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Whether the PayPal account country matches the billing address country. Mismatches are a fraud signal.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$payer   = strtoupper( (string) ( $args['paypal_payer_country'] ?? '' ) );
					$billing = strtoupper( (string) ( $args['billing_country'] ?? '' ) );

					return $payer !== '' && $billing !== '' && $payer === $billing;
				},
				'required_args' => [ 'paypal_payer_country', 'billing_country' ],
			],
			'paypal_decline_reason'      => [
				'label'         => __( 'PayPal Decline Reason', 'arraypress' ),
				'group'         => $group,
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. DECLINED_BY_RISK_FRAUD_FILTERS', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'PayPal status_details.reason value. DECLINED_BY_RISK_FRAUD_FILTERS means PayPal\'s own filters caught the transaction.', 'arraypress' ),
				'compare_value' => fn( $args ) => strtoupper( (string) ( $args['paypal_decline_reason'] ?? '' ) ),
				'required_args' => [ 'paypal_decline_reason' ],
			],
		];
	}

	/**
	 * PayPal seller_protection.status values.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	private static function get_paypal_seller_protection_levels(): array {
		return [
			[ 'value' => 'ELIGIBLE', 'label' => __( 'Eligible', 'arraypress' ) ],
			[ 'value' => 'PARTIALLY_ELIGIBLE', 'label' => __( 'Partially Eligible', 'arraypress' ) ],
			[ 'value' => 'NOT_ELIGIBLE', 'label' => __( 'Not Eligible', 'arraypress' ) ],
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
