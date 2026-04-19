<?php
/**
 * The main template file
 *
 * @package PowerUp_Theme
 */
get_header(); ?>
<main class="site-section">
  <div class="site-inner">
    <?php if ( have_posts() ) : ?>
      <div class="section-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 28px;">
        <?php while ( have_posts() ) : the_post(); ?>
          <article id="post-<?php the_ID(); ?>" <?php post_class( 'product-card' ); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
              <div class="product-image"><?php the_post_thumbnail( 'medium_large' ); ?></div>
            <?php endif; ?>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p><?php echo wp_trim_words( get_the_excerpt(), 28, '...' ); ?></p>
            <a class="btn btn-secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'powerup-theme' ); ?></a>
          </article>
        <?php endwhile; ?>
      </div>
      <div class="pagination" style="margin-top: 40px;">
        <?php echo paginate_links(); ?>
      </div>
    <?php else : ?>
      <p><?php esc_html_e( 'No posts were found.', 'powerup-theme' ); ?></p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>