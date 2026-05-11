<?php
/**
 * PowerUp Theme WooCommerce Integration
 *
 * @package PowerUp_Theme
 * @subpackage WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Declare WooCommerce support.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'powerup_woocommerce_setup');

/**
 * Enqueue WooCommerce-specific styles and scripts.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_assets() {
    wp_enqueue_style(
        'powerup-woocommerce-style',
        get_template_directory_uri() . '/assets/css/woocommerce.css',
        array('woocommerce-general'),
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'powerup-woocommerce-script',
        get_template_directory_uri() . '/assets/js/woocommerce.js',
        array('jquery', 'woocommerce'),
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'powerup_woocommerce_assets');

/**
 * Remove default WooCommerce styles and add our own.
 *
 * @since 1.0.0
 * @param array $enqueue_styles Array of WooCommerce styles to enqueue.
 * @return array Modified styles array.
 */
function powerup_woocommerce_style_management($enqueue_styles) {
    unset($enqueue_styles['woocommerce-general']);
    unset($enqueue_styles['woocommerce-layout']);
    unset($enqueue_styles['woocommerce-smallscreen']);

    $enqueue_styles['powerup-woocommerce'] = array(
        'src'     => get_template_directory_uri() . '/assets/css/woocommerce.css',
        'deps'    => '',
        'version' => wp_get_theme()->get('Version'),
        'media'   => 'all',
        'has_rtl' => false,
    );

    return $enqueue_styles;
}
add_filter('woocommerce_enqueue_styles', 'powerup_woocommerce_style_management');

/**
 * Update WooCommerce product image sizes.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_image_sizes() {
    update_option('woocommerce_single_image_width', 800);

    update_option('woocommerce_thumbnail_image_width', 400);
    update_option('woocommerce_thumbnail_cropping', 'custom');
    update_option('woocommerce_thumbnail_cropping_custom_width', 4);
    update_option('woocommerce_thumbnail_cropping_custom_height', 5);

    update_option('woocommerce_gallery_thumbnail_image_width', 200);
}
add_action('init', 'powerup_woocommerce_image_sizes');

/**
 * Change number of products per row.
 *
 * @since 1.0.0
 * @return int Number of columns.
 */
function powerup_woocommerce_loop_columns() {
    return 4; // 4 products per row on desktop.
}
add_filter('loop_shop_columns', 'powerup_woocommerce_loop_columns');

/**
 * Change number of related products output.
 *
 * @since 1.0.0
 * @param array $args Related products args.
 * @return array Modified args.
 */
function powerup_woocommerce_related_products_args($args) {
    $args['posts_per_page'] = 4; // 4 related products.
    $args['columns'] = 4; // Arranged in 4 columns.
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'powerup_woocommerce_related_products_args');

/**
 * Change number of products per page.
 *
 * @since 1.0.0
 * @return int Number of products per page.
 */
function powerup_woocommerce_products_per_page() {
    return 12; // 12 products per page.
}
add_filter('loop_shop_per_page', 'powerup_woocommerce_products_per_page');

/**
 * Remove WooCommerce breadcrumbs (we use our own).
 *
 * @since 1.0.0
 * @return void
 */
function powerup_remove_woocommerce_breadcrumbs() {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
}
add_action('init', 'powerup_remove_woocommerce_breadcrumbs');

/**
 * Add responsive product images with srcset support.
 *
 * @since 1.0.0
 * @param string $html       Product image HTML.
 * @param int    $product_id Product ID.
 * @return string Modified HTML.
 */
function powerup_woocommerce_responsive_product_image($html, $product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        return $html;
    }

    $image_id = $product->get_image_id();
    if (!$image_id) {
        return $html;
    }

    $html = wp_get_attachment_image(
        $image_id,
        array('product_large', 'product_medium', 'product_small'),
        false,
        array(
            'srcset' => wp_get_attachment_image_srcset($image_id),
            'sizes'  => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
            'loading' => 'lazy',
            'class'   => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail product-image-responsive',
            'alt'     => $product->get_name(),
        )
    );

    return $html;
}
add_filter('woocommerce_product_get_image', 'powerup_woocommerce_responsive_product_image', 10, 2);

/**
 * Add responsive images to product gallery.
 *
 * @since 1.0.0
 * @param array $html       Gallery image HTML.
 * @param int   $attachment_id Attachment ID.
 * @return array Modified HTML.
 */
