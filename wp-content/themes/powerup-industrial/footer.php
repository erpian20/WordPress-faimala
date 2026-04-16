<?php
/**
 * The template for displaying the footer
 *
 * @package PowerUp_Theme
 */

$support_emails = function_exists( 'powerup_theme_get_support_email_recipients' )
  ? powerup_theme_get_support_email_recipients()
  : array( (string) powerup_theme_get_config_value( 'contact.support_email', 'randian5757@gmail.com' ) );
$whatsapp_number = (string) powerup_theme_get_config_value( 'contact.whatsapp_number', '' );
$whatsapp_qr_url = (string) powerup_theme_get_config_value( 'contact.whatsapp_qr_image_url', '' );

$whatsapp_digits = preg_replace( '/\D+/', '', $whatsapp_number );
$whatsapp_chat_url = '';
if ( '' !== $whatsapp_digits ) {
  $whatsapp_chat_url = 'https://wa.me/' . $whatsapp_digits;
}

if ( '' === $whatsapp_qr_url ) {
  $whatsapp_qr_url = get_template_directory_uri() . '/assets/images/whatsapp-qr-placeholder.svg';
}
?>
    </div><!-- #content -->
  <footer class="site-footer">
    <div class="site-inner">
      <div class="section-heading">
        <h2><?php esc_html_e( 'Subscribe to our newsletter', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Get the latest deals & updates for your outdoor power tools.', 'powerup-theme' ); ?></p>
      </div>
      <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'subscribe', 'is-inline-dark' ); } ?>
      <form class="newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <label class="screen-reader-text" for="newsletter-email"><?php esc_html_e( 'Email Address', 'powerup-theme' ); ?></label>
          <?php wp_nonce_field( 'powerup_subscribe_submit', 'powerup_subscribe_nonce' ); ?>
          <input type="email" id="newsletter-email" name="subscriber_email" placeholder="<?php esc_attr_e( 'Enter your email address', 'powerup-theme' ); ?>" required>
        <input type="hidden" name="action" value="powerup_subscribe">
        <button type="submit" class="btn-primary"><?php esc_html_e( 'Subscribe', 'powerup-theme' ); ?></button>
      </form>
      <div class="footer-support-box" aria-label="<?php esc_attr_e( 'After-sales support', 'powerup-theme' ); ?>">
        <div class="footer-support-copy">
          <h3><?php esc_html_e( 'After-sales Support', 'powerup-theme' ); ?></h3>
          <p><?php esc_html_e( 'We provide customer service via instant chat tools.', 'powerup-theme' ); ?></p>
          <p><span class="email-icon" aria-hidden="true">&#9993;</span> <strong><?php esc_html_e( 'Email', 'powerup-theme' ); ?>:</strong>
            <?php if ( ! empty( $support_emails ) ) : ?>
              <?php foreach ( $support_emails as $index => $support_email ) : ?>
                <?php if ( $index > 0 ) : ?> / <?php endif; ?>
                <a href="mailto:<?php echo esc_attr( (string) $support_email ); ?>"><?php echo esc_html( (string) $support_email ); ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </p>
          <?php if ( '' !== $whatsapp_chat_url ) : ?>
            <p><a class="btn btn-secondary footer-whatsapp-btn" href="<?php echo esc_url( $whatsapp_chat_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WhatsApp Live Chat', 'powerup-theme' ); ?></a></p>
          <?php endif; ?>
        </div>
        <div class="footer-support-qr-wrap">
          <p class="whatsapp-qr-label"><span class="whatsapp-icon" aria-hidden="true">&#128172;</span> <?php esc_html_e( 'WhatsApp', 'powerup-theme' ); ?></p>
          <img class="footer-support-qr" src="<?php echo esc_url( $whatsapp_qr_url ); ?>" alt="<?php esc_attr_e( 'WhatsApp support QR code', 'powerup-theme' ); ?>" loading="lazy">
        </div>
      </div>
      <p style="margin-top: 32px; color: rgba(255,255,255,.6);">&copy; <?php echo date_i18n( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'powerup-theme' ); ?></p>
    </div>
  </footer>
  </div><!-- #page -->
  <?php wp_footer(); ?>
</body>
</html>
