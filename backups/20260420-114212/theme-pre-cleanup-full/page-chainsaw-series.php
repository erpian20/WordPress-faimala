<?php
/**
 * Template Name: Chainsaw Series Page
 *
 * @package PowerUp_Theme
 */

get_header();

$series_slugs   = function_exists( 'powerup_theme_get_reference_series_product_slugs' ) ? powerup_theme_get_reference_series_product_slugs() : array();
$series_items   = function_exists( 'powerup_theme_get_reference_series_nav_items' ) ? powerup_theme_get_reference_series_nav_items() : array();
$payload_map    = function_exists( 'powerup_theme_get_reference_series_payload_map' ) ? powerup_theme_get_reference_series_payload_map() : array();
$shop_url       = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' );
$contact_url    = function_exists( 'powerup_theme_get_contact_page_url' ) ? powerup_theme_get_contact_page_url() : home_url( '/contact-us/' );
$fallback_image = get_template_directory_uri() . '/assets/images/product-placeholder.svg';

$ordered_items = array();
foreach ( $series_slugs as $series_slug ) {
  if ( empty( $series_items[ $series_slug ] ) ) {
    continue;
  }

  $item    = $series_items[ $series_slug ];
  $payload = isset( $payload_map[ $series_slug ] ) ? $payload_map[ $series_slug ] : array();

  $item['about_points'] = ! empty( $payload['about_points'] ) && is_array( $payload['about_points'] )
    ? array_slice( array_map( 'strval', $payload['about_points'] ), 0, 3 )
    : array();
  $item['hero_image'] = ! empty( $item['image'] ) ? $item['image'] : $fallback_image;

  $ordered_items[] = $item;
}

$hero_item = ! empty( $ordered_items ) ? $ordered_items[0] : null;
$series_lead_status = isset( $_GET['series_lead'] ) ? sanitize_key( wp_unslash( (string) $_GET['series_lead'] ) ) : '';
?>

