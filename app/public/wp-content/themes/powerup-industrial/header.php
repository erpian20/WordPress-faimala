<?php
/**
 * The header for our theme
 *
 * @package PowerUp_Theme
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
  <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'powerup-theme' ); ?></a>
  <?php
  $shop_url       = function_exists( 'powerup_theme_get_shop_url' ) ? powerup_theme_get_shop_url() : home_url( '/shop/' );
  $about_url      = function_exists( 'powerup_theme_get_about_page_url' ) ? powerup_theme_get_about_page_url() : home_url( '/about-us/' );
  $contact_url    = function_exists( 'powerup_theme_get_contact_page_url' ) ? powerup_theme_get_contact_page_url() : home_url( '/contact-us/' );
  $blog_url       = function_exists( 'powerup_theme_get_blog_page_url' ) ? powerup_theme_get_blog_page_url() : home_url( '/blog/' );
  $battery_url    = function_exists( 'powerup_theme_get_battery_compatibility_page_url' ) ? powerup_theme_get_battery_compatibility_page_url() : home_url( '/battery-compatibility/' );
  $language_items = function_exists( 'powerup_theme_get_language_switcher_items' ) ? powerup_theme_get_language_switcher_items() : array();
  $currency_items = function_exists( 'powerup_theme_get_currency_switcher_items' ) ? powerup_theme_get_currency_switcher_items() : array();

  $about_page_id   = function_exists( 'powerup_theme_get_page_id_by_template' ) ? powerup_theme_get_page_id_by_template( 'page-about.php' ) : 0;
  $contact_page_id = function_exists( 'powerup_theme_get_page_id_by_template' ) ? powerup_theme_get_page_id_by_template( 'page-contact.php' ) : 0;
  $blog_page_id    = (int) get_option( 'page_for_posts' );
  if ( $blog_page_id <= 0 && function_exists( 'powerup_theme_get_page_id_by_template' ) ) {
    $blog_page_id = powerup_theme_get_page_id_by_template( 'page-blog.php' );
  }
  ?>
  <header class="site-header">
    <div class="header-inner">
      <div class="site-branding">
        <a class="logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
      </div>

      <button class="menu-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'powerup-theme' ); ?>">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav class="site-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'powerup-theme' ); ?>">
        <ul class="nav-menu">
          <?php $is_home_active = is_front_page() || ( is_home() && ! is_page() ); ?>
          <li class="<?php echo $is_home_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'powerup-theme' ); ?></a></li>

          <?php $is_shop_active = ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product' ) && is_product() ) || ( function_exists( 'is_product_category' ) && is_product_category() ) || ( function_exists( 'is_product_tag' ) && is_product_tag() ) || is_page( 'shop' ); ?>
          <li class="<?php echo $is_shop_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop', 'powerup-theme' ); ?></a></li>

          <?php $is_battery_active = is_page( 'battery-compatibility' ) || is_page( 'chainsaw-series' ); ?>
          <li class="<?php echo $is_battery_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( $battery_url ); ?>"><?php esc_html_e( 'Battery Guide', 'powerup-theme' ); ?></a></li>

          <?php $is_about_active = $about_page_id > 0 ? is_page( $about_page_id ) : is_page( 'about-us' ); ?>
          <li class="<?php echo $is_about_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About Us', 'powerup-theme' ); ?></a></li>
          <?php $is_contact_active = $contact_page_id > 0 ? is_page( $contact_page_id ) : is_page( 'contact-us' ); ?>
          <li class="<?php echo $is_contact_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Us', 'powerup-theme' ); ?></a></li>

          <?php $is_blog_active = ( $blog_page_id > 0 ? is_page( $blog_page_id ) : is_page( 'blog' ) ) || ( is_home() && ! is_front_page() ) || is_singular( 'post' ) || is_category() || is_tag(); ?>
          <li class="<?php echo $is_blog_active ? 'current-menu-item' : ''; ?>"><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'powerup-theme' ); ?></a></li>
        </ul>
      </nav>

      <div class="header-actions">
        <?php if ( ! empty( $language_items ) ) : ?>
          <div class="lang-switch" aria-label="<?php esc_attr_e( 'Language switcher', 'powerup-theme' ); ?>">
            <?php foreach ( $language_items as $language_item ) : ?>
              <?php
              $language_code = isset( $language_item['code'] ) ? strtoupper( (string) $language_item['code'] ) : '';
              $language_url  = isset( $language_item['url'] ) ? (string) $language_item['url'] : '';
              if ( '' === $language_code || '' === $language_url ) {
                continue;
              }
              ?>
              <a href="<?php echo esc_url( $language_url ); ?>"<?php echo ! empty( $language_item['current'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $language_code ); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ( ! empty( $currency_items ) ) : ?>
          <div class="currency-switch" aria-label="<?php esc_attr_e( 'Currency switcher', 'powerup-theme' ); ?>">
            <?php foreach ( $currency_items as $currency_item ) : ?>
              <?php
              $currency_code = isset( $currency_item['code'] ) ? strtoupper( (string) $currency_item['code'] ) : '';
              $currency_url  = isset( $currency_item['url'] ) ? (string) $currency_item['url'] : '';
              if ( '' === $currency_code || '' === $currency_url ) {
                continue;
              }
              $currency_class = 'currency-switch__link';
              if ( ! empty( $currency_item['current'] ) ) {
                $currency_class .= ' is-current';
              }
              ?>
              <a class="<?php echo esc_attr( $currency_class ); ?>" href="<?php echo esc_url( $currency_url ); ?>"<?php echo ! empty( $currency_item['current'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $currency_code ); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php
        $cart_url       = function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'cart' ) > 0 ? get_permalink( wc_get_page_id( 'cart' ) ) : home_url( '/cart/' );
        $account_url    = function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'myaccount' ) > 0 ? get_permalink( wc_get_page_id( 'myaccount' ) ) : home_url( '/my-account/' );
        $cart_count     = function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
        $is_cart_active = function_exists( 'is_cart' ) && is_cart();
        $is_account_active = function_exists( 'is_account_page' ) && is_account_page();
        ?>
        <a class="cart-link <?php echo $is_cart_active ? 'current-menu-item' : ''; ?>" href="<?php echo esc_url( $cart_url ); ?>">
          <span class="cart-link__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
              <circle cx="9" cy="20" r="1.6"></circle>
              <circle cx="17" cy="20" r="1.6"></circle>
              <path d="M3 4h2l2.1 10.2a1.5 1.5 0 0 0 1.47 1.2h8.9a1.5 1.5 0 0 0 1.46-1.15L21 8H7"></path>
            </svg>
          </span>
          <span><?php esc_html_e( 'Cart', 'powerup-theme' ); ?></span>
          <span class="cart-count"><?php echo esc_html( (string) $cart_count ); ?></span>
        </a>
        <a class="cart-link <?php echo $is_account_active ? 'current-menu-item' : ''; ?>" href="<?php echo esc_url( $account_url ); ?>">
          <span><?php esc_html_e( 'My Account', 'powerup-theme' ); ?></span>
        </a>
      </div>
    </div>
  </header>
  <div id="content" class="site-content">
