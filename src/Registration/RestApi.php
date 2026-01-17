<?php
/**
 * REST API Registration
 *
 * Handles REST API route registration for conditions.
 *
 * @package     ArrayPress\Conditions\Registration
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Registration;

use ArrayPress\Conditions\REST;

/**
 * Class RestApi
 *
 * Manages REST API route registration.
 */
class RestApi {

	/**
	 * The REST namespace.
	 *
	 * @var string
	 */
	private string $namespace = 'conditions/v1';

	/**
	 * Capability required for REST access.
	 *
	 * @var string
	 */
	private string $capability;

	/**
	 * Constructor.
	 *
	 * @param string $capability The capability required for REST access.
	 */
	public function __construct( string $capability = 'manage_options' ) {
		$this->capability = $capability;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register all REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$this->register_posts_route();
		$this->register_terms_route();
		$this->register_users_route();
		$this->register_ajax_route();
	}

	/**
	 * Register posts endpoint.
	 *
	 * @return void
	 */
	private function register_posts_route(): void {
		register_rest_route( $this->namespace, '/posts', [
			'methods'             => 'GET',
			'callback'            => [ REST\Posts::class, 'search' ],
			'permission_callback' => [ $this, 'permission_check' ],
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
	}

	/**
	 * Register terms endpoint.
	 *
	 * @return void
	 */
	private function register_terms_route(): void {
		register_rest_route( $this->namespace, '/terms', [
			'methods'             => 'GET',
			'callback'            => [ REST\Terms::class, 'search' ],
			'permission_callback' => [ $this, 'permission_check' ],
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
	}

	/**
	 * Register users endpoint.
	 *
	 * @return void
	 */
	private function register_users_route(): void {
		register_rest_route( $this->namespace, '/users', [
			'methods'             => 'GET',
			'callback'            => [ REST\Users::class, 'search' ],
			'permission_callback' => [ $this, 'permission_check' ],
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
	}

	/**
	 * Register custom AJAX endpoint for type => 'ajax' conditions.
	 *
	 * @return void
	 */
	private function register_ajax_route(): void {
		register_rest_route( $this->namespace, '/ajax', [
			'methods'             => 'GET',
			'callback'            => [ REST\Ajax::class, 'handle' ],
			'permission_callback' => [ $this, 'permission_check' ],
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
	public function permission_check(): bool {
		/**
		 * Filter the capability required for REST API access.
		 *
		 * @param string $capability The required capability.
		 */
		$capability = apply_filters( 'conditions_rest_capability', $this->capability );

		return current_user_can( $capability );
	}

	/**
	 * Get the REST namespace.
	 *
	 * @return string
	 */
	public function get_namespace(): string {
		return $this->namespace;
	}

}