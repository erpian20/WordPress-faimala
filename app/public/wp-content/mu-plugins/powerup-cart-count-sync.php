<?php
/**
 * Plugin Name: PowerUp Cart Count Sync
 * Description: Keeps the header cart count aligned with the WooCommerce cart.
 * Version: 2026.05.31.1
 */

defined( 'ABSPATH' ) || exit;

function powerup_cart_count_sync_output() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	?>
	<script id="powerup-cart-count-sync">
		(function () {
			'use strict';

			var timer;
			var cartEndpoint = <?php echo wp_json_encode( rest_url( 'wc/store/v1/cart' ) ); ?>;

			function updateBadges(count) {
				document.querySelectorAll('.cart-count').forEach(function (badge) {
					if (badge.textContent !== String(count)) {
						badge.textContent = String(count);
					}
				});
			}

			function refreshCartCount() {
				window.clearTimeout(timer);
				timer = window.setTimeout(function () {
					window.fetch(cartEndpoint, {
						credentials: 'same-origin',
						cache: 'no-store',
						headers: {
							'Accept': 'application/json'
						}
					})
						.then(function (response) {
							if (!response.ok) {
								throw new Error('Unable to refresh cart count');
							}

							return response.json();
						})
						.then(function (cart) {
							var count = Array.isArray(cart.items)
								? cart.items.reduce(function (total, item) {
									return total + Number(item.quantity || 0);
								}, 0)
								: 0;

							updateBadges(count);
						})
						.catch(function () {
							// Keep the server-rendered count if the cart endpoint is unavailable.
						});
				}, 120);
			}

			document.addEventListener('DOMContentLoaded', refreshCartCount);
			window.addEventListener('pageshow', refreshCartCount);
			document.body.addEventListener('wc-blocks_added_to_cart', refreshCartCount);
			document.body.addEventListener('wc-blocks_removed_from_cart', refreshCartCount);

			if (window.jQuery) {
				window.jQuery(document.body).on(
					'added_to_cart removed_from_cart updated_cart_totals wc_fragments_refreshed',
					refreshCartCount
				);
			}

			if (window.MutationObserver) {
				var cartRegion = document.querySelector('.wp-block-woocommerce-cart, .wc-block-cart');

				if (cartRegion) {
					new MutationObserver(refreshCartCount).observe(cartRegion, {
						childList: true,
						subtree: true
					});
				}
			}
		}());
	</script>
	<?php
}
add_action( 'wp_footer', 'powerup_cart_count_sync_output', 90 );
