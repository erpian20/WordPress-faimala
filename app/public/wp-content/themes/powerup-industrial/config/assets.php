<?php
/**
 * PowerUp Theme Assets Configuration
 *
 * @package PowerUp_Theme
 * @subpackage Config
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Assets Configuration
 */
return array(
    /**
     * Stylesheet Configuration
     */
    'styles' => array(
        'main' => array(
            'handle'    => 'powerup-style',
            'src'       => POWERUP_THEME_URI . '/style.css',
            'deps'      => array(),
            'ver'       => POWERUP_CACHE_VERSION,
            'media'     => 'all',
            'in_footer' => false,
        ),
        'additional' => array(
            'handle'    => 'powerup-additional-style',
            'src'       => POWERUP_CSS_URI . '/style.css',
            'deps'      => array('powerup-style'),
            'ver'       => POWERUP_CACHE_VERSION,
            'media'     => 'all',
            'in_footer' => false,
            'condition' => 'file_exists:' . POWERUP_CSS_DIR . '/style.css',
        ),
        'woocommerce' => array(
            'handle'    => 'powerup-woocommerce-style',
            'src'       => POWERUP_CSS_URI . '/woocommerce.css',
            'deps'      => array('woocommerce-general'),
            'ver'       => POWERUP_CACHE_VERSION,
            'media'     => 'all',
            'in_footer' => false,
            'condition' => 'class_exists:WooCommerce',
        ),
        'critical' => array(
            'handle'    => 'powerup-critical-style',
            'inline'    => true,
            'content'   => '', // Will be populated dynamically
            'condition' => POWERUP_ENABLE_CRITICAL_CSS,
        ),
    ),

    /**
     * Script Configuration
     */
    'scripts' => array(
        'navigation' => array(
            'handle'    => 'powerup-navigation',
            'src'       => POWERUP_JS_URI . '/navigation.js',
            'deps'      => array(),
            'ver'       => POWERUP_CACHE_VERSION,
            'in_footer' => true,
            'async'     => false,
            'defer'     => true,
        ),
        'main' => array(
            'handle'    => 'powerup-script',
            'src'       => POWERUP_JS_URI . '/theme.js',
            'deps'      => array('jquery'),
            'ver'       => POWERUP_CACHE_VERSION,
            'in_footer' => true,
            'async'     => false,
            'defer'     => true,
            'localize'  => array(
                'name' => 'powerup_ajax',
                'data' => array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('powerup_ajax_nonce'),
                ),
            ),
        ),
        'woocommerce' => array(
            'handle'    => 'powerup-woocommerce-script',
            'src'       => POWERUP_JS_URI . '/woocommerce.js',
            'deps'      => array('jquery', 'woocommerce'),
            'ver'       => POWERUP_CACHE_VERSION,
            'in_footer' => true,
            'async'     => false,
            'defer'     => true,
            'condition' => 'class_exists:WooCommerce',
        ),
        'customizer-preview' => array(
            'handle'    => 'powerup-customizer-preview',
            'src'       => POWERUP_JS_URI . '/customizer-preview.js',
            'deps'      => array('customize-preview', 'jquery'),
            'ver'       => POWERUP_CACHE_VERSION,
            'in_footer' => true,
            'condition' => 'is_customize_preview',
        ),
        'comment-reply' => array(
            'handle'    => 'comment-reply',
            'condition' => 'is_singular:comments_open:get_option_thread_comments',
        ),
    ),

    /**
     * Preload Assets Configuration
     */
    'preload' => array(
        'fonts' => array(
            array(
                'href'        => POWERUP_THEME_URI . '/assets/fonts/example.woff2',
                'as'          => 'font',
                'type'        => 'font/woff2',
                'crossorigin' => true,
            ),
        ),
        'images' => array(
            // Critical images to preload
        ),
    ),

    /**
     * Defer/Async Script Configuration
     */
    'defer_scripts' => array(
        'powerup-navigation',
        'powerup-script',
        'powerup-woocommerce-script',
    ),

    'async_scripts' => array(
        // Scripts to load asynchronously
    ),

    /**
     * Asset Optimization Configuration
     */
    'optimization' => array(
        'minify_css' => POWERUP_ENABLE_ASSETS_MINIFICATION,
        'minify_js'  => POWERUP_ENABLE_ASSETS_MINIFICATION,
        'concatenate_css' => false, // Be careful with CSS concatenation
        'concatenate_js'  => false, // Be careful with JS concatenation
        'lazy_load_images' => POWERUP_ENABLE_LAZY_LOADING,
        'lazy_load_iframes' => POWERUP_ENABLE_LAZY_LOADING,
        'preload_critical_assets' => true,
        'cache_busting' => POWERUP_ENABLE_CACHE_BUSTING,
    ),

    /**
     * Critical CSS Configuration
     */
    'critical_css' => array(
        'enable' => POWERUP_ENABLE_CRITICAL_CSS,
        'content' => '
            /* Critical CSS for above-the-fold content */
            .site-header { background: #fff; }
            .site-branding { padding: 1rem 0; }
            .main-navigation { display: flex; }
            .hero-section { background: #f5f5f5; padding: 2rem 0; }
        ',
        'auto_generate' => false,
        'generator_url' => '', // URL to critical CSS generator
    ),

    /**
     * Web Fonts Configuration
     */
    'web_fonts' => array(
        'google_fonts' => array(
            'enable' => false,
            'families' => array(
                // Example: 'Open Sans:300,400,600,700'
            ),
            'display' => 'swap',
            'preconnect' => array(
                'https://fonts.googleapis.com',
                'https://fonts.gstatic.com',
            ),
        ),
        'local_fonts' => array(
            'enable' => true,
            'families' => array(
                array(
                    'family' => 'System UI',
                    'fallback' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    'files' => array(
                        // Local font files
                    ),
                ),
            ),
        ),
    ),

    /**
     * Icon Fonts Configuration
     */
    'icon_fonts' => array(
        'font_awesome' => array(
            'enable' => false,
            'version' => '6.4.0',
            'method' => 'cdn', // 'cdn' or 'local'
            'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        ),
        'dashicons' => array(
            'enable' => true, // WordPress core dashicons
        ),
    ),

    /**
     * Image Optimization Configuration
     */
    'image_optimization' => array(
        'responsive_images' => true,
        'srcset_sizes' => array(
            'thumbnail' => array('thumbnail'),
            'medium'    => array('medium'),
            'large'     => array('large'),
            'full'      => array('full'),
            'product_large' => array('product_large', 'product_medium', 'product_small'),
        ),
        'webp_support' => true,
        'lazy_loading' => array(
            'enable' => POWERUP_ENABLE_LAZY_LOADING,
            'placeholder' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"%3E%3C/svg%3E',
            'threshold' => 0, // Intersection Observer threshold
        ),
        'compression' => array(
            'quality' => 85,
            'strip_metadata' => true,
        ),
    ),

    /**
     * CDN Configuration
     */
    'cdn' => array(
        'enable' => false,
        'url' => '', // CDN URL
        'includes' => array(
            'wp-content/themes/' . get_template(),
            'wp-content/uploads/',
        ),
        'excludes' => array(
            '.php',
            '.xml',
            '.json',
        ),
    ),

    /**
     * Service Worker Configuration
     */
    'service_worker' => array(
        'enable' => false,
        'scope' => '/',
        'cache_name' => 'powerup-cache-v' . POWERUP_CACHE_VERSION,
        'strategies' => array(
            'css' => 'cache-first',
            'js'  => 'cache-first',
            'images' => 'cache-first',
            'fonts' => 'cache-first',
        ),
        'precache' => array(
            // URLs to precache
        ),
        'runtime_caching' => array(
            // Runtime caching rules
        ),
    ),

    /**
     * Performance Monitoring
     */
    'performance_monitoring' => array(
        'enable' => POWERUP_DEBUG,
        'metrics' => array(
            'first_contentful_paint',
            'largest_contentful_paint',
            'first_input_delay',
            'cumulative_layout_shift',
        ),
        'reporting' => array(
            'console' => POWERUP_DEBUG,
            'analytics' => false,
            'endpoint' => '', // Performance reporting endpoint
        ),
    ),
);

/**
 * Get asset configuration by type
 */
function powerup_get_asset_config($type = 'styles') {
    $config = include POWERUP_CONFIG_DIR . '/assets.php';
    return isset($config[$type]) ? $config[$type] : array();
}

/**
 * Check asset condition
 */
function powerup_check_asset_condition($condition) {
    if (empty($condition)) {
        return true;
    }

    // Support for multiple conditions separated by :
    if (strpos($condition, ':') !== false) {
        $conditions = explode(':', $condition);
        $result = true;
        foreach ($conditions as $cond) {
            $result = $result && powerup_check_single_condition($cond);
        }
        return $result;
    }

    return powerup_check_single_condition($condition);
}

/**
 * Check single condition
 */
function powerup_check_single_condition($condition) {
    if (empty($condition)) {
        return true;
    }

    // File existence check
    if (strpos($condition, 'file_exists:') === 0) {
        $file = substr($condition, 12);
        return file_exists($file);
    }

    // Class existence check
    if (strpos($condition, 'class_exists:') === 0) {
        $class = substr($condition, 13);
        return class_exists($class);
    }

    // Function existence check
    if (strpos($condition, 'function_exists:') === 0) {
        $function = substr($condition, 16);
        return function_exists($function);
    }

    // Conditional tag check
    if (strpos($condition, 'is_') === 0) {
        return call_user_func($condition);
    }

    // Boolean check
    if ($condition === 'true' || $condition === 'false') {
        return $condition === 'true';
    }

    // Default: assume it's a boolean constant or variable
    return (bool) $condition;
}