<?php
/**
 * WordPress Options Helper
 *
 * Provides WordPress-specific option arrays for select fields.
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
 * Class WordPress
 *
 * WordPress-specific option arrays for select condition fields.
 */
class WordPress {

	/** Environment ***************************************************************/

	/**
	 * Get environment type options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_environment_types(): array {
		return [
			[ 'value' => 'local', 'label' => __( 'Local', 'arraypress' ) ],
			[ 'value' => 'development', 'label' => __( 'Development', 'arraypress' ) ],
			[ 'value' => 'staging', 'label' => __( 'Staging', 'arraypress' ) ],
			[ 'value' => 'production', 'label' => __( 'Production', 'arraypress' ) ],
		];
	}

	/** Users & Roles *************************************************************/

	/**
	 * Get role options for select field.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_roles(): array {
		$roles   = wp_roles()->get_names();
		$options = [];

		foreach ( $roles as $value => $label ) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Get user capabilities options.
	 *
	 * Returns all unique capabilities across all roles.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_capabilities(): array {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$capabilities = [];

		foreach ( $wp_roles->roles as $role ) {
			if ( isset( $role['capabilities'] ) && is_array( $role['capabilities'] ) ) {
				foreach ( $role['capabilities'] as $cap => $granted ) {
					if ( is_string( $cap ) && ! empty( $cap ) && $granted === true ) {
						$capabilities[ $cap ] = true;
					}
				}
			}
		}

		ksort( $capabilities );

		$options = [];
		foreach ( array_keys( $capabilities ) as $cap ) {
			$options[] = [
				'value' => $cap,
				'label' => self::format_capability_label( $cap ),
			];
		}

		return $options;
	}

	/**
	 * Format a capability name as a readable label.
	 *
	 * @param string $capability The capability name.
	 *
	 * @return string Formatted label.
	 */
	private static function format_capability_label( string $capability ): string {
		$label = str_replace( '_', ' ', $capability );

		return ucwords( $label );
	}

	/** Posts *********************************************************************/

	/**
	 * Get post status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_statuses(): array {
		$statuses = get_post_stati( [ 'show_in_admin_status_list' => true ], 'objects' );
		$options  = [];

		foreach ( $statuses as $status ) {
			$options[] = [
				'value' => $status->name,
				'label' => $status->label,
			];
		}

		return $options;
	}

	/**
	 * Get post type options.
	 *
	 * @param array $args Optional arguments for get_post_types().
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_types( array $args = [ 'public' => true ] ): array {
		$types   = get_post_types( $args, 'objects' );
		$options = [];

		foreach ( $types as $type ) {
			$options[] = [
				'value' => $type->name,
				'label' => $type->labels->singular_name,
			];
		}

		return $options;
	}

	/**
	 * Get post format options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_post_formats(): array {
		$formats = get_post_format_strings();
		$options = [];

		foreach ( $formats as $value => $label ) {
			$options[] = [
				'value' => $value ?: 'standard',
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Get page template options.
	 *
	 * @param string $post_type The post type to get templates for.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_page_templates( string $post_type = 'page' ): array {
		$templates = get_page_templates( null, $post_type );
		$options   = [];

		$options[] = [
			'value' => 'default',
			'label' => __( 'Default Template', 'arraypress' ),
		];

		foreach ( $templates as $name => $file ) {
			$options[] = [
				'value' => $file,
				'label' => $name,
			];
		}

		return $options;
	}

	/**
	 * Get comment status options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_comment_statuses(): array {
		return [
			[ 'value' => 'open', 'label' => __( 'Open', 'arraypress' ) ],
			[ 'value' => 'closed', 'label' => __( 'Closed', 'arraypress' ) ],
		];
	}

	/** Taxonomies ****************************************************************/

	/**
	 * Get registered taxonomies as options.
	 *
	 * @param array $args Optional. Arguments to pass to get_taxonomies().
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_taxonomies( array $args = [ 'public' => true ] ): array {
		$taxonomies = get_taxonomies( $args, 'objects' );
		$options    = [];

		foreach ( $taxonomies as $taxonomy ) {
			$options[] = [
				'value' => $taxonomy->name,
				'label' => $taxonomy->labels->singular_name,
			];
		}

		return $options;
	}

	/** Media *********************************************************************/

	/**
	 * Get registered image sizes as options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_image_sizes(): array {
		$sizes   = get_intermediate_image_sizes();
		$options = [];

		foreach ( $sizes as $size ) {
			$options[] = [
				'value' => $size,
				'label' => ucwords( str_replace( [ '-', '_' ], ' ', $size ) ),
			];
		}

		$options[] = [
			'value' => 'full',
			'label' => __( 'Full Size', 'arraypress' ),
		];

		return $options;
	}

	/** Menus & Sidebars **********************************************************/

	/**
	 * Get registered nav menus as options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_nav_menus(): array {
		$menus   = wp_get_nav_menus();
		$options = [];

		foreach ( $menus as $menu ) {
			$options[] = [
				'value' => (string) $menu->term_id,
				'label' => $menu->name,
			];
		}

		return $options;
	}

	/**
	 * Get registered menu locations as options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_menu_locations(): array {
		$locations = get_registered_nav_menus();
		$options   = [];

		foreach ( $locations as $location => $description ) {
			$options[] = [
				'value' => $location,
				'label' => $description,
			];
		}

		return $options;
	}

	/**
	 * Get registered sidebars/widget areas as options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_sidebars(): array {
		global $wp_registered_sidebars;

		$options = [];

		if ( ! empty( $wp_registered_sidebars ) ) {
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$options[] = [
					'value' => $sidebar['id'],
					'label' => $sidebar['name'],
				];
			}
		}

		return $options;
	}

}