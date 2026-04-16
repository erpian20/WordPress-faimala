<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Core {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'plugins_loaded', array( $this, 'bootstrap' ), 20 );
    add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
  }

  public static function activate() {
    if ( ! wp_next_scheduled( 'powerup_b2c_retry_failed_pushes' ) ) {
      wp_schedule_event( time() + 120, 'powerup_five_minutes', 'powerup_b2c_retry_failed_pushes' );
    }

    if ( ! wp_next_scheduled( 'powerup_b2c_refresh_exchange_rates' ) ) {
      wp_schedule_event( time() + 300, 'daily', 'powerup_b2c_refresh_exchange_rates' );
    }
  }

  public static function deactivate() {
    wp_clear_scheduled_hook( 'powerup_b2c_retry_failed_pushes' );
    wp_clear_scheduled_hook( 'powerup_b2c_refresh_exchange_rates' );
  }

  public function add_cron_interval( $schedules ) {
    if ( ! isset( $schedules['powerup_five_minutes'] ) ) {
      $schedules['powerup_five_minutes'] = array(
        'interval' => 300,
        'display'  => __( 'Every 5 Minutes', 'powerup-b2c' ),
      );
    }

    return $schedules;
  }

  public function bootstrap() {
    if ( ! class_exists( 'WooCommerce' ) ) {
      add_action( 'admin_notices', array( $this, 'woocommerce_required_notice' ) );
      return;
    }

    PowerUp_B2C_Settings::instance();
    PowerUp_B2C_Currency::instance();
    PowerUp_B2C_Forms::instance();
    PowerUp_B2C_Checkout::instance();
    PowerUp_B2C_SEO::instance();
    PowerUp_B2C_Shipping::instance();
    PowerUp_B2C_ERP::instance();
    PowerUp_B2C_Marketplace::instance();
    PowerUp_B2C_PDP_Gallery::instance();
    PowerUp_B2C_Dashboard::instance();

    if ( ! wp_next_scheduled( 'powerup_b2c_retry_failed_pushes' ) ) {
      wp_schedule_event( time() + 120, 'powerup_five_minutes', 'powerup_b2c_retry_failed_pushes' );
    }

    if ( ! wp_next_scheduled( 'powerup_b2c_refresh_exchange_rates' ) ) {
      wp_schedule_event( time() + 300, 'daily', 'powerup_b2c_refresh_exchange_rates' );
    }
  }

  public function woocommerce_required_notice() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
      return;
    }

    echo '<div class="notice notice-error"><p>' . esc_html__( 'PowerUp B2C Suite requires WooCommerce to be activated.', 'powerup-b2c' ) . '</p></div>';
  }
}
