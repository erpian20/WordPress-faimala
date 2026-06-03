<?php
/**
 * Plugin Name: PowerUp Rating Link Contrast Fix
 * Description: Keeps the product summary review-count link readable on single product pages.
 * Version: 2026.06.04.1
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		?>
		<style id="powerup-rating-link-contrast-fix">
			body.single-product div.product .summary.entry-summary .woocommerce-product-rating {
				display: flex !important;
				align-items: center !important;
				gap: 8px !important;
				line-height: 1.1 !important;
			}

			body.single-product div.product .summary.entry-summary .woocommerce-product-rating .star-rating {
				color: #ffb400 !important;
				display: inline-flex !important;
				align-items: center !important;
				line-height: 1 !important;
				opacity: 1 !important;
				vertical-align: middle !important;
			}

			body.single-product div.product .summary.entry-summary .woocommerce-product-rating a.woocommerce-review-link {
				color: #182234 !important;
				display: inline-flex !important;
				align-items: center !important;
				font-size: 15px !important;
				font-weight: 900 !important;
				line-height: 1.15 !important;
				opacity: 1 !important;
				text-decoration-color: rgba(24, 34, 52, 0.55) !important;
				text-decoration-thickness: 1px !important;
				text-shadow: none !important;
			}

			body.single-product div.product .summary.entry-summary .woocommerce-product-rating a.woocommerce-review-link:hover,
			body.single-product div.product .summary.entry-summary .woocommerce-product-rating a.woocommerce-review-link:focus {
				color: #b34700 !important;
				text-decoration-color: currentColor !important;
			}
		</style>
		<?php
	},
	1000
);
