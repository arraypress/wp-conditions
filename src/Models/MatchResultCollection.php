<?php
/**
 * Match Result Collection
 *
 * Collection of match results from checking all rules.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Models;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class MatchResultCollection
 *
 * Collection of MatchResult objects.
 */
class MatchResultCollection implements Countable, IteratorAggregate {

	/**
	 * Array of match results.
	 *
	 * @var MatchResult[]
	 */
	private array $results;

	/**
	 * Constructor.
	 *
	 * @param MatchResult[] $results Array of match results.
	 */
	public function __construct( array $results = [] ) {
		$this->results = $results;
	}

	/**
	 * Check if any matches were found.
	 *
	 * @return bool
	 */
	public function has_matches(): bool {
		return ! empty( $this->results );
	}

	/**
	 * Get the count of matches.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->results );
	}

	/**
	 * Get all match results.
	 *
	 * @return MatchResult[]
	 */
	public function get_all(): array {
		return $this->results;
	}

	/**
	 * Get the first match result.
	 *
	 * @return MatchResult|null
	 */
	public function get_first(): ?MatchResult {
		return $this->results[0] ?? null;
	}

	/**
	 * Get the last match result.
	 *
	 * @return MatchResult|null
	 */
	public function get_last(): ?MatchResult {
		if ( empty( $this->results ) ) {
			return null;
		}

		return $this->results[ count( $this->results ) - 1 ];
	}

	/**
	 * Get all matched rule IDs.
	 *
	 * @return int[]
	 */
	public function get_rule_ids(): array {
		return array_filter(
			array_map(
				fn( MatchResult $result ) => $result->get_rule_id(),
				$this->results
			)
		);
	}

	/**
	 * Get all matched rule titles.
	 *
	 * @return string[]
	 */
	public function get_rule_titles(): array {
		return array_filter(
			array_map(
				fn( MatchResult $result ) => $result->get_rule_title(),
				$this->results
			)
		);
	}

	/**
	 * Get all matched rule posts.
	 *
	 * @return \WP_Post[]
	 */
	public function get_rules(): array {
		return array_filter(
			array_map(
				fn( MatchResult $result ) => $result->get_rule(),
				$this->results
			)
		);
	}

	/**
	 * Get iterator for foreach.
	 *
	 * @return Traversable
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->results );
	}

	/**
	 * Check if collection is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->results );
	}

	/**
	 * Filter results by a callback.
	 *
	 * @param callable $callback Filter callback.
	 *
	 * @return self
	 */
	public function filter( callable $callback ): self {
		return new self( array_filter( $this->results, $callback ) );
	}

	/**
	 * Map results through a callback.
	 *
	 * @param callable $callback Map callback.
	 *
	 * @return array
	 */
	public function map( callable $callback ): array {
		return array_map( $callback, $this->results );
	}

}
