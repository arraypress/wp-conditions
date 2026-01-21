<?php
/**
 * Conditions Registry
 *
 * Central registry for all condition sets and their conditions.
 * Handles registration, storage, and retrieval of conditions.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions;

use ArrayPress\Conditions\Abstracts\Condition;
use ArrayPress\Conditions\Admin\Assets;
use ArrayPress\Conditions\Conditions\Core;
use ArrayPress\Conditions\Registration\MetaBox;
use ArrayPress\Conditions\Registration\PostType;
use ArrayPress\Conditions\Registration\RestApi;
use InvalidArgumentException;

/**
 * Class Registry
 *
 * Manages all registered condition sets and conditions.
 */
class Registry {

	/**
	 * Registered condition sets.
	 *
	 * @var array<string, array>
	 */
	private static array $sets = [];

	/**
	 * Registered conditions by set.
	 *
	 * @var array<string, array<string, array>>
	 */
	private static array $conditions = [];

	/**
	 * PostType instances by set ID.
	 *
	 * @var array<string, PostType>
	 */
	private static array $post_types = [];

	/**
	 * MetaBox instances by set ID.
	 *
	 * @var array<string, MetaBox>
	 */
	private static array $meta_boxes = [];

	/**
	 * REST API instance.
	 *
	 * @var RestApi|null
	 */
	private static ?RestApi $rest_api = null;

	/**
	 * Assets instance.
	 *
	 * @var Assets|null
	 */
	private static ?Assets $assets = null;

	/**
	 * Allowed post types for REST queries.
	 *
	 * @var array<string>
	 */
	private static array $allowed_post_types = [];

	/**
	 * Allowed taxonomies for REST queries.
	 *
	 * @var array<string>
	 */
	private static array $allowed_taxonomies = [];

	/**
	 * Allowed user roles for REST queries.
	 *
	 * @var array<string>
	 */
	private static array $allowed_roles = [];

	/**
	 * Whether the library has been initialized.
	 *
	 * @var bool
	 */
	private static bool $initialized = false;

	/**
	 * Initialize the library.
	 *
	 * Hooks into WordPress to set up REST endpoints, admin assets, etc.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}

		self::$initialized = true;

		// Initialize REST API
		self::$rest_api = new RestApi();
		self::$rest_api->register();

		// Initialize Assets
		self::$assets = new Assets();
		self::$assets->register();
	}

	/**
	 * Register a condition set.
	 *
	 * Creates a custom post type for storing rules with this condition set.
	 *
	 * @param string $set_id        Unique identifier for the condition set.
	 * @param array  $args          {
	 *                              Configuration arguments.
	 *
	 * @type array   $labels        Labels for the CPT (singular, plural).
	 * @type string  $menu_icon     Dashicon or URL for menu icon.
	 * @type string  $menu_parent   Parent menu slug to nest under.
	 * @type string  $parent_file   Parent file for menu highlighting.
	 * @type string  $submenu_file  Submenu file for menu highlighting.
	 * @type string  $capability    Required capability to manage.
	 * @type array   $conditions    Array of conditions to register.
	 * @type array   $redirect      Redirect configuration (after_trash, show_undo).
	 * @type string  $description   Meta box description text.
	 * @type string  $metabox_title Custom meta box title.
	 * @type array   $supports      Post type supports array.
	 *                              }
	 *
	 * @return void
	 * @throws InvalidArgumentException If set_id is empty or already registered.
	 */
	public static function register_set( string $set_id, array $args = [] ): void {
		if ( empty( $set_id ) ) {
			throw new InvalidArgumentException( 'Condition set ID cannot be empty.' );
		}

		if ( isset( self::$sets[ $set_id ] ) ) {
			throw new InvalidArgumentException( sprintf( 'Condition set "%s" is already registered.', $set_id ) );
		}

		// Ensure library is initialized
		self::init();

		// Parse defaults
		$args = wp_parse_args( $args, [
			'labels'        => [
				'singular' => ucwords( str_replace( [ '_', '-' ], ' ', $set_id ) ),
				'plural'   => ucwords( str_replace( [ '_', '-' ], ' ', $set_id ) ) . 's',
			],
			'menu_icon'     => 'dashicons-yes-alt',
			'menu_parent'   => null,
			'parent_file'   => null,
			'submenu_file'  => null,
			'show_in_menu'  => true,
			'capability'    => 'manage_options',
			'conditions'    => [],
			'redirect'      => [],
			'description'   => __( 'Configure when this rule should apply. Groups are connected with OR logic, conditions within a group use AND logic.', 'arraypress' ),
			'metabox_title' => null,
			'supports'      => [ 'title' ],
		] );

		self::$sets[ $set_id ] = $args;

		// Initialize conditions array for this set
		if ( ! isset( self::$conditions[ $set_id ] ) ) {
			self::$conditions[ $set_id ] = [];
		}

		// Register conditions if provided
		if ( ! empty( $args['conditions'] ) ) {
			self::register_conditions_array( $set_id, $args['conditions'] );
		}

		// Register the post type
		self::$post_types[ $set_id ] = new PostType( $set_id, $args );
		self::$post_types[ $set_id ]->register();

		// Register the meta box
		self::$meta_boxes[ $set_id ] = new MetaBox( $set_id, $args );
		self::$meta_boxes[ $set_id ]->register();

		// Add to assets tracking
		if ( self::$assets ) {
			self::$assets->add_set_id( $set_id );
		}

		/**
		 * Action fired after a condition set is registered.
		 *
		 * @param string $set_id The condition set ID.
		 * @param array  $args   The condition set configuration.
		 */
		do_action( 'conditions_set_registered', $set_id, $args );
	}

