<?php
/**
 * Template Name: Battery Compatibility Page
 *
 * @package PowerUp_Theme
 */

get_header();

$shop_url   = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' );
$series_url = function_exists( 'powerup_theme_get_reference_series_page_url' ) ? powerup_theme_get_reference_series_page_url() : home_url( '/chainsaw-series/' );

$get_product_url = static function ( $slug ) use ( $shop_url ) {
  $product_post = get_page_by_path( $slug, OBJECT, 'product' );
  return $product_post instanceof WP_Post ? get_permalink( $product_post ) : $shop_url;
};

$battery_paths = array(
  array(
    'eyebrow' => 'READY-TO-RUN PATH',
    'title'   => 'Start with a complete PowerUp kit',
    'copy'    => 'Choose this path when you want the chainsaw, battery, and charger in one package. It is the clearest starting point for a first cordless chainsaw.',
    'checks'  => array( 'Battery and charger included', 'Simple first-purchase option', 'No existing battery platform required' ),
    'label'   => 'View complete 20V kit',
    'url'     => $get_product_url( '12-inch-20v-cordless-electric-chainsaw-kit-b0ffgspwws' ),
  ),
  array(
    'eyebrow' => 'DEWALT BATTERY PATH',
    'title'   => 'Use a compatible DeWalt battery',
    'copy'    => 'Choose a compatible tool-only model when you already own the correct DeWalt-style battery pack and charger. Compact kits are also available for lighter pruning work.',
    'checks'  => array( 'Selected 20V MAX and 60V style battery fit', 'Tool-only 12-inch option', 'Compact 8-inch kit option' ),
    'label'   => 'View DeWalt-compatible saw',
    'url'     => $get_product_url( '12-inch-brushless-chainsaw-for-dewalt-20v-60v-battery-b0fcly4dc1' ),
  ),
  array(
    'eyebrow' => 'MILWAUKEE BATTERY PATH',
    'title'   => 'Use a compatible Milwaukee M18 battery',
    'copy'    => 'Choose this path when you already use compatible Milwaukee M18-style packs and want to add a cordless chainsaw without buying another full battery bundle.',
    'checks'  => array( 'Selected M18 style battery fit', 'Tool-only 12-inch option', 'Compact 8-inch option for trimming' ),
    'label'   => 'View Milwaukee-compatible saw',
    'url'     => $get_product_url( '12-inch-brushless-chainsaw-for-milwaukee-m18-battery-b0fcm6hxvx' ),
  ),
);
?>

