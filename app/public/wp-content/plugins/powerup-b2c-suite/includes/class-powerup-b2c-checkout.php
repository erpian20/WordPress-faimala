<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Checkout {
  private static $instance = null;
  const LOCK_OPTION = 'powerup_b2c_stock_locks';

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'woocommerce_after_order_notes', array( $this, 'add_vat_ioss_fields' ) );
    add_action( 'woocommerce_checkout_process', array( $this, 'validate_vat_ioss_fields' ) );
    add_action( 'woocommerce_checkout_create_order', array( $this, 'save_vat_ioss_fields' ), 20, 2 );
    add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'render_admin_vat_ioss' ) );

    add_action( 'init', array( $this, 'add_account_endpoint' ) );
    add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_item' ) );
    add_action( 'woocommerce_account_tracking-center_endpoint', array( $this, 'render_tracking_center' ) );

    add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_stock_lock' ), 20, 2 );
    add_action( 'woocommerce_checkout_order_processed', array( $this, 'reserve_stock_lock' ), 20, 3 );
    add_action( 'woocommerce_order_status_changed', array( $this, 'release_stock_lock_by_status' ), 20, 4 );
  }

  public function add_vat_ioss_fields( $checkout ) {
    echo '<div id="powerup-vat-ioss"><h3>' . esc_html__( 'Tax & Customs (EU)', 'powerup-b2c' ) . '</h3>';

    woocommerce_form_field(
      'powerup_vat_number',
      array(
        'type'        => 'text',
        'label'       => __( 'VAT Number', 'powerup-b2c' ),
        'required'    => false,
        'class'       => array( 'form-row-wide' ),
        'placeholder' => 'EU123456789',
      ),
      $checkout->get_value( 'powerup_vat_number' )
    );

    woocommerce_form_field(
      'powerup_ioss_number',
      array(
        'type'        => 'text',
        'label'       => __( 'IOSS Number', 'powerup-b2c' ),
        'required'    => false,
        'class'       => array( 'form-row-wide' ),
        'placeholder' => 'IM1234567890',
      ),
      $checkout->get_value( 'powerup_ioss_number' )
    );

    echo '</div>';
  }

  public function validate_vat_ioss_fields() {
    $vat  = isset( $_POST['powerup_vat_number'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['powerup_vat_number'] ) ) ) ) : '';
    $ioss = isset( $_POST['powerup_ioss_number'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['powerup_ioss_number'] ) ) ) ) : '';

    if ( '' !== $vat && ! preg_match( '/^[A-Z0-9\-]{6,20}$/', $vat ) ) {
      wc_add_notice( __( 'VAT Number format is invalid.', 'powerup-b2c' ), 'error' );
    }

    if ( '' !== $ioss && ! preg_match( '/^[A-Z0-9\-]{8,20}$/', $ioss ) ) {
      wc_add_notice( __( 'IOSS Number format is invalid.', 'powerup-b2c' ), 'error' );
    }
  }

  public function save_vat_ioss_fields( $order, $data ) {
    if ( isset( $_POST['powerup_vat_number'] ) ) {
      $order->update_meta_data( '_powerup_vat_number', sanitize_text_field( wp_unslash( $_POST['powerup_vat_number'] ) ) );
    }

    if ( isset( $_POST['powerup_ioss_number'] ) ) {
      $order->update_meta_data( '_powerup_ioss_number', sanitize_text_field( wp_unslash( $_POST['powerup_ioss_number'] ) ) );
    }
  }

  public function render_admin_vat_ioss( $order ) {
    $vat  = $order->get_meta( '_powerup_vat_number', true );
    $ioss = $order->get_meta( '_powerup_ioss_number', true );

    if ( $vat ) {
      echo '<p><strong>' . esc_html__( 'VAT Number:', 'powerup-b2c' ) . '</strong> ' . esc_html( $vat ) . '</p>';
    }

    if ( $ioss ) {
      echo '<p><strong>' . esc_html__( 'IOSS Number:', 'powerup-b2c' ) . '</strong> ' . esc_html( $ioss ) . '</p>';
    }
  }

  public function add_account_endpoint() {
    add_rewrite_endpoint( 'tracking-center', EP_ROOT | EP_PAGES );
  }

  public function add_account_menu_item( $items ) {
    $logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : '';
    unset( $items['customer-logout'] );

    $items['tracking-center'] = __( 'Order Tracking', 'powerup-b2c' );

    if ( $logout ) {
      $items['customer-logout'] = $logout;
    }

    return $items;
  }

  public function render_tracking_center() {
    $orders = wc_get_orders(
      array(
        'customer' => get_current_user_id(),
        'limit'    => 20,
        'orderby'  => 'date',
        'order'    => 'DESC',
      )
    );

    echo '<h3>' . esc_html__( 'Tracking Center', 'powerup-b2c' ) . '</h3>';

    if ( empty( $orders ) ) {
      echo '<p>' . esc_html__( 'No orders found.', 'powerup-b2c' ) . '</p>';
      return;
    }

    echo '<table class="shop_table shop_table_responsive my_account_orders"><thead><tr><th>' . esc_html__( 'Order', 'powerup-b2c' ) . '</th><th>' . esc_html__( 'Status', 'powerup-b2c' ) . '</th><th>' . esc_html__( 'Tracking', 'powerup-b2c' ) . '</th><th>' . esc_html__( 'Carrier', 'powerup-b2c' ) . '</th></tr></thead><tbody>';

    foreach ( $orders as $order ) {
      $tracking_number = $order->get_meta( '_powerup_tracking_number', true );
      $tracking_carrier = $order->get_meta( '_powerup_tracking_carrier', true );

      echo '<tr>';
      echo '<td>#' . esc_html( $order->get_order_number() ) . '</td>';
      echo '<td>' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</td>';
      echo '<td>' . esc_html( $tracking_number ? $tracking_number : '-' ) . '</td>';
      echo '<td>' . esc_html( $tracking_carrier ? $tracking_carrier : '-' ) . '</td>';
      echo '</tr>';
    }

    echo '</tbody></table>';
  }

  public function validate_stock_lock( $data, $errors ) {
    if ( ! WC()->cart ) {
      return;
    }

    $locks = $this->get_locks();

    foreach ( WC()->cart->get_cart() as $cart_item ) {
      $product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
      if ( ! $product instanceof WC_Product || ! $product->managing_stock() ) {
        continue;
      }

      $product_id = $product->get_id();
      $qty_in_cart = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;
      $locked_qty = $this->get_locked_qty_for_product( $locks, $product_id );
      $stock_qty = (int) $product->get_stock_quantity();
      $available = $stock_qty - $locked_qty;

      if ( $available < $qty_in_cart ) {
        $errors->add( 'stock_lock_insufficient', sprintf( __( '%s stock is limited, please reduce quantity.', 'powerup-b2c' ), $product->get_name() ) );
      }
    }
  }

  public function reserve_stock_lock( $order_id, $posted_data, $order ) {
    if ( ! $order instanceof WC_Order ) {
      return;
    }

    $locks = $this->get_locks();
    $now   = time();

    foreach ( $order->get_items() as $item ) {
      if ( ! $item instanceof WC_Order_Item_Product ) {
        continue;
      }

      $product = $item->get_product();
      if ( ! $product instanceof WC_Product || ! $product->managing_stock() ) {
        continue;
      }

      $product_id = $product->get_id();
      $qty        = (int) $item->get_quantity();

      if ( ! isset( $locks[ $product_id ] ) || ! is_array( $locks[ $product_id ] ) ) {
        $locks[ $product_id ] = array();
      }

      $locks[ $product_id ][ $order_id ] = array(
        'qty' => $qty,
        'expires' => $now + ( 15 * MINUTE_IN_SECONDS ),
      );
    }

    update_option( self::LOCK_OPTION, $locks, false );
    $order->update_meta_data( '_powerup_stock_locked', 1 );
    $order->save();
  }

  public function release_stock_lock_by_status( $order_id, $from_status, $to_status, $order ) {
    if ( ! $order instanceof WC_Order ) {
      return;
    }

    $release_statuses = array( 'processing', 'completed', 'cancelled', 'failed', 'refunded' );
    if ( ! in_array( $to_status, $release_statuses, true ) ) {
      return;
    }

    $locks = $this->get_locks();

    foreach ( $order->get_items() as $item ) {
      if ( ! $item instanceof WC_Order_Item_Product ) {
        continue;
      }

      $product = $item->get_product();
      if ( ! $product instanceof WC_Product ) {
        continue;
      }

      $product_id = $product->get_id();

      if ( isset( $locks[ $product_id ][ $order_id ] ) ) {
        unset( $locks[ $product_id ][ $order_id ] );
      }

      if ( isset( $locks[ $product_id ] ) && empty( $locks[ $product_id ] ) ) {
        unset( $locks[ $product_id ] );
      }
    }

    update_option( self::LOCK_OPTION, $locks, false );
    $order->delete_meta_data( '_powerup_stock_locked' );
    $order->save();
  }

  public function get_locks() {
    $locks = get_option( self::LOCK_OPTION, array() );
    return is_array( $locks ) ? $locks : array();
  }

  private function get_locked_qty_for_product( $locks, $product_id ) {
    if ( empty( $locks[ $product_id ] ) || ! is_array( $locks[ $product_id ] ) ) {
      return 0;
    }

    $qty = 0;
    $now = time();

    foreach ( $locks[ $product_id ] as $order_id => $row ) {
      if ( empty( $row['expires'] ) || (int) $row['expires'] < $now ) {
        continue;
      }

      $qty += isset( $row['qty'] ) ? (int) $row['qty'] : 0;
    }

    return $qty;
  }

  public static function cleanup_expired_locks() {
    $locks = get_option( self::LOCK_OPTION, array() );
    if ( ! is_array( $locks ) || empty( $locks ) ) {
      return;
    }

    $now = time();

    foreach ( $locks as $product_id => $order_rows ) {
      if ( ! is_array( $order_rows ) ) {
        unset( $locks[ $product_id ] );
        continue;
      }

      foreach ( $order_rows as $order_id => $row ) {
        if ( empty( $row['expires'] ) || (int) $row['expires'] < $now ) {
          unset( $locks[ $product_id ][ $order_id ] );
        }
      }

      if ( empty( $locks[ $product_id ] ) ) {
        unset( $locks[ $product_id ] );
      }
    }

    update_option( self::LOCK_OPTION, $locks, false );
  }
}
