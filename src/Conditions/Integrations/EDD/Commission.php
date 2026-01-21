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

namespace ArrayPress\Conditions\Conditions\Integrations\EDD;

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
		return array_merge(
			self::get_amount_conditions(),
			self::get_detail_conditions(),
			self::get_product_conditions(),
			self::get_recipient_conditions()
		);
	}

	/**
	 * Get amount-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_amount_conditions(): array {
		return [
			'edd_commission_amount' => [
				'label'         => __( 'Amount', 'arraypress' ),
				'group'         => __( 'Commission: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The commission amount.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? (float) $commission->amount : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_rate'   => [
				'label'         => __( 'Rate (%)', 'arraypress' ),
				'group'         => __( 'Commission: Amounts', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'The commission rate percentage.', 'arraypress' ),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? (float) $commission->rate : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
		];
	}

	/**
	 * Get detail-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_detail_conditions(): array {
		return [
			'edd_commission_status' => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Commission: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The commission status.', 'arraypress' ),
				'options'       => [
					[ 'value' => 'unpaid', 'label' => __( 'Unpaid', 'arraypress' ) ],
					[ 'value' => 'paid', 'label' => __( 'Paid', 'arraypress' ) ],
					[ 'value' => 'revoked', 'label' => __( 'Revoked', 'arraypress' ) ],
				],
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? $commission->status : '';
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_type'   => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Commission: Details', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'The commission type (flat or percentage).', 'arraypress' ),
				'options'       => [
					[ 'value' => 'percentage', 'label' => __( 'Percentage', 'arraypress' ) ],
					[ 'value' => 'flat', 'label' => __( 'Flat Amount', 'arraypress' ) ],
				],
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? ( $commission->type ?? 'percentage' ) : '';
				},
				'required_args' => [ 'commission_id' ],
			],
		];
	}

	/**
	 * Get product-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_product_conditions(): array {
		return [
			'edd_commission_product'    => [
				'label'         => __( 'Product', 'arraypress' ),
				'group'         => __( 'Commission: Product', 'arraypress' ),
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'The product the commission is for.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? (int) $commission->download_id : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_categories' => [
				'label'         => __( 'Product Categories', 'arraypress' ),
				'group'         => __( 'Commission: Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories of the commission product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					if ( ! $commission || ! $commission->download_id ) {
						return [];
					}

					$terms = wp_get_object_terms( $commission->download_id, 'download_category', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'commission_id' ],
			],
			'edd_commission_tags'       => [
				'label'         => __( 'Product Tags', 'arraypress' ),
				'group'         => __( 'Commission: Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags of the commission product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					if ( ! $commission || ! $commission->download_id ) {
						return [];
					}

					$terms = wp_get_object_terms( $commission->download_id, 'download_tag', [ 'fields' => 'ids' ] );

					return is_array( $terms ) ? $terms : [];
				},
				'required_args' => [ 'commission_id' ],
			],
		];
	}

	/**
	 * Get recipient-related conditions.
	 *
	 * @return array<string, array>
	 */
	private static function get_recipient_conditions(): array {
		return [
			'edd_commission_user' => [
				'label'         => __( 'Recipient User', 'arraypress' ),
				'group'         => __( 'Commission: Recipient', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The user receiving the commission.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => function ( $args ) {
					$commission = self::get_commission( $args );

					return $commission ? (int) $commission->user_id : 0;
				},
				'required_args' => [ 'commission_id' ],
			],
		];
	}

	/**
	 * Get commission object from args.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return object|null
	 */
	private static function get_commission( array $args ): ?object {
		if ( ! isset( $args['commission_id'] ) || ! function_exists( 'eddc_get_commission' ) ) {
			return null;
		}

		return eddc_get_commission( $args['commission_id'] );
	}

}