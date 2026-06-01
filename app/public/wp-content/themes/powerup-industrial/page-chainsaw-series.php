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

$get_selector_product_url = static function ( $slug ) use ( $shop_url ) {
  $product_post = get_page_by_path( $slug, OBJECT, 'product' );
  return $product_post instanceof WP_Post ? get_permalink( $product_post ) : $shop_url;
};

$selector_models = array(
  'complete-kit' => array(
    'title' => __( '12 Inch 20V Cordless Electric Chainsaw Kit', 'powerup-theme' ),
    'copy'  => __( 'A ready-to-run package with batteries and charger for regular yard cleanup, pruning, and light wood cutting.', 'powerup-theme' ),
    'url'   => $get_selector_product_url( '12-inch-20v-cordless-electric-chainsaw-kit-b0ffgspwws' ),
  ),
  'dewalt-12' => array(
    'title' => __( '12 Inch Brushless Chainsaw for DeWalt 20V/60V Battery', 'powerup-theme' ),
    'copy'  => __( 'A tool-only 12-inch option for shoppers who already own a compatible DeWalt-style battery pack.', 'powerup-theme' ),
    'url'   => $get_selector_product_url( '12-inch-brushless-chainsaw-for-dewalt-20v-60v-battery-b0fcly4dc1' ),
  ),
  'milwaukee-12' => array(
    'title' => __( '12 Inch Brushless Chainsaw for Milwaukee M18 Battery', 'powerup-theme' ),
    'copy'  => __( 'A tool-only 12-inch option for shoppers who already own a compatible Milwaukee M18-style battery pack.', 'powerup-theme' ),
    'url'   => $get_selector_product_url( '12-inch-brushless-chainsaw-for-milwaukee-m18-battery-b0fcm6hxvx' ),
  ),
  'dewalt-8' => array(
    'title' => __( '8 Inch Brushless Chainsaw Kit for DeWalt 20V MAX Battery', 'powerup-theme' ),
    'copy'  => __( 'A compact choice for lighter pruning and quick jobs when an 8-inch guide plate is the better fit.', 'powerup-theme' ),
    'url'   => $get_selector_product_url( '8-inch-brushless-chainsaw-kit-for-dewalt-20v-max-battery-b0ggtkhn4g' ),
  ),
  'milwaukee-8' => array(
    'title' => __( '8 Inch Brushless Chainsaw Kit for Milwaukee M18 Battery', 'powerup-theme' ),
    'copy'  => __( 'A compact Milwaukee-compatible choice for pruning and lighter garden maintenance.', 'powerup-theme' ),
    'url'   => $get_selector_product_url( '8-inch-brushless-chainsaw-kit-for-milwaukee-m18-battery-b0ggtdwrnn' ),
  ),
);
?>

