<?php
/**
 * Plugin Name: PowerUp B2C Suite
 * Description: B2C cross-border toolkit for WooCommerce: multi-currency, VAT/IOSS, SEO, shipping templates, and ERP sync.
 * Version: 1.0.0
 * Author: PowerUp Dev Team
 * Requires PHP: 8.0
 * Text Domain: powerup-b2c
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

define( 'POWERUP_B2C_VERSION', '1.0.0' );
define( 'POWERUP_B2C_FILE', __FILE__ );
define( 'POWERUP_B2C_PATH', plugin_dir_path( __FILE__ ) );
define( 'POWERUP_B2C_URL', plugin_dir_url( __FILE__ ) );

require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-core.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-settings.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-currency.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-forms.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-checkout.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-seo.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-shipping.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-erp.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-marketplace.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-pdp-gallery.php';
require_once POWERUP_B2C_PATH . 'includes/class-powerup-b2c-dashboard.php';

register_activation_hook( __FILE__, array( 'PowerUp_B2C_Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PowerUp_B2C_Core', 'deactivate' ) );

PowerUp_B2C_Core::instance();
