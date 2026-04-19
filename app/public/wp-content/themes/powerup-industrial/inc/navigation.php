<?php
/**
 * PowerUp Theme Navigation Functions
 *
 * @package PowerUp_Theme
 * @subpackage Navigation
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register navigation menus.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_register_nav_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'powerup-theme'),
        'footer'  => __('Footer Menu', 'powerup-theme'),
        'mobile'  => __('Mobile Menu', 'powerup-theme'),
    ));
}
add_action('after_setup_theme', 'powerup_register_nav_menus');

/**
 * Add responsive menu toggle button for mobile navigation.
 *
 * @since 1.0.0
 * @param string $items The HTML list content for the menu items.
 * @param object $args  An object containing wp_nav_menu() arguments.
 * @return string Modified menu items.
 */
function powerup_add_mobile_menu_toggle($items, $args) {
    if ('primary' === $args->theme_location) {
        $toggle_button = '<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">'
            . '<span class="screen-reader-text">' . __('Menu', 'powerup-theme') . '</span>'
            . '<span class="hamburger"></span>'
            . '</button>';
        $items = $toggle_button . $items;
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'powerup_add_mobile_menu_toggle', 10, 2);

/**
 * Add dropdown toggle buttons for menu items with children.
 *
 * @since 1.0.0
 * @param string   $item_output The menu item's starting HTML output.
 * @param WP_Post  $item        Menu item data object.
 * @param int      $depth       Depth of menu item. Used for padding.
 * @param stdClass $args        An object of wp_nav_menu() arguments.
 * @return string Modified menu item output.
 */
function powerup_add_dropdown_toggles($item_output, $item, $depth, $args) {
    if (in_array('menu-item-has-children', $item->classes, true)) {
        $toggle_button = '<button class="submenu-toggle" aria-expanded="false">'
            . '<span class="screen-reader-text">' . __('Toggle submenu', 'powerup-theme') . '</span>'
            . '<span class="dropdown-icon"></span>'
            . '</button>';
        $item_output .= $toggle_button;
    }
    return $item_output;
}
add_filter('walker_nav_menu_start_el', 'powerup_add_dropdown_toggles', 10, 4);

/**
 * Generate breadcrumb navigation.
 *
 * @since 1.0.0
 * @param array $args {
 *     Optional. Breadcrumb arguments.
 *
 *     @type string $delimiter   Delimiter between crumbs. Default '&raquo;'.
 *     @type string $home        Home link text. Default 'Home'.
 *     @type bool   $show_current Whether to show the current page title. Default true.
 *     @type string $before      HTML to prepend to the current crumb. Default '<span class="current">'.
 *     @type string $after       HTML to append to the current crumb. Default '</span>'.
 * }
 * @return string Breadcrumb HTML.
 */
function powerup_breadcrumbs($args = array()) {
    $defaults = array(
        'delimiter'   => '&raquo;',
        'home'        => __('Home', 'powerup-theme'),
        'show_current' => true,
        'before'      => '<span class="current">',
        'after'       => '</span>',
    );
    $args = wp_parse_args($args, $defaults);

    $breadcrumb = '';
    $home_link = home_url('/');

    // Home link.
    $breadcrumb .= '<a href="' . esc_url($home_link) . '">' . esc_html($args['home']) . '</a>';

    if (is_category()) {
        // Category archive.
        $cat = get_category(get_query_var('cat'), false);
        if ($cat->parent != 0) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . get_category_parents($cat->parent, true, ' ' . $args['delimiter'] . ' ');
        }
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . single_cat_title('', false) . $args['after'];
    } elseif (is_search()) {
        // Search results.
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . __('Search results for:', 'powerup-theme') . ' "' . get_search_query() . '"' . $args['after'];
    } elseif (is_day()) {
        // Daily archive.
        $breadcrumb .= ' ' . $args['delimiter'] . ' <a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>';
        $breadcrumb .= ' ' . $args['delimiter'] . ' <a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a>';
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_time('d') . $args['after'];
    } elseif (is_month()) {
        // Monthly archive.
        $breadcrumb .= ' ' . $args['delimiter'] . ' <a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>';
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_time('F') . $args['after'];
    } elseif (is_year()) {
        // Yearly archive.
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_time('Y') . $args['after'];
    } elseif (is_single() && !is_attachment()) {
        // Single post.
        if (get_post_type() != 'post') {
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            $breadcrumb .= ' ' . $args['delimiter'] . ' <a href="' . $home_link . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
            if ($args['show_current']) {
                $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_title() . $args['after'];
            }
        } else {
            $cat = get_the_category();
            if (!empty($cat)) {
                $cat = $cat[0];
                $breadcrumb .= ' ' . $args['delimiter'] . ' ' . get_category_parents($cat, true, ' ' . $args['delimiter'] . ' ');
            }
            if ($args['show_current']) {
                $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_title() . $args['after'];
            }
        }
    } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
        // Custom post type archive.
        $post_type = get_post_type_object(get_post_type());
        if ($post_type) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . $post_type->labels->singular_name . $args['after'];
        }
    } elseif (is_attachment()) {
        // Attachment.
        $parent = get_post($post->post_parent);
        $cat = get_the_category($parent->ID);
        if (!empty($cat)) {
            $cat = $cat[0];
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . get_category_parents($cat, true, ' ' . $args['delimiter'] . ' ');
        }
        $breadcrumb .= ' ' . $args['delimiter'] . ' <a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
        if ($args['show_current']) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_title() . $args['after'];
        }
    } elseif (is_page() && !$post->post_parent) {
        // Parent page.
        if ($args['show_current']) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_title() . $args['after'];
        }
    } elseif (is_page() && $post->post_parent) {
        // Child page.
        $parent_id = $post->post_parent;
        $breadcrumbs = array();
        while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
            $parent_id = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        foreach ($breadcrumbs as $crumb) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $crumb;
        }
        if ($args['show_current']) {
            $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . get_the_title() . $args['after'];
        }
    } elseif (is_tag()) {
        // Tag archive.
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . __('Posts tagged', 'powerup-theme') . ' "' . single_tag_title('', false) . '"' . $args['after'];
    } elseif (is_author()) {
        // Author archive.
        global $author;
        $userdata = get_userdata($author);
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . __('Articles by', 'powerup-theme') . ' ' . $userdata->display_name . $args['after'];
    } elseif (is_404()) {
        // 404 page.
        $breadcrumb .= ' ' . $args['delimiter'] . ' ' . $args['before'] . __('Error 404', 'powerup-theme') . $args['after'];
    }

    return '<nav class="breadcrumb" aria-label="' . __('Breadcrumb', 'powerup-theme') . '">' . $breadcrumb . '</nav>';
}

