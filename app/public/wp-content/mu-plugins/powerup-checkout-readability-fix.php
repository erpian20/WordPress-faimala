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

		.woocommerce-checkout .wc-block-checkout__no-payment-methods-notice {
			display: none !important;
		}

		.powerup-payment-pending-notice {
			margin: 0 0 1.25rem;
			padding: 1rem;
			border: 1px solid #f0d8c4;
			border-radius: 12px;
			background: linear-gradient(180deg, #fff7ea 0%, #ffffff 100%);
			color: #1f2937;
			box-shadow: 0 12px 28px rgba(17, 24, 39, 0.08);
		}

		.powerup-payment-pending-notice h2 {
			margin: 0 0 0.4rem;
			color: #111827;
			font-size: clamp(1.35rem, 2.4vw, 1.8rem);
			line-height: 1.1;
		}

		.powerup-payment-pending-notice p {
			margin: 0;
			color: #4b5563;
			line-height: 1.55;
		}

		.powerup-payment-pending-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 0.55rem;
			margin-top: 0.85rem;
		}

		.powerup-payment-pending-actions a {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-height: 42px;
			padding: 0.55rem 0.95rem;
			border-radius: 999px;
			font-weight: 800;
			text-decoration: none;
		}

		.powerup-payment-pending-actions a:first-child {
			background: #ff6a00;
			color: #111827;
		}

		.powerup-payment-pending-actions a:not(:first-child) {
			background: #111827;
			color: #ffffff;
		}

		@media (max-width: 520px) {
			.powerup-payment-pending-actions a {
				flex: 1 1 100%;
			}
		}
	</style>
	<?php
}
add_action( 'wp_head', 'powerup_checkout_readability_fix_output', 35 );

function powerup_checkout_payment_pending_notice_output() {
	if ( ! is_checkout() || ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ) {
		return;
	}

	$amazon_url   = 'https://www.amazon.com/s?k=PowerUp+cordless+chainsaw';
	$whatsapp_url = function_exists( 'powerup_theme_get_whatsapp_chat_url' )
		? powerup_theme_get_whatsapp_chat_url( 'Hi, I would like help placing a PowerUp order.' )
		: home_url( '/contact-us/' );
	$email_url    = function_exists( 'powerup_theme_get_support_mailto_url' )
		? powerup_theme_get_support_mailto_url( 'PowerUp order support' )
		: 'mailto:randian5757@gmail.com?subject=PowerUp%20order%20support';
	?>
	<div id="powerup-payment-pending-notice" class="powerup-payment-pending-notice" hidden>
		<h2><?php esc_html_e( 'Online checkout is being prepared.', 'powerup-theme' ); ?></h2>
		<p><?php esc_html_e( 'You can still order through Amazon or contact support for availability while online payment setup is being completed.', 'powerup-theme' ); ?></p>
		<div class="powerup-payment-pending-actions">
			<a href="<?php echo esc_url( $amazon_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Shop on Amazon', 'powerup-theme' ); ?></a>
			<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact on WhatsApp', 'powerup-theme' ); ?></a>
			<a href="<?php echo esc_url( $email_url ); ?>"><?php esc_html_e( 'Email Support', 'powerup-theme' ); ?></a>
		</div>
	</div>
	<script>
	(function () {
		var notice = document.getElementById('powerup-payment-pending-notice');
		if (!notice) {
			return;
		}

		var placeNotice = function () {
			var checkout = document.querySelector('.wc-block-checkout, form.checkout, article.feature-card');
			if (!checkout || !checkout.parentNode) {
				return false;
			}

			if (notice.parentNode !== checkout.parentNode || notice.nextElementSibling !== checkout) {
				checkout.parentNode.insertBefore(notice, checkout);
			}
			notice.hidden = false;

			document.querySelectorAll('.wc-block-components-notice-banner, .woocommerce-error').forEach(function (banner) {
				var text = (banner.textContent || '').toLowerCase();
				if (text.indexOf('no payment methods') !== -1 || text.indexOf('payment methods available') !== -1) {
					banner.style.display = 'none';
				}
			});
			return true;
		};

		document.addEventListener('DOMContentLoaded', placeNotice);
		window.setTimeout(placeNotice, 600);
		window.setTimeout(placeNotice, 1600);
		placeNotice();
	}());
	</script>
	<?php
}
add_action( 'wp_footer', 'powerup_checkout_payment_pending_notice_output', 30 );
