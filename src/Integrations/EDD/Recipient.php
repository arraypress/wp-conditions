<?php
/**
 * EDD Recipient Helper
 *
 * Provides recipient-related utilities for EDD conditions.
 *
 * @package     ArrayPress\Conditions\Helpers\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\EDD;

use ArrayPress\Conditions\Helpers\DateTime;
use ArrayPress\Conditions\Helpers\Parse;

/**
 * Class Recipient
 *
 * Recipient utilities for EDD conditions.
 */
class Recipient {

	/**
	 * Get user ID from args or current user.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_user_id( array $args ): int {
		return (int) ( $args['user_id'] ?? get_current_user_id() );
	}

	/** -------------------------------------------------------------------------
	 * Earnings Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get total earnings (paid + unpaid).
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_total_earnings( array $args ): float {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
			return 0.0;
		}

		$paid   = (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
		$unpaid = (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );

		return $paid + $unpaid;
	}

	/**
	 * Get paid earnings.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_paid_earnings( array $args ): float {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_get_paid_totals' ) ) {
			return 0.0;
		}

		return (float) ( eddc_get_paid_totals( $user_id ) ?? 0 );
	}

	/**
	 * Get unpaid earnings.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_unpaid_earnings( array $args ): float {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_get_unpaid_totals' ) ) {
			return 0.0;
		}

		return (float) ( eddc_get_unpaid_totals( $user_id ) ?? 0 );
	}

	/** -------------------------------------------------------------------------
	 * Commission Count Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get total sales count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_total_sales( array $args ): int {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
			return 0;
		}

		return (int) eddc_count_user_commissions( $user_id );
	}

	/**
	 * Get unpaid commission count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_unpaid_count( array $args ): int {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
			return 0;
		}

		return (int) eddc_count_user_commissions( $user_id, 'unpaid' );
	}

	/**
	 * Get paid commission count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_paid_count( array $args ): int {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
			return 0;
		}

		return (int) eddc_count_user_commissions( $user_id, 'paid' );
	}

	/**
	 * Get revoked commission count.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_revoked_count( array $args ): int {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_count_user_commissions' ) ) {
			return 0;
		}

		return (int) eddc_count_user_commissions( $user_id, 'revoked' );
	}

	/** -------------------------------------------------------------------------
	 * Profile Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get account age in specified unit.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int
	 */
	public static function get_account_age( array $args ): int {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id ) {
			return 0;
		}

		$user = get_userdata( $user_id );

		if ( ! $user || empty( $user->user_registered ) ) {
			return 0;
		}

		$parsed = Parse::number_unit( $args );

		return DateTime::get_age( $user->user_registered, $parsed['unit'] );
	}

	/**
	 * Check if user is a vendor.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return bool
	 */
	public static function is_vendor( array $args ): bool {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id ) {
			return false;
		}

		// Check if user has any commissions
		if ( function_exists( 'eddc_count_user_commissions' ) ) {
			return eddc_count_user_commissions( $user_id ) > 0;
		}

		// Fallback: check user meta for vendor rate
		$rate = get_user_meta( $user_id, 'eddc_user_rate', true );

		return $rate !== '';
	}

	/**
	 * Get effective commission rate for user.
	 *
	 * Uses eddc_get_recipient_rate() which checks user rate, then falls back to global.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return float
	 */
	public static function get_commission_rate( array $args ): float {
		$user_id = self::get_user_id( $args );

		if ( ! $user_id || ! function_exists( 'eddc_get_recipient_rate' ) ) {
			return 0.0;
		}

		return (float) eddc_get_recipient_rate( $user_id );
	}

}