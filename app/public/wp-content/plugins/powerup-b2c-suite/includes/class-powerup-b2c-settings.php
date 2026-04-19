<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Settings {
  private static $instance = null;
  const OPTION_KEY = 'powerup_b2c_options';

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'admin_init', array( $this, 'register_settings' ) );
    add_action( 'admin_menu', array( $this, 'add_menu' ), 30 );
  }

  public static function defaults() {
    return array(
      'checkout_currency' => 'USD',
      'country_currency_map' => "US=USD\nCA=CAD\nGB=GBP\nDE=EUR\nFR=EUR\nES=EUR\nIT=EUR\nAU=AUD",
      'fallback_currency' => 'USD',
      'exchange_rates' => "USD=1\nEUR=0.92\nGBP=0.79\nCAD=1.36\nAUD=1.52",
      'auto_exchange_refresh' => 1,
      'exchange_api_url' => 'https://api.exchangerate.host/latest',
      'erp' => array(
        'dianxiaomi' => array( 'enabled' => 0, 'endpoint' => '', 'api_key' => '', 'api_secret' => '', 'shop_id' => '' ),
        'mangguo'    => array( 'enabled' => 0, 'endpoint' => '', 'api_key' => '', 'api_secret' => '', 'shop_id' => '' ),
        'wanliniu'   => array( 'enabled' => 0, 'endpoint' => '', 'api_key' => '', 'api_secret' => '', 'shop_id' => '' ),
        'lingxing'   => array( 'enabled' => 0, 'endpoint' => '', 'api_key' => '', 'api_secret' => '', 'shop_id' => '' ),
        'yicang'     => array( 'enabled' => 0, 'endpoint' => '', 'api_key' => '', 'api_secret' => '', 'shop_id' => '' ),
      ),
      'webhook_token' => '',
      'erp_retry_limit' => 5,
      'erp_retry_backoff' => 300,
    );
  }

  public static function get_options() {
    $saved = get_option( self::OPTION_KEY, array() );
    if ( ! is_array( $saved ) ) {
      $saved = array();
    }

    return wp_parse_args( $saved, self::defaults() );
  }

  public static function get_option( $key, $default = null ) {
    $options = self::get_options();
    return isset( $options[ $key ] ) ? $options[ $key ] : $default;
  }

  public function register_settings() {
    register_setting(
      'powerup_b2c_group',
      self::OPTION_KEY,
      array(
        'type' => 'array',
        'sanitize_callback' => array( $this, 'sanitize' ),
        'default' => self::defaults(),
      )
    );
  }

  public function sanitize( $input ) {
    $defaults = self::defaults();
    $output   = $defaults;

    $output['checkout_currency'] = isset( $input['checkout_currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $input['checkout_currency'] ) ) ) : $defaults['checkout_currency'];
    $output['fallback_currency'] = isset( $input['fallback_currency'] ) ? strtoupper( sanitize_text_field( wp_unslash( $input['fallback_currency'] ) ) ) : $defaults['fallback_currency'];
    $output['country_currency_map'] = isset( $input['country_currency_map'] ) ? sanitize_textarea_field( wp_unslash( $input['country_currency_map'] ) ) : $defaults['country_currency_map'];
    $output['exchange_rates'] = isset( $input['exchange_rates'] ) ? sanitize_textarea_field( wp_unslash( $input['exchange_rates'] ) ) : $defaults['exchange_rates'];
    $output['auto_exchange_refresh'] = ! empty( $input['auto_exchange_refresh'] ) ? 1 : 0;
    $output['exchange_api_url'] = isset( $input['exchange_api_url'] ) ? esc_url_raw( trim( wp_unslash( $input['exchange_api_url'] ) ) ) : $defaults['exchange_api_url'];
    $output['webhook_token'] = isset( $input['webhook_token'] ) ? sanitize_text_field( wp_unslash( $input['webhook_token'] ) ) : '';
    $output['erp_retry_limit'] = isset( $input['erp_retry_limit'] ) ? max( 1, min( 10, absint( $input['erp_retry_limit'] ) ) ) : 5;
    $output['erp_retry_backoff'] = isset( $input['erp_retry_backoff'] ) ? max( 60, absint( $input['erp_retry_backoff'] ) ) : 300;

    if ( isset( $input['erp'] ) && is_array( $input['erp'] ) ) {
      foreach ( $defaults['erp'] as $provider_key => $provider_defaults ) {
        $row = isset( $input['erp'][ $provider_key ] ) && is_array( $input['erp'][ $provider_key ] ) ? $input['erp'][ $provider_key ] : array();
        $output['erp'][ $provider_key ] = array(
          'enabled'    => ! empty( $row['enabled'] ) ? 1 : 0,
          'endpoint'   => isset( $row['endpoint'] ) ? esc_url_raw( trim( wp_unslash( $row['endpoint'] ) ) ) : '',
          'api_key'    => isset( $row['api_key'] ) ? sanitize_text_field( wp_unslash( $row['api_key'] ) ) : '',
          'api_secret' => isset( $row['api_secret'] ) ? sanitize_text_field( wp_unslash( $row['api_secret'] ) ) : '',
          'shop_id'    => isset( $row['shop_id'] ) ? sanitize_text_field( wp_unslash( $row['shop_id'] ) ) : '',
        );
      }
    }

    return $output;
  }

  public function add_menu() {
    add_submenu_page(
      'woocommerce',
      __( 'PowerUp B2C Suite', 'powerup-b2c' ),
      __( 'PowerUp B2C Suite', 'powerup-b2c' ),
      'manage_woocommerce',
      'powerup-b2c-suite',
      array( $this, 'render_page' )
    );
  }

  public function render_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
      return;
    }

    $options = self::get_options();
    $erp_names = array(
      'dianxiaomi' => '店小秘',
      'mangguo'    => '芒果店长',
      'wanliniu'   => '万里牛',
      'lingxing'   => '领星',
      'yicang'     => '易仓',
    );

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'PowerUp B2C Suite', 'powerup-b2c' ) . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields( 'powerup_b2c_group' );

    echo '<h2>' . esc_html__( 'Multi Currency', 'powerup-b2c' ) . '</h2>';
    echo '<table class="form-table" role="presentation">';
    echo '<tr><th><label for="checkout_currency">' . esc_html__( 'Checkout Currency', 'powerup-b2c' ) . '</label></th><td><input id="checkout_currency" name="' . esc_attr( self::OPTION_KEY ) . '[checkout_currency]" value="' . esc_attr( $options['checkout_currency'] ) . '" class="regular-text" /></td></tr>';
    echo '<tr><th><label for="fallback_currency">' . esc_html__( 'Fallback Currency', 'powerup-b2c' ) . '</label></th><td><input id="fallback_currency" name="' . esc_attr( self::OPTION_KEY ) . '[fallback_currency]" value="' . esc_attr( $options['fallback_currency'] ) . '" class="regular-text" /></td></tr>';
    echo '<tr><th><label for="country_currency_map">' . esc_html__( 'Country Currency Map', 'powerup-b2c' ) . '</label></th><td><textarea id="country_currency_map" name="' . esc_attr( self::OPTION_KEY ) . '[country_currency_map]" rows="8" cols="60">' . esc_textarea( $options['country_currency_map'] ) . '</textarea><p class="description">US=USD 一行一条，按访客国家自动匹配。</p></td></tr>';
    echo '<tr><th><label for="exchange_rates">' . esc_html__( 'Exchange Rates', 'powerup-b2c' ) . '</label></th><td><textarea id="exchange_rates" name="' . esc_attr( self::OPTION_KEY ) . '[exchange_rates]" rows="8" cols="60">' . esc_textarea( $options['exchange_rates'] ) . '</textarea><p class="description">USD=1 一行一条，作为统一基准汇率。</p></td></tr>';
    echo '<tr><th><label for="auto_exchange_refresh">' . esc_html__( 'Auto Refresh Rates', 'powerup-b2c' ) . '</label></th><td><label><input type="checkbox" id="auto_exchange_refresh" name="' . esc_attr( self::OPTION_KEY ) . '[auto_exchange_refresh]" value="1" ' . checked( ! empty( $options['auto_exchange_refresh'] ), true, false ) . ' /> ' . esc_html__( 'Daily refresh from API source', 'powerup-b2c' ) . '</label></td></tr>';
    echo '<tr><th><label for="exchange_api_url">' . esc_html__( 'Exchange API URL', 'powerup-b2c' ) . '</label></th><td><input id="exchange_api_url" name="' . esc_attr( self::OPTION_KEY ) . '[exchange_api_url]" value="' . esc_attr( $options['exchange_api_url'] ) . '" class="regular-text" style="width:600px" /></td></tr>';
    echo '</table>';

    echo '<h2>' . esc_html__( 'ERP Integrations', 'powerup-b2c' ) . '</h2>';
    echo '<table class="widefat striped" style="max-width:1200px">';
    echo '<thead><tr><th>ERP</th><th>启用</th><th>Endpoint</th><th>API Key</th><th>API Secret</th><th>Shop ID</th></tr></thead><tbody>';

    foreach ( $erp_names as $provider_key => $label ) {
      $row = isset( $options['erp'][ $provider_key ] ) ? $options['erp'][ $provider_key ] : array();
      echo '<tr>';
      echo '<td><strong>' . esc_html( $label ) . '</strong></td>';
      echo '<td><input type="checkbox" name="' . esc_attr( self::OPTION_KEY ) . '[erp][' . esc_attr( $provider_key ) . '][enabled]" value="1" ' . checked( ! empty( $row['enabled'] ), true, false ) . ' /></td>';
      echo '<td><input type="url" style="width:100%" name="' . esc_attr( self::OPTION_KEY ) . '[erp][' . esc_attr( $provider_key ) . '][endpoint]" value="' . esc_attr( isset( $row['endpoint'] ) ? $row['endpoint'] : '' ) . '" placeholder="https://api.xxx.com/webhook" /></td>';
      echo '<td><input type="text" style="width:100%" name="' . esc_attr( self::OPTION_KEY ) . '[erp][' . esc_attr( $provider_key ) . '][api_key]" value="' . esc_attr( isset( $row['api_key'] ) ? $row['api_key'] : '' ) . '" /></td>';
      echo '<td><input type="text" style="width:100%" name="' . esc_attr( self::OPTION_KEY ) . '[erp][' . esc_attr( $provider_key ) . '][api_secret]" value="' . esc_attr( isset( $row['api_secret'] ) ? $row['api_secret'] : '' ) . '" /></td>';
      echo '<td><input type="text" style="width:100%" name="' . esc_attr( self::OPTION_KEY ) . '[erp][' . esc_attr( $provider_key ) . '][shop_id]" value="' . esc_attr( isset( $row['shop_id'] ) ? $row['shop_id'] : '' ) . '" /></td>';
      echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<h2>' . esc_html__( 'Webhook & Retry', 'powerup-b2c' ) . '</h2>';
    echo '<table class="form-table" role="presentation">';
    echo '<tr><th><label for="webhook_token">Webhook Token</label></th><td><input id="webhook_token" name="' . esc_attr( self::OPTION_KEY ) . '[webhook_token]" value="' . esc_attr( $options['webhook_token'] ) . '" class="regular-text" /><p class="description">用于 ERP 回调签名校验。</p></td></tr>';
    echo '<tr><th><label for="erp_retry_limit">失败重试次数</label></th><td><input type="number" min="1" max="10" id="erp_retry_limit" name="' . esc_attr( self::OPTION_KEY ) . '[erp_retry_limit]" value="' . esc_attr( $options['erp_retry_limit'] ) . '" /></td></tr>';
    echo '<tr><th><label for="erp_retry_backoff">重试间隔(秒)</label></th><td><input type="number" min="60" id="erp_retry_backoff" name="' . esc_attr( self::OPTION_KEY ) . '[erp_retry_backoff]" value="' . esc_attr( $options['erp_retry_backoff'] ) . '" /></td></tr>';
    echo '</table>';

    submit_button( __( 'Save Settings', 'powerup-b2c' ) );
    echo '</form>';
    echo '</div>';
  }
}
