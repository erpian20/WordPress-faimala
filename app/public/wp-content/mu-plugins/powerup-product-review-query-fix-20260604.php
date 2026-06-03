<?php
/**
 * Plugin Name: PowerUp Product Review Query Fix 20260604
 * Description: Ensures WooCommerce product review lists include review-type comments.
 * Version: 2026.06.04.1
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$product_id = get_queried_object_id();
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			return;
		}

		$reviews = get_comments(
			array(
				'post_id' => (int) $product_id,
				'status'  => 'approve',
				'type'    => 'review',
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);

		global $wp_query;
		if ( $wp_query instanceof WP_Query ) {
			$wp_query->comments      = $reviews;
			$wp_query->comment_count = count( $reviews );
		}
	},
	30
);

add_filter(
	'comments_template_query_args',
	function ( $args ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$args['type'] = 'review';
		}

		return $args;
	},
	20
);
