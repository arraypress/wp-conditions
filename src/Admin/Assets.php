<?php
/**
 * Admin Assets
 *
 * Handles script and style enqueuing for the conditions admin interface.
 *
 * @package     ArrayPress\Conditions\Admin
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Admin;

use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Registry;

/**
 * Class Assets
 *
 * Manages admin assets for conditions UI.
 */
class Assets {

	/**
	 * Registered condition set IDs.
	 *
	 * @var array
	 */
	private array $set_ids = [];

	/**
	 * Constructor.
	 *
	 * @param array $set_ids Array of condition set IDs.
	 */
	public function __construct( array $set_ids = [] ) {
		$this->set_ids = $set_ids;
	}

	/**
	 * Register asset hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add a set ID to track.
	 *
	 * @param string $set_id The condition set ID.
	 *
	 * @return void
	 */
	public function add_set_id( string $set_id ): void {
		if ( ! in_array( $set_id, $this->set_ids, true ) ) {
			$this->set_ids[] = $set_id;
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		global $post_type;

		// Only load on our CPT edit screens
		if ( ! in_array( $post_type, $this->set_ids, true ) ) {
			return;
		}

		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$this->enqueue_select2();
		$this->enqueue_conditions_ui( $post_type );
	}

	/**
	 * Enqueue Select2 assets.
	 *
	 * @return void
	 */
	private function enqueue_select2(): void {
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
	}

	/**
	 * Enqueue conditions UI assets.
	 *
	 * @param string $post_type The current post type.
	 *
	 * @return void
	 */
	private function enqueue_conditions_ui( string $post_type ): void {
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
		wp_localize_script( 'conditions-admin', 'conditionsData', $this->get_localized_data( $post_type ) );
	}

	/**
	 * Get localized data for JavaScript.
	 *
	 * @param string $post_type The current post type.
	 *
	 * @return array
	 */
	private function get_localized_data( string $post_type ): array {
		$data = [
			'conditions' => Registry::get_conditions( $post_type ),
			'operators'  => Operators::get_all(),
			'restUrl'    => rest_url( 'conditions/v1' ),
			'nonce'      => wp_create_nonce( 'wp_rest' ),
			'i18n'       => $this->get_i18n_strings(),
		];

		/**
		 * Filter the localized data passed to JavaScript.
		 *
		 * @param array  $data      The localized data.
		 * @param string $post_type The current post type.
		 */
		return apply_filters( 'conditions_localized_data', $data, $post_type );
	}

	/**
	 * Get i18n strings for JavaScript.
	 *
	 * @return array
	 */
	private function get_i18n_strings(): array {
		$strings = [
			'selectCondition' => __( 'Select condition...', 'arraypress' ),
			'selectOperator'  => __( 'Select operator...', 'arraypress' ),
			'selectValue'     => __( 'Select...', 'arraypress' ),
			'search'          => __( 'Search...', 'arraypress' ),
			'addCondition'    => __( 'Add Condition', 'arraypress' ),
			'addGroup'        => __( 'Add "OR" Group', 'arraypress' ),
			'remove'          => __( 'Remove', 'arraypress' ),
			'and'             => __( 'AND', 'arraypress' ),
			'or'              => __( 'OR', 'arraypress' ),
			'duplicate'       => __( 'Duplicate', 'arraypress' ),
			'delete'          => __( 'Delete', 'arraypress' ),
			'matchAll'        => __( 'Match all of the following rules', 'arraypress' ),
			'orMatchAll'      => __( 'Or match all of the following rules', 'arraypress' ),
			'noResults'       => __( 'No results found', 'arraypress' ),
			'loading'         => __( 'Loading...', 'arraypress' ),
			'errorLoading'    => __( 'Error loading results', 'arraypress' ),
		];

		/**
		 * Filter the i18n strings for JavaScript.
		 *
		 * @param array $strings The i18n strings.
		 */
		return apply_filters( 'conditions_i18n_strings', $strings );
	}

}