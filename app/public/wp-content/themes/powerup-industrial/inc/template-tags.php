<?php
/**
 * PowerUp Theme Template Tags
 *
 * @package PowerUp_Theme
 * @subpackage Template_Tags
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display post meta information.
 *
 * @since 1.0.0
 * @param array $args {
 *     Optional. Post meta arguments.
 *
 *     @type bool $show_author   Whether to show author. Default true.
 *     @type bool $show_date     Whether to show date. Default true.
 *     @type bool $show_category Whether to show category. Default true.
 *     @type bool $show_comments Whether to show comments count. Default false.
 *     @type string $before      HTML to prepend. Default '<div class="post-meta">'.
 *     @type string $after       HTML to append. Default '</div>'.
 *     @type string $sep         Separator between items. Default ' / '.
 * }
 * @return void
 */
function powerup_post_meta($args = array()) {
    $defaults = array(
        'show_author'   => true,
        'show_date'     => true,
        'show_category' => true,
        'show_comments' => false,
        'before'        => '<div class="post-meta">',
        'after'         => '</div>',
        'sep'           => ' / ',
    );
    $args = wp_parse_args($args, $defaults);

    $meta_items = array();

    if ($args['show_author']) {
        $meta_items[] = sprintf(
            '<span class="post-author">%s <a href="%s">%s</a></span>',
            __('By', 'powerup-theme'),
            esc_url(get_author_posts_url(get_the_author_meta('ID'))),
            esc_html(get_the_author())
        );
    }

    if ($args['show_date']) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
        if (get_the_time('U') !== get_the_modified_time('U')) {
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
        }

        $meta_items[] = sprintf(
            '<span class="post-date">%s</span>',
            sprintf(
                $time_string,
                esc_attr(get_the_date(DATE_W3C)),
                esc_html(get_the_date()),
                esc_attr(get_the_modified_date(DATE_W3C)),
                esc_html(get_the_modified_date())
            )
        );
    }

    if ($args['show_category'] && has_category()) {
        $categories = get_the_category();
        $category_links = array();
        foreach ($categories as $category) {
            $category_links[] = sprintf(
                '<a href="%s" rel="category tag">%s</a>',
                esc_url(get_category_link($category->term_id)),
                esc_html($category->name)
            );
        }
        $meta_items[] = sprintf(
            '<span class="post-category">%s %s</span>',
            __('In', 'powerup-theme'),
            implode(', ', $category_links)
        );
    }

    if ($args['show_comments'] && comments_open()) {
        $comments_number = get_comments_number();
        $comments_text = sprintf(
            _n('%s comment', '%s comments', $comments_number, 'powerup-theme'),
            number_format_i18n($comments_number)
        );
        $meta_items[] = sprintf(
            '<span class="post-comments"><a href="%s">%s</a></span>',
            esc_url(get_comments_link()),
            $comments_text
        );
    }

    if (empty($meta_items)) {
        return;
    }

    echo $args['before'] . implode($args['sep'], $meta_items) . $args['after'];
}

/**
 * Display post thumbnail with optional link.
 *
 * @since 1.0.0
 * @param string $size       Image size.
 * @param bool   $link       Whether to link to post.
 * @param array  $attr       Image attributes.
 * @param string $fallback   Fallback text.
 * @return void
 */
function powerup_post_thumbnail($size = 'post-thumbnail', $link = true, $attr = array(), $fallback = '') {
    if (!has_post_thumbnail()) {
        if ($fallback) {
            echo '<div class="post-thumbnail-fallback">' . esc_html($fallback) . '</div>';
        }
        return;
    }

    $thumbnail = get_the_post_thumbnail(null, $size, $attr);

    if ($link) {
        printf(
            '<a class="post-thumbnail-link" href="%s" aria-hidden="true" tabindex="-1">%s</a>',
            esc_url(get_permalink()),
            $thumbnail
        );
    } else {
        echo '<div class="post-thumbnail">' . $thumbnail . '</div>';
    }
}

/**
 * Display entry footer with tags and edit link.
 *
 * @since 1.0.0
 * @return void
 */
function powerup_entry_footer() {
    if ('post' === get_post_type()) {
        $tags_list = get_the_tag_list('', ', ');
        if ($tags_list) {
            printf(
                '<span class="tags-links">%s %s</span>',
                __('Tagged:', 'powerup-theme'),
                $tags_list
            );
        }
    }

    edit_post_link(
        sprintf(
            __('Edit %s', 'powerup-theme'),
            '<span class="screen-reader-text">' . get_the_title() . '</span>'
        ),
        '<span class="edit-link">',
        '</span>'
    );
}

