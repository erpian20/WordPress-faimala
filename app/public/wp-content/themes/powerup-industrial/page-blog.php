<?php
/**
 * Template Name: Blog Page
 *
 * @package PowerUp_Theme
 */
get_header();

$hero_image_path = get_template_directory() . '/assets/images/blog-hero-custom.jpg';
$hero_image      = get_template_directory_uri() . '/assets/images/blog-hero-custom.jpg';
if ( file_exists( $hero_image_path ) ) {
  $hero_image .= '?v=' . filemtime( $hero_image_path );
}
$fallback_image  = get_template_directory_uri() . '/assets/images/product-placeholder.svg';
$fallback_images = array_fill( 0, 8, $fallback_image );

$featured_guide_post = function_exists( 'powerup_theme_get_featured_blog_guide_post' ) ? powerup_theme_get_featured_blog_guide_post() : null;
$featured_guide_id   = $featured_guide_post instanceof WP_Post ? (int) $featured_guide_post->ID : 0;
$featured_guide_toc  = function_exists( 'powerup_theme_get_featured_blog_guide_toc' ) ? powerup_theme_get_featured_blog_guide_toc() : array();

$blog_query = new WP_Query(
  array(
    'post_type'           => 'post',
    'posts_per_page'      => 7,
    'ignore_sticky_posts' => true,
    'post__not_in'        => $featured_guide_id > 0 ? array( $featured_guide_id ) : array(),
  )
);

$posts_data = array();
if ($blog_query->have_posts()) {
  $image_index = 1;
  while ($blog_query->have_posts()) {
    $blog_query->the_post();
    $reading_data = function_exists( 'powerup_theme_get_post_reading_time_data' ) ? powerup_theme_get_post_reading_time_data( get_the_ID() ) : array();
    $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
    $posts_data[] = array(
      'title'   => get_the_title(),
      'excerpt' => wp_trim_words(get_the_excerpt(), 18, '...'),
      'url'     => get_permalink(),
      'image'   => $thumb_url ? $thumb_url : $fallback_images[$image_index % count($fallback_images)],
      'date'    => get_the_date(),
      'reading' => isset( $reading_data['label'] ) ? (string) $reading_data['label'] : __( '1 min read', 'powerup-theme' ),
    );
    $image_index++;
  }
  wp_reset_postdata();
}

