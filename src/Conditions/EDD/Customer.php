<?php
/**
 * EDD Customer Conditions
 *
 * Sub-grouped under "Customer: Profile / Purchases / Catalogue / Risk"
 * so the rule editor's group dropdown stays scannable rather than
 * dumping nineteen items under a single "Customer" header. Group
 * strings are local variables so reorganising in the future is one
 * edit per section, not nineteen.
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Customer as CustomerHelper;
use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Operators;

/**
 * Class Customer
 *
 * Provides EDD customer-related conditions.
 */
class Customer {

	/**
	 * Get all customer conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$profile   = __( 'Customer: Profile', 'arraypress' );
		$purchases = __( 'Customer: Purchases', 'arraypress' );
		$catalogue = __( 'Customer: Catalogue', 'arraypress' );
		$risk      = __( 'Customer: Risk', 'arraypress' );

		return [
			// Profile — who they are.
			'edd_customer_segment'              => [
				'label'         => __( 'Customer Type', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select segment...', 'arraypress' ),
				'description'   => __( 'Whether the customer is a first-time or returning buyer. First-time buyers are statistically more fraud-prone — combine with cart total, VPN, or other signals to identify high-risk new orders. Returning customers are usually safe but can be tripped by ATO (account takeover).', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'options'       => CustomerHelper::get_segment_options(),
				'compare_value' => fn( $args ) => CustomerHelper::get_segment( $args ),
				'required_args' => [],
			],
			'edd_customer_email'                => [
				'label'         => __( 'Email', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'email',
				'placeholder'   => __( 'e.g. john@test.com, @gmail.com, .edu', 'arraypress' ),
				'description'   => __( 'Match the customer\'s email address using full email, @domain, .tld, or partial domain patterns. Examples: `john@test.com` (exact), `@gmail.com` (any Gmail), `.edu` (any educational TLD), `temp` (substring match).', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_email( $args ),
				'required_args' => [],
			],
			'edd_customer_date_created'         => [
				'label'         => __( 'Date Registered', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'date',
				'description'   => __( 'The date the customer account was created.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_date_created( $args ),
				'required_args' => [],
			],
			'edd_customer_account_age'          => [
				'label'         => __( 'Account Age', 'arraypress' ),
				'group'         => $profile,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long the customer record has existed. Brand-new customers (account age < 1 day) placing high-value orders is a fraud pattern. Established accounts (months/years) trip far less often.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_account_age( $args ),
				'required_args' => [],
			],

			// Purchases — what they've spent / when / how often.
			'edd_customer_order_count'          => [
				'label'         => __( 'Lifetime Orders', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Lifetime order count for this customer. Useful for building "trust by tenure" rules — `Order Count == 0` lets you target first-time buyers specifically. The customer\'s lifetime value is one of the strongest fraud-signal modifiers.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_order_count( $args ),
				'required_args' => [],
			],
			'edd_customer_total_spent'          => [
				'label'         => __( 'Total Spent', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'Lifetime total this customer has spent on the store. High-spend customers are very rarely fraud — useful as an inverse signal ("if total spent > 500, allow"). Pair with `Refund Rate` to catch the rare bad-actor whales.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_total_spent( $args ),
				'required_args' => [],
			],
			'edd_customer_avg_order_value'      => [
				'label'         => __( 'Average Order Value', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 50.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The average value of orders.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_average_order_value( $args ),
				'required_args' => [],
			],
			'edd_customer_last_order_date'      => [
				'label'         => __( 'Last Order Date', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'date',
				'description'   => __( 'The date of the customer\'s most recent order.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_last_order_date( $args ),
				'required_args' => [],
			],
			'edd_customer_days_since_order'     => [
				'label'         => __( 'Days Since Last Order', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'Time since the customer\'s last order.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_days_since_last_order( $args ),
				'required_args' => [],
			],
			'edd_customer_orders_in_period'     => [
				'label'         => __( 'Orders in Period', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Number of orders placed within a date range.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_orders_in_period( $args ),
				'required_args' => [],
			],
			'edd_customer_spend_in_period'      => [
				'label'         => __( 'Spend in Period', 'arraypress' ),
				'group'         => $purchases,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 100.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Amount spent within a date range.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_spend_in_period( $args ),
				'required_args' => [],
			],

			// Catalogue — what they've bought.
			'edd_customer_purchased_products'   => [
				'label'         => __( 'Purchased Products', 'arraypress' ),
				'group'         => $catalogue,
				'type'          => 'post',
				'post_type'     => 'download',
				'multiple'      => true,
				'placeholder'   => __( 'Search products...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased specific products.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_product_ids( $args ),
				'required_args' => [],
			],
			'edd_customer_purchased_categories' => [
				'label'         => __( 'Purchased Categories', 'arraypress' ),
				'group'         => $catalogue,
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased from specific categories.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_term_ids( $args, 'download_category' ),
				'required_args' => [],
			],
			'edd_customer_purchased_tags'       => [
				'label'         => __( 'Purchased Tags', 'arraypress' ),
				'group'         => $catalogue,
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'Check if the customer has purchased products with specific tags.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => CustomerHelper::get_term_ids( $args, 'download_tag' ),
				'required_args' => [],
			],

			// Risk — fraud-specific signals.
			'edd_customer_ip_count'             => [
				'label'         => __( 'Unique IP Count', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many distinct IP addresses this customer has ever ordered from. Most customers shop from 1-3 IPs (home, work, mobile). High counts (≥5) suggest credential sharing or account takeover.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_unique_ip_count( $args ),
				'required_args' => [],
			],
			'edd_customer_ip_country_changed'   => [
				'label'         => __( 'IP Country Changed', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'boolean',
				'description'   => __( 'True when the current checkout\'s IP country differs from every one of the customer\'s 5 most recent orders. Strong signal for account takeover and stolen-card use — most legitimate customers stay in one country across orders even when their raw IP changes (mobile, coffee shops, VPN). Returns false when there\'s no order history to compare against.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_ip_country_changed( $args ),
				'required_args' => [ 'ip_country' ],
			],
			'edd_customer_user_agent_changed'   => [
				'label'         => __( 'User-Agent Changed', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'boolean',
				'description'   => __( 'True when the current checkout\'s User-Agent matches none of the customer\'s 5 most recent orders. Catches "different device" patterns useful for review-tier rules; less actionable for blocking since legitimate users rotate browsers and OS upgrades bump the UA string. Returns false when no past UAs are recorded.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::is_user_agent_changed( $args ),
				'required_args' => [ 'user_agent' ],
			],
			'edd_customer_refund_count'         => [
				'label'         => __( 'Refund Count', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 2', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of this customer\'s past orders have been refunded. High counts can indicate "friendly fraud" — customers who buy, claim non-receipt, get refunded, repeat. Use Refund Rate (%) for a more accurate signal that scales with order count.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_count( $args ),
				'required_args' => [],
			],
			'edd_customer_refund_rate'          => [
				'label'         => __( 'Refund Rate (%)', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10', 'arraypress' ),
				'min'           => 0,
				'max'           => 100,
				'step'          => 0.1,
				'description'   => __( 'Refund rate as a percentage (refunded orders / total orders × 100). Better than raw refund count because it scales with customer history. ≥30% on a customer with 5+ orders is unusual; ≥50% strongly suggests refund abuse. Pair with `Order Count >= 3` to avoid noise from one-off bad luck.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_refund_rate( $args ),
				'required_args' => [],
			],

			'edd_customer_active_subscriptions'   => [
				'label'         => __( 'Active Subscriptions', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many of the customer\'s subscriptions are currently billing (active or trialling). Requires EDD Recurring; without it the rule does not apply.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_active_subscription_count( $args ),
				'required_args' => [],
			],
			'edd_customer_has_active_subscription' => [
				'label'         => __( 'Has Active Subscription', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer has a subscription that is currently billing. The usual condition for a subscriber discount, or for exempting subscribers from a first-order rule. Requires EDD Recurring.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_active_subscription( $args ),
				'required_args' => [],
			],
			'edd_customer_active_licenses'        => [
				'label'         => __( 'Active Licences', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'How many active licences the customer holds. Requires Software Licensing; without it the rule does not apply.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_active_license_count( $args ),
				'required_args' => [],
			],
			'edd_customer_has_active_license'     => [
				'label'         => __( 'Has Active Licence', 'arraypress' ),
				'group'         => __( 'Customer: Subscriptions', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Whether the customer holds an active licence. Requires Software Licensing.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::has_active_license( $args ),
				'required_args' => [],
			],
			'edd_customer_downloads_in_window'    => [
				'label'         => __( 'File Downloads', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 20', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many files the customer downloaded in the chosen time window. A burst of downloads right after purchase is a shared account being emptied.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_downloads_in_window( $args ),
				'required_args' => [],
			],
			'edd_customer_download_ip_count'      => [
				'label'         => __( 'Download IPs', 'arraypress' ),
				'group'         => $risk,
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => Periods::get_window_units(),
				'description'   => __( 'How many different IP addresses downloaded the customer\'s files in the chosen time window. One customer downloading from five addresses in a day is a shared licence, not a busy person.', 'arraypress' ),
				'compare_value' => fn( $args ) => CustomerHelper::get_download_ip_count( $args ),
				'required_args' => [],
			],
		];
	}
}
