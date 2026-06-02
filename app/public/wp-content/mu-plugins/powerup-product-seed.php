<?php
/**
 * Plugin Name: PowerUp Local Product Seed
 * Description: Creates the first Amazon-sourced WooCommerce product set for local launch preparation.
 * Version: 2026.05.30.1
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'powerup_seed_launch_products', 40 );
add_action( 'init', 'powerup_seed_cleanup_legacy_products_once', 80 );
add_action( 'template_redirect', 'powerup_seed_handle_local_cleanup_request' );

function powerup_seed_cleanup_legacy_products_once() {
	$cleanup_version = '2026.05.30.3';

	if ( get_option( 'powerup_legacy_product_cleanup_version' ) === $cleanup_version ) {
		return;
	}

	if ( ! class_exists( 'WC_Product_Simple' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$products = powerup_seed_product_data();
	$trashed  = powerup_seed_trash_legacy_products( $products );
	powerup_seed_normalize_launch_product_categories( $products );
	powerup_seed_refresh_product_category_counts();
	powerup_seed_clear_page_cache();

	update_option( 'powerup_legacy_product_cleanup_version', $cleanup_version, false );
	update_option(
		'powerup_legacy_product_cleanup_last_result',
		array(
			'version'  => $cleanup_version,
			'trashed'  => $trashed,
			'run_time' => current_time( 'mysql' ),
		),
		false
	);
}

function powerup_seed_handle_local_cleanup_request() {
	if ( ! isset( $_GET['powerup_cleanup_legacy_products'] ) || 'local' !== wp_get_environment_type() ) {
		return;
	}

	if ( ! class_exists( 'WC_Product_Simple' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		wp_send_json_error( array( 'message' => 'WooCommerce is not ready.' ), 503 );
	}

	$products = powerup_seed_product_data();
	$trashed  = powerup_seed_trash_legacy_products( $products );
	powerup_seed_normalize_launch_product_categories( $products );
	powerup_seed_refresh_product_category_counts();
	powerup_seed_clear_page_cache();

	wp_send_json_success(
		array(
			'kept_skus' => array_map(
				static function ( $item ) {
					return (string) $item['asin'];
				},
				$products
			),
			'trashed'   => $trashed,
		)
	);
}

function powerup_seed_launch_products() {
	if ( ! class_exists( 'WC_Product_Simple' ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$seed_version = '2026.06.02.3';

	if ( get_option( 'powerup_product_seed_version' ) === $seed_version ) {
		return;
	}

	$category_ids = powerup_seed_product_categories();
	$products     = powerup_seed_product_data();
	$updated      = array();

	foreach ( $products as $index => $item ) {
		$product_id = powerup_seed_upsert_product( $item, $category_ids );

		if ( $product_id ) {
			update_post_meta( $product_id, '_powerup_launch_order', (string) ( $index + 1 ) );
			$updated[] = $item['asin'];
		}
	}

	$trashed = powerup_seed_trash_legacy_products( $products );
	powerup_seed_normalize_launch_product_categories( $products );
	powerup_seed_refresh_product_category_counts();
	powerup_seed_clear_page_cache();

	update_option( 'powerup_product_seed_version', $seed_version, false );
	update_option(
		'powerup_product_seed_last_result',
		array(
			'version'  => $seed_version,
			'updated'  => $updated,
			'trashed'  => $trashed,
			'run_time' => current_time( 'mysql' ),
		),
		false
	);
}

function powerup_seed_trash_legacy_products( array $products ) {
	$keep_skus = array_map(
		static function ( $item ) {
			return (string) $item['asin'];
		},
		$products
	);

	$product_ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$trashed = array();

	foreach ( $product_ids as $product_id ) {
		$product_id = (int) $product_id;
		$product    = wc_get_product( $product_id );
		$sku        = $product instanceof WC_Product ? (string) $product->get_sku() : (string) get_post_meta( $product_id, '_sku', true );

		if ( in_array( $sku, $keep_skus, true ) ) {
			continue;
		}

		$trashed_post = wp_trash_post( $product_id );
		if ( $trashed_post instanceof WP_Post ) {
			$trashed[] = array(
				'id'    => $product_id,
				'title' => get_the_title( $product_id ),
				'sku'   => $sku,
			);
		}
	}

	return $trashed;
}

function powerup_seed_normalize_launch_product_categories( array $products ) {
	$category_ids = powerup_seed_product_categories();

	foreach ( $products as $item ) {
		$product_id = wc_get_product_id_by_sku( (string) $item['asin'] );
		if ( ! $product_id ) {
			continue;
		}

		$product_category_ids = array();
		foreach ( $item['categories'] as $category_slug ) {
			if ( isset( $category_ids[ $category_slug ] ) ) {
				$product_category_ids[] = $category_ids[ $category_slug ];
			}
		}

		if ( ! empty( $product_category_ids ) ) {
			wp_set_object_terms( (int) $product_id, array_values( array_unique( $product_category_ids ) ), 'product_cat', false );
		}
	}
}

function powerup_seed_clear_page_cache() {
	if ( class_exists( 'Breeze_PurgeCache' ) && method_exists( 'Breeze_PurgeCache', 'breeze_cache_flush' ) ) {
		try {
			Breeze_PurgeCache::breeze_cache_flush( true, true, true );
		} catch ( Throwable $error ) {
			// Cloudways may configure Breeze with an unavailable FTP filesystem in CLI requests.
		}
	}

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

function powerup_seed_refresh_product_category_counts() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	wp_update_term_count_now( array_map( 'absint', $terms ), 'product_cat' );
	clean_term_cache( array_map( 'absint', $terms ), 'product_cat' );
}

function powerup_seed_product_categories() {
	$categories = array(
		'chainsaw'           => 'Chainsaw',
		'chainsaw-guide-bar' => 'Chainsaw Guide Bar',
		'chainsaw-chain'     => 'Chainsaw Chain',
	);
	$category_ids = array();

	foreach ( $categories as $slug => $name ) {
		$term = term_exists( $slug, 'product_cat' );

		if ( ! $term ) {
			$term = wp_insert_term(
				$name,
				'product_cat',
				array(
					'slug' => $slug,
				)
			);
		}

		if ( ! is_wp_error( $term ) ) {
			$category_ids[ $slug ] = (int) $term['term_id'];
		}
	}

	return $category_ids;
}

function powerup_seed_upsert_product( array $item, array $category_ids ) {
	$product_id = wc_get_product_id_by_sku( $item['asin'] );
	$product    = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();

	if ( ! $product ) {
		$product = new WC_Product_Simple();
	}

	$product->set_name( $item['title'] );
	$product->set_slug( sanitize_title( $item['title'] . '-' . $item['asin'] ) );
	$product->set_sku( $item['asin'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( $item['price'] );
	$product->set_price( $item['price'] );
	$product->set_manage_stock( false );
	$product->set_stock_status( 'instock' );
	$product->set_sold_individually( false );
	$product->set_short_description( powerup_seed_short_description( $item ) );
	$product->set_description( powerup_seed_long_description( $item ) );

	$product_category_ids = array();
	foreach ( $item['categories'] as $category_slug ) {
		if ( isset( $category_ids[ $category_slug ] ) ) {
			$product_category_ids[] = $category_ids[ $category_slug ];
		}
	}
	$product->set_category_ids( $product_category_ids );

	$image_ids = powerup_seed_image_ids( $item['images'], $item['title'] );
	if ( ! empty( $image_ids ) ) {
		$product->set_image_id( $image_ids[0] );
		$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
	}

	$product_id = $product->save();

	update_post_meta( $product_id, '_powerup_asin', $item['asin'] );
	update_post_meta( $product_id, '_powerup_amazon_url', $item['amazon_url'] );
	update_post_meta( $product_id, '_powerup_launch_product', 'yes' );
	update_post_meta( $product_id, '_product_version', $item['version_note'] );

	if ( $item['featured'] ) {
		wp_set_object_terms( $product_id, 'featured', 'product_visibility', true );
	}

	return $product_id;
}

function powerup_seed_short_description( array $item ) {
	$html  = '<p>' . esc_html( $item['summary'] ) . '</p>';
	$html .= '<ul class="powerup-product-highlights">';

	foreach ( $item['highlights'] as $highlight ) {
		$html .= '<li>' . esc_html( $highlight ) . '</li>';
	}

	$html .= '</ul>';

	return $html;
}

function powerup_seed_long_description( array $item ) {
	$sections = array(
		'Best For'            => $item['best_for'],
		'Compatibility'       => $item['compatibility'],
		'What Is Included'    => $item['included'],
		'Setup Notes'         => $item['notes'],
		'Amazon Source ASIN'  => array( $item['asin'] ),
	);

	$html  = '<div class="powerup-product-detail">';
	$html .= '<p>' . esc_html( $item['detail'] ) . '</p>';

	foreach ( $sections as $heading => $lines ) {
		$html .= '<h3>' . esc_html( $heading ) . '</h3><ul>';

		foreach ( $lines as $line ) {
			$html .= '<li>' . esc_html( $line ) . '</li>';
		}

		$html .= '</ul>';
	}

	if ( ! empty( $item['aplus_images'] ) ) {
		$aplus_class = ! empty( $item['aplus_layout'] ) ? ' powerup-product-aplus--' . sanitize_html_class( $item['aplus_layout'] ) : '';
		$html       .= '<div class="powerup-product-aplus' . esc_attr( $aplus_class ) . '">';

		foreach ( powerup_seed_image_ids( $item['aplus_images'], $item['title'] ) as $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );

			if ( $image_url ) {
				$html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $item['title'] ) . '" loading="lazy" decoding="async">';
			}
		}

		$html .= '</div>';
	}

	$html .= '</div>';

	return $html;
}

function powerup_seed_image_ids( array $filenames, $alt_prefix = '' ) {
	$image_ids = array();

	foreach ( $filenames as $index => $filename ) {
		$image_id = powerup_seed_attachment_id_for_upload( $filename );

		if ( $image_id ) {
			if ( '' !== trim( (string) $alt_prefix ) && '' === trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) ) {
				update_post_meta(
					$image_id,
					'_wp_attachment_image_alt',
					sprintf(
						/* translators: 1: product name, 2: image position. */
						__( '%1$s product image %2$d', 'powerup-theme' ),
						$alt_prefix,
						$index + 1
					)
				);
			}

			$image_ids[] = $image_id;
		}
	}

	return array_values( array_unique( $image_ids ) );
}

