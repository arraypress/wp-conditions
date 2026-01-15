<?php
/**
 * Match Result
 *
 * Represents the result of a condition evaluation.
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
 * Class MatchResult
 *
 * Contains the result of matching conditions.
 */
class MatchResult {

	/**
	 * Whether a match was found.
	 *
	 * @var bool
	 */
	private bool $matched;

	/**
	 * The matched rule post.
	 *
	 * @var WP_Post|null
	 */
	private ?WP_Post $rule;

	/**
	 * The matched condition group.
	 *
	 * @var array|null
	 */
	private ?array $group;

	/**
	 * Constructor.
	 *
	 * @param bool         $matched Whether a match was found.
	 * @param WP_Post|null $rule    The matched rule post.
	 * @param array|null   $group   The matched condition group.
	 */
	public function __construct( bool $matched, ?WP_Post $rule = null, ?array $group = null ) {
		$this->matched = $matched;
		$this->rule    = $rule;
		$this->group   = $group;
	}

	/**
	 * Check if conditions matched.
	 *
	 * @return bool
	 */
	public function matched(): bool {
		return $this->matched;
	}

	/**
	 * Get the matched rule post.
	 *
	 * @return WP_Post|null
	 */
	public function get_rule(): ?WP_Post {
		return $this->rule;
	}

	/**
	 * Get the matched rule ID.
	 *
	 * @return int|null
	 */
	public function get_rule_id(): ?int {
		return $this->rule?->ID;
	}

	/**
	 * Get the matched rule title.
	 *
	 * @return string|null
	 */
	public function get_rule_title(): ?string {
		return $this->rule?->post_title;
	}

	/**
	 * Get the matched condition group.
	 *
	 * @return array|null
	 */
	public function get_matched_group(): ?array {
		return $this->group;
	}

	/**
	 * Get post meta from the matched rule.
	 *
	 * @param string $key    Meta key.
	 * @param bool   $single Whether to return a single value.
	 *
	 * @return mixed
	 */
	public function get_rule_meta( string $key, bool $single = true ): mixed {
		if ( ! $this->rule ) {
			return null;
		}

		return get_post_meta( $this->rule->ID, $key, $single );
	}

}
