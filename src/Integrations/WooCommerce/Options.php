<?php
/**
 * WooCommerce Options Helper
 *
 * Supplies the choice lists behind WooCommerce select and AJAX condition
 * fields. Every method degrades to an empty list when WooCommerce is not
 * loaded, so the rule editor renders an empty select rather than fataling.
 *
 * @package     ArrayPress\Conditions\Integrations\WooCommerce
 * @copyright   Copyright (c) 2026, ArrayPress Limited
 * @license     GPL-2.0-or-later
 * @since       1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Conditions\Integrations\WooCommerce;

use ArrayPress\ArrayUtils\Arr;

/**
 * Class Options
 *
 * Option utilities for WooCommerce conditions.
 */
class Options {

	/** -------------------------------------------------------------------------
	 * Products
	 * ------------------------------------------------------------------------ */

	/**
	 * Product options for an AJAX select.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $ids    Specific IDs to resolve back to labels.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_product_options( ?string $search, ?array $ids ): array {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return [];
		}

		$args = [
			'limit'  => 20,
			'status' => [ 'publish', 'private', 'draft' ],
		];

		if ( $ids ) {
			$args['include'] = array_map( 'intval', $ids );
			$args['limit']   = count( $args['include'] );
		} elseif ( $search ) {
			$args['s'] = $search;
		}

		$products = wc_get_products( $args );

		if ( empty( $products ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $products as $product ) {
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
				continue;
			}

			$sku   = (string) $product->get_sku();
			$label = (string) $product->get_name();

			if ( '' !== $sku ) {
				$label .= ' (' . $sku . ')';
			}

			$options[] = [
				'value' => (string) $product->get_id(),
				'label' => $label,
			];
		}

		return $options;
	}

	/**
	 * Variation options for an AJAX select.
	 *
	 * Variations are products too, but they never appear in an ordinary product
	 * search -- WooCommerce excludes them unless the type is asked for.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $ids    Specific IDs to resolve back to labels.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_variation_options( ?string $search, ?array $ids ): array {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return [];
		}

		$args = [
			'limit'  => 20,
			'type'   => 'variation',
			'status' => [ 'publish', 'private' ],
		];

		if ( $ids ) {
			$args['include'] = array_map( 'intval', $ids );
			$args['limit']   = count( $args['include'] );
		} elseif ( $search ) {
			$args['s'] = $search;
		}

		$variations = wc_get_products( $args );

		if ( empty( $variations ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $variations as $variation ) {
			if ( ! is_object( $variation ) || ! method_exists( $variation, 'get_id' ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) $variation->get_id(),
				'label' => (string) $variation->get_name(),
			];
		}

		return $options;
	}

	/**
	 * Product type options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_product_types(): array {
		if ( ! function_exists( 'wc_get_product_types' ) ) {
			return [];
		}

		return Arr::to_options( wc_get_product_types() );
	}

	/**
	 * Stock status options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_stock_statuses(): array {
		if ( ! function_exists( 'wc_get_product_stock_status_options' ) ) {
			return [];
		}

		return Arr::to_options( wc_get_product_stock_status_options() );
	}

	/**
	 * Tax class options. The standard class has an empty slug in WooCommerce.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_classes(): array {
		if ( ! class_exists( 'WC_Tax' ) ) {
			return [];
		}

		$options = [
			[
				'value' => '',
				'label' => __( 'Standard', 'arraypress' ),
			],
		];

		foreach ( (array) \WC_Tax::get_tax_classes() as $class ) {
			$options[] = [
				'value' => sanitize_title( (string) $class ),
				'label' => (string) $class,
			];
		}

		return $options;
	}

	/**
	 * Tax status options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_statuses(): array {
		return [
			[
				'value' => 'taxable',
				'label' => __( 'Taxable', 'arraypress' ),
			],
			[
				'value' => 'shipping',
				'label' => __( 'Shipping only', 'arraypress' ),
			],
			[
				'value' => 'none',
				'label' => __( 'None', 'arraypress' ),
			],
		];
	}

	/** -------------------------------------------------------------------------
	 * Orders
	 * ------------------------------------------------------------------------ */

