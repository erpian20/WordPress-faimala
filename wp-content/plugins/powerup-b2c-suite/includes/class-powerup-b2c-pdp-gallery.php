<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_PDP_Gallery {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'init', array( $this, 'replace_default_product_gallery' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
  }

  public function replace_default_product_gallery() {
    if ( ! class_exists( 'WooCommerce' ) ) {
      return;
    }

    remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
    add_action( 'woocommerce_before_single_product_summary', array( $this, 'render_amazon_like_gallery' ), 20 );
  }

  public function enqueue_assets() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
      return;
    }

    wp_enqueue_style(
      'powerup-b2c-pdp-gallery',
      POWERUP_B2C_URL . 'assets/css/pdp-gallery.css',
      array(),
      POWERUP_B2C_VERSION
    );

    wp_enqueue_script(
      'powerup-b2c-pdp-gallery',
      POWERUP_B2C_URL . 'assets/js/pdp-gallery.js',
      array(),
      POWERUP_B2C_VERSION,
      true
    );
  }

  public function render_amazon_like_gallery() {
    global $product;

    if ( ! $product instanceof WC_Product ) {
      return;
    }

    $main_image_id = $product->get_image_id();
    $gallery_ids   = $product->get_gallery_image_ids();

    $image_ids = array();
    if ( $main_image_id ) {
      $image_ids[] = $main_image_id;
    }

    if ( ! empty( $gallery_ids ) && is_array( $gallery_ids ) ) {
      foreach ( $gallery_ids as $gallery_id ) {
        if ( ! in_array( $gallery_id, $image_ids, true ) ) {
          $image_ids[] = $gallery_id;
        }
      }
    }

    $image_items = array();

    foreach ( $image_ids as $image_id ) {
      $thumb_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
      $full_url  = wp_get_attachment_image_url( $image_id, 'large' );
      $zoom_url  = wp_get_attachment_image_url( $image_id, 'full' );
      $alt       = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

      if ( ! $thumb_url || ! $full_url ) {
        continue;
      }

      $image_items[] = array(
        'thumb' => $thumb_url,
        'full'  => $full_url,
        'zoom'  => $zoom_url ? $zoom_url : $full_url,
        'alt'   => $alt,
      );
    }

    // When product has very few images, append demo tool photos so the full gallery UI can be previewed.
    if ( count( $image_items ) < 4 ) {
      $needed = 4 - count( $image_items );
      $demos  = $this->get_demo_gallery_images();

      for ( $i = 0; $i < $needed && isset( $demos[ $i ] ); $i++ ) {
        $image_items[] = $demos[ $i ];
      }
    }

    if ( empty( $image_items ) ) {
      echo '<div class="powerup-amz-gallery">';
      echo '<div class="powerup-amz-main"><div class="powerup-amz-empty">' . esc_html__( 'No product image available.', 'powerup-b2c' ) . '</div></div>';
      echo '</div>';
      return;
    }

    $first_url  = $image_items[0]['full'];
    $first_zoom = $image_items[0]['zoom'];
    $first_alt  = $image_items[0]['alt'];

    echo '<div class="powerup-amz-gallery" data-powerup-pdp-gallery="1">';

    echo '<div class="powerup-amz-thumbs" role="list">';
    foreach ( $image_items as $index => $image_item ) {
      $thumb_url = $image_item['thumb'];
      $full_url  = $image_item['full'];
      $zoom_url  = $image_item['zoom'];
      $alt       = $image_item['alt'];

      $is_active = 0 === $index;
      $button_class = 'powerup-amz-thumb' . ( $is_active ? ' is-active' : '' );

      echo '<button type="button" class="' . esc_attr( $button_class ) . '" data-image="' . esc_url( $full_url ) . '" data-zoom="' . esc_url( $zoom_url ) . '" data-alt="' . esc_attr( $alt ) . '" aria-label="' . esc_attr__( 'Switch product image', 'powerup-b2c' ) . '">';
      echo '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />';
      echo '</button>';
    }
    echo '</div>';

    if ( ! $first_zoom ) {
      $first_zoom = $first_url;
    }

    echo '<div class="powerup-amz-main" data-zoom-image="' . esc_url( $first_zoom ) . '">';
    echo '<img class="powerup-amz-main-image" src="' . esc_url( $first_url ) . '" alt="' . esc_attr( $first_alt ) . '" />';
    echo '<span class="powerup-amz-lens" aria-hidden="true"></span>';
    echo '<div class="powerup-amz-zoom-pane" aria-hidden="true"></div>';
    echo '</div>';

    echo '</div>';
  }

  private function get_demo_gallery_images() {
    return array(
      array(
        'thumb' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=180&q=80',
        'full'  => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=1200&q=85',
        'zoom'  => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=1800&q=90',
        'alt'   => 'Cordless power saw demo image',
      ),
      array(
        'thumb' => 'https://images.unsplash.com/photo-1581147036324-c1c0a4d4f8c6?auto=format&fit=crop&w=180&q=80',
        'full'  => 'https://images.unsplash.com/photo-1581147036324-c1c0a4d4f8c6?auto=format&fit=crop&w=1200&q=85',
        'zoom'  => 'https://images.unsplash.com/photo-1581147036324-c1c0a4d4f8c6?auto=format&fit=crop&w=1800&q=90',
        'alt'   => 'Battery-powered garden tool demo image',
      ),
      array(
        'thumb' => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?auto=format&fit=crop&w=180&q=80',
        'full'  => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?auto=format&fit=crop&w=1200&q=85',
        'zoom'  => 'https://images.unsplash.com/photo-1530124566582-a618bc2615dc?auto=format&fit=crop&w=1800&q=90',
        'alt'   => 'Workshop detail demo image',
      ),
      array(
        'thumb' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=180&q=80',
        'full'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=85',
        'zoom'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1800&q=90',
        'alt'   => 'Outdoor maintenance tool demo image',
      ),
    );
  }
}
