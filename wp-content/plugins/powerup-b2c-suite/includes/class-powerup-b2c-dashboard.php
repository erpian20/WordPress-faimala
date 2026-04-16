<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Dashboard {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'admin_menu', array( $this, 'add_menu' ), 35 );
    add_action( 'admin_post_powerup_b2c_refresh_rates_now', array( $this, 'handle_manual_rate_refresh' ) );
  }

  public function add_menu() {
    add_submenu_page(
      'woocommerce',
      __( 'B2C Ops Dashboard', 'powerup-b2c' ),
      __( 'B2C Ops Dashboard', 'powerup-b2c' ),
      'manage_woocommerce',
      'powerup-b2c-ops-dashboard',
      array( $this, 'render_page' )
    );
  }

  public function render_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
      return;
    }

    $queue = get_option( PowerUp_B2C_ERP::RETRY_OPTION, array() );
    $queue_count = is_array( $queue ) ? count( $queue ) : 0;
    $last_rate_sync = get_option( 'powerup_b2c_rates_last_sync', '' );
    $recent_notes = $this->get_recent_erp_order_notes();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'B2C Ops Dashboard', 'powerup-b2c' ) . '</h1>';

    echo '<table class="widefat striped" style="max-width:900px;margin:16px 0;">';
    echo '<tbody>';
    echo '<tr><td style="width:260px;"><strong>' . esc_html__( 'ERP Retry Queue', 'powerup-b2c' ) . '</strong></td><td>' . esc_html( (string) $queue_count ) . '</td></tr>';
    echo '<tr><td><strong>' . esc_html__( 'Last Exchange Sync (UTC)', 'powerup-b2c' ) . '</strong></td><td>' . esc_html( $last_rate_sync ? $last_rate_sync : '-' ) . '</td></tr>';
    echo '</tbody>';
    echo '</table>';

    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:8px 0 24px;">';
    wp_nonce_field( 'powerup_b2c_refresh_rates_now', 'powerup_b2c_refresh_rates_nonce' );
    echo '<input type="hidden" name="action" value="powerup_b2c_refresh_rates_now" />';
    echo '<button type="submit" class="button button-primary">' . esc_html__( 'Refresh Exchange Rates Now', 'powerup-b2c' ) . '</button>';
    echo '</form>';

    echo '<h2>' . esc_html__( 'Recent ERP Push Notes', 'powerup-b2c' ) . '</h2>';

    if ( empty( $recent_notes ) ) {
      echo '<p>' . esc_html__( 'No ERP notes found yet.', 'powerup-b2c' ) . '</p>';
    } else {
      echo '<table class="widefat striped" style="max-width:1200px;">';
      echo '<thead><tr><th style="width:110px;">' . esc_html__( 'Order', 'powerup-b2c' ) . '</th><th style="width:190px;">' . esc_html__( 'Time (UTC)', 'powerup-b2c' ) . '</th><th>' . esc_html__( 'Note', 'powerup-b2c' ) . '</th></tr></thead><tbody>';

      foreach ( $recent_notes as $note ) {
        $order_id = (int) $note->comment_post_ID;
        $order_link = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        echo '<tr>';
        echo '<td><a href="' . esc_url( $order_link ) . '">#' . esc_html( (string) $order_id ) . '</a></td>';
        echo '<td>' . esc_html( get_gmt_from_date( $note->comment_date, 'Y-m-d H:i:s' ) ) . '</td>';
        echo '<td>' . esc_html( wp_trim_words( wp_strip_all_tags( $note->comment_content ), 28, '...' ) ) . '</td>';
        echo '</tr>';
      }

      echo '</tbody></table>';
    }

    echo '</div>';
  }

  public function handle_manual_rate_refresh() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
      wp_die( esc_html__( 'Access denied.', 'powerup-b2c' ), 403 );
    }

    check_admin_referer( 'powerup_b2c_refresh_rates_now', 'powerup_b2c_refresh_rates_nonce' );

    PowerUp_B2C_Currency::instance()->refresh_exchange_rates();

    wp_safe_redirect( admin_url( 'admin.php?page=powerup-b2c-ops-dashboard' ) );
    exit;
  }

  private function get_recent_erp_order_notes() {
    return get_comments(
      array(
        'number'      => 12,
        'status'      => 'approve',
        'type'        => 'order_note',
        'post_type'   => 'shop_order',
        'search'      => 'ERP',
        'orderby'     => 'comment_date_gmt',
        'order'       => 'DESC',
      )
    );
  }
}
