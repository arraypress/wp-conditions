<?php
/**
 * Easy Digital Downloads stand-ins.
 *
 * Same shape as the WooCommerce stubs: inert by default, every getter reading
 * $GLOBALS['wpc_edd'], which starts empty. A test that does not set anything up
 * sees exactly what a site with no store data sees.
 *
 * Unlike the WooCommerce side there is no single class to gate on -- the
 * library checks function_exists( 'EDD' ), so that one function is deliberately
 * NOT declared here. Declaring it would register the EDD conditions in every
 * other test as a side effect.
 *
 * @package ArrayPress\Conditions
 */

declare( strict_types=1 );

namespace EDD\Orders {

	/**
	 * Stands in for the EDD 3 order object.
	 *
	 * Properties are read directly on the real thing (via __get), so the stub
	 * exposes them the same way rather than through getters.
	 */
	class Order extends \WPC_Stub_Object {

		public function __get( string $name ) {
			return $this->data[ $name ] ?? null;
		}

		public function get_number(): string {
			return (string) ( $this->data['order_number'] ?? '' );
		}

		public function get_items(): array {
			return (array) ( $this->data['items'] ?? [] );
		}

		public function get_fees(): array {
			return (array) ( $this->data['fees'] ?? [] );
		}

		public function get_discounts(): array {
			return (array) ( $this->data['discounts'] ?? [] );
		}

		public function get_address() {
			return $this->data['address'] ?? null;
		}

		public function get_transaction_id(): string {
			return (string) ( $this->data['transaction_id'] ?? '' );
		}

		public function has_unlimited_downloads(): bool {
			return (bool) ( $this->data['unlimited_downloads'] ?? false );
		}

		public function is_recoverable(): bool {
			return (bool) ( $this->data['recoverable'] ?? false );
		}

		public function is_complete(): bool {
			return (bool) ( $this->data['complete'] ?? false );
		}
	}
}

namespace EDD {

	/**
	 * Stands in for EDD\Stats. Every figure is zero unless a test says otherwise.
	 */
	class Stats {

		private array $args;

		public function __construct( array $args = [] ) {
			$this->args = $args;
		}

		public function __call( string $method, array $arguments ) {
			$stats = $GLOBALS['wpc_edd']['stats'] ?? [];

			return $stats[ $method ] ?? 0;
		}
	}
}

namespace EDD\Reports {

	/**
	 * The range presets EDD offers in its reports filter.
	 */
	function get_dates_filter_options(): array {
		return [
			'today'      => 'Today',
			'this_week'  => 'This Week',
			'this_month' => 'This Month',
			'last_month' => 'Last Month',
			'this_year'  => 'This Year',
			'other'      => 'Custom',
		];
	}

	/**
	 * Turn a preset into a start and end date.
	 *
	 * @param string $range Range preset.
	 *
	 * @return array
	 */
	function parse_dates_for_range( string $range = '' ): array {
		return [
			'start' => new \DateTime( '@0' ),
			'end'   => new \DateTime( '@0' ),
		];
	}
}

namespace {

	$GLOBALS['wpc_edd'] = [
		'cart'      => [],
		'customer'  => null,
		'orders'    => [],
		'products'  => [],
		'discounts' => [],
		'results'   => [],
	];

	/**
	 * Clear anything a test set up.
	 */
	function wpc_edd_reset(): void {
		$GLOBALS['wpc_edd'] = [
			'cart'      => [],
			'customer'  => null,
			'orders'    => [],
			'products'  => [],
			'discounts' => [],
			'results'   => [],
		];
	}

	/**
	 * Read a key out of the bag.
	 *
	 * @param string $key     Bag key.
	 * @param mixed  $default Value when nothing is set up.
	 *
	 * @return mixed
	 */
	function wpc_edd( string $key, $default = null ) {
		return $GLOBALS['wpc_edd'][ $key ] ?? $default;
	}

	/** ---------------------------------------------------------------------------
	 * Classes
	 * -------------------------------------------------------------------------- */

	/**
	 * Stands in for the EDD customer object.
	 */
	class EDD_Customer extends WPC_Stub_Object {

		public function __get( string $name ) {
			return $this->data[ $name ] ?? null;
		}
	}

	/** -----------------------------------------------------------------------
	 * Setup helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Put an order in place, addressable by ID.
	 */
	function wpc_edd_order( int $id, array $data ): \EDD\Orders\Order {
		$order = new \EDD\Orders\Order( $data + [ 'id' => $id ] );

		$GLOBALS['wpc_edd']['orders'][ $id ] = $order;

		return $order;
	}