<main class="chainsaw-series-page">
  <section class="chainsaw-series-hero">
    <div class="site-inner chainsaw-series-inner chainsaw-series-hero__grid">
      <div class="chainsaw-series-hero__copy">
        <span class="powerup-series-badge"><?php esc_html_e( 'Chainsaw Series', 'powerup-theme' ); ?></span>
        <h1><?php esc_html_e( 'Three Cordless Chainsaws. One Clean Buying Path.', 'powerup-theme' ); ?></h1>
        <p><?php esc_html_e( 'This series page brings the 20V kit model, the Dewalt-compatible tool-only model, and the Milwaukee M18-compatible tool-only model into one comparison-ready landing page for ads, organic traffic, and internal navigation.', 'powerup-theme' ); ?></p>
        <div class="chainsaw-series-hero__actions">
          <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop All Products', 'powerup-theme' ); ?></a>
          <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Sales', 'powerup-theme' ); ?></a>
        </div>
        <ul class="chainsaw-series-hero__stats">
          <li><strong>3</strong><span><?php esc_html_e( 'Featured models', 'powerup-theme' ); ?></span></li>
          <li><strong>12"</strong><span><?php esc_html_e( 'Cutting bar size', 'powerup-theme' ); ?></span></li>
          <li><strong>13m/s</strong><span><?php esc_html_e( 'Top chain speed', 'powerup-theme' ); ?></span></li>
        </ul>
      </div>
      <div class="chainsaw-series-hero__media">
        <?php if ( $hero_item ) : ?>
          <img src="<?php echo esc_url( $hero_item['hero_image'] ); ?>" alt="<?php echo esc_attr( $hero_item['title'] ); ?>" loading="eager" decoding="async">
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-band">
    <div class="site-inner chainsaw-series-inner chainsaw-series-band__grid">
      <article>
        <strong><?php esc_html_e( 'Brushless Output', 'powerup-theme' ); ?></strong>
        <p><?php esc_html_e( 'Built for fast cuts, steady torque, and lower maintenance in daily use.', 'powerup-theme' ); ?></p>
      </article>
      <article>
        <strong><?php esc_html_e( 'Battery Platform Choice', 'powerup-theme' ); ?></strong>
        <p><?php esc_html_e( 'Offer a full kit or route buyers to the platform-compatible tool-only version they already use.', 'powerup-theme' ); ?></p>
      </article>
      <article>
        <strong><?php esc_html_e( 'Retail-Ready Positioning', 'powerup-theme' ); ?></strong>
        <p><?php esc_html_e( 'Use a single landing page as the destination for campaigns, bundles, and dealer conversations.', 'powerup-theme' ); ?></p>
      </article>
    </div>
  </section>

  <section class="chainsaw-series-models">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Series Models', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Each card pulls from the same synced product data already powering your PDP and Shop experience.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-models__grid">
        <?php foreach ( $ordered_items as $ordered_item ) : ?>
          <article class="chainsaw-series-model-card">
            <a class="chainsaw-series-model-card__media" href="<?php echo esc_url( $ordered_item['url'] ); ?>">
              <img src="<?php echo esc_url( $ordered_item['hero_image'] ); ?>" alt="<?php echo esc_attr( $ordered_item['title'] ); ?>" loading="lazy" decoding="async">
            </a>
            <div class="chainsaw-series-model-card__body">
              <?php echo wp_kses_post( powerup_theme_get_reference_series_badge_html( 'compact' ) ); ?>
              <h3><a href="<?php echo esc_url( $ordered_item['url'] ); ?>"><?php echo esc_html( $ordered_item['title'] ); ?></a></h3>
              <p><?php echo esc_html( $ordered_item['excerpt'] ); ?></p>
              <?php if ( ! empty( $ordered_item['about_points'] ) ) : ?>
                <ul>
                  <?php foreach ( $ordered_item['about_points'] as $about_point ) : ?>
                    <li><?php echo esc_html( $about_point ); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <div class="chainsaw-series-model-card__meta">
                <strong><?php echo esc_html( $ordered_item['price'] ); ?></strong>
                <a href="<?php echo esc_url( $ordered_item['url'] ); ?>"><?php esc_html_e( 'Open Product', 'powerup-theme' ); ?></a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-compare">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Platform Positioning', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Use this view when customers need a fast explanation of how the three models differ commercially.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-compare__table">
        <div class="chainsaw-series-compare__row chainsaw-series-compare__row--head">
          <span><?php esc_html_e( 'Model', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Battery Positioning', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Best Fit', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( '20V Kit Model', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Includes batteries and charger', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'First-time buyers who want a ready-to-run package.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Dewalt Tool-Only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Works with Dewalt 20V/60V platform', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Customers already invested in Dewalt batteries.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Milwaukee Tool-Only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Works with Milwaukee M18 platform', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Customers already invested in Milwaukee batteries.', 'powerup-theme' ); ?></span>
        </div>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-faq">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Series FAQ', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Short answers for the buying questions that usually block conversion on comparison-driven traffic.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-faq__grid">
        <details class="chainsaw-series-faq__item" open>
          <summary><?php esc_html_e( 'What is the practical difference between the three models?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'The 20V kit model is the easiest package for first-time buyers because it includes batteries and charger. The Dewalt and Milwaukee versions are tool-only models aimed at users who already own those battery platforms.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Are the tool-only versions intended for dealers and marketplace campaigns?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Yes. The tool-only versions are useful when you want to target buyers searching for a specific battery ecosystem and avoid friction caused by duplicate battery bundles.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Can this page be used as a wholesale or reseller landing page?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Yes. It is structured to help explain the lineup quickly, then route the lead into contact or direct product pages depending on where the buyer is in the funnel.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'How should I route buyers who want more than one battery option?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Send them to this series page first, then let them branch into the ready-to-run kit or the tool-only model that matches the battery system they already use.', 'powerup-theme' ); ?></p>
        </details>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-lead">
    <div class="site-inner chainsaw-series-inner chainsaw-series-lead__grid">
      <div class="chainsaw-series-lead__copy">
        <span class="powerup-series-badge"><?php esc_html_e( 'Lead Capture', 'powerup-theme' ); ?></span>
        <h2><?php esc_html_e( 'Request Pricing, Dealer Terms, Or A Matching Recommendation', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Use this form when the visitor is not ready to check out immediately and needs a recommendation, bundle advice, or wholesale follow-up.', 'powerup-theme' ); ?></p>
        <ul>
          <li><?php esc_html_e( 'Route ad traffic into a direct sales conversation.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Capture dealer and reseller interest without sending users through a generic contact path.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Help buyers choose between the kit model and battery-compatible tool-only models.', 'powerup-theme' ); ?></li>
        </ul>
      </div>
      <div class="chainsaw-series-lead__form-wrap">
        <?php if ( 'success' === $series_lead_status ) : ?>
          <div class="chainsaw-series-lead__notice is-success"><p><?php esc_html_e( 'Your request has been sent. We will follow up shortly.', 'powerup-theme' ); ?></p></div>
        <?php elseif ( 'missing' === $series_lead_status ) : ?>
          <div class="chainsaw-series-lead__notice is-error"><p><?php esc_html_e( 'Please provide a valid name and email address.', 'powerup-theme' ); ?></p></div>
        <?php elseif ( 'invalid' === $series_lead_status || 'failed' === $series_lead_status ) : ?>
          <div class="chainsaw-series-lead__notice is-error"><p><?php esc_html_e( 'The request could not be submitted. Please try again.', 'powerup-theme' ); ?></p></div>
        <?php endif; ?>
        <form class="chainsaw-series-lead__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
          <input type="hidden" name="action" value="powerup_series_lead_submit">
          <?php wp_nonce_field( 'powerup_series_lead_submit', 'powerup_series_lead_nonce' ); ?>
          <div class="chainsaw-series-lead__fields">
            <label>
              <span><?php esc_html_e( 'Full Name', 'powerup-theme' ); ?></span>
              <input type="text" name="series_lead_name" required>
            </label>
            <label>
              <span><?php esc_html_e( 'Email Address', 'powerup-theme' ); ?></span>
              <input type="email" name="series_lead_email" required>
            </label>
            <label>
              <span><?php esc_html_e( 'Phone Number', 'powerup-theme' ); ?></span>
              <input type="text" name="series_lead_phone">
            </label>
            <label>
              <span><?php esc_html_e( 'Company', 'powerup-theme' ); ?></span>
              <input type="text" name="series_lead_company">
            </label>
          </div>
          <label>
            <span><?php esc_html_e( 'Project Details', 'powerup-theme' ); ?></span>
            <textarea name="series_lead_message" rows="6" placeholder="<?php esc_attr_e( 'Tell us which model you are considering, your battery platform, quantity, or market goal.', 'powerup-theme' ); ?>"></textarea>
          </label>
          <button type="submit" class="shop-ref-btn shop-ref-btn-primary"><?php esc_html_e( 'Submit Request', 'powerup-theme' ); ?></button>
        </form>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-cta">
    <div class="site-inner chainsaw-series-inner chainsaw-series-cta__inner">
      <div>
        <h2><?php esc_html_e( 'Need A Dealer, Bundle, Or Wholesale Angle?', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Use the Contact page to route distributor, reseller, or private-label conversations without sending buyers through a generic catalog path.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-cta__actions">
        <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Talk To Us', 'powerup-theme' ); ?></a>
        <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Back To Shop', 'powerup-theme' ); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>