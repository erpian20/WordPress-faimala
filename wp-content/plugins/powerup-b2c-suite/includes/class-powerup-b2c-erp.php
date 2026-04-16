<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_ERP {
  private static $instance = null;
  const RETRY_OPTION = 'powerup_b2c_erp_retry_queue';
  const DISPATCH_META_PREFIX = '_powerup_erp_dispatch_';

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'woocommerce_checkout_order_processed', array( $this, 'push_order_created' ), 25, 1 );
    add_action( 'woocommerce_order_status_changed', array( $this, 'push_order_status_changed' ), 25, 3 );

    add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    add_action( 'powerup_b2c_retry_failed_pushes', array( $this, 'retry_failed_pushes' ) );
    add_action( 'powerup_b2c_retry_failed_pushes', array( 'PowerUp_B2C_Checkout', 'cleanup_expired_locks' ) );
  }

  public function providers() {
    return array(
      'dianxiaomi' => '店小秘',
      'mangguo'    => '芒果店长',
      'wanliniu'   => '万里牛',
      'lingxing'   => '领星',
      'yicang'     => '易仓',
    );
  }

  public function register_routes() {
    register_rest_route(
      'powerup-b2c/v1',
      '/erp/(?P<provider>[a-z0-9_-]+)/products',
      array(
        'methods' => 'POST',
        'callback' => array( $this, 'sync_products_from_erp' ),
        'permission_callback' => array( $this, 'authenticate_erp_request' ),
      )
    );

    register_rest_route(
      'powerup-b2c/v1',
      '/erp/(?P<provider>[a-z0-9_-]+)/shipments',
      array(
        'methods' => 'POST',
        'callback' => array( $this, 'sync_shipment_from_erp' ),
        'permission_callback' => array( $this, 'authenticate_erp_request' ),
      )
    );

    register_rest_route(
      'powerup-b2c/v1',
      '/erp/(?P<provider>[a-z0-9_-]+)/stock-price',
      array(
        'methods' => 'POST',
        'callback' => array( $this, 'sync_stock_price_from_erp' ),
        'permission_callback' => array( $this, 'authenticate_erp_request' ),
      )
    );
  }

  public function authenticate_erp_request( WP_REST_Request $request ) {
    $provider = sanitize_key( (string) $request['provider'] );
    if ( ! isset( $this->providers()[ $provider ] ) ) {
      return new WP_Error( 'invalid_provider', 'Provider not supported.', array( 'status' => 400 ) );
    }

    $options = PowerUp_B2C_Settings::get_options();
    $erp_config = isset( $options['erp'][ $provider ] ) ? $options['erp'][ $provider ] : array();
    $secret = isset( $erp_config['api_secret'] ) ? (string) $erp_config['api_secret'] : '';
    $global_token = isset( $options['webhook_token'] ) ? (string) $options['webhook_token'] : '';

    $incoming_signature = (string) $request->get_header( 'x-powerup-signature' );
    $incoming_timestamp = (int) $request->get_header( 'x-powerup-timestamp' );
    $incoming_nonce     = sanitize_text_field( (string) $request->get_header( 'x-powerup-nonce' ) );
    $raw_body = (string) $request->get_body();

    if ( $incoming_timestamp > 0 && abs( time() - $incoming_timestamp ) > 300 ) {
      return new WP_Error( 'expired_request', 'Request expired.', array( 'status' => 401 ) );
    }

    if ( '' !== $incoming_nonce ) {
      $nonce_key = 'powerup_erp_nonce_' . md5( $provider . '|' . $incoming_nonce );
      if ( get_transient( $nonce_key ) ) {
        return new WP_Error( 'replay_request', 'Replay request.', array( 'status' => 401 ) );
      }
      set_transient( $nonce_key, 1, 10 * MINUTE_IN_SECONDS );
    }

    if ( $secret && $incoming_signature ) {
      $signed_payload = $incoming_timestamp > 0 ? $incoming_timestamp . '.' . $raw_body : $raw_body;
      $expected = hash_hmac( 'sha256', $signed_payload, $secret );
      if ( hash_equals( $expected, $incoming_signature ) ) {
        return true;
      }
    }

    if ( $global_token ) {
      $incoming_token = (string) $request->get_header( 'x-powerup-token' );
      if ( hash_equals( $global_token, $incoming_token ) ) {
        return true;
      }
    }

    return new WP_Error( 'invalid_signature', 'Invalid signature or token.', array( 'status' => 401 ) );
  }

  public function sync_products_from_erp( WP_REST_Request $request ) {
    $payload = $request->get_json_params();
    $items = isset( $payload['items'] ) && is_array( $payload['items'] ) ? $payload['items'] : array();

    if ( empty( $items ) ) {
      return new WP_REST_Response( array( 'ok' => false, 'message' => 'No items.' ), 400 );
    }

    $result = array();

    foreach ( $items as $item ) {
      $sku   = isset( $item['sku'] ) ? sanitize_text_field( (string) $item['sku'] ) : '';
      $name  = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : '';
      $price = isset( $item['price'] ) ? (float) $item['price'] : null;
      $stock = isset( $item['stock'] ) ? (int) $item['stock'] : null;

      if ( '' === $sku || '' === $name ) {
        $result[] = array( 'sku' => $sku, 'status' => 'skipped', 'reason' => 'sku_or_name_missing' );
        continue;
      }

      $product_id = wc_get_product_id_by_sku( $sku );

      if ( $product_id ) {
        $product = wc_get_product( $product_id );
      } else {
        $product = new WC_Product_Simple();
        $product->set_sku( $sku );
      }

      if ( ! $product instanceof WC_Product ) {
        $result[] = array( 'sku' => $sku, 'status' => 'failed', 'reason' => 'product_init_failed' );
        continue;
      }

      $product->set_name( $name );
      $product->set_status( 'publish' );
      $product->set_catalog_visibility( 'visible' );

      if ( null !== $price && $price >= 0 ) {
        $product->set_regular_price( (string) $price );
      }

      if ( null !== $stock ) {
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $stock );
        $product->set_stock_status( $stock > 0 ? 'instock' : 'outofstock' );
      }

      $saved_id = $product->save();

      if ( ! empty( $item['description'] ) ) {
        wp_update_post(
          array(
            'ID' => $saved_id,
            'post_content' => wp_kses_post( (string) $item['description'] ),
          )
        );
      }

      $result[] = array( 'sku' => $sku, 'product_id' => $saved_id, 'status' => 'ok' );
    }

    return new WP_REST_Response( array( 'ok' => true, 'items' => $result ), 200 );
  }

  public function sync_stock_price_from_erp( WP_REST_Request $request ) {
    $payload = $request->get_json_params();
    $items = isset( $payload['items'] ) && is_array( $payload['items'] ) ? $payload['items'] : array();

    if ( empty( $items ) ) {
      return new WP_REST_Response( array( 'ok' => false, 'message' => 'No items.' ), 400 );
    }

    $result = array();

    foreach ( $items as $item ) {
      $sku = isset( $item['sku'] ) ? sanitize_text_field( (string) $item['sku'] ) : '';
      if ( '' === $sku ) {
        $result[] = array( 'status' => 'skipped', 'reason' => 'sku_missing' );
        continue;
      }

      $product_id = wc_get_product_id_by_sku( $sku );
      if ( ! $product_id ) {
        $result[] = array( 'sku' => $sku, 'status' => 'not_found' );
        continue;
      }

      $product = wc_get_product( $product_id );
      if ( ! $product instanceof WC_Product ) {
        $result[] = array( 'sku' => $sku, 'status' => 'invalid_product' );
        continue;
      }

      if ( isset( $item['stock'] ) ) {
        $stock = (int) $item['stock'];
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $stock );
        $product->set_stock_status( $stock > 0 ? 'instock' : 'outofstock' );
      }

      if ( isset( $item['price'] ) ) {
        $price = (float) $item['price'];
        $product->set_regular_price( (string) $price );
      }

      $product->save();
      $result[] = array( 'sku' => $sku, 'status' => 'ok' );
    }

    return new WP_REST_Response( array( 'ok' => true, 'items' => $result ), 200 );
  }

  public function sync_shipment_from_erp( WP_REST_Request $request ) {
    $payload = $request->get_json_params();

    $order_id = isset( $payload['order_id'] ) ? absint( $payload['order_id'] ) : 0;
    $order_number = isset( $payload['order_number'] ) ? sanitize_text_field( (string) $payload['order_number'] ) : '';

    if ( ! $order_id && '' !== $order_number ) {
      if ( ctype_digit( $order_number ) ) {
        $order_id = absint( $order_number );
      }

      if ( ! $order_id ) {
        $found_orders = wc_get_orders(
          array(
            'limit'  => 1,
            'return' => 'ids',
            'search' => $order_number,
          )
        );

        if ( ! empty( $found_orders ) ) {
          $order_id = (int) $found_orders[0];
        }
      }
    }

    if ( ! $order_id ) {
      return new WP_REST_Response( array( 'ok' => false, 'message' => 'Order not found.' ), 404 );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order instanceof WC_Order ) {
      return new WP_REST_Response( array( 'ok' => false, 'message' => 'Order invalid.' ), 404 );
    }

    $tracking_number = isset( $payload['tracking_number'] ) ? sanitize_text_field( (string) $payload['tracking_number'] ) : '';
    $tracking_carrier = isset( $payload['carrier'] ) ? sanitize_text_field( (string) $payload['carrier'] ) : '';
    $provider = sanitize_key( (string) $request['provider'] );
    $event_id = isset( $payload['event_id'] ) ? sanitize_text_field( (string) $payload['event_id'] ) : '';

    if ( '' !== $event_id ) {
      $event_meta_key = '_powerup_erp_event_' . md5( $provider . '|' . $event_id );
      if ( $order->get_meta( $event_meta_key, true ) ) {
        return new WP_REST_Response( array( 'ok' => true, 'duplicate' => true, 'order_id' => $order_id ), 200 );
      }
      $order->update_meta_data( $event_meta_key, time() );
    }

    if ( '' === $tracking_number ) {
      return new WP_REST_Response( array( 'ok' => false, 'message' => 'Tracking number required.' ), 400 );
    }

    $order->update_meta_data( '_powerup_tracking_number', $tracking_number );
    $order->update_meta_data( '_powerup_tracking_carrier', $tracking_carrier );

    if ( in_array( $order->get_status(), array( 'pending', 'on-hold' ), true ) ) {
      $order->set_status( 'processing', __( 'Shipment synced from ERP callback.', 'powerup-b2c' ) );
    }

    $order->add_order_note( sprintf( 'ERP物流回传: %s / %s', $tracking_carrier, $tracking_number ) );
    $order->save();

    return new WP_REST_Response( array( 'ok' => true, 'order_id' => $order_id ), 200 );
  }

  public function push_order_created( $order_id ) {
    $this->dispatch_order_to_erp( $order_id, 'order_created', array() );
  }

  public function push_order_status_changed( $order_id, $old_status, $new_status ) {
    $this->dispatch_order_to_erp(
      $order_id,
      'order_status_changed',
      array(
        'old_status' => (string) $old_status,
        'new_status' => (string) $new_status,
      )
    );
  }

  private function dispatch_order_to_erp( $order_id, $event, $context ) {
    $order = wc_get_order( $order_id );
    if ( ! $order instanceof WC_Order ) {
      return;
    }

    $payload = $this->build_order_payload( $order, $event, $context );
    $options = PowerUp_B2C_Settings::get_options();
    $erp_rows = isset( $options['erp'] ) && is_array( $options['erp'] ) ? $options['erp'] : array();

    foreach ( $this->providers() as $provider_key => $provider_name ) {
      $config = isset( $erp_rows[ $provider_key ] ) ? $erp_rows[ $provider_key ] : array();
      if ( empty( $config['enabled'] ) || empty( $config['endpoint'] ) ) {
        continue;
      }

      $dispatch_key = $this->build_dispatch_key( $provider_key, $event, $order, $context );
      $meta_key     = self::DISPATCH_META_PREFIX . $dispatch_key;

      if ( $order->get_meta( $meta_key, true ) ) {
        continue;
      }

      $result = $this->send_payload_to_provider( $provider_key, $config, $payload, $dispatch_key );

      if ( is_wp_error( $result ) ) {
        $order->add_order_note( sprintf( '[ERP:%s] 推送失败：%s', $provider_name, $result->get_error_message() ) );
        $this->enqueue_retry( $provider_key, $payload, $result->get_error_message() );
      } else {
        $order->update_meta_data( $meta_key, gmdate( 'c' ) );
        $order->save();
        $order->add_order_note( sprintf( '[ERP:%s] 推送成功（HTTP %d）', $provider_name, $result['status'] ) );
      }
    }
  }

  private function build_order_payload( WC_Order $order, $event, $context ) {
    $items = array();

    foreach ( $order->get_items() as $item ) {
      if ( ! $item instanceof WC_Order_Item_Product ) {
        continue;
      }

      $product = $item->get_product();
      $items[] = array(
        'name'         => $item->get_name(),
        'product_id'   => $item->get_product_id(),
        'variation_id' => $item->get_variation_id(),
        'sku'          => $product instanceof WC_Product ? $product->get_sku() : '',
        'qty'          => (int) $item->get_quantity(),
        'line_total'   => (float) $item->get_total(),
      );
    }

    return array(
      'source' => 'powerup-woocommerce',
      'event'  => $event,
      'event_id' => md5( $order->get_id() . '|' . $event . '|' . wp_json_encode( $context ) ),
      'site'   => home_url( '/' ),
      'time'   => gmdate( 'c' ),
      'context' => $context,
      'order'  => array(
        'id'         => $order->get_id(),
        'number'     => $order->get_order_number(),
        'status'     => $order->get_status(),
        'currency'   => $order->get_currency(),
        'total'      => (float) $order->get_total(),
        'shipping'   => (float) $order->get_shipping_total(),
        'tax'        => (float) $order->get_total_tax(),
        'payment_method' => $order->get_payment_method(),
        'vat_number' => $order->get_meta( '_powerup_vat_number', true ),
        'ioss_number' => $order->get_meta( '_powerup_ioss_number', true ),
        'billing'    => array(
          'first_name' => $order->get_billing_first_name(),
          'last_name'  => $order->get_billing_last_name(),
          'email'      => $order->get_billing_email(),
          'phone'      => $order->get_billing_phone(),
          'address_1'  => $order->get_billing_address_1(),
          'address_2'  => $order->get_billing_address_2(),
          'city'       => $order->get_billing_city(),
          'state'      => $order->get_billing_state(),
          'postcode'   => $order->get_billing_postcode(),
          'country'    => $order->get_billing_country(),
        ),
        'shipping_address' => array(
          'first_name' => $order->get_shipping_first_name(),
          'last_name'  => $order->get_shipping_last_name(),
          'phone'      => method_exists( $order, 'get_shipping_phone' ) ? $order->get_shipping_phone() : '',
          'address_1'  => $order->get_shipping_address_1(),
          'address_2'  => $order->get_shipping_address_2(),
          'city'       => $order->get_shipping_city(),
          'state'      => $order->get_shipping_state(),
          'postcode'   => $order->get_shipping_postcode(),
          'country'    => $order->get_shipping_country(),
        ),
        'items' => $items,
      ),
    );
  }

  private function send_payload_to_provider( $provider_key, $config, $payload, $dispatch_key = '' ) {
    $endpoint = trim( (string) $config['endpoint'] );

    if ( '' === $endpoint ) {
      return new WP_Error( 'empty_endpoint', 'Endpoint is empty.' );
    }

    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'X-PowerUp-Provider' => $provider_key,
    );

    if ( ! empty( $config['api_key'] ) ) {
      $headers['Authorization'] = 'Bearer ' . $config['api_key'];
      $headers['X-Api-Key']     = $config['api_key'];
    }

    if ( ! empty( $config['shop_id'] ) ) {
      $headers['X-Shop-Id'] = (string) $config['shop_id'];
    }

    $timestamp = time();
    $nonce     = wp_generate_password( 12, false, false );

    $headers['X-PowerUp-Timestamp'] = (string) $timestamp;
    $headers['X-PowerUp-Nonce']     = $nonce;

    if ( '' !== $dispatch_key ) {
      $headers['X-Idempotency-Key'] = $dispatch_key;
    }

    $secret = ! empty( $config['api_secret'] ) ? (string) $config['api_secret'] : '';
    $encoded_payload = wp_json_encode( $payload );

    if ( $secret ) {
      $headers['X-PowerUp-Signature'] = hash_hmac( 'sha256', $timestamp . '.' . $encoded_payload, $secret );
    }

    $response = wp_remote_post(
      $endpoint,
      array(
        'timeout' => 20,
        'headers' => $headers,
        'body'    => $encoded_payload,
      )
    );

    if ( is_wp_error( $response ) ) {
      return $response;
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    if ( $status >= 200 && $status < 300 ) {
      return array( 'status' => $status );
    }

    $body = wp_remote_retrieve_body( $response );
    return new WP_Error( 'http_error', 'HTTP ' . $status . ': ' . wp_strip_all_tags( (string) $body ) );
  }

  private function enqueue_retry( $provider_key, $payload, $error_message ) {
    $options = PowerUp_B2C_Settings::get_options();
    $retry_limit = isset( $options['erp_retry_limit'] ) ? absint( $options['erp_retry_limit'] ) : 5;
    $backoff = isset( $options['erp_retry_backoff'] ) ? absint( $options['erp_retry_backoff'] ) : 300;

    $queue = get_option( self::RETRY_OPTION, array() );
    if ( ! is_array( $queue ) ) {
      $queue = array();
    }

    $queue[] = array(
      'provider' => $provider_key,
      'payload'  => $payload,
      'error'    => $error_message,
      'attempts' => 1,
      'max_attempts' => max( 1, $retry_limit ),
      'next_run' => time() + max( 60, $backoff ),
      'created_at' => time(),
    );

    if ( count( $queue ) > 1000 ) {
      $queue = array_slice( $queue, -1000 );
    }

    update_option( self::RETRY_OPTION, $queue, false );
    $this->log( 'warning', 'ERP enqueue retry', array( 'provider' => $provider_key, 'error' => $error_message ) );
  }

  public function retry_failed_pushes() {
    $queue = get_option( self::RETRY_OPTION, array() );
    if ( ! is_array( $queue ) || empty( $queue ) ) {
      return;
    }

    $options = PowerUp_B2C_Settings::get_options();
    $erp_rows = isset( $options['erp'] ) && is_array( $options['erp'] ) ? $options['erp'] : array();
    $backoff = isset( $options['erp_retry_backoff'] ) ? absint( $options['erp_retry_backoff'] ) : 300;

    $now = time();
    $new_queue = array();

    foreach ( $queue as $task ) {
      if ( empty( $task['next_run'] ) || (int) $task['next_run'] > $now ) {
        $new_queue[] = $task;
        continue;
      }

      $provider = isset( $task['provider'] ) ? sanitize_key( $task['provider'] ) : '';
      if ( ! $provider || empty( $erp_rows[ $provider ] ) ) {
        continue;
      }

      $config = $erp_rows[ $provider ];
      $payload = isset( $task['payload'] ) && is_array( $task['payload'] ) ? $task['payload'] : array();
      $attempts = isset( $task['attempts'] ) ? absint( $task['attempts'] ) : 1;
      $max_attempts = isset( $task['max_attempts'] ) ? absint( $task['max_attempts'] ) : 5;

      $result = $this->send_payload_to_provider( $provider, $config, $payload );

      if ( is_wp_error( $result ) ) {
        $attempts++;

        if ( $attempts <= $max_attempts ) {
          $task['attempts'] = $attempts;
          $task['next_run'] = $now + max( 60, $backoff * $attempts );
          $task['error'] = $result->get_error_message();
          $new_queue[] = $task;
        } else {
          $this->log( 'error', 'ERP retry exhausted', array( 'provider' => $provider, 'error' => $result->get_error_message() ) );
        }
      } else {
        $this->log( 'info', 'ERP retry success', array( 'provider' => $provider, 'status' => $result['status'] ) );
      }
    }

    update_option( self::RETRY_OPTION, $new_queue, false );
  }

  private function log( $level, $message, $context = array() ) {
    if ( function_exists( 'wc_get_logger' ) ) {
      $logger = wc_get_logger();
      $logger->log( $level, $message . ' ' . wp_json_encode( $context ), array( 'source' => 'powerup-b2c-erp' ) );
      return;
    }

    error_log( '[powerup-b2c-erp] ' . $message . ' ' . wp_json_encode( $context ) );
  }

  private function build_dispatch_key( $provider_key, $event, WC_Order $order, $context ) {
    return md5( $provider_key . '|' . $event . '|' . $order->get_id() . '|' . wp_json_encode( $context ) );
  }
}
