<?php
/**
 * PowerUp Theme Utility Functions
 *
 * @package PowerUp_Theme
 * @subpackage Utilities
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get page ID by template.
 *
 * @since 1.0.0
 * @param string $template_file Template file name.
 * @return int Page ID or 0 if not found.
 */
function powerup_get_page_id_by_template($template_file) {
    $template_file = trim((string) $template_file);
    if ('' === $template_file) {
        return 0;
    }

    $pages = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_wp_page_template',
        'meta_value'     => $template_file,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ));

    if (!empty($pages)) {
        return (int) $pages[0];
    }

    return 0;
}

/**
 * Get page URL by template or path.
 *
 * @since 1.0.0
 * @param string $template_file Template file name.
 * @param string $path          Page path/slug.
 * @param string $fallback      Fallback URL.
 * @return string Page URL.
 */
function powerup_get_page_url_by_template_or_path($template_file, $path, $fallback = '/') {
    $page_id = powerup_get_page_id_by_template($template_file);
    if ($page_id > 0) {
        return get_permalink($page_id);
    }

    $path = trim((string) $path, '/');
    if ('' !== $path) {
        $page = get_page_by_path($path, OBJECT, 'page');
        if ($page instanceof WP_Post) {
            return get_permalink($page);
        }
    }

    return home_url((string) $fallback);
}

/**
 * Get shop URL.
 *
 * @since 1.0.0
 * @return string Shop URL.
 */
function powerup_get_shop_url() {
    if (function_exists('wc_get_page_id')) {
        $shop_id = (int) wc_get_page_id('shop');
        if ($shop_id > 0) {
            return get_permalink($shop_id);
        }
    }

    return powerup_get_page_url_by_template_or_path('page-shop.php', 'shop', '/shop/');
}

/**
 * Get about page URL.
 *
 * @since 1.0.0
 * @return string About page URL.
 */
function powerup_get_about_page_url() {
    return powerup_get_page_url_by_template_or_path('page-about.php', 'about-us', '/about-us/');
}

/**
 * Get contact page URL.
 *
 * @since 1.0.0
 * @return string Contact page URL.
 */
function powerup_get_contact_page_url() {
    return powerup_get_page_url_by_template_or_path('page-contact.php', 'contact-us', '/contact-us/');
}

/**
 * Get blog page URL.
 *
 * @since 1.0.0
 * @return string Blog page URL.
 */
function powerup_get_blog_page_url() {
    $posts_page_id = (int) get_option('page_for_posts');
    if ($posts_page_id > 0) {
        return get_permalink($posts_page_id);
    }

    return powerup_get_page_url_by_template_or_path('page-blog.php', 'blog', '/blog/');
}

/**
 * Get runtime configuration.
 *
 * @since 1.0.0
 * @return array Runtime configuration.
 */
function powerup_get_runtime_config() {
    static $runtime_config = null;

    if (null !== $runtime_config) {
        return $runtime_config;
    }

    $runtime_config = array(
        'version'           => wp_get_theme()->get('Version'),
        'template_directory' => get_template_directory(),
        'template_uri'      => get_template_directory_uri(),
        'stylesheet_directory' => get_stylesheet_directory(),
        'stylesheet_uri'    => get_stylesheet_directory_uri(),
        'is_child_theme'    => is_child_theme(),
        'text_domain'       => 'powerup-theme',
        'language_dir'      => get_template_directory() . '/languages',
    );

    return apply_filters('powerup_runtime_config', $runtime_config);
}

/**
 * Check if WooCommerce is active.
 *
 * @since 1.0.0
 * @return bool True if WooCommerce is active.
 */
function powerup_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Check if plugin is active.
 *
 * @since 1.0.0
 * @param string $plugin Plugin name or path.
 * @return bool True if plugin is active.
 */
function powerup_is_plugin_active($plugin) {
    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active($plugin);
}

/**
 * Get theme option with fallback.
 *
 * @since 1.0.0
 * @param string $option  Option name.
 * @param mixed  $default Default value.
 * @return mixed Option value.
 */
function powerup_get_theme_option($option, $default = false) {
    $value = get_theme_mod($option, $default);
    return apply_filters("powerup_theme_option_{$option}", $value);
}

/**
 * Sanitize checkbox value.
 *
 * @since 1.0.0
 * @param mixed $value Checkbox value.
 * @return bool Sanitized value.
 */
function powerup_sanitize_checkbox($value) {
    return (bool) $value;
}

/**
 * Sanitize select/radio value.
 *
 * @since 1.0.0
 * @param mixed $value       Input value.
 * @param array $choices     Available choices.
 * @param mixed $default     Default value.
 * @return mixed Sanitized value.
 */
function powerup_sanitize_select($value, $choices, $default = '') {
    if (array_key_exists($value, $choices)) {
        return $value;
    }
    return $default;
}

/**
 * Sanitize number value.
 *
 * @since 1.0.0
 * @param mixed $value Input value.
 * @param int   $min   Minimum value.
 * @param int   $max   Maximum value.
 * @return int Sanitized value.
 */
