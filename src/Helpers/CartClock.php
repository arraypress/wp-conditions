<?php
/**
 * Cart Clock
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
 * Class CartClock
 *
 * When the cart was started, for a checkout-speed rule.
 *
 * A bot fills a cart and pays in seconds; a person takes minutes. Neither
 * shop records when a cart began, so this notes the moment the first item
 * goes in -- in the shop's own session, which already follows the visitor
 * through checkout -- and answers with the seconds since. A host that knows
 * better passes `cart_started_at` in the arguments and is believed first.
 */
final class CartClock {

	/**
	 * The session key the start time is kept under.
	 */
	private const KEY = 'wp_conditions_cart_started';

	/**
	 * Whether the hooks are attached.
	 *
	 * @var bool
	 */
	private static bool $hooked = false;

	/**
	 * Attach to both shops' add-to-cart actions.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( self::$hooked || ! function_exists( 'add_action' ) ) {
			return;
		}

		self::$hooked = true;

		add_action( 'woocommerce_add_to_cart', [ self::class, 'start_woocommerce' ] );
		add_action( 'woocommerce_cart_emptied', [ self::class, 'clear_woocommerce' ] );
		add_action( 'edd_post_add_to_cart', [ self::class, 'start_edd' ] );
		add_action( 'edd_empty_cart', [ self::class, 'clear_edd' ] );
	}

	/**
	 * Note the start of a WooCommerce cart, once.
	 *
	 * @return void
	 */
	public static function start_woocommerce(): void {
		$session = self::woocommerce_session();

		if ( $session && ! $session->get( self::KEY ) ) {
			$session->set( self::KEY, time() );
		}
	}

	/**
	 * Forget a WooCommerce cart's start.
	 *
	 * @return void
	 */
	public static function clear_woocommerce(): void {
		self::woocommerce_session()?->set( self::KEY, null );
	}

	/**
	 * Note the start of an EDD cart, once.
	 *
	 * @return void
	 */
	public static function start_edd(): void {
		$session = self::edd_session();

		if ( $session && ! $session->get( self::KEY ) ) {
			$session->set( self::KEY, time() );
		}
	}

	/**
	 * Forget an EDD cart's start.
	 *
	 * @return void
	 */
	public static function clear_edd(): void {
		self::edd_session()?->set( self::KEY, null );
	}

	/**
	 * When the cart was started, as a timestamp.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int|null Null when nothing recorded it.
	 */
	public static function started_at( array $args = [] ): ?int {
		if ( isset( $args['cart_started_at'] ) ) {
			$started = (int) $args['cart_started_at'];

			return $started > 0 ? $started : null;
		}

		$started = (int) ( self::woocommerce_session()?->get( self::KEY ) ?: 0 );

		if ( ! $started ) {
			$started = (int) ( self::edd_session()?->get( self::KEY ) ?: 0 );
		}

		return $started > 0 ? $started : null;
	}

	/**
	 * Seconds since the cart was started.
	 *
	 * @param array $args The condition arguments.
	 *
	 * @return int|null Null when nothing recorded the start.
	 */
	public static function seconds_since_start( array $args = [] ): ?int {
		$started = self::started_at( $args );

		return null === $started ? null : max( 0, time() - $started );
	}

	/**
	 * The WooCommerce session, when there is one.
	 *
	 * @return object|null
	 */
	private static function woocommerce_session(): ?object {
		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		$session = WC()->session ?? null;

		return is_object( $session ) && method_exists( $session, 'get' ) && method_exists( $session, 'set' ) ? $session : null;
	}

	/**
	 * The EDD session, when there is one.
	 *
	 * @return object|null
	 */
	private static function edd_session(): ?object {
		if ( ! function_exists( 'EDD' ) ) {
			return null;
		}

		$session = EDD()->session ?? null;

		return is_object( $session ) && method_exists( $session, 'get' ) && method_exists( $session, 'set' ) ? $session : null;
	}
}
