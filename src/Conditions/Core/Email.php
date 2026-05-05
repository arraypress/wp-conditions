<?php
/**
 * Email Pattern Conditions
 *
 * Generic email-pattern conditions usable for any visitor email — not tied
 * to a particular customer record. Powered by `arraypress/email-utils`.
 *
 * @package     ArrayPress\Conditions\Conditions\Core
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\Core;

use ArrayPress\Conditions\Helpers\Email as EmailHelper;
use ArrayPress\Conditions\Operators;

/**
 * Class Email
 *
 * Provides email-pattern conditions.
 */
class Email {

	/**
	 * Get all email conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$group = __( 'Email', 'arraypress' );

		return [
			'email_is_freemail'           => [
				'label'         => __( 'Is Free Email Provider', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Check if the email is from a free provider (gmail, yahoo, hotmail, etc).', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::is_freemail( $args ),
				'required_args' => [ 'email' ],
			],
			'email_domain'                => [
				'label'         => __( 'Email Domain', 'arraypress' ),
				'group'         => $group,
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. mailinator.com, proton.me', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Match the email domain against a list (e.g. disposable-provider watchlist).', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::get_domain( $args ),
				'required_args' => [ 'email' ],
			],
			'email_has_plus_alias'        => [
				'label'         => __( 'Has Plus Alias', 'arraypress' ),
				'group'         => $group,
				'type'          => 'boolean',
				'description'   => __( 'Check for sub-addressing (foo+tag@example.com). Common evasion technique.', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::has_plus_alias( $args ),
				'required_args' => [ 'email' ],
			],
			'email_local_suspicion_score' => [
				'label'         => __( 'Local Part Suspicion Score', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 60', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'Composite 0-100 score combining digit density, length, plus-alias, dot-stuffing, and excessive specials. Higher = more suspicious. ≥60 → review, ≥80 → likely auto-generated.', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::get_local_suspicion_score( $args ),
				'required_args' => [ 'email' ],
			],

			// Granular signals — kept for power users / consumer-plugin filters.
			'email_tld'                   => [
				'label'         => __( 'Email TLD', 'arraypress' ),
				'group'         => $group,
				'type'          => 'tags',
				'placeholder'   => __( 'e.g. cc, club, xyz, top', 'arraypress' ),
				'operators'     => Operators::tags_exact(),
				'description'   => __( 'Match the email TLD against a list. Some TLDs correlate strongly with abuse.', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::get_tld( $args ),
				'required_args' => [ 'email' ],
			],
			'email_local_length'          => [
				'label'         => __( 'Email Local Part Length', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 20', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Length of the local-part (before the @). Auto-generated fraud emails skew long.', 'arraypress' ),
				'compare_value' => fn( $args ) => EmailHelper::get_local_length( $args ),
				'required_args' => [ 'email' ],
			],
			'email_local_digit_density'   => [
				'label'         => __( 'Local Part Digit Density (%)', 'arraypress' ),
				'group'         => $group,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 1,
				'description'   => __( 'Percentage of digits in the local-part (0–100). High density correlates with auto-generated fraud emails.', 'arraypress' ),
				'compare_value' => fn( $args ) => (int) round( EmailHelper::get_local_digit_density( $args ) * 100 ),
				'required_args' => [ 'email' ],
			],
		];
	}

}
