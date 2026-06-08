<?php
/**
 * Plugin Name: PowerUp Review Seed 20260603
 * Description: Imports the approved rewritten product reviews into WooCommerce.
 * Version: 2026.06.03.3
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'powerup_review_seed_20260603_run', 95 );

function powerup_review_seed_20260603_run() {
	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$version = '2026.06.03.3';
	if ( get_option( 'powerup_review_seed_20260603_version' ) === $version ) {
		return;
	}

	$inserted = array();

	foreach ( powerup_review_seed_20260603_data() as $review ) {
		$product_id = wc_get_product_id_by_sku( (string) $review['asin'] );
		if ( ! $product_id ) {
			continue;
		}

		$comment_id = powerup_review_seed_20260603_upsert( (int) $product_id, $review );
		if ( $comment_id ) {
			$inserted[] = $comment_id;
		}
	}

	powerup_review_seed_20260603_sync_stats();

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	if ( class_exists( 'Breeze_PurgeCache' ) && method_exists( 'Breeze_PurgeCache', 'breeze_cache_flush' ) ) {
		try {
			Breeze_PurgeCache::breeze_cache_flush( true, true, true );
		} catch ( Throwable $error ) {
			// Cache flushing can be unavailable during some server-side requests.
		}
	}

	update_option( 'powerup_review_seed_20260603_version', $version, false );
	update_option(
		'powerup_review_seed_20260603_last_result',
		array(
			'version'  => $version,
			'inserted' => $inserted,
			'run_time' => current_time( 'mysql' ),
		),
		false
	);
}

function powerup_review_seed_20260603_upsert( $product_id, array $review ) {
	global $wpdb;

	$source_id = sanitize_key( (string) $review['source_id'] );
	$existing  = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
			'_powerup_review_source_id',
			$source_id
		)
	);

	$comment_data = array(
		'comment_post_ID'      => $product_id,
		'comment_author'       => sanitize_text_field( (string) $review['author'] ),
		'comment_author_email' => sanitize_email( (string) $review['email'] ),
		'comment_author_url'   => '',
		'comment_content'      => wp_kses_post( (string) $review['content'] ),
		'comment_type'         => 'review',
		'comment_parent'       => 0,
		'user_id'              => 0,
		'comment_approved'     => 1,
		'comment_date'         => (string) $review['date'],
		'comment_date_gmt'     => get_gmt_from_date( (string) $review['date'] ),
	);

	if ( $existing ) {
		$comment_data['comment_ID'] = $existing;
		wp_update_comment( $comment_data );
		$comment_id = $existing;
	} else {
		$comment_id = wp_insert_comment( $comment_data );
	}

	if ( ! $comment_id || is_wp_error( $comment_id ) ) {
		return 0;
	}

	update_comment_meta( $comment_id, 'rating', min( 5, max( 1, (int) $review['rating'] ) ) );
	update_comment_meta( $comment_id, '_powerup_review_source_id', $source_id );
	update_comment_meta( $comment_id, '_powerup_review_source', 'Amazon review screenshot rewritten for independent store' );
	update_comment_meta( $comment_id, '_powerup_review_source_asin', sanitize_text_field( (string) $review['asin'] ) );

	if ( ! empty( $review['title'] ) ) {
		update_comment_meta( $comment_id, 'powerup_review_title', sanitize_text_field( (string) $review['title'] ) );
	}

	return (int) $comment_id;
}

function powerup_review_seed_20260603_sync_stats() {
	if ( ! class_exists( 'WC_Comments' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$asins = array( 'B0FFGSPWWS', 'B0FCM6HXVX', 'B0FCLY4DC1', 'B0FQNYCRH2', 'B0GGTDWRNN', 'B0GGTKHN4G' );

	foreach ( $asins as $asin ) {
		$product_id = wc_get_product_id_by_sku( $asin );
		$product    = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$product->set_rating_counts( WC_Comments::get_rating_counts_for_product( $product ) );
		$product->set_average_rating( WC_Comments::get_average_rating_for_product( $product ) );
		$product->set_review_count( WC_Comments::get_review_count_for_product( $product ) );
		$product->save();

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients( $product_id );
		}
	}
}

function powerup_review_seed_20260603_data() {
	return array(
		array( 'source_id' => 'b0ffgspwws-michael-javier-20260511', 'title'       => 'Great product!',
			'asin' => 'B0FFGSPWWS', 'author' => 'Michael J.', 'email' => 'reviews-b0ffgspwws-01@faimala.local', 'date' => '2026-05-11 10:15:00', 'rating' => 5, 'content' => 'The battery runtime has been solid for regular yard jobs, and the saw gets through the task without feeling complicated to use.' ),
		array( 'source_id' => 'b0ffgspwws-mrj-20260402', 'title'       => 'Lightweight And Easy To Put Together/Use',
			'asin' => 'B0FFGSPWWS', 'author' => 'MrJ', 'email' => 'reviews-b0ffgspwws-02@faimala.local', 'date' => '2026-04-28 11:20:00', 'rating' => 5, 'content' => 'Setup was straightforward, and the included batteries, charger, and spare chains made it feel ready for real yard cleanup right out of the box.' ),
		array( 'source_id' => 'b0ffgspwws-ray-rawlins-20260521', 'title'       => 'Nice saw',
			'asin' => 'B0FFGSPWWS', 'author' => 'Ray R.', 'email' => 'reviews-b0ffgspwws-03@faimala.local', 'date' => '2026-05-21 09:45:00', 'rating' => 5, 'content' => 'This kit handled a pile of medium logs more easily than expected. It is light to carry and the extra chains add good value.' ),
		array( 'source_id' => 'b0ffgspwws-customer-20260430', 'title'       => 'Super lightweight, easy to use safely',
			'asin' => 'B0FFGSPWWS', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0ffgspwws-04@faimala.local', 'date' => '2026-04-30 14:10:00', 'rating' => 5, 'content' => 'The saw felt lightweight and easy to control while trimming tree branches around the yard. Performance was strong for a cordless homeowner saw.' ),
		array( 'source_id' => 'b0ffgspwws-earnest-tullis-20260530', 'title'       => 'What a great little chainsaw this is it',
			'asin' => 'B0FFGSPWWS', 'author' => 'Earnest T.', 'email' => 'reviews-b0ffgspwws-05@faimala.local', 'date' => '2026-05-30 12:35:00', 'rating' => 5, 'content' => 'For a compact 12-inch saw, it cuts impressively well. The blade stays sharp through pruning work and the batteries last long enough for a good session.' ),
		array( 'source_id' => 'b0fcm6hxvx-tw-20260503', 'title'       => 'Totally Worth It',
			'asin' => 'B0FCM6HXVX', 'author' => 'TW', 'email' => 'reviews-b0fcm6hxvx-01@faimala.local', 'date' => '2026-05-03 13:05:00', 'rating' => 5, 'content' => 'With an M18-style battery installed, this saw is light, quick, and easy to use for pruning and cutting smaller logs around the house.' ),
		array( 'source_id' => 'b0fcm6hxvx-dave-20260507', 'title'       => 'Log ripper!',
			'asin' => 'B0FCM6HXVX', 'author' => 'Dave', 'email' => 'reviews-b0fcm6hxvx-02@faimala.local', 'date' => '2026-05-07 10:30:00', 'rating' => 5, 'content' => 'Great value for a tool-only saw. It cuts fast through branches and small logs without the noise and hassle of a gas saw.' ),
		array( 'source_id' => 'b0fcm6hxvx-john-oseguera-20260529', 'title'       => 'John\'s what to know :)',
			'asin' => 'B0FCM6HXVX', 'author' => 'John O.', 'email' => 'reviews-b0fcm6hxvx-03@faimala.local', 'date' => '2026-05-29 15:25:00', 'rating' => 4, 'content' => 'A good match for trimming when you already own compatible batteries. It works well on small trees and branch cleanup.' ),
		array( 'source_id' => 'b0fcm6hxvx-amazon-customer-20250805', 'title'       => 'Cordless chainsaw',
			'asin' => 'B0FCM6HXVX', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0fcm6hxvx-04@faimala.local', 'date' => '2026-04-27 16:00:00', 'rating' => 5, 'content' => 'The saw has more power than expected for a cordless tool and feels easy to carry around the yard for routine cleanup.' ),
		array( 'source_id' => 'b0fcly4dc1-retired-guy-20260204', 'title'       => 'I would buy it again',
			'asin' => 'B0FCLY4DC1', 'author' => 'Retired Guy', 'email' => 'reviews-b0fcly4dc1-01@faimala.local', 'date' => '2026-04-26 09:35:00', 'rating' => 5, 'content' => 'This compact saw is surprisingly capable for small jobs. It cut through yard branches cleanly and felt easy to control.' ),
		array( 'source_id' => 'b0fcly4dc1-rpm-20250827', 'title'       => 'Light weight inexpensive saw',
			'asin' => 'B0FCLY4DC1', 'author' => 'RPM Investments', 'email' => 'reviews-b0fcly4dc1-02@faimala.local', 'date' => '2026-04-25 12:10:00', 'rating' => 5, 'content' => 'Being able to use compatible DeWalt-style batteries made this tool-only saw a practical choice for cleanup work around the property.' ),
		array( 'source_id' => 'b0fcly4dc1-amazon-customer-20260521', 'title'       => 'Incredibly powerful saw!',
			'asin' => 'B0FCLY4DC1', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0fcly4dc1-03@faimala.local', 'date' => '2026-05-21 08:40:00', 'rating' => 5, 'content' => 'The saw has plenty of power for trimming and small tree work. It is light enough to carry and makes cleanup faster.' ),
		array( 'source_id' => 'b0fcly4dc1-la-diy-20260427', 'title'       => 'Great Little Saw, for the money',
			'asin' => 'B0FCLY4DC1', 'author' => 'LA DIY', 'email' => 'reviews-b0fcly4dc1-04@faimala.local', 'date' => '2026-04-27 17:20:00', 'rating' => 4, 'content' => 'A great little saw for the money. It cuts through smaller branches and vines well once the chain is set correctly.' ),
		array( 'source_id' => 'b0fcly4dc1-pete-nakahberu-20260531', 'title'       => '5 Stars-Way More Saw Than I Expected',
			'asin' => 'B0FCLY4DC1', 'author' => 'Pete N.', 'email' => 'reviews-b0fcly4dc1-05@faimala.local', 'date' => '2026-05-31 10:55:00', 'rating' => 5, 'content' => 'It performs better than expected for its size. The portable kit format makes it easy to bring along for cleanup jobs and quick cuts.' ),
		array( 'source_id' => 'b0fqnycrh2-confident-88-20260414', 'title'       => 'Tough',
			'asin' => 'B0FQNYCRH2', 'author' => 'Confident 88', 'email' => 'reviews-b0fqnycrh2-01@faimala.local', 'date' => '2026-04-24 09:25:00', 'rating' => 5, 'content' => 'These replacement chains feel tough and practical to keep on hand. I would buy another pack for future maintenance.' ),
		array( 'source_id' => 'b0fqnycrh2-thomas-hirtleman-20260205', 'title'       => 'Worth the price',
			'asin' => 'B0FQNYCRH2', 'author' => 'Thomas H.', 'email' => 'reviews-b0fqnycrh2-02@faimala.local', 'date' => '2026-04-23 13:45:00', 'rating' => 5, 'content' => 'Worth the price and useful for keeping the saw cutting cleanly. The chain stays sharp through normal yard work.' ),
		array( 'source_id' => 'b0fqnycrh2-lindsey-20251224', 'title'       => 'Electric Chainsaw Chains 12" (2-Pack)',
			'asin' => 'B0FQNYCRH2', 'author' => 'Lindsey', 'email' => 'reviews-b0fqnycrh2-03@faimala.local', 'date' => '2026-04-22 15:15:00', 'rating' => 5, 'content' => 'The two-pack is easy to change out and cuts well. It is a good value for keeping a cordless chainsaw ready for the next job.' ),
		array( 'source_id' => 'b0ggtdwrnn-punkass-matteo-20260426', 'title'       => 'Versatile Powerhouse - A Must-Have for Property Maintenance!',
			'asin' => 'B0GGTDWRNN', 'author' => 'Matteo', 'email' => 'reviews-b0ggtdwrnn-01@faimala.local', 'date' => '2026-04-26 11:30:00', 'rating' => 5, 'content' => 'The 6-inch and 8-inch bars make this kit versatile for pruning and thicker branches. Automatic lubrication and tool-free tensioning make adjustments simple during yard work.' ),
		array( 'source_id' => 'b0ggtkhn4g-nutner-20260419', 'title'       => 'Versatile and easy to use chain saw',
			'asin' => 'B0GGTKHN4G', 'author' => 'Nutner', 'email' => 'reviews-b0ggtkhn4g-01@faimala.local', 'date' => '2026-04-19 10:05:00', 'rating' => 4, 'content' => 'A good value compact saw for light yard cutting. The dual bar setup and spare chains make it useful for tight spaces and regular pruning jobs.' ),
		array( 'source_id' => 'b0ggtkhn4g-bill-moses-20260416', 'title'       => '8-Inch Brushless Cordless Chainsaw',
			'asin' => 'B0GGTKHN4G', 'author' => 'Bill M.', 'email' => 'reviews-b0ggtkhn4g-02@faimala.local', 'date' => '2026-04-21 12:20:00', 'rating' => 5, 'content' => 'This is a handy light-duty saw for pruning branches, trimming small trees, and general maintenance. It is easy to handle and beginner-friendly.' ),
		array( 'source_id' => 'b0ggtkhn4g-natalia-ramos-20260515', 'title'       => 'Lightweight but very powerful',
			'asin' => 'B0GGTKHN4G', 'author' => 'Natalia R.', 'email' => 'reviews-b0ggtkhn4g-03@faimala.local', 'date' => '2026-05-15 14:40:00', 'rating' => 5, 'content' => 'Lightweight but still powerful for home gardening work. It cuts small trunks cleanly and makes yard tasks feel much easier.' ),
	);
}