function powerup_woocommerce_responsive_gallery_image($html, $attachment_id) {
    $full_size_image = wp_get_attachment_image_src($attachment_id, 'full');
    $thumbnail       = wp_get_attachment_image_src($attachment_id, 'shop_thumbnail');

    $full_url = (is_array($full_size_image) && !empty($full_size_image[0])) ? $full_size_image[0] : '';
    $thumb_url = (is_array($thumbnail) && !empty($thumbnail[0])) ? $thumbnail[0] : '';

    if ('' === $full_url) {
        return $html;
    }

    if ('' === $thumb_url) {
        $thumb_url = $full_url;
    }

    $full_width = (is_array($full_size_image) && !empty($full_size_image[1])) ? (int) $full_size_image[1] : 0;
    $full_height = (is_array($full_size_image) && !empty($full_size_image[2])) ? (int) $full_size_image[2] : 0;

    $html = '<div data-thumb="' . esc_url($thumb_url) . '" data-thumb-alt="' . esc_attr(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) . '">';
    $html .= wp_get_attachment_image(
        $attachment_id,
        array('product_large', 'product_medium', 'product_small'),
        false,
        array(
            'srcset' => wp_get_attachment_image_srcset($attachment_id),
            'sizes'  => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
            'loading' => 'lazy',
            'data-large_image'        => $full_url,
            'data-large_image_width'  => $full_width,
            'data-large_image_height' => $full_height,
        )
    );
    $html .= '</div>';

    return $html;
}
add_filter('woocommerce_single_product_image_thumbnail_html', 'powerup_woocommerce_responsive_gallery_image', 10, 2);

/**
 * Add currency switcher support.
 *
 * @since 1.0.0
 * @return array Currency switcher items.
 */
function powerup_get_currency_switcher_items() {
    $items = array();

    if (class_exists('WOOCS')) {
        global $WOOCS;
        $currencies = $WOOCS->get_currencies();
        $base_url = home_url(add_query_arg(null, null));
        $current = $WOOCS->current_currency;

        foreach ($currencies as $currency_code => $currency_data) {
            $items[] = array(
                'code'    => $currency_code,
                'symbol'  => isset($currency_data['symbol']) ? (string) $currency_data['symbol'] : get_woocommerce_currency_symbol($currency_code),
                'url'     => add_query_arg('currency', rawurlencode($currency_code), $base_url),
                'current' => '' !== $current && $currency_code === $current,
            );
        }
    }

    return apply_filters('powerup_currency_switcher_items', $items);
}

/**
 * Update cart contents count via AJAX.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_cart_count() {
    if (!wp_doing_ajax()) {
        return;
    }

    check_ajax_referer('powerup_ajax_nonce', 'nonce');

    echo WC()->cart->get_cart_contents_count();
    wp_die();
}
add_action('wp_ajax_powerup_update_cart_count', 'powerup_woocommerce_cart_count');
add_action('wp_ajax_nopriv_powerup_update_cart_count', 'powerup_woocommerce_cart_count');

/**
 * Add quick view functionality.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_quick_view() {
    if (!wp_doing_ajax()) {
        return;
    }

    check_ajax_referer('powerup_ajax_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_die('Invalid product ID');
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_die('Product not found');
    }

    wc_get_template('quick-view.php', array('product' => $product));

    wp_die();
}
add_action('wp_ajax_powerup_quick_view', 'powerup_woocommerce_quick_view');
add_action('wp_ajax_nopriv_powerup_quick_view', 'powerup_woocommerce_quick_view');

/**
 * Add wishlist functionality.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_woocommerce_wishlist() {
    if (!wp_doing_ajax()) {
        return;
    }

    check_ajax_referer('powerup_ajax_nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $action = isset($_POST['wishlist_action']) ? sanitize_text_field($_POST['wishlist_action']) : '';

    if (!$product_id || !$action) {
        wp_send_json_error('Invalid request');
    }

    $user_id = get_current_user_id();
    $wishlist = get_user_meta($user_id, 'powerup_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    if ('add' === $action && !in_array($product_id, $wishlist, true)) {
        $wishlist[] = $product_id;
        update_user_meta($user_id, 'powerup_wishlist', $wishlist);
        wp_send_json_success('Product added to wishlist');
    } elseif ('remove' === $action) {
        $wishlist = array_diff($wishlist, array($product_id));
        update_user_meta($user_id, 'powerup_wishlist', $wishlist);
        wp_send_json_success('Product removed from wishlist');
    }

    wp_send_json_error('Invalid action');
}
add_action('wp_ajax_powerup_wishlist', 'powerup_woocommerce_wishlist');