<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class PowerUp_B2C_Shipping {
  private static $instance = null;

  public static function instance() {
    if ( null === self::$instance ) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function __construct() {
    add_action( 'woocommerce_shipping_init', array( $this, 'register_method_class' ) );
    add_filter( 'woocommerce_shipping_methods', array( $this, 'register_shipping_method' ) );
  }

  public function register_method_class() {
    if ( class_exists( 'WC_PowerUp_Carrier_Shipping' ) ) {
      return;
    }

    class WC_PowerUp_Carrier_Shipping extends WC_Shipping_Method {
      public function __construct( $instance_id = 0 ) {
        $this->id                 = 'powerup_carrier_shipping';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'PowerUp Carrier Shipping', 'powerup-b2c' );
        $this->method_description = __( '按重量 + 地区 + 物流商计算运费（4PX/燕文/云途）。', 'powerup-b2c' );
        $this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );

        $this->init();
      }

      public function init() {
        $this->instance_form_fields = array(
          'title' => array(
            'title'       => __( 'Title', 'powerup-b2c' ),
            'type'        => 'text',
            'default'     => __( 'International Shipping', 'powerup-b2c' ),
          ),
          'carrier' => array(
            'title'   => __( 'Carrier', 'powerup-b2c' ),
            'type'    => 'select',
            'default' => '4px',
            'options' => array(
              '4px'      => '4PX',
              'yanwen'   => 'Yanwen',
              'yunexpress' => 'YunExpress',
            ),
          ),
          'first_weight' => array(
            'title'       => __( 'First Weight (kg)', 'powerup-b2c' ),
            'type'        => 'number',
            'default'     => '0.5',
            'custom_attributes' => array( 'step' => '0.1', 'min' => '0.1' ),
          ),
          'first_cost' => array(
            'title'       => __( 'First Cost', 'powerup-b2c' ),
            'type'        => 'number',
            'default'     => '8',
            'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
          ),
          'additional_weight' => array(
            'title'       => __( 'Additional Weight Unit (kg)', 'powerup-b2c' ),
            'type'        => 'number',
            'default'     => '0.5',
            'custom_attributes' => array( 'step' => '0.1', 'min' => '0.1' ),
          ),
          'additional_cost' => array(
            'title'       => __( 'Additional Cost', 'powerup-b2c' ),
            'type'        => 'number',
            'default'     => '4',
            'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
          ),
          'remote_countries' => array(
            'title'       => __( 'Remote Countries', 'powerup-b2c' ),
            'type'        => 'text',
            'default'     => 'IS,GL,RE',
            'description' => __( '逗号分隔国家代码，例如 IS,GL,RE', 'powerup-b2c' ),
          ),
          'remote_surcharge' => array(
            'title'       => __( 'Remote Surcharge', 'powerup-b2c' ),
            'type'        => 'number',
            'default'     => '6',
            'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
          ),
        );

        $this->title = $this->get_option( 'title', __( 'International Shipping', 'powerup-b2c' ) );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
      }

      public function calculate_shipping( $package = array() ) {
        $total_weight = 0.0;

        if ( ! empty( $package['contents'] ) && is_array( $package['contents'] ) ) {
          foreach ( $package['contents'] as $item ) {
            if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
              continue;
            }
            $qty = isset( $item['quantity'] ) ? (float) $item['quantity'] : 1;
            $weight = (float) $item['data']->get_weight();
            $total_weight += ( $weight > 0 ? $weight : 0.2 ) * $qty;
          }
        }

        $first_weight      = (float) $this->get_option( 'first_weight', 0.5 );
        $first_cost        = (float) $this->get_option( 'first_cost', 8 );
        $additional_weight = (float) $this->get_option( 'additional_weight', 0.5 );
        $additional_cost   = (float) $this->get_option( 'additional_cost', 4 );
        $remote_surcharge  = (float) $this->get_option( 'remote_surcharge', 0 );

        $cost = $first_cost;

        if ( $total_weight > $first_weight && $additional_weight > 0 ) {
          $extra = $total_weight - $first_weight;
          $units = (int) ceil( $extra / $additional_weight );
          $cost += $units * $additional_cost;
        }

        $country_code = isset( $package['destination']['country'] ) ? strtoupper( $package['destination']['country'] ) : '';
        $remote_list  = array_map( 'trim', explode( ',', (string) $this->get_option( 'remote_countries', '' ) ) );

        if ( $country_code && in_array( $country_code, array_map( 'strtoupper', $remote_list ), true ) ) {
          $cost += $remote_surcharge;
        }

        $carrier = strtoupper( (string) $this->get_option( 'carrier', '4px' ) );

        $this->add_rate(
          array(
            'id'    => $this->get_rate_id(),
            'label' => $carrier . ' - ' . $this->title,
            'cost'  => max( 0, $cost ),
          )
        );
      }
    }
  }

  public function register_shipping_method( $methods ) {
    $methods['powerup_carrier_shipping'] = 'WC_PowerUp_Carrier_Shipping';
    return $methods;
  }
}
