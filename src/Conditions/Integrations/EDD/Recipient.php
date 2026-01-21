<?php
/**
 * EDD Recipient Conditions
 *
 * Provides conditions for commission recipients (vendors/affiliates).
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Integrations\EDD;

use ArrayPress\Conditions\Helpers\Periods;

/**
 * Class Recipient
 *
 * Provides EDD commission recipient conditions.
 */
class Recipient {

	/**
	 * Get all recipient conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_earnings_conditions(),
			self::get_commission_count_conditions(),
			self::get_profile_conditions(),
			self::get_settings_conditions()
		);
	}

	/**
	 * Get earnings-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_earnings_conditions(): array {
		return [
			'edd_recipient_total_earnings'  => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'Recipient: Earnings', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total earnings for the recipient (all time).', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
						return 0;
					}

					$paid   = (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
					$unpaid = (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );

					return $paid + $unpaid;
				},
				'required_args' => [],
			],
			'edd_recipient_paid_earnings'   => [
				'label'         => __( 'Paid Earnings', 'arraypress' ),
				'group'         => __( 'Recipient: Earnings', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total paid earnings for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_get_paid_totals' ) ) {
						return 0;
					}

					return (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
				},
				'required_args' => [],
			],
			'edd_recipient_unpaid_earnings' => [
				'label'         => __( 'Unpaid Earnings', 'arraypress' ),
				'group'         => __( 'Recipient: Earnings', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 250.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total unpaid earnings for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
						return 0;
					}

					return (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get commission count conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_commission_count_conditions(): array {
		return [
			'edd_recipient_total_sales'   => [
				'label'         => __( 'Total Sales Count', 'arraypress' ),
				'group'         => __( 'Recipient: Commission Counts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Total number of sales for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
						return 0;
					}

					return (int) eddc_count_user_commissions( $user_id );
				},
				'required_args' => [],
			],
			'edd_recipient_unpaid_count'  => [
				'label'         => __( 'Unpaid Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient: Commission Counts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of unpaid commissions for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
						return 0;
					}

					return (int) eddc_count_user_commissions( $user_id, 'unpaid' );
				},
				'required_args' => [],
			],
			'edd_recipient_paid_count'    => [
				'label'         => __( 'Paid Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient: Commission Counts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 40', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of paid commissions for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
						return 0;
					}

					return (int) eddc_count_user_commissions( $user_id, 'paid' );
				},
				'required_args' => [],
			],
			'edd_recipient_revoked_count' => [
				'label'         => __( 'Revoked Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient: Commission Counts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of revoked commissions for the recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
						return 0;
					}

					return (int) eddc_count_user_commissions( $user_id, 'revoked' );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get profile-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_profile_conditions(): array {
		return [
			'edd_recipient_account_age' => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'Recipient: Profile', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the user has been a commission recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id ) {
						return 0;
					}

					$user = get_userdata( $user_id );

					if ( ! $user ) {
						return 0;
					}

					return Periods::get_age( $user->user_registered, $args['_unit'] ?? 'day' );
				},
				'required_args' => [],
			],
			'edd_recipient_is_vendor'   => [
				'label'         => __( 'Is Vendor', 'arraypress' ),
				'group'         => __( 'Recipient: Profile', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is a registered vendor/commission recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id ) {
						return false;
					}

					// Check if user has any commissions (indicating they are a vendor)
					if ( function_exists( 'eddc_count_user_commissions' ) ) {
						return eddc_count_user_commissions( $user_id ) > 0;
					}

					// Alternative: check user meta for vendor status
					$is_vendor = get_user_meta( $user_id, 'eddc_user_rate', true );

					return ! empty( $is_vendor );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get settings-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_settings_conditions(): array {
		return [
			'edd_recipient_commission_rate'   => [
				'label'         => __( 'Default Commission Rate (%)', 'arraypress' ),
				'group'         => __( 'Recipient: Settings', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'The default commission rate for this recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id ) {
						return 0;
					}

					$rate = get_user_meta( $user_id, 'eddc_user_rate', true );

					return $rate !== '' ? (float) $rate : 0;
				},
				'required_args' => [],
			],
			'edd_recipient_payout_method'     => [
				'label'         => __( 'Payout Method', 'arraypress' ),
				'group'         => __( 'Recipient: Settings', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. paypal', 'arraypress' ),
				'description'   => __( 'The payout method configured for this recipient.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id ) {
						return '';
					}

					return get_user_meta( $user_id, 'eddc_user_payout_method', true ) ?: '';
				},
				'required_args' => [],
			],
			'edd_recipient_has_payout_method' => [
				'label'         => __( 'Has Payout Method', 'arraypress' ),
				'group'         => __( 'Recipient: Settings', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the recipient has a payout method configured.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$user_id = self::get_user_id( $args );

					if ( ! $user_id ) {
						return false;
					}

					$method = get_user_meta( $user_id, 'eddc_user_payout_method', true );

					return ! empty( $method );
				},
				'required_args' => [],
			],
		];
	}

	/**
	 * Get user ID from args or current user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	private static function get_user_id( array $args ): int {
		return (int) ( $args['user_id'] ?? get_current_user_id() );
	}

}