/**
 * Display paginated links for post navigation.
 *
 * @since 1.0.0
 * @param array $args {
 *     Optional. Post navigation arguments.
 *
 *     @type string $prev_text Text for previous post link.
 *     @type string $next_text Text for next post link.
 *     @type bool   $in_same_term Whether to stay in same taxonomy term.
 *     @type string $taxonomy    Taxonomy name if $in_same_term is true.
 *     @type string $screen_reader_text Screen reader text for nav.
 * }
 * @return void
 */
function powerup_post_navigation($args = array()) {
    $defaults = array(
        'prev_text' => '%title',
        'next_text' => '%title',
        'in_same_term' => false,
        'taxonomy' => 'category',
        'screen_reader_text' => __('Post navigation', 'powerup-theme'),
    );
    $args = wp_parse_args($args, $defaults);

    $navigation = '';

    $previous = get_previous_post_link(
        '<div class="nav-previous">%link</div>',
        $args['prev_text'],
        $args['in_same_term'],
        '',
        $args['taxonomy']
    );

    $next = get_next_post_link(
        '<div class="nav-next">%link</div>',
        $args['next_text'],
        $args['in_same_term'],
        '',
        $args['taxonomy']
    );

    if ($previous || $next) {
        $navigation = _navigation_markup($previous . $next, 'post-navigation', $args['screen_reader_text']);
    }

    echo $navigation;
}

/**
 * Display comments template.
 *
 * @since 1.0.0
 * @param array $args {
 *     Optional. Comments arguments.
 *
 *     @type string $style       Style of comments listing.
 *     @type string $type        Type of comments to show.
 *     @type string $reply_text  Reply button text.
 *     @type string $login_text  Login prompt text.
 * }
 * @return void
 */
function powerup_comments_template($args = array()) {
    $defaults = array(
        'style'      => 'div',
        'type'       => 'all',
        'reply_text' => __('Reply', 'powerup-theme'),
        'login_text' => __('Log in to reply', 'powerup-theme'),
    );
    $args = wp_parse_args($args, $defaults);

    if (comments_open() || get_comments_number()) {
        comments_template();
    }
}

/**
 * Display social sharing buttons.
 *
 * @since 1.0.0
 * @param array $platforms Platforms to display.
 * @param array $args      Additional arguments.
 * @return void
 */
function powerup_social_sharing($platforms = array(), $args = array()) {
    $default_platforms = array('facebook', 'twitter', 'linkedin', 'pinterest');
    if (empty($platforms)) {
        $platforms = $default_platforms;
    }

    $defaults = array(
        'title'   => get_the_title(),
        'url'     => get_permalink(),
        'summary' => get_the_excerpt(),
        'image'   => get_the_post_thumbnail_url(),
        'before'  => '<div class="social-sharing">',
        'after'   => '</div>',
        'label'   => __('Share:', 'powerup-theme'),
    );
    $args = wp_parse_args($args, $defaults);

    $sharing_links = array();
    foreach ($platforms as $platform) {
        $share_url = powerup_get_share_url($platform, $args);
        if (empty($share_url)) {
            continue;
        }

        $icon_class = "social-icon-{$platform}";
        $aria_label = sprintf(__('Share on %s', 'powerup-theme'), ucfirst($platform));

        $sharing_links[] = sprintf(
            '<a href="%s" class="social-share-link %s" aria-label="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url($share_url),
            esc_attr($icon_class),
            esc_attr($aria_label),
            esc_html(ucfirst($platform))
        );
    }

    if (empty($sharing_links)) {
        return;
    }

    echo $args['before'];
    if ($args['label']) {
        echo '<span class="social-sharing-label">' . esc_html($args['label']) . '</span>';
    }
    echo implode('', $sharing_links);
    echo $args['after'];
}

/**
 * Display breadcrumbs.
 *
 * @since 1.0.0
 * @param array $args Breadcrumb arguments.
 * @return void
 */
function powerup_breadcrumbs($args = array()) {
    echo powerup_get_breadcrumbs($args);
}

/**
 * Display pagination.
 *
 * @since 1.0.0
 * @param array $args Pagination arguments.
 * @return void
 */
function powerup_pagination($args = array()) {
    echo powerup_get_pagination($args);
}

/**
 * Display language switcher.
 *
 * @since 1.0.0
 * @param array $args Language switcher arguments.
 * @return void
 */
