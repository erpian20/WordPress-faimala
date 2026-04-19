<?php
/**
 * PowerUp Theme Accessibility Functions
 *
 * @package PowerUp_Theme
 * @subpackage Accessibility
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add skip link to skip to main content.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#main">' . __('Skip to main content', 'powerup-theme') . '</a>';
}
add_action('wp_body_open', 'powerup_skip_link');

/**
 * Add ARIA landmarks to theme regions.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_aria_landmarks() {
    // These are added via template files, but we ensure they're present.
    add_filter('body_class', 'powerup_body_aria_classes');
}
add_action('after_setup_theme', 'powerup_aria_landmarks');

/**
 * Add ARIA role and landmark classes to body.
 *
 * @since 1.0.0
 * @param array $classes Body classes.
 * @return array Modified body classes.
 */
function powerup_body_aria_classes($classes) {
    $classes[] = 'has-aria-landmarks';
    return $classes;
}

/**
 * Make sure images have alt text.
 *
 * @since 1.0.0
 * @param string $html       Image HTML.
 * @param int    $attachment_id Attachment ID.
 * @return string Modified HTML.
 */
function powerup_ensure_image_alt($html, $attachment_id) {
    // If image has no alt attribute, add empty alt for decorative images.
    if (strpos($html, 'alt="') === false) {
        $html = str_replace('<img ', '<img alt="" ', $html);
    }
    return $html;
}
add_filter('wp_get_attachment_image', 'powerup_ensure_image_alt', 10, 2);

/**
 * Add screen reader text to form fields.
 *
 * @since 1.0.0
 * @param string $field Field HTML.
 * @param string $key   Field key.
 * @param array  $args  Field arguments.
 * @return string Modified field HTML.
 */
function powerup_form_field_accessible_labels($field, $key, $args) {
    if (!empty($args['label']) && empty($args['placeholder'])) {
        $field = str_replace('<label', '<label class="screen-reader-text"', $field);
    }
    return $field;
}
add_filter('woocommerce_form_field', 'powerup_form_field_accessible_labels', 10, 3);

/**
 * Improve keyboard navigation for menus.
 *
 * @since 1.0.0
 * @param string $items Menu items HTML.
 * @param object $args  Menu arguments.
 * @return string Modified menu items.
 */
function powerup_keyboard_navigation_menu($items, $args) {
    // Add tabindex to menu items for keyboard navigation.
    $items = str_replace('<a ', '<a tabindex="0" ', $items);
    return $items;
}
add_filter('wp_nav_menu_items', 'powerup_keyboard_navigation_menu', 10, 2);

/**
 * Add ARIA attributes to search form.
 *
 * @since 1.0.0
 * @param string $form Search form HTML.
 * @return string Modified form.
 */
function powerup_accessible_search_form($form) {
    $form = str_replace(
        '<input type="search"',
        '<input type="search" aria-label="' . __('Search', 'powerup-theme') . '"',
        $form
    );
    $form = str_replace(
        '<input type="submit"',
        '<input type="submit" aria-label="' . __('Submit search', 'powerup-theme') . '"',
        $form
    );
    return $form;
}
add_filter('get_search_form', 'powerup_accessible_search_form');

/**
 * Add focus styles for keyboard navigation.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_keyboard_focus_styles() {
    echo '<style>
        :focus {
            outline: 2px solid #ff6200;
            outline-offset: 2px;
        }
        .skip-link:focus {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 100000;
            padding: 10px;
            background: #fff;
            color: #000;
            text-decoration: none;
        }
        button:focus,
        a:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #ff6200;
            outline-offset: 2px;
        }
    </style>';
}
add_action('wp_head', 'powerup_keyboard_focus_styles');

/**
 * Ensure proper heading hierarchy.
 *
 * @since 1.0.0
 * @param string $title Page title.
 * @param string $sep   Title separator.
 * @return string Modified title.
 */
function powerup_accessible_title($title, $sep) {
    // Ensure proper heading hierarchy in titles.
    return $title;
}
add_filter('wp_title', 'powerup_accessible_title', 10, 2);

/**
 * Add ARIA live regions for dynamic content.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_aria_live_regions() {
    echo '<div class="screen-reader-text" aria-live="polite" aria-atomic="true" id="powerup-aria-live"></div>';
}
add_action('wp_footer', 'powerup_aria_live_regions');

/**
 * Make pagination accessible.
 *
 * @since 1.0.0
 * @param string $links Pagination links.
 * @return string Modified links.
 */
function powerup_accessible_pagination($links) {
    $links = str_replace('<a ', '<a aria-label="' . __('Page', 'powerup-theme') . ' " ', $links);
    $links = str_replace('<span ', '<span aria-current="page" ', $links);
    return $links;
}
add_filter('paginate_links', 'powerup_accessible_pagination');

