<?php
/**
 * Template Name: B2C Landing
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

get_header();
?>

<main class="b2c-landing-template" style="padding:40px 20px;max-width:1200px;margin:0 auto;">
  <section style="background:#111;color:#fff;padding:40px;border-radius:16px;">
    <h1 style="margin:0 0 12px;"><?php echo esc_html__( 'Global Power Tools For Modern Commerce', 'powerup-industrial-child' ); ?></h1>
    <p style="margin:0 0 20px;"><?php echo esc_html__( 'Use this page template for campaign landing pages, localized promotions, and ERP-driven bestseller showcases.', 'powerup-industrial-child' ); ?></p>
    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" style="display:inline-block;background:#ff6a00;color:#111;padding:12px 18px;border-radius:8px;font-weight:700;">
      <?php echo esc_html__( 'Shop Now', 'powerup-industrial-child' ); ?>
    </a>
  </section>

  <section style="padding:30px 0;">
    <?php
    while ( have_posts() ) :
      the_post();
      the_content();
    endwhile;
    ?>
  </section>
</main>

<?php
get_footer();
