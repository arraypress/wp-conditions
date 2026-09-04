<?php
/**
 * WooCommerce Customer Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\WooCommerce;

use ArrayPress\Conditions\Integrations\WooCommerce\Customer as CustomerHelper;
use ArrayPress\Conditions\Integrations\WooCommerce\Options;
use ArrayPress\Conditions\Operators;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Options\WordPress;

/**
 * Class Customer
 *
 * Provides WooCommerce customer conditions.
 */
class Customer {

	/**
	 * Get all customer conditions.
	 *
	 * @return array<string, array>
	 *
	 * @since 1.0.0
	 */
	public static function get_all(): array {
		$profile   = __( 'Customer: Profile', 'arraypress' );
		$purchases = __( 'Customer: Purchases', 'arraypress' );
		$location  = __( 'Customer: Location', 'arraypress' );

		return [
			// Profile.
			'wc_customer_email'             => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match the customer\'s billing email using a full address, @domain, .tld, or a substring. Examples: `john@test.com`, `@gmail.com`, `.edu`, `temp`.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_email(),
				'required_args' => [],
			],
			'wc_customer_is_guest'          => [
				'label'         => __( 'Guest Checkout', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer is checking out without an account.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_guest(),
				'required_args' => [],
			],
			'wc_customer_roles'             => [
				'label'         => __( 'Role', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select role...', 'arraypress' ),
				'description'   => __( 'The roles held by the logged-in customer. A guest holds none, so a "none of" rule matches guests as well.', 'arraypress' ),
				'options'       => WordPress::get_roles(),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_roles(),
				'required_args' => [],
			],
			'wc_customer_account_age'       => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the account has existed. A guest reports 0, the same as an account opened today — which is the direction a new-account rule points anyway.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_account_age( $args ),
				'required_args' => [],
			],

			// Purchases.
			'wc_customer_order_count'       => [
				'label'         => __( 'Lifetime Orders', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Paid orders placed under this billing email. Counted by email rather than user ID, so a guest with a history is not treated as brand new.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_order_count(),
				'required_args' => [],
			],
			'wc_customer_is_first_order'    => [
				'label'         => __( 'First Order', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'boolean',
				'description'   => __( 'Whether this would be the customer\'s first paid order. First orders carry more risk than any later one — pair with cart total rather than blocking on their own.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_first_order(),
				'required_args' => [],
			],
			'wc_customer_total_spent'       => [
				'label'         => __( 'Total Spent', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Lifetime spend across paid orders. Useful as an inverse signal — a customer who has spent a lot is rarely worth stopping.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_total_spent(),
				'required_args' => [],
			],
			'wc_customer_avg_order_value'   => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Average value of the customer\'s paid orders.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_average_order_value(),
				'required_args' => [],
			],

			// Location.
			'wc_customer_billing_country'   => [
				'label'         => __( 'Billing Country', 'arraypress' ),
				'group'         => $location,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The customer\'s billing country.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CustomerHelper::get_billing_country(),
				'required_args' => [],
			],
			'wc_customer_shipping_country'  => [
				'label'         => __( 'Shipping Country', 'arraypress' ),
				'group'         => $location,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select country...', 'arraypress' ),
				'description'   => __( 'The customer\'s shipping country.', 'arraypress' ),
				'options'       => Options::get_countries(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => CustomerHelper::get_shipping_country(),
				'required_args' => [],
			],
			'wc_customer_country_mismatch'  => [
				'label'         => __( 'Billing/Shipping Country Mismatch', 'arraypress' ),
				'group'         => $location,
				'type'          => 'boolean',
				'description'   => __( 'Whether the billing and shipping countries differ. A cart with no shipping country — an all-virtual one — never counts as a mismatch.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_country_mismatch(),
				'required_args' => [],
			],

			// Purchases — history and recency.
			'wc_customer_is_paying'         => [
				'label'         => __( 'Paying Customer', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'boolean',
				'description'   => __( 'WooCommerce\'s own flag, kept on the user record. Cheaper than counting orders, but only ever set for logged-in customers — a guest always reads false.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_paying_customer(),
				'required_args' => [],
			],
			'wc_customer_last_order_date'   => [
				'label'         => __( 'Last Order Date', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'date',
				'description'   => __( 'The date of the customer\'s most recent paid order.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_last_order_date(),
				'required_args' => [],
			],
			'wc_customer_days_since_order'  => [
				'label'         => __( 'Days Since Last Order', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Days since the customer last paid for something. A customer who has never ordered counts as infinitely long ago rather than zero — otherwise "less than 7 days" would match every first-time buyer.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_days_since_last_order(),
				'required_args' => [],
			],

			// Profile — contact details.
			'wc_customer_phone'             => [
				'label'         => __( 'Phone', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. +44', 'arraypress' ),
				'description'   => __( 'The customer\'s billing phone.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_phone(),
				'required_args' => [],
			],
			'wc_customer_company'           => [
				'label'         => __( 'Company', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. Acme Ltd', 'arraypress' ),
				'description'   => __( 'The customer\'s billing company.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_company(),
				'required_args' => [],
			],

			// Tax.
			'wc_customer_is_vat_exempt'     => [
				'label'         => __( 'VAT Exempt', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer is exempt from VAT.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_vat_exempt(),
				'required_args' => [],
			],

			// Location — the rest of the address.
			'wc_customer_billing_state'     => [
				'label'         => __( 'Billing State/Region', 'arraypress' ),
				'group'         => $location,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA', 'arraypress' ),
				'description'   => __( 'The customer\'s billing state or region.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_billing_state(),
				'required_args' => [],
			],
			'wc_customer_billing_city'      => [
				'label'         => __( 'Billing City', 'arraypress' ),
				'group'         => $location,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. London', 'arraypress' ),
				'description'   => __( 'The customer\'s billing city.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_billing_city(),
				'required_args' => [],
			],
			'wc_customer_billing_postcode'  => [
				'label'         => __( 'Billing Postcode', 'arraypress' ),
				'group'         => $location,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. SW1A', 'arraypress' ),
				'description'   => __( 'The customer\'s billing postcode.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_billing_postcode(),
				'required_args' => [],
			],
			'wc_customer_shipping_state'    => [
				'label'         => __( 'Shipping State/Region', 'arraypress' ),
				'group'         => $location,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. CA', 'arraypress' ),
				'description'   => __( 'The customer\'s shipping state or region.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_shipping_state(),
				'required_args' => [],
			],
			'wc_customer_shipping_postcode' => [
				'label'         => __( 'Shipping Postcode', 'arraypress' ),
				'group'         => $location,
				'type'          => 'text',
				'placeholder'   => __( 'e.g. SW1A', 'arraypress' ),
				'description'   => __( 'The customer\'s shipping postcode.', 'arraypress' ),
				'operators'     => Operators::text_advanced(),
				'compare_value' => fn( $args ) => CustomerHelper::get_shipping_postcode(),
				'required_args' => [],
			],

			'wc_customer_purchased_products'      => [
				'label'         => __( 'Purchased Products', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'ajax',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Every product the customer has paid for, across their own orders. A variation counts as its parent too.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'ajax'          => fn( ?string $search, ?array $ids ): array => Options::get_product_options( $search, $ids ),
				'compare_value' => fn( $args ) => CustomerHelper::get_purchased_product_ids(),
				'required_args' => [],
			],
			'wc_customer_purchased_categories'          => [
				'label'         => __( 'Purchased Categories', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'term',
				'taxonomy'      => 'product_cat',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Categories of every product the customer has paid for, across their own orders. "Has bought from category X before" is the usual loyalty or cross-sell condition.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_purchased_term_ids( 'product_cat' ),
				'required_args' => [],
			],
			'wc_customer_purchased_tags'                => [
				'label'         => __( 'Purchased Tags', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'term',
				'taxonomy'      => 'product_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Tags of every product the customer has paid for, across their own orders.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_purchased_term_ids( 'product_tag' ),
				'required_args' => [],
			],
			'wc_customer_refund_count'            => [
				'label'         => __( 'Refunded Orders', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of the customer\'s orders have had money refunded, fully or in part.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_count(),
				'required_args' => [],
			],
			'wc_customer_refund_rate'             => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.01,
				'description'   => __( 'The share of the customer\'s orders that were refunded, as a percentage. A serial refunder is a cost, whatever else they are.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_rate(),
				'required_args' => [],
			],
			'wc_customer_has_active_subscription' => [
				'label'         => __( 'Has Active Subscription', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer has a subscription that is currently billing. The usual condition for a subscriber discount, or for exempting subscribers from a first-order rule. Requires WooCommerce Subscriptions; without it the rule does not apply.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_active_subscription(),
				'required_args' => [],
			],
			'wc_customer_active_subscriptions'    => [
				'label'         => __( 'Active Subscriptions', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of the customer\'s subscriptions are currently billing. Requires WooCommerce Subscriptions.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_active_subscription_count(),
				'required_args' => [],
			],
			'wc_customer_purchased_brands'          => [
				'label'         => __( 'Purchased Brands', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'term',
				'taxonomy'      => 'product_brand',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Brands of every product the customer has paid for, across their own orders.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_purchased_term_ids( 'product_brand' ),
				'required_args' => [],
			],
			'wc_customer_ip_count'                => [
				'label'         => __( 'Distinct IPs', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many different IP addresses the customer has ordered from, across their own orders.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_ip_count(),
				'required_args' => [],
			],
			'wc_customer_user_agent_changed'      => [
				'label'         => __( 'Browser Changed', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether this order\'s browser differs from every earlier order\'s. A stolen account is used from a different machine. With no earlier order the rule does not apply.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_user_agent_changed( $args ),
				'required_args' => [],
			],
			'wc_customer_distinct_shipping_addresses' => [
				'label'         => __( 'Distinct Shipping Addresses', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many different shipping addresses the customer has used. A reshipper collects packages at many doors.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_distinct_shipping_address_count(),
				'required_args' => [],
			],
			'wc_customer_distinct_billing_countries' => [
				'label'         => __( 'Distinct Billing Countries', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many different billing countries the customer has used.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_distinct_billing_country_count(),
				'required_args' => [],
			],
			'wc_customer_failed_order_count'      => [
				'label'         => __( 'Failed Orders', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of the customer\'s orders failed at payment.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_failed_order_count(),
				'required_args' => [],
			],
			'wc_customer_cancelled_order_count'   => [
				'label'         => __( 'Cancelled Orders', 'arraypress' ),
				'group'         => __( 'Customer: Drift', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of the customer\'s orders were cancelled.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_cancelled_order_count(),
				'required_args' => [],
			],
			'wc_customer_has_saved_payment_method' => [
				'label'         => __( 'Has Saved Payment Method', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer has a payment method saved to their account. A guest has none.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_saved_payment_method(),
				'required_args' => [],
			],
			'wc_customer_taxable_country'         => [
				'label'         => __( 'Taxable Country', 'arraypress' ),
				'group'         => $location,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select countries...', 'arraypress' ),
				'description'   => __( 'The country WooCommerce will tax this customer in, by the store\'s tax settings.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => fn() => Options::get_countries(),
				'compare_value' => fn( $args ) => CustomerHelper::get_taxable_country(),
				'required_args' => [],
			],
			'wc_customer_is_outside_base'         => [
				'label'         => __( 'Outside Store Base', 'arraypress' ),
				'group'         => $location,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer\'s taxable address is outside the store\'s base location.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_outside_base(),
				'required_args' => [],
			],
			'wc_customer_has_full_shipping_address' => [
				'label'         => __( 'Has Full Shipping Address', 'arraypress' ),
				'group'         => $location,
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer has a complete shipping address on file.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_full_shipping_address(),
				'required_args' => [],
			],
		];
	}
}
