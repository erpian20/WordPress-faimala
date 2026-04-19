<?php
/**
 * The template for displaying all pages
 *
 * @package PowerUp_Theme
 */
get_header(); ?>
<main class="site-section">
  <div class="site-inner">
    <?php
    while ( have_posts() ) : the_post();
      ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class( 'feature-card' ); ?>>
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
</main>
<?php get_footer(); ?>