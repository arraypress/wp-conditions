<?php
/**
 * Meta Box Renderer
 *
 * Renders the conditions meta box in the admin.
 *
 * @package     ArrayPress\Conditions\Admin
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Admin;

use ArrayPress\Conditions\Registry;
use WP_Post;

/**
 * Class MetaBoxRenderer
 *
 * Renders the conditions UI meta box.
 */
class MetaBoxRenderer {

    /**
     * Render the meta box.
     *
     * @param WP_Post $post    The post object.
     * @param array   $metabox Metabox arguments.
     *
     * @return void
     */
    public static function render( WP_Post $post, array $metabox ): void {
        $set_id      = $metabox['args']['set_id'] ?? '';
        $conditions  = Registry::get_conditions( $set_id );
        $saved       = get_post_meta( $post->ID, '_conditions', true );
        $config      = Registry::get_set( $set_id );
        $description = $config['description'] ?? '';

        if ( empty( $saved ) || ! is_array( $saved ) ) {
            $saved = [];
        }

        // Nonce for security
        wp_nonce_field( 'save_conditions', 'conditions_nonce' );
        ?>
        <div class="conditions-builder" data-set-id="<?php echo esc_attr( $set_id ); ?>">
            <?php if ( ! empty( $description ) ) : ?>
                <p class="description">
                    <?php echo esc_html( $description ); ?>
                </p>
            <?php endif; ?>

            <div class="condition-groups" data-conditions='<?php echo esc_attr( wp_json_encode( $saved ) ); ?>'>
                <!-- Groups will be rendered by JavaScript -->
            </div>

            <button type="button" class="button add-group">
                <?php esc_html_e( '+ Add "OR" Group', 'arraypress' ); ?>
            </button>
        </div>

        <?php self::render_templates( $conditions ); ?>
        <?php
    }

    /**
     * Render JavaScript templates.
     *
     * @param array $conditions The conditions array.
     *
     * @return void
     */
    private static function render_templates( array $conditions ): void {
        ?>
        <!-- Templates for JavaScript -->
        <script type="text/html" id="tmpl-condition-group">
            <div class="condition-group" data-group-id="{{ data.id }}">
                <div class="condition-group-header">
                    <span class="group-label">
                        <# if ( data.index === 0 ) { #>
                            <?php esc_html_e( 'Match all of the following rules', 'arraypress' ); ?>
                        <# } else { #>
                            <?php esc_html_e( 'Or match all of the following rules', 'arraypress' ); ?>
                        <# } #>
                    </span>
                    <div class="group-actions">
                        <a href="#" class="duplicate-group"><?php esc_html_e( 'Duplicate', 'arraypress' ); ?></a>
                        <a href="#" class="delete-group"><?php esc_html_e( 'Delete', 'arraypress' ); ?></a>
                    </div>
                </div>

                <div class="conditions-list">
                    <!-- Conditions will be rendered here -->
                </div>

                <div class="conditions-list-footer">
                    <button type="button" class="button add-condition">
                        <?php esc_html_e( '+ Add Condition', 'arraypress' ); ?>
                    </button>
                </div>
            </div>
        </script>

        <script type="text/html" id="tmpl-condition-row">
            <div class="condition-row" data-condition-id="{{ data.id }}">
                <div class="condition-fields">
                    <select class="condition-select conditions-condition-select"
                            name="_conditions[{{ data.groupId }}][rules][{{ data.id }}][condition]">
                        <option value=""><?php esc_html_e( 'Select condition...', 'arraypress' ); ?></option>
                        <?php echo self::render_condition_options( $conditions ); ?>
                    </select>

                    <select class="operator-select"
                            name="_conditions[{{ data.groupId }}][rules][{{ data.id }}][operator]" disabled>
                        <option value=""><?php esc_html_e( 'Select...', 'arraypress' ); ?></option>
                    </select>

                    <div class="value-field-wrapper">
                        <input type="text" class="value-input" disabled
                               placeholder="<?php esc_attr_e( 'Select a condition first', 'arraypress' ); ?>">
                    </div>

                    <span class="condition-tooltip" style="display: none;">
                        <span class="dashicons dashicons-info-outline"></span>
                    </span>
                </div>

                <div class="condition-row-actions">
                    <button type="button" class="button-link remove-condition"
                            title="<?php esc_attr_e( 'Remove', 'arraypress' ); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
        </script>

        <script type="text/html" id="tmpl-group-connector">
            <div class="group-connector"><?php esc_html_e( 'OR', 'arraypress' ); ?></div>
        </script>
        <?php
    }

    /**
     * Render condition options grouped by category.
     *
     * @param array $conditions The conditions to render.
     *
     * @return string
     */
    private static function render_condition_options( array $conditions ): string {
        // Group conditions by their group property
        $grouped = [];
        foreach ( $conditions as $id => $condition ) {
            $group               = $condition['group'] ?? 'General';
            $grouped[ $group ][] = [
                    'id'    => $id,
                    'label' => $condition['label'] ?? $id,
            ];
        }

        // Sort groups alphabetically
        ksort( $grouped );

        $html = '';
        foreach ( $grouped as $group => $items ) {
            $html .= '<optgroup label="' . esc_attr( $group ) . '">';
            foreach ( $items as $item ) {
                $html .= '<option value="' . esc_attr( $item['id'] ) . '">' . esc_html( $item['label'] ) . '</option>';
            }
            $html .= '</optgroup>';
        }

        return $html;
    }

}