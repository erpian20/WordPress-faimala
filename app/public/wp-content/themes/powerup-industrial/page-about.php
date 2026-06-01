<?php
/**
 * Template Name: About Us Page
 *
 * @package PowerUp_Theme
 */
get_header();

$shop_url    = home_url( '/shop/' );
$contact_url = home_url( '/contact-us/' );

$mission_image = get_template_directory_uri() . '/assets/images/about-highlight-custom.jpg';
$team_image    = get_template_directory_uri() . '/assets/images/about-team-custom.jpg';

$about_product_cards = array(
  array(
    'slug'        => '12-inch-20v-cordless-electric-chainsaw-kit-b0ffgspwws',
    'label'       => '12-Inch Chainsaws',
    'description' => 'Ready-to-cut kits and tool-only options for trimming, cleanup, and light wood cutting.',
  ),
  array(
    'slug'        => '8-inch-brushless-chainsaw-kit-for-dewalt-20v-max-battery-b0ggtkhn4g',
    'label'       => 'Compact Chainsaw Kits',
    'description' => 'Lightweight 8-inch options for pruning and quick jobs around the yard.',
  ),
  array(
    'slug'        => '12-inch-chainsaw-guide-bar-1-4-inch-pitch-b0fw3rlqy9',
    'label'       => 'Chainsaw Guide Bars',
    'description' => 'Replacement bars sized for compatible PowerUp compact and 12-inch saws.',
  ),
  array(
    'slug'        => '12-inch-chainsaw-chain-replacement-2-pack-b0fqnycrh2',
    'label'       => 'Replacement Chains',
    'description' => 'Matching replacement chains to help keep regular maintenance simple.',
  ),
);

foreach ( $about_product_cards as $index => $about_product_card ) {
  $product_post  = get_page_by_path( $about_product_card['slug'], OBJECT, 'product' );
  $product_image = $product_post instanceof WP_Post ? get_the_post_thumbnail_url( $product_post->ID, 'full' ) : '';

  $about_product_cards[ $index ]['image'] = $product_image ? $product_image : get_template_directory_uri() . '/assets/images/product-placeholder.svg';
  $about_product_cards[ $index ]['url']   = $product_post instanceof WP_Post ? get_permalink( $product_post->ID ) : $shop_url;
}
?>

<section class="hero-section about-hero about-hero-main-bg">
  <div class="site-inner about-inner">
    <p class="about-eyebrow">ABOUT POWERUP</p>
    <h1 class="about-title">Cordless chainsaws made easier to choose</h1>
    <p class="about-subtitle">PowerUp helps homeowners find practical chainsaws, compatible battery options, and replacement parts for everyday yard work.</p>
    <div class="about-hero-actions">
      <a href="<?php echo esc_url( $shop_url ); ?>" class="about-hero-btn about-hero-btn-primary">SHOP CHAINSAWS</a>
      <a href="<?php echo esc_url( $contact_url ); ?>" class="about-hero-btn about-hero-btn-outline">CONTACT SUPPORT</a>
    </div>
  </div>
</section>

<section class="about-feature-band" aria-label="PowerUp service highlights">
  <div class="site-inner about-inner">
    <div class="about-feature-grid">
      <div class="about-feature-item">
        <strong>Clear battery choices</strong>
        <p>Complete kits and tool-only models</p>
      </div>
      <div class="about-feature-item">
        <strong>U.S. fulfillment</strong>
        <p>Free shipping with estimated delivery in 2-5 days</p>
      </div>
      <div class="about-feature-item">
        <strong>Practical maintenance</strong>
        <p>Replacement guide bars and chains</p>
      </div>
      <div class="about-feature-item">
        <strong>Responsive support</strong>
        <p>30-day returns and a 180-day warranty</p>
      </div>
    </div>
  </div>
</section>

<main class="site-section about-main">
  <div class="site-inner about-inner">
    <section class="about-section about-section--mission">
      <div class="about-mission-layout">
        <div class="about-mission-left">
          <img src="<?php echo esc_url( $mission_image ); ?>" alt="PowerUp team reviewing cordless tool options" width="960" height="720" loading="lazy" decoding="async" class="about-media">
        </div>

        <div class="about-mission-right">
          <p class="about-eyebrow about-eyebrow--dark">OUR FOCUS</p>
          <h2 class="about-section-title">Useful tools for the jobs that happen at home</h2>
          <p class="about-copy">PowerUp is focused on cordless chainsaws and the parts that keep them working. Our catalog is built around the jobs homeowners return to throughout the year: pruning branches, clearing storm debris, preparing firewood, and maintaining a property without the upkeep of a gas saw.</p>
          <p class="about-copy">We keep the buying process straightforward. Each product page explains what is included, which battery platform fits, and which replacement bar or chain to choose later.</p>
          <ul class="about-list about-history-list">
            <li>Complete chainsaw kits with batteries and a charger for a ready-to-cut setup.</li>
            <li>Tool-only options for compatible DeWalt and Milwaukee battery platforms.</li>
            <li>Replacement guide bars and chains for regular maintenance.</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="about-section about-section--service">
      <p class="about-eyebrow about-eyebrow--dark">WHAT YOU CAN EXPECT</p>
      <h2 class="about-section-title">A clearer path from product choice to after-sales support</h2>
      <div class="about-values-with-team">
        <div class="about-values-left">
          <div class="about-values-grid">
            <div class="about-value-card">
              <span class="about-value-number">01</span>
              <h3>Choose with confidence</h3>
              <p>Clear compatibility notes help you compare full kits, tool-only models, and replacement parts before ordering.</p>
            </div>
            <div class="about-value-card">
              <span class="about-value-number">02</span>
              <h3>Work without gas-tool upkeep</h3>
              <p>Cordless tools provide quick startup, easier storage, and a practical fit for routine property maintenance.</p>
            </div>
            <div class="about-value-card">
              <span class="about-value-number">03</span>
              <h3>Get help after delivery</h3>
              <p>Our support team is available 24/7 for fit, setup, returns, and warranty questions.</p>
            </div>
          </div>
        </div>
        <div class="about-values-right">
          <img src="<?php echo esc_url( $team_image ); ?>" alt="PowerUp team supporting cordless chainsaw customers" width="960" height="720" loading="lazy" decoding="async" class="about-media about-team-photo">
        </div>
      </div>
    </section>

    <section class="about-section about-section--featured-reviews">
      <p class="about-eyebrow">SHOP THE RANGE</p>
      <h2 class="about-section-title">Start with the chainsaw or replacement part that fits your work</h2>
      <div class="about-products-grid">
        <?php foreach ( $about_product_cards as $about_product_card ) : ?>
          <a href="<?php echo esc_url( $about_product_card['url'] ); ?>" class="about-product-item">
            <img src="<?php echo esc_url( $about_product_card['image'] ); ?>" alt="<?php echo esc_attr( $about_product_card['label'] ); ?>" width="800" height="800" loading="lazy" decoding="async" class="about-media">
            <span class="about-product-item__content">
              <strong><?php echo esc_html( $about_product_card['label'] ); ?></strong>
              <span><?php echo esc_html( $about_product_card['description'] ); ?></span>
              <em>View products</em>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>

<?php get_footer(); ?>
