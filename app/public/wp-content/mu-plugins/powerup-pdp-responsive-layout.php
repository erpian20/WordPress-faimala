<?php
/**
 * Plugin Name: PowerUp PDP Responsive Layout
 * Description: Keeps product detail pages readable and renders uncropped related product images.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function powerup_pdp_responsive_layout_styles() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  $css = <<<'CSS'
.single-product div.product {
  display: flex !important;
  flex-direction: column !important;
  flex-wrap: nowrap !important;
  gap: 12px !important;
}
.single-product div.product > .powerup-pdp-media-stack,
.single-product .powerup-pdp-media-stack {
  display: contents !important;
}
.single-product .powerup-pdp-media-stack > .powerup-amz-gallery {
  order: 1 !important;
}
.single-product div.product > .summary.entry-summary {
  order: 2 !important;
  width: 100% !important;
  max-width: none !important;
  min-width: 0 !important;
  float: none !important;
  margin-top: 0 !important;
}
.single-product .powerup-pdp-media-stack > .powerup-pdp-about-item-panel {
  order: 3 !important;
  display: block !important;
  margin-top: 0 !important;
}
.single-product div.product > :not(.powerup-pdp-media-stack):not(.summary.entry-summary),
.single-product div.product > .powerup-pdp-media-stack > :not(.powerup-amz-gallery):not(.powerup-pdp-about-item-panel),
.single-product div.product > .woocommerce-tabs,
.single-product div.product > .related.products,
.single-product div.product > .up-sells,
.single-product div.product > .cross-sells {
  order: 4 !important;
}
.single-product .related.products ul.products li.product {
  display: flex;
  flex-direction: column;
}
.single-product .related.products ul.products li.product .woocommerce-LoopProduct-link {
  display: flex;
  flex: 1;
  flex-direction: column;
}
.single-product .related.products ul.products li.product .powerup-related-product-image {
  display: block;
  width: 100%;
  height: auto;
  object-fit: contain;
}
.single-product .related.products ul.products li.product .button {
  margin-top: auto;
}
@media (max-width: 1024px) {
  .single-product .related.products ul.products {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 640px) {
  .single-product .related.products ul.products {
    grid-template-columns: 1fr;
  }
}
CSS;

  wp_add_inline_style( 'powerup-theme-pdp-reference-layout', $css );
}
add_action( 'wp_enqueue_scripts', 'powerup_pdp_responsive_layout_styles', 99 );

function powerup_pdp_render_related_product_image_uncropped( $html, $product, $size, $attr, $placeholder ) {
  if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'wc_get_loop_prop' ) || 'related' !== wc_get_loop_prop( 'name' ) ) {
    return $html;
  }

  if ( ! $product instanceof WC_Product || ! $product->get_image_id() ) {
    return $html;
  }

  $image_id  = $product->get_image_id();
  $image_alt = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );

  return wp_get_attachment_image(
    $image_id,
    'medium_large',
    false,
    array(
      'alt'      => '' !== $image_alt ? $image_alt : $product->get_name(),
      'class'    => 'attachment-medium_large size-medium_large powerup-related-product-image',
      'decoding' => 'async',
      'loading'  => 'lazy',
      'sizes'    => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw',
    )
  );
}
add_filter( 'woocommerce_product_get_image', 'powerup_pdp_render_related_product_image_uncropped', 30, 5 );