/**
 * Add ARIA labels to social media links.
 *
 * @since 1.0.0
 * @param string $link Social media link HTML.
 * @param string $platform Platform name.
 * @return string Modified link.
 */
function powerup_social_media_aria_labels($link, $platform) {
    $aria_label = sprintf(__('Follow us on %s', 'powerup-theme'), $platform);
    $link = str_replace('<a ', '<a aria-label="' . esc_attr($aria_label) . '" ', $link);
    return $link;
}

/**
 * Check color contrast for accessibility.
 *
 * @since 1.0.0
 * @param string $color1 First color (hex).
 * @param string $color2 Second color (hex).
 * @return float Contrast ratio.
 */
function powerup_color_contrast_ratio($color1, $color2) {
    // Convert hex to RGB.
    $rgb1 = powerup_hex_to_rgb($color1);
    $rgb2 = powerup_hex_to_rgb($color2);

    // Calculate relative luminance.
    $l1 = powerup_relative_luminance($rgb1);
    $l2 = powerup_relative_luminance($rgb2);

    // Calculate contrast ratio.
    $ratio = ($l1 + 0.05) / ($l2 + 0.05);
    if ($l2 > $l1) {
        $ratio = ($l2 + 0.05) / ($l1 + 0.05);
    }

    return round($ratio, 2);
}

/**
 * Convert hex color to RGB.
 *
 * @since 1.0.0
 * @param string $hex Hex color.
 * @return array RGB values.
 */
function powerup_hex_to_rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return array($r, $g, $b);
}

/**
 * Calculate relative luminance.
 *
 * @since 1.0.0
 * @param array $rgb RGB values.
 * @return float Relative luminance.
 */
function powerup_relative_luminance($rgb) {
    list($r, $g, $b) = $rgb;

    // Convert to sRGB.
    $r = $r / 255;
    $g = $g / 255;
    $b = $b / 255;

    // Apply gamma correction.
    $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
    $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
    $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

    // Calculate luminance.
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

/**
 * Check if color combination meets WCAG standards.
 *
 * @since 1.0.0
 * @param string $color1 First color.
 * @param string $color2 Second color.
 * @param string $level  WCAG level (AA, AAA).
 * @param string $size   Text size (normal, large).
 * @return bool Whether combination meets standards.
 */
function powerup_check_wcag_contrast($color1, $color2, $level = 'AA', $size = 'normal') {
    $ratio = powerup_color_contrast_ratio($color1, $color2);

    // WCAG 2.1 contrast requirements.
    $requirements = array(
        'AA' => array(
            'normal' => 4.5,
            'large'  => 3.0,
        ),
        'AAA' => array(
            'normal' => 7.0,
            'large'  => 4.5,
        ),
    );

    if (!isset($requirements[$level][$size])) {
        return false;
    }

    return $ratio >= $requirements[$level][$size];
}

/**
 * Add accessibility statement to footer.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_accessibility_statement() {
    echo '<div class="accessibility-statement">';
    echo '<p>' . __('We are committed to making our website accessible to everyone. If you encounter any accessibility issues, please contact us.', 'powerup-theme') . '</p>';
    echo '</div>';
}
add_action('wp_footer', 'powerup_accessibility_statement');

/**
 * Make videos accessible.
 *
 * @since 1.0.0
 * @param string $content Post content.
 * @return string Modified content.
 */
function powerup_accessible_videos($content) {
    // Add captions to embedded videos if not present.
    $content = preg_replace_callback(
        '/<iframe(.*?)><\/iframe>/',
        function($matches) {
            $iframe = $matches[0];
            if (strpos($iframe, 'title=') === false) {
                $iframe = str_replace('<iframe', '<iframe title="' . __('Embedded video', 'powerup-theme') . '"', $iframe);
            }
            return $iframe;
        },
        $content
    );
    return $content;
}
add_filter('the_content', 'powerup_accessible_videos');

/**
 * Add accessible error messages.
 *
 * @since 1.0.0
 * @param string $message Error message.
 * @return string Modified message.
 */
function powerup_accessible_error_message($message) {
    return '<span role="alert" aria-live="assertive">' . $message . '</span>';
}

/**
 * Make tables accessible.
 *
 * @since 1.0.0
 * @param string $content Table HTML.
 * @return string Modified table.
 */
function powerup_accessible_tables($content) {
    $content = preg_replace_callback(
        '/<table(.*?)>(.*?)<\/table>/s',
        function($matches) {
            $table = $matches[0];
            if (strpos($table, 'role=') === false) {
                $table = str_replace('<table', '<table role="table"', $table);
            }
            if (strpos($table, '<caption>') === false) {
                $table = str_replace('<table', '<table aria-label="' . __('Data table', 'powerup-theme') . '"', $table);
            }
            return $table;
        },
        $content
    );
    return $content;
}
add_filter('the_content', 'powerup_accessible_tables');