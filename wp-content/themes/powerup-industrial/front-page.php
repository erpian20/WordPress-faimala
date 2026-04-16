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

foreach ( $home_series_slugs as $home_series_slug ) {
  if ( ! empty( $home_series_items[ $home_series_slug ] ) ) {
    $ordered_home_series_items[] = $home_series_items[ $home_series_slug ];
  }
}
?>
<main class="hero">
  <div class="hero-inner">
    <div class="section-heading">
      <span><?php esc_html_e( 'Unrivaled Power & Portability for Every Cut', 'powerup-theme' ); ?></span>
      <h1 class="hero-title"><?php esc_html_e( 'POWER UP YOUR WORK', 'powerup-theme' ); ?></h1>
      <p class="hero-subtitle"><?php esc_html_e( 'Professional lithium tools for every task — built for durability, speed, and cordless freedom.', 'powerup-theme' ); ?></p>
    </div>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop Now', 'powerup-theme' ); ?></a>
      <a class="btn btn-secondary" href="<?php echo esc_url( function_exists( 'powerup_theme_get_about_page_url' ) ? powerup_theme_get_about_page_url() : home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'Learn More', 'powerup-theme' ); ?></a>
    </div>
  </div>
</main>

<div class="hero-feature-bar">
  <div class="hero-feature-bar__inner">
    <div class="hero-feature-bar__item">
      <div class="hero-feature-bar__icon" aria-hidden="true">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Standard 8-tooth gear: r_outer=18, r_inner=12, tooth±10°, valley arc between -->
          <path d="M21.9 12.2 L20.9 6.3 A18 18 0 0 1 27.1 6.3 L26.1 12.2
                   A12 12 0 0 1 30.9 14.2 L34.3 9.3 A18 18 0 0 1 38.7 13.7 L33.8 17.1
                   A12 12 0 0 1 35.8 21.9 L41.7 20.9 A18 18 0 0 1 41.7 27.1 L35.8 26.1
                   A12 12 0 0 1 33.8 30.9 L38.7 34.3 A18 18 0 0 1 34.3 38.7 L30.9 33.8
                   A12 12 0 0 1 26.1 35.8 L27.1 41.7 A18 18 0 0 1 20.9 41.7 L21.9 35.8
                   A12 12 0 0 1 17.1 33.8 L13.7 38.7 A18 18 0 0 1 9.3 34.3 L14.2 30.9
                   A12 12 0 0 1 12.2 26.1 L6.3 27.1 A18 18 0 0 1 6.3 20.9 L12.2 21.9
                   A12 12 0 0 1 14.2 17.1 L9.3 13.7 A18 18 0 0 1 13.7 9.3 L17.1 14.2
                   A12 12 0 0 1 21.9 12.2 Z"
                stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
          <circle cx="24" cy="24" r="6.5" stroke="currentColor" stroke-width="2"/>
          <path d="M20.5 24 L23 27 L28.5 20"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
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
        <h3><?php esc_html_e( 'High Performance', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Powerful, reliable motors engineered for heavy-duty cutting and trimming.', 'powerup-theme' ); ?></p>
      </article>
      <article class="feature-card">
        <h3><?php esc_html_e( 'Cordless Convenience', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'No cords, no limits — enjoy maximum mobility with long-lasting battery life.', 'powerup-theme' ); ?></p>
      </article>
      <article class="feature-card">
        <h3><?php esc_html_e( 'Durable Design', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Rugged construction ready for professional use and tough outdoor conditions.', 'powerup-theme' ); ?></p>
      </article>
    </div>
  </div>
</section>

<section class="site-section site-section-light">
  <div class="site-inner">
    <div class="section-heading">
      <h2><?php esc_html_e( 'Featured Products', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Browse our top-selling cordless chainsaws, trimmers, impact wrenches, and leaf blowers.', 'powerup-theme' ); ?></p>
    </div>
    <div class="featured-products section-grid section-grid-products-home">
      <article class="product-card">
        <div class="product-image"><img src="<?php echo esc_url( $placeholder_image_url ); ?>" alt="Cordless Chainsaw" width="800" height="520" loading="eager" fetchpriority="high" decoding="async"></div>
        <h3><?php esc_html_e( 'Cordless Chainsaw', 'powerup-theme' ); ?></h3>
        <span class="price">$119.99</span>
        <a class="btn btn-primary" href="<?php echo esc_url( $chainsaw_url ); ?>"><?php esc_html_e( 'View Product', 'powerup-theme' ); ?></a>
      </article>
      <article class="product-card">
        <div class="product-image"><img src="<?php echo esc_url( $placeholder_image_url ); ?>" alt="Hedge Trimmer" width="800" height="520" loading="lazy" decoding="async"></div>
        <h3><?php esc_html_e( 'Hedge Trimmer', 'powerup-theme' ); ?></h3>
        <span class="price">$89.99</span>
        <a class="btn btn-primary" href="<?php echo esc_url( $trimmer_url ); ?>"><?php esc_html_e( 'View Product', 'powerup-theme' ); ?></a>
      </article>
      <article class="product-card">
        <div class="product-image"><img src="<?php echo esc_url( $placeholder_image_url ); ?>" alt="Impact Wrench" width="800" height="520" loading="lazy" decoding="async"></div>
        <h3><?php esc_html_e( 'Impact Wrench', 'powerup-theme' ); ?></h3>
        <span class="price">$69.99</span>
        <a class="btn btn-primary" href="<?php echo esc_url( $wrench_url ); ?>"><?php esc_html_e( 'View Product', 'powerup-theme' ); ?></a>
      </article>
      <article class="product-card">
        <div class="product-image"><img src="<?php echo esc_url( $placeholder_image_url ); ?>" alt="Leaf Blower" width="800" height="520" loading="lazy" decoding="async"></div>
        <h3><?php esc_html_e( 'Leaf Blower', 'powerup-theme' ); ?></h3>
        <span class="price">$59.99</span>
        <a class="btn btn-primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View Product', 'powerup-theme' ); ?></a>
      </article>
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
      <h2><?php esc_html_e( 'Customer Feedback', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Real reviews from buyers who trust our tools for every job.', 'powerup-theme' ); ?></p>
    </div>
    <div class="testimonial-grid testimonial-grid-home">
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'Powerful and Reliable', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'The chainsaw cuts through thick logs with ease and the battery lasts much longer than expected.', 'powerup-theme' ); ?></p>
      </article>
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'Excellent Cordless Freedom', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'No cords to worry about during yard work, and the tool feels solid in hand.', 'powerup-theme' ); ?></p>
      </article>
      <article class="testimonial-card">
        <h3><?php esc_html_e( 'Professional Quality', 'powerup-theme' ); ?></h3>
        <p><?php esc_html_e( 'Fast shipping and great support. The product quality is excellent for the price.', 'powerup-theme' ); ?></p>
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
