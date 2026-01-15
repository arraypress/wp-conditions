<?php
/**
 * Condition Matcher
 *
 * Evaluates conditions against provided arguments.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions;

use WP_Post;

/**
 * Class Matcher
 *
 * Evaluates conditions and returns match results.
 */
class Matcher {

	/**
	 * The condition set ID.
	 *
	 * @var string
	 */
	private string $set_id;

	/**
	 * Arguments to evaluate against.
	 *
	 * @var array
	 */
	private array $args;

	/**
	 * Constructor.
	 *
	 * @param string $set_id The condition set ID.
	 * @param array  $args   Arguments to evaluate against.
	 */
	public function __construct( string $set_id, array $args ) {
		$this->set_id = $set_id;
		$this->args   = $args;
	}

	/**
	 * Check conditions and return on first match.
	 *
	 * @return MatchResult
	 */
	public function check(): MatchResult {
		$rules = $this->get_rules();

		foreach ( $rules as $rule_post ) {
			$conditions = get_post_meta( $rule_post->ID, '_conditions', true );

			if ( empty( $conditions ) || ! is_array( $conditions ) ) {
				continue;
			}

			// OR logic between groups
			foreach ( $conditions as $group ) {
				if ( $this->check_group( $group ) ) {
					return new MatchResult( true, $rule_post, $group );
				}
			}
		}

		return new MatchResult( false );
	}

	/**
	 * Check all conditions and return all matches.
	 *
	 * @return MatchResultCollection
	 */
	public function check_all(): MatchResultCollection {
		$rules   = $this->get_rules();
		$matches = [];

		foreach ( $rules as $rule_post ) {
			$conditions = get_post_meta( $rule_post->ID, '_conditions', true );

			if ( empty( $conditions ) || ! is_array( $conditions ) ) {
				continue;
			}

			foreach ( $conditions as $group ) {
				if ( $this->check_group( $group ) ) {
					$matches[] = new MatchResult( true, $rule_post, $group );
					break; // Move to next rule
				}
			}
		}

		return new MatchResultCollection( $matches );
	}

	/**
	 * Check a single AND group.
	 *
	 * All conditions in the group must pass.
	 *
	 * @param array $group The condition group.
	 *
	 * @return bool
	 */
	private function check_group( array $group ): bool {
		$rules = $group['rules'] ?? [];

		if ( empty( $rules ) ) {
			return false;
		}

		foreach ( $rules as $rule ) {
			$result = $this->check_rule( $rule );

			// null = couldn't evaluate (missing args), skip
			// false = evaluated and failed
			// true = evaluated and passed
			if ( $result === false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check a single rule.
	 *
	 * @param array $rule The rule configuration.
	 *
	 * @return bool|null True if passed, false if failed, null if skipped.
	 */
	private function check_rule( array $rule ): ?bool {
		$condition_id = $rule['condition'] ?? '';
		$operator     = $rule['operator'] ?? '';
		$user_value   = $rule['value'] ?? null;

		if ( empty( $condition_id ) || empty( $operator ) ) {
			return null;
		}

		// Get condition configuration
		$condition = Registry::get_condition( $this->set_id, $condition_id );

		if ( ! $condition ) {
			return null;
		}

		// Check required args
		$required_args = $condition['required_args'] ?? [];
		foreach ( $required_args as $arg ) {
			if ( ! array_key_exists( $arg, $this->args ) ) {
				return null;
			}
		}

		// Get compare value
		$compare_value = $this->get_compare_value( $condition );

		// Handle number_unit type
		if ( $condition['type'] === 'number_unit' && is_array( $user_value ) ) {
			$this->args['_unit'] = $user_value['unit'] ?? null;
			$user_value          = $user_value['number'] ?? null;

			// Re-get compare value with unit in args
			$compare_value = $this->get_compare_value( $condition );
		}

		// Perform comparison
		return $this->compare( $condition, $operator, $user_value, $compare_value );
	}

	/**
	 * Get the compare value for a condition.
	 *
	 * @param array $condition The condition configuration.
	 *
	 * @return mixed
	 */
	private function get_compare_value( array $condition ): mixed {
		// If condition has an instance (class-based), use its method
		if ( isset( $condition['instance'] ) && $condition['instance'] instanceof Condition ) {
			return $condition['instance']->get_compare_value( $this->args );
		}

		// If there's a compare_value callback
		if ( isset( $condition['compare_value'] ) && is_callable( $condition['compare_value'] ) ) {
			return call_user_func( $condition['compare_value'], $this->args );
		}

		// Simple arg reference
		if ( isset( $condition['arg'] ) ) {
			return $this->args[ $condition['arg'] ] ?? null;
		}

		return null;
	}

	/**
	 * Perform comparison.
	 *
	 * @param array  $condition     The condition configuration.
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The value configured by the user.
	 * @param mixed  $compare_value The actual value to compare against.
	 *
	 * @return bool
	 */
	private function compare( array $condition, string $operator, mixed $user_value, mixed $compare_value ): bool {
		// If condition has an instance (class-based), use its compare method
		if ( isset( $condition['instance'] ) && $condition['instance'] instanceof Condition ) {
			return $condition['instance']->compare( $operator, $user_value, $compare_value );
		}

		// Use the comparator
		$type     = $condition['type'] ?? 'text';
		$multiple = $condition['multiple'] ?? false;

		$comparator = new Comparators\Comparator( $type, $multiple );

		return $comparator->compare( $operator, $user_value, $compare_value );
	}

	/**
	 * Get rule posts for this condition set.
	 *
	 * @return WP_Post[]
	 */
	private function get_rules(): array {
		return get_posts( [
			'post_type'      => $this->set_id,
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		] );
	}

}
