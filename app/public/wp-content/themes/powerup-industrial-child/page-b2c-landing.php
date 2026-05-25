<?php
/**
 * Template Name: B2C Landing
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

get_header();

$shop_url    = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
$contact_url = function_exists( 'powerup_theme_get_contact_page_url' ) ? powerup_theme_get_contact_page_url() : home_url( '/contact-us/' );
?>

<main class="b2c-landing-template">
  <section class="b2c-landing-hero">
    <div class="b2c-landing-hero__copy">
      <span><?php echo esc_html__( 'Cordless outdoor power tools', 'powerup-industrial-child' ); ?></span>
      <h1><?php echo esc_html__( 'Find The Right Battery Chainsaw Faster', 'powerup-industrial-child' ); ?></h1>
      <p><?php echo esc_html__( 'Compare ready-to-run kits and tool-only cordless chainsaws built for home, yard, and light-duty outdoor work.', 'powerup-industrial-child' ); ?></p>
      <div class="b2c-landing-hero__actions">
        <a class="btn btn-primary" href="<?php echo esc_url( $shop_url ); ?>">
          <?php echo esc_html__( 'Shop Tools', 'powerup-industrial-child' ); ?>
        </a>
        <a class="btn btn-secondary" href="<?php echo esc_url( $contact_url ); ?>">
          <?php echo esc_html__( 'Ask For Support', 'powerup-industrial-child' ); ?>
        </a>
      </div>
    </div>
  </section>

  <section class="b2c-landing-benefits" aria-label="<?php echo esc_attr__( 'Shopping benefits', 'powerup-industrial-child' ); ?>">
    <article>
      <h2><?php echo esc_html__( 'Choose By Battery Platform', 'powerup-industrial-child' ); ?></h2>
      <p><?php echo esc_html__( 'Help buyers quickly decide between a complete kit and compatible tool-only models.', 'powerup-industrial-child' ); ?></p>
    </article>
    <article>
      <h2><?php echo esc_html__( 'See Practical Details', 'powerup-industrial-child' ); ?></h2>
      <p><?php echo esc_html__( 'Product pages emphasize included parts, intended jobs, and support information.', 'powerup-industrial-child' ); ?></p>
    </article>
    <article>
      <h2><?php echo esc_html__( 'Buy With Support', 'powerup-industrial-child' ); ?></h2>
      <p><?php echo esc_html__( 'Contact options stay visible for setup questions, order help, and warranty support.', 'powerup-industrial-child' ); ?></p>
    </article>
  </section>

  <section class="b2c-landing-content">
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
