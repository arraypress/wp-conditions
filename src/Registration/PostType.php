<?php
/**
 * Post Type Registration
 *
 * Handles custom post type registration, labels, messages, redirects, and menu highlighting.
 *
 * @package     ArrayPress\Conditions\Registration
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Registration;

/**
 * Class PostType
 *
 * Manages custom post type registration and related functionality.
 */
class PostType {

	/**
	 * The condition set ID (also the post type name).
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
	 * Register the post type and hook into WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		// Don't register if already registered
		if ( post_type_exists( $this->set_id ) ) {
			return;
		}

		$this->register_post_type();
		$this->register_hooks();
	}

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	private function register_post_type(): void {
		$labels = $this->get_labels();

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => $this->config['show_in_menu'] !== false,
			'show_in_menu'        => $this->determine_menu_visibility(),
			'menu_icon'           => $this->config['menu_icon'],
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => $this->config['supports'] ?? [ 'title' ],
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => false,
			'exclude_from_search' => true,
		];

		register_post_type( $this->set_id, $args );
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		// Custom post updated messages
		add_filter( 'post_updated_messages', [ $this, 'filter_post_updated_messages' ] );

		// Menu highlighting for nested CPTs
		if ( ! empty( $this->config['parent_file'] ) ) {
			add_filter( 'parent_file', [ $this, 'filter_parent_file' ] );
		}

		if ( ! empty( $this->config['submenu_file'] ) ) {
			add_filter( 'submenu_file', [ $this, 'filter_submenu_file' ] );
		}

		// Redirect after trash
		if ( ! empty( $this->config['redirect']['after_trash'] ) ) {
			add_action( 'trashed_post', [ $this, 'handle_trash_redirect' ] );
		}

		// Admin notices for trash with undo
		if ( ! empty( $this->config['redirect']['show_undo'] ) ) {
			add_action( 'admin_notices', [ $this, 'display_trash_notice' ] );
		}
	}

	/**
	 * Get the post type labels.
	 *
	 * @return array
	 */
	private function get_labels(): array {
		$singular = $this->config['labels']['singular'];
		$plural   = $this->config['labels']['plural'];

		$labels = [
			'name'                  => $plural,
			'singular_name'         => $singular,
			'add_new'               => sprintf( __( 'Add New %s', 'arraypress' ), $singular ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'arraypress' ), $singular ),
			'edit_item'             => sprintf( __( 'Edit %s', 'arraypress' ), $singular ),
			'new_item'              => sprintf( __( 'New %s', 'arraypress' ), $singular ),
			'view_item'             => sprintf( __( 'View %s', 'arraypress' ), $singular ),
			'view_items'            => sprintf( __( 'View %s', 'arraypress' ), $plural ),
			'search_items'          => sprintf( __( 'Search %s', 'arraypress' ), $plural ),
			'not_found'             => sprintf( __( 'No %s found', 'arraypress' ), strtolower( $plural ) ),
			'not_found_in_trash'    => sprintf( __( 'No %s found in trash', 'arraypress' ), strtolower( $plural ) ),
			'all_items'             => sprintf( __( 'All %s', 'arraypress' ), $plural ),
			'archives'              => sprintf( __( '%s Archives', 'arraypress' ), $singular ),
			'attributes'            => sprintf( __( '%s Attributes', 'arraypress' ), $singular ),
			'insert_into_item'      => sprintf( __( 'Insert into %s', 'arraypress' ), strtolower( $singular ) ),
			'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'arraypress' ), strtolower( $singular ) ),
			'filter_items_list'     => sprintf( __( 'Filter %s list', 'arraypress' ), strtolower( $plural ) ),
			'items_list_navigation' => sprintf( __( '%s list navigation', 'arraypress' ), $plural ),
			'items_list'            => sprintf( __( '%s list', 'arraypress' ), $plural ),
		];

