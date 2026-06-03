<?php
/**
 * Keep the homepage "Why Choose Us" benefits compact on mobile.
 *
 * @package PowerUp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$css = '
@media (max-width: 680px) {
  .home-why-banner__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.75rem 0.5rem !important;
    max-width: 520px;
    margin-left: auto;
    margin-right: auto;
  }

  .home-why-banner__item {
    min-width: 0;
    padding: 0 0.15rem;
  }

  .home-why-banner__item h3 {
    font-size: clamp(1rem, 4.4vw, 1.18rem) !important;
    line-height: 1.08 !important;
  }

  .home-why-banner__item p {
    font-size: clamp(0.76rem, 3.55vw, 0.9rem) !important;
    line-height: 1.22 !important;
    overflow-wrap: anywhere;
  }

  .home-why-banner__icon {
    width: 58px !important;
    height: 58px !important;
    margin-bottom: 0.35rem !important;
  }

  .home-why-banner__svg,
  .home-why-banner__icon-image {
    width: 46px !important;
    height: 46px !important;
  }
}
';

		wp_register_style( 'powerup-home-why-mobile-layout', false, array(), '2026.06.03.1' );
		wp_enqueue_style( 'powerup-home-why-mobile-layout' );
		wp_add_inline_style( 'powerup-home-why-mobile-layout', $css );
	},
	30
);