	/**
	 * Register a single condition to a set.
	 *
	 * @param string       $set_id       The condition set ID.
	 * @param string|array $condition_id Condition ID, class name, or built-in name.
	 * @param array        $args         Condition configuration (if not a class).
	 *
	 * @return void
	 * @throws InvalidArgumentException If set doesn't exist or condition is invalid.
	 */
	public static function register_condition( string $set_id, string|array $condition_id, array $args = [] ): void {
		// Ensure set exists
		if ( ! isset( self::$sets[ $set_id ] ) ) {
			// Auto-create set with defaults if it doesn't exist
			self::register_set( $set_id );
		}

		// Handle class-based conditions
		if ( is_string( $condition_id ) && class_exists( $condition_id ) ) {
			$instance = new $condition_id();

			if ( ! $instance instanceof Condition ) {
				throw new InvalidArgumentException(
					sprintf( 'Class "%s" must extend ArrayPress\\Conditions\\Abstracts\\Condition.', $condition_id )
				);
			}

			$config                                         = $instance->to_array();
			self::$conditions[ $set_id ][ $config['name'] ] = $config;
			self::update_allowed_queries( $config );

			return;
		}

		// Handle built-in condition references
		if ( is_string( $condition_id ) && empty( $args ) ) {
			$built_in = Core::get( $condition_id );

			if ( $built_in ) {
				self::$conditions[ $set_id ][ $condition_id ] = $built_in;
				self::update_allowed_queries( $built_in );

				return;
			}

			throw new InvalidArgumentException(
				sprintf( 'Built-in condition "%s" not found.', $condition_id )
			);
		}

		// Handle config-based conditions
		if ( is_string( $condition_id ) && ! empty( $args ) ) {
			$config = self::normalize_condition_config( $condition_id, $args );

			self::$conditions[ $set_id ][ $condition_id ] = $config;
			self::update_allowed_queries( $config );

			return;
		}

		throw new InvalidArgumentException( 'Invalid condition format.' );
	}

	/**
	 * Register multiple conditions from an array.
	 *
	 * @param string $set_id     The condition set ID.
	 * @param array  $conditions Array of conditions.
	 *
	 * @return void
	 */
	private static function register_conditions_array( string $set_id, array $conditions ): void {
		foreach ( $conditions as $key => $value ) {
			// Class-based: MyCondition::class
			if ( is_string( $value ) && class_exists( $value ) ) {
				self::register_condition( $set_id, $value );
				continue;
			}

			// Built-in reference: 'user_role'
			if ( is_string( $value ) && is_int( $key ) ) {
				self::register_condition( $set_id, $value );
				continue;
			}

			// Config-based: 'my_condition' => [...]
			if ( is_string( $key ) && is_array( $value ) ) {
				self::register_condition( $set_id, $key, $value );
			}
		}
	}

	/**
	 * Normalize a condition configuration array.
	 *
	 * @param string $condition_id The condition ID.
	 * @param array  $args         The condition arguments.
	 *
	 * @return array Normalized configuration.
	 */
	private static function normalize_condition_config( string $condition_id, array $args ): array {
		return wp_parse_args( $args, [
			'name'          => $condition_id,
			'label'         => ucwords( str_replace( [ '_', '-' ], ' ', $condition_id ) ),
			'group'         => 'General',
			'type'          => 'text',
			'multiple'      => false,
			'options'       => [],
			'units'         => [],
			'operators'     => null,
			'arg'           => null,
			'compare_value' => null,
			'required_args' => [],
			'post_type'     => null,
			'taxonomy'      => null,
			'role'          => null,
		] );
	}

