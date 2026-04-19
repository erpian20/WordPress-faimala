<?php
/**
 * PowerUp Theme Settings Configuration
 *
 * @package PowerUp_Theme
 * @subpackage Config
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Settings Configuration
 */
return array(
    /**
     * Theme Support Settings
     */
    'theme_supports' => array(
        'title-tag'                           => POWERUP_SUPPORTS_TITLE_TAG,
        'custom-logo'                         => POWERUP_SUPPORTS_CUSTOM_LOGO,
        'post-thumbnails'                     => POWERUP_SUPPORTS_POST_THUMBNAILS,
        'html5'                               => POWERUP_SUPPORTS_HTML5 ? array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ) : false,
        'woocommerce'                         => POWERUP_SUPPORTS_WOOCOMMERCE,
        'wc-product-gallery-zoom'             => POWERUP_SUPPORTS_WOOCOMMERCE,
        'wc-product-gallery-lightbox'         => POWERUP_SUPPORTS_WOOCOMMERCE,
        'wc-product-gallery-slider'           => POWERUP_SUPPORTS_WOOCOMMERCE,
        'customize-selective-refresh-widgets' => POWERUP_SUPPORTS_CUSTOMIZE_SELECTIVE_REFRESH,
        'responsive-embeds'                   => POWERUP_SUPPORTS_RESPONSIVE_EMBEDS,
        'align-wide'                          => POWERUP_SUPPORTS_ALIGN_WIDE,
        'automatic-feed-links'                => true,
    ),

    /**
     * Navigation Menu Locations
     */
    'nav_menus' => array(
        'primary' => __('Primary Menu', POWERUP_TEXT_DOMAIN),
        'footer'  => __('Footer Menu', POWERUP_TEXT_DOMAIN),
        'mobile'  => __('Mobile Menu', POWERUP_TEXT_DOMAIN),
    ),

    /**
     * Image Size Settings
     */
    'image_sizes' => array(
        // Default WordPress sizes
        'thumbnail' => array(
            'width'  => POWERUP_IMAGE_SIZE_THUMBNAIL_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_THUMBNAIL_HEIGHT,
            'crop'   => true,
        ),
        'medium' => array(
            'width'  => POWERUP_IMAGE_SIZE_MEDIUM_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_MEDIUM_HEIGHT,
            'crop'   => false,
        ),
        'large' => array(
            'width'  => POWERUP_IMAGE_SIZE_LARGE_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_LARGE_HEIGHT,
            'crop'   => false,
        ),

        // Custom product image sizes for responsive images
        'product_large' => array(
            'width'  => POWERUP_IMAGE_SIZE_PRODUCT_LARGE_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_PRODUCT_LARGE_HEIGHT,
            'crop'   => true,
        ),
        'product_medium' => array(
            'width'  => POWERUP_IMAGE_SIZE_PRODUCT_MEDIUM_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_PRODUCT_MEDIUM_HEIGHT,
            'crop'   => true,
        ),
        'product_small' => array(
            'width'  => POWERUP_IMAGE_SIZE_PRODUCT_SMALL_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_PRODUCT_SMALL_HEIGHT,
            'crop'   => true,
        ),
        'product_thumbnail' => array(
            'width'  => POWERUP_IMAGE_SIZE_PRODUCT_THUMBNAIL_WIDTH,
            'height' => POWERUP_IMAGE_SIZE_PRODUCT_THUMBNAIL_HEIGHT,
            'crop'   => true,
        ),
    ),

    /**
     * WooCommerce Settings
     */
    'woocommerce' => array(
        'products_per_row'        => POWERUP_WOOCOMMERCE_PRODUCTS_PER_ROW,
        'products_per_page'       => POWERUP_WOOCOMMERCE_PRODUCTS_PER_PAGE,
        'related_products_count'  => POWERUP_WOOCOMMERCE_RELATED_PRODUCTS_COUNT,
        'cross_sell_count'        => POWERUP_WOOCOMMERCE_CROSS_SELL_COUNT,
        'up_sell_count'           => POWERUP_WOOCOMMERCE_UP_SELL_COUNT,
        'thumbnail_image_width'   => POWERUP_IMAGE_SIZE_PRODUCT_THUMBNAIL_WIDTH,
        'single_image_width'      => POWERUP_IMAGE_SIZE_PRODUCT_LARGE_WIDTH,
        'gallery_thumbnail_width' => 200,
    ),

    /**
     * Performance Settings
     */
    'performance' => array(
        'enable_assets_minification' => POWERUP_ENABLE_ASSETS_MINIFICATION,
        'enable_lazy_loading'        => POWERUP_ENABLE_LAZY_LOADING,
        'enable_cache_busting'       => POWERUP_ENABLE_CACHE_BUSTING,
        'enable_critical_css'        => POWERUP_ENABLE_CRITICAL_CSS,
        'cache_version'              => POWERUP_CACHE_VERSION,
        'cache_expire'               => POWERUP_CACHE_EXPIRE,
    ),

    /**
     * Security Settings
     */
    'security' => array(
        'enable_security_headers'     => POWERUP_ENABLE_SECURITY_HEADERS,
        'enable_nonce_verification'   => POWERUP_ENABLE_NONCE_VERIFICATION,
        'enable_input_sanitization'   => POWERUP_ENABLE_INPUT_SANITIZATION,
        'enable_output_escaping'      => POWERUP_ENABLE_OUTPUT_ESCAPING,
        'limit_login_attempts'        => true,
        'login_attempts_limit'        => 5,
        'login_lockout_duration'      => 15 * MINUTE_IN_SECONDS,
        'disable_xmlrpc'              => true,
        'hide_wordpress_version'      => true,
    ),

    /**
     * Accessibility Settings
     */
    'accessibility' => array(
        'enable_aria_labels'          => POWERUP_ENABLE_ARIA_LABELS,
        'enable_keyboard_navigation'  => POWERUP_ENABLE_KEYBOARD_NAVIGATION,
        'enable_skip_links'           => POWERUP_ENABLE_SKIP_LINKS,
        'enable_color_contrast_check' => POWERUP_ENABLE_COLOR_CONTRAST_CHECK,
        'focus_outline_color'         => '#ff6200',
        'focus_outline_width'         => '2px',
        'skip_link_text'              => __('Skip to main content', POWERUP_TEXT_DOMAIN),
    ),

    /**
     * Content Width
     */
    'content_width' => 1200,

    /**
     * Customizer Default Settings
     */
    'customizer_defaults' => array(
        'primary_color'            => '#ff6200',
        'secondary_color'          => '#333333',
        'background_color'         => '#ffffff',
        'base_font_size'           => 16,
        'font_family'              => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'show_header_search'       => true,
        'sticky_header'            => true,
        'footer_copyright'         => sprintf(__('© %s. All rights reserved.', POWERUP_TEXT_DOMAIN), date('Y')),
        'show_footer_social'       => true,
        'show_blog_featured_image' => true,
        'show_post_meta'           => true,
        'show_read_more'           => true,
        'products_per_row'         => '4',
        'products_per_page'        => 12,
        'show_quick_view'          => true,
    ),

    /**
     * Social Media Settings
     */
    'social_media' => array(
        'platforms' => array(
            'facebook'  => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'Facebook',
                'icon'    => 'facebook',
            ),
            'twitter'   => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'Twitter',
                'icon'    => 'twitter',
            ),
            'instagram' => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'Instagram',
                'icon'    => 'instagram',
            ),
            'linkedin'  => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'LinkedIn',
                'icon'    => 'linkedin',
            ),
            'youtube'   => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'YouTube',
                'icon'    => 'youtube',
            ),
            'pinterest' => array(
                'enabled' => false,
                'url'     => '',
                'label'   => 'Pinterest',
                'icon'    => 'pinterest',
            ),
        ),
        'share_platforms' => array('facebook', 'twitter', 'linkedin', 'pinterest'),
    ),

    /**
     * SEO Settings
     */
    'seo' => array(
        'enable_schema_markup'      => true,
        'enable_opengraph'          => true,
        'enable_twitter_cards'      => true,
        'enable_json_ld'            => true,
        'default_meta_description'  => get_bloginfo('description'),
        'default_meta_keywords'     => '',
        'default_og_image'          => '',
        'twitter_username'          => '',
        'facebook_app_id'           => '',
    ),

    /**
     * Analytics Settings
     */
    'analytics' => array(
        'google_analytics_id'       => '',
        'google_tag_manager_id'     => '',
        'facebook_pixel_id'         => '',
        'enable_analytics'          => false,
    ),

    /**
     * Development Settings
     */
    'development' => array(
        'dev_mode'      => POWERUP_DEV_MODE,
        'staging_mode'  => POWERUP_STAGING_MODE,
        'production_mode' => POWERUP_PRODUCTION_MODE,
        'enable_debug_log' => POWERUP_DEBUG_LOG,
        'enable_debug_display' => POWERUP_DEBUG_DISPLAY,
    ),
);

/**
 * Filter to modify theme settings
 */
add_filter('powerup_theme_settings', function($settings) {
    return apply_filters('powerup_theme_settings_config', $settings);
});