<main class="chainsaw-series-page">
  <section class="chainsaw-series-hero">
    <div class="site-inner chainsaw-series-inner chainsaw-series-hero__grid">
      <div class="chainsaw-series-hero__copy">
        <span class="powerup-series-badge"><?php esc_html_e( 'Chainsaw Series', 'powerup-theme' ); ?></span>
        <h1><?php esc_html_e( 'Choose The Cordless Chainsaw That Fits Your Battery Setup', 'powerup-theme' ); ?></h1>
        <p><?php esc_html_e( 'Compare a complete 20V kit with tool-only chainsaws for compatible DeWalt and Milwaukee battery platforms, then choose the package that fits your yard work.', 'powerup-theme' ); ?></p>
        <div class="chainsaw-series-hero__actions">
          <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop All Products', 'powerup-theme' ); ?></a>
          <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Ask For Help', 'powerup-theme' ); ?></a>
        </div>
        <ul class="chainsaw-series-hero__stats">
          <li><strong>3</strong><span><?php esc_html_e( 'Featured models', 'powerup-theme' ); ?></span></li>
          <li><strong>12"</strong><span><?php esc_html_e( 'Cutting bar size', 'powerup-theme' ); ?></span></li>
          <li><strong>3</strong><span><?php esc_html_e( 'Battery paths', 'powerup-theme' ); ?></span></li>
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
        <p><?php esc_html_e( 'Start with a full kit or choose a tool-only model for a compatible battery platform you already use.', 'powerup-theme' ); ?></p>
      </article>
      <article>
        <strong><?php esc_html_e( 'Built For Yard Work', 'powerup-theme' ); ?></strong>
        <p><?php esc_html_e( 'Compare package contents, battery fit, and intended use before you buy.', 'powerup-theme' ); ?></p>
      </article>
    </div>
  </section>

  <section class="chainsaw-selector" aria-labelledby="chainsaw-selector-title">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2 id="chainsaw-selector-title"><?php esc_html_e( 'Find Your Chainsaw Match', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Answer three quick questions to narrow the package that best fits your battery setup and yard work.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-selector__layout">
        <form class="chainsaw-selector__form">
          <label>
            <span><?php esc_html_e( '1. Which battery path fits you?', 'powerup-theme' ); ?></span>
            <select name="battery">
              <option value="none"><?php esc_html_e( 'I need a complete kit', 'powerup-theme' ); ?></option>
              <option value="dewalt"><?php esc_html_e( 'I already use a compatible DeWalt battery', 'powerup-theme' ); ?></option>
              <option value="milwaukee"><?php esc_html_e( 'I already use a compatible Milwaukee M18 battery', 'powerup-theme' ); ?></option>
            </select>
          </label>
          <label>
            <span><?php esc_html_e( '2. What kind of work do you expect most often?', 'powerup-theme' ); ?></span>
            <select name="work">
              <option value="regular"><?php esc_html_e( 'Regular pruning, cleanup, and light wood cutting', 'powerup-theme' ); ?></option>
              <option value="compact"><?php esc_html_e( 'Lighter pruning and quick garden jobs', 'powerup-theme' ); ?></option>
            </select>
          </label>
          <label>
            <span><?php esc_html_e( '3. Do you want a battery included?', 'powerup-theme' ); ?></span>
            <select name="bundle">
              <option value="yes"><?php esc_html_e( 'Yes, include a battery package', 'powerup-theme' ); ?></option>
              <option value="no"><?php esc_html_e( 'No, show a tool-only path when available', 'powerup-theme' ); ?></option>
            </select>
          </label>
        </form>
        <div class="chainsaw-selector__result" aria-live="polite">
          <span><?php esc_html_e( 'Recommended starting point', 'powerup-theme' ); ?></span>
          <h3 data-selector-title><?php echo esc_html( $selector_models['complete-kit']['title'] ); ?></h3>
          <p data-selector-copy><?php echo esc_html( $selector_models['complete-kit']['copy'] ); ?></p>
          <a class="shop-ref-btn shop-ref-btn-primary" data-selector-link href="<?php echo esc_url( $selector_models['complete-kit']['url'] ); ?>"><?php esc_html_e( 'Open Recommended Product', 'powerup-theme' ); ?></a>
        </div>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-models">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Series Models', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Compare package contents, battery compatibility, and everyday use cases before opening a product page.', 'powerup-theme' ); ?></p>
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
        <h2><?php esc_html_e( 'Choose Your Battery Platform', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Start with the battery system you already use, or choose the complete kit if you want everything in one box.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-compare__table">
        <div class="chainsaw-series-compare__row chainsaw-series-compare__row--head">
          <span><?php esc_html_e( 'Model', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Bar Size', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Battery Path', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Package', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Best Fit', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( '20V Kit Model', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( '12 inch', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'PowerUp battery included', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Batteries and charger included', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'First-time buyers who want a ready-to-run package.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Dewalt Tool-Only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( '12 inch', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compatible DeWalt 20V MAX / 60V style pack', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Tool only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Customers already invested in Dewalt batteries.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Milwaukee Tool-Only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( '12 inch', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compatible Milwaukee M18 style pack', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Tool only', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Customers already invested in Milwaukee batteries.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Compact DeWalt-Compatible Kit', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( '6 and 8 inch', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compatible DeWalt 20V MAX style pack', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compact saw kit with battery', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Lighter pruning and quick garden jobs.', 'powerup-theme' ); ?></span>
        </div>
        <div class="chainsaw-series-compare__row">
          <span><?php esc_html_e( 'Compact Milwaukee-Compatible Kit', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( '6 and 8 inch', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compatible Milwaukee M18 style pack', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Compact saw kit with battery', 'powerup-theme' ); ?></span>
          <span><?php esc_html_e( 'Lighter pruning and garden maintenance.', 'powerup-theme' ); ?></span>
        </div>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-faq">
    <div class="site-inner chainsaw-series-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Series FAQ', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Short answers to common questions about kits, tool-only models, and battery compatibility.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-faq__grid">
        <details class="chainsaw-series-faq__item" open>
          <summary><?php esc_html_e( 'What is the practical difference between the three models?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'The 20V kit model is the easiest package for first-time buyers because it includes batteries and charger. The Dewalt and Milwaukee versions are tool-only models aimed at users who already own those battery platforms.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'When should I choose a tool-only chainsaw?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Choose a tool-only chainsaw when you already own a compatible battery and charger. This avoids buying a second battery bundle you may not need.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Which model is easiest for a first-time buyer?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'The complete 20V kit is the simplest starting point because it includes the chainsaw, batteries, and charger in one package.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'What should I check before ordering?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Check your battery platform, the included parts, and the guide bar size. Open the product page for the complete package list and intended use cases.', 'powerup-theme' ); ?></p>
        </details>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-lead">
    <div class="site-inner chainsaw-series-inner chainsaw-series-lead__grid">
      <div class="chainsaw-series-lead__copy">
        <span class="powerup-series-badge"><?php esc_html_e( 'Need Help Choosing?', 'powerup-theme' ); ?></span>
        <h2><?php esc_html_e( 'Ask For A Product Recommendation', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Tell us which battery platform you use and what kind of cutting work you need to handle. We will help you narrow down the right package.', 'powerup-theme' ); ?></p>
        <ul>
          <li><?php esc_html_e( 'Confirm whether you need a complete kit or a tool-only chainsaw.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Check compatibility before ordering a battery-platform model.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Ask about guide bars, replacement chains, and maintenance parts.', 'powerup-theme' ); ?></li>
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
            <textarea name="series_lead_message" rows="6" placeholder="<?php esc_attr_e( 'Tell us which model you are considering, your battery platform, and the type of cutting work you need to handle.', 'powerup-theme' ); ?>"></textarea>
          </label>
          <button type="submit" class="shop-ref-btn shop-ref-btn-primary"><?php esc_html_e( 'Submit Request', 'powerup-theme' ); ?></button>
        </form>
      </div>
    </div>
  </section>

  <section class="chainsaw-series-cta">
    <div class="site-inner chainsaw-series-inner chainsaw-series-cta__inner">
      <div>
        <h2><?php esc_html_e( 'Still Comparing Chainsaw Options?', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Send us a message if you need help matching a chainsaw, guide bar, or replacement chain to your yard work.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-cta__actions">
        <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Talk To Us', 'powerup-theme' ); ?></a>
        <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Back To Shop', 'powerup-theme' ); ?></a>
      </div>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var selector = document.querySelector('.chainsaw-selector');
  if (!selector) {
    return;
  }

  var models = <?php echo wp_json_encode( $selector_models, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>;
  var form = selector.querySelector('.chainsaw-selector__form');
  var title = selector.querySelector('[data-selector-title]');
  var copy = selector.querySelector('[data-selector-copy]');
  var link = selector.querySelector('[data-selector-link]');

  function getRecommendation() {
    var battery = form.elements.battery.value;
    var work = form.elements.work.value;
    var bundle = form.elements.bundle.value;
    var key = 'complete-kit';

    if ('dewalt' === battery) {
      key = 'compact' === work && 'yes' === bundle ? 'dewalt-8' : 'dewalt-12';
    } else if ('milwaukee' === battery) {
      key = 'compact' === work && 'yes' === bundle ? 'milwaukee-8' : 'milwaukee-12';
    }

    return models[key] || models['complete-kit'];
  }

  function updateRecommendation() {
    var recommendation = getRecommendation();
    title.textContent = recommendation.title;
    copy.textContent = recommendation.copy;
    link.href = recommendation.url;
  }

  form.addEventListener('change', updateRecommendation);
  updateRecommendation();
});
</script>

<?php get_footer(); ?>