	/**
	 * Order status options, with the wc- prefix stripped.
	 *
	 * The prefix is a storage detail; WC_Order::get_status() returns the bare
	 * slug, so a rule saved with the prefix would never match.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_order_statuses(): array {
		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return [];
		}

		$options = [];

		foreach ( wc_get_order_statuses() as $slug => $label ) {
			$options[] = [
				'value' => (string) preg_replace( '/^wc-/', '', (string) $slug ),
				'label' => (string) $label,
			];
		}

		return $options;
	}

	/**
	 * Payment gateway options.
	 *
	 * Every registered gateway is listed, not only the enabled ones -- a rule
	 * written against a gateway that is temporarily switched off should keep
	 * its meaning when it is switched back on.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_gateways(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->payment_gateways ) || ! is_object( $wc->payment_gateways ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $wc->payment_gateways->payment_gateways() as $gateway ) {
			if ( ! is_object( $gateway ) || ! isset( $gateway->id ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) $gateway->id,
				'label' => (string) ( $gateway->get_method_title() ?: $gateway->id ),
			];
		}

		return $options;
	}

	/**
	 * Currency options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_currencies(): array {
		if ( ! function_exists( 'get_woocommerce_currencies' ) ) {
			return [];
		}

		return Arr::to_options( get_woocommerce_currencies() );
	}

	/**
	 * Country options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_countries(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->countries ) || ! is_object( $wc->countries ) ) {
			return [];
		}

		return Arr::to_options( (array) $wc->countries->get_countries() );
	}

	/** -------------------------------------------------------------------------
	 * Coupons and shipping
	 * ------------------------------------------------------------------------ */

	/**
	 * Coupon options for an AJAX select.
	 *
	 * Coupons are matched on their code, not their post ID -- that is what the
	 * cart and the order both carry.
	 *
	 * @param string|null $search Search term.
	 * @param array|null  $codes  Specific codes to resolve back to labels.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_coupon_options( ?string $search, ?array $codes ): array {
		if ( $codes ) {
			return array_map( static function ( $code ): array {
				return [
					'value' => (string) $code,
					'label' => (string) $code,
				];
			}, $codes );
		}

		$query = [
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		];

		if ( $search ) {
			$query['s'] = $search;
		}

		$posts = get_posts( $query );

		if ( empty( $posts ) ) {
			return [];
		}

		$options = [];

		foreach ( $posts as $post_id ) {
			$code = get_post_field( 'post_title', $post_id );

			if ( ! $code ) {
				continue;
			}

			$options[] = [
				'value' => (string) $code,
				'label' => (string) $code,
			];
		}

		return $options;
	}

	/**
	 * Shipping method options, keyed by method ID.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_methods(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->shipping ) || ! is_object( $wc->shipping ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $wc->shipping->get_shipping_methods() as $id => $method ) {
			$options[] = [
				'value' => (string) $id,
				'label' => is_object( $method ) && ! empty( $method->method_title ) ? (string) $method->method_title : (string) $id,
			];
		}

		return $options;
	}

	/**
	 * Shipping class options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_classes(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->shipping ) || ! is_object( $wc->shipping ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $wc->shipping->get_shipping_classes() as $class ) {
			if ( ! is_object( $class ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) $class->term_id,
				'label' => (string) $class->name,
			];
		}

		return $options;
	}

	/**
	 * Catalog visibility options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_catalog_visibilities(): array {
		return Arr::to_options( [
			'visible' => __( 'Shop and search results', 'arraypress' ),
			'catalog' => __( 'Shop only', 'arraypress' ),
			'search'  => __( 'Search results only', 'arraypress' ),
			'hidden'  => __( 'Hidden', 'arraypress' ),
		] );
	}

	/**
	 * Backorder policy options.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_backorder_options(): array {
		return Arr::to_options( [
			'no'     => __( 'Do not allow', 'arraypress' ),
			'notify' => __( 'Allow, but notify customer', 'arraypress' ),
			'yes'    => __( 'Allow', 'arraypress' ),
		] );
	}

	/**
	 * Options for which address tax is calculated from.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_based_on_options(): array {
		return Arr::to_options( [
			'shipping' => __( 'Customer shipping address', 'arraypress' ),
			'billing'  => __( 'Customer billing address', 'arraypress' ),
			'base'     => __( 'Shop base address', 'arraypress' ),
		] );
	}

	/**
	 * How an order came to exist.
	 *
	 * WooCommerce writes this itself; the values are not filterable in
	 * practice, so they are listed rather than discovered.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_created_via_options(): array {
		return Arr::to_options( [
			'checkout'  => __( 'Checkout', 'arraypress' ),
			'store-api' => __( 'Store API (blocks checkout)', 'arraypress' ),
			'rest-api'  => __( 'REST API', 'arraypress' ),
			'admin'     => __( 'Admin', 'arraypress' ),
		] );
	}

	/**
	 * Global product attribute taxonomies.
	 *
	 * Names carry the pa_ prefix, which is how a product stores them.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_attribute_taxonomies(): array {
		if ( ! function_exists( 'wc_get_attribute_taxonomies' ) || ! function_exists( 'wc_attribute_taxonomy_name' ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) wc_get_attribute_taxonomies() as $attribute ) {
			if ( ! is_object( $attribute ) || empty( $attribute->attribute_name ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) wc_attribute_taxonomy_name( $attribute->attribute_name ),
				'label' => (string) ( $attribute->attribute_label ?: $attribute->attribute_name ),
			];
		}

		return $options;
	}

	/**
	 * Shipping class options, keyed by slug rather than term ID.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_shipping_class_slugs(): array {
		if ( ! function_exists( 'WC' ) ) {
			return [];
		}

		$wc = WC();

		if ( ! isset( $wc->shipping ) || ! is_object( $wc->shipping ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) $wc->shipping->get_shipping_classes() as $class ) {
			if ( ! is_object( $class ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) $class->slug,
				'label' => (string) $class->name,
			];
		}

		return $options;
	}

	/**
	 * Tax class options with the standard class named rather than blank.
	 *
	 * The cart and order helpers normalise WooCommerce's empty slug to
	 * "standard" so a rule can express it; this list has to match.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_tax_class_slugs(): array {
		$options = [
			[
				'value' => 'standard',
				'label' => __( 'Standard', 'arraypress' ),
			],
		];

		if ( ! class_exists( 'WC_Tax' ) ) {
			return $options;
		}

		foreach ( (array) \WC_Tax::get_tax_classes() as $class ) {
			$options[] = [
				'value' => sanitize_title( (string) $class ),
				'label' => (string) $class,
			];
		}

		return $options;
	}

	/** -------------------------------------------------------------------------
	 * Reporting
	 * ------------------------------------------------------------------------ */