function powerup_sanitize_number($value, $min = 0, $max = 9999) {
    $value = (int) $value;
    if ($value < $min) {
        $value = $min;
    }
    if ($value > $max) {
        $value = $max;
    }
    return $value;
}

/**
 * Get image URL with fallback.
 *
 * @since 1.0.0
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @param string $fallback      Fallback image URL.
 * @return string Image URL.
 */
function powerup_get_image_url($attachment_id, $size = 'full', $fallback = '') {
    if (!$attachment_id) {
        return $fallback;
    }

    $image_url = wp_get_attachment_image_url($attachment_id, $size);
    if (!$image_url) {
        return $fallback;
    }

    return $image_url;
}

/**
 * Get post excerpt with custom length.
 *
 * @since 1.0.0
 * @param int    $post_id Post ID.
 * @param int    $length  Excerpt length.
 * @param string $more    More text.
 * @return string Excerpt.
 */
function powerup_get_excerpt($post_id = null, $length = 55, $more = '...') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $post = get_post($post_id);
    if (!$post) {
        return '';
    }

    $excerpt = $post->post_excerpt;
    if (empty($excerpt)) {
        $excerpt = $post->post_content;
    }

    $excerpt = strip_shortcodes($excerpt);
    $excerpt = wp_strip_all_tags($excerpt);
    $excerpt = substr($excerpt, 0, $length);

    if (strlen($excerpt) >= $length) {
        $excerpt .= $more;
    }

    return $excerpt;
}

/**
 * Format file size.
 *
 * @since 1.0.0
 * @param int $bytes File size in bytes.
 * @param int $decimals Number of decimals.
 * @return string Formatted file size.
 */
function powerup_format_file_size($bytes, $decimals = 2) {
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    $size = $bytes / pow(1024, $factor);
    return sprintf("%.{$decimals}f", $size) . ' ' . $sizes[$factor];
}

/**
 * Get current URL.
 *
 * @since 1.0.0
 * @return string Current URL.
 */
function powerup_get_current_url() {
    global $wp;
    return home_url($wp->request);
}

/**
 * Get browser information.
 *
 * @since 1.0.0
 * @return array Browser info.
 */
function powerup_get_browser_info() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    return array(
        'user_agent' => $user_agent,
        'is_mobile'  => wp_is_mobile(),
        'is_tablet'  => (bool) preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent),
        'is_desktop' => !wp_is_mobile() && !(bool) preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $user_agent),
    );
}

/**
 * Debug logging helper.
 *
 * @since 1.0.0
 * @param mixed $data Data to log.
 * @param string $label Optional label.
 * @return void
 */
function powerup_debug_log($data, $label = '') {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    if ($label) {
        error_log('[' . $label . '] ' . print_r($data, true));
    } else {
        error_log(print_r($data, true));
    }
}

/**
 * Check if current page is AMP.
 *
 * @since 1.0.0
 * @return bool True if AMP page.
 */
function powerup_is_amp() {
    return function_exists('is_amp_endpoint') && is_amp_endpoint();
}

/**
 * Get social media share URLs.
 *
 * @since 1.0.0
 * @param string $platform Social media platform.
 * @param array  $args     Additional arguments.
 * @return string Share URL.
 */
function powerup_get_share_url($platform, $args = array()) {
    $defaults = array(
        'url'     => get_permalink(),
        'title'   => get_the_title(),
        'summary' => get_the_excerpt(),
        'image'   => get_the_post_thumbnail_url(),
    );
    $args = wp_parse_args($args, $defaults);

    $share_urls = array(
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($args['url']),
        'twitter'  => 'https://twitter.com/intent/tweet?text=' . urlencode($args['title']) . '&url=' . urlencode($args['url']),
        'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode($args['url']) . '&title=' . urlencode($args['title']) . '&summary=' . urlencode($args['summary']),
        'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . urlencode($args['url']) . '&media=' . urlencode($args['image']) . '&description=' . urlencode($args['title']),
        'whatsapp' => 'https://api.whatsapp.com/send?text=' . urlencode($args['title'] . ' ' . $args['url']),
        'email'    => 'mailto:?subject=' . urlencode($args['title']) . '&body=' . urlencode($args['url']),
    );

    if (!isset($share_urls[$platform])) {
        return '';
    }

    return $share_urls[$platform];
}

/**
 * Get theme asset URL.
 *
 * @since 1.0.0
 * @param string $path Asset path relative to theme directory.
 * @return string Asset URL.
 */
function powerup_get_asset_url($path) {
    $path = ltrim($path, '/');
    return get_template_directory_uri() . '/' . $path;
}

/**
 * Minify CSS string.
 *
 * @since 1.0.0
 * @param string $css CSS string.
 * @return string Minified CSS.
 */
function powerup_minify_css($css) {
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\/\*.*?\*\//', '', $css);
    $css = preg_replace('/\s*([{:;,])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return trim($css);
}

/**
 * Generate unique ID.
 *
 * @since 1.0.0
 * @param string $prefix ID prefix.
 * @return string Unique ID.
 */
function powerup_generate_id($prefix = 'powerup') {
    return $prefix . '_' . uniqid();
}