<?php
/**
 * The template for displaying comments
 *
 * @package PowerUp_Theme
 */
if ( post_password_required() ) {
  return;
}

$powerup_recent_product_reviews = array();

if ( class_exists( 'WooCommerce' ) && ! is_singular( 'product' ) ) {
  $powerup_recent_product_reviews = get_comments(
    array(
      'status'    => 'approve',
      'type'      => 'review',
      'number'    => 6,
      'post_type' => 'product',
      'orderby'   => 'comment_date_gmt',
      'order'     => 'DESC',
    )
  );
}
?>
<section id="comments" class="comments-area">
  <?php if ( have_comments() ) : ?>
    <h2 class="comments-title">
      <?php
      printf(
        esc_html( _nx( 'One comment', '%1$s comments', get_comments_number(), 'comments title', 'powerup-theme' ) ),
        number_format_i18n( get_comments_number() )
      );
      ?>
    </h2>
    <ol class="comment-list">
      <?php
      wp_list_comments( array(
        'style'      => 'ol',
        'short_ping' => true,
      ) );
      ?>
    </ol>
    <?php the_comments_navigation(); ?>
  <?php else : ?>
    <?php if ( ! empty( $powerup_recent_product_reviews ) ) : ?>
      <h2 class="comments-title"><?php esc_html_e( 'Latest Product Reviews', 'powerup-theme' ); ?></h2>
      <ol class="comment-list powerup-demo-comment-list">
        <?php foreach ( $powerup_recent_product_reviews as $review ) : ?>
          <?php $review_product_id = (int) $review->comment_post_ID; ?>
          <?php $review_video_url = (string) get_comment_meta( $review->comment_ID, 'powerup_review_video_url', true ); ?>
          <?php $review_video_mime = (string) get_comment_meta( $review->comment_ID, 'powerup_review_video_mime', true ); ?>
          <li class="comment byuser">
            <article class="comment-body">
              <footer class="comment-meta">
                <div class="comment-author vcard">
                  <b class="fn"><?php echo esc_html( $review->comment_author ); ?></b>
                </div>
                <div class="comment-metadata">
                  <time datetime="<?php echo esc_attr( mysql2date( 'c', $review->comment_date ) ); ?>"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $review->comment_date ) ); ?></time>
                </div>
              </footer>
              <div class="comment-content">
                <p><?php echo esc_html( wp_strip_all_tags( $review->comment_content ) ); ?></p>
                <?php if ( '' !== $review_video_url ) : ?>
                  <p class="powerup-comment-review-video">
                    <video controls preload="metadata" playsinline>
                      <source src="<?php echo esc_url( $review_video_url ); ?>" type="<?php echo esc_attr( $review_video_mime ); ?>">
                    </video>
                  </p>
                <?php endif; ?>
                <?php if ( $review_product_id > 0 ) : ?>
                  <p class="powerup-review-product-ref">
                    <?php
                    printf(
                      /* translators: %s: product title. */
                      esc_html__( 'Product: %s', 'powerup-theme' ),
                      esc_html( get_the_title( $review_product_id ) )
                    );
                    ?>
                  </p>
                <?php endif; ?>
              </div>
            </article>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php else : ?>
      <h2 class="comments-title"><?php esc_html_e( 'No comments yet', 'powerup-theme' ); ?></h2>
      <p><?php esc_html_e( 'Be the first to leave a product review.', 'powerup-theme' ); ?></p>
    <?php endif; ?>
  <?php endif; ?>

  <?php
  comment_form( array(
    'class_form' => 'comment-form',
    'title_reply' => esc_html__( 'Leave a Reply', 'powerup-theme' ),
  ) );
  ?>
</section>