	/**
	 * Date range presets for store conditions.
	 *
	 * Kept in step with Stats::get_date_range() -- a preset offered here that
	 * the resolver does not know would silently report today's figures.
	 *
	 * @return array<array{value: string, label: string}>
	 *
	 * @since 1.0.0
	 */
	public static function get_date_ranges(): array {
		return Arr::to_options( [
			'today'        => __( 'Today', 'arraypress' ),
			'yesterday'    => __( 'Yesterday', 'arraypress' ),
			'this_week'    => __( 'This week', 'arraypress' ),
			'last_week'    => __( 'Last week', 'arraypress' ),
			'this_month'   => __( 'This month', 'arraypress' ),
			'last_month'   => __( 'Last month', 'arraypress' ),
			'this_quarter' => __( 'This quarter', 'arraypress' ),
			'this_year'    => __( 'This year', 'arraypress' ),
			'last_year'    => __( 'Last year', 'arraypress' ),
			'last_7_days'  => __( 'Last 7 days', 'arraypress' ),
			'last_30_days' => __( 'Last 30 days', 'arraypress' ),
			'last_90_days' => __( 'Last 90 days', 'arraypress' ),
			'all_time'     => __( 'All time', 'arraypress' ),
		] );
	}
	/**
	 * Shipping zone options, the uncovered-locations zone included.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_shipping_zone_options(): array {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return [];
		}

		$options = [];

		foreach ( (array) \WC_Shipping_Zones::get_zones() as $zone ) {
			if ( ! is_array( $zone ) || ! isset( $zone['id'] ) ) {
				continue;
			}

			$options[] = [
				'value' => (string) (int) $zone['id'],
				'label' => (string) ( $zone['zone_name'] ?? $zone['id'] ),
			];
		}

		$options[] = [
			'value' => '0',
			'label' => __( 'Locations not covered by your other zones', 'arraypress' ),
		];

		return $options;
	}

	/**
	 * Coupon discount types.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_coupon_types(): array {
		if ( function_exists( 'wc_get_coupon_types' ) ) {
			return Arr::to_options( (array) wc_get_coupon_types() );
		}

		return Arr::to_options( [
			'percent'       => __( 'Percentage discount', 'arraypress' ),
			'fixed_cart'    => __( 'Fixed cart discount', 'arraypress' ),
			'fixed_product' => __( 'Fixed product discount', 'arraypress' ),
		] );
	}

	/**
	 * Card brands, as WooCommerce names them.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_card_brands(): array {
		return Arr::to_options( [
			'visa'       => 'Visa',
			'mastercard' => 'Mastercard',
			'amex'       => 'American Express',
			'discover'   => 'Discover',
			'diners'     => 'Diners Club',
			'jcb'        => 'JCB',
			'interac'    => 'Interac',
			'unionpay'   => 'UnionPay',
		] );
	}

	/**
	 * Order attribution source types.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_attribution_source_types(): array {
		return Arr::to_options( [
			'typein'     => __( 'Direct', 'arraypress' ),
			'organic'    => __( 'Organic search', 'arraypress' ),
			'referral'   => __( 'Referral', 'arraypress' ),
			'utm'        => __( 'Campaign (UTM)', 'arraypress' ),
			'admin'      => __( 'Admin', 'arraypress' ),
			'mobile_app' => __( 'Mobile app', 'arraypress' ),
			'unknown'    => __( 'Unknown', 'arraypress' ),
		] );
	}

	/**
	 * Device types as order attribution records them.
	 *
	 * @return array<array{value: string, label: string}>
	 */
	public static function get_attribution_device_types(): array {
		return Arr::to_options( [
			'desktop' => __( 'Desktop', 'arraypress' ),
			'mobile'  => __( 'Mobile', 'arraypress' ),
			'tablet'  => __( 'Tablet', 'arraypress' ),
			'unknown' => __( 'Unknown', 'arraypress' ),
		] );
	}
}
