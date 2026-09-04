<?php
/**
 * Test bootstrap.
 *
 * The comparison layer and the matcher's group logic decide whether a rule
 * fires, and neither needs WordPress to do it. The few WordPress functions
 * they touch are stubbed so the logic can be tested for what it is.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		return $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = [] ) {
		return array_merge( $defaults, (array) $args );
	}
}

/**
 * WordPress time constants.
 *
 * The library reads these directly; without them a helper that divides by
 * DAY_IN_SECONDS raises rather than returning an age.
 */
foreach ( [
	'MINUTE_IN_SECONDS' => 60,
	'HOUR_IN_SECONDS'   => 3600,
	'DAY_IN_SECONDS'    => 86400,
	'WEEK_IN_SECONDS'   => 604800,
	'MONTH_IN_SECONDS'  => 2592000,
	'YEAR_IN_SECONDS'   => 31536000,
] as $wpc_constant => $wpc_value ) {
	if ( ! defined( $wpc_constant ) ) {
		define( $wpc_constant, $wpc_value );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = null ) {
		return $text;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type = 'timestamp', $gmt = 0 ) {
		return 'timestamp' === $type ? time() : gmdate( 'Y-m-d H:i:s' );
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return 0;
	}
}

if ( ! function_exists( 'get_userdata' ) ) {
	function get_userdata( $user_id ) {
		return false;
	}
}

if ( ! function_exists( 'is_super_admin' ) ) {
	function is_super_admin( $user_id = false ) {
		return false;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $key ) {
		return false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $key, $value, $expiration = 0 ) {
		return true;
	}
}

if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( $post, $taxonomy ) {
		return false;
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	/**
	 * Records what it was asked and answers with whatever a test staged.
	 */
	function get_posts( $args = [] ) {
		$GLOBALS['wpc_last_query'] = $args;

		return $GLOBALS['wpc_posts'] ?? [];
	}
}

if ( ! function_exists( 'get_post_field' ) ) {
	function get_post_field( $field, $post = null, $context = 'display' ) {
		return '';
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $title, $fallback = '', $context = 'save' ) {
		$title = strtolower( preg_replace( '/[^A-Za-z0-9_-]+/', '-', (string) $title ) );

		return trim( $title, '-' );
	}
}

if ( ! function_exists( 'get_post_stati' ) ) {
	function get_post_stati( $args = [], $output = 'names', $operator = 'and' ) {
		$statuses = [];

		foreach ( [
			'publish' => 'Published',
			'draft' => 'Draft',
			'private' => 'Private',
		] as $name => $label ) {
			$statuses[ $name ] = (object) [
				'name'  => $name,
				'label' => $label,
			];
		}

		return 'objects' === $output ? $statuses : array_keys( $statuses );
	}
}

if ( ! function_exists( 'wp_roles' ) ) {
	/**
	 * Minimal stand-in for the roles registry.
	 */
	class WPC_Test_Roles {
		public function get_names(): array {
			return [
				'administrator' => 'Administrator',
				'customer'      => 'Customer',
				'subscriber'    => 'Subscriber',
			];
		}
	}

	function wp_roles() {
		return new WPC_Test_Roles();
	}
}

if ( ! function_exists( 'get_the_ID' ) ) {
	function get_the_ID() {
		return false;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post = null, $output = 'OBJECT', $filter = 'raw' ) {
		return null;
	}
}

if ( ! function_exists( 'get_the_date' ) ) {
	function get_the_date( $format = '', $post = null ) {
		return '';
	}
}

if ( ! function_exists( 'get_the_modified_date' ) ) {
	function get_the_modified_date( $format = '', $post = null ) {
		return '';
	}
}

if ( ! function_exists( 'get_user_locale' ) ) {
	function get_user_locale( $user = 0 ) {
		return 'en_US';
	}
}

if ( ! function_exists( 'get_locale' ) ) {
	function get_locale() {
		return 'en_US';
	}
}

/*
 * Enough of WordPress for a set to register and a rule to be saved. The
 * sanitizers are close to core's -- close enough that a regex run through
 * sanitize_text_field() comes out mangled the way it really does.
 */
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str = (string) $str;

		if ( str_contains( $str, '<' ) ) {
			$str = strip_tags( $str );
			$str = str_replace( '<', '&lt;', $str );
		}

		$str = (string) preg_replace( '/[\r\n\t ]+/', ' ', $str );
		$str = (string) preg_replace( '/%[a-f0-9]{2}/i', '', $str );

		return trim( $str );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return is_scalar( $key ) ? strtolower( (string) preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $key ) ) : '';
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_check_invalid_utf8' ) ) {
	function wp_check_invalid_utf8( $text ) {
		return (string) $text;
	}
}

foreach ( [ 'add_action', 'add_filter', 'register_post_type', 'register_post_meta', 'add_meta_box', 'register_rest_route' ] as $wpc_noop ) {
	if ( ! function_exists( $wpc_noop ) ) {
		eval( 'function ' . $wpc_noop . '( ...$args ) { return true; }' ); // phpcs:ignore Squiz.PHP.Eval.Discouraged
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
	}
}

if ( ! function_exists( 'post_type_exists' ) ) {
	function post_type_exists( $post_type ) {
		return false;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $post_id, $key = '', $single = false ) {
		return $GLOBALS['wpc_post_meta'][ $post_id ][ $key ] ?? ( $single ? '' : [] );
	}
}

require_once __DIR__ . '/wc-stubs.php';

require_once __DIR__ . '/edd-stubs.php';
