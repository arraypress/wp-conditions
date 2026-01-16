<?php
/**
 * Base Condition Class
 *
 * Abstract base class for creating custom conditions.
 * Extend this class to create conditions with custom comparison logic.
 *
 * @package     ArrayPress\Conditions
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Abstracts;

use ArrayPress\Conditions\Comparators\Comparator;
use ArrayPress\Conditions\Operators;

/**
 * Class Condition
 *
 * Base class for creating custom conditions.
 */
abstract class Condition {

	/**
	 * Unique identifier for this condition.
	 *
	 * @var string
	 */
	protected string $name = '';

	/**
	 * Display label for the condition.
	 *
	 * @var string
	 */
	protected string $label = '';

	/**
	 * Group for organizing conditions in the dropdown.
	 *
	 * @var string
	 */
	protected string $group = 'General';

	/**
	 * Field type (text, number, select, post, term, user, etc.).
	 *
	 * @var string
	 */
	protected string $type = 'text';

	/**
	 * Whether multiple values can be selected.
	 *
	 * @var bool
	 */
	protected bool $multiple = false;

	/**
	 * Argument key to pull compare value from.
	 *
	 * @var string|null
	 */
	protected ?string $arg = null;

	/**
	 * Required arguments for this condition.
	 *
	 * @var array
	 */
	protected array $required_args = [];

	/**
	 * Post type for 'post' type conditions.
	 *
	 * @var string|null
	 */
	protected ?string $post_type = null;

	/**
	 * Taxonomy for 'term' type conditions.
	 *
	 * @var string|null
	 */
	protected ?string $taxonomy = null;

	/**
	 * Role(s) for 'user' type conditions.
	 *
	 * @var string|array|null
	 */
	protected string|array|null $role = null;

	/**
	 * Get the condition name/identifier.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the display label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Get the group for organizing conditions.
	 *
	 * @return string
	 */
	public function get_group(): string {
		return $this->group;
	}

	/**
	 * Get the field type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Whether this condition supports multiple selection.
	 *
	 * @return bool
	 */
	public function is_multiple(): bool {
		return $this->multiple;
	}

	/**
	 * Get required arguments.
	 *
	 * @return array
	 */
	public function get_required_args(): array {
		return $this->required_args;
	}

	/**
	 * Get the operators available for this condition.
	 *
	 * Override in child class for custom operators.
	 *
	 * @return array<string, string> Array of operator => label.
	 */
	public function get_operators(): array {
		return Operators::for_type( $this->type, $this->multiple );
	}

	/**
	 * Get options for select-type conditions.
	 *
	 * Override in child class to provide options.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public function get_options(): array {
		return [];
	}

	/**
	 * Get units for number_unit type conditions.
	 *
	 * Override in child class to provide units.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public function get_units(): array {
		return [];
	}

	/**
	 * Get the value to compare against.
	 *
	 * Override in child class for computed values.
	 *
	 * @param array $args Arguments passed to the matcher.
	 *
	 * @return mixed
	 */
	public function get_compare_value( array $args ): mixed {
		if ( $this->arg && isset( $args[ $this->arg ] ) ) {
			return $args[ $this->arg ];
		}

		return null;
	}

	/**
	 * Perform the comparison.
	 *
	 * Override in child class for custom comparison logic.
	 *
	 * @param string $operator      The operator.
	 * @param mixed  $user_value    The value configured by the user.
	 * @param mixed  $compare_value The actual value to compare against.
	 *
	 * @return bool
	 */
	public function compare( string $operator, mixed $user_value, mixed $compare_value ): bool {
		$comparator = new Comparator( $this->type, $this->multiple );

		return $comparator->compare( $operator, $user_value, $compare_value );
	}

	/**
	 * Validate that the condition can be evaluated.
	 *
	 * Override for custom validation logic.
	 *
	 * @param array $args Arguments passed to the matcher.
	 *
	 * @return bool
	 */
	public function validate( array $args ): bool {
		foreach ( $this->required_args as $arg ) {
			if ( ! array_key_exists( $arg, $args ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Convert the condition to a configuration array.
	 *
	 * Used by the registry to store condition configuration.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return [
			'name'          => $this->get_name(),
			'label'         => $this->get_label(),
			'group'         => $this->get_group(),
			'type'          => $this->get_type(),
			'multiple'      => $this->is_multiple(),
			'required_args' => $this->get_required_args(),
			'operators'     => $this->get_operators(),
			'options'       => $this->get_options(),
			'units'         => $this->get_units(),
			'post_type'     => $this->post_type,
			'taxonomy'      => $this->taxonomy,
			'role'          => $this->role,
			'arg'           => $this->arg,
			'instance'      => $this,
		];
	}

}
