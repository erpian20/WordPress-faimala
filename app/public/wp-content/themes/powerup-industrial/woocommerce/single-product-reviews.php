<?php
/**
 * Amazon-style review layout for single product tabs.
 *
 * @package PowerUp_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

global $product;

if ( ! comments_open() ) {
  return;
}

$commenter         = wp_get_current_commenter();
$rating_count      = $product ? $product->get_rating_count() : 0;
$review_count      = $product ? $product->get_review_count() : 0;
$average           = $product ? $product->get_average_rating() : 0;
$ratings_breakdown = array();

for ( $star = 5; $star >= 1; $star-- ) {
  $ratings_breakdown[ $star ] = 0;
}

$approved_reviews = get_approved_comments( $product->get_id() );
foreach ( $approved_reviews as $review_comment ) {
  if ( 'review' !== $review_comment->comment_type ) {
    continue;
  }

  $review_rating = (int) get_comment_meta( $review_comment->comment_ID, 'rating', true );
  if ( $review_rating >= 1 && $review_rating <= 5 ) {
    $ratings_breakdown[ $review_rating ]++;
  }
}
?>
<div id="reviews" class="woocommerce-Reviews powerup-amz-reviews-wrap">
  <div id="comments" class="powerup-amz-reviews-grid">
    <aside class="powerup-amz-summary">
      <h2><?php esc_html_e( 'Customer reviews', 'powerup-theme' ); ?></h2>
      <div class="powerup-amz-summary-rating">
        <span class="powerup-amz-summary-stars"><?php echo esc_html( powerup_theme_render_star_icons( (int) round( (float) $average ) ) ); ?></span>
        <strong><?php echo esc_html( number_format_i18n( (float) $average, 1 ) ); ?></strong>
        <span>/ 5</span>
      </div>
      <p class="powerup-amz-summary-count">
        <?php
        printf(
          esc_html( _n( '%s global rating', '%s global ratings', $rating_count, 'powerup-theme' ) ),
          esc_html( number_format_i18n( $rating_count ) )
        );
        ?>
      </p>

      <div class="powerup-amz-rating-bars">
        <?php foreach ( $ratings_breakdown as $star => $count ) : ?>
          <?php
          $percent = $review_count > 0 ? ( $count / $review_count ) * 100 : 0;
          ?>
          <div class="powerup-amz-rating-row">
            <span class="powerup-amz-rating-label"><?php echo esc_html( $star ); ?> <?php esc_html_e( 'star', 'powerup-theme' ); ?></span>
            <span class="powerup-amz-rating-track"><span style="width: <?php echo esc_attr( round( $percent, 2 ) ); ?>%;"></span></span>
            <span class="powerup-amz-rating-value"><?php echo esc_html( $count ); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </aside>

    <section class="powerup-amz-reviews-list-wrap">
      <h2 class="woocommerce-Reviews-title">
        <?php
        if ( 1 === $review_count ) {
          esc_html_e( '1 review', 'powerup-theme' );
        } else {
          printf(
            esc_html__( '%s reviews', 'powerup-theme' ),
            esc_html( number_format_i18n( $review_count ) )
          );
        }
        ?>
      </h2>

      <?php if ( have_comments() ) : ?>
        <ol class="commentlist powerup-amz-review-list">
          <?php
          wp_list_comments(
            array(
              'callback' => 'powerup_theme_amazon_review_callback',
              'style'    => 'ol',
              'type'     => 'comment',
            )
          );
          ?>
        </ol>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
          <nav class="woocommerce-pagination">
            <?php paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', array( 'prev_text' => '&larr;', 'next_text' => '&rarr;', 'type' => 'list' ) ) ); ?>
          </nav>
        <?php endif; ?>
      <?php else : ?>
        <p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'powerup-theme' ); ?></p>
      <?php endif; ?>
    </section>
  </div>

  <?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
    <div id="review_form_wrapper" class="powerup-amz-review-form-wrap">
      <div id="review_form">
        <?php
        $comment_form = array(
          'title_reply'          => have_comments() ? esc_html__( 'Review this product', 'powerup-theme' ) : sprintf( esc_html__( 'Be the first to review “%s”', 'powerup-theme' ), get_the_title() ),
          'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'powerup-theme' ),
          'title_reply_before'   => '<span id="reply-title" class="comment-reply-title">',
          'title_reply_after'    => '</span>',
          'comment_notes_after'  => '',
          'label_submit'         => esc_html__( 'Submit Review', 'powerup-theme' ),
          'logged_in_as'         => '',
          'comment_field'        => '',
        );

        $name_email_required = (bool) get_option( 'require_name_email', 1 );
        $fields              = array(
          'author' => array(
            'label'    => __( 'Name', 'powerup-theme' ),
            'type'     => 'text',
            'value'    => $commenter['comment_author'],
            'required' => $name_email_required,
          ),
          'email'  => array(
            'label'    => __( 'Email', 'powerup-theme' ),
            'type'     => 'email',
            'value'    => $commenter['comment_author_email'],
            'required' => $name_email_required,
          ),
        );

        $comment_form['fields'] = array();

        foreach ( $fields as $key => $field ) {
          $field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
          $field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );
          if ( $field['required'] ) {
            $field_html .= '&nbsp;<span class="required">*</span>';
          }
          $field_html .= '</label>';
          $field_html .= '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' />';
          $field_html .= '</p>';

          $comment_form['fields'][ $key ] = $field_html;
        }

        if ( wc_review_ratings_enabled() ) {
          $comment_form['comment_field'] .= '<p class="comment-form-rating"><label for="rating">' . esc_html__( 'Overall rating', 'powerup-theme' ) . '&nbsp;<span class="required">*</span></label><select name="rating" id="rating" required><option value="">' . esc_html__( 'Select a rating', 'powerup-theme' ) . '</option><option value="5">' . esc_html__( '5 stars - Excellent', 'powerup-theme' ) . '</option><option value="4">' . esc_html__( '4 stars - Good', 'powerup-theme' ) . '</option><option value="3">' . esc_html__( '3 stars - Average', 'powerup-theme' ) . '</option><option value="2">' . esc_html__( '2 stars - Poor', 'powerup-theme' ) . '</option><option value="1">' . esc_html__( '1 star - Bad', 'powerup-theme' ) . '</option></select></p>';
        }

        $comment_form['comment_field'] .= '<p class="comment-form-powerup-review-video"><label for="powerup_review_video">' . esc_html__( 'Review Video (Optional)', 'powerup-theme' ) . '</label><input id="powerup_review_video" name="powerup_review_video" type="file" accept="video/mp4,video/webm,video/ogg,video/quicktime"><small>' . esc_html__( 'Supported formats: MP4, WebM, OGV, MOV.', 'powerup-theme' ) . '</small></p>';
        $comment_form['comment_field'] .= wp_nonce_field( 'powerup_review_video_upload', 'powerup_review_video_nonce', true, false );

        $comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Write your review', 'powerup-theme' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

        comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
        ?>
      </div>
    </div>
  <?php else : ?>
    <p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'powerup-theme' ); ?></p>
  <?php endif; ?>

  <div class="clear"></div>
</div>
