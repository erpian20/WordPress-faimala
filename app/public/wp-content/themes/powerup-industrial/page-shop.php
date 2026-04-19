<?php
/**
 * Template Name: Shop Page
 *
 * @package PowerUp_Theme
 */

get_header();

if ( ! function_exists( 'powerup_theme_shop_category_tree_has_selected_descendant' ) ) {
  function powerup_theme_shop_category_tree_has_selected_descendant( $terms_by_parent, $parent_id, $selected_categories ) {
    if ( empty( $terms_by_parent[ $parent_id ] ) || ! is_array( $terms_by_parent[ $parent_id ] ) ) {
      return false;
    }

    foreach ( $terms_by_parent[ $parent_id ] as $term ) {
      if ( ! $term instanceof WP_Term ) {
        continue;
      }

      if ( in_array( $term->slug, $selected_categories, true ) ) {
        return true;
      }

      if ( powerup_theme_shop_category_tree_has_selected_descendant( $terms_by_parent, (int) $term->term_id, $selected_categories ) ) {
        return true;
      }
    }

    return false;
  }
}

if ( ! function_exists( 'powerup_theme_render_shop_category_filter_items' ) ) {
  function powerup_theme_render_shop_category_filter_items( $terms_by_parent, $parent_id, $selected_categories, $depth = 0 ) {
    if ( empty( $terms_by_parent[ $parent_id ] ) || ! is_array( $terms_by_parent[ $parent_id ] ) ) {
      return;
    }

    foreach ( $terms_by_parent[ $parent_id ] as $term ) {
      if ( ! $term instanceof WP_Term ) {
        continue;
      }

      if ( 0 === $depth && 'uncategorized' === $term->slug ) {
        continue;
      }

      $has_children = ! empty( $terms_by_parent[ (int) $term->term_id ] );
      $is_selected  = in_array( $term->slug, $selected_categories, true );
      $is_open      = $is_selected || ( $has_children && powerup_theme_shop_category_tree_has_selected_descendant( $terms_by_parent, (int) $term->term_id, $selected_categories ) );

      $item_class = 0 === $depth ? 'shop-ref-check-group-head' : 'shop-ref-check-group-child depth-' . (string) $depth;
      $item_class .= ' shop-ref-check-item';
      if ( $has_children ) {
        $item_class .= ' has-children';
      }
      if ( $is_open ) {
        $item_class .= ' is-open';
      }

      ?>
      <li class="<?php echo esc_attr( $item_class ); ?>">
        <div class="shop-ref-check-row">
          <label>
            <input type="checkbox" name="pcat[]" value="<?php echo esc_attr( (string) $term->slug ); ?>" <?php checked( $is_selected ); ?>>
            <span class="shop-ref-cat-label-text"><?php echo esc_html( (string) $term->name ); ?></span>
          </label>
          <div class="shop-ref-check-actions">
            <em><?php echo esc_html( (string) $term->count ); ?></em>
            <?php if ( $has_children ) : ?>
              <button
                type="button"
                class="shop-ref-cat-toggle"
                aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                aria-label="<?php echo esc_attr( sprintf( __( 'Toggle subcategories for %s', 'powerup-theme' ), (string) $term->name ) ); ?>"
              >▾</button>
            <?php endif; ?>
          </div>
        </div>

        <?php if ( $has_children ) : ?>
          <ul class="shop-ref-check-list shop-ref-check-list--grouped shop-ref-check-list--nested" <?php echo $is_open ? '' : 'hidden'; ?>>
            <?php powerup_theme_render_shop_category_filter_items( $terms_by_parent, (int) $term->term_id, $selected_categories, $depth + 1 ); ?>
          </ul>
        <?php endif; ?>
      </li>
      <?php
    }
  }
}

