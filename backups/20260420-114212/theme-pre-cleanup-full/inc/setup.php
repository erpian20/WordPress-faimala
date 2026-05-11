<?php
/**
 * PowerUp Theme Setup Functions
 *
 * @package PowerUp_Theme
 * @subpackage Setup
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_theme_setup() {
    // Load theme text domain for translation.
    load_theme_textdomain('powerup-theme', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // Add support for custom logo.
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Switch default core markup to output valid HTML5.
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for core custom logo.
    add_theme_support('custom-logo', apply_filters('powerup_custom_logo_args', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    )));

    // Add support for WooCommerce.
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Register navigation menus.
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'powerup-theme'),
        'footer'  => esc_html__('Footer Menu', 'powerup-theme'),
        'mobile'  => esc_html__('Mobile Menu', 'powerup-theme'),
    ));

    // Add custom image sizes for responsive images.
    add_image_size('product_large', 800, 800, true);      // For large screens
    add_image_size('product_medium', 600, 600, true);     // For tablets
    add_image_size('product_small', 400, 400, true);      // For mobile devices
    add_image_size('product_thumbnail', 200, 200, true);  // For thumbnails
}
add_action('after_setup_theme', 'powerup_theme_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @since 1.0.0
 * @global int $content_width
 * @return void
 */
function powerup_content_width() {
    $GLOBALS['content_width'] = apply_filters('powerup_content_width', 1200);
}
add_action('after_setup_theme', 'powerup_content_width', 0);