while (count($posts_data) < 7) {
  $i = count($posts_data) + 1;
  $fallback_reading = function_exists( 'powerup_theme_format_reading_time_label' ) ? powerup_theme_format_reading_time_label( 5 ) : __( '5 min read', 'powerup-theme' );
  $posts_data[] = array(
    'title'   => sprintf(__('Lithium Tool Guide %d', 'powerup-theme'), $i),
    'excerpt' => __('Actionable tips on battery care, safe operation, and daily maintenance to keep cordless tools running at peak performance.', 'powerup-theme'),
    'url'     => '#',
    'image'   => $fallback_images[$i % count($fallback_images)],
    'date'    => __('Recent News', 'powerup-theme'),
    'reading' => $fallback_reading,
  );
}
?>
<main class="blog-reference-page">
  <section class="blog-ref-hero" style="background-image: linear-gradient(90deg, rgba(22,22,22,0.86) 0%, rgba(22,22,22,0.78) 45%, rgba(22,22,22,0.32) 100%), url('<?php echo esc_url($hero_image); ?>');">
    <div class="blog-ref-hero-inner">
      <div class="blog-ref-hero-copy">
        <h1><?php esc_html_e('BLOG', 'powerup-theme'); ?></h1>
        <p><?php esc_html_e('INSIGHTS & GUIDES', 'powerup-theme'); ?></p>
        <div class="blog-ref-hero-actions">
          <a class="btn-ref btn-ref-primary" href="<?php echo esc_url(home_url('/shop/')); ?>"><?php esc_html_e('SHOP NOW', 'powerup-theme'); ?></a>
          <a class="btn-ref btn-ref-ghost" href="<?php echo esc_url(home_url('/about-us/')); ?>"><?php esc_html_e('LEARN MORE', 'powerup-theme'); ?></a>
        </div>
      </div>
    </div>
  </section>

  <section class="blog-ref-feature-strip">
    <div class="blog-ref-feature-item"><span class="blog-ref-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="18" height="10" rx="1.5"></rect><path d="M22 10v4"></path><path d="M6 10h4"></path></svg></span><span class="blog-ref-feature-label"><?php esc_html_e('Battery & Efficiency', 'powerup-theme'); ?></span></div>
    <div class="blog-ref-feature-item"><span class="blog-ref-feature-icon" aria-hidden="true">⚙</span><span class="blog-ref-feature-label"><?php esc_html_e('Longer Battery Life', 'powerup-theme'); ?></span></div>
    <div class="blog-ref-feature-item"><span class="blog-ref-feature-icon" aria-hidden="true">⨂</span><span class="blog-ref-feature-label"><?php esc_html_e('Performance Armor', 'powerup-theme'); ?></span></div>
    <div class="blog-ref-feature-item"><span class="blog-ref-feature-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"></path><path d="M9.5 12l1.8 1.8L14.8 10"></path></svg></span><span class="blog-ref-feature-label"><?php esc_html_e('Built To Last', 'powerup-theme'); ?></span></div>
  </section>

  <section class="blog-ref-content">
    <div class="blog-ref-content-inner">
      <div class="blog-ref-main">
        <h2 class="blog-ref-title"><?php esc_html_e('BLOG POSTS', 'powerup-theme'); ?></h2>

        <?php if ( $featured_guide_post instanceof WP_Post ) : ?>
          <?php $featured_guide_reading = function_exists( 'powerup_theme_get_post_reading_time_data' ) ? powerup_theme_get_post_reading_time_data( $featured_guide_post->ID ) : array( 'label' => __( '1 min read', 'powerup-theme' ) ); ?>
          <article class="blog-ref-guide">
            <?php $featured_guide_cover = get_the_post_thumbnail_url( $featured_guide_post, 'large' ); ?>
            <?php if ( ! $featured_guide_cover ) { $featured_guide_cover = get_post_meta( $featured_guide_post->ID, '_powerup_cover_image_url', true ); } ?>
            <?php if ( $featured_guide_cover ) : ?>
              <a class="blog-ref-guide__media" href="<?php echo esc_url( get_permalink( $featured_guide_post ) ); ?>">
                <img src="<?php echo esc_url( $featured_guide_cover ); ?>" alt="<?php echo esc_attr( get_the_title( $featured_guide_post ) ); ?>" loading="lazy" decoding="async">
              </a>
            <?php endif; ?>
            <div class="blog-ref-guide__header">
              <span class="powerup-series-badge"><?php esc_html_e( 'Featured Guide', 'powerup-theme' ); ?></span>
              <h3><a href="<?php echo esc_url( get_permalink( $featured_guide_post ) ); ?>"><?php echo esc_html( get_the_title( $featured_guide_post ) ); ?></a></h3>
              <p class="blog-ref-guide__meta"><?php echo esc_html( get_the_date( '', $featured_guide_post ) ); ?> | <?php echo esc_html( wp_strip_all_tags( get_the_category_list( ', ', '', $featured_guide_post->ID ) ) ); ?> | <span class="blog-ref-reading-time"><?php echo esc_html( $featured_guide_reading['label'] ?? __( '1 min read', 'powerup-theme' ) ); ?></span></p>
              <?php if ( has_excerpt( $featured_guide_post ) ) : ?>
                <p><?php echo esc_html( get_the_excerpt( $featured_guide_post ) ); ?></p>
              <?php endif; ?>
            </div>
            <div class="blog-ref-guide__summary">
              <div class="blog-ref-guide__summary-copy">
                <p><?php esc_html_e( 'This featured guide compares the core cordless outdoor tool categories, explains where 20V and 40V systems fit best, and gives buyers a practical framework for choosing the right battery platform.', 'powerup-theme' ); ?></p>
                <div class="blog-ref-guide__tags">
                  <?php foreach ( get_the_tags( $featured_guide_post->ID ) ?: array() as $featured_tag ) : ?>
                    <span><?php echo esc_html( $featured_tag->name ); ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php if ( ! empty( $featured_guide_toc ) ) : ?>
                <div class="blog-ref-guide__toc">
                  <strong><?php esc_html_e( 'Inside This Article', 'powerup-theme' ); ?></strong>
                  <ul>
                    <?php foreach ( $featured_guide_toc as $toc_item ) : ?>
                      <li><a href="<?php echo esc_url( get_permalink( $featured_guide_post ) . '#' . $toc_item['id'] ); ?>"><?php echo esc_html( $toc_item['label'] ); ?></a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
            <div class="blog-ref-guide__cta">
              <strong><?php esc_html_e( 'Open the full article', 'powerup-theme' ); ?></strong>
              <p><?php esc_html_e( 'Read the complete guide with anchored sections, direct sharing URL, and standalone article layout.', 'powerup-theme' ); ?></p>
              <a class="btn-ref btn-ref-primary" href="<?php echo esc_url( get_permalink( $featured_guide_post ) ); ?>"><?php esc_html_e( 'Open Article', 'powerup-theme' ); ?></a>
            </div>
          </article>
        <?php endif; ?>

        <div class="blog-ref-lead-row">
          <article class="blog-ref-lead-card">
            <a href="<?php echo esc_url($posts_data[0]['url']); ?>">
              <img src="<?php echo esc_url($posts_data[0]['image']); ?>" alt="<?php echo esc_attr($posts_data[0]['title']); ?>" loading="lazy" decoding="async">
            </a>
            <div class="blog-ref-card-copy">
              <h3><a href="<?php echo esc_url($posts_data[0]['url']); ?>"><?php echo esc_html($posts_data[0]['title']); ?></a></h3>
              <p><?php echo esc_html($posts_data[0]['excerpt']); ?></p>
              <div class="blog-ref-card-meta">
                <a class="blog-ref-read-btn" href="<?php echo esc_url($posts_data[0]['url']); ?>"><?php esc_html_e('Read More', 'powerup-theme'); ?></a>
                <span><?php echo esc_html($posts_data[0]['date']); ?> | <span class="blog-ref-reading-time"><?php echo esc_html($posts_data[0]['reading']); ?></span></span>
              </div>
            </div>
          </article>

          <div class="blog-ref-mini-grid">
            <?php for ($i = 1; $i <= 4; $i++) : ?>
              <article class="blog-ref-mini-card">
                <a href="<?php echo esc_url($posts_data[$i]['url']); ?>">
                  <img src="<?php echo esc_url($posts_data[$i]['image']); ?>" alt="<?php echo esc_attr($posts_data[$i]['title']); ?>" loading="lazy" decoding="async">
                </a>
                <div class="blog-ref-card-copy">
                  <h4><a href="<?php echo esc_url($posts_data[$i]['url']); ?>"><?php echo esc_html($posts_data[$i]['title']); ?></a></h4>
                  <div class="blog-ref-card-meta">
                    <a class="blog-ref-read-btn" href="<?php echo esc_url($posts_data[$i]['url']); ?>"><?php esc_html_e('Read More', 'powerup-theme'); ?></a>
                    <span><?php echo esc_html($posts_data[$i]['date']); ?> | <span class="blog-ref-reading-time"><?php echo esc_html($posts_data[$i]['reading']); ?></span></span>
                  </div>
                </div>
              </article>
            <?php endfor; ?>
          </div>
        </div>

        <div class="blog-ref-bottom-grid">
          <?php for ($i = 4; $i <= 6; $i++) : ?>
            <article class="blog-ref-bottom-card">
              <a href="<?php echo esc_url($posts_data[$i]['url']); ?>">
                <img src="<?php echo esc_url($posts_data[$i]['image']); ?>" alt="<?php echo esc_attr($posts_data[$i]['title']); ?>" loading="lazy" decoding="async">
              </a>
              <div class="blog-ref-card-copy">
                <h3><a href="<?php echo esc_url($posts_data[$i]['url']); ?>"><?php echo esc_html($posts_data[$i]['title']); ?></a></h3>
                <p><?php echo esc_html($posts_data[$i]['excerpt']); ?></p>
                <div class="blog-ref-card-meta">
                  <a class="blog-ref-read-btn" href="<?php echo esc_url($posts_data[$i]['url']); ?>"><?php esc_html_e('Read More', 'powerup-theme'); ?></a>
                  <span><?php echo esc_html($posts_data[$i]['date']); ?> | <span class="blog-ref-reading-time"><?php echo esc_html($posts_data[$i]['reading']); ?></span></span>
                </div>
              </div>
            </article>
          <?php endfor; ?>
        </div>
      </div>

      <aside class="blog-ref-sidebar">
        <div class="blog-ref-sidebar-box">
          <h3><?php esc_html_e('CATEGORIES', 'powerup-theme'); ?></h3>
          <form role="search" method="get" class="blog-ref-search" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="search" placeholder="<?php esc_attr_e('Search', 'powerup-theme'); ?>" name="s" value="">
          </form>
          <ul class="blog-ref-cat-list">
            <li><span>▸</span><?php esc_html_e('Cordless Chainsaw Guides', 'powerup-theme'); ?></li>
            <li><span>▸</span><?php esc_html_e('Battery Maintenance', 'powerup-theme'); ?></li>
            <li><span>▸</span><?php esc_html_e('Product Comparisons', 'powerup-theme'); ?></li>
            <li><span>▸</span><?php esc_html_e('DIY & Landscaping Tips', 'powerup-theme'); ?></li>
          </ul>
          <h4><?php esc_html_e('POPULAR TAGS', 'powerup-theme'); ?></h4>
          <p class="blog-ref-tags"><?php esc_html_e('lithium battery, chainsaw, hedge trimmer, brushless motor, runtime', 'powerup-theme'); ?></p>
        </div>
      </aside>
    </div>
  </section>

  <section class="blog-ref-featured-reviews">
    <div class="blog-ref-featured-inner">
      <h2><?php esc_html_e('FEATURED REVIEWS', 'powerup-theme'); ?></h2>
      <div class="blog-ref-reviews-grid">
        <?php for ($i = 0; $i < 4; $i++) : ?>
          <article class="blog-ref-review-card">
            <img src="<?php echo esc_url($fallback_images[($i + 2) % count($fallback_images)]); ?>" alt="<?php esc_attr_e('Featured review', 'powerup-theme'); ?>" loading="lazy" decoding="async">
          </article>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <section class="blog-ref-newsletter">
    <div class="blog-ref-newsletter-inner">
      <h2><?php esc_html_e('SUBSCRIBE TO OUR NEWSLETTER', 'powerup-theme'); ?></h2>
      <p><?php esc_html_e('Get the latest deals & updates', 'powerup-theme'); ?></p>
      <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'subscribe', 'is-inline-dark' ); } ?>
      <form class="blog-ref-newsletter-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="email" name="subscriber_email" placeholder="<?php esc_attr_e('Enter your email address', 'powerup-theme'); ?>" required>
        <?php wp_nonce_field( 'powerup_subscribe_submit', 'powerup_subscribe_nonce' ); ?>
        <button type="submit"><?php esc_html_e('SUBSCRIBE', 'powerup-theme'); ?></button>
        <input type="hidden" name="action" value="powerup_subscribe">
      </form>
    </div>
  </section>
</main>
<?php get_footer(); ?>