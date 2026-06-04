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

if ( ! function_exists( 'powerup_theme_review_has_customer_media' ) ) {
  function powerup_theme_review_has_customer_media( $comment_id ) {
    $image_ids = get_comment_meta( $comment_id, 'powerup_review_image_ids', true );
    if ( is_array( $image_ids ) && ! empty( array_filter( array_map( 'absint', $image_ids ) ) ) ) {
      return true;
    }

    $video_url = (string) get_comment_meta( $comment_id, 'powerup_review_video_url', true );
    return '' !== trim( $video_url );
  }
}

$commenter         = wp_get_current_commenter();
$rating_count      = 0;
$review_count      = 0;
$rating_total      = 0;
$average           = 0;
$ratings_breakdown = array();

for ( $star = 5; $star >= 1; $star-- ) {
  $ratings_breakdown[ $star ] = 0;
}

$approved_reviews = get_approved_comments( $product->get_id() );
$approved_product_reviews = array();
foreach ( $approved_reviews as $review_comment ) {
  if ( 'review' !== $review_comment->comment_type ) {
    continue;
  }

  $approved_product_reviews[] = $review_comment;
  $review_count++;
  $review_rating = (int) get_comment_meta( $review_comment->comment_ID, 'rating', true );
  if ( $review_rating >= 1 && $review_rating <= 5 ) {
    $ratings_breakdown[ $review_rating ]++;
    $rating_count++;
    $rating_total += $review_rating;
  }
}

usort(
  $approved_product_reviews,
  function ( $left_review, $right_review ) {
    $left_has_media  = powerup_theme_review_has_customer_media( $left_review->comment_ID );
    $right_has_media = powerup_theme_review_has_customer_media( $right_review->comment_ID );

    if ( $left_has_media !== $right_has_media ) {
      return $left_has_media ? -1 : 1;
    }

    $left_time  = strtotime( (string) $left_review->comment_date_gmt );
    $right_time = strtotime( (string) $right_review->comment_date_gmt );

    return $right_time <=> $left_time;
  }
);

if ( $rating_count > 0 ) {
  $average = $rating_total / $rating_count;
}

$reviews_per_page_options = array( 20, 50, 100 );
$reviews_per_page         = isset( $_GET['reviews_per_page'] ) ? absint( wp_unslash( $_GET['reviews_per_page'] ) ) : 20;
if ( ! in_array( $reviews_per_page, $reviews_per_page_options, true ) ) {
  $reviews_per_page = 20;
}

$total_review_pages = $review_count > 0 ? (int) ceil( $review_count / $reviews_per_page ) : 1;
$current_review_page = isset( $_GET['review_page'] ) ? absint( wp_unslash( $_GET['review_page'] ) ) : 1;
if ( $current_review_page < 1 ) {
  $current_review_page = 1;
}
if ( $current_review_page > $total_review_pages ) {
  $current_review_page = $total_review_pages;
}

$review_offset = ( $current_review_page - 1 ) * $reviews_per_page;
$visible_product_reviews = array_slice( $approved_product_reviews, $review_offset, $reviews_per_page );
$visible_review_start    = $review_count > 0 ? $review_offset + 1 : 0;
$visible_review_end      = min( $review_offset + $reviews_per_page, $review_count );

$reviews_base_url = remove_query_arg( array( 'review_page', 'reviews_per_page' ) );
$reviews_base_url = $reviews_base_url ? $reviews_base_url : get_permalink( $product->get_id() );
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

      <?php if ( ! empty( $approved_product_reviews ) ) : ?>
        <div class="powerup-amz-review-toolbar">
          <p class="powerup-amz-review-range">
            <?php
            printf(
              esc_html__( 'Showing %1$s-%2$s of %3$s reviews', 'powerup-theme' ),
              esc_html( number_format_i18n( $visible_review_start ) ),
              esc_html( number_format_i18n( $visible_review_end ) ),
              esc_html( number_format_i18n( $review_count ) )
            );
            ?>
          </p>
          <form class="powerup-amz-review-per-page" method="get" action="<?php echo esc_url( $reviews_base_url ); ?>#reviews">
            <?php foreach ( $_GET as $query_key => $query_value ) : ?>
              <?php
              if ( in_array( $query_key, array( 'review_page', 'reviews_per_page' ), true ) || is_array( $query_value ) ) {
                continue;
              }
              ?>
              <input type="hidden" name="<?php echo esc_attr( sanitize_key( $query_key ) ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $query_value ) ) ); ?>">
            <?php endforeach; ?>
            <input type="hidden" name="review_page" value="1">
            <label for="powerup-reviews-per-page"><?php esc_html_e( 'Reviews per page', 'powerup-theme' ); ?></label>
            <select id="powerup-reviews-per-page" name="reviews_per_page" onchange="this.form.submit()">
              <?php foreach ( $reviews_per_page_options as $option ) : ?>
                <option value="<?php echo esc_attr( (string) $option ); ?>" <?php selected( $reviews_per_page, $option ); ?>><?php echo esc_html( (string) $option ); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <ol class="commentlist powerup-amz-review-list">
          <?php
          wp_list_comments(
            array(
              'callback' => 'powerup_theme_amazon_review_callback',
              'style'    => 'ol',
              'type'     => 'all',
            ),
            $visible_product_reviews
          );
          ?>
        </ol>

        <?php if ( $total_review_pages > 1 ) : ?>
          <nav class="powerup-amz-review-pagination" aria-label="<?php esc_attr_e( 'Review pagination', 'powerup-theme' ); ?>">
            <?php if ( $current_review_page > 1 ) : ?>
              <a class="powerup-amz-review-page-link powerup-amz-review-page-link--prev" href="<?php echo esc_url( add_query_arg( array( 'review_page' => $current_review_page - 1, 'reviews_per_page' => $reviews_per_page ), $reviews_base_url ) . '#reviews' ); ?>"><?php esc_html_e( 'Previous', 'powerup-theme' ); ?></a>
            <?php endif; ?>

            <?php for ( $page_number = 1; $page_number <= $total_review_pages; $page_number++ ) : ?>
              <?php if ( $page_number === $current_review_page ) : ?>
                <span class="powerup-amz-review-page-link is-current" aria-current="page"><?php echo esc_html( number_format_i18n( $page_number ) ); ?></span>
              <?php else : ?>
                <a class="powerup-amz-review-page-link" href="<?php echo esc_url( add_query_arg( array( 'review_page' => $page_number, 'reviews_per_page' => $reviews_per_page ), $reviews_base_url ) . '#reviews' ); ?>"><?php echo esc_html( number_format_i18n( $page_number ) ); ?></a>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ( $current_review_page < $total_review_pages ) : ?>
              <a class="powerup-amz-review-page-link powerup-amz-review-page-link--next" href="<?php echo esc_url( add_query_arg( array( 'review_page' => $current_review_page + 1, 'reviews_per_page' => $reviews_per_page ), $reviews_base_url ) . '#reviews' ); ?>"><?php esc_html_e( 'Next', 'powerup-theme' ); ?></a>
            <?php endif; ?>
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
          'title_reply'          => ! empty( $approved_product_reviews ) ? esc_html__( 'Review this product', 'powerup-theme' ) : sprintf( esc_html__( 'Be the first to review “%s”', 'powerup-theme' ), get_the_title() ),
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
