<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_SEO {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_filter( 'wp_get_attachment_image_attributes', array( $this, 'auto_fill_image_alt' ), 20, 2 );
    add_action( 'wp_head', array( $this, 'output_hreflang_tags' ), 5 );
    add_action( 'wp_head', array( $this, 'output_product_schema' ), 35 );
  }

  public function auto_fill_image_alt( $attr, $attachment ) {
    if ( ! empty( $attr['alt'] ) ) {
      return $attr;
    }

    $title = get_the_title( $attachment->ID );
    if ( $title ) {
      $attr['alt'] = wp_strip_all_tags( $title );
    }

    return $attr;
  }

  public function output_hreflang_tags() {
    if ( is_admin() || is_feed() || is_404() ) {
      return;
    }

    if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_languages_list' ) && is_singular() ) {
      $post_id = get_queried_object_id();
      $langs = call_user_func( 'pll_languages_list', array( 'fields' => 'slug' ) );
      if ( is_array( $langs ) ) {
        foreach ( $langs as $slug ) {
          $translated_id = call_user_func( 'pll_get_post', $post_id, $slug );
          if ( $translated_id ) {
            echo '<link rel="alternate" hreflang="' . esc_attr( $slug ) . '" href="' . esc_url( get_permalink( $translated_id ) ) . '" />' . "\n";
          }
        }
      }
      return;
    }

    if ( function_exists( 'icl_get_languages' ) && is_singular() ) {
      $languages = call_user_func( 'icl_get_languages', 'skip_missing=0' );
      if ( is_array( $languages ) ) {
        foreach ( $languages as $lang ) {
          if ( ! empty( $lang['language_code'] ) && ! empty( $lang['url'] ) ) {
            echo '<link rel="alternate" hreflang="' . esc_attr( $lang['language_code'] ) . '" href="' . esc_url( $lang['url'] ) . '" />' . "\n";
          }
        }
      }
    }
  }

  public function output_product_schema() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
      return;
    }

    global $product;
    if ( ! $product instanceof WC_Product ) {
      return;
    }

    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

    $schema = array(
      '@context' => 'https://schema.org',
      '@type'    => 'Product',
      'name'     => $product->get_name(),
      'sku'      => $product->get_sku(),
      'description' => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ),
      'image'    => $image_url ? array( $image_url ) : array(),
      'offers'   => array(
        '@type'         => 'Offer',
        'priceCurrency' => get_woocommerce_currency(),
        'price'         => (string) wc_get_price_to_display( $product ),
        'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => get_permalink( $product->get_id() ),
      ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
  }
}