$shop_base_url = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' );
$about_page_url = function_exists( 'powerup_theme_get_about_page_url' ) ? powerup_theme_get_about_page_url() : home_url( '/about-us/' );
$hero_image_path = get_template_directory() . '/assets/images/shop-hero-custom.png';
$hero_image      = get_template_directory_uri() . '/assets/images/shop-hero-custom.png';
if ( file_exists( $hero_image_path ) ) {
  $hero_image .= '?v=' . filemtime( $hero_image_path );
}

$selected_categories = array();
if ( isset( $_GET['pcat'] ) || isset( $_GET['cat'] ) ) {
  $raw_selected_categories = isset( $_GET['pcat'] ) ? (array) wp_unslash( $_GET['pcat'] ) : (array) wp_unslash( $_GET['cat'] );
  $selected_categories = array_values(
    array_unique(
      array_filter(
        array_map(
          'sanitize_title',
          $raw_selected_categories
        )
      )
    )
  );
  sort( $selected_categories, SORT_STRING );
}

$selected_prices = array();
if ( isset( $_GET['price'] ) ) {
  $selected_prices = array_values(
    array_unique(
      array_filter(
        array_map(
          'intval',
          (array) wp_unslash( $_GET['price'] )
        ),
        static function ( $value ) {
          return $value >= 0 && $value <= 3;
        }
      )
    )
  );
  sort( $selected_prices, SORT_NUMERIC );
}

$search_query       = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';
$has_active_filters = '' !== $search_query || ! empty( $selected_categories ) || ! empty( $selected_prices );
$current_page       = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$per_page           = (int) powerup_theme_get_config_value( 'shop.products_per_page', 9 );
$per_page           = $per_page > 0 ? $per_page : 9;

$tax_query = array();
if ( ! empty( $selected_categories ) ) {
  $tax_query[] = array(
    'taxonomy'         => 'product_cat',
    'field'            => 'slug',
    'terms'            => $selected_categories,
    'include_children' => true,
  );
}

$meta_query = array();
if ( ! empty( $selected_prices ) ) {
  $price_or_query = array( 'relation' => 'OR' );
  foreach ( $selected_prices as $price_idx ) {
    if ( 0 === $price_idx ) {
      $price_or_query[] = array(
        'key'     => '_price',
        'value'   => array( 0, 100 ),
        'type'    => 'NUMERIC',
        'compare' => 'BETWEEN',
      );
    } elseif ( 1 === $price_idx ) {
      $price_or_query[] = array(
        'key'     => '_price',
        'value'   => array( 100, 200 ),
        'type'    => 'NUMERIC',
        'compare' => 'BETWEEN',
      );
    } elseif ( 2 === $price_idx ) {
      $price_or_query[] = array(
        'key'     => '_price',
        'value'   => array( 200, 300 ),
        'type'    => 'NUMERIC',
        'compare' => 'BETWEEN',
      );
    } elseif ( 3 === $price_idx ) {
      $price_or_query[] = array(
        'key'     => '_price',
        'value'   => 300,
        'type'    => 'NUMERIC',
        'compare' => '>=',
      );
    }
  }

  if ( count( $price_or_query ) > 1 ) {
    $meta_query[] = $price_or_query;
  }
}

$query_args = array(
  'post_type'      => 'product',
  'post_status'    => 'publish',
  'posts_per_page' => $per_page,
  'paged'          => $current_page,
  's'              => $search_query,
);

if ( ! empty( $tax_query ) ) {
  $query_args['tax_query'] = $tax_query;
}

if ( ! empty( $meta_query ) ) {
  $query_args['meta_query'] = $meta_query;
}

$products_query = new WP_Query( $query_args );

$terms = get_terms(
  array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
  )
);

$terms_by_parent = array();
if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
  foreach ( $terms as $term ) {
    $parent = (int) $term->parent;
    if ( ! isset( $terms_by_parent[ $parent ] ) ) {
      $terms_by_parent[ $parent ] = array();
    }
    $terms_by_parent[ $parent ][] = $term;
  }
}

