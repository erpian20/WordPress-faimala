<?php
/**
 * The template for displaying archive pages
 *
 * @package PowerUp_Theme
 */
get_header(); ?>
<main class="site-section">
  <div class="site-inner">
    <div class="section-heading">
      <h1><?php esc_html_e( 'Archive', 'powerup-theme' ); ?></h1>
      <p><?php esc_html_e( 'Browse our articles and updates.', 'powerup-theme' ); ?></p>
    </div>
    <?php if ( have_posts() ) : ?>
      <div class="section-grid archive-grid">
        <?php while ( have_posts() ) : the_post(); ?>
          <article class="product-card">
            <?php if ( has_post_thumbnail() ) : ?>
              <div class="product-image"><?php the_post_thumbnail( 'medium_large' ); ?></div>
            <?php endif; ?>
            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <p><?php echo wp_trim_words( get_the_excerpt(), 24, '...' ); ?></p>
            <a class="btn btn-secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'powerup-theme' ); ?></a>
          </article>
        <?php endwhile; ?>
      </div>
      <div class="pagination archive-pagination">
        <?php echo paginate_links(); ?>
      </div>
    <?php else : ?>
      <p><?php esc_html_e( 'No content found.', 'powerup-theme' ); ?></p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>