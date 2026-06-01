<?php
/**
 * Template Name: Policy Page
 *
 * @package PowerUp_Theme
 */

get_header();

$policy_slug = (string) get_post_field( 'post_name', get_queried_object_id() );
$policy      = function_exists( 'powerup_theme_get_policy_page_content' ) ? powerup_theme_get_policy_page_content( $policy_slug ) : array();
?>
<main class="policy-page">
  <section class="policy-page__hero">
    <div class="site-inner policy-page__inner">
      <span class="powerup-series-badge"><?php esc_html_e( 'Customer Information', 'powerup-theme' ); ?></span>
      <h1><?php echo esc_html( $policy['title'] ?? get_the_title() ); ?></h1>
      <p><?php echo esc_html( $policy['intro'] ?? '' ); ?></p>
    </div>
  </section>

  <section class="policy-page__content">
    <div class="site-inner policy-page__inner">
      <p class="policy-page__updated"><?php esc_html_e( 'Last updated: May 31, 2026', 'powerup-theme' ); ?></p>
      <?php echo wp_kses_post( $policy['content'] ?? '' ); ?>
    </div>
  </section>
</main>
<?php
get_footer();
