<?php
/**
 * EDD Product Conditions
 *
 * @package     ArrayPress\Conditions\Conditions\Integrations\EDD
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Conditions\EDD;

use ArrayPress\Conditions\Integrations\EDD\Product as ProductHelper;
use ArrayPress\Conditions\Integrations\EDD\Options;
use ArrayPress\Conditions\Options\Periods;
use ArrayPress\Conditions\Options\WordPress;
use ArrayPress\Conditions\Operators;

/**
 * Class Product
 *
 * Provides EDD product-related conditions.
 */
class Product {

	/**
	 * Get all product conditions.
	 *
	 * @return array<string, array>
	 */
	public static function get_all(): array {
		$conditions = [
			// Details
			'edd_product_type'               => [
				'label'         => __( 'Type', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select type...', 'arraypress' ),
				'description'   => __( 'The type of product (default, bundle, or service).', 'arraypress' ),
				'options'       => Options::get_product_types(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_type( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_status'             => [
				'label'         => __( 'Status', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select status...', 'arraypress' ),
				'description'   => __( 'The post status of the product.', 'arraypress' ),
				'options'       => WordPress::get_post_statuses(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_status( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_author'             => [
				'label'         => __( 'Author', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'user',
				'multiple'      => true,
				'placeholder'   => __( 'Search users...', 'arraypress' ),
				'description'   => __( 'The author of the product.', 'arraypress' ),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_author( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_date_created'       => [
				'label'         => __( 'Date Created', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the product was published.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_date_created( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_date_modified'      => [
				'label'         => __( 'Date Modified', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'date',
				'description'   => __( 'The date the product was last modified.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_date_modified( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_age'                => [
				'label'         => __( 'Age', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 30', 'arraypress' ),
				'min'           => 0,
				'units'         => Periods::get_age_units(),
				'description'   => __( 'How long ago the product was published.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_age( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_is_bundle'          => [
				'label'         => __( 'Is Bundle', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product is a bundle.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::is_bundle( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_bundle_count'       => [
				'label'         => __( 'Bundle Product Count', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of products in the bundle.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_bundle_count( $args ),
				'required_args' => [ 'product_id' ],
			],

			// Taxonomies
			'edd_product_categories'         => [
				'label'         => __( 'Categories', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_category',
				'multiple'      => true,
				'placeholder'   => __( 'Search categories...', 'arraypress' ),
				'description'   => __( 'The categories assigned to the product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => ProductHelper::get_categories( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_tags'               => [
				'label'         => __( 'Tags', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'term',
				'taxonomy'      => 'download_tag',
				'multiple'      => true,
				'placeholder'   => __( 'Search tags...', 'arraypress' ),
				'description'   => __( 'The tags assigned to the product.', 'arraypress' ),
				'operators'     => Operators::collection(),
				'compare_value' => fn( $args ) => ProductHelper::get_tags( $args ),
				'required_args' => [ 'product_id' ],
			],

			// Pricing
			'edd_product_price'              => [
				'label'         => __( 'Price', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 29.99', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The product price (or base price for variable pricing).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_price( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_variable_prices' => [
				'label'         => __( 'Has Variable Prices', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has variable pricing enabled.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_variable_prices( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_is_free'            => [
				'label'         => __( 'Is Free', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product is free.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::is_free( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_price_option_count' => [
				'label'         => __( 'Price Option Count', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of price options for variable priced products.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_price_option_count( $args ),
				'required_args' => [ 'product_id' ],
			],

			// Files
			'edd_product_file_count'         => [
				'label'         => __( 'File Count', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 3', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'Number of downloadable files attached.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_file_count( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_files'          => [
				'label'         => __( 'Has Files', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has downloadable files.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_files( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_download_limit'     => [
				'label'         => __( 'Download Limit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The file download limit (0 = unlimited).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_download_limit( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_download_limit' => [
				'label'         => __( 'Has Download Limit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a download limit set.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_download_limit( $args ),
				'required_args' => [ 'product_id' ],
			],

			// Content
			'edd_product_has_featured_image' => [
				'label'         => __( 'Has Featured Image', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a featured image.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_featured_image( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_excerpt'        => [
				'label'         => __( 'Has Excerpt', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a short description.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_excerpt( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_notes'          => [
				'label'         => __( 'Has Purchase Notes', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has purchase notes.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_notes( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_word_count'         => [
				'label'         => __( 'Word Count', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 500', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The word count of the product description.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_word_count( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_shortcode'      => [
				'label'         => __( 'Has Shortcode', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. gallery', 'arraypress' ),
				'description'   => __( 'Check if the description contains a specific shortcode.', 'arraypress' ),
				'operators'     => Operators::contains(),
				'compare_value' => fn( $args, $user_value ) => ProductHelper::has_shortcode( $args, $user_value ) ? $user_value : '',
				'required_args' => [ 'product_id' ],
			],
			'edd_product_has_block'          => [
				'label'         => __( 'Has Block', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. core/image', 'arraypress' ),
				'description'   => __( 'Check if the description contains a specific block.', 'arraypress' ),
				'operators'     => Operators::contains(),
				'compare_value' => fn( $args, $user_value ) => ProductHelper::has_block( $args, $user_value ) ? $user_value : '',
				'required_args' => [ 'product_id' ],
			],

			// Stats
			'edd_product_sales'              => [
				'label'         => __( 'Total Sales', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 100', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The total number of sales.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_sales( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings'           => [
				'label'         => __( 'Total Earnings', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1000.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The total earnings.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_earnings( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_sales_in_period'    => [
				'label'         => __( 'Sales in Period', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 50', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Sales within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_sales_in_period( $args ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_earnings_in_period' => [
				'label'         => __( 'Earnings in Period', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number_unit',
				'placeholder'   => __( 'e.g. 500.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'units'         => fn() => Options::get_date_ranges(),
				'description'   => __( 'Earnings within a time period.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_earnings_in_period( $args ),
				'required_args' => [ 'product_id' ],
			],

			// Meta
			'edd_product_meta_text'          => [
				'label'         => __( 'Meta (Text)', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a text meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => ProductHelper::get_meta_text( $args, $user_value ),
				'required_args' => [ 'product_id' ],
			],
			'edd_product_meta_number'        => [
				'label'         => __( 'Meta (Number)', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'meta_key:value', 'arraypress' ),
				'description'   => __( 'Match against a numeric meta field. Format: meta_key:value', 'arraypress' ),
				'compare_value' => fn( $args, $user_value ) => ProductHelper::get_meta_number( $args, $user_value ),
				'required_args' => [ 'product_id' ],
			],
		];

		// Licensing conditions (requires EDD Software Licensing)
		if ( class_exists( 'EDD_SL_Download' ) ) {
			$conditions['edd_product_licensing_enabled']  = [
				'label'         => __( 'Licensing Enabled', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if licensing is enabled.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_licensing( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_license_limit']      = [
				'label'         => __( 'Activation Limit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 5', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The license activation limit.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_license_limit( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_has_license_limit']  = [
				'label'         => __( 'Has Activation Limit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if there is an activation limit (not unlimited).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_license_limit( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_is_lifetime']        = [
				'label'         => __( 'Is Lifetime License', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the license never expires.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::is_lifetime_license( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_license_exp_length'] = [
				'label'         => __( 'License Expiration Length', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 1', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The license expiration length (e.g., 1 for "1 year").', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_license_exp_length( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_license_exp_unit']   = [
				'label'         => __( 'License Expiration Unit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select unit...', 'arraypress' ),
				'description'   => __( 'The license expiration time unit.', 'arraypress' ),
				'options'       => Periods::get_age_units(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_license_exp_unit( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_has_beta']           = [
				'label'         => __( 'Has Beta Releases', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if beta releases are enabled.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_beta( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_version']            = [
				'label'         => __( 'Version', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'text',
				'placeholder'   => __( 'e.g. 1.0.0', 'arraypress' ),
				'description'   => __( 'The current stable version.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_version( $args ),
				'required_args' => [ 'product_id' ],
			];
		}

		// Recurring conditions (requires EDD Recurring)
		if ( function_exists( 'edd_recurring' ) ) {
			$conditions['edd_product_is_recurring']   = [
				'label'         => __( 'Is Recurring', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product is a subscription.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::is_recurring( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_billing_period'] = [
				'label'         => __( 'Billing Period', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select period...', 'arraypress' ),
				'description'   => __( 'The billing period for recurring products.', 'arraypress' ),
				'options'       => Periods::get_billing_periods(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_billing_period( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_billing_times']  = [
				'label'         => __( 'Billing Times', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 12 (0 = unlimited)', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The number of billing cycles (0 = unlimited).', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_billing_times( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_has_free_trial'] = [
				'label'         => __( 'Has Free Trial', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product offers a free trial.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_free_trial( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_trial_quantity'] = [
				'label'         => __( 'Trial Length', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 14', 'arraypress' ),
				'min'           => 0,
				'step'          => 1,
				'description'   => __( 'The trial period length (e.g., 14 for "14 days").', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_trial_quantity( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_trial_unit']     = [
				'label'         => __( 'Trial Unit', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'select',
				'multiple'      => true,
				'placeholder'   => __( 'Select unit...', 'arraypress' ),
				'description'   => __( 'The trial period time unit.', 'arraypress' ),
				'options'       => Periods::get_age_units(),
				'operators'     => Operators::collection_any_none(),
				'compare_value' => fn( $args ) => ProductHelper::get_trial_unit( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_has_signup_fee'] = [
				'label'         => __( 'Has Signup Fee', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'boolean',
				'description'   => __( 'Check if the product has a signup fee.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::has_signup_fee( $args ),
				'required_args' => [ 'product_id' ],
			];
			$conditions['edd_product_signup_fee']     = [
				'label'         => __( 'Signup Fee Amount', 'arraypress' ),
				'group'         => __( 'Product', 'arraypress' ),
				'type'          => 'number',
				'placeholder'   => __( 'e.g. 10.00', 'arraypress' ),
				'min'           => 0,
				'step'          => 0.01,
				'description'   => __( 'The signup fee amount.', 'arraypress' ),
				'compare_value' => fn( $args ) => ProductHelper::get_signup_fee( $args ),
				'required_args' => [ 'product_id' ],
			];
		}

		return $conditions;
	}

}