<main class="battery-guide-page">
  <section class="battery-guide-hero">
    <div class="site-inner battery-guide-inner battery-guide-hero__grid">
      <div>
        <span class="powerup-series-badge"><?php esc_html_e( 'Battery Compatibility Center', 'powerup-theme' ); ?></span>
        <h1><?php esc_html_e( 'Choose a cordless chainsaw around the battery setup you already have', 'powerup-theme' ); ?></h1>
        <p><?php esc_html_e( 'Start with a complete PowerUp kit or compare selected tool-only chainsaws designed for compatible DeWalt and Milwaukee battery styles. This guide helps you narrow the right path before opening a product page.', 'powerup-theme' ); ?></p>
        <div class="battery-guide-actions">
          <a class="shop-ref-btn shop-ref-btn-primary" href="<?php echo esc_url( $series_url ); ?>"><?php esc_html_e( 'Open Chainsaw Selector', 'powerup-theme' ); ?></a>
          <a class="shop-ref-btn shop-ref-btn-ghost" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Browse All Products', 'powerup-theme' ); ?></a>
        </div>
      </div>
      <aside class="battery-guide-hero__note">
        <strong><?php esc_html_e( 'Compatibility means battery fit', 'powerup-theme' ); ?></strong>
        <p><?php esc_html_e( 'PowerUp is not affiliated with, sponsored by, or endorsed by DeWalt or Milwaukee. Always confirm the individual product page before ordering.', 'powerup-theme' ); ?></p>
      </aside>
    </div>
  </section>

  <section class="battery-guide-section">
    <div class="site-inner battery-guide-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Three practical battery paths', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'The best choice usually starts with one question: do you already own a compatible battery platform?', 'powerup-theme' ); ?></p>
      </div>
      <div class="battery-guide-paths">
        <?php foreach ( $battery_paths as $battery_path ) : ?>
          <article class="battery-guide-path">
            <span><?php echo esc_html( $battery_path['eyebrow'] ); ?></span>
            <h3><?php echo esc_html( $battery_path['title'] ); ?></h3>
            <p><?php echo esc_html( $battery_path['copy'] ); ?></p>
            <ul>
              <?php foreach ( $battery_path['checks'] as $battery_check ) : ?>
                <li><?php echo esc_html( $battery_check ); ?></li>
              <?php endforeach; ?>
            </ul>
            <a href="<?php echo esc_url( $battery_path['url'] ); ?>"><?php echo esc_html( $battery_path['label'] ); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="battery-guide-section battery-guide-section--muted">
    <div class="site-inner battery-guide-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Battery compatibility at a glance', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Use this table as a starting point, then confirm the exact package contents and battery notes on the product page.', 'powerup-theme' ); ?></p>
      </div>
      <div class="battery-guide-table-wrap">
        <table class="battery-guide-table">
          <thead>
            <tr>
              <th><?php esc_html_e( 'Path', 'powerup-theme' ); ?></th>
              <th><?php esc_html_e( 'Battery approach', 'powerup-theme' ); ?></th>
              <th><?php esc_html_e( 'Best for', 'powerup-theme' ); ?></th>
              <th><?php esc_html_e( 'Check before ordering', 'powerup-theme' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php esc_html_e( 'Complete PowerUp kit', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Battery and charger included', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'First-time buyers and ready-to-run setup', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Package contents and bar size', 'powerup-theme' ); ?></td>
            </tr>
            <tr>
              <td><?php esc_html_e( 'DeWalt-compatible models', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Selected DeWalt 20V MAX or 60V style battery fit', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Existing compatible-battery owners', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Pack style, voltage note, and whether battery is included', 'powerup-theme' ); ?></td>
            </tr>
            <tr>
              <td><?php esc_html_e( 'Milwaukee-compatible models', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Selected Milwaukee M18 style battery fit', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Existing compatible-battery owners', 'powerup-theme' ); ?></td>
              <td><?php esc_html_e( 'Pack style and whether the listing is tool-only', 'powerup-theme' ); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="battery-guide-section">
    <div class="site-inner battery-guide-inner">
      <div class="chainsaw-series-section-head">
        <h2><?php esc_html_e( 'Battery compatibility FAQ', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Short answers for the questions shoppers most often ask before choosing a tool-only chainsaw.', 'powerup-theme' ); ?></p>
      </div>
      <div class="chainsaw-series-faq__grid">
        <details class="chainsaw-series-faq__item" open>
          <summary><?php esc_html_e( 'Do tool-only chainsaws include a battery or charger?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'No. Tool-only chainsaws do not include a battery or charger unless the product page explicitly says otherwise. Choose a complete kit when you want a ready-to-run package.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Which PowerUp chainsaws work with DeWalt batteries?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Selected PowerUp tool-only chainsaws are designed for compatible DeWalt 20V MAX or 60V style battery packs. Check the individual product page before ordering.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Which PowerUp chainsaws work with Milwaukee batteries?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'Selected PowerUp tool-only and compact chainsaw models are designed for compatible Milwaukee M18 style battery packs. Check the individual product page before ordering.', 'powerup-theme' ); ?></p>
        </details>
        <details class="chainsaw-series-faq__item">
          <summary><?php esc_html_e( 'Does compatibility mean brand affiliation?', 'powerup-theme' ); ?></summary>
          <p><?php esc_html_e( 'No. Compatibility describes battery fit only. PowerUp is not affiliated with, sponsored by, or endorsed by DeWalt or Milwaukee.', 'powerup-theme' ); ?></p>
        </details>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
