<?php
/**
 * PowerUp Theme Constants
 *
 * @package PowerUp_Theme
 * @subpackage Config
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Information
 */
define('POWERUP_THEME_NAME', 'PowerUp Industrial');
define('POWERUP_THEME_VERSION', wp_get_theme()->get('Version'));
define('POWERUP_THEME_AUTHOR', 'PowerUp Team');
define('POWERUP_THEME_AUTHOR_URI', 'https://powerup.com');
define('POWERUP_THEME_URI', wp_get_theme()->get('ThemeURI'));

/**
 * Path Constants
 */
define('POWERUP_THEME_DIR', get_template_directory());
define('POWERUP_THEME_URI', get_template_directory_uri());
define('POWERUP_CHILD_DIR', get_stylesheet_directory());
define('POWERUP_CHILD_URI', get_stylesheet_directory_uri());
define('POWERUP_INC_DIR', POWERUP_THEME_DIR . '/inc');
define('POWERUP_INC_URI', POWERUP_THEME_URI . '/inc');
define('POWERUP_CONFIG_DIR', POWERUP_THEME_DIR . '/config');
define('POWERUP_CONFIG_URI', POWERUP_THEME_URI . '/config');
define('POWERUP_ASSETS_DIR', POWERUP_THEME_DIR . '/assets');
define('POWERUP_ASSETS_URI', POWERUP_THEME_URI . '/assets');
define('POWERUP_CSS_DIR', POWERUP_ASSETS_DIR . '/css');
define('POWERUP_CSS_URI', POWERUP_ASSETS_URI . '/css');
define('POWERUP_JS_DIR', POWERUP_ASSETS_DIR . '/js');
define('POWERUP_JS_URI', POWERUP_ASSETS_URI . '/js');
define('POWERUP_IMAGES_DIR', POWERUP_ASSETS_DIR . '/images');
define('POWERUP_IMAGES_URI', POWERUP_ASSETS_URI . '/images');
define('POWERUP_LANGUAGES_DIR', POWERUP_THEME_DIR . '/languages');
define('POWERUP_LANGUAGES_URI', POWERUP_THEME_URI . '/languages');

/**
 * Text Domain
 */
define('POWERUP_TEXT_DOMAIN', 'powerup-theme');

/**
 * Theme Support Constants
 */
define('POWERUP_SUPPORTS_TITLE_TAG', true);
define('POWERUP_SUPPORTS_CUSTOM_LOGO', true);
define('POWERUP_SUPPORTS_POST_THUMBNAILS', true);
define('POWERUP_SUPPORTS_HTML5', true);
define('POWERUP_SUPPORTS_WOOCOMMERCE', true);
define('POWERUP_SUPPORTS_CUSTOMIZE_SELECTIVE_REFRESH', true);
define('POWERUP_SUPPORTS_RESPONSIVE_EMBEDS', true);
define('POWERUP_SUPPORTS_ALIGN_WIDE', true);

/**
 * Image Size Constants
 */
define('POWERUP_IMAGE_SIZE_THUMBNAIL_WIDTH', 150);
define('POWERUP_IMAGE_SIZE_THUMBNAIL_HEIGHT', 150);
define('POWERUP_IMAGE_SIZE_MEDIUM_WIDTH', 300);
define('POWERUP_IMAGE_SIZE_MEDIUM_HEIGHT', 300);
define('POWERUP_IMAGE_SIZE_LARGE_WIDTH', 1024);
define('POWERUP_IMAGE_SIZE_LARGE_HEIGHT', 1024);

// Custom image sizes for responsive images
define('POWERUP_IMAGE_SIZE_PRODUCT_LARGE_WIDTH', 800);
define('POWERUP_IMAGE_SIZE_PRODUCT_LARGE_HEIGHT', 800);
define('POWERUP_IMAGE_SIZE_PRODUCT_MEDIUM_WIDTH', 600);
define('POWERUP_IMAGE_SIZE_PRODUCT_MEDIUM_HEIGHT', 600);
define('POWERUP_IMAGE_SIZE_PRODUCT_SMALL_WIDTH', 400);
define('POWERUP_IMAGE_SIZE_PRODUCT_SMALL_HEIGHT', 400);
define('POWERUP_IMAGE_SIZE_PRODUCT_THUMBNAIL_WIDTH', 200);
define('POWERUP_IMAGE_SIZE_PRODUCT_THUMBNAIL_HEIGHT', 200);

/**
 * WooCommerce Constants
 */
define('POWERUP_WOOCOMMERCE_PRODUCTS_PER_ROW', 4);
define('POWERUP_WOOCOMMERCE_PRODUCTS_PER_PAGE', 12);
define('POWERUP_WOOCOMMERCE_RELATED_PRODUCTS_COUNT', 4);
define('POWERUP_WOOCOMMERCE_CROSS_SELL_COUNT', 4);
define('POWERUP_WOOCOMMERCE_UP_SELL_COUNT', 4);

/**
 * Performance Constants
 */
define('POWERUP_ENABLE_ASSETS_MINIFICATION', true);
define('POWERUP_ENABLE_LAZY_LOADING', true);
define('POWERUP_ENABLE_CACHE_BUSTING', true);
define('POWERUP_ENABLE_CRITICAL_CSS', true);

/**
 * Security Constants
 */
define('POWERUP_ENABLE_SECURITY_HEADERS', true);
define('POWERUP_ENABLE_NONCE_VERIFICATION', true);
define('POWERUP_ENABLE_INPUT_SANITIZATION', true);
define('POWERUP_ENABLE_OUTPUT_ESCAPING', true);

/**
 * Accessibility Constants
 */
define('POWERUP_ENABLE_ARIA_LABELS', true);
define('POWERUP_ENABLE_KEYBOARD_NAVIGATION', true);
define('POWERUP_ENABLE_SKIP_LINKS', true);
define('POWERUP_ENABLE_COLOR_CONTRAST_CHECK', true);

/**
 * Debug Constants
 */
define('POWERUP_DEBUG', (defined('WP_DEBUG') && WP_DEBUG));
define('POWERUP_DEBUG_LOG', (POWERUP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG));
define('POWERUP_DEBUG_DISPLAY', (POWERUP_DEBUG && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY));

/**
 * Cache Constants
 */
define('POWERUP_CACHE_VERSION', POWERUP_THEME_VERSION);
define('POWERUP_CACHE_EXPIRE', HOUR_IN_SECONDS);

/**
 * Conditional Constants
 */
define('POWERUP_IS_CHILD_THEME', is_child_theme());
define('POWERUP_IS_WOOCOMMERCE_ACTIVE', class_exists('WooCommerce'));
define('POWERUP_IS_ADMIN', is_admin());
define('POWERUP_IS_AJAX', (defined('DOING_AJAX') && DOING_AJAX));
define('POWERUP_IS_REST_API', (defined('REST_REQUEST') && REST_REQUEST));
define('POWERUP_IS_CRON', (defined('DOING_CRON') && DOING_CRON));
define('POWERUP_IS_XMLRPC', (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST));

/**
 * Development Constants
 */
define('POWERUP_DEV_MODE', (defined('WP_ENV') && 'development' === WP_ENV));
define('POWERUP_STAGING_MODE', (defined('WP_ENV') && 'staging' === WP_ENV));
define('POWERUP_PRODUCTION_MODE', (defined('WP_ENV') && 'production' === WP_ENV) || (!defined('WP_ENV') && !POWERUP_DEBUG));