	/**
	 * Put a customer in place.
	 */
	function wpc_edd_customer( array $data ): EDD_Customer {
		$customer = new EDD_Customer( $data );

		$GLOBALS['wpc_edd']['customer'] = $customer;

		return $customer;
	}

	/**
	 * Set the cart contents.
	 */
	function wpc_edd_cart( array $items ): void {
		$GLOBALS['wpc_edd']['cart'] = $items;
	}

	/** -----------------------------------------------------------------------
	 * Cart
	 * ---------------------------------------------------------------------- */

	function edd_get_cart_contents() {
		return wpc_edd( 'cart', [] );
	}

	function edd_get_cart_content_details() {
		return wpc_edd( 'cart', [] );
	}

	function edd_get_cart_total() {
		return (float) ( $GLOBALS['wpc_edd']['total'] ?? 0.0 );
	}

	function edd_get_cart_subtotal() {
		return (float) ( $GLOBALS['wpc_edd']['subtotal'] ?? 0.0 );
	}

	function edd_get_cart_tax() {
		return (float) ( $GLOBALS['wpc_edd']['tax'] ?? 0.0 );
	}

	function edd_get_cart_discounted_amount() {
		return (float) ( $GLOBALS['wpc_edd']['discount'] ?? 0.0 );
	}

	function edd_get_cart_fee_total() {
		return (float) ( $GLOBALS['wpc_edd']['fee_total'] ?? 0.0 );
	}

	function edd_get_cart_fees() {
		return (array) ( $GLOBALS['wpc_edd']['fees'] ?? [] );
	}

	function edd_get_cart_quantity() {
		return (int) ( $GLOBALS['wpc_edd']['quantity'] ?? 0 );
	}

	function edd_get_cart_discounts() {
		return (array) ( $GLOBALS['wpc_edd']['cart_discounts'] ?? [] );
	}

	function edd_cart_has_discounts() {
		return ! empty( $GLOBALS['wpc_edd']['cart_discounts'] );
	}

	function edd_get_chosen_gateway() {
		return (string) ( $GLOBALS['wpc_edd']['gateway'] ?? '' );
	}

	/** -----------------------------------------------------------------------
	 * Orders
	 * ---------------------------------------------------------------------- */

	function edd_get_order( $id = 0 ) {
		return $GLOBALS['wpc_edd']['orders'][ (int) $id ] ?? null;
	}

	function edd_get_orders( $args = [] ) {
		return wpc_edd( 'results', [] );
	}

	function edd_count_orders( $args = [] ) {
		return count( (array) wpc_edd( 'results', [] ) );
	}

	function edd_get_order_items( $args = [] ) {
		return [];
	}

	function edd_get_order_adjustments( $args = [] ) {
		return [];
	}

	function edd_get_order_refunds( $order_id = 0 ) {
		return (array) ( $GLOBALS['wpc_edd']['refunds'] ?? [] );
	}

	function edd_get_order_meta( $order_id = 0, $key = '', $single = false ) {
		return $single ? '' : [];
	}

	function edd_get_payment_statuses() {
		return [
			'pending'   => 'Pending',
			'complete'  => 'Completed',
			'refunded'  => 'Refunded',
			'failed'    => 'Failed',
			'abandoned' => 'Abandoned',
		];
	}

	function edd_get_complete_order_statuses() {
		return [ 'complete', 'partially_refunded' ];
	}

	function edd_get_deliverable_order_item_statuses() {
		return [ 'complete', 'partially_refunded' ];
	}

	/** -----------------------------------------------------------------------
	 * Customers
	 * ---------------------------------------------------------------------- */

	function is_user_logged_in() {
		return ! empty( $GLOBALS['wpc_edd']['user_id'] );
	}

	function edd_get_customer( $id = 0 ) {
		return wpc_edd( 'customer' );
	}

	function edd_get_customer_by( $field = '', $value = '' ) {
		return wpc_edd( 'customer' );
	}

	function edd_get_customers( $args = [] ) {
		$customer = wpc_edd( 'customer' );

		return $customer ? [ $customer ] : [];
	}

	/** -----------------------------------------------------------------------
	 * Downloads
	 * ---------------------------------------------------------------------- */

	function edd_get_download( $id = 0 ) {
		return $GLOBALS['wpc_edd']['products'][ (int) $id ] ?? null;
	}

