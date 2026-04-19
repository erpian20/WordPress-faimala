<?php
/**
 * The template for displaying all single posts
 *
 * @package PowerUp_Theme
 */
get_header(); ?>
<main class="site-section">
  <div class="site-inner">
    <?php
    while ( have_posts() ) : the_post();
      get_template_part( 'template-parts/content', get_post_type() );
      if ( comments_open() || get_comments_number() ) {
        comments_template();
      }
    endwhile;
    ?>
  </div>
</main>
<?php get_footer(); ?>