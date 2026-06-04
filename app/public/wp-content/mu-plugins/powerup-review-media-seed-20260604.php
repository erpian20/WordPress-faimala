<?php
/**
 * Plugin Name: PowerUp Review Media Seed 20260604
 * Description: Binds Excel-anchored customer review photos to imported WooCommerce reviews.
 * Version: 2026.06.04.2
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'powerup_review_media_seed_20260604_run', 98 );

function powerup_review_media_seed_20260604_run() {
	$version = '2026.06.04.2';
	if ( get_option( 'powerup_review_media_seed_20260604_version' ) === $version ) {
		return;
	}

	$bound = 0;
	$missing_files = 0;
	$missing_comments = 0;
	$media_data = powerup_review_media_seed_20260604_data();

	powerup_review_media_seed_20260604_clear_old_bindings( array_keys( $media_data ) );

	foreach ( $media_data as $source_id => $media_files ) {
		$comment_id = powerup_review_media_seed_20260604_get_comment_id( $source_id );
		if ( ! $comment_id ) {
			$missing_comments++;
			continue;
		}

		$image_ids = array();
		foreach ( $media_files as $media_file ) {
			$attachment_id = powerup_review_media_seed_20260604_get_or_create_attachment( $media_file );
			if ( $attachment_id ) {
				$image_ids[] = $attachment_id;
			} else {
				$missing_files++;
			}
		}

		if ( ! empty( $image_ids ) ) {
			update_comment_meta( $comment_id, 'powerup_review_image_ids', array_values( array_unique( $image_ids ) ) );
			update_comment_meta( $comment_id, '_powerup_review_media_source', 'Amazon Excel review media selected for independent store display' );
			$bound++;
		}
	}

	$result = array(
		'version'          => $version,
		'bound_comments'   => $bound,
		'missing_files'    => $missing_files,
		'missing_comments' => $missing_comments,
		'run_time'         => current_time( 'mysql' ),
	);

	update_option( 'powerup_review_media_seed_20260604_last_result', $result, false );

	if ( 0 === $missing_files && $bound > 0 ) {
		update_option( 'powerup_review_media_seed_20260604_version', $version, false );
	}

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

function powerup_review_media_seed_20260604_data() {
	return array(
		'excel-20260604-b0ffgspwws-20-fdd8d72a'  => array( 'b0ffgspwws-review-media-row20-col11.jpg' ),
		'excel-20260604-b0ffgspwws-21-8e4c8115'  => array( 'b0ffgspwws-review-media-row21-col11.jpg' ),
		'excel-20260604-b0fqnycrh2-22-d0e0aa9c'  => array( 'b0fqnycrh2-review-media-row22-col11.jpg' ),
		'excel-20260604-b0ffgspwws-26-e42cabd0'  => array( 'b0ffgspwws-review-media-row26-col11.jpg' ),
		'excel-20260604-b0ffgspwws-28-8d85811a'  => array( 'b0ffgspwws-review-media-row28-col11.jpg' ),
		'excel-20260604-b0ffgspwws-29-80d4291c'  => array( 'b0ffgspwws-review-media-row29-col11.jpg' ),
		'excel-20260604-b0ffgspwws-31-c95b8717'  => array( 'b0ffgspwws-review-media-row31-col11.jpg', 'b0ffgspwws-review-media-row31-col12.jpg' ),
		'excel-20260604-b0ffgspwws-32-005200f1'  => array( 'b0ffgspwws-review-media-row32-col11.jpg', 'b0ffgspwws-review-media-row32-col12.jpg' ),
		'excel-20260604-b0ffgspwws-43-52e08b64'  => array( 'b0ffgspwws-review-media-row43-col11.jpg', 'b0ffgspwws-review-media-row43-col12.jpg' ),
		'excel-20260604-b0fcly4dc1-44-e105dc50'  => array( 'b0fcly4dc1-review-media-row44-col11.jpg', 'b0fcly4dc1-review-media-row44-col12.jpg' ),
		'excel-20260604-b0ffgspwws-50-d7ecb4c1'  => array( 'b0ffgspwws-review-media-row50-col11.jpg' ),
		'excel-20260604-b0fcly4dc1-61-d9ef0397'  => array( 'b0fcly4dc1-review-media-row61-col11.jpg', 'b0fcly4dc1-review-media-row61-col12.jpg' ),
		'excel-20260604-b0fqnycrh2-78-e4984fd4'  => array( 'b0fqnycrh2-review-media-row78-col11.jpg' ),
		'excel-20260604-b0ffgspwws-84-33aad6ac'  => array( 'b0ffgspwws-review-media-row84-col11.jpg' ),
		'excel-20260604-b0ffgspwws-93-320a3b9f'  => array( 'b0ffgspwws-review-media-row93-col11.jpg', 'b0ffgspwws-review-media-row93-col12.jpg', 'b0ffgspwws-review-media-row93-col13.jpg' ),
		'excel-20260604-b0fcm6hxvx-95-da122f97'  => array( 'b0fcm6hxvx-review-media-row95-col11.jpg' ),
		'excel-20260604-b0ffgspwws-100-0f9618b5' => array( 'b0ffgspwws-review-media-row100-col11.jpg' ),
		'excel-20260604-b0fcly4dc1-111-48ba9086' => array( 'b0fcly4dc1-review-media-row111-col11.jpg' ),
	);
}

function powerup_review_media_seed_20260604_clear_old_bindings( $source_ids ) {
	global $wpdb;

	$comment_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s",
			'_powerup_review_media_source',
			'Amazon Excel review media selected for independent store display'
		)
	);

	foreach ( $source_ids as $source_id ) {
		$comment_id = powerup_review_media_seed_20260604_get_comment_id( $source_id );
		if ( $comment_id ) {
			$comment_ids[] = $comment_id;
		}
	}

	$comment_ids = array_filter( array_unique( array_map( 'absint', $comment_ids ) ) );
	foreach ( $comment_ids as $comment_id ) {
		delete_comment_meta( $comment_id, 'powerup_review_image_ids' );
		delete_comment_meta( $comment_id, '_powerup_review_media_source' );
	}
}

function powerup_review_media_seed_20260604_get_comment_id( $source_id ) {
	global $wpdb;

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			'_powerup_review_source_id',
			sanitize_key( (string) $source_id )
		)
	);
}

function powerup_review_media_seed_20260604_get_or_create_attachment( $file_name ) {
	global $wpdb;

	$file_name = sanitize_file_name( (string) $file_name );
	if ( '' === $file_name ) {
		return 0;
	}

	$asset_key = '2026/06/review-media/' . $file_name;
	$existing  = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			'_powerup_review_media_asset',
			$asset_key
		)
	);

	if ( $existing ) {
		return $existing;
	}

	$file_path = trailingslashit( ABSPATH ) . 'wp-content/uploads/' . $asset_key;
	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$file_type = wp_check_filetype( $file_name, null );
	if ( empty( $file_type['type'] ) ) {
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => content_url( 'uploads/' . $asset_key ),
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$file_path
	);

	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';

	$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
	if ( ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	update_post_meta( $attachment_id, '_powerup_review_media_asset', $asset_key );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Customer review photo' );

	return (int) $attachment_id;
}
