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
$blog_is_draft   = false;
$blog_guide_links = array(
  array(
    'label'       => __( 'Battery Guide', 'powerup-theme' ),
    'title'       => __( 'Battery compatibility', 'powerup-theme' ),
    'description' => __( 'Compare complete kits and battery-compatible tool-only paths.', 'powerup-theme' ),
    'url'         => home_url( '/battery-compatibility/' ),
  ),
  array(
    'label'       => __( 'Buying Guide', 'powerup-theme' ),
    'title'       => __( 'Choose the right chainsaw', 'powerup-theme' ),
    'description' => __( 'Review bar size, package contents, and common yard-work use cases.', 'powerup-theme' ),
    'url'         => home_url( '/cordless-chainsaw-battery-compatibility-guide/' ),
  ),
  array(
    'label'       => __( 'Maintenance', 'powerup-theme' ),
    'title'       => __( 'Care and setup tips', 'powerup-theme' ),
    'description' => __( 'Find practical guidance for chain care, oiling, and routine use.', 'powerup-theme' ),
    'url'         => add_query_arg( 's', rawurlencode( 'maintenance chainsaw' ), home_url( '/' ) ),
  ),
  array(
    'label'       => __( 'Accessories', 'powerup-theme' ),
    'title'       => __( 'Guide bars and chains', 'powerup-theme' ),
    'description' => __( 'Compare replacement chains and guide bars for ongoing upkeep.', 'powerup-theme' ),
    'url'         => add_query_arg( 'pcat', array( 'chainsaw-guide-bar', 'chainsaw-chain' ), home_url( '/shop/' ) ),
  ),
);

$featured_guide_post = function_exists( 'powerup_theme_get_featured_blog_guide_post' ) ? powerup_theme_get_featured_blog_guide_post() : null;
$featured_guide_id   = $featured_guide_post instanceof WP_Post ? (int) $featured_guide_post->ID : 0;
$featured_guide_toc  = function_exists( 'powerup_theme_get_featured_blog_guide_toc' ) ? powerup_theme_get_featured_blog_guide_toc() : array();
$excluded_post_ids   = $featured_guide_id > 0 ? array( $featured_guide_id ) : array();
$hello_world_post    = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $hello_world_post instanceof WP_Post ) {
  $excluded_post_ids[] = (int) $hello_world_post->ID;
}

$blog_query = new WP_Query(
  array(
    'post_type'           => 'post',
    'posts_per_page'      => 7,
    'ignore_sticky_posts' => true,
    'post__not_in'        => array_values( array_unique( $excluded_post_ids ) ),
  )
);

$posts_data = array();
if ($blog_query->have_posts()) {
  $image_index = 1;
  while ($blog_query->have_posts()) {
    $blog_query->the_post();
    $reading_data = function_exists( 'powerup_theme_get_post_reading_time_data' ) ? powerup_theme_get_post_reading_time_data( get_the_ID() ) : array();
    $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
    if ( ! $thumb_url ) {
      $cover_url = get_post_meta( get_the_ID(), '_powerup_cover_image_url', true );
      $thumb_url = is_string( $cover_url ) ? trim( $cover_url ) : '';
    }
    $primary_category = null;
    $post_categories  = get_the_category( get_the_ID() );
    if ( ! empty( $post_categories ) && $post_categories[0] instanceof WP_Term ) {
      $primary_category = $post_categories[0];
    }
    $posts_data[] = array(
      'title'        => get_the_title(),
      'excerpt'      => wp_trim_words(get_the_excerpt(), 18, '...'),
      'url'          => get_permalink(),
      'image'        => $thumb_url ? $thumb_url : $fallback_images[$image_index % count($fallback_images)],
      'date'         => powerup_theme_format_english_post_date( get_the_ID() ),
      'reading'      => isset( $reading_data['label'] ) ? (string) $reading_data['label'] : __( '1 min read', 'powerup-theme' ),
      'category'     => $primary_category instanceof WP_Term ? (string) $primary_category->name : __( 'Guide', 'powerup-theme' ),
      'category_url' => $primary_category instanceof WP_Term ? get_category_link( $primary_category ) : home_url( '/blog/' ),
    );
    $image_index++;
  }
  wp_reset_postdata();
}