	function edd_get_download_type( $id = 0 ) {
		return (string) ( $GLOBALS['wpc_edd']['download_type'] ?? 'default' );
	}

	function edd_get_download_types() {
		return [
			''        => 'Default',
			'bundle'  => 'Bundle',
			'service' => 'Service',
		];
	}

	function edd_get_download_price( $id = 0 ) {
		return (float) ( $GLOBALS['wpc_edd']['price'] ?? 0.0 );
	}

	function edd_get_download_sku( $id = 0 ) {
		return (string) ( $GLOBALS['wpc_edd']['sku'] ?? '-' );
	}

	function edd_get_download_files( $id = 0, $price_id = null ) {
		return (array) ( $GLOBALS['wpc_edd']['files'] ?? [] );
	}

	function edd_get_download_refund_window( $id = 0 ) {
		return (int) ( $GLOBALS['wpc_edd']['refund_window'] ?? 0 );
	}

	function edd_get_download_sales_stats( $id = 0 ) {
		return (int) ( $GLOBALS['wpc_edd']['sales'] ?? 0 );
	}

	function edd_get_download_earnings_stats( $id = 0 ) {
		return (float) ( $GLOBALS['wpc_edd']['earnings'] ?? 0.0 );
	}

	function edd_get_file_download_limit( $id = 0 ) {
		return (int) ( $GLOBALS['wpc_edd']['download_limit'] ?? 0 );
	}

	function edd_get_product_notes( $id = 0 ) {
		return (string) ( $GLOBALS['wpc_edd']['notes'] ?? '' );
	}

	function edd_is_free_download( $id = 0 ) {
		return (bool) ( $GLOBALS['wpc_edd']['is_free'] ?? false );
	}

	function edd_is_bundled_product( $id = 0 ) {
		return (bool) ( $GLOBALS['wpc_edd']['is_bundle'] ?? false );
	}

	function edd_get_bundled_products( $id = 0 ) {
		return (array) ( $GLOBALS['wpc_edd']['bundled'] ?? [] );
	}

	function edd_has_variable_prices( $id = 0 ) {
		return (bool) ( $GLOBALS['wpc_edd']['variable_prices'] ?? false );
	}

	function edd_get_variable_prices( $id = 0 ) {
		return (array) ( $GLOBALS['wpc_edd']['prices'] ?? [] );
	}

	function edd_get_lowest_price_option( $id = 0 ) {
		return (float) ( $GLOBALS['wpc_edd']['lowest_price'] ?? 0.0 );
	}

	function edd_get_highest_price_option( $id = 0 ) {
		return (float) ( $GLOBALS['wpc_edd']['highest_price'] ?? 0.0 );
	}

	/** -----------------------------------------------------------------------
	 * Discounts
	 * ---------------------------------------------------------------------- */

	function edd_get_discounts( $args = [] ) {
		return (array) wpc_edd( 'discounts', [] );
	}

	function edd_get_discount_by_code( $code = '' ) {
		return false;
	}

	/** -----------------------------------------------------------------------
	 * Store settings
	 * ---------------------------------------------------------------------- */

	function edd_get_currency() {
		return (string) ( $GLOBALS['wpc_edd']['currency'] ?? '' );
	}

	function edd_get_currencies() {
		return [
			'GBP' => 'Pound Sterling (&pound;)',
			'USD' => 'US Dollars (&#36;)',
			'EUR' => 'Euros (&euro;)',
		];
	}

	function edd_get_country_list() {
		return [
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'IE' => 'Ireland',
		];
	}

	function edd_get_shop_country() {
		return (string) ( $GLOBALS['wpc_edd']['base_country'] ?? '' );
	}

	function edd_get_payment_gateways() {
		return [
			'stripe' => [
				'admin_label'    => 'Stripe',
				'checkout_label' => 'Credit Card',
			],
			'paypal' => [
				'admin_label'    => 'PayPal',
				'checkout_label' => 'PayPal',
			],
		];
	}

	function edd_use_taxes() {
		return (bool) ( $GLOBALS['wpc_edd']['taxes_enabled'] ?? false );
	}

	function edd_is_test_mode() {
		return (bool) ( $GLOBALS['wpc_edd']['test_mode'] ?? false );
	}

	function edd_no_guest_checkout() {
		return (bool) ( $GLOBALS['wpc_edd']['no_guest_checkout'] ?? false );
	}

	function edd_item_quantities_enabled() {
		return (bool) ( $GLOBALS['wpc_edd']['item_quantities'] ?? false );
	}
}
