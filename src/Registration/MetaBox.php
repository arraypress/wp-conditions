<?php
/**
 * Meta Box Registration
 *
 * Handles meta box registration for condition sets.
 *
 * @package     ArrayPress\Conditions\Registration
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Registration;

use ArrayPress\Conditions\Admin\MetaBoxRenderer;
use ArrayPress\Conditions\Admin\Sanitizer;
use ArrayPress\Conditions\Registry;
use WP_Post;

/**
 * Class MetaBox
 *
 * Manages meta box registration for condition sets.
 */
class MetaBox {

	/**
	 * The condition set ID.
	 *
	 * @var string
	 */
	private string $set_id;

	/**
	 * The condition set configuration.
	 *
	 * @var array
	 */
	private array $config;

	/**
	 * Constructor.
	 *
	 * @param string $set_id The condition set ID.
	 * @param array  $config The condition set configuration.
	 */
	public function __construct( string $set_id, array $config ) {
		$this->set_id = $set_id;
		$this->config = $config;
	}

	/**
	 * Register the meta box.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta' ], 10, 2 );

		// Register the post meta
		$this->register_meta();
	}

	/**
	 * Add the meta box.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		$title = $this->config['metabox_title'] ?? __( 'Conditions', 'arraypress' );

		/**
		 * Filter the meta box title.
		 *
		 * @param string $title  The meta box title.
		 * @param string $set_id The condition set ID.
		 * @param array  $config The condition set configuration.
		 */
		$title = apply_filters( "{$this->set_id}_metabox_title", $title, $this->set_id, $this->config );

		add_meta_box(
			$this->set_id . '_conditions',
			$title,
			[ MetaBoxRenderer::class, 'render' ],
			$this->set_id,
			'normal',
			'high',
			[ 'set_id' => $this->set_id ]
		);
	}

	/**
	 * Register post meta for conditions.
	 *
	 * @return void
	 */
	private function register_meta(): void {
		register_post_meta( $this->set_id, '_conditions', [
			'type'          => 'array',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function ( $allowed, $meta_key, $post_id ) {
				$capability = $this->config['capability'] ?? 'manage_options';

				return current_user_can( $capability, $post_id );
			},
		] );
	}

	/**
	 * Save conditions meta from form submission.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function save_meta( int $post_id, WP_Post $post ): void {
		// Check if this is our CPT
		if ( $post->post_type !== $this->set_id ) {
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

		// Check permissions
		$capability = $this->config['capability'] ?? 'manage_options';
		if ( ! current_user_can( $capability, $post_id ) ) {
			return;
		}

		// Get raw conditions from form
		$raw_conditions = $_POST['_conditions'] ?? [];

		// Get condition configurations for custom sanitization
		$condition_configs = Registry::get_conditions_raw( $this->set_id );

		// Sanitize with condition configs for custom sanitizers
		$conditions = Sanitizer::sanitize_conditions( $raw_conditions, $condition_configs );

		/**
		 * Filter the conditions before saving.
		 *
		 * @param array   $conditions The sanitized conditions.
		 * @param int     $post_id    The post ID.
		 * @param WP_Post $post       The post object.
		 * @param string  $set_id     The condition set ID.
		 */
		$conditions = apply_filters( "{$this->set_id}_save_conditions", $conditions, $post_id, $post, $this->set_id );

		// Save
		update_post_meta( $post_id, '_conditions', $conditions );

		/**
		 * Action fired after conditions are saved.
		 *
		 * @param int     $post_id    The post ID.
		 * @param array   $conditions The saved conditions.
		 * @param WP_Post $post       The post object.
		 * @param string  $set_id     The condition set ID.
		 */
		do_action( "{$this->set_id}_conditions_saved", $post_id, $conditions, $post, $this->set_id );
	}

}