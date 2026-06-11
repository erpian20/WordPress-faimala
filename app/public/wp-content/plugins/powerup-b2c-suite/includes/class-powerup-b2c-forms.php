<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Forms {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'admin_post_nopriv_powerup_contact_submit', array( $this, 'handle_contact_submit' ) );
    add_action( 'admin_post_powerup_contact_submit', array( $this, 'handle_contact_submit' ) );
    add_action( 'admin_post_nopriv_powerup_return_request_submit', array( $this, 'handle_return_request_submit' ) );
    add_action( 'admin_post_powerup_return_request_submit', array( $this, 'handle_return_request_submit' ) );
    add_action( 'admin_post_nopriv_powerup_subscribe', array( $this, 'handle_subscribe_submit' ) );
    add_action( 'admin_post_powerup_subscribe', array( $this, 'handle_subscribe_submit' ) );
  }

  public function handle_contact_submit() {
    $nonce = isset( $_POST['powerup_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_contact_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'powerup_contact_submit' ) ) {
      wp_safe_redirect( add_query_arg( 'contact', 'invalid', wp_get_referer() ? wp_get_referer() : home_url( '/contact-us/' ) ) );
      exit;
    }

    $name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
    $email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
    $order   = isset( $_POST['contact_order'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_order'] ) ) : '';
    $message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

    if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
      wp_safe_redirect( add_query_arg( 'contact', 'invalid', wp_get_referer() ? wp_get_referer() : home_url( '/contact-us/' ) ) );
      exit;
    }

    $admin_email = get_option( 'admin_email' );
    $subject     = sprintf( '[PowerUp] Contact Form - %s', $name );
    $body        = "Name: {$name}\nEmail: {$email}\nOrder: {$order}\n\nMessage:\n{$message}";
    $headers     = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

    wp_mail( $admin_email, $subject, $body, $headers );

    wp_safe_redirect( add_query_arg( 'contact', 'sent', wp_get_referer() ? wp_get_referer() : home_url( '/contact-us/' ) ) );
    exit;
  }

  public function handle_return_request_submit() {
    $fallback_url = home_url( '/return-request/' );
    $redirect_url = wp_get_referer() ? wp_get_referer() : $fallback_url;
    $nonce        = isset( $_POST['powerup_return_request_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_return_request_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'powerup_return_request_submit' ) ) {
      wp_safe_redirect( add_query_arg( 'return_request', 'invalid', $redirect_url ) );
      exit;
    }

    $name    = isset( $_POST['return_name'] ) ? sanitize_text_field( wp_unslash( $_POST['return_name'] ) ) : '';
    $email   = isset( $_POST['return_email'] ) ? sanitize_email( wp_unslash( $_POST['return_email'] ) ) : '';
    $order   = isset( $_POST['return_order'] ) ? sanitize_text_field( wp_unslash( $_POST['return_order'] ) ) : '';
    $product = isset( $_POST['return_product'] ) ? sanitize_text_field( wp_unslash( $_POST['return_product'] ) ) : '';
    $reason  = isset( $_POST['return_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['return_reason'] ) ) : '';
    $message = isset( $_POST['return_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['return_message'] ) ) : '';

    if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $order ) || empty( $reason ) || empty( $message ) ) {
      wp_safe_redirect( add_query_arg( 'return_request', 'missing', $redirect_url ) );
      exit;
    }

    $recipients = array( get_option( 'admin_email' ) );
    if ( function_exists( 'powerup_theme_get_support_email_recipients' ) ) {
      $support_recipients = powerup_theme_get_support_email_recipients();
      if ( is_array( $support_recipients ) && ! empty( $support_recipients ) ) {
        $recipients = $support_recipients;
      }
    }

    $subject = sprintf( '[PowerUp] Return Request - Order %s', $order );
    $body    = "Return request submitted from " . home_url( '/' ) . "\n\n";
    $body   .= "Name: {$name}\n";
    $body   .= "Email: {$email}\n";
    $body   .= "Order Number: {$order}\n";
    $body   .= "Product: {$product}\n";
    $body   .= "Reason: {$reason}\n\n";
    $body   .= "Details:\n{$message}\n";
    $headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

    $sent = wp_mail( $recipients, $subject, $body, $headers );

    wp_safe_redirect( add_query_arg( 'return_request', $sent ? 'sent' : 'failed', $redirect_url ) );
    exit;
  }

  public function handle_subscribe_submit() {
    $nonce = isset( $_POST['powerup_subscribe_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_subscribe_nonce'] ) ) : '';

    if ( ! wp_verify_nonce( $nonce, 'powerup_subscribe_submit' ) ) {
      wp_safe_redirect( add_query_arg( 'subscribe', 'invalid', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
      exit;
    }

    $email = isset( $_POST['subscriber_email'] ) ? sanitize_email( wp_unslash( $_POST['subscriber_email'] ) ) : '';

    if ( empty( $email ) || ! is_email( $email ) ) {
      wp_safe_redirect( add_query_arg( 'subscribe', 'invalid', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
      exit;
    }

    $list = get_option( 'powerup_subscribers', array() );
    if ( ! is_array( $list ) ) {
      $list = array();
    }

    if ( ! in_array( $email, $list, true ) ) {
      $list[] = $email;
      update_option( 'powerup_subscribers', $list, false );
    }

    wp_safe_redirect( add_query_arg( 'subscribe', 'success', wp_get_referer() ? wp_get_referer() : home_url( '/' ) ) );
    exit;
  }

  public static function get_form_notice( $type ) {
    $status = isset( $_GET[ $type ] ) ? sanitize_key( wp_unslash( $_GET[ $type ] ) ) : '';

    if ( 'contact' === $type ) {
      if ( 'sent' === $status ) {
        return array(
          'class'   => 'is-success',
          'message' => __( 'Message sent successfully. Our team will contact you soon.', 'powerup-theme' ),
        );
      }

      if ( 'invalid' === $status ) {
        return array(
          'class'   => 'is-error',
          'message' => __( 'Please complete name, email, and message before submitting.', 'powerup-theme' ),
        );
      }
    }

    if ( 'subscribe' === $type ) {
      if ( 'success' === $status ) {
        return array(
          'class'   => 'is-success',
          'message' => __( 'Subscription successful. Thank you for joining our newsletter.', 'powerup-theme' ),
        );
      }

      if ( 'invalid' === $status ) {
        return array(
          'class'   => 'is-error',
          'message' => __( 'Please enter a valid email address.', 'powerup-theme' ),
        );
      }
    }

    if ( 'return_request' === $type ) {
      if ( 'sent' === $status ) {
        return array(
          'class'   => 'is-success',
          'message' => __( 'Return request received. Our support team will review it and send the next steps.', 'powerup-theme' ),
        );
      }

      if ( 'missing' === $status ) {
        return array(
          'class'   => 'is-error',
          'message' => __( 'Please complete all required return request fields before submitting.', 'powerup-theme' ),
        );
      }

      if ( 'invalid' === $status ) {
        return array(
          'class'   => 'is-error',
          'message' => __( 'The return request could not be verified. Please refresh the page and try again.', 'powerup-theme' ),
        );
      }

      if ( 'failed' === $status ) {
        return array(
          'class'   => 'is-error',
          'message' => __( 'We could not send the return request right now. Please contact support by email.', 'powerup-theme' ),
        );
      }
    }

    return null;
  }

  public static function render_form_notice( $type, $extra_class = '' ) {
    $notice = self::get_form_notice( $type );

    if ( ! $notice ) {
      return;
    }

    $classes = trim( 'powerup-form-notice ' . $notice['class'] . ' ' . $extra_class );
    echo '<div class="' . esc_attr( $classes ) . '">' . esc_html( $notice['message'] ) . '</div>';
  }
}

if ( ! function_exists( 'powerup_get_form_notice' ) ) {
  function powerup_get_form_notice( $type ) {
    return PowerUp_B2C_Forms::get_form_notice( $type );
  }
}

if ( ! function_exists( 'powerup_render_form_notice' ) ) {
  function powerup_render_form_notice( $type, $extra_class = '' ) {
    PowerUp_B2C_Forms::render_form_notice( $type, $extra_class );
  }
}
