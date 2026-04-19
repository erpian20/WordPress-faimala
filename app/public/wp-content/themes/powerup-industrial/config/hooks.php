<?php
/**
 * PowerUp Theme Hooks Configuration
 *
 * @package PowerUp_Theme
 * @subpackage Config
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Hooks Configuration
 *
 * This file defines all action and filter hooks used by the theme.
 * Hooks are organized by priority and functionality.
 */
return array(
    /**
     * Setup Hooks (Priority: 1-100)
     */
    'setup' => array(
        array(
            'hook'     => 'after_setup_theme',
            'callback' => 'powerup_theme_setup',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'after_setup_theme',
            'callback' => 'powerup_content_width',
            'priority' => 0,
            'args'     => 0,
        ),
        array(
            'hook'     => 'after_setup_theme',
            'callback' => 'powerup_woocommerce_setup',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * Init Hooks (Priority: 1-100)
     */
    'init' => array(
        array(
            'hook'     => 'init',
            'callback' => 'powerup_cleanup_assets',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'init',
            'callback' => 'powerup_disable_xmlrpc',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'init',
            'callback' => 'powerup_remove_woocommerce_breadcrumbs',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'init',
            'callback' => 'powerup_woocommerce_image_sizes',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * Widgets Hooks
     */
    'widgets' => array(
        array(
            'hook'     => 'widgets_init',
            'callback' => 'powerup_widgets_init',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * Enqueue Scripts Hooks (Priority: 1-100)
     */
    'enqueue_scripts' => array(
        array(
            'hook'     => 'wp_enqueue_scripts',
            'callback' => 'powerup_enqueue_assets',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_enqueue_scripts',
            'callback' => 'powerup_woocommerce_assets',
            'priority' => 20,
            'args'     => 0,
        ),
        array(
            'hook'     => 'admin_enqueue_scripts',
            'callback' => 'powerup_admin_assets',
            'priority' => 10,
            'args'     => 1,
        ),
        array(
            'hook'     => 'customize_preview_init',
            'callback' => 'powerup_customize_preview_js',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * WP Head Hooks (Priority: 1-100)
     */
    'wp_head' => array(
        array(
            'hook'     => 'wp_head',
            'callback' => 'powerup_preload_assets',
            'priority' => 1,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_head',
            'callback' => 'powerup_customizer_css',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_head',
            'callback' => 'powerup_keyboard_focus_styles',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_head',
            'callback' => 'powerup_theme_render_sitewide_seo_meta_tags',
            'priority' => 1,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_head',
            'callback' => 'powerup_theme_render_post_seo_meta_tags',
            'priority' => 2,
            'args'     => 0,
        ),
    ),

    /**
     * WP Footer Hooks (Priority: 1-100)
     */
    'wp_footer' => array(
        array(
            'hook'     => 'wp_footer',
            'callback' => 'powerup_customizer_js',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_footer',
            'callback' => 'powerup_aria_live_regions',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_footer',
            'callback' => 'powerup_accessibility_statement',
            'priority' => 20,
            'args'     => 0,
        ),
    ),

    /**
     * WP Body Open Hooks (Priority: 1-100)
     */
    'wp_body_open' => array(
        array(
            'hook'     => 'wp_body_open',
            'callback' => 'powerup_skip_link',
            'priority' => 1,
            'args'     => 0,
        ),
    ),

    /**
     * Send Headers Hooks
     */
    'send_headers' => array(
        array(
            'hook'     => 'send_headers',
            'callback' => 'powerup_security_headers',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * Customizer Hooks
     */
    'customizer' => array(
        array(
            'hook'     => 'customize_register',
            'callback' => 'powerup_customize_register',
            'priority' => 10,
            'args'     => 1,
        ),
    ),

    /**
     * WooCommerce Hooks
     */
    'woocommerce' => array(
        // Product image hooks
        array(
            'hook'     => 'woocommerce_product_get_image',
            'callback' => 'powerup_woocommerce_responsive_product_image',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'woocommerce_single_product_image_thumbnail_html',
            'callback' => 'powerup_woocommerce_responsive_gallery_image',
            'priority' => 10,
            'args'     => 2,
        ),

        // Loop hooks
        array(
            'hook'     => 'loop_shop_columns',
            'callback' => 'powerup_woocommerce_loop_columns',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'loop_shop_per_page',
            'callback' => 'powerup_woocommerce_products_per_page',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'woocommerce_output_related_products_args',
            'callback' => 'powerup_woocommerce_related_products_args',
            'priority' => 10,
            'args'     => 1,
        ),

        // Cart hooks
        array(
            'hook'     => 'woocommerce_before_calculate_totals',
            'callback' => 'powerup_theme_apply_tier_pricing_to_cart',
            'priority' => 10,
            'args'     => 1,
        ),
        array(
            'hook'     => 'woocommerce_get_item_data',
            'callback' => 'powerup_theme_cart_item_tier_pricing_data',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'woocommerce_cart_item_price',
            'callback' => 'powerup_theme_cart_item_price_html',
            'priority' => 10,
            'args'     => 3,
        ),
        array(
            'hook'     => 'woocommerce_cart_item_subtotal',
            'callback' => 'powerup_theme_cart_item_subtotal_html',
            'priority' => 10,
            'args'     => 3,
        ),
        array(
            'hook'     => 'woocommerce_cart_item_name',
            'callback' => 'powerup_theme_cart_item_name_with_savings',
            'priority' => 10,
            'args'     => 3,
        ),

        // Product page hooks
        array(
            'hook'     => 'woocommerce_single_product_summary',
            'callback' => 'powerup_theme_render_pdp_tier_pricing',
            'priority' => 25,
            'args'     => 0,
        ),
        array(
            'hook'     => 'woocommerce_product_tabs',
            'callback' => 'powerup_theme_add_shipping_delivery_tab',
            'priority' => 10,
            'args'     => 1,
        ),
    ),

    /**
     * Filter Hooks
     */
    'filters' => array(
        // Script and style filters
        array(
            'hook'     => 'script_loader_tag',
            'callback' => 'powerup_defer_scripts',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'script_loader_tag',
            'callback' => 'powerup_async_scripts',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'style_loader_src',
            'callback' => 'powerup_remove_version_parameter',
            'priority' => 9999,
            'args'     => 1,
        ),
        array(
            'hook'     => 'script_loader_src',
            'callback' => 'powerup_remove_version_parameter',
            'priority' => 9999,
            'args'     => 1,
        ),

        // WooCommerce style management
        array(
            'hook'     => 'woocommerce_enqueue_styles',
            'callback' => 'powerup_woocommerce_style_management',
            'priority' => 10,
            'args'     => 1,
        ),

        // Menu filters
        array(
            'hook'     => 'wp_nav_menu_items',
            'callback' => 'powerup_add_mobile_menu_toggle',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'walker_nav_menu_start_el',
            'callback' => 'powerup_add_dropdown_toggles',
            'priority' => 10,
            'args'     => 4,
        ),
        array(
            'hook'     => 'wp_nav_menu_items',
            'callback' => 'powerup_keyboard_navigation_menu',
            'priority' => 10,
            'args'     => 2,
        ),

        // Image filters
        array(
            'hook'     => 'wp_get_attachment_image',
            'callback' => 'powerup_ensure_image_alt',
            'priority' => 10,
            'args'     => 2,
        ),

        // Search form filters
        array(
            'hook'     => 'get_search_form',
            'callback' => 'powerup_accessible_search_form',
            'priority' => 10,
            'args'     => 1,
        ),

        // WooCommerce form field filters
        array(
            'hook'     => 'woocommerce_form_field',
            'callback' => 'powerup_form_field_accessible_labels',
            'priority' => 10,
            'args'     => 3,
        ),

        // Pagination filters
        array(
            'hook'     => 'paginate_links',
            'callback' => 'powerup_accessible_pagination',
            'priority' => 10,
            'args'     => 1,
        ),

        // Content filters
        array(
            'hook'     => 'the_content',
            'callback' => 'powerup_accessible_videos',
            'priority' => 10,
            'args'     => 1,
        ),
        array(
            'hook'     => 'the_content',
            'callback' => 'powerup_accessible_tables',
            'priority' => 10,
            'args'     => 1,
        ),

        // Body class filters
        array(
            'hook'     => 'body_class',
            'callback' => 'powerup_body_aria_classes',
            'priority' => 10,
            'args'     => 1,
        ),
        array(
            'hook'     => 'body_class',
            'callback' => 'powerup_body_classes',
            'priority' => 10,
            'args'     => 1,
        ),

        // Excerpt filters
        array(
            'hook'     => 'excerpt_more',
            'callback' => 'powerup_excerpt_more',
            'priority' => 10,
            'args'     => 1,
        ),

        // Query filters
        array(
            'hook'     => 'query_vars',
            'callback' => 'powerup_prevent_username_enumeration',
            'priority' => 10,
            'args'     => 1,
        ),

        // Authentication filters
        array(
            'hook'     => 'wp_authenticate_user',
            'callback' => 'powerup_limit_login_attempts',
            'priority' => 10,
            'args'     => 1,
        ),
        array(
            'hook'     => 'the_generator',
            'callback' => 'powerup_remove_version',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * AJAX Hooks
     */
    'ajax' => array(
        array(
            'hook'     => 'wp_ajax_powerup_update_cart_count',
            'callback' => 'powerup_woocommerce_cart_count',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_ajax_nopriv_powerup_update_cart_count',
            'callback' => 'powerup_woocommerce_cart_count',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_ajax_powerup_quick_view',
            'callback' => 'powerup_woocommerce_quick_view',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_ajax_nopriv_powerup_quick_view',
            'callback' => 'powerup_woocommerce_quick_view',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'wp_ajax_powerup_wishlist',
            'callback' => 'powerup_woocommerce_wishlist',
            'priority' => 10,
            'args'     => 0,
        ),
    ),

    /**
     * Login Hooks
     */
    'login' => array(
        array(
            'hook'     => 'wp_login',
            'callback' => 'powerup_reset_login_attempts',
            'priority' => 10,
            'args'     => 2,
        ),
    ),

    /**
     * Admin Hooks
     */
    'admin' => array(
        array(
            'hook'     => 'admin_init',
            'callback' => 'powerup_theme_update',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'add_meta_boxes',
            'callback' => 'powerup_theme_add_marketplace_meta_box',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'add_meta_boxes',
            'callback' => 'powerup_theme_add_tier_pricing_meta_box',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'add_meta_boxes',
            'callback' => 'powerup_theme_add_product_video_meta_box',
            'priority' => 10,
            'args'     => 0,
        ),
        array(
            'hook'     => 'save_post_product',
            'callback' => 'powerup_theme_save_marketplace_meta_box',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'save_post_product',
            'callback' => 'powerup_theme_save_tier_pricing_meta_box',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'save_post_product',
            'callback' => 'powerup_theme_save_product_video_meta_box',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'manage_comments_custom_column',
            'callback' => 'powerup_theme_comments_render_video_column',
            'priority' => 10,
            'args'     => 2,
        ),
        array(
            'hook'     => 'manage_edit-comments_columns',
            'callback' => 'powerup_theme_comments_add_video_column',
            'priority' => 10,
            'args'     => 1,
        ),
    ),

    /**
     * Theme Activation/Deactivation Hooks
     */
    'theme_activation' => array(
        array(
            'hook'     => 'after_switch_theme',
            'callback' => 'powerup_theme_activation_checks',
            'priority' => 10,
            'args'     => 0,
        ),
    ),
);

/**
 * Register all hooks from configuration
 */
function powerup_register_hooks() {
    $hooks_config = include POWERUP_CONFIG_DIR . '/hooks.php';

    foreach ($hooks_config as $hook_group) {
        foreach ($hook_group as $hook_config) {
            if (isset($hook_config['hook']) && isset($hook_config['callback'])) {
                $priority = isset($hook_config['priority']) ? $hook_config['priority'] : 10;
                $args = isset($hook_config['args']) ? $hook_config['args'] : 1;

                if (strpos($hook_config['hook'], 'filter_') === 0) {
                    $hook_name = substr($hook_config['hook'], 7);
                    add_filter($hook_name, $hook_config['callback'], $priority, $args);
                } else {
                    add_action($hook_config['hook'], $hook_config['callback'], $priority, $args);
                }
            }
        }
    }
}

// Register hooks
if (!POWERUP_IS_ADMIN || (defined('DOING_AJAX') && DOING_AJAX)) {
    add_action('init', 'powerup_register_hooks', 1);
}