<?php
/**
 * Core Conditions
 *
 * Registry of pre-configured conditions that can be referenced by name.
 *
 * @package     ArrayPress\Conditions\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions;

use ArrayPress\Conditions\Conditions\Core;
use ArrayPress\Conditions\Conditions\Services;
use ArrayPress\Conditions\Conditions\EDD;

/**
 * Class Conditions
 *
 * Provides access to core and integration condition configurations.
 */
class Conditions {

	/**
	 * Get a condition configuration by name.
	 *
	 * @param string $name The condition name.
	 *
	 * @return array|null
	 */
	public static function get( string $name ): ?array {
		$conditions = self::get_all();

		return $conditions[ $name ] ?? null;
	}

	/**
	 * Get all available conditions (core + active integrations).
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		return array_merge(
			self::get_core(),
			self::get_services(),
			self::get_integrations()
		);
	}

	/**
	 * Get core WordPress conditions only.
	 *
	 * @return array<string, array>
	 */
	public static function get_core(): array {
		return array_merge(
			Core\DateTime::get_all(),
			Core\User::get_all(),
			Core\Post::get_all(),
			Core\Request::get_all(),
			Core\Context::get_all()
		);
	}

	/**
	 * Get core service conditions only.
	 *
	 * @return array<string, array>
	 */
	public static function get_services(): array {
		return array_merge(
			Services\ProxyCheck::get_all(),
			Services\IPInfo::get_all(),
			Services\IPQualityScore::get_all()
		);
	}

	/**
	 * Get conditions from active integrations.
	 *
	 * @return array<string, array>
	 */
	public static function get_integrations(): array {
		$conditions = [];

		// Easy Digital Downloads
		if ( function_exists( 'EDD' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			$conditions = array_merge( $conditions, EDD\Conditions::get_all() );
		}

		// WooCommerce (future)
		// if ( class_exists( 'WooCommerce' ) ) {
		//     $conditions = array_merge( $conditions, WooCommerce::get_all() );
		// }

		// AffiliateWP (future)
		// if ( class_exists( 'Affiliate_WP' ) ) {
		//     $conditions = array_merge( $conditions, AffiliateWP::get_all() );
		// }

		return $conditions;
	}

}