$price_ranges = array(
  0 => array( 'label' => __( '$0 - $100', 'powerup-theme' ) ),
  1 => array( 'label' => __( '$100 - $200', 'powerup-theme' ) ),
  2 => array( 'label' => __( '$200 - $300', 'powerup-theme' ) ),
  3 => array( 'label' => __( '$300+', 'powerup-theme' ) ),
);

$review_images = array(
  get_template_directory_uri() . '/assets/images/blog-cover-complete-guide.svg',
  get_template_directory_uri() . '/assets/images/blog-cover-chainsaw-maintenance.svg',
  get_template_directory_uri() . '/assets/images/blog-cover-20v-40v.svg',
  get_template_directory_uri() . '/assets/images/blog-cover-leaf-blower.svg',
);
?>

<main class="shop-reference-page">
  <section class="shop-ref-hero" style="background-image: linear-gradient(90deg, rgba(15,15,15,0.92) 0%, rgba(20,20,20,0.72) 42%, rgba(20,20,20,0.16) 100%), url('<?php echo esc_url( $hero_image ); ?>');">
    <div class="shop-ref-hero-inner">
      <h1><?php esc_html_e( 'SHOP', 'powerup-theme' ); ?></h1>
      <p><?php esc_html_e( 'Browse Our Collection', 'powerup-theme' ); ?></p>
      <div class="shop-ref-hero-actions">
        <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $shop_base_url ); ?>"><?php esc_html_e( 'SHOP NOW', 'powerup-theme' ); ?></a>
        <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $about_page_url ); ?>"><?php esc_html_e( 'LEARN MORE', 'powerup-theme' ); ?></a>
      </div>
    </div>
  </section>

  <section class="shop-ref-feature-strip" aria-label="Key benefits">
    <div class="shop-ref-feature-item">
      <span class="shop-ref-feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><rect x="2" y="7" width="18" height="10" rx="1.5"></rect><path d="M22 10v4"></path><path d="M6 10h4"></path></svg>
      </span>
      <p><?php esc_html_e( 'Powerful Performance', 'powerup-theme' ); ?></p>
    </div>
    <div class="shop-ref-feature-item">
      <span class="shop-ref-feature-icon" aria-hidden="true">⚙</span>
      <p><?php esc_html_e( 'Longer Battery Life', 'powerup-theme' ); ?></p>
    </div>
    <div class="shop-ref-feature-item">
      <span class="shop-ref-feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M12 12l4-2"></path><path d="M12 4v2"></path></svg>
      </span>
      <p><?php esc_html_e( 'High Torque Motor', 'powerup-theme' ); ?></p>
    </div>
    <div class="shop-ref-feature-item">
      <span class="shop-ref-feature-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"></path><path d="M9.5 12l1.8 1.8L14.8 10"></path></svg>
      </span>
      <p><?php esc_html_e( 'Built To Last', 'powerup-theme' ); ?></p>
    </div>
  </section>

  <section class="shop-ref-products-wrap">
    <div class="shop-ref-inner">
      <h2 class="shop-ref-title"><?php esc_html_e( 'ALL PRODUCTS', 'powerup-theme' ); ?></h2>

      <div class="shop-ref-grid-layout">
        <aside class="shop-ref-sidebar" id="shop-categories">
          <form class="shop-ref-filter-form" method="get" action="<?php echo esc_url( $shop_base_url ); ?>">
            <div class="shop-ref-filter-head">
              <h3 id="shop-categories-heading"><?php esc_html_e( 'CATEGORIES', 'powerup-theme' ); ?></h3>
              <button
                type="button"
                class="shop-ref-categories-toggle"
                aria-expanded="true"
                aria-controls="shop-categories-panel"
              ><?php esc_html_e( 'Collapse', 'powerup-theme' ); ?></button>
            </div>

            <div class="shop-ref-categories-panel" id="shop-categories-panel">
              <input class="shop-ref-search" type="search" name="q" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search', 'powerup-theme' ); ?>">

              <ul class="shop-ref-check-list shop-ref-check-list--grouped">
                <?php powerup_theme_render_shop_category_filter_items( $terms_by_parent, 0, $selected_categories, 0 ); ?>
              </ul>
            </div>

            <h4><?php esc_html_e( 'PRICE RANGE', 'powerup-theme' ); ?></h4>
            <ul class="shop-ref-check-list">
              <?php foreach ( $price_ranges as $price_index => $price_item ) : ?>
                <li>
                  <label>
                    <input type="checkbox" name="price[]" value="<?php echo esc_attr( (string) $price_index ); ?>" <?php checked( in_array( $price_index, $selected_prices, true ) ); ?>>
                    <span><?php echo esc_html( (string) $price_item['label'] ); ?></span>
                  </label>
                </li>
              <?php endforeach; ?>
            </ul>

            <button class="shop-ref-filter-submit" type="submit"><?php esc_html_e( 'Apply', 'powerup-theme' ); ?></button>
            <?php if ( $has_active_filters ) : ?>
              <a class="shop-ref-clear-filters" href="<?php echo esc_url( $shop_base_url ); ?>"><?php esc_html_e( 'Clear Filters', 'powerup-theme' ); ?></a>
            <?php endif; ?>
          </form>
        </aside>

        <div class="shop-ref-products-grid">
          <?php if ( ! $products_query->have_posts() ) : ?>
            <div class="shop-ref-empty-state"><p><?php esc_html_e( 'No products found for this filter.', 'powerup-theme' ); ?></p></div>
          <?php else : ?>
            <?php while ( $products_query->have_posts() ) : ?>
              <?php
              $products_query->the_post();
              $product_id = (int) get_the_ID();
              $product    = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
              $image_url  = get_the_post_thumbnail_url( $product_id, 'medium' );
              if ( ! $image_url ) {
                $image_url = get_template_directory_uri() . '/assets/images/product-placeholder.svg';
              }

              $price = $product instanceof WC_Product ? wp_strip_all_tags( (string) $product->get_price_html() ) : '';
              if ( '' === trim( $price ) ) {
                $price = __( 'Request Quote', 'powerup-theme' );
              }

              $excerpt   = wp_trim_words( get_the_excerpt(), 12, '...' );
              $star_seed = $product_id > 0 ? $product_id : strlen( get_the_title() );
              $stars     = 4 + ( $star_seed % 2 );
              ?>
              <article class="shop-ref-product-card">
                <a class="shop-ref-product-image" href="<?php the_permalink(); ?>">
                  <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
                </a>
                <div class="shop-ref-product-copy">
                  <h3><?php echo esc_html( get_the_title() ); ?></h3>
                  <div class="shop-ref-stars"><?php echo esc_html( str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ) ); ?> <span><?php echo esc_html( $price ); ?></span></div>
                  <p><?php echo esc_html( $excerpt ); ?></p>
                  <div class="shop-ref-actions">
                    <a class="shop-ref-read-btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Add Cart', 'powerup-theme' ); ?></a>
                    <a class="shop-ref-secondary-btn" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'powerup-theme' ); ?></a>
                  </div>
                </div>
              </article>
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
          <?php endif; ?>
        </div>
      </div>

      <?php
      $pagination_base_args = array();
      if ( '' !== $search_query ) {
        $pagination_base_args['q'] = $search_query;
      }
      foreach ( $selected_categories as $cat_slug ) {
        $pagination_base_args['pcat[]'][] = $cat_slug;
      }
      foreach ( $selected_prices as $price_idx ) {
        $pagination_base_args['price[]'][] = $price_idx;
      }

      $pagination_links = paginate_links(
        array(
          'base'      => esc_url_raw( add_query_arg( 'paged', '%#%', $shop_base_url ) ),
          'format'    => '',
          'current'   => $current_page,
          'total'     => max( 1, (int) $products_query->max_num_pages ),
          'type'      => 'array',
          'add_args'  => $pagination_base_args,
          'prev_text' => '&laquo;',
          'next_text' => '&raquo;',
        )
      );
      ?>

      <?php if ( ! empty( $pagination_links ) ) : ?>
        <nav class="shop-ref-pagination" aria-label="<?php esc_attr_e( 'Shop Pagination', 'powerup-theme' ); ?>">
          <ul>
            <?php foreach ( $pagination_links as $pagination_link ) : ?>
              <li><?php echo wp_kses_post( $pagination_link ); ?></li>
            <?php endforeach; ?>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </section>

  <section class="shop-ref-featured-reviews">
    <div class="shop-ref-inner">
      <h2><?php esc_html_e( 'FEATURED REVIEWS', 'powerup-theme' ); ?></h2>
      <div class="shop-ref-reviews-grid">
        <?php foreach ( $review_images as $review_image ) : ?>
          <article><img src="<?php echo esc_url( $review_image ); ?>" alt="<?php esc_attr_e( 'Featured review', 'powerup-theme' ); ?>" loading="lazy"></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="shop-ref-newsletter">
    <div class="shop-ref-newsletter-inner">
      <h2><?php esc_html_e( 'SUBSCRIBE TO OUR NEWSLETTER', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Get latest updates and special deals.', 'powerup-theme' ); ?></p>
      <form class="shop-ref-newsletter-form" action="#" method="post">
        <input type="email" name="newsletter_email" placeholder="<?php esc_attr_e( 'Enter your email address', 'powerup-theme' ); ?>" required>
        <button type="submit"><?php esc_html_e( 'SUBSCRIBE', 'powerup-theme' ); ?></button>
      </form>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var sidebar = document.getElementById('shop-categories');
  if (!sidebar) {
    return;
  }

  var categoriesPanel = sidebar.querySelector('#shop-categories-panel');
  var categoriesToggle = sidebar.querySelector('.shop-ref-categories-toggle');

  var toggleCategoryItem = function (item) {
    if (!item) {
      return;
    }

    var toggle = item.querySelector('.shop-ref-cat-toggle');
    if (!toggle) {
      return;
    }

    var nestedList = null;
    for (var i = 0; i < item.children.length; i += 1) {
      var child = item.children[i];
      if (child.classList && child.classList.contains('shop-ref-check-list--nested')) {
        nestedList = child;
        break;
      }
    }

    if (!nestedList) {
      return;
    }

    var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
    var nextExpanded = !isExpanded;
    toggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
    nestedList.hidden = !nextExpanded;
    item.classList.toggle('is-open', nextExpanded);
  };

  if (categoriesPanel && categoriesToggle) {
    categoriesToggle.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();

      var expanded = categoriesToggle.getAttribute('aria-expanded') === 'true';
      var nextExpanded = !expanded;
      categoriesToggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
      categoriesToggle.textContent = nextExpanded ? 'Collapse' : 'Expand';
      categoriesPanel.hidden = !nextExpanded;
    });
  }

  sidebar.addEventListener('click', function (event) {
    // 处理分类名称点击
    var labelText = event.target.closest('.shop-ref-cat-label-text');
    if (labelText) {
      event.preventDefault();
      event.stopPropagation();

      var label = labelText.closest('label');
      var row = label ? label.closest('.shop-ref-check-row') : null;
      var item = row ? row.closest('.shop-ref-check-item') : null;

      if (item && item.classList.contains('has-children')) {
        // 有子分类：切换折叠状态
        toggleCategoryItem(item);
      } else if (label) {
        // 没有子分类：手动切换复选框状态
        var checkbox = label.querySelector('input[type="checkbox"]');
        if (checkbox) {
          checkbox.checked = !checkbox.checked;
          // 手动触发change事件，但延迟提交以避免立即跳转
          var changeEvent = new Event('change', { bubbles: true });
          checkbox.dispatchEvent(changeEvent);
        }
      }
      return;
    }

    // 处理折叠按钮点击
    var toggle = event.target.closest('.shop-ref-cat-toggle');
    if (!toggle) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    var item = toggle.closest('.shop-ref-check-item');
    if (!item) {
      return;
    }

    toggleCategoryItem(item);
  });
});
</script>

<?php get_footer();