function powerup_seed_attachment_id_for_upload( $filename ) {
	global $wpdb;

	$relative_file = false !== strpos( $filename, '/' ) ? ltrim( $filename, '/' ) : '2026/04/' . $filename;
	$existing_id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
			$relative_file
		)
	);

	if ( $existing_id ) {
		return $existing_id;
	}

	$uploads = wp_get_upload_dir();
	$path    = trailingslashit( $uploads['basedir'] ) . $relative_file;

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$filetype      = wp_check_filetype( $path );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_text_field( preg_replace( '/\.[^.]+$/', '', $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$path
	);

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attached_file', $relative_file );

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $path );
	if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	return (int) $attachment_id;
}

function powerup_seed_amazon_image_paths( array $image_ids ) {
	return array_map(
		static function ( $image_id ) {
			return '2026/05/' . $image_id . '.png';
		},
		$image_ids
	);
}

function powerup_seed_product_data() {
	$chainsaw_gallery = array(
		'71j-ADcAUoL',
		'81e02eBhrxL',
		'81t5E9XZuxL',
		'8186lVmrS4L',
		'81d4xNfYeBL',
		'81Fv4Nyb-tL',
		'81gLyT-e-lL',
	);

	$m18_gallery = array(
		'71Z5FRZgOwL',
		'81XmvW50kqL',
		'81iF-2n6WxL',
		'71ShVDSldNL',
		'81gJDGkf1VL',
		'81AcmElazTL',
		'81ohjK6JBBL',
	);

	$dewalt_gallery = array(
		'71WgfIqcIVL',
		'81mlT5Xtc2L',
		'81H0rTe9RzL',
		'7128ZzZbHJL',
		'81d4xNfYeBL',
		'81Fv4Nyb-tL',
		'81Qd5HlitmL',
	);

	$aplus_gallery = array(
		'6cc34d28-df4a-4db0-ae44-dae8c2b63c74.__CR00970600_PT0_SX970_V1___.jpg',
		'133e2216-3bed-48c3-8192-8f31d5258256.__CR00970600_PT0_SX970_V1___.jpg',
		'af022674-26d9-4cfd-bc95-e357e9452819.__CR00970600_PT0_SX970_V1___.jpg',
	);

	return array(
		array(
			'asin'          => 'B0FFGSPWWS',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FFGSPWWS?th=1',
			'title'         => '12 Inch 20V Cordless Electric Chainsaw Kit',
			'price'         => '95.99',
			'categories'    => array( 'chainsaw' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( $chainsaw_gallery ),
			'aplus_images'  => $aplus_gallery,
			'summary'       => 'A ready-to-cut 12 inch cordless chainsaw kit with two 4.0Ah batteries and charger for yard work, pruning, and light wood cutting.',
			'highlights'    => array(
				'12 inch cutting bar for branches, firewood prep, and storm cleanup.',
				'Includes two 4.0Ah lithium batteries plus charger for longer work sessions.',
				'Cordless design helps you work away from outlets without gas maintenance.',
				'Built for homeowners who want fast setup, cleaner storage, and easier handling.',
			),
			'detail'        => 'This 20V chainsaw kit is the best first choice for shoppers who want everything in one box. It is positioned for everyday property maintenance, tree trimming, and seasonal branch cutting without the fuel, pull-start, or extension-cord workflow of older saws.',
			'best_for'      => array( 'Tree trimming', 'Branch pruning', 'Yard cleanup', 'Small log cutting', 'Homeowner firewood prep' ),
			'compatibility' => array( 'Uses the included PowerUp 20V lithium battery system.', 'Not designed for third-party battery packs unless clearly stated by PowerUp.' ),
			'included'      => array( '12 inch cordless chainsaw', 'Two 4.0Ah lithium ion batteries', 'Battery charger', 'Guide bar and chain', 'User setup accessories' ),
			'notes'         => array( 'Add bar and chain oil before cutting.', 'Check chain tension before every session.', 'Wear eye, hand, and leg protection when operating.' ),
			'version_note'  => '20V kit with batteries and charger',
		),
		array(
			'asin'          => 'B0FCM6HXVX',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FCM6HXVX?th=1',
			'title'         => '12 Inch Brushless Chainsaw for Milwaukee M18 Battery',
			'price'         => '75.99',
			'categories'    => array( 'chainsaw' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( $m18_gallery ),
			'aplus_images'  => $aplus_gallery,
			'summary'       => 'A tool-only 12 inch brushless cordless chainsaw made for users who already own Milwaukee M18 18V batteries.',
			'highlights'    => array(
				'Brushless motor delivers efficient cutting with less routine maintenance.',
				'Tool-only format avoids paying again for batteries you already own.',
				'Automatic oiling helps keep the bar and chain moving smoothly.',
				'Safety lock helps reduce accidental startup during handling.',
			),
			'detail'        => 'This model is built for Milwaukee M18 battery users who want to add a compact chainsaw to their existing battery platform. It keeps the independent-site product page focused on compatibility, cutting use cases, and value for customers who already own compatible packs.',
			'best_for'      => array( 'M18 battery owners', 'Tree saw trimming', 'Branch cutting', 'Cordless property maintenance' ),
			'compatibility' => array( 'Compatible with Milwaukee M18 18V style batteries.', 'Battery and charger are not included.', 'Confirm battery fit before purchase if using third-party packs.' ),
			'included'      => array( '12 inch brushless chainsaw body', 'Guide bar and chain', 'Tool setup accessories' ),
			'notes'         => array( 'Tool only: battery and charger sold separately.', 'Fill the oil reservoir before operation.', 'Use fresh chain oil and check tension regularly.' ),
			'version_note'  => 'Tool only for Milwaukee M18 battery platform',
		),
		array(
			'asin'          => 'B0FCLY4DC1',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FCLY4DC1?th=1',
			'title'         => '12 Inch Brushless Chainsaw for DeWalt 20V/60V Battery',
			'price'         => '75.99',
			'categories'    => array( 'chainsaw' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( $dewalt_gallery ),
			'aplus_images'  => $aplus_gallery,
			'summary'       => 'A tool-only 12 inch brushless cordless chainsaw for DeWalt 20V MAX and 60V battery users who need quick pruning and branch cutting.',
			'highlights'    => array(
				'Compatible format for DeWalt 20V MAX and 60V battery owners.',
				'Brushless drive system supports steady cutting performance.',
				'Auto-oiler keeps the chain lubricated through repeated cuts.',
				'Security lock design supports safer startup control.',
			),
			'detail'        => 'This DeWalt-compatible chainsaw gives existing battery-platform users a practical saw body for trimming, branch work, and light wood cutting. The product page should make the tool-only format clear so shoppers understand they need a compatible battery and charger.',
			'best_for'      => array( 'DeWalt battery owners', 'Branch pruning', 'Tree trimming', 'Quick property cleanup' ),
			'compatibility' => array( 'Compatible with DeWalt 20V MAX and 60V style batteries.', 'Battery and charger are not included.', 'Confirm pack style before purchase.' ),
			'included'      => array( '12 inch brushless chainsaw body', 'Guide bar and chain', 'Tool setup accessories' ),
			'notes'         => array( 'Tool only: battery and charger sold separately.', 'Add chain oil before first cut.', 'Let the chain stop fully before setting the tool down.' ),
			'version_note'  => 'Tool only for DeWalt 20V/60V battery platform',
		),
		array(
			'asin'          => 'B0FW3RLQY9',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FW3RLQY9',
			'title'         => '12 Inch Chainsaw Guide Bar 1/4 Inch Pitch',
			'price'         => '15.99',
			'categories'    => array( 'chainsaw-guide-bar' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( array( '41zuFPa25bL' ) ),
			'summary'       => 'A 12 inch replacement guide bar for compatible brushless electric chainsaws using a 1/4 inch, 1.1mm setup.',
			'highlights'    => array(
				'Replacement guide bar for 12 inch electric chainsaw setups.',
				'1/4 inch pitch and 1.1mm gauge specification.',
				'Useful spare for maintenance, repair, or keeping a backup on hand.',
				'Pairs with the matching 12 inch replacement chain.',
			),
			'detail'        => 'This guide bar is positioned as a maintenance accessory for customers already using a compatible PowerUp chainsaw. The page should help buyers confirm size and chain match before checkout.',
			'best_for'      => array( 'Guide bar replacement', 'Accessory upsell', 'Maintenance kits', 'Backup spare parts' ),
			'compatibility' => array( 'Fits compatible 12 inch brushless electric chainsaw models.', 'Requires a matching 1/4 inch pitch, 1.1mm chain.', 'Check original bar length before ordering.' ),
			'included'      => array( 'One 12 inch guide bar' ),
			'notes'         => array( 'Guide bar only.', 'Chain, battery, charger, and chainsaw body are not included.', 'Replace worn bars to reduce cutting drag and uneven wear.' ),
			'version_note'  => '12 inch guide bar accessory',
		),
		array(
			'asin'          => 'B0FW3ZVC7F',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FW3ZVC7F',
			'title'         => '8 Inch Chainsaw Guide Bar 1/4 Inch Pitch',
			'price'         => '12.99',
			'categories'    => array( 'chainsaw-guide-bar' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( array( '518yzsDo9fL' ) ),
			'summary'       => 'An 8 inch replacement guide bar for compatible compact brushless chainsaws using a 1/4 inch, 1.1mm cutting setup.',
			'highlights'    => array(
				'Compact 8 inch bar for smaller cordless chainsaw configurations.',
				'1/4 inch pitch and 1.1mm gauge specification.',
				'Good spare for trimming saws used frequently around the yard.',
				'Helps keep accessory purchases inside the same brand ecosystem.',
			),
			'detail'        => 'This 8 inch guide bar supports the compact chainsaw range and gives shoppers an easy way to maintain their saw after regular pruning work. It should be merchandised near the 8 inch kits and compatible chains.',
			'best_for'      => array( 'Compact saw maintenance', 'Replacement guide bar', 'Accessory bundle building', 'Regular pruning users' ),
			'compatibility' => array( 'Fits compatible 8 inch brushless cordless chainsaw models.', 'Use with matching 1/4 inch pitch, 1.1mm chain.', 'Confirm bar length before purchase.' ),
			'included'      => array( 'One 8 inch guide bar' ),
			'notes'         => array( 'Guide bar only.', 'Chain and chainsaw body are not included.', 'Inspect for wear if cuts begin drifting.' ),
			'version_note'  => '8 inch guide bar accessory',
		),
		array(
			'asin'          => 'B0FQNYCRH2',
			'amazon_url'    => 'https://www.amazon.com/dp/B0FQNYCRH2',
			'title'         => '12 Inch Chainsaw Chain Replacement 2 Pack',
			'price'         => '19.99',
			'categories'    => array( 'chainsaw-chain' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( array( '61fk6crBwJL', '81em8LFv74L' ) ),
			'summary'       => 'A two-piece 12 inch replacement chain set with 1/4 inch pitch and 62 drive links for compatible electric chainsaws.',
			'highlights'    => array(
				'Two replacement chains for longer maintenance intervals.',
				'12 inch size with 1/4 inch pitch and 62 drive links.',
				'Designed for compatible electric chainsaw guide bars.',
				'Helpful add-on for shoppers buying a new saw or guide bar.',
			),
			'detail'        => 'This two-pack replacement chain keeps customers stocked for future cutting sessions and helps the store sell practical aftercare items instead of only tool bodies. The product copy should make pitch and drive-link count easy to verify.',
			'best_for'      => array( 'Chain replacement', 'Routine maintenance', 'Accessory bundling', 'Frequent yard cleanup' ),
			'compatibility' => array( 'Fits compatible 12 inch electric chainsaws.', '1/4 inch pitch, 62 drive links.', 'Match chain specs with your existing guide bar before purchase.' ),
			'included'      => array( 'Two 12 inch chains' ),
			'notes'         => array( 'Chain only.', 'Guide bar and chainsaw body are not included.', 'Sharpen or replace chains when cutting slows or smoke appears.' ),
			'version_note'  => '12 inch replacement chain two-pack',
		),
		array(
			'asin'          => 'B0GGTDWRNN',
			'amazon_url'    => 'https://www.amazon.com/dp/B0GGTDWRNN',
			'title'         => '8 Inch Brushless Chainsaw Kit for Milwaukee M18 Battery',
			'price'         => '129.99',
			'categories'    => array( 'chainsaw' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( array( '81YwQOPuGyL', '81G-M50BWtL', '81WNvdsr2fL', '81YX6iRkwGL', '81xMk1S7GKL', '81sKBKIS0JL', '81sOowhxYBL', '814zHIB1nZL' ) ),
			'aplus_images'  => array(
				'2026/06/b0ggtdwrnn-aplus-01.jpg',
				'2026/06/b0ggtdwrnn-aplus-02.jpg',
				'2026/06/b0ggtdwrnn-aplus-03-1.jpg',
				'2026/06/b0ggtdwrnn-aplus-03-2.jpg',
				'2026/06/b0ggtdwrnn-aplus-03-3.jpg',
				'2026/06/b0ggtdwrnn-aplus-03-4.jpg',
				'2026/06/b0ggtdwrnn-aplus-04.jpg',
				'2026/06/b0ggtdwrnn-aplus-05.jpg',
				'2026/06/b0ggtdwrnn-aplus-06.jpg',
			),
			'aplus_layout'  => 'feature-grid',
			'summary'       => 'Compact 8 inch brushless cordless chainsaw kit with 6 inch and 8 inch guide plates, Milwaukee M18 battery compatibility, and one included 4.0Ah battery.',
			'highlights'    => array(
				'Includes 6 inch and 8 inch guide plates for flexible trimming work.',
				'Compatible with Milwaukee M18 18V battery platform.',
				'Tool-free chain tensioning makes setup and adjustment easier.',
				'Automatic lubrication system supports smoother repeated cutting.',
			),
			'detail'        => 'This compact kit is suited for users who want a lighter saw for branch trimming and garden maintenance, while still keeping compatibility with the Milwaukee M18 battery ecosystem. It should sit near both the 12 inch M18 tool-only saw and accessory guide bars.',
			'best_for'      => array( 'Compact trimming', 'Garden pruning', 'M18 platform users', 'One-handed branch cleanup where appropriate' ),
			'compatibility' => array( 'Compatible with Milwaukee M18 18V style batteries.', 'Includes one 4.0Ah battery for launch bundle positioning.', 'Confirm charger requirements before purchase if needed.' ),
			'included'      => array( '8 inch brushless cordless chainsaw', '6 inch guide plate', '8 inch guide plate', '4.0Ah battery', 'Chain and setup accessories' ),
			'notes'         => array( 'Add chain oil before first use.', 'Use the correct guide plate and chain pairing.', 'Do not force the saw through oversized logs.' ),
			'version_note'  => '8 inch kit for Milwaukee M18 battery platform',
		),
		array(
			'asin'          => 'B0GGTKHN4G',
			'amazon_url'    => 'https://www.amazon.com/dp/B0GGTKHN4G',
			'title'         => '8 Inch Brushless Chainsaw Kit for DeWalt 20V MAX Battery',
			'price'         => '129.99',
			'categories'    => array( 'chainsaw' ),
			'featured'      => true,
			'images'        => powerup_seed_amazon_image_paths( array( '81uh0l4R0WL', '71NTHE5ehBL', '816CLjqkcJL', '81pB1hTORUL', '81h3CLEiwzL', '81sN8oe3DJL', '81-ixT0AwAL', '81XEexRNOAL' ) ),
			'aplus_images'  => array(
				'2026/06/b0ggtkhn4g-aplus-01.jpg',
				'2026/06/b0ggtkhn4g-aplus-02.jpg',
				'2026/06/b0ggtkhn4g-aplus-03-1.jpg',
				'2026/06/b0ggtkhn4g-aplus-03-2.jpg',
				'2026/06/b0ggtkhn4g-aplus-03-3.jpg',
				'2026/06/b0ggtkhn4g-aplus-03-4.jpg',
				'2026/06/b0ggtkhn4g-aplus-04.jpg',
				'2026/06/b0ggtkhn4g-aplus-05.jpg',
				'2026/06/b0ggtkhn4g-aplus-06.jpg',
			),
			'aplus_layout'  => 'feature-grid',
			'summary'       => 'Compact 8 inch brushless cordless chainsaw kit with 6 inch and 8 inch guide plates, DeWalt 20V MAX battery compatibility, and one included 5.0Ah battery.',
			'highlights'    => array(
				'Compact brushless saw for quick trimming and pruning jobs.',
				'Compatible with DeWalt 20V MAX battery platform.',
				'Includes 6 inch and 8 inch guide plates for different cut sizes.',
				'Tool-free chain tensioning and automatic lubrication support easier maintenance.',
			),
			'detail'        => 'This DeWalt-compatible compact kit is designed for shoppers who want a smaller cordless saw with a battery included. The independent-store copy should highlight the bundle value, compatibility, and easy maintenance features without relying on Amazon review language.',
			'best_for'      => array( 'DeWalt platform users', 'Compact pruning', 'Garden cleanup', 'Light branch cutting' ),
			'compatibility' => array( 'Compatible with DeWalt 20V MAX style batteries.', 'Includes one 5.0Ah battery for launch bundle positioning.', 'Confirm charger requirements before purchase if needed.' ),
			'included'      => array( '8 inch brushless cordless chainsaw', '6 inch guide plate', '8 inch guide plate', '5.0Ah battery', 'Chain and setup accessories' ),
			'notes'         => array( 'Fill oil reservoir before cutting.', 'Match chain and guide plate before operation.', 'Keep hands clear until the chain fully stops.' ),
			'version_note'  => '8 inch kit for DeWalt 20V MAX battery platform',
		),
	);
}