	/**
	 * Update allowed queries based on condition config.
	 *
	 * @param array $config Condition configuration.
	 *
	 * @return void
	 */
	private static function update_allowed_queries( array $config ): void {
		$type = $config['type'] ?? '';

		if ( $type === 'post' && ! empty( $config['post_type'] ) ) {
			$post_types               = (array) $config['post_type'];
			self::$allowed_post_types = array_unique(
				array_merge( self::$allowed_post_types, $post_types )
			);
		}

		if ( $type === 'term' && ! empty( $config['taxonomy'] ) ) {
			$taxonomies               = (array) $config['taxonomy'];
			self::$allowed_taxonomies = array_unique(
				array_merge( self::$allowed_taxonomies, $taxonomies )
			);
		}

		if ( $type === 'user' && ! empty( $config['role'] ) ) {
			$roles               = (array) $config['role'];
			self::$allowed_roles = array_unique(
				array_merge( self::$allowed_roles, $roles )
			);
		}
	}

	/**
	 * Get a registered condition set.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return array|null Set configuration or null if not found.
	 */
	public static function get_set( string $set_id ): ?array {
		return self::$sets[ $set_id ] ?? null;
	}

	/**
	 * Get all registered condition sets.
	 *
	 * @return array<string, array>
	 */
	public static function get_sets(): array {
		return self::$sets;
	}

	/**
	 * Get a specific condition from a set.
	 *
	 * @param string $set_id       The condition set ID.
	 * @param string $condition_id The condition ID.
	 *
	 * @return array|null Condition configuration or null if not found.
	 */
	public static function get_condition( string $set_id, string $condition_id ): ?array {
		return self::$conditions[ $set_id ][ $condition_id ] ?? null;
	}

	/**
	 * Get all conditions for a set.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return array<string, array>
	 */
	public static function get_conditions( string $set_id ): array {
		$conditions = self::$conditions[ $set_id ] ?? [];

		// Resolve any callable options for JavaScript
		foreach ( $conditions as $key => $condition ) {
			if ( isset( $condition['options'] ) && is_callable( $condition['options'] ) ) {
				$conditions[ $key ]['options'] = call_user_func( $condition['options'] );
			}
			if ( isset( $condition['units'] ) && is_callable( $condition['units'] ) ) {
				$conditions[ $key ]['units'] = call_user_func( $condition['units'] );
			}
		}

		return $conditions;
	}

	/**
	 * Get raw conditions without resolving callables.
	 *
	 * Used internally for AJAX callbacks that need access to closures.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return array<string, array>
	 */
	public static function get_conditions_raw( string $set_id ): array {
		return self::$conditions[ $set_id ] ?? [];
	}

	/**
	 * Check if a post type is allowed for REST queries.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool
	 */
	public static function is_post_type_allowed( string $post_type ): bool {
		return in_array( $post_type, self::$allowed_post_types, true );
	}

	/**
	 * Check if a taxonomy is allowed for REST queries.
	 *
	 * @param string $taxonomy The taxonomy to check.
	 *
	 * @return bool
	 */
	public static function is_taxonomy_allowed( string $taxonomy ): bool {
		return in_array( $taxonomy, self::$allowed_taxonomies, true );
	}

	/**
	 * Check if a role is allowed for REST queries.
	 *
	 * @param string $role The role to check.
	 *
	 * @return bool
	 */
	public static function is_role_allowed( string $role ): bool {
		// If no roles specified, allow all
		if ( empty( self::$allowed_roles ) ) {
			return true;
		}

		return in_array( $role, self::$allowed_roles, true );
	}

	/**
	 * Get the PostType instance for a set.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return PostType|null
	 */
	public static function get_post_type_instance( string $set_id ): ?PostType {
		return self::$post_types[ $set_id ] ?? null;
	}

	/**
	 * Get the MetaBox instance for a set.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return MetaBox|null
	 */
	public static function get_meta_box_instance( string $set_id ): ?MetaBox {
		return self::$meta_boxes[ $set_id ] ?? null;
	}

	/**
	 * Get the REST API instance.
	 *
	 * @return RestApi|null
	 */
	public static function get_rest_api_instance(): ?RestApi {
		return self::$rest_api;
	}

	/**
	 * Get the Assets instance.
	 *
	 * @return Assets|null
	 */
	public static function get_assets_instance(): ?Assets {
		return self::$assets;
	}

	/**
	 * Check if the library has been initialized.
	 *
	 * @return bool
	 */
	public static function is_initialized(): bool {
		return self::$initialized;
	}

	/**
	 * Reset the registry (primarily for testing).
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$sets               = [];
		self::$conditions         = [];
		self::$post_types         = [];
		self::$meta_boxes         = [];
		self::$rest_api           = null;
		self::$assets             = null;
		self::$allowed_post_types = [];
		self::$allowed_taxonomies = [];
		self::$allowed_roles      = [];
		self::$initialized        = false;
	}

}