<?php
/**
 * WooCommerce stand-ins.
 *
 * Inert by default. Every getter here answers from $GLOBALS['wpc_wc'], which
 * starts empty, so a test that does not set anything up sees exactly what a
 * site with no WooCommerce sees -- which is what the registry test asserts on.
 *
 * A test that wants a cart, a product or an order calls wpc_stub_*() to put one
 * there and wpc_stub_reset() to take it away again.
 *
 * The WooCommerce class itself is deliberately never declared: the library gates
 * the whole integration on class_exists( 'WooCommerce' ), and declaring it here
 * would register the conditions in every other test as a side effect.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

$GLOBALS['wpc_wc'] = [
	'cart'     => null,
	'customer' => null,
	'session'  => [],
	'products' => [],
	'orders'   => [],
	'results'  => [],
];

/**
 * Clear anything a test set up.
 */
function wpc_stub_reset(): void {
	$GLOBALS['wpc_wc'] = [
		'cart'     => null,
		'customer' => null,
		'session'  => [],
		'products' => [],
		'orders'   => [],
		'results'  => [],
	];
}

/**
 * A property bag that answers WooCommerce's getter naming.
 *
 * get_foo() reads 'foo'; is_foo() and has_foo() read it as a boolean. Anything
 * not set answers null, which is what a real product with an empty field does.
 */
class WPC_Stub_Object {

	/**
	 * @var array<string, mixed>
	 */
	protected array $data;

	public function __construct( array $data = [] ) {
		$this->data = $data;
	}

	public function __call( string $method, array $args ) {
		foreach ( [ 'get_', 'is_', 'has_' ] as $prefix ) {
			if ( str_starts_with( $method, $prefix ) ) {
				$key   = substr( $method, strlen( $prefix ) );
				$value = $this->data[ $key ] ?? null;

				return 'get_' === $prefix ? $value : (bool) $value;
			}
		}

		return $this->data[ $method ] ?? null;
	}
}

/**
 * Stands in for WC_DateTime.
 */
class WPC_Stub_Date {

	private int $timestamp;

	public function __construct( int $timestamp ) {
		$this->timestamp = $timestamp;
	}

	public function date( string $format ): string {
		return gmdate( $format, $this->timestamp );
	}

	public function getTimestamp(): int {
		return $this->timestamp;
	}
}

class WC_Cart extends WPC_Stub_Object {}

class WC_Product extends WPC_Stub_Object {}

class WC_Order extends WPC_Stub_Object {}

class WC_Customer extends WPC_Stub_Object {}

class WC_Order_Item_Product extends WPC_Stub_Object {}

class WC_Order_Item_Shipping extends WPC_Stub_Object {}

/**
 * Put a cart in place.
 */
function wpc_stub_cart( array $data ): WC_Cart {
	$cart = new WC_Cart( $data );

	$GLOBALS['wpc_wc']['cart'] = $cart;

	return $cart;
}

/**
 * Put a customer in place.
 */
function wpc_stub_customer( array $data ): WC_Customer {
	$customer = new WC_Customer( $data );

	$GLOBALS['wpc_wc']['customer'] = $customer;

	return $customer;
}

/**
 * Put a product in place, addressable by ID.
 */
function wpc_stub_product( int $id, array $data ): WC_Product {
	$product = new WC_Product( $data + [ 'id' => $id ] );

	$GLOBALS['wpc_wc']['products'][ $id ] = $product;

	return $product;
}

/**
 * Put an order in place, addressable by ID.
 */
function wpc_stub_order( int $id, array $data ): WC_Order {
	$order = new WC_Order( $data + [ 'id' => $id ] );

	$GLOBALS['wpc_wc']['orders'][ $id ] = $order;

	return $order;
}

if ( ! function_exists( 'WC' ) ) {
	function WC() {
		return (object) [
			'cart'     => $GLOBALS['wpc_wc']['cart'],
			'customer' => $GLOBALS['wpc_wc']['customer'],
			'session'  => new WPC_Stub_Session(),
		];
	}
}

/**
 * Stands in for the WooCommerce session.
 */
class WPC_Stub_Session {

	public function get( string $key, $default = null ) {
		return $GLOBALS['wpc_wc']['session'][ $key ] ?? $default;
	}

	public function set( string $key, $value ): void {
		$GLOBALS['wpc_wc']['session'][ $key ] = $value;
	}
}

if ( ! function_exists( 'wc_get_product' ) ) {
	function wc_get_product( $id = false ) {
		return $GLOBALS['wpc_wc']['products'][ (int) $id ] ?? null;
	}
}

if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( $id = false ) {
		return $GLOBALS['wpc_wc']['orders'][ (int) $id ] ?? null;
	}
}

if ( ! function_exists( 'wc_get_orders' ) ) {
	function wc_get_orders( $args = [] ) {
		$results = $GLOBALS['wpc_wc']['results'];

		// A query asking only for IDs gets IDs, the same as the real thing --
		// the count helpers rely on that shape.
		if ( isset( $args['return'] ) && 'ids' === $args['return'] ) {
			return array_map( static fn( $order ) => $order->get_id(), $results );
		}

		return $results;
	}
}

/**
 * Set what the next order query returns.
 *
 * @param array<int, array> $orders Order data, one entry per order.
 */
function wpc_stub_order_results( array $orders ): void {
	$GLOBALS['wpc_wc']['results'] = array_map(
		static fn( array $data, int $i ) => new WC_Order( $data + [ 'id' => $i + 1 ] ),
		$orders,
		array_keys( $orders )
	);
}

/**
 * Lookup tables. These carry no store state, so they are always on -- a
 * condition asking what the order statuses are should get the real list.
 */
if ( ! function_exists( 'wc_get_order_statuses' ) ) {
	function wc_get_order_statuses() {
		return [
			'wc-pending'    => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold'    => 'On hold',
			'wc-completed'  => 'Completed',
			'wc-cancelled'  => 'Cancelled',
			'wc-refunded'   => 'Refunded',
			'wc-failed'     => 'Failed',
		];
	}
}

if ( ! function_exists( 'wc_get_product_types' ) ) {
	function wc_get_product_types() {
		return [
			'simple'   => 'Simple product',
			'grouped'  => 'Grouped product',
			'external' => 'External/Affiliate product',
			'variable' => 'Variable product',
		];
	}
}

if ( ! function_exists( 'wc_get_product_stock_status_options' ) ) {
	function wc_get_product_stock_status_options() {
		return [
			'instock'     => 'In stock',
			'outofstock'  => 'Out of stock',
			'onbackorder' => 'On backorder',
		];
	}
}

if ( ! class_exists( 'WC_Tax' ) ) {
	/**
	 * WooCommerce returns only the *additional* tax classes here. The standard
	 * class is implicit, stored as an empty slug, and never in this list.
	 */
	class WC_Tax {
		public static function get_tax_classes(): array {
			return [ 'Reduced rate', 'Zero rate' ];
		}
	}
}

if ( ! function_exists( 'get_woocommerce_currencies' ) ) {
	function get_woocommerce_currencies() {
		return [
			'GBP' => 'Pound sterling',
			'EUR' => 'Euro',
			'USD' => 'United States dollar',
		];
	}
}