function powerup_language_switcher($args = array()) {
    $defaults = array(
        'show_flags' => true,
        'show_names' => true,
        'dropdown'   => false,
        'before'     => '<div class="language-switcher">',
        'after'      => '</div>',
    );
    $args = wp_parse_args($args, $defaults);

    $items = powerup_get_language_switcher_items();
    if (empty($items)) {
        return;
    }

    echo $args['before'];

    if ($args['dropdown']) {
        echo '<select class="language-switcher-dropdown" onchange="window.location.href=this.value;">';
        foreach ($items as $item) {
            printf(
                '<option value="%s"%s>%s%s</option>',
                esc_url($item['url']),
                $item['current'] ? ' selected' : '',
                $args['show_flags'] ? '<span class="language-flag">' . esc_html($item['code']) . '</span> ' : '',
                $args['show_names'] ? esc_html($item['label']) : esc_html($item['code'])
            );
        }
        echo '</select>';
    } else {
        echo '<ul class="language-switcher-list">';
        foreach ($items as $item) {
            printf(
                '<li class="language-item%s"><a href="%s">%s%s</a></li>',
                $item['current'] ? ' current-language' : '',
                esc_url($item['url']),
                $args['show_flags'] ? '<span class="language-flag">' . esc_html($item['code']) . '</span> ' : '',
                $args['show_names'] ? esc_html($item['label']) : esc_html($item['code'])
            );
        }
        echo '</ul>';
    }

    echo $args['after'];
}

/**
 * Display currency switcher.
 *
 * @since 1.0.0
 * @param array $args Currency switcher arguments.
 * @return void
 */
function powerup_currency_switcher($args = array()) {
    $defaults = array(
        'show_symbols' => true,
        'show_codes'   => true,
        'dropdown'     => false,
        'before'       => '<div class="currency-switcher">',
        'after'        => '</div>',
    );
    $args = wp_parse_args($args, $defaults);

    $items = powerup_get_currency_switcher_items();
    if (empty($items)) {
        return;
    }

    echo $args['before'];

    if ($args['dropdown']) {
        echo '<select class="currency-switcher-dropdown" onchange="window.location.href=this.value;">';
        foreach ($items as $item) {
            printf(
                '<option value="%s"%s>%s%s</option>',
                esc_url($item['url']),
                $item['current'] ? ' selected' : '',
                $args['show_symbols'] ? esc_html($item['symbol']) . ' ' : '',
                $args['show_codes'] ? esc_html($item['code']) : ''
            );
        }
        echo '</select>';
    } else {
        echo '<ul class="currency-switcher-list">';
        foreach ($items as $item) {
            printf(
                '<li class="currency-item%s"><a href="%s">%s%s</a></li>',
                $item['current'] ? ' current-currency' : '',
                esc_url($item['url']),
                $args['show_symbols'] ? '<span class="currency-symbol">' . esc_html($item['symbol']) . '</span> ' : '',
                $args['show_codes'] ? esc_html($item['code']) : ''
            );
        }
        echo '</ul>';
    }

    echo $args['after'];
}

/**
 * Display related posts.
 *
 * @since 1.0.0
 * @param int   $post_id      Post ID.
 * @param int   $number_posts Number of posts to show.
 * @param array $args         Additional arguments.
 * @return void
 */
function powerup_related_posts($post_id = null, $number_posts = 3, $args = array()) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $defaults = array(
        'orderby' => 'rand',
        'post_type' => 'post',
        'taxonomy' => 'category',
        'before' => '<div class="related-posts">',
        'after'  => '</div>',
        'title'  => __('Related Posts', 'powerup-theme'),
    );
    $args = wp_parse_args($args, $defaults);

    $categories = get_the_terms($post_id, $args['taxonomy']);
    if (!$categories) {
        return;
    }

    $category_ids = array();
    foreach ($categories as $category) {
        $category_ids[] = $category->term_id;
    }

    $query_args = array(
        'post_type'      => $args['post_type'],
        'posts_per_page' => $number_posts,
        'post__not_in'   => array($post_id),
        'tax_query'      => array(
            array(
                'taxonomy' => $args['taxonomy'],
                'field'    => 'term_id',
                'terms'    => $category_ids,
            ),
        ),
        'orderby'        => $args['orderby'],
    );

    $related_posts = new WP_Query($query_args);

    if (!$related_posts->have_posts()) {
        return;
    }

    echo $args['before'];
    if ($args['title']) {
        echo '<h3 class="related-posts-title">' . esc_html($args['title']) . '</h3>';
    }
    echo '<div class="related-posts-grid">';

    while ($related_posts->have_posts()) {
        $related_posts->the_post();
        ?>
        <article class="related-post">
            <?php if (has_post_thumbnail()) : ?>
                <a href="<?php the_permalink(); ?>" class="related-post-thumbnail">
                    <?php the_post_thumbnail('medium'); ?>
                </a>
            <?php endif; ?>
            <h4 class="related-post-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h4>
            <div class="related-post-excerpt">
                <?php the_excerpt(); ?>
            </div>
        </article>
        <?php
    }

    echo '</div>';
    echo $args['after'];

    wp_reset_postdata();
}