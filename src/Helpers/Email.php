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
	 * Whether the local-part contains a dot (Gmail's dot-trick).
	 *
	 * On Gmail, `f.o.o@gmail.com` and `foo@gmail.com` deliver to the same inbox.
	 *
	 * @param array $args Condition args.
	 *
	 * @return bool
	 */
	public static function has_dot_aliasing( array $args ): bool {
		$email = self::object( $args );
		if ( $email === null ) {
			return false;
		}

		// Only relevant for Gmail-family domains.
		$domain = strtolower( $email->domain() );
		if ( ! in_array( $domain, [ 'gmail.com', 'googlemail.com' ], true ) ) {
			return false;
		}

		return str_contains( $email->local(), '.' );
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

}