if ( $blog_is_draft ) {
  $featured_guide_post = null;
  $posts_data = array();
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
    <?php foreach ( $blog_guide_links as $index => $guide_link ) : ?>
      <a class="blog-ref-feature-item blog-ref-feature-link" href="<?php echo esc_url( $guide_link['url'] ); ?>">
        <span class="blog-ref-feature-icon" aria-hidden="true">
          <?php if ( 0 === $index ) : ?>
            <svg viewBox="0 0 24 24"><rect x="2" y="7" width="18" height="10" rx="1.5"></rect><path d="M22 10v4"></path><path d="M6 10h4"></path></svg>
          <?php elseif ( 1 === $index ) : ?>
            <svg viewBox="0 0 24 24"><path d="M4 7h16"></path><path d="M7 12h10"></path><path d="M10 17h4"></path></svg>
          <?php elseif ( 2 === $index ) : ?>
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a8 8 0 000-6"></path><path d="M4.6 9a8 8 0 000 6"></path></svg>
          <?php else : ?>
            <svg viewBox="0 0 24 24"><path d="M5 7h14"></path><path d="M7 7l1 12h8l1-12"></path><path d="M9 11h6"></path></svg>
          <?php endif; ?>
        </span>
        <span class="blog-ref-feature-copy">
          <span class="blog-ref-feature-label"><?php echo esc_html( $guide_link['label'] ); ?></span>
          <span><?php echo esc_html( $guide_link['title'] ); ?></span>
        </span>
      </a>
    <?php endforeach; ?>
  </section>

  <section class="blog-ref-content">
    <div class="blog-ref-content-inner">
      <div class="blog-ref-main">
        <h2 class="blog-ref-title"><?php esc_html_e('BLOG POSTS', 'powerup-theme'); ?></h2>

        <?php if ( $blog_is_draft ) : ?>
          <article class="blog-ref-guide">
            <div class="blog-ref-guide__header">
              <span class="powerup-series-badge"><?php esc_html_e( 'Guides In Progress', 'powerup-theme' ); ?></span>
              <h3><?php esc_html_e( 'Practical Cordless Chainsaw Guides Are Coming Soon', 'powerup-theme' ); ?></h3>
              <p><?php esc_html_e( 'We are preparing clear guides for chainsaw sizing, battery compatibility, replacement chains, guide bars, maintenance, and safer everyday pruning.', 'powerup-theme' ); ?></p>
            </div>
          </article>
        <?php endif; ?>

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
              <p class="blog-ref-guide__meta"><?php echo esc_html( powerup_theme_format_english_post_date( $featured_guide_post->ID ) ); ?> | <?php echo esc_html( wp_strip_all_tags( get_the_category_list( ', ', '', $featured_guide_post->ID ) ) ); ?> | <span class="blog-ref-reading-time"><?php echo esc_html( $featured_guide_reading['label'] ?? __( '1 min read', 'powerup-theme' ) ); ?></span></p>
              <?php if ( has_excerpt( $featured_guide_post ) ) : ?>
                <p><?php echo esc_html( get_the_excerpt( $featured_guide_post ) ); ?></p>
              <?php endif; ?>
            </div>
            <div class="blog-ref-guide__summary">
              <div class="blog-ref-guide__summary-copy">
                <p><?php esc_html_e( 'This featured guide explains how to choose, use, and maintain cordless chainsaws and related outdoor tools with a practical focus on battery fit and everyday yard work.', 'powerup-theme' ); ?></p>
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

        <?php if ( ! empty( $posts_data ) ) : ?>
        <div class="blog-ref-lead-row">
          <article class="blog-ref-lead-card">
            <a href="<?php echo esc_url($posts_data[0]['url']); ?>">
              <img src="<?php echo esc_url($posts_data[0]['image']); ?>" alt="<?php echo esc_attr($posts_data[0]['title']); ?>" loading="lazy" decoding="async">
            </a>
            <div class="blog-ref-card-copy">
              <a class="blog-ref-category-chip" href="<?php echo esc_url($posts_data[0]['category_url']); ?>"><?php echo esc_html($posts_data[0]['category']); ?></a>
              <h3><a href="<?php echo esc_url($posts_data[0]['url']); ?>"><?php echo esc_html($posts_data[0]['title']); ?></a></h3>
              <p><?php echo esc_html($posts_data[0]['excerpt']); ?></p>
              <div class="blog-ref-card-meta">
                <a class="blog-ref-read-btn" href="<?php echo esc_url($posts_data[0]['url']); ?>"><?php esc_html_e('Read More', 'powerup-theme'); ?></a>
                <span><?php echo esc_html($posts_data[0]['date']); ?> | <span class="blog-ref-reading-time"><?php echo esc_html($posts_data[0]['reading']); ?></span></span>
              </div>
            </div>
          </article>

          <div class="blog-ref-mini-grid">
            <?php for ($i = 1; $i <= 4 && isset( $posts_data[$i] ); $i++) : ?>
              <article class="blog-ref-mini-card">
                <a href="<?php echo esc_url($posts_data[$i]['url']); ?>">
                  <img src="<?php echo esc_url($posts_data[$i]['image']); ?>" alt="<?php echo esc_attr($posts_data[$i]['title']); ?>" loading="lazy" decoding="async">
                </a>
                <div class="blog-ref-card-copy">
                  <a class="blog-ref-category-chip" href="<?php echo esc_url($posts_data[$i]['category_url']); ?>"><?php echo esc_html($posts_data[$i]['category']); ?></a>
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
          <?php for ($i = 5; $i <= 6 && isset( $posts_data[$i] ); $i++) : ?>
            <article class="blog-ref-bottom-card">
              <a href="<?php echo esc_url($posts_data[$i]['url']); ?>">
                <img src="<?php echo esc_url($posts_data[$i]['image']); ?>" alt="<?php echo esc_attr($posts_data[$i]['title']); ?>" loading="lazy" decoding="async">
              </a>
              <div class="blog-ref-card-copy">
                <a class="blog-ref-category-chip" href="<?php echo esc_url($posts_data[$i]['category_url']); ?>"><?php echo esc_html($posts_data[$i]['category']); ?></a>
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
        <?php endif; ?>
      </div>

      <aside class="blog-ref-sidebar">
        <div class="blog-ref-sidebar-box">
          <h3><?php esc_html_e('CATEGORIES', 'powerup-theme'); ?></h3>
          <form role="search" method="get" class="blog-ref-search" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="search" placeholder="<?php esc_attr_e('Search', 'powerup-theme'); ?>" name="s" value="">
          </form>
          <ul class="blog-ref-cat-list">
            <?php foreach ( $blog_guide_links as $guide_link ) : ?>
              <li>
                <a href="<?php echo esc_url( $guide_link['url'] ); ?>">
                  <span>▸</span>
                  <strong><?php echo esc_html( $guide_link['label'] ); ?></strong>
                  <em><?php echo esc_html( $guide_link['description'] ); ?></em>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <h4><?php esc_html_e('POPULAR TAGS', 'powerup-theme'); ?></h4>
          <p class="blog-ref-tags"><?php esc_html_e('cordless chainsaw, replacement chain, guide bar, battery compatibility, maintenance', 'powerup-theme'); ?></p>
        </div>
      </aside>
    </div>
  </section>

</main>
<?php get_footer(); ?>
