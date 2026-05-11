<?php
/**
 * Template part for displaying post content
 *
 * @package PowerUp_Theme
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'feature-card' ); ?>>
  <?php
  $current_post_id   = get_the_ID();
  $is_featured_guide = false;
  $faq_items         = array();
  $guide_toc_items   = array();
  $related_posts     = function_exists( 'powerup_theme_get_related_posts_for_post' ) ? powerup_theme_get_related_posts_for_post( $current_post_id, 3 ) : array();
  $reading_data      = function_exists( 'powerup_theme_get_post_reading_time_data' ) ? powerup_theme_get_post_reading_time_data( $current_post_id ) : array(
    'label' => __( '1 min read', 'powerup-theme' ),
  );

  if ( function_exists( 'powerup_theme_get_featured_blog_guide_post' ) ) {
    $featured_guide_post = powerup_theme_get_featured_blog_guide_post();
    if ( $featured_guide_post instanceof WP_Post && (int) $featured_guide_post->ID === (int) $current_post_id ) {
      $is_featured_guide = true;
    }
  }

  if ( $is_featured_guide && function_exists( 'powerup_theme_get_featured_blog_guide_faq_items' ) ) {
    $faq_items = powerup_theme_get_featured_blog_guide_faq_items();
    $faq_items = is_array( $faq_items ) ? $faq_items : array();
  }

  if ( $is_featured_guide && function_exists( 'powerup_theme_get_featured_blog_guide_toc' ) ) {
    $guide_toc_items = powerup_theme_get_featured_blog_guide_toc();
    $guide_toc_items = is_array( $guide_toc_items ) ? $guide_toc_items : array();
  }
  ?>

  <nav class="post-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'powerup-theme' ); ?>">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'powerup-theme' ); ?></a>
    <span aria-hidden="true">/</span>
    <a href="<?php echo esc_url( function_exists( 'powerup_theme_get_blog_page_url' ) ? powerup_theme_get_blog_page_url() : home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'powerup-theme' ); ?></a>
    <span aria-hidden="true">/</span>
    <span class="is-current"><?php the_title(); ?></span>
  </nav>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="product-image">
      <?php the_post_thumbnail( 'large' ); ?>
    </div>
  <?php else : ?>
    <?php $powerup_cover_image_url = get_post_meta( get_the_ID(), '_powerup_cover_image_url', true ); ?>
    <?php if ( is_string( $powerup_cover_image_url ) && '' !== $powerup_cover_image_url ) : ?>
      <div class="product-image">
        <img src="<?php echo esc_url( $powerup_cover_image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
      </div>
    <?php endif; ?>
  <?php endif; ?>
  <header class="entry-header">
    <h1 class="entry-title"><?php the_title(); ?></h1>
    <div class="entry-meta">
      <span><?php echo get_the_date(); ?></span>
      <span> · </span>
      <span><?php echo esc_html( get_the_author() ); ?></span>
      <span> · </span>
      <span class="entry-reading-time"><?php echo esc_html( $reading_data['label'] ?? __( '1 min read', 'powerup-theme' ) ); ?></span>
    </div>
  </header>

  <div class="post-guide-layout<?php echo ! empty( $guide_toc_items ) ? ' has-toc' : ''; ?>">
    <div class="entry-content" id="post-guide-content">
      <?php the_content(); ?>

      <?php if ( ! empty( $reading_data['word_count'] ) ) : ?>
        <div class="entry-reading-stats">
          <span><?php echo esc_html( number_format_i18n( (int) $reading_data['word_count'] ) ); ?> <?php echo esc_html( _n( 'word', 'words', (int) $reading_data['word_count'], 'powerup-theme' ) ); ?></span>
          <span aria-hidden="true">·</span>
          <span><?php echo esc_html( $reading_data['label'] ?? __( '1 min read', 'powerup-theme' ) ); ?></span>
        </div>
      <?php endif; ?>
    </div>

    <?php if ( ! empty( $guide_toc_items ) ) : ?>
      <aside class="post-guide-toc" aria-label="<?php esc_attr_e( 'Article navigation', 'powerup-theme' ); ?>" data-guide-toc>
        <strong><?php esc_html_e( 'On This Page', 'powerup-theme' ); ?></strong>
        <div class="post-guide-progress" aria-hidden="true">
          <div class="post-guide-progress__label">
            <span><?php esc_html_e( 'Reading Progress', 'powerup-theme' ); ?></span>
            <span data-guide-progress-text>0%</span>
          </div>
          <div class="post-guide-progress__track">
            <span data-guide-progress-bar></span>
          </div>
        </div>
        <ul>
          <?php foreach ( $guide_toc_items as $toc_item ) : ?>
            <?php
            $toc_id    = isset( $toc_item['id'] ) ? sanitize_title( (string) $toc_item['id'] ) : '';
            $toc_label = isset( $toc_item['label'] ) ? (string) $toc_item['label'] : '';
            if ( '' === $toc_id || '' === $toc_label ) {
              continue;
            }
            ?>
            <li><a href="#<?php echo esc_attr( $toc_id ); ?>" data-guide-link><?php echo esc_html( $toc_label ); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php endif; ?>
  </div>

  <?php if ( ! empty( $faq_items ) ) : ?>
    <section class="post-faq" aria-labelledby="post-faq-title">
      <h2 id="post-faq-title"><?php esc_html_e( 'Frequently Asked Questions', 'powerup-theme' ); ?></h2>
      <div class="post-faq-list">
        <?php foreach ( $faq_items as $faq_item ) : ?>
          <?php
          $question = isset( $faq_item['question'] ) ? (string) $faq_item['question'] : '';
          $answer   = isset( $faq_item['answer'] ) ? (string) $faq_item['answer'] : '';

          if ( '' === $question || '' === $answer ) {
            continue;
          }
          ?>
          <details class="post-faq-item">
            <summary><?php echo esc_html( $question ); ?></summary>
            <p><?php echo esc_html( $answer ); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ( ! empty( $related_posts ) ) : ?>
    <section class="post-related" aria-labelledby="post-related-title">
      <h2 id="post-related-title"><?php esc_html_e( 'Related Articles', 'powerup-theme' ); ?></h2>
      <div class="post-related-grid">
        <?php foreach ( $related_posts as $related_post ) : ?>
          <article class="post-related-card">
            <a class="post-related-card__media" href="<?php echo esc_url( $related_post['url'] ); ?>">
              <img src="<?php echo esc_url( $related_post['image'] ); ?>" alt="<?php echo esc_attr( $related_post['title'] ); ?>" loading="lazy" decoding="async">
            </a>
            <div class="post-related-card__body">
              <h3><a href="<?php echo esc_url( $related_post['url'] ); ?>"><?php echo esc_html( $related_post['title'] ); ?></a></h3>
              <?php if ( ! empty( $related_post['excerpt'] ) ) : ?>
                <p><?php echo esc_html( $related_post['excerpt'] ); ?></p>
              <?php endif; ?>
              <div class="post-related-card__meta">
                <span><?php echo esc_html( $related_post['date'] ); ?></span>
                <span aria-hidden="true">·</span>
                <span><?php echo esc_html( $related_post['reading'] ); ?></span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</article>
