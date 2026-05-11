<?php
/**
 * PowerUp Theme Customizer Functions
 *
 * @package PowerUp_Theme
 * @subpackage Customizer
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Customizer settings.
 *
 * @since 1.0.0
 * @param WP_Customize_Manager $wp_customize Customizer object.
 * @return void
 */
function powerup_customize_register($wp_customize) {
    // Add theme options panel.
    $wp_customize->add_panel('powerup_theme_options', array(
        'title'    => __('Theme Options', 'powerup-theme'),
        'priority' => 30,
    ));

    // Colors section.
    $wp_customize->add_section('powerup_colors', array(
        'title'    => __('Colors', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 10,
    ));

    // Primary color.
    $wp_customize->add_setting('powerup_primary_color', array(
        'default'           => '#ff6200',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control(
        $wp_customize,
        'powerup_primary_color',
        array(
            'label'    => __('Primary Color', 'powerup-theme'),
            'section'  => 'powerup_colors',
            'settings' => 'powerup_primary_color',
        )
    ));

    // Secondary color.
    $wp_customize->add_setting('powerup_secondary_color', array(
        'default'           => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control(
        $wp_customize,
        'powerup_secondary_color',
        array(
            'label'    => __('Secondary Color', 'powerup-theme'),
            'section'  => 'powerup_colors',
            'settings' => 'powerup_secondary_color',
        )
    ));

    // Background color.
    $wp_customize->add_setting('powerup_background_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control(
        $wp_customize,
        'powerup_background_color',
        array(
            'label'    => __('Background Color', 'powerup-theme'),
            'section'  => 'powerup_colors',
            'settings' => 'powerup_background_color',
        )
    ));

    // Typography section.
    $wp_customize->add_section('powerup_typography', array(
        'title'    => __('Typography', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 20,
    ));

    // Base font size.
    $wp_customize->add_setting('powerup_base_font_size', array(
        'default'           => '16',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_base_font_size', array(
        'label'       => __('Base Font Size (px)', 'powerup-theme'),
        'section'     => 'powerup_typography',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 12,
            'max'  => 24,
            'step' => 1,
        ),
    ));

    // Font family.
    $wp_customize->add_setting('powerup_font_family', array(
        'default'           => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_font_family', array(
        'label'   => __('Font Family', 'powerup-theme'),
        'section' => 'powerup_typography',
        'type'    => 'text',
    ));

    // Header section.
    $wp_customize->add_section('powerup_header', array(
        'title'    => __('Header', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 30,
    ));

    // Show search in header.
    $wp_customize->add_setting('powerup_show_header_search', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_show_header_search', array(
        'label'   => __('Show Search in Header', 'powerup-theme'),
        'section' => 'powerup_header',
        'type'    => 'checkbox',
    ));

    // Sticky header.
    $wp_customize->add_setting('powerup_sticky_header', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_sticky_header', array(
        'label'   => __('Sticky Header', 'powerup-theme'),
        'section' => 'powerup_header',
        'type'    => 'checkbox',
    ));

    // Footer section.
    $wp_customize->add_section('powerup_footer', array(
        'title'    => __('Footer', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 40,
    ));

    // Footer copyright text.
    $wp_customize->add_setting('powerup_footer_copyright', array(
        'default'           => sprintf(__('© %s. All rights reserved.', 'powerup-theme'), date('Y')),
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_footer_copyright', array(
        'label'   => __('Footer Copyright Text', 'powerup-theme'),
        'section' => 'powerup_footer',
        'type'    => 'textarea',
    ));

    // Show social icons in footer.
    $wp_customize->add_setting('powerup_show_footer_social', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_show_footer_social', array(
        'label'   => __('Show Social Icons in Footer', 'powerup-theme'),
        'section' => 'powerup_footer',
        'type'    => 'checkbox',
    ));

    // Blog section.
    $wp_customize->add_section('powerup_blog', array(
        'title'    => __('Blog', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 50,
    ));

    // Show featured images on blog archive.
    $wp_customize->add_setting('powerup_show_blog_featured_image', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_show_blog_featured_image', array(
        'label'   => __('Show Featured Images on Blog Archive', 'powerup-theme'),
        'section' => 'powerup_blog',
        'type'    => 'checkbox',
    ));

    // Show post meta.
    $wp_customize->add_setting('powerup_show_post_meta', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_show_post_meta', array(
        'label'   => __('Show Post Meta (author, date, categories)', 'powerup-theme'),
        'section' => 'powerup_blog',
        'type'    => 'checkbox',
    ));

    // Show read more links.
    $wp_customize->add_setting('powerup_show_read_more', array(
        'default'           => true,
        'sanitize_callback' => 'powerup_sanitize_checkbox',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_show_read_more', array(
        'label'   => __('Show "Read More" Links', 'powerup-theme'),
        'section' => 'powerup_blog',
        'type'    => 'checkbox',
    ));

    // WooCommerce section (if WooCommerce is active).
    if (class_exists('WooCommerce')) {
        $wp_customize->add_section('powerup_woocommerce', array(
            'title'    => __('WooCommerce', 'powerup-theme'),
            'panel'    => 'powerup_theme_options',
            'priority' => 60,
        ));

        // Products per row.
        $wp_customize->add_setting('powerup_products_per_row', array(
            'default'           => '4',
            'sanitize_callback' => 'powerup_sanitize_select',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control('powerup_products_per_row', array(
            'label'   => __('Products Per Row', 'powerup-theme'),
            'section' => 'powerup_woocommerce',
            'type'    => 'select',
            'choices' => array(
                '2' => __('2', 'powerup-theme'),
                '3' => __('3', 'powerup-theme'),
                '4' => __('4', 'powerup-theme'),
                '5' => __('5', 'powerup-theme'),
                '6' => __('6', 'powerup-theme'),
            ),
        ));

        // Products per page.
        $wp_customize->add_setting('powerup_products_per_page', array(
            'default'           => '12',
            'sanitize_callback' => 'absint',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control('powerup_products_per_page', array(
            'label'       => __('Products Per Page', 'powerup-theme'),
            'section'     => 'powerup_woocommerce',
            'type'        => 'number',
            'input_attrs' => array(
                'min'  => 1,
                'max'  => 100,
                'step' => 1,
            ),
        ));

        // Show product quick view.
        $wp_customize->add_setting('powerup_show_quick_view', array(
            'default'           => true,
            'sanitize_callback' => 'powerup_sanitize_checkbox',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control('powerup_show_quick_view', array(
            'label'   => __('Show Quick View Button', 'powerup-theme'),
            'section' => 'powerup_woocommerce',
            'type'    => 'checkbox',
        ));
    }

    // Advanced section.
    $wp_customize->add_section('powerup_advanced', array(
        'title'    => __('Advanced', 'powerup-theme'),
        'panel'    => 'powerup_theme_options',
        'priority' => 100,
    ));

    // Custom CSS.
    $wp_customize->add_setting('powerup_custom_css', array(
        'default'           => '',
        'sanitize_callback' => 'wp_strip_all_tags',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_custom_css', array(
        'label'   => __('Custom CSS', 'powerup-theme'),
        'section' => 'powerup_advanced',
        'type'    => 'textarea',
        'description' => __('Add custom CSS code here. It will be included in the theme.', 'powerup-theme'),
    ));

    // Custom JavaScript (head).
    $wp_customize->add_setting('powerup_custom_js_head', array(
        'default'           => '',
        'sanitize_callback' => 'wp_strip_all_tags',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_custom_js_head', array(
        'label'   => __('Custom JavaScript (Head)', 'powerup-theme'),
        'section' => 'powerup_advanced',
        'type'    => 'textarea',
        'description' => __('Add custom JavaScript code for the head section.', 'powerup-theme'),
    ));

    // Custom JavaScript (footer).
    $wp_customize->add_setting('powerup_custom_js_footer', array(
        'default'           => '',
        'sanitize_callback' => 'wp_strip_all_tags',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('powerup_custom_js_footer', array(
        'label'   => __('Custom JavaScript (Footer)', 'powerup-theme'),
        'section' => 'powerup_advanced',
        'type'    => 'textarea',
        'description' => __('Add custom JavaScript code for the footer section.', 'powerup-theme'),
    ));
}
add_action('customize_register', 'powerup_customize_register');

/**
 * Enqueue Customizer preview JavaScript.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_customize_preview_js() {
    wp_enqueue_script(
        'powerup-customizer-preview',
        get_template_directory_uri() . '/assets/js/customizer-preview.js',
        array('customize-preview', 'jquery'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('customize_preview_init', 'powerup_customize_preview_js');

/**
 * Output Customizer CSS to frontend.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_customizer_css() {
    echo '<style type="text/css">';

    // Primary color.
    $primary_color = get_theme_mod('powerup_primary_color', '#ff6200');
    if ($primary_color !== '#ff6200') {
        echo ":root { --color-primary: {$primary_color}; }";
        echo "a, .primary-color { color: {$primary_color}; }";
        echo ".button-primary { background-color: {$primary_color}; }";
    }

    // Secondary color.
    $secondary_color = get_theme_mod('powerup_secondary_color', '#333333');
    if ($secondary_color !== '#333333') {
        echo ":root { --color-secondary: {$secondary_color}; }";
        echo ".secondary-color { color: {$secondary_color}; }";
    }

    // Background color.
    $background_color = get_theme_mod('powerup_background_color', '#ffffff');
    if ($background_color !== '#ffffff') {
        echo ":root { --color-background: {$background_color}; }";
        echo "body { background-color: {$background_color}; }";
    }

    // Base font size.
    $base_font_size = get_theme_mod('powerup_base_font_size', '16');
    if ($base_font_size !== '16') {
        echo "html { font-size: {$base_font_size}px; }";
    }

    // Font family.
    $font_family = get_theme_mod('powerup_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif');
    if ($font_family !== 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif') {
        echo "body { font-family: {$font_family}; }";
    }

    // Custom CSS.
    $custom_css = get_theme_mod('powerup_custom_css', '');
    if (!empty($custom_css)) {
        echo $custom_css;
    }

    echo '</style>';
}
add_action('wp_head', 'powerup_customizer_css');

/**
 * Output Customizer JavaScript to frontend.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_customizer_js() {
    // Head JavaScript.
    $custom_js_head = get_theme_mod('powerup_custom_js_head', '');
    if (!empty($custom_js_head)) {
        echo '<script>' . $custom_js_head . '</script>';
    }

    // Footer JavaScript.
    $custom_js_footer = get_theme_mod('powerup_custom_js_footer', '');
    if (!empty($custom_js_footer)) {
        echo '<script>' . $custom_js_footer . '</script>';
    }
}
add_action('wp_footer', 'powerup_customizer_js');

/**
 * Sanitize checkbox for Customizer.
 *
 * @since 1.0.0
 * @param mixed $input Checkbox value.
 * @return bool Sanitized value.
 */
function powerup_sanitize_checkbox($input) {
    return (bool) $input;
}

/**
 * Sanitize select for Customizer.
 *
 * @since 1.0.0
 * @param mixed $input   Input value.
 * @param mixed $setting Setting object.
 * @return mixed Sanitized value.
 */
function powerup_sanitize_select($input, $setting) {
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control($setting->id)->choices;
    return (array_key_exists($input, $choices) ? $input : $setting->default);
}

/**
 * Get all Customizer settings as array.
 *
 * @since 1.0.0
 * @return array Customizer settings.
 */
function powerup_get_customizer_settings() {
    $settings = array(
        'colors' => array(
            'primary'   => get_theme_mod('powerup_primary_color', '#ff6200'),
            'secondary' => get_theme_mod('powerup_secondary_color', '#333333'),
            'background' => get_theme_mod('powerup_background_color', '#ffffff'),
        ),
        'typography' => array(
            'base_font_size' => get_theme_mod('powerup_base_font_size', '16'),
            'font_family'    => get_theme_mod('powerup_font_family', 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'),
        ),
        'header' => array(
            'show_search' => get_theme_mod('powerup_show_header_search', true),
            'sticky'      => get_theme_mod('powerup_sticky_header', true),
        ),
        'footer' => array(
            'copyright' => get_theme_mod('powerup_footer_copyright', sprintf(__('© %s. All rights reserved.', 'powerup-theme'), date('Y'))),
            'show_social' => get_theme_mod('powerup_show_footer_social', true),
        ),
        'blog' => array(
            'show_featured_image' => get_theme_mod('powerup_show_blog_featured_image', true),
            'show_post_meta'      => get_theme_mod('powerup_show_post_meta', true),
            'show_read_more'      => get_theme_mod('powerup_show_read_more', true),
        ),
        'advanced' => array(
            'custom_css'      => get_theme_mod('powerup_custom_css', ''),
            'custom_js_head'  => get_theme_mod('powerup_custom_js_head', ''),
            'custom_js_footer' => get_theme_mod('powerup_custom_js_footer', ''),
        ),
    );

    if (class_exists('WooCommerce')) {
        $settings['woocommerce'] = array(
            'products_per_row'  => get_theme_mod('powerup_products_per_row', '4'),
            'products_per_page' => get_theme_mod('powerup_products_per_page', '12'),
            'show_quick_view'   => get_theme_mod('powerup_show_quick_view', true),
        );
    }

    return $settings;
}