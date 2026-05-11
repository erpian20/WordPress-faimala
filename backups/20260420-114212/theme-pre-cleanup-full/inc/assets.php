<?php
/**
 * PowerUp Theme Assets Management
 *
 * @package PowerUp_Theme
 * @subpackage Assets
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue theme styles and scripts.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_enqueue_assets() {
    // Get theme version for cache busting.
    $theme_version = wp_get_theme()->get('Version');

    // Main stylesheet.
    wp_enqueue_style(
        'powerup-style',
        get_template_directory_uri() . '/style.css',
        array(),
        $theme_version
    );

    // Additional CSS files.
    if (file_exists(get_template_directory() . '/assets/css/style.css')) {
        wp_enqueue_style(
            'powerup-additional-style',
            get_template_directory_uri() . '/assets/css/style.css',
            array('powerup-style'),
            $theme_version
        );
    }

    // Responsive navigation script.
    wp_enqueue_script(
        'powerup-navigation',
        get_template_directory_uri() . '/assets/js/navigation.js',
        array(),
        $theme_version,
        true
    );

    // Main theme script.
    wp_enqueue_script(
        'powerup-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        array('jquery'),
        $theme_version,
        true
    );

    // Localize script for AJAX calls.
    wp_localize_script('powerup-script', 'powerup_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('powerup_ajax_nonce'),
    ));

    // Add inline critical CSS for above-the-fold content.
    $critical_css = '
        /* Critical CSS will be added here */
    ';
    wp_add_inline_style('powerup-style', $critical_css);

    // Comment reply script.
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'powerup_enqueue_assets');

/**
 * Add preload links for critical assets.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_preload_assets() {
    // Preload critical fonts.
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/example.woff2" as="font" type="font/woff2" crossorigin>';
}
add_action('wp_head', 'powerup_preload_assets', 1);

/**
 * Add defer attribute to non-critical scripts.
 *
 * @since 1.0.0
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Modified script tag.
 */
function powerup_defer_scripts($tag, $handle) {
    // Scripts to defer.
    $scripts_to_defer = array(
        'powerup-navigation',
        'powerup-script',
    );

    if (in_array($handle, $scripts_to_defer, true)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'powerup_defer_scripts', 10, 2);

/**
 * Add async attribute to non-critical scripts.
 *
 * @since 1.0.0
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Modified script tag.
 */
function powerup_async_scripts($tag, $handle) {
    // Scripts to async.
    $scripts_to_async = array(
        // Add script handles here.
    );

    if (in_array($handle, $scripts_to_async, true)) {
        return str_replace(' src', ' async src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'powerup_async_scripts', 10, 2);

/**
 * Remove unnecessary scripts and styles.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_cleanup_assets() {
    // Remove emoji scripts and styles.
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');

    // Remove WordPress version from scripts and styles.
    add_filter('style_loader_src', 'powerup_remove_version_parameter', 9999);
    add_filter('script_loader_src', 'powerup_remove_version_parameter', 9999);
}
add_action('init', 'powerup_cleanup_assets');

/**
 * Remove version parameter from scripts and styles.
 *
 * @since 1.0.0
 * @param string $src The source URL.
 * @return string Modified URL.
 */
function powerup_remove_version_parameter($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}