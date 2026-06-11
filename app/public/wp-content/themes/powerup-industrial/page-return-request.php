<?php
/**
 * Template Name: Return Request Page
 *
 * @package PowerUp_Theme
 */
get_header();

$support_emails = function_exists( 'powerup_theme_get_support_email_recipients' )
  ? powerup_theme_get_support_email_recipients()
  : array( (string) powerup_theme_get_config_value( 'contact.support_email', 'randian5757@gmail.com' ) );
$support_hours = powerup_theme_get_config_value( 'contact.support_hours', '24/7 Customer Support' );
?>
<main class="site-section">
  <div class="site-inner">
    <div class="section-heading">
      <h1><?php esc_html_e( 'Return Request', 'powerup-theme' ); ?></h1>
      <p><?php esc_html_e( 'Start a return within 30 days of delivery. Submit your order details and our support team will send the next steps.', 'powerup-theme' ); ?></p>
    </div>
    <div class="section-grid contact-legacy-grid return-request-grid">
      <form class="feature-card contact-legacy-card return-request-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'return_request' ); } ?>
        <label for="return-name"><?php esc_html_e( 'Full Name', 'powerup-theme' ); ?></label>
        <input id="return-name" class="contact-legacy-input" type="text" name="return_name" required>

        <label for="return-email"><?php esc_html_e( 'Email Address', 'powerup-theme' ); ?></label>
        <input id="return-email" class="contact-legacy-input" type="email" name="return_email" required>

        <label for="return-order"><?php esc_html_e( 'Order Number', 'powerup-theme' ); ?></label>
        <input id="return-order" class="contact-legacy-input" type="text" name="return_order" required>

        <label for="return-product"><?php esc_html_e( 'Product Name', 'powerup-theme' ); ?></label>
        <input id="return-product" class="contact-legacy-input" type="text" name="return_product" placeholder="<?php esc_attr_e( 'Example: 12 Inch 20V Cordless Electric Chainsaw Kit', 'powerup-theme' ); ?>">

        <label for="return-reason"><?php esc_html_e( 'Return Reason', 'powerup-theme' ); ?></label>
        <select id="return-reason" class="contact-legacy-input" name="return_reason" required>
          <option value=""><?php esc_html_e( 'Select a reason', 'powerup-theme' ); ?></option>
          <option value="Damaged or defective item"><?php esc_html_e( 'Damaged or defective item', 'powerup-theme' ); ?></option>
          <option value="Wrong item received"><?php esc_html_e( 'Wrong item received', 'powerup-theme' ); ?></option>
          <option value="Battery or platform fit question"><?php esc_html_e( 'Battery or platform fit question', 'powerup-theme' ); ?></option>
          <option value="Changed my mind"><?php esc_html_e( 'Changed my mind', 'powerup-theme' ); ?></option>
          <option value="Other"><?php esc_html_e( 'Other', 'powerup-theme' ); ?></option>
        </select>

        <label for="return-message"><?php esc_html_e( 'Return Details', 'powerup-theme' ); ?></label>
        <textarea id="return-message" class="contact-legacy-input contact-legacy-textarea" name="return_message" rows="8" placeholder="<?php esc_attr_e( 'Tell us what happened and whether the item has been opened or used.', 'powerup-theme' ); ?>" required></textarea>

        <?php wp_nonce_field( 'powerup_return_request_submit', 'powerup_return_request_nonce' ); ?>
        <input type="hidden" name="action" value="powerup_return_request_submit">
        <button class="btn btn-primary" type="submit"><?php esc_html_e( 'Submit Return Request', 'powerup-theme' ); ?></button>
      </form>
      <aside class="feature-card contact-legacy-card return-request-card">
        <h2><?php esc_html_e( 'Return Policy Summary', 'powerup-theme' ); ?></h2>
        <ul class="return-request-list">
          <li><?php esc_html_e( 'Return requests are accepted within 30 days of delivery.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Return shipping is covered by us after the request is approved.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'Please keep the product, included parts, and packaging until support confirms the next step.', 'powerup-theme' ); ?></li>
          <li><?php esc_html_e( 'For damaged, incorrect, or incomplete items, describe the issue clearly. Photos or video can be sent by replying to our support email.', 'powerup-theme' ); ?></li>
        </ul>
        <h2><?php esc_html_e( 'Need Help?', 'powerup-theme' ); ?></h2>
        <p><strong><?php esc_html_e( 'Hours', 'powerup-theme' ); ?>:</strong> <?php echo esc_html( $support_hours ); ?></p>
        <p><span class="email-icon" aria-hidden="true">&#9993;</span> <strong><?php esc_html_e( 'Email', 'powerup-theme' ); ?>:</strong>
          <?php if ( ! empty( $support_emails ) ) : ?>
            <?php foreach ( $support_emails as $index => $support_email ) : ?>
              <?php if ( $index > 0 ) : ?> / <?php endif; ?>
              <a href="mailto:<?php echo esc_attr( (string) $support_email ); ?>"><?php echo esc_html( (string) $support_email ); ?></a>
            <?php endforeach; ?>
          <?php endif; ?>
        </p>
      </aside>
    </div>
  </div>
</main>
<?php get_footer(); ?>
