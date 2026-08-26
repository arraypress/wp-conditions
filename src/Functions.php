<?php
/**
 * Global Helper Functions
 *
 * Simple global functions for registering and checking conditions.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

use ArrayPress\Conditions\Registry;
use ArrayPress\Conditions\Matcher;
use ArrayPress\Conditions\Models\MatchResult;
use ArrayPress\Conditions\Models\MatchResultCollection;

if ( ! function_exists( 'register_conditions' ) ) :
	/**
	 * Register a condition set with its conditions.
	 *
	 * Creates a custom post type for storing rules and registers
	 * the specified conditions for use in that set.
	 *
	 * Example usage:
	 * ```php
	 * register_conditions( 'fraud_rule', [
	 *     'labels' => [
	 *         'singular' => 'Fraud Rule',
	 *         'plural'   => 'Fraud Rules',
	 *     ],
	 *     'menu_icon' => 'dashicons-shield',
	 *     'conditions' => [
	 *         // Config-based
	 *         'order_total' => [
	 *             'label' => 'Order Total',
	 *             'group' => 'Order',
	 *             'type'  => 'number',
	 *             'arg'   => 'order_total',
	 *         ],
	 *         // Built-in conditions
	 *         'user_role',
	 *         'day_of_week',
	 *         // Class-based
	 *         My_Custom_Condition::class,
	 *     ],
	 * ] );
	 * ```
	 *
	 * @param string $set_id      Unique identifier for the condition set.
	 * @param array  $args        {
	 *                            Configuration arguments.
	 *
	 * @type array   $labels      Labels (singular, plural) for the CPT.
	 * @type string  $menu_icon   Dashicon or URL for the menu icon.
	 * @type string  $menu_parent Parent menu slug to nest under.
	 * @type string  $capability  Required capability to manage rules.
	 * @type array   $conditions  Array of conditions to register.
	 *                            }
	 *
	 * @return void
	 */
	function register_conditions( string $set_id, array $args = [] ): void {
		Registry::register_set( $set_id, $args );
	}
endif;

if ( ! function_exists( 'register_condition' ) ) :
	/**
	 * Register a single condition to an existing set.
	 *
	 * Use this to add conditions to a set after initial registration,
	 * or to extend a set registered by another plugin.
	 *
	 * Example usage:
	 * ```php
	 * // Config-based
	 * register_condition( 'fraud_rule', 'ip_address', [
	 *     'label' => 'IP Address',
	 *     'group' => 'Customer',
	 *     'type'  => 'text',
	 *     'arg'   => 'ip_address',
	 * ] );
	 *
	 * // Class-based
	 * register_condition( 'fraud_rule', My_Custom_Condition::class );
	 *
	 * // Built-in
	 * register_condition( 'fraud_rule', 'user_role' );
	 * ```
	 *
	 * @param string       $set_id       The condition set ID.
	 * @param string|array $condition_id Condition ID, class name, or built-in name.
	 * @param array        $args         Condition configuration (if config-based).
	 *
	 * @return void
	 */
	function register_condition( string $set_id, string|array $condition_id, array $args = [] ): void {
		Registry::register_condition( $set_id, $condition_id, $args );
	}
endif;

if ( ! function_exists( 'check_conditions' ) ) :
	/**
	 * Check conditions and return on first match.
	 *
	 * Evaluates all rules in a condition set against the provided
	 * arguments. Returns immediately when a match is found.
	 *
	 * Example usage:
	 * ```php
	 * // Basic usage
	 * $result = check_conditions( 'fraud_rule', [
	 *     'order_total'     => 150.00,
	 *     'billing_country' => 'US',
	 *     'user_id'         => 123,
	 * ] );
	 *
	 * // With custom query args (meta query, ordering, etc.)
	 * $result = check_conditions( 'discount_rule', $args, [
	 *     'orderby'    => 'meta_value_num',
	 *     'meta_key'   => '_priority',
	 *     'order'      => 'DESC',
	 *     'meta_query' => [
	 *         [
	 *             'key'     => '_active',
	 *             'value'   => '1',
	 *             'compare' => '=',
	 *         ],
	 *     ],
	 * ] );
	 *
	 * if ( $result->matched() ) {
	 *     $rule = $result->get_rule();
	 *     echo "Matched rule: " . $rule->post_title;
	 * }
	 * ```
	 *
	 * @param string $set_id     The condition set ID.
	 * @param array  $args       Arguments to evaluate conditions against.
	 * @param array  $query_args Optional. Query arguments for retrieving rules.
	 *                           Supports all WP_Query arguments except 'post_type'.
	 *
	 * @return MatchResult
	 */
	function check_conditions( string $set_id, array $args = [], array $query_args = [] ): MatchResult {
		$matcher = new Matcher( $set_id, $args, $query_args );

		return $matcher->check();
	}
endif;

if ( ! function_exists( 'check_all_conditions' ) ) :
	/**
	 * Check all conditions and return all matches.
	 *
	 * Evaluates all rules in a condition set against the provided
	 * arguments. Returns a collection of all matching rules.
	 *
	 * Example usage:
	 * ```php
	 * // Basic usage
	 * $results = check_all_conditions( 'discount_rule', [
	 *     'cart_total' => 200.00,
	 *     'user_role'  => 'wholesale',
	 * ] );
	 *
	 * // With custom query args
	 * $results = check_all_conditions( 'notification_rule', $args, [
	 *     'posts_per_page' => 10,
	 *     'meta_query'     => [
	 *         [
	 *             'key'     => '_enabled',
	 *             'value'   => '1',
	 *             'compare' => '=',
	 *         ],
	 *     ],
	 * ] );
	 *
	 * if ( $results->has_matches() ) {
	 *     foreach ( $results as $match ) {
	 *         echo "Matched: " . $match->get_rule_title();
	 *     }
	 * }
	 * ```
	 *
	 * @param string $set_id     The condition set ID.
	 * @param array  $args       Arguments to evaluate conditions against.
	 * @param array  $query_args Optional. Query arguments for retrieving rules.
	 *                           Supports all WP_Query arguments except 'post_type'.
	 *
	 * @return MatchResultCollection
	 */
	function check_all_conditions( string $set_id, array $args = [], array $query_args = [] ): MatchResultCollection {
		$matcher = new Matcher( $set_id, $args, $query_args );

		return $matcher->check_all();
	}
endif;
