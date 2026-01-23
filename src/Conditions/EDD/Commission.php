<?php
/**
 * EDD Commission Conditions
 *
 * Provides conditions for the EDD Commissions add-on.
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Commission as CommissionHelper;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Operators;

/**
 * Class Commission
 *
 * Provides EDD commission-related conditions.
 */
class Commission {

	/**
	 * Get all commission conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return [
			// Amounts
			'edd_commission_amount'     => [
				'label'         => __( 'Amount', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The commission amount.', 'arraypress' ),
				'compare_value' => fn( $args ) => CommissionHelper::get_amount( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_rate'       => [
				'label'         => __( 'Rate (%)', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'The commission rate percentage.', 'arraypress' ),
				'compare_value' => fn( $args ) => CommissionHelper::get_rate( $args ),
				'required_args' => [ 'commission_id' ],
			],

			// Details
			'edd_commission_status'     => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The commission status.', 'arraypress' ),
				'options'       => CommissionHelper::get_status_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CommissionHelper::get_status( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_type'       => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'The commission type (flat or percentage).', 'arraypress' ),
				'options'       => CommissionHelper::get_type_options(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CommissionHelper::get_type( $args ),
				'required_args' => [ 'commission_id' ],
			],

			// Dates
			'edd_commission_date'       => [
				'label'         => __( 'Date Created', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the commission was created.', 'arraypress' ),
				'compare_value' => fn( $args ) => CommissionHelper::get_date_created( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_date_paid'  => [
				'label'         => __( 'Date Paid', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the commission was paid.', 'arraypress' ),
				'compare_value' => fn( $args ) => CommissionHelper::get_date_paid( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_age'        => [
				'label'         => __( 'Age', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'description'   => __( 'How long since the commission was created.', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'compare_value' => fn( $args ) => CommissionHelper::get_age( $args ),
				'required_args' => [ 'commission_id' ],
			],

			// Product
			'edd_commission_product'    => [
				'label'         => __( 'Product', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'The product the commission is for.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CommissionHelper::get_product_id( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_categories' => [
				'label'         => __( 'Product Categories', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories of the commission product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CommissionHelper::get_category_ids( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_tags'       => [
				'label'         => __( 'Product Tags', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags of the commission product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CommissionHelper::get_tag_ids( $args ),
				'required_args' => [ 'commission_id' ],
			],

			// Recipient
			'edd_commission_user'       => [
				'label'         => __( 'Recipient', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The user receiving the commission.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CommissionHelper::get_user_id( $args ),
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_user_email' => [
				'label'         => __( 'Recipient Email', 'arraypress' ),
				'group'         => __( 'Commission', 'arraypress' ),
				'type'          => 'email',
				'placeholder'   => __( 'e.g. @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'The email of the user receiving the commission.', 'arraypress' ),
				'compare_value' => fn( $args ) => CommissionHelper::get_user_email( $args ),
				'required_args' => [ 'commission_id' ],
			],
		];
	}

}