<?php
/**
 * Statuses Helper
 *
 * Provides standardized status options for select fields.
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class Statuses
 *
 * Standardized status definitions for select condition fields.
 */
class Statuses {

	/**
	 * Get active/inactive statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function active(): array {
		return [
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'inactive', 'label' => __( 'Inactive', 'arraypress' ) ],
		];
	}

	/**
	 * Get enabled/disabled statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function enabled(): array {
		return [
			[ 'value' => 'enabled', 'label' => __( 'Enabled', 'arraypress' ) ],
			[ 'value' => 'disabled', 'label' => __( 'Disabled', 'arraypress' ) ],
		];
	}

	/**
	 * Get common lifecycle statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function lifecycle(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'active', 'label' => __( 'Active', 'arraypress' ) ],
			[ 'value' => 'expired', 'label' => __( 'Expired', 'arraypress' ) ],
			[ 'value' => 'cancelled', 'label' => __( 'Cancelled', 'arraypress' ) ],
			[ 'value' => 'archived', 'label' => __( 'Archived', 'arraypress' ) ],
		];
	}

	/**
	 * Get approval statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function approval(): array {
		return [
			[ 'value' => 'pending', 'label' => __( 'Pending', 'arraypress' ) ],
			[ 'value' => 'approved', 'label' => __( 'Approved', 'arraypress' ) ],
			[ 'value' => 'rejected', 'label' => __( 'Rejected', 'arraypress' ) ],
		];
	}

	/**
	 * Get stock statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function stock(): array {
		return [
			[ 'value' => 'in_stock', 'label' => __( 'In Stock', 'arraypress' ) ],
			[ 'value' => 'low_stock', 'label' => __( 'Low Stock', 'arraypress' ) ],
			[ 'value' => 'out_of_stock', 'label' => __( 'Out of Stock', 'arraypress' ) ],
		];
	}

	/**
	 * Get visibility statuses.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function visibility(): array {
		return [
			[ 'value' => 'public', 'label' => __( 'Public', 'arraypress' ) ],
			[ 'value' => 'private', 'label' => __( 'Private', 'arraypress' ) ],
			[ 'value' => 'hidden', 'label' => __( 'Hidden', 'arraypress' ) ],
		];
	}

	/**
	 * Get clothing/product sizes.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function sizes(): array {
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
	public static function sizes_extended(): array {
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