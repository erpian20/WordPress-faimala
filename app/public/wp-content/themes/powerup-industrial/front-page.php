<?php
/**
 * The template for displaying the homepage
 *
 * @package PowerUp_Theme
 */
get_header();

$chainsaw_url = function_exists( 'powerup_get_product_url' ) ? powerup_get_product_url( 'cordless-chainsaw-pro' ) : ( function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' ) );
$trimmer_url  = function_exists( 'powerup_get_product_url' ) ? powerup_get_product_url( 'hedge-trimmer-elite' ) : ( function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' ) );
$wrench_url   = function_exists( 'powerup_get_product_url' ) ? powerup_get_product_url( 'impact-wrench-max' ) : ( function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' ) );
$shop_url     = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' );
$series_url   = function_exists( 'powerup_theme_get_reference_series_page_url' ) ? powerup_theme_get_reference_series_page_url() : home_url( '/chainsaw-series/' );
$placeholder_image_url = get_template_directory_uri() . '/assets/images/product-placeholder.svg';

$home_series_items = function_exists( 'powerup_theme_get_reference_series_nav_items' ) ? powerup_theme_get_reference_series_nav_items() : array();
$home_series_slugs = function_exists( 'powerup_theme_get_reference_series_product_slugs' ) ? powerup_theme_get_reference_series_product_slugs() : array();
$ordered_home_series_items = array();
$home_featured_products = array();

foreach ( $home_series_slugs as $home_series_slug ) {
  if ( ! empty( $home_series_items[ $home_series_slug ] ) ) {
    $ordered_home_series_items[] = $home_series_items[ $home_series_slug ];
  }
}

if ( class_exists( 'WooCommerce' ) ) {
  $home_launch_product_ids = get_posts(
    array(
      'post_type'              => 'product',
      'post_status'            => 'publish',
      'posts_per_page'         => 3,
      'fields'                 => 'ids',
      'meta_key'               => '_powerup_launch_order',
      'orderby'                => 'meta_value_num',
      'order'                  => 'ASC',
      'no_found_rows'          => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    )
  );

  foreach ( $home_launch_product_ids as $home_launch_product_id ) {
    $home_product = wc_get_product( $home_launch_product_id );
    if ( ! $home_product instanceof WC_Product ) {
      continue;
    }

    $home_featured_products[] = array(
      'id'    => (int) $home_launch_product_id,
      'title' => get_the_title( $home_launch_product_id ),
      'url'   => get_permalink( $home_launch_product_id ),
      'image' => get_the_post_thumbnail_url( $home_launch_product_id, 'woocommerce_thumbnail' ),
      'price' => wp_strip_all_tags( (string) $home_product->get_price_html() ),
      'note'  => has_excerpt( $home_launch_product_id ) ? wp_trim_words( get_the_excerpt( $home_launch_product_id ), 10, '...' ) : __( 'Launch-ready cordless chainsaw product.', 'powerup-theme' ),
    );
  }

  foreach ( $home_series_slugs as $home_series_slug ) {
    if ( count( $home_featured_products ) >= 3 ) {
      break;
    }

    $home_product_post = get_page_by_path( $home_series_slug, OBJECT, 'product' );
    if ( ! $home_product_post instanceof WP_Post ) {
      continue;
    }

    $home_product = wc_get_product( $home_product_post->ID );
    if ( ! $home_product instanceof WC_Product ) {
      continue;
    }

    $home_featured_products[] = array(
      'id'    => (int) $home_product_post->ID,
      'title' => get_the_title( $home_product_post ),
      'url'   => get_permalink( $home_product_post ),
      'image' => get_the_post_thumbnail_url( $home_product_post->ID, 'woocommerce_thumbnail' ),
      'price' => wp_strip_all_tags( (string) $home_product->get_price_html() ),
      'note'  => has_excerpt( $home_product_post ) ? wp_trim_words( $home_product_post->post_excerpt, 10, '...' ) : __( 'Ready for fast outdoor cutting jobs.', 'powerup-theme' ),
    );
  }

  if ( count( $home_featured_products ) < 3 ) {
    $home_fallback_query = new WP_Query(
      array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => wp_list_pluck( $home_featured_products, 'id' ),
        'orderby'        => 'date',
        'order'          => 'DESC',
      )
    );

    while ( $home_fallback_query->have_posts() && count( $home_featured_products ) < 3 ) {
      $home_fallback_query->the_post();
      $home_product = wc_get_product( get_the_ID() );
      if ( ! $home_product instanceof WC_Product ) {
        continue;
      }

      $home_featured_products[] = array(
        'id'    => (int) get_the_ID(),
        'title' => get_the_title(),
        'url'   => get_permalink(),
        'image' => get_the_post_thumbnail_url( get_the_ID(), 'woocommerce_thumbnail' ),
        'price' => wp_strip_all_tags( (string) $home_product->get_price_html() ),
        'note'  => has_excerpt() ? wp_trim_words( get_the_excerpt(), 10, '...' ) : __( 'Popular cordless tool for home and outdoor work.', 'powerup-theme' ),
      );
    }
    wp_reset_postdata();
  }
}

if ( empty( $home_featured_products ) ) {
  $home_featured_products = array(
    array(
      'id'    => 0,
      'title' => __( '12-Inch 20V Cordless Chainsaw Kit', 'powerup-theme' ),
      'url'   => $series_url,
      'image' => $placeholder_image_url,
      'price' => __( 'View latest price', 'powerup-theme' ),
      'note'  => __( 'Battery-powered cutting kit for home, yard, and light-duty jobs.', 'powerup-theme' ),
    ),
    array(
      'id'    => 0,
      'title' => __( 'DeWalt-Compatible Chainsaw Tool', 'powerup-theme' ),
      'url'   => $series_url,
      'image' => $placeholder_image_url,
      'price' => __( 'View latest price', 'powerup-theme' ),
      'note'  => __( 'Tool-only option for buyers already using compatible batteries.', 'powerup-theme' ),
    ),
    array(
      'id'    => 0,
      'title' => __( 'Milwaukee M18-Compatible Chainsaw Tool', 'powerup-theme' ),
      'url'   => $series_url,
      'image' => $placeholder_image_url,
      'price' => __( 'View latest price', 'powerup-theme' ),
      'note'  => __( 'Cordless saw option built around common battery platforms.', 'powerup-theme' ),
    ),
  );
}
?>
<main class="hero">
  <div class="hero-inner">
    <div class="section-heading">
      <span><?php esc_html_e( 'Cordless chainsaws and outdoor power tools', 'powerup-theme' ); ?></span>
      <h1 class="hero-title"><?php esc_html_e( 'Cut Faster With Battery Freedom', 'powerup-theme' ); ?></h1>
      <p class="hero-subtitle"><?php esc_html_e( 'Shop practical cordless chainsaw options, including ready-to-run 20V kits and tool-only models for popular battery platforms.', 'powerup-theme' ); ?></p>
    </div>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url( $series_url ); ?>"><?php esc_html_e( 'Compare Chainsaw Models', 'powerup-theme' ); ?></a>
      <a class="btn btn-secondary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Browse All Tools', 'powerup-theme' ); ?></a>
    </div>
  </div>
</main>

<div class="hero-feature-bar">
  <div class="hero-feature-bar__inner">
    <div class="hero-feature-bar__item">
      <div class="hero-feature-bar__icon" aria-hidden="true">
        <span class="hero-feature-bar__icon-image hero-feature-bar__icon-image--high-performance" aria-hidden="true"></span>
      </div>
      <div class="hero-feature-bar__text">
        <strong><?php esc_html_e( 'High Performance', 'powerup-theme' ); ?></strong>
        <span><?php esc_html_e( 'Powerful &amp; Reliable', 'powerup-theme' ); ?></span>
      </div>
    </div>

    <div class="hero-feature-bar__item">
      <div class="hero-feature-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="5" y="14" width="36" height="20" rx="4" stroke="currentColor" stroke-width="3"/>
          <rect x="41" y="20" width="3.5" height="8" rx="1.4" fill="currentColor"/>
          <rect x="10" y="18" width="6" height="12" rx="1.5" fill="currentColor"/>
          <rect x="18" y="18" width="6" height="12" rx="1.5" fill="currentColor"/>
          <rect x="26" y="18" width="6" height="12" rx="1.5" fill="currentColor"/>
          <rect x="34" y="18" width="3" height="12" rx="1.5" stroke="currentColor" stroke-width="2"/>
        </svg>
      </div>
      <div class="hero-feature-bar__text">
        <strong><?php esc_html_e( 'Cordless Convenience', 'powerup-theme' ); ?></strong>
        <span><?php esc_html_e( 'No Cords, No Limits', 'powerup-theme' ); ?></span>
      </div>
    </div>

    <div class="hero-feature-bar__item">
      <div class="hero-feature-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M24 6 L40 12 V24 C40 33 33 40 24 43 C15 40 8 33 8 24 V12 Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
          <path d="M17 24 L22 29 L31 19" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="hero-feature-bar__text">
        <strong><?php esc_html_e( 'Durable Design', 'powerup-theme' ); ?></strong>
        <span><?php esc_html_e( 'Built to Last', 'powerup-theme' ); ?></span>
      </div>
    </div>
  </div>
</div>

<section class="site-section">
  <div class="site-inner">
    <div class="section-heading">
      <h2><?php esc_html_e( 'Why choose PowerUp?', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Discover reliable cordless tools with premium performance, fast shipping, and real user confidence.', 'powerup-theme' ); ?></p>
    </div>
    <div class="feature-cards">
      <article class="feature-card">
        <h3><?php esc_html_e( 'Clear Battery Choices', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Help buyers choose between a complete 20V kit and tool-only models for existing battery platforms.', 'powerup-theme' ); ?></p>
      </article>
      <article class="feature-card">
        <h3><?php esc_html_e( 'Ready For Outdoor Work', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Compact cordless tools designed for yard cleanup, branch cutting, trimming, and everyday maintenance.', 'powerup-theme' ); ?></p>
      </article>
      <article class="feature-card">
        <h3><?php esc_html_e( 'Support After Purchase', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Simple product guidance, warranty support, and responsive after-sales help when buyers need it.', 'powerup-theme' ); ?></p>
      </article>
    </div>
  </div>
</section>

<section class="site-section site-section-light">
  <div class="site-inner">
    <div class="home-featured-layout">
      <div class="home-featured-intro">
        <span class="powerup-series-badge"><?php esc_html_e( 'Core Chainsaws', 'powerup-theme' ); ?></span>
        <h2><?php esc_html_e( 'Featured Tools', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Choose a complete 20V kit or match a tool-only model to the battery platform you already use.', 'powerup-theme' ); ?></p>
        <a class="btn btn-secondary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View All Tools', 'powerup-theme' ); ?></a>
      </div>
      <div class="featured-products section-grid section-grid-products-home">
        <?php foreach ( $home_featured_products as $home_product_index => $home_featured_product ) : ?>
          <?php $home_product_image = ! empty( $home_featured_product['image'] ) ? (string) $home_featured_product['image'] : $placeholder_image_url; ?>
          <article class="product-card">
            <a class="product-image" href="<?php echo esc_url( $home_featured_product['url'] ); ?>">
              <img src="<?php echo esc_url( $home_product_image ); ?>" alt="<?php echo esc_attr( $home_featured_product['title'] ); ?>" width="800" height="520" loading="<?php echo 0 === $home_product_index ? 'eager' : 'lazy'; ?>" <?php echo 0 === $home_product_index ? 'fetchpriority="high"' : ''; ?> decoding="async">
            </a>
            <h3><?php echo esc_html( $home_featured_product['title'] ); ?></h3>
            <?php if ( ! empty( $home_featured_product['note'] ) ) : ?>
              <p><?php echo esc_html( $home_featured_product['note'] ); ?></p>
            <?php endif; ?>
            <span class="price"><?php echo esc_html( $home_featured_product['price'] ); ?></span>
            <a class="btn btn-primary" href="<?php echo esc_url( $home_featured_product['url'] ); ?>"><?php esc_html_e( 'View Details', 'powerup-theme' ); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php if ( ! empty( $ordered_home_series_items ) ) : ?>
<section class="site-section home-series-showcase">
  <div class="site-inner home-series-showcase__inner">
    <div class="home-series-showcase__intro">
      <span class="powerup-series-badge"><?php esc_html_e( 'Chainsaw Series', 'powerup-theme' ); ?></span>
      <h2><?php esc_html_e( 'Choose The Battery Path That Fits The Buyer', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Drive homepage traffic into one focused product family: a ready-to-run 20V kit, a Dewalt-compatible tool-only model, and a Milwaukee M18-compatible tool-only model.', 'powerup-theme' ); ?></p>
      <div class="home-series-showcase__actions">
        <a class="btn btn-primary" href="<?php echo esc_url( $series_url ); ?>"><?php esc_html_e( 'Open Series Page', 'powerup-theme' ); ?></a>
        <a class="btn btn-secondary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Browse Shop', 'powerup-theme' ); ?></a>
      </div>
    </div>
    <div class="home-series-showcase__grid">
      <?php foreach ( $ordered_home_series_items as $home_series_item ) : ?>
        <article class="home-series-card">
          <a class="home-series-card__media" href="<?php echo esc_url( $home_series_item['url'] ); ?>">
            <img src="<?php echo esc_url( ! empty( $home_series_item['image'] ) ? $home_series_item['image'] : $placeholder_image_url ); ?>" alt="<?php echo esc_attr( $home_series_item['title'] ); ?>" loading="lazy" decoding="async">
          </a>
          <div class="home-series-card__body">
            <?php echo wp_kses_post( powerup_theme_get_reference_series_badge_html( 'compact' ) ); ?>
            <h3><a href="<?php echo esc_url( $home_series_item['url'] ); ?>"><?php echo esc_html( $home_series_item['title'] ); ?></a></h3>
            <p><?php echo esc_html( $home_series_item['excerpt'] ); ?></p>
            <div class="home-series-card__meta">
              <strong><?php echo esc_html( $home_series_item['price'] ); ?></strong>
              <a href="<?php echo esc_url( $home_series_item['url'] ); ?>"><?php esc_html_e( 'View Model', 'powerup-theme' ); ?></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="site-section">
  <div class="site-inner">
    <div class="section-heading">
      <h2><?php esc_html_e( 'Buyer Confidence', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Make the purchase decision easier with clear support, shipping, and product-fit information.', 'powerup-theme' ); ?></p>
    </div>
    <div class="testimonial-grid testimonial-grid-home">
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'Model Guidance', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Clear product pages help buyers confirm battery fit, included accessories, and intended use before checkout.', 'powerup-theme' ); ?></p>
      </article>
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'After-Sales Support', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Support contact options stay visible so customers know where to go for setup, warranty, or order questions.', 'powerup-theme' ); ?></p>
      </article>
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'Practical Tool Selection', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'The shop highlights cordless tools for common outdoor jobs instead of making buyers search from scratch.', 'powerup-theme' ); ?></p>
      </article>
    </div>
  </div>
</section>

<section class="home-why-banner home-why-banner--size-large" aria-label="Why Choose Us">
  <div class="home-why-banner__overlay">
    <div class="site-inner home-why-banner__inner">
      <h2 class="home-why-banner__title"><?php esc_html_e( 'WHY CHOOSE US?', 'powerup-theme' ); ?></h2>

      <div class="home-why-banner__grid">
        <article class="home-why-banner__item">
          <div class="home-why-banner__icon" aria-hidden="true">
            <img class="home-why-banner__icon-image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/why-battery-icon.svg?v=20260410d' ); ?>" alt="" loading="lazy" decoding="async">
          </div>
          <h3><?php esc_html_e( 'Long Battery Life', 'powerup-theme' ); ?></h3>
          <p><?php esc_html_e( 'Extended Power', 'powerup-theme' ); ?></p>
        </article>

        <article class="home-why-banner__item">
          <div class="home-why-banner__icon" aria-hidden="true">
            <svg class="home-why-banner__svg" viewBox="4 4 88 64" role="presentation" focusable="false">
              <rect x="16" y="25" width="38" height="22" rx="2" ry="2"></rect>
              <path d="M54 31 H66 L76 38 V47 H54 Z"></path>
              <circle cx="31" cy="54" r="6"></circle>
              <circle cx="65" cy="54" r="6"></circle>
              <line x1="8" y1="29" x2="14" y2="29"></line>
              <line x1="5" y1="36" x2="14" y2="36"></line>
              <line x1="9" y1="43" x2="14" y2="43"></line>
              <line x1="57" y1="38" x2="67" y2="38"></line>
            </svg>
          </div>
          <h3><?php esc_html_e( 'Fast Shipping', 'powerup-theme' ); ?></h3>
          <p><?php esc_html_e( 'Quick & Reliable', 'powerup-theme' ); ?></p>
        </article>

        <article class="home-why-banner__item">
          <div class="home-why-banner__icon" aria-hidden="true">
            <svg class="home-why-banner__svg" viewBox="0 0 96 72" role="presentation" focusable="false">
              <path d="M48 8 L72 18 V34 C72 47 62 58 48 63 C34 58 24 47 24 34 V18 Z"></path>
              <path d="M36 35 L45 44 L61 28"></path>
              <path d="M48 8 V63" opacity="0"></path>
            </svg>
          </div>
          <h3><?php esc_html_e( '1 Year Warranty', 'powerup-theme' ); ?></h3>
          <p><?php esc_html_e( 'Guaranteed Quality', 'powerup-theme' ); ?></p>
        </article>

        <article class="home-why-banner__item">
          <div class="home-why-banner__icon" aria-hidden="true">
            <svg class="home-why-banner__svg" viewBox="0 0 96 72" role="presentation" focusable="false">
              <path d="M22 39 V33 C22 20 33 11 48 11 C63 11 74 20 74 33 V39"></path>
              <path d="M30 37 V31 C30 24 37 18 48 18 C59 18 66 24 66 31 V37"></path>
              <rect x="16" y="34" width="12" height="22" rx="5" ry="5"></rect>
              <rect x="68" y="34" width="12" height="22" rx="5" ry="5"></rect>
              <path d="M33 56 V60 C33 63 35 64 38 64 H52"></path>
              <path d="M52 64 H57"></path>
              <circle cx="59" cy="64" r="2"></circle>
            </svg>
          </div>
          <h3><?php esc_html_e( '24/7 Support', 'powerup-theme' ); ?></h3>
          <p><?php esc_html_e( 'Always Here to Help', 'powerup-theme' ); ?></p>
        </article>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
