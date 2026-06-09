<?php
/**
 * Plugin Name: PowerUp Review Seed 20260603
 * Description: Imports the approved rewritten product reviews into WooCommerce.
 * Version: 2026.06.03.5
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'powerup_review_seed_20260603_run', 95 );

function powerup_review_seed_20260603_run() {
	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$version = '2026.06.03.5';
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
			'asin' => 'B0FFGSPWWS', 'author' => 'Michael J.', 'email' => 'reviews-b0ffgspwws-01@faimala.local', 'date' => '2026-05-11 10:15:00', 'rating' => 5, 'content' => 'Works great. The battery life is long enough for yard tasks, and the saw gets the job done without feeling complicated.' ),
		array( 'source_id' => 'b0ffgspwws-mrj-20260402', 'title'       => 'Lightweight And Easy To Put Together/Use',
			'asin' => 'B0FFGSPWWS', 'author' => 'MrJ', 'email' => 'reviews-b0ffgspwws-02@faimala.local', 'date' => '2026-04-28 11:20:00', 'rating' => 5, 'content' => 'Assembly was simple, with only the included wrench needed. It cut cleanly through a fallen branch around 3 inches thick and felt lightweight compared with gas saws.' ),
		array( 'source_id' => 'b0ffgspwws-ray-rawlins-20260521', 'title'       => 'Nice saw',
			'asin' => 'B0FFGSPWWS', 'author' => 'Ray R.', 'email' => 'reviews-b0ffgspwws-03@faimala.local', 'date' => '2026-05-21 09:45:00', 'rating' => 5, 'content' => 'It handled a bunch of logs around 12 inches thick and was easy to use. The extra chains make the kit feel like a good bargain.' ),
		array( 'source_id' => 'b0ffgspwws-customer-20260430', 'title'       => 'Super lightweight, easy to use safely',
			'asin' => 'B0FFGSPWWS', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0ffgspwws-04@faimala.local', 'date' => '2026-04-30 14:10:00', 'rating' => 5, 'content' => 'It trimmed a 30-foot live oak and felt lightweight, safe, and strong enough for the job.' ),
		array( 'source_id' => 'b0ffgspwws-earnest-tullis-20260530', 'title'       => 'What a great little chainsaw this is it',
			'asin' => 'B0FFGSPWWS', 'author' => 'Earnest T.', 'email' => 'reviews-b0ffgspwws-05@faimala.local', 'date' => '2026-05-30 12:35:00', 'rating' => 5, 'content' => 'Compared with larger saws, this compact 12-inch model impressed me. The batteries last a long time, and the blade feels sharp for normal pruning.' ),
		array( 'source_id' => 'b0fcm6hxvx-tw-20260503', 'title'       => 'Totally Worth It',
			'asin' => 'B0FCM6HXVX', 'author' => 'TW', 'email' => 'reviews-b0fcm6hxvx-01@faimala.local', 'date' => '2026-05-03 13:05:00', 'rating' => 5, 'content' => 'With one compatible M18-style 5Ah battery, it made repeated cuts on a 10-inch log and stayed fast when maintained.' ),
		array( 'source_id' => 'b0fcm6hxvx-dave-20260507', 'title'       => 'Log ripper!',
			'asin' => 'B0FCM6HXVX', 'author' => 'Dave', 'email' => 'reviews-b0fcm6hxvx-02@faimala.local', 'date' => '2026-05-07 10:30:00', 'rating' => 5, 'content' => 'Great value and a real log ripper. It cuts through logs quickly.' ),
		array( 'source_id' => 'b0fcm6hxvx-john-oseguera-20260529', 'title'       => 'John\'s what to know :)',
			'asin' => 'B0FCM6HXVX', 'author' => 'John O.', 'email' => 'reviews-b0fcm6hxvx-03@faimala.local', 'date' => '2026-05-29 15:25:00', 'rating' => 4, 'content' => 'It is tool-only, but works well for trimming if you already own compatible batteries. It tackled small trees and branch cleanup better than expected.' ),
		array( 'source_id' => 'b0fcm6hxvx-amazon-customer-20250805', 'title'       => 'Cordless chainsaw',
			'asin' => 'B0FCM6HXVX', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0fcm6hxvx-04@faimala.local', 'date' => '2026-04-27 16:00:00', 'rating' => 5, 'content' => 'It has more power than expected, trims branches and storm debris well, and is light enough for regular yard work.' ),
		array( 'source_id' => 'b0fcly4dc1-retired-guy-20260204', 'title'       => 'I would buy it again',
			'asin' => 'B0FCLY4DC1', 'author' => 'Retired Guy', 'email' => 'reviews-b0fcly4dc1-01@faimala.local', 'date' => '2026-04-26 09:35:00', 'rating' => 5, 'content' => 'I used it for small jobs around the yard and even cut a large hardwood branch to clear a pathway. It is light, handy, and capable for its size.' ),
		array( 'source_id' => 'b0fcly4dc1-rpm-20250827', 'title'       => 'Light weight inexpensive saw',
			'asin' => 'B0FCLY4DC1', 'author' => 'RPM Investments', 'email' => 'reviews-b0fcly4dc1-02@faimala.local', 'date' => '2026-04-25 12:10:00', 'rating' => 5, 'content' => 'It works well with compatible DeWalt-style batteries and avoids buying another battery setup. It is light enough for weekend trimming and cleanup work.' ),
		array( 'source_id' => 'b0fcly4dc1-amazon-customer-20260521', 'title'       => 'Incredibly powerful saw!',
			'asin' => 'B0FCLY4DC1', 'author' => 'PowerUp Customer', 'email' => 'reviews-b0fcly4dc1-03@faimala.local', 'date' => '2026-05-21 08:40:00', 'rating' => 5, 'content' => 'This saw has strong cutting power for its size. It stayed useful through a long pile of branches and was easy to carry between cuts.' ),
		array( 'source_id' => 'b0fcly4dc1-la-diy-20260427', 'title'       => 'Great Little Saw, for the money',
			'asin' => 'B0FCLY4DC1', 'author' => 'LA DIY', 'email' => 'reviews-b0fcly4dc1-04@faimala.local', 'date' => '2026-04-27 17:20:00', 'rating' => 4, 'content' => 'It cut a large amount of ivy and small branches for yard work. Once the chain is set correctly, it is a useful little saw for the money.' ),
		array( 'source_id' => 'b0fcly4dc1-pete-nakahberu-20260531', 'title'       => '5 Stars-Way More Saw Than I Expected',
			'asin' => 'B0FCLY4DC1', 'author' => 'Pete N.', 'email' => 'reviews-b0fcly4dc1-05@faimala.local', 'date' => '2026-05-31 10:55:00', 'rating' => 5, 'content' => 'It was much more capable than expected. It sliced through wood easily, cut logs in sections, and the carry bag made it easy to take along.' ),
		array( 'source_id' => 'b0fqnycrh2-confident-88-20260414', 'title'       => 'Tough',
			'asin' => 'B0FQNYCRH2', 'author' => 'Confident 88', 'email' => 'reviews-b0fqnycrh2-01@faimala.local', 'date' => '2026-04-24 09:25:00', 'rating' => 5, 'content' => 'These chains feel tough and useful to keep on hand. I would buy more packs for future cutting jobs.' ),
		array( 'source_id' => 'b0fqnycrh2-thomas-hirtleman-20260205', 'title'       => 'Worth the price',
			'asin' => 'B0FQNYCRH2', 'author' => 'Thomas H.', 'email' => 'reviews-b0fqnycrh2-02@faimala.local', 'date' => '2026-04-23 13:45:00', 'rating' => 5, 'content' => 'They are worth the price and stay sharp for normal yard cutting.' ),
		array( 'source_id' => 'b0fqnycrh2-lindsey-20251224', 'title'       => 'Electric Chainsaw Chains 12" (2-Pack)',
			'asin' => 'B0FQNYCRH2', 'author' => 'Lindsey', 'email' => 'reviews-b0fqnycrh2-03@faimala.local', 'date' => '2026-04-22 15:15:00', 'rating' => 5, 'content' => 'The two-pack was easy to change out, fit well, and cut wood smoothly. It works like the original chain and feels like a good value.' ),
		array( 'source_id' => 'b0ggtdwrnn-punkass-matteo-20260426', 'title'       => 'Versatile Powerhouse - A Must-Have for Property Maintenance!',
			'asin' => 'B0GGTDWRNN', 'author' => 'Matteo', 'email' => 'reviews-b0ggtdwrnn-01@faimala.local', 'date' => '2026-04-26 11:30:00', 'rating' => 5, 'content' => 'The 6-inch and 8-inch bars make the kit versatile from light pruning to thicker branches. Automatic lubrication and tool-free tensioning make adjustments easy during use.' ),
		array( 'source_id' => 'b0ggtkhn4g-nutner-20260419', 'title'       => 'Versatile and easy to use chain saw',
			'asin' => 'B0GGTKHN4G', 'author' => 'Nutner', 'email' => 'reviews-b0ggtkhn4g-01@faimala.local', 'date' => '2026-04-19 10:05:00', 'rating' => 4, 'content' => 'It is light enough to use for longer trimming sessions, and the two bar sizes give it useful flexibility for tight spots.' ),
		array( 'source_id' => 'b0ggtkhn4g-bill-moses-20260416', 'title'       => '8-Inch Brushless Cordless Chainsaw',
			'asin' => 'B0GGTKHN4G', 'author' => 'Bill M.', 'email' => 'reviews-b0ggtkhn4g-02@faimala.local', 'date' => '2026-04-21 12:20:00', 'rating' => 5, 'content' => 'It works well for light-duty yard work such as pruning branches and trimming small trees. The brushless motor and dual guide plates make it approachable for home maintenance.' ),
		array( 'source_id' => 'b0ggtkhn4g-natalia-ramos-20260515', 'title'       => 'Lightweight but very powerful',
			'asin' => 'B0GGTKHN4G', 'author' => 'Natalia R.', 'email' => 'reviews-b0ggtkhn4g-03@faimala.local', 'date' => '2026-05-15 14:40:00', 'rating' => 5, 'content' => 'It is lightweight but still powerful for home gardening work. It cut small trunks cleanly and made yard work faster.' ),
	);
}
