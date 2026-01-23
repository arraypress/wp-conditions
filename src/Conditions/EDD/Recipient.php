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

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Recipient as RecipientHelper;
use ArrayPress\Conditions\Options\Periods;

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
		return [
			// Earnings
			'edd_recipient_total_earnings'  => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total earnings (paid + unpaid).', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_total_earnings( $args ),
				'required_args' => [],
			],
			'edd_recipient_paid_earnings'   => [
				'label'         => __( 'Paid Earnings', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total paid earnings.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_paid_earnings( $args ),
				'required_args' => [],
			],
			'edd_recipient_unpaid_earnings' => [
				'label'         => __( 'Unpaid Earnings', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 250.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Total unpaid earnings.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_unpaid_earnings( $args ),
				'required_args' => [],
			],

			// Commission Counts
			'edd_recipient_total_sales'     => [
				'label'         => __( 'Total Sales', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Total number of sales.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_total_sales( $args ),
				'required_args' => [],
			],
			'edd_recipient_unpaid_count'    => [
				'label'         => __( 'Unpaid Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of unpaid commissions.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_unpaid_count( $args ),
				'required_args' => [],
			],
			'edd_recipient_paid_count'      => [
				'label'         => __( 'Paid Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 40', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of paid commissions.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_paid_count( $args ),
				'required_args' => [],
			],
			'edd_recipient_revoked_count'   => [
				'label'         => __( 'Revoked Commission Count', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of revoked commissions.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_revoked_count( $args ),
				'required_args' => [],
			],

			// Profile
			'edd_recipient_account_age'     => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the user has been registered.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_account_age( $args ),
				'required_args' => [],
			],
			'edd_recipient_is_vendor'       => [
				'label'         => __( 'Is Vendor', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the user is a registered vendor.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::is_vendor( $args ),
				'required_args' => [],
			],
			'edd_recipient_commission_rate' => [
				'label'         => __( 'Commission Rate (%)', 'arraypress' ),
				'group'         => __( 'Recipient', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'The effective commission rate for this recipient.', 'arraypress' ),
				'compare_value' => fn( $args ) => RecipientHelper::get_commission_rate( $args ),
				'required_args' => [],
			],
		];
	}

}