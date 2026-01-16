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
use ArrayPress\Conditions\Conditions\BuiltIn;
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

		// Register REST routes
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_routes' ] );

		// Admin assets
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'register_admin_assets' ] );

		// Meta boxes
		add_action( 'add_meta_boxes', [ __CLASS__, 'register_meta_boxes' ] );

		// Save meta
		add_action( 'save_post', [ __CLASS__, 'save_conditions_meta' ], 10, 2 );
	}

	/**
	 * Register a condition set.
	 *
	 * Creates a custom post type for storing rules with this condition set.
	 *
	 * @param string $set_id      Unique identifier for the condition set.
	 * @param array  $args        {
	 *                            Configuration arguments.
	 *
	 * @type array   $labels      Labels for the CPT (singular, plural).
	 * @type string  $menu_icon   Dashicon or URL for menu icon.
	 * @type string  $menu_parent Parent menu slug to nest under.
	 * @type string  $capability  Required capability to manage.
	 * @type array   $conditions  Array of conditions to register.
	 *                            }
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
			'labels'       => [
				'singular' => ucwords( str_replace( [ '_', '-' ], ' ', $set_id ) ),
				'plural'   => ucwords( str_replace( [ '_', '-' ], ' ', $set_id ) ) . 's',
			],
			'menu_icon'    => 'dashicons-yes-alt',
			'menu_parent'  => null,
			'show_in_menu' => true,
			'capability'   => 'manage_options',
			'conditions'   => [],
			'description'  => __( 'Configure when this rule should apply. Groups are connected with OR logic, conditions within a group use AND logic.', 'arraypress' ),
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

		// Register the CPT immediately
		self::register_single_post_type( $set_id );
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
			$built_in = BuiltIn::get( $condition_id );

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
				continue;
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
			'operators'     => null, // Will be set based on type
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
	 * Get a condition set configuration.
	 * /**
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
	 * Register a single custom post type for a condition set.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return void
	 */
	private static function register_single_post_type( string $set_id ): void {
		if ( ! isset( self::$sets[ $set_id ] ) ) {
			return;
		}

		// Don't register if already registered
		if ( post_type_exists( $set_id ) ) {
			return;
		}

		$config = self::$sets[ $set_id ];
		$labels = $config['labels'];

		$args = [
			'labels'              => [
				'name'               => $labels['plural'],
				'singular_name'      => $labels['singular'],
				'add_new'            => sprintf( 'Add New %s', $labels['singular'] ),
				'add_new_item'       => sprintf( 'Add New %s', $labels['singular'] ),
				'edit_item'          => sprintf( 'Edit %s', $labels['singular'] ),
				'new_item'           => sprintf( 'New %s', $labels['singular'] ),
				'view_item'          => sprintf( 'View %s', $labels['singular'] ),
				'search_items'       => sprintf( 'Search %s', $labels['plural'] ),
				'not_found'          => sprintf( 'No %s found', strtolower( $labels['plural'] ) ),
				'not_found_in_trash' => sprintf( 'No %s found in trash', strtolower( $labels['plural'] ) ),
			],
			'public'              => false,
			'show_ui'             => $config['show_in_menu'] !== false,
			'show_in_menu'        => self::determine_menu_visibility( $config ),
			'menu_icon'           => $config['menu_icon'],
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
		];

		register_post_type( $set_id, $args );

		// Register the conditions meta for this post type
		self::register_conditions_meta( $set_id );
	}

	/**
	 * Determine menu visibility for a condition set.
	 *
	 * @param array $config The condition set configuration.
	 *
	 * @return bool|string False to hide, true for top-level, or parent slug.
	 */
	private static function determine_menu_visibility( array $config ): bool|string {
		// Explicitly hidden
		if ( $config['show_in_menu'] === false ) {
			return false;
		}

		// Nested under parent menu
		if ( ! empty( $config['menu_parent'] ) ) {
			return $config['menu_parent'];
		}

		// Top-level menu
		return true;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public static function register_rest_routes(): void {
		$namespace = 'conditions/v1';

		// Posts endpoint
		register_rest_route( $namespace, '/posts', [
			'methods'             => 'GET',
			'callback'            => [ REST\Posts::class, 'search' ],
			'permission_callback' => [ __CLASS__, 'rest_permission_check' ],
			'args'                => [
				'post_type' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'search'    => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'include'   => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		// Terms endpoint
		register_rest_route( $namespace, '/terms', [
			'methods'             => 'GET',
			'callback'            => [ REST\Terms::class, 'search' ],
			'permission_callback' => [ __CLASS__, 'rest_permission_check' ],
			'args'                => [
				'taxonomy' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'search'   => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'include'  => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		// Users endpoint
		register_rest_route( $namespace, '/users', [
			'methods'             => 'GET',
			'callback'            => [ REST\Users::class, 'search' ],
			'permission_callback' => [ __CLASS__, 'rest_permission_check' ],
			'args'                => [
				'role'    => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'search'  => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'include' => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		// Custom AJAX endpoint for type => 'ajax' conditions
		register_rest_route( $namespace, '/ajax', [
			'methods'             => 'GET',
			'callback'            => [ REST\Ajax::class, 'handle' ],
			'permission_callback' => [ __CLASS__, 'rest_permission_check' ],
			'args'                => [
				'set_id'       => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'condition_id' => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'search'       => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'include'      => [
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * REST API permission check.
	 *
	 * @return bool
	 */
	public static function rest_permission_check(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 *
	 * @return void
	 */
	public static function register_admin_assets( string $hook ): void {
		global $post_type;

		// Only load on our CPT edit screens
		if ( ! isset( self::$sets[ $post_type ] ) ) {
			return;
		}

		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		// Select2
		wp_enqueue_composer_style(
			'conditions-select2',
			__FILE__,
			'css/select2.min.css',
			[],
			'4.1.0'
		);

		wp_enqueue_composer_script(
			'conditions-select2',
			__FILE__,
			'js/select2.min.js',
			[ 'jquery' ],
			'4.1.0',
			true
		);

		// Conditions UI
		wp_enqueue_composer_style(
			'conditions-admin',
			__FILE__,
			'css/conditions.css',
			[ 'conditions-select2' ],
			'1.0.0'
		);

		wp_enqueue_composer_script(
			'conditions-admin',
			__FILE__,
			'js/conditions.js',
			[ 'jquery', 'conditions-select2', 'wp-util' ],
			'1.0.0',
			true
		);

		// Localize script
		wp_localize_script( 'conditions-admin', 'conditionsData', [
			'conditions' => self::get_conditions( $post_type ),
			'operators'  => Operators::get_all(),
			'restUrl'    => rest_url( 'conditions/v1' ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'i18n'       => [
				'selectCondition' => __( 'Select condition...', 'arraypress' ),
				'selectOperator'  => __( 'Select operator...', 'arraypress' ),
				'selectValue'     => __( 'Select...', 'arraypress' ),
				'search'          => __( 'Search...', 'arraypress' ),
				'addCondition'    => __( 'Add Condition', 'arraypress' ),
				'addGroup'        => __( 'Add "OR" Group', 'arraypress' ),
				'remove'          => __( 'Remove', 'arraypress' ),
				'and'             => __( 'AND', 'arraypress' ),
				'or'              => __( 'OR', 'arraypress' ),
			],
		] );
	}

	/**
	 * Register meta boxes.
	 *
	 * @return void
	 */
	public static function register_meta_boxes(): void {
		foreach ( self::$sets as $set_id => $config ) {
			add_meta_box(
				$set_id . '_conditions',
				__( 'Conditions', 'arraypress' ),
				[ Admin\MetaBox::class, 'render' ],
				$set_id,
				'normal',
				'high',
				[ 'set_id' => $set_id ]
			);
		}
	}

	/**
	 * Register post meta for conditions.
	 *
	 * @param string $post_type The post type to register meta for.
	 *
	 * @return void
	 */
	private static function register_conditions_meta( string $post_type ): void {
		register_post_meta( $post_type, '_conditions', [
			'type'          => 'array',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function ( $allowed, $meta_key, $post_id ) use ( $post_type ) {
				$capability = self::$sets[ $post_type ]['capability'] ?? 'manage_options';

				return current_user_can( $capability, $post_id );
			},
		] );
	}

	/**
	 * Save conditions meta from form submission.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public static function save_conditions_meta( int $post_id, \WP_Post $post ): void {
		// Check if this is one of our CPTs
		if ( ! isset( self::$sets[ $post->post_type ] ) ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['conditions_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['conditions_nonce'], 'save_conditions' ) ) {
			return;
		}

		// Don't save on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Get raw conditions from form
		$raw_conditions = $_POST['_conditions'] ?? [];

		// Get condition configurations for custom sanitization
		$condition_configs = self::get_conditions_raw( $post->post_type );

		// Sanitize with condition configs for custom sanitizers
		$conditions = Admin\Sanitizer::sanitize_conditions( $raw_conditions, $condition_configs );

		// Save
		update_post_meta( $post_id, '_conditions', $conditions );
	}

}
