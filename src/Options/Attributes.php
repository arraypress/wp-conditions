<?php
/**
 * Attributes Options Helper
 *
 * Provides standardized product/item attribute options for select fields.
 *
 * @package     ArrayPress\Conditions\Options
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Options;

/**
 * Class Attributes
 *
 * Standardized attribute definitions for condition fields.
 */
class Attributes {

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

	/**
	 * Get product condition options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_conditions(): array {
		return [
			[ 'value' => 'new', 'label' => __( 'New', 'arraypress' ) ],
			[ 'value' => 'like_new', 'label' => __( 'Like New', 'arraypress' ) ],
			[ 'value' => 'refurbished', 'label' => __( 'Refurbished', 'arraypress' ) ],
			[ 'value' => 'used', 'label' => __( 'Used', 'arraypress' ) ],
		];
	}

}