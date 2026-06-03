<?php
/**
 * Plugin Name: PowerUp Review List Fix 20260603
 * Description: Makes product review comments available to the frontend review list.
 * Version: 2026.06.03.1
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'comments_array',
	function ( $comments, $post_id ) {
		if ( 'product' !== get_post_type( $post_id ) ) {
			return $comments;
		}

		$has_reviews = false;
		foreach ( $comments as $comment ) {
			if ( $comment instanceof WP_Comment && 'review' === $comment->comment_type ) {
				$has_reviews = true;
				break;
			}
		}

		if ( $has_reviews ) {
			return $comments;
		}

		return get_comments(
			array(
				'post_id' => (int) $post_id,
				'status'  => 'approve',
				'type'    => 'review',
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);
	},
	20,
	2
);
