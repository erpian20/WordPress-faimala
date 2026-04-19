<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Currency {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'init', array( $this, 'capture_currency_switch' ) );
    add_filter( 'woocommerce_currency', array( $this, 'filter_store_currency' ), 50 );
    add_filter( 'woocommerce_product_get_price', array( $this, 'convert_price_by_currency' ), 20, 2 );
    add_filter( 'woocommerce_product_get_regular_price', array( $this, 'convert_price_by_currency' ), 20, 2 );
    add_filter( 'woocommerce_product_variation_get_price', array( $this, 'convert_price_by_currency' ), 20, 2 );
    add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'convert_price_by_currency' ), 20, 2 );
    add_action( 'powerup_b2c_refresh_exchange_rates', array( $this, 'refresh_exchange_rates' ) );
  }

  public function capture_currency_switch() {
    if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->session ) {
      return;
    }

    if ( isset( $_GET['powerup_currency'] ) ) {
      $currency = strtoupper( sanitize_text_field( wp_unslash( $_GET['powerup_currency'] ) ) );
      if ( preg_match( '/^[A-Z]{3}$/', $currency ) ) {
        WC()->session->set( 'powerup_currency', $currency );
        setcookie( 'powerup_currency', $currency, time() + MONTH_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
      }
    }

    if ( WC()->session && empty( WC()->session->get( 'powerup_currency' ) ) && ! empty( $_COOKIE['powerup_currency'] ) ) {
      $from_cookie = strtoupper( sanitize_text_field( wp_unslash( $_COOKIE['powerup_currency'] ) ) );
      if ( preg_match( '/^[A-Z]{3}$/', $from_cookie ) ) {
        WC()->session->set( 'powerup_currency', $from_cookie );
      }
    }
  }

  public function filter_store_currency( $currency ) {
    $options          = PowerUp_B2C_Settings::get_options();
    $checkout_currency = ! empty( $options['checkout_currency'] ) ? strtoupper( $options['checkout_currency'] ) : 'USD';

    // 支付货币统一：结账与支付页固定为后台设置货币。
    if ( is_checkout() || is_wc_endpoint_url( 'order-pay' ) ) {
      return $checkout_currency;
    }

    if ( function_exists( 'WC' ) && WC() && WC()->session && WC()->session->get( 'powerup_currency' ) ) {
      return strtoupper( WC()->session->get( 'powerup_currency' ) );
    }

    $country_code = $this->detect_country_code();
    $mapped       = $this->resolve_currency_by_country( $country_code, $options['country_currency_map'] );

    if ( $mapped ) {
      return $mapped;
    }

    return ! empty( $options['fallback_currency'] ) ? strtoupper( $options['fallback_currency'] ) : $currency;
  }

  private function detect_country_code() {
    if ( class_exists( 'WC_Geolocation' ) ) {
      $geo = WC_Geolocation::geolocate_ip();
      if ( ! empty( $geo['country'] ) ) {
        return strtoupper( $geo['country'] );
      }
    }

    return '';
  }

  private function resolve_currency_by_country( $country_code, $map_text ) {
    if ( empty( $country_code ) || empty( $map_text ) ) {
      return '';
    }

    $lines = preg_split( '/\r\n|\r|\n/', (string) $map_text );
    if ( ! is_array( $lines ) ) {
      return '';
    }

    foreach ( $lines as $line ) {
      $line = trim( $line );
      if ( '' === $line || false === strpos( $line, '=' ) ) {
        continue;
      }

      list( $country, $currency ) = array_map( 'trim', explode( '=', $line, 2 ) );
      if ( strtoupper( $country ) === $country_code && preg_match( '/^[A-Z]{3}$/', strtoupper( $currency ) ) ) {
        return strtoupper( $currency );
      }
    }

    return '';
  }

  public function convert_price_by_currency( $price, $product ) {
    if ( '' === $price || null === $price || ! is_numeric( $price ) ) {
      return $price;
    }

    if ( is_admin() && ! wp_doing_ajax() ) {
      return $price;
    }

    $options = PowerUp_B2C_Settings::get_options();
    $target_currency = get_woocommerce_currency();
    $rates = $this->parse_exchange_rates( isset( $options['exchange_rates'] ) ? $options['exchange_rates'] : '' );

    if ( empty( $rates['USD'] ) || empty( $rates[ $target_currency ] ) ) {
      return $price;
    }

    // 结账阶段金额不再二次转换，统一由 checkout currency 结算。
    if ( is_checkout() || is_wc_endpoint_url( 'order-pay' ) ) {
      return $price;
    }

    $source_currency = ! empty( $options['checkout_currency'] ) ? strtoupper( $options['checkout_currency'] ) : 'USD';
    if ( empty( $rates[ $source_currency ] ) ) {
      $source_currency = 'USD';
    }

    $numeric_price = (float) $price;
    if ( $source_currency === $target_currency ) {
      return $numeric_price;
    }

    $price_in_usd = $numeric_price / (float) $rates[ $source_currency ];
    $converted    = $price_in_usd * (float) $rates[ $target_currency ];

    return round( $converted, wc_get_price_decimals() );
  }

  private function parse_exchange_rates( $raw_text ) {
    $rates = array();
    $lines = preg_split( '/\r\n|\r|\n/', (string) $raw_text );

    if ( ! is_array( $lines ) ) {
      return $rates;
    }

    foreach ( $lines as $line ) {
      $line = trim( $line );

      if ( '' === $line || false === strpos( $line, '=' ) ) {
        continue;
      }

      list( $currency, $rate ) = array_map( 'trim', explode( '=', $line, 2 ) );
      $currency = strtoupper( $currency );
      $rate     = (float) $rate;

      if ( preg_match( '/^[A-Z]{3}$/', $currency ) && $rate > 0 ) {
        $rates[ $currency ] = $rate;
      }
    }

    return $rates;
  }

  public function refresh_exchange_rates() {
    $options = PowerUp_B2C_Settings::get_options();

    if ( empty( $options['auto_exchange_refresh'] ) ) {
      return;
    }

    $api_url = ! empty( $options['exchange_api_url'] ) ? (string) $options['exchange_api_url'] : 'https://api.exchangerate.host/latest';
    $current_rates = $this->parse_exchange_rates( isset( $options['exchange_rates'] ) ? $options['exchange_rates'] : '' );

    if ( empty( $current_rates ) ) {
      $current_rates = array( 'USD' => 1 );
    }

    $symbols = implode( ',', array_keys( $current_rates ) );
    $request_url = add_query_arg(
      array(
        'base'    => 'USD',
        'symbols' => $symbols,
      ),
      $api_url
    );

    $response = wp_remote_get(
      $request_url,
      array(
        'timeout' => 20,
      )
    );

    if ( is_wp_error( $response ) ) {
      return;
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( $code < 200 || $code >= 300 ) {
      return;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( empty( $data['rates'] ) || ! is_array( $data['rates'] ) ) {
      return;
    }

    $new_rates = array( 'USD' => 1 );
    foreach ( $data['rates'] as $currency => $rate ) {
      $currency = strtoupper( sanitize_text_field( (string) $currency ) );
      $rate = (float) $rate;

      if ( preg_match( '/^[A-Z]{3}$/', $currency ) && $rate > 0 ) {
        $new_rates[ $currency ] = $rate;
      }
    }

    if ( count( $new_rates ) <= 1 ) {
      return;
    }

    $lines = array();
    foreach ( $new_rates as $currency => $rate ) {
      $lines[] = $currency . '=' . $rate;
    }

    $options['exchange_rates'] = implode( "\n", $lines );
    update_option( PowerUp_B2C_Settings::OPTION_KEY, $options, false );
    update_option( 'powerup_b2c_rates_last_sync', gmdate( 'Y-m-d H:i:s' ), false );
  }
}
