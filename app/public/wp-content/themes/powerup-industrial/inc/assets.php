<?php
/**
 * PowerUp Theme Assets Management
 *
 * @package PowerUp_Theme
 * @subpackage Assets
 */

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
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'powerup-style',
        get_template_directory_uri() . '/style.css',
        array(),
        $theme_version
    );

    if (file_exists(get_template_directory() . '/assets/css/style.css')) {
        wp_enqueue_style(
            'powerup-additional-style',
            get_template_directory_uri() . '/assets/css/style.css',
            array('powerup-style'),
            $theme_version
        );
    }

    wp_enqueue_script(
        'powerup-navigation',
        get_template_directory_uri() . '/assets/js/navigation.js',
        array(),
        $theme_version,
        true
    );

    wp_enqueue_script(
        'powerup-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        array('jquery'),
        $theme_version,
        true
    );

    wp_localize_script('powerup-script', 'powerup_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('powerup_ajax_nonce'),
    ));

    $critical_css = '
        /* Critical CSS will be added here */
    ';
    wp_add_inline_style('powerup-style', $critical_css);

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
    $scripts_to_async = array(
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
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');

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