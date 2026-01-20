<?php
/**
 * Built-in Conditions
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

namespace ArrayPress\Conditions\Conditions;

use ArrayPress\Conditions\Conditions\BuiltIn\Context;
use ArrayPress\Conditions\Conditions\BuiltIn\DateTime;
use ArrayPress\Conditions\Conditions\BuiltIn\EDD;
use ArrayPress\Conditions\Conditions\BuiltIn\Post;
use ArrayPress\Conditions\Conditions\BuiltIn\Request;
use ArrayPress\Conditions\Conditions\BuiltIn\User;

/**
 * Class BuiltIn
 *
 * Provides access to built-in condition configurations.
 */
class BuiltIn {

	/**
	 * Get a built-in condition configuration.
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
	 * Get all built-in conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$conditions = array_merge(
			DateTime::get_all(),
			User::get_all(),
			Post::get_all(),
			Request::get_all(),
			Context::get_all()
		);

		// Include EDD conditions only if EDD is active
		if ( function_exists( 'EDD' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			$conditions = array_merge( $conditions, EDD::get_all() );
		}

		return $conditions;
	}

}
