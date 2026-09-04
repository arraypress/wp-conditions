<?php
/**
 * Address and Name Heuristics
 *
 * @package     ArrayPress\Conditions\Helpers
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.1.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Helpers;

/**
 * Class Address
 *
 * The cheap checks every fraud tool runs on a billing form: is the address
 * a box rather than a door, does it have a number, do the names look typed
 * by a person, does the name appear in the email. None is proof of anything;
 * each is a signal to pair with another.
 */
class Address {

	/**
	 * Whether an address line is a post office box.
	 *
	 * @param string $address The address line.
	 *
	 * @return bool
	 */
	public static function is_po_box( string $address ): bool {
		$pattern = '/(?:^|[\s,.#-])(?:p\.?\s*o\.?\s*box|post\s*office\s*box|pobox|postbus|bo[iî]te\s*postale|postfach|apartado|caixa\s*postal)(?:$|[\s,.#-])/iu';

		return 1 === preg_match( $pattern, ' ' . $address . ' ' );
	}

	/**
	 * Whether an address line carries a street number.
	 *
	 * @param string $address The address line.
	 *
	 * @return bool
	 */
	public static function has_street_number( string $address ): bool {
		return 1 === preg_match( '/\p{N}/u', $address );
	}

	/**
	 * Whether the first and last names are the same word.
	 *
	 * "John John" is what a form filler produces, not a person.
	 *
	 * @param string $first The first name.
	 * @param string $last  The last name.
	 *
	 * @return bool
	 */
	public static function names_identical( string $first, string $last ): bool {
		$first = self::letters( $first );
		$last  = self::letters( $last );

		return '' !== $first && $first === $last;
	}

	/**
	 * Whether the name appears in the email's local part.
	 *
	 * A real person's email tends to carry their name -- "jane.doe",
	 * "jdoe", "doej" -- and a generated one does not. Three letters of
	 * either name, or the first initial followed by the last name, count.
	 *
	 * @param string $first The first name.
	 * @param string $last  The last name.
	 * @param string $email The email address.
	 *
	 * @return bool
	 */
	public static function name_matches_email( string $first, string $last, string $email ): bool {
		$local = self::letters( (string) ( strstr( $email, '@', true ) ?: '' ) );
		$first = self::letters( $first );
		$last  = self::letters( $last );

		if ( '' === $local ) {
			return false;
		}

		foreach ( [ $first, $last ] as $name ) {
			if ( mb_strlen( $name ) >= 3 && str_contains( $local, $name ) ) {
				return true;
			}
		}

		if ( '' !== $first && mb_strlen( $last ) >= 3 && str_contains( $local, mb_substr( $first, 0, 1 ) . $last ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Lower-case letters and digits only.
	 *
	 * @param string $value The value.
	 *
	 * @return string
	 */
	private static function letters( string $value ): string {
		return (string) preg_replace( '/[^\p{L}\p{N}]+/u', '', mb_strtolower( $value ) );
	}
}
