<?php
/**
 * Condition Matcher
 *
 * Evaluates conditions against provided arguments.
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
use ArrayPress\Conditions\Models\MatchResult;
use ArrayPress\Conditions\Models\MatchResultCollection;
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
	 * Query arguments for retrieving rules.
	 *
	 * @var array
	 */
	private array $query_args;

	/**
	 * Default query arguments for retrieving rules.
	 *
	 * @var array
	 */
	private const DEFAULT_QUERY_ARGS = [
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		// The id as a tie-breaker. Every rule has a menu_order of nought until
		// somebody sets one, and "ORDER BY menu_order" alone leaves the
		// database to pick, so which rule check() returned first could change
		// between servers.
		'orderby'        => [
			'menu_order' => 'ASC',
			'ID'         => 'ASC',
		],
	];

	/**
	 * Constructor.
	 *
	 * @param string $set_id     The condition set ID.
	 * @param array  $args       Arguments to evaluate against.
	 * @param array  $query_args Optional. Query arguments for retrieving rules.
	 */
	public function __construct( string $set_id, array $args, array $query_args = [] ) {
		$this->set_id     = $set_id;
		$this->args       = $args;
		$this->query_args = $query_args;
	}

	/**
	 * Evaluate a conditions array directly.
	 *
	 * The groups are ORed and the rules inside each group are ANDed, which is
	 * what check() does per rule post -- this is the same logic without the
	 * assumption that the conditions came from post meta. A caller storing
	 * rules in its own table, an option, or anywhere else uses this.
	 *
	 * An empty set does not match. A rule with no conditions is not a rule
	 * that applies to everything; it is a rule nobody has finished writing,
	 * and treating it as universal is how a half-built row starts matching
	 * every request.
	 *
	 * @param array $conditions Groups of rules, as stored by the admin UI.
	 *
	 * @return bool
	 */
	public function matches( array $conditions ): bool {
		return null !== $this->first_matching_group( $conditions );
	}

	/**
	 * The first group that matches, or null when none do.
	 *
	 * Returning the group rather than a boolean is what lets a caller log
	 * which conditions actually fired -- a verdict that cannot say why it
	 * fired is not much use in a log.
	 *
	 * @param array $conditions Groups of rules.
	 *
	 * @return array|null
	 */
	public function first_matching_group( array $conditions ): ?array {
		foreach ( $conditions as $group ) {
			if ( is_array( $group ) && $this->check_group( $group ) ) {
				return $group;
			}
		}

		return null;
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

			// OR logic between groups.
			$group = $this->first_matching_group( $conditions );

			if ( null !== $group ) {
				return new MatchResult( true, $rule_post, $group );
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

			$group = $this->first_matching_group( $conditions );

			if ( null !== $group ) {
				$matches[] = new MatchResult( true, $rule_post, $group );
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

		$evaluated = 0;

		foreach ( $rules as $rule ) {
			$result = $this->check_rule( $rule );

			// null = couldn't evaluate (missing args), skip
			// false = evaluated and failed
			// true = evaluated and passed
			if ( $result === false ) {
				return false;
			}

			if ( null !== $result ) {
				++$evaluated;
			}
		}

		// Nothing in the group could be evaluated, so the group has not been
		// satisfied -- it has not been tested. Returning true here made a rule
		// match everything the moment its conditions stopped resolving: a
		// renamed condition id, a provider without an API key, a context
		// missing an argument. A group with no rules at all already returns
		// false; this makes a group with no *evaluable* rules agree.
		return $evaluated > 0;
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

		// The unit and number travel to the helper through the arguments --
		// but a copy of them. Writing into $this->args left a later rule
		// inheriting the previous rule's unit, so "orders in 3 hours" followed
		// by a plain number condition read that condition in hours too.
		$args = $this->args;
		$type = $condition['type'] ?? 'text';

		if ( 'number_unit' === $type && is_array( $user_value ) ) {
			$args['_unit']   = $user_value['unit'] ?? null;
			$args['_number'] = $user_value['number'] ?? null;
			$user_value      = $user_value['number'] ?? null;
		}

		if ( 'text_unit' === $type && is_array( $user_value ) ) {
			$args['_unit'] = $user_value['unit'] ?? null;
			$args['_text'] = $user_value['text'] ?? null;
			$user_value    = $user_value['text'] ?? null;
		}

		// Get compare value (passing user_value for conditions that need it)
		$compare_value = $this->get_compare_value( $condition, $user_value, $args );

		// A condition may reshape the rule's value before it is compared. The
		// key:value conditions need this: the helper reads the key half to
		// know what to fetch, and the comparator wants the value half. Without
		// it the observed value was compared against the whole "key:value"
		// string, and six conditions could never match.
		if ( isset( $condition['user_value'] ) && is_callable( $condition['user_value'] ) ) {
			$user_value = call_user_func( $condition['user_value'], $user_value, $args );
		}

		// Perform comparison
		return $this->compare( $condition, $operator, $user_value, $compare_value );
	}

	/**
	 * Get the compare value for a condition.
	 *
	 * @param array $condition  The condition configuration.
	 * @param mixed $user_value The value configured by the user in the admin UI.
	 * @param array $args       The evaluation context, with any unit for this rule.
	 *
	 * @return mixed
	 */
	private function get_compare_value( array $condition, mixed $user_value, array $args ): mixed {
		// If condition has an instance (class-based), use its method
		if ( isset( $condition['instance'] ) && $condition['instance'] instanceof Condition ) {
			return $condition['instance']->get_compare_value( $args, $user_value );
		}

		// If there's a compare_value callback
		if ( isset( $condition['compare_value'] ) && is_callable( $condition['compare_value'] ) ) {
			return call_user_func( $condition['compare_value'], $args, $user_value );
		}

		// Simple arg reference
		if ( isset( $condition['arg'] ) ) {
			return $args[ $condition['arg'] ] ?? null;
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

		// Use the comparator. compare_as lets a condition render one way and
		// compare another: a "meta_key:5" rule needs a text input and a
		// numeric comparison.
		$type     = $condition['compare_as'] ?? $condition['type'] ?? 'text';
		$multiple = $condition['multiple'] ?? false;

		$comparator = new Comparators\Comparator( $type, $multiple );

		return $comparator->compare( $operator, $user_value, $compare_value );
	}

	/**
	 * Get rule posts for this condition set.
	 *
	 * Merges user-provided query arguments with defaults. The post_type
	 * is always forced to the set_id and cannot be overridden.
	 *
	 * @return WP_Post[]
	 */
	private function get_rules(): array {
		$args = wp_parse_args( $this->query_args, self::DEFAULT_QUERY_ARGS );

		// Force post_type to the set_id (cannot be overridden)
		$args['post_type'] = $this->set_id;

		return get_posts( $args );
	}
}