		/**
		 * Filter the post type labels.
		 *
		 * @param array  $labels   The labels array.
		 * @param string $set_id   The condition set ID.
		 * @param array  $config   The condition set configuration.
		 */
		return apply_filters( "{$this->set_id}_labels", $labels, $this->set_id, $this->config );
	}

	/**
	 * Determine menu visibility for the post type.
	 *
	 * @return bool|string
	 */
	private function determine_menu_visibility(): bool|string {
		// Explicitly hidden
		if ( $this->config['show_in_menu'] === false ) {
			return false;
		}

		// Nested under parent menu
		if ( ! empty( $this->config['menu_parent'] ) ) {
			return $this->config['menu_parent'];
		}

		// Top-level menu
		return true;
	}

	/**
	 * Filter post updated messages.
	 *
	 * @param array $messages The messages array.
	 *
	 * @return array
	 */
	public function filter_post_updated_messages( array $messages ): array {
		global $post;

		if ( ! $post || $post->post_type !== $this->set_id ) {
			return $messages;
		}

		$singular = $this->config['labels']['singular'];

		$messages[ $this->set_id ] = [
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( '%s updated.', 'arraypress' ), $singular ),
			2  => __( 'Custom field updated.', 'arraypress' ),
			3  => __( 'Custom field deleted.', 'arraypress' ),
			4  => sprintf( __( '%s updated.', 'arraypress' ), $singular ),
			5  => isset( $_GET['revision'] )
				? sprintf(
					__( '%s restored to revision from %s.', 'arraypress' ),
					$singular,
					wp_post_revision_title( (int) $_GET['revision'], false )
				)
				: false,
			6  => sprintf( __( '%s published.', 'arraypress' ), $singular ),
			7  => sprintf( __( '%s saved.', 'arraypress' ), $singular ),
			8  => sprintf( __( '%s submitted.', 'arraypress' ), $singular ),
			9  => sprintf(
				__( '%s scheduled for: %s.', 'arraypress' ),
				$singular,
				date_i18n( __( 'M j, Y @ G:i', 'arraypress' ), strtotime( $post->post_date ) )
			),
			10 => sprintf( __( '%s draft updated.', 'arraypress' ), $singular ),
		];

		/**
		 * Filter the post updated messages.
		 *
		 * @param array  $messages The messages for this post type.
		 * @param string $set_id   The condition set ID.
		 * @param array  $config   The condition set configuration.
		 */
		$messages[ $this->set_id ] = apply_filters(
			"{$this->set_id}_post_updated_messages",
			$messages[ $this->set_id ],
			$this->set_id,
			$this->config
		);

		return $messages;
	}

	/**
	 * Filter parent file for menu highlighting.
	 *
	 * @param string $parent_file The parent file.
	 *
	 * @return string
	 */
	public function filter_parent_file( string $parent_file ): string {
		global $current_screen;

		if ( $current_screen && $current_screen->post_type === $this->set_id ) {
			return $this->config['parent_file'];
		}

		return $parent_file;
	}

	/**
	 * Filter submenu file for menu highlighting.
	 *
	 * @param string|null $submenu_file The submenu file.
	 *
	 * @return string|null
	 */
	public function filter_submenu_file( ?string $submenu_file ): ?string {
		global $current_screen;

		if ( $current_screen && $current_screen->post_type === $this->set_id ) {
			return $this->config['submenu_file'];
		}

		return $submenu_file;
	}

	/**
	 * Handle redirect after trashing a post.
	 *
	 * @param int $post_id The post ID being trashed.
	 *
	 * @return void
	 */
	public function handle_trash_redirect( int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post || $post->post_type !== $this->set_id ) {
			return;
		}

		// Only redirect if we're in admin and this is a direct request (not AJAX)
		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		$redirect_url = $this->config['redirect']['after_trash'];

		// Add trashed message parameters
		$redirect_url = add_query_arg( [
			"{$this->set_id}_trashed" => 1,
			'ids'                     => $post_id,
		], admin_url( $redirect_url ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Display trash notice with undo link.
	 *
	 * @return void
	 */
	public function display_trash_notice(): void {
		if ( empty( $_GET["{$this->set_id}_trashed"] ) ) {
			return;
		}

		$ids = isset( $_GET['ids'] ) ? array_map( 'absint', explode( ',', $_GET['ids'] ) ) : [];

		if ( empty( $ids ) ) {
			return;
		}

		$singular = $this->config['labels']['singular'];
		$plural   = $this->config['labels']['plural'];
		$count    = count( $ids );

		// Build undo URL
		$undo_url = wp_nonce_url(
			admin_url( sprintf( 'post.php?action=untrash&post=%s', implode( ',', $ids ) ) ),
			'untrash-post_' . $ids[0]
		);

		$message = sprintf(
			_n(
				'%d %s moved to the Trash.',
				'%d %s moved to the Trash.',
				$count,
				'arraypress'
			),
			$count,
			$count === 1 ? strtolower( $singular ) : strtolower( $plural )
		);

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html( $message ),
			esc_url( $undo_url ),
			esc_html__( 'Undo', 'arraypress' )
		);
	}

	/**
	 * Get the set ID.
	 *
	 * @return string
	 */
	public function get_set_id(): string {
		return $this->set_id;
	}

	/**
	 * Get the configuration.
	 *
	 * @return array
	 */
	public function get_config(): array {
		return $this->config;
	}

}