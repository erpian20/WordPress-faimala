<?php
/**
 * Template Name: Contact Us Page
 *
 * @package PowerUp_Theme
 */
get_header();

$whatsapp_number = (string) powerup_theme_get_config_value( 'contact.whatsapp_number', '' );
$whatsapp_qr_url = (string) powerup_theme_get_config_value( 'contact.whatsapp_qr_image_url', '' );
$support_emails = function_exists( 'powerup_theme_get_support_email_recipients' )
  ? powerup_theme_get_support_email_recipients()
  : array( (string) powerup_theme_get_config_value( 'contact.support_email', 'randian5757@gmail.com' ) );
$support_hours = powerup_theme_get_config_value( 'contact.support_hours', '9:00 - 18:00' );

$whatsapp_digits = preg_replace( '/\D+/', '', $whatsapp_number );
$whatsapp_chat_url = '';
if ( '' !== $whatsapp_digits ) {
  $whatsapp_chat_url = 'https://wa.me/' . $whatsapp_digits;
}

if ( '' === $whatsapp_qr_url ) {
  $whatsapp_qr_url = get_template_directory_uri() . '/assets/images/whatsapp-qr.jpg';
}
?>
<main class="site-section">
  <div class="site-inner">
    <div class="section-heading">
      <h1><?php esc_html_e( 'Contact Us', 'powerup-theme' ); ?></h1>
      <p><?php esc_html_e( 'Send us a message for product inquiries, support or cooperation.', 'powerup-theme' ); ?></p>
    </div>
    <div class="section-grid contact-legacy-grid">
      <form class="feature-card contact-legacy-card" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'contact' ); } ?>
        <label for="contact-name"><?php esc_html_e( 'Full Name', 'powerup-theme' ); ?></label>
        <input id="contact-name" class="contact-legacy-input" type="text" name="contact_name" required>
        <label for="contact-email"><?php esc_html_e( 'Email Address', 'powerup-theme' ); ?></label>
        <input id="contact-email" class="contact-legacy-input" type="email" name="contact_email" required>
        <label for="contact-order"><?php esc_html_e( 'Order Number (Optional)', 'powerup-theme' ); ?></label>
        <input id="contact-order" class="contact-legacy-input" type="text" name="contact_order">
        <label for="contact-message"><?php esc_html_e( 'Your Message', 'powerup-theme' ); ?></label>
        <textarea id="contact-message" class="contact-legacy-input contact-legacy-textarea" name="contact_message" rows="8" required></textarea>
        <?php wp_nonce_field( 'powerup_contact_submit', 'powerup_contact_nonce' ); ?>
        <input type="hidden" name="action" value="powerup_contact_submit">
        <button class="btn btn-primary" type="submit"><?php esc_html_e( 'Send Message', 'powerup-theme' ); ?></button>
      </form>
      <aside class="feature-card contact-legacy-card">
        <h2><?php esc_html_e( 'Live Chat Support', 'powerup-theme' ); ?></h2>
        <p><?php esc_html_e( 'Scan the WhatsApp QR code for instant customer support. We currently handle support via chat tools only.', 'powerup-theme' ); ?></p>
        <p class="whatsapp-qr-label"><span class="whatsapp-icon" aria-hidden="true">&#128172;</span> <?php esc_html_e( 'WhatsApp', 'powerup-theme' ); ?></p>
        <div class="contact-whatsapp-qr-wrap">
          <img class="contact-whatsapp-qr" src="<?php echo esc_url( $whatsapp_qr_url ); ?>" alt="<?php esc_attr_e( 'WhatsApp support QR code', 'powerup-theme' ); ?>" loading="lazy">
        </div>
        <?php if ( '' !== $whatsapp_chat_url ) : ?>
          <p><a class="btn btn-secondary contact-whatsapp-btn" href="<?php echo esc_url( $whatsapp_chat_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open WhatsApp Chat', 'powerup-theme' ); ?></a></p>
        <?php endif; ?>
        <p><span class="email-icon" aria-hidden="true">&#9993;</span> <strong><?php esc_html_e( 'Email', 'powerup-theme' ); ?>:</strong>
          <?php if ( ! empty( $support_emails ) ) : ?>
            <?php foreach ( $support_emails as $index => $support_email ) : ?>
              <?php if ( $index > 0 ) : ?> / <?php endif; ?>
              <a href="mailto:<?php echo esc_attr( (string) $support_email ); ?>"><?php echo esc_html( (string) $support_email ); ?></a>
            <?php endforeach; ?>
          <?php endif; ?>
        </p>
        <p><strong><?php esc_html_e( 'Hours', 'powerup-theme' ); ?>:</strong> <?php echo esc_html( $support_hours ); ?></p>
      </aside>
    </div>
  </div>
</main>
<?php get_footer(); ?>
