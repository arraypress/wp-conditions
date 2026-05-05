<?php
/**
 * Email Helper
 *
 * Wraps `arraypress/email-utils` to provide condition-friendly accessors
 * for fraud-relevant email signals (freemail providers, TLDs, suspicious
 * local-part patterns).
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

use ArrayPress\EmailUtils\Email as EmailObject;
use Throwable;

/**
 * Class Email
 *
 * Utilities for email-pattern conditions.
 */
class Email {

	/**
	 * Build an Email value object, returning null on invalid input.
	 *
	 * @param array $args Condition args.
	 *
	 * @return EmailObject|null
	 */
	private static function object( array $args ): ?EmailObject {
		$email = trim( (string) ( $args['email'] ?? '' ) );
		if ( $email === '' ) {
			return null;
		}

		try {
			return new EmailObject( $email );
		} catch ( Throwable $e ) {
			return null;
		}
	}

	/**
	 * Whether the email is from a free / common provider (gmail, yahoo, etc).
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function is_freemail( array $args ): bool {
		$email = self::object( $args );

		return $email !== null && $email->is_common_provider();
	}

	/**
	 * Get the email's domain (e.g. `gmail.com`).
	 *
	 * @param array $args Condition args.
	 *
	 * @return string
	 */
	public static function get_domain( array $args ): string {
		$email = self::object( $args );

		return $email !== null ? $email->domain() : '';
	}

	/**
	 * Get the email's TLD (e.g. `com`, `co.uk`).
	 *
	 * @param array $args Condition args.
	 *
	 * @return string
	 */
	public static function get_tld( array $args ): string {
		$email = self::object( $args );

		return $email !== null ? $email->tld() : '';
	}

	/**
	 * Get the length of the local-part (before the `@`).
	 *
	 * Auto-generated fraud emails skew long.
	 *
	 * @param array $args Condition args.
	 *
	 * @return int
	 */
	public static function get_local_length( array $args ): int {
		$email = self::object( $args );

		return $email !== null ? strlen( $email->local() ) : 0;
	}

	/**
	 * Whether the email uses plus-aliasing (`foo+tag@gmail.com`).
	 *
	 * Common evasion technique on Gmail.
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function has_plus_alias( array $args ): bool {
		$email = self::object( $args );

		return $email !== null && $email->is_subaddressed();
	}

	/**
	 * Get the digit-density of the local-part (0.0 - 1.0).
	 *
	 * High density (e.g. >0.5) often indicates an auto-generated fraud email.
	 *
	 * @param array $args Condition args.
	 *
	 * @return float
	 */
	public static function get_local_digit_density( array $args ): float {
		$email = self::object( $args );
		if ( $email === null ) {
			return 0.0;
		}

		$local = $email->local();
		$len   = strlen( $local );
		if ( $len === 0 ) {
			return 0.0;
		}

		$digits = strlen( preg_replace( '/\D/', '', $local ) ?? '' );

		return round( $digits / $len, 3 );
	}

	/**
	 * Compute a 0-100 suspicion score for the local-part of the email.
	 *
	 * Combines several individual signals into a single tunable threshold,
	 * so a merchant can write `score >= 60 → review` rather than building
	 * a multi-condition AND group out of length / digit-density / aliases /
	 * specials. Each signal contributes weighted points up to a 100 cap.
	 *
	 * Signals + weights:
	 *
	 *   Digit density            up to 35 pts (linear: density × 35)
	 *   Length over 16 chars     up to 25 pts (linear over 16-32 char range)
	 *   Has plus alias           20 pts flat
	 *   Multiple dots in local   10 pts (1 dot = 0, 2+ = 10)
	 *   Excessive specials       10 pts (consecutive _- or 3+ specials total)
	 *
	 * Capped at 100. Returns 0 for invalid input.
	 *
	 * Sensible thresholds:
	 *   < 30  — almost certainly legit
	 *   30-59 — borderline; pair with another signal in a rule
	 *   60-79 — strong suspicion; good for review rules
	 *   80+   — auto-generated almost certainly; good for block rules
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Condition args.
	 *
	 * @return int 0-100 score.
	 */
	public static function get_local_suspicion_score( array $args ): int {
		$email = self::object( $args );
		if ( $email === null ) {
			return 0;
		}

		$local = $email->local();
		$len   = strlen( $local );
		if ( $len === 0 ) {
			return 0;
		}

		$score = 0.0;

		// Digit density (0-35).
		$digits  = strlen( preg_replace( '/\D/', '', $local ) ?? '' );
		$density = $digits / $len;
		$score   += min( 35.0, $density * 35.0 );

		// Length penalty (0-25, kicks in over 16 chars, maxed at 32).
		if ( $len > 16 ) {
			$over   = min( 16, $len - 16 );
			$score += ( $over / 16 ) * 25.0;
		}

		// Plus alias (flat 20).
		if ( $email->is_subaddressed() ) {
			$score += 20.0;
		}

		// Multiple dots in local-part (10).
		if ( substr_count( $local, '.' ) >= 2 ) {
			$score += 10.0;
		}

		// Excessive specials — 3+ specials, or any consecutive doubles.
		$specials = preg_match_all( '/[._\-+]/', $local );
		if ( $specials >= 3 || preg_match( '/[._\-+]{2,}/', $local ) ) {
			$score += 10.0;
		}

		return (int) min( 100, round( $score ) );
	}

}
