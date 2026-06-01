<?php
/**
 * Plugin Name: PowerUp WooCommerce Readability Fix
 * Description: Improves text contrast for WooCommerce cart, checkout, and account pages.
 * Version: 2026.05.31.3
 */

defined( 'ABSPATH' ) || exit;

function powerup_checkout_readability_fix_output() {
	if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		return;
	}
	?>
	<style id="powerup-checkout-readability-fix">
		.woocommerce-cart article.feature-card,
		.woocommerce-checkout article.feature-card {
			background: #ffffff;
			color: #1f2937;
		}

		.woocommerce-cart .wc-block-cart,
		.woocommerce-cart .wc-block-cart h1,
		.woocommerce-cart .wc-block-cart h2,
		.woocommerce-cart .wc-block-cart h3,
		.woocommerce-cart .wc-block-cart label,
		.woocommerce-cart .wc-block-cart p,
		.woocommerce-cart .wc-block-cart span,
		.woocommerce-cart .wc-block-cart a,
		.woocommerce-cart .wc-block-cart button,
		.woocommerce-checkout .wc-block-checkout,
		.woocommerce-checkout .wc-block-checkout h1,
		.woocommerce-checkout .wc-block-checkout h2,
		.woocommerce-checkout .wc-block-checkout h3,
		.woocommerce-checkout .wc-block-checkout label,
		.woocommerce-checkout .wc-block-checkout p,
		.woocommerce-checkout .wc-block-components-title,
		.woocommerce-checkout .wc-block-components-checkout-step,
		.woocommerce-checkout .wc-block-components-order-summary,
		.woocommerce-checkout .wc-block-components-totals-wrapper,
		.woocommerce-checkout .wc-block-components-totals-item,
		.woocommerce-checkout .wc-block-components-product-name {
			color: #1f2937;
		}

		.woocommerce-cart .wc-block-components-product-metadata,
		.woocommerce-cart .wc-block-components-product-metadata p,
		.woocommerce-cart .wc-block-components-product-details,
		.woocommerce-cart .wc-block-components-product-details li,
		.woocommerce-cart .wc-block-cart-item__remove-link,
		.woocommerce-cart .wc-block-components-totals-item__description {
			color: #4b5563;
		}

		.woocommerce-cart .wc-block-components-button.wc-block-cart__submit-button {
			background: #ff6a00;
			color: #111827;
		}

		.woocommerce-cart .wc-block-components-button.wc-block-cart__submit-button:hover {
			background: #e85f00;
			color: #111827;
		}

		.woocommerce-account article.feature-card {
			background: #ffffff;
			color: #1f2937;
		}

		.woocommerce-account article.feature-card h1,
		.woocommerce-account article.feature-card h2,
		.woocommerce-account article.feature-card h3,
		.woocommerce-account article.feature-card label,
		.woocommerce-account article.feature-card p,
		.woocommerce-account article.feature-card a:not(.button),
		.woocommerce-account article.feature-card .woocommerce-MyAccount-content,
		.woocommerce-account article.feature-card .woocommerce-MyAccount-navigation-link a {
			color: #1f2937;
		}

		.woocommerce-account article.feature-card input,
		.woocommerce-account article.feature-card select,
		.woocommerce-account article.feature-card textarea {
			background: #ffffff;
			border-color: #6b7280;
			color: #111827;
		}

		.woocommerce-account article.feature-card .woocommerce-message {
			background: #f0fdf4;
			border-color: #86b817;
			color: #166534;
		}

		.woocommerce-account article.feature-card .woocommerce-message a.button {
			background: #ff6a00;
			color: #111827;
		}

		.woocommerce-checkout .wc-block-components-text-input input,
		.woocommerce-checkout .wc-block-components-combobox .wc-block-components-combobox-control input,
		.woocommerce-checkout .wc-block-components-address-form select {
			background: #ffffff;
			border-color: #6b7280;
			color: #111827;
		}

		.woocommerce-checkout .wc-block-components-text-input input::placeholder,
		.woocommerce-checkout .wc-block-components-combobox .wc-block-components-combobox-control input::placeholder {
			color: #6b7280;
			opacity: 1;
		}

		.woocommerce-checkout .wc-block-checkout__guest-checkout-notice,
		.woocommerce-checkout .wc-block-components-checkbox__label,
		.woocommerce-checkout .wc-block-components-totals-item__description,
		.woocommerce-checkout .wc-block-components-product-metadata {
			color: #4b5563;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'powerup_checkout_readability_fix_output', 35 );
