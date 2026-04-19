<?php
/**
 * WooCommerce product archive template override.
 *
 * Route shop archive through the custom Shop layout for 1:1 visual parity.
 *
 * @package PowerUp_Theme
 */

$template = locate_template( 'page-shop.php', false, false );

if ( ! empty( $template ) ) {
  include $template;
  return;
}

get_header();

if ( function_exists( 'woocommerce_content' ) ) {
  woocommerce_content();
}

get_footer();
