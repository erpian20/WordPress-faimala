<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Marketplace {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_marketplace_product_fields' ) );
    add_action( 'woocommerce_process_product_meta', array( $this, 'save_marketplace_product_fields' ) );
    add_action( 'woocommerce_single_product_summary', array( $this, 'render_marketplace_buttons' ), 31 );
    add_filter( 'woocommerce_get_price_html', array( $this, 'single_product_compare_price_html' ), 20, 2 );
  }

  public static function get_platforms() {
    return array(
      'amazon' => array(
        'label'       => 'Amazon',
        'meta_key'    => '_powerup_amazon_url',
        'placeholder' => 'https://www.amazon.com/dp/xxxx',
        'class'       => 'marketplace-amazon',
      ),
    );
  }

  public function render_marketplace_product_fields() {
    echo '<div class="options_group">';

    foreach ( self::get_platforms() as $platform ) {
      woocommerce_wp_text_input(
        array(
          'id'          => $platform['meta_key'],
          'label'       => sprintf( __( '%s URL', 'powerup-theme' ), $platform['label'] ),
          'placeholder' => $platform['placeholder'],
          'desc_tip'    => true,
          'description' => sprintf( __( 'Custom %s product URL for this item.', 'powerup-theme' ), $platform['label'] ),
          'type'        => 'url',
        )
      );
    }

    echo '</div>';
  }

  public function save_marketplace_product_fields( $product_id ) {
    foreach ( self::get_platforms() as $platform ) {
      $meta_key = $platform['meta_key'];
      $value    = isset( $_POST[ $meta_key ] ) ? esc_url_raw( wp_unslash( $_POST[ $meta_key ] ) ) : '';
      update_post_meta( $product_id, $meta_key, $value );
    }
  }

  private function build_default_marketplace_links( $product ) {
    if ( ! $product instanceof WC_Product ) {
      return array();
    }

    $product_name = trim( wp_strip_all_tags( $product->get_name() ) );
    if ( '' === $product_name ) {
      return array();
    }

    $query = rawurlencode( preg_replace( '/\s+/', ' ', $product_name ) );

    return array(
      array(
        'key'   => 'amazon',
        'label' => 'Amazon',
        'url'   => 'https://www.amazon.com/s?k=' . $query,
        'class' => 'marketplace-amazon',
      ),
    );
  }

  public function render_marketplace_buttons() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
      return;
    }

    global $product;
    if ( ! $product instanceof WC_Product ) {
      return;
    }

    $platform_links = array();
    $is_fallback    = false;

    foreach ( self::get_platforms() as $key => $platform ) {
      $url = trim( (string) get_post_meta( $product->get_id(), $platform['meta_key'], true ) );
      if ( '' === $url ) {
        continue;
      }

      $platform_links[] = array(
        'key'   => $key,
        'label' => $platform['label'],
        'url'   => $url,
        'class' => $platform['class'],
      );
    }

    if ( empty( $platform_links ) ) {
      $platform_links = $this->build_default_marketplace_links( $product );
      $is_fallback    = ! empty( $platform_links );
    }

    if ( empty( $platform_links ) ) {
      return;
    }

    echo '<div class="powerup-marketplace-box">';
    echo '<p class="powerup-marketplace-title">' . esc_html__( 'Buy on Amazon', 'powerup-theme' ) . '</p>';
    if ( $is_fallback ) {
      echo '<p class="powerup-marketplace-note">' . esc_html__( 'Showing marketplace search results for this product. Add exact platform URLs in product settings to replace these links.', 'powerup-theme' ) . '</p>';
    }
    echo '<div class="powerup-marketplace-links">';

    foreach ( $platform_links as $platform ) {
      echo '<a class="marketplace-btn ' . esc_attr( $platform['class'] ) . '" href="' . esc_url( $platform['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $platform['label'] ) . '</a>';
    }

    echo '</div>';
    echo '</div>';
  }

  public function single_product_compare_price_html( $price_html, $product ) {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
      return $price_html;
    }

    if ( ! $product instanceof WC_Product ) {
      return $price_html;
    }

    if ( false !== strpos( $price_html, '<del' ) ) {
      return $price_html;
    }

    $current_price = (float) $product->get_price();
    if ( $current_price <= 0 ) {
      return $price_html;
    }

    $fixed_discount_percent = 30.0;
    $compare_price = round( $current_price / ( 1 - ( $fixed_discount_percent / 100 ) ), 2 );

    if ( $compare_price <= $current_price ) {
      return $price_html;
    }

    $compare_html = '<del class="powerup-compare-price">' . wc_price( $compare_price ) . '</del>';
    $current_html = '<ins class="powerup-current-price">' . wc_price( $current_price ) . '</ins>';

    return '<span class="price powerup-price-with-compare">' . $compare_html . ' ' . $current_html . '</span>';
  }
}

if ( ! function_exists( 'powerup_get_marketplace_platforms' ) ) {
  function powerup_get_marketplace_platforms() {
    return PowerUp_B2C_Marketplace::get_platforms();
  }
}
