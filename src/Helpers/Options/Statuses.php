<?php
/**
 * Statuses Options Helper
 *
 * Provides standardized status options for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers\Options
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers\Options;

/**
 * Class Statuses
 *
 * Standardized status definitions for select condition fields.
 */
class Statuses {

	/**
	 * Get common lifecycle statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_lifecycle(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'expired', 'label' => __( 'Expired', 'arraypress' ) ],
			[ 'value' => 'cancelled', 'label' => __( 'Cancelled', 'arraypress' ) ],
			[ 'value' => 'archived', 'label' => __( 'Archived', 'arraypress' ) ],
		];
	}

	/**
	 * Get stock statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_stock(): array {
		return [
			[ 'value' => 'in_stock', 'label' => __( 'In Stock', 'arraypress' ) ],
			[ 'value' => 'low_stock', 'label' => __( 'Low Stock', 'arraypress' ) ],
			[ 'value' => 'out_of_stock', 'label' => __( 'Out of Stock', 'arraypress' ) ],
		];
	}

	/**
	 * Get payment statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_payment(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'processing', 'label' => __( 'Processing', 'arraypress' ) ],
			[ 'value' => 'completed', 'label' => __( 'Completed', 'arraypress' ) ],
			[ 'value' => 'failed', 'label' => __( 'Failed', 'arraypress' ) ],
			[ 'value' => 'refunded', 'label' => __( 'Refunded', 'arraypress' ) ],
		];
	}

	/**
	 * Get subscription statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_subscription(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'trialing', 'label' => __( 'Trialing', 'arraypress' ) ],
			[ 'value' => 'cancelled', 'label' => __( 'Cancelled', 'arraypress' ) ],
			[ 'value' => 'expired', 'label' => __( 'Expired', 'arraypress' ) ],
			[ 'value' => 'failing', 'label' => __( 'Failing', 'arraypress' ) ],
		];
	}

	/**
	 * Get commission statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_commission(): array {
		return [
			[ 'value' => 'unpaid', 'label' => __( 'Unpaid', 'arraypress' ) ],
			[ 'value' => 'paid', 'label' => __( 'Paid', 'arraypress' ) ],
			[ 'value' => 'revoked', 'label' => __( 'Revoked', 'arraypress' ) ],
		];
	}

	/**
	 * Get license statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_license(): array {
		return [
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'inactive', 'label' => __( 'Inactive', 'arraypress' ) ],
			[ 'value' => 'expired', 'label' => __( 'Expired', 'arraypress' ) ],
			[ 'value' => 'disabled', 'label' => __( 'Disabled', 'arraypress' ) ],
		];
	}

	/**
	 * Get clothing/product sizes.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_sizes(): array {
		return [
			[ 'value' => 'xs', 'label' => __( 'Extra Small (XS)', 'arraypress' ) ],
			[ 'value' => 's', 'label' => __( 'Small (S)', 'arraypress' ) ],
			[ 'value' => 'm', 'label' => __( 'Medium (M)', 'arraypress' ) ],
			[ 'value' => 'l', 'label' => __( 'Large (L)', 'arraypress' ) ],
			[ 'value' => 'xl', 'label' => __( 'Extra Large (XL)', 'arraypress' ) ],
		];
	}

	/**
	 * Get extended clothing/product sizes.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_sizes_extended(): array {
		return [
			[ 'value' => 'xxs', 'label' => __( 'Extra Extra Small (XXS)', 'arraypress' ) ],
			[ 'value' => 'xs', 'label' => __( 'Extra Small (XS)', 'arraypress' ) ],
			[ 'value' => 's', 'label' => __( 'Small (S)', 'arraypress' ) ],
			[ 'value' => 'm', 'label' => __( 'Medium (M)', 'arraypress' ) ],
			[ 'value' => 'l', 'label' => __( 'Large (L)', 'arraypress' ) ],
			[ 'value' => 'xl', 'label' => __( 'Extra Large (XL)', 'arraypress' ) ],
			[ 'value' => 'xxl', 'label' => __( 'Extra Extra Large (XXL)', 'arraypress' ) ],
			[ 'value' => 'xxxl', 'label' => __( 'Extra Extra Extra Large (XXXL)', 'arraypress' ) ],
		];
	}

}