/**
 * Generate pagination for archive pages.
 *
 * @since 1.0.0
 * @param array $args {
 *     Optional. Pagination arguments.
 *
 *     @type string $prev_text Text for previous page link. Default '&laquo; Previous'.
 *     @type string $next_text Text for next page link. Default 'Next &raquo;'.
 *     @type string $mid_size  How many numbers to show on each side of current page. Default 2.
 *     @type string $end_size  How many numbers to show at beginning and end. Default 1.
 * }
 * @return string Pagination HTML.
 */
function powerup_pagination($args = array()) {
    global $wp_query;

    $defaults = array(
        'prev_text' => __('&laquo; Previous', 'powerup-theme'),
        'next_text' => __('Next &raquo;', 'powerup-theme'),
        'mid_size'  => 2,
        'end_size'  => 1,
    );
    $args = wp_parse_args($args, $defaults);

    $paginate_links = paginate_links(array(
        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'prev_text' => $args['prev_text'],
        'next_text' => $args['next_text'],
        'mid_size'  => $args['mid_size'],
        'end_size'  => $args['end_size'],
    ));

    if ($paginate_links) {
        return '<nav class="pagination" aria-label="' . __('Page navigation', 'powerup-theme') . '">' . $paginate_links . '</nav>';
    }

    return '';
}

/**
 * Get language switcher items.
 *
 * @since 1.0.0
 * @return array Language switcher items.
 */
function powerup_get_language_switcher_items() {
    $items = array();

    // Polylang support.
    if (function_exists('pll_the_languages')) {
        $languages = pll_the_languages(array(
            'raw'                    => 1,
            'hide_if_empty'          => 0,
            'hide_if_no_translation' => 0,
        ));

        if (is_array($languages)) {
            foreach ($languages as $language) {
                if (empty($language['url'])) {
                    continue;
                }

                $items[] = array(
                    'code'    => isset($language['slug']) ? strtoupper((string) $language['slug']) : '',
                    'label'   => isset($language['name']) ? (string) $language['name'] : '',
                    'url'     => (string) $language['url'],
                    'current' => !empty($language['current_lang']),
                );
            }
        }
    }
    // WPML support.
    elseif (function_exists('icl_get_languages')) {
        $languages = icl_get_languages('skip_missing=0&orderby=code');

        if (is_array($languages)) {
            foreach ($languages as $language) {
                if (empty($language['url'])) {
                    continue;
                }

                $items[] = array(
                    'code'    => isset($language['language_code']) ? strtoupper((string) $language['language_code']) : '',
                    'label'   => isset($language['translated_name']) ? (string) $language['translated_name'] : '',
                    'url'     => (string) $language['url'],
                    'current' => !empty($language['active']),
                );
            }
        }
    }

    return apply_filters('powerup_language_switcher_items', $items);
}