<?php
/**
 * PowerUp Theme functions and definitions
 *
 * @package PowerUp_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

function powerup_theme_setup() {
  load_theme_textdomain( 'powerup-theme', get_template_directory() . '/languages' );

  add_theme_support( 'title-tag' );
  add_theme_support( 'custom-logo' );
  add_theme_support( 'post-thumbnails' );
  add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
  add_theme_support( 'woocommerce' );

  register_nav_menus( array(
    'primary' => esc_html__( 'Primary Menu', 'powerup-theme' ),
    'footer'  => esc_html__( 'Footer Menu', 'powerup-theme' ),
  ) );
}
add_action( 'after_setup_theme', 'powerup_theme_setup' );

function powerup_theme_get_frontend_forced_locale() {
  return (string) apply_filters( 'powerup_theme_frontend_forced_locale', 'en_US' );
}

function powerup_theme_force_frontend_locale( $locale ) {
  if ( is_admin() ) {
    return $locale;
  }

  $is_wp_cli = defined( 'WP_CLI' ) ? (bool) constant( 'WP_CLI' ) : false;

  if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $is_wp_cli ) {
    return $locale;
  }

  if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
    return $locale;
  }

  $forced_locale = powerup_theme_get_frontend_forced_locale();
  if ( '' === $forced_locale ) {
    return $locale;
  }

  return $forced_locale;
}
add_filter( 'locale', 'powerup_theme_force_frontend_locale', 20 );

function powerup_theme_get_page_id_by_template( $template_file ) {
  $template_file = trim( (string) $template_file );
  if ( '' === $template_file ) {
    return 0;
  }

  $pages = get_posts(
    array(
      'post_type'      => 'page',
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'fields'         => 'ids',
      'meta_key'       => '_wp_page_template',
      'meta_value'     => $template_file,
      'orderby'        => 'ID',
      'order'          => 'ASC',
      'no_found_rows'  => true,
    )
  );

  if ( ! empty( $pages ) ) {
    return (int) $pages[0];
  }

  return 0;
}

function powerup_theme_get_page_url_by_template_or_path( $template_file, $path, $fallback = '/' ) {
  $page_id = powerup_theme_get_page_id_by_template( $template_file );
  if ( $page_id > 0 ) {
    return get_permalink( $page_id );
  }

  $path = trim( (string) $path, '/' );
  if ( '' !== $path ) {
    $page = get_page_by_path( $path, OBJECT, 'page' );
    if ( $page instanceof WP_Post ) {
      return get_permalink( $page );
    }
  }

  return home_url( (string) $fallback );
}

function powerup_theme_get_shop_url() {
  if ( function_exists( 'wc_get_page_id' ) ) {
    $shop_id = (int) wc_get_page_id( 'shop' );
    if ( $shop_id > 0 ) {
      return get_permalink( $shop_id );
    }
  }

  return powerup_theme_get_page_url_by_template_or_path( 'page-shop.php', 'shop', '/shop/' );
}

function powerup_theme_get_about_page_url() {
  return powerup_theme_get_page_url_by_template_or_path( 'page-about.php', 'about-us', '/about-us/' );
}

function powerup_theme_get_contact_page_url() {
  return powerup_theme_get_page_url_by_template_or_path( 'page-contact.php', 'contact-us', '/contact-us/' );
}

function powerup_theme_get_blog_page_url() {
  $posts_page_id = (int) get_option( 'page_for_posts' );
  if ( $posts_page_id > 0 ) {
    return get_permalink( $posts_page_id );
  }

  return powerup_theme_get_page_url_by_template_or_path( 'page-blog.php', 'blog', '/blog/' );
}

function powerup_theme_get_language_switcher_items() {
  $items = array();

  if ( function_exists( 'pll_the_languages' ) ) {
    $languages = call_user_func(
      'pll_the_languages',
      array(
        'raw'                    => 1,
        'hide_if_empty'          => 0,
        'hide_if_no_translation' => 0,
      )
    );

    if ( is_array( $languages ) ) {
      foreach ( $languages as $language ) {
        if ( empty( $language['url'] ) ) {
          continue;
        }

        $items[] = array(
          'code'    => isset( $language['slug'] ) ? strtoupper( (string) $language['slug'] ) : '',
          'label'   => isset( $language['name'] ) ? (string) $language['name'] : '',
          'url'     => (string) $language['url'],
          'current' => ! empty( $language['current_lang'] ),
        );
      }
    }
  } elseif ( function_exists( 'icl_get_languages' ) ) {
    $languages = call_user_func( 'icl_get_languages', 'skip_missing=0&orderby=code' );

    if ( is_array( $languages ) ) {
      foreach ( $languages as $language ) {
        if ( empty( $language['url'] ) ) {
          continue;
        }

        $items[] = array(
          'code'    => isset( $language['language_code'] ) ? strtoupper( (string) $language['language_code'] ) : '',
          'label'   => isset( $language['translated_name'] ) ? (string) $language['translated_name'] : '',
          'url'     => (string) $language['url'],
          'current' => ! empty( $language['active'] ),
        );
      }
    }
  }

  return apply_filters( 'powerup_theme_language_switcher_items', $items );
}

function powerup_theme_get_currency_switcher_items() {
  $items = array();


  if ( class_exists( 'WOOCS' ) ) {
    global $WOOCS;
    $currencies = $WOOCS->get_currencies();
    $base_url = home_url( add_query_arg( null, null ) );
    $current = $WOOCS->current_currency;
    foreach ( $currencies as $currency_code => $currency_data ) {
      $items[] = array(
        'code'    => $currency_code,
        'symbol'  => isset( $currency_data['symbol'] ) ? (string) $currency_data['symbol'] : get_woocommerce_currency_symbol( $currency_code ),
        'url'     => add_query_arg( 'currency', rawurlencode( $currency_code ), $base_url ),
        'current' => '' !== $current && $currency_code === $current,
      );
    }
  }

  return apply_filters( 'powerup_theme_currency_switcher_items', $items );
}

function powerup_theme_get_runtime_config() {
  static $runtime_config = null;

  if ( null !== $runtime_config ) {
    return $runtime_config;
  }

  $runtime_config = array(
    'shop' => array(
      'products_per_page' => 9,
      'max_categories'    => 6,
      'cache_ttl'         => 10 * MINUTE_IN_SECONDS,
      'category_sync_enabled' => 1,
    ),
    'contact' => array(
      'whatsapp_number' => '',
      'whatsapp_qr_image_url' => '',
      'support_email' => 'randian5757@gmail.com',
      'support_hours' => '9:00 - 18:00',
    ),
    'about' => array(
      'prompts' => array(
        'mission'        => 'three men working with power tools and laptops in a workshop',
        'team'           => 'professional team in business attire posing for a photo',
        'chainsaw'       => 'chainsaw with orange and black design',
        'hedge_trimmer'  => 'hedge trimmer with orange and black design',
        'string_trimmer' => 'string trimmer with orange and black design',
        'leaf_blower'    => 'leaf blower with orange and black design',
      ),
    ),
    'media' => array(
      'generated_image_base_url' => '',
    ),
    'seo' => array(
      'home_description' => 'PowerUp offers cordless chainsaws, paint sprayers, trimmers, and accessories with practical buying guides and support.',
      'shop_description' => 'Browse PowerUp product categories including electric chainsaws, paint sprayers, hedge trimmers, and compatible accessories.',
      'blog_description' => 'Read practical guides about cordless outdoor tools, maintenance tips, and battery platform selection for everyday work.',
    ),
  );

  $saved_config = get_option( 'powerup_theme_runtime_config', array() );
  if ( is_array( $saved_config ) && ! empty( $saved_config ) ) {
    $runtime_config = array_replace_recursive( $runtime_config, $saved_config );
  }

  $runtime_config = apply_filters( 'powerup_theme_runtime_config', $runtime_config );

  return $runtime_config;
}

function powerup_theme_get_config_value( $path, $default = null ) {
  if ( ! is_string( $path ) || '' === $path ) {
    return $default;
  }

  $config = powerup_theme_get_runtime_config();
  $keys   = explode( '.', $path );
  $value  = $config;

  foreach ( $keys as $key ) {
    if ( ! is_array( $value ) || ! array_key_exists( $key, $value ) ) {
      return $default;
    }
    $value = $value[ $key ];
  }

  return $value;
}

function powerup_theme_get_support_email_recipients() {
  $primary_email = sanitize_email( (string) powerup_theme_get_config_value( 'contact.support_email', '' ) );
  $extra_emails  = apply_filters( 'powerup_theme_extra_support_emails', array( 'randian5858@gmail.com' ) );
  $extra_emails  = is_array( $extra_emails ) ? $extra_emails : array();

  $recipients = array();

  if ( '' !== $primary_email && is_email( $primary_email ) ) {
    $recipients[] = $primary_email;
  }

  foreach ( $extra_emails as $email ) {
    $email = sanitize_email( (string) $email );
    if ( '' === $email || ! is_email( $email ) ) {
      continue;
    }

    $recipients[] = $email;
  }

  if ( empty( $recipients ) ) {
    $admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
    if ( '' !== $admin_email && is_email( $admin_email ) ) {
      $recipients[] = $admin_email;
    }
  }

  return array_values( array_unique( $recipients ) );
}

function powerup_theme_get_runtime_config_defaults() {
  $runtime_config = powerup_theme_get_runtime_config();

  if ( is_array( $runtime_config ) ) {
    return $runtime_config;
  }

  return array();
}

function powerup_theme_sanitize_runtime_config( $input ) {
  $defaults = array(
    'shop' => array(
      'products_per_page' => 9,
      'max_categories'    => 6,
      'cache_ttl'         => 10 * MINUTE_IN_SECONDS,
      'category_sync_enabled' => 1,
    ),
    'contact' => array(
      'whatsapp_number' => '',
      'whatsapp_qr_image_url' => '',
      'support_email' => 'randian5757@gmail.com',
      'support_hours' => '9:00 - 18:00',
    ),
    'about' => array(
      'prompts' => array(
        'mission'        => 'three men working with power tools and laptops in a workshop',
        'team'           => 'professional team in business attire posing for a photo',
        'chainsaw'       => 'chainsaw with orange and black design',
        'hedge_trimmer'  => 'hedge trimmer with orange and black design',
        'string_trimmer' => 'string trimmer with orange and black design',
        'leaf_blower'    => 'leaf blower with orange and black design',
      ),
    ),
    'media' => array(
      'generated_image_base_url' => '',
    ),
    'seo' => array(
      'home_description' => 'PowerUp offers cordless chainsaws, paint sprayers, trimmers, and accessories with practical buying guides and support.',
      'shop_description' => 'Browse PowerUp product categories including electric chainsaws, paint sprayers, hedge trimmers, and compatible accessories.',
      'blog_description' => 'Read practical guides about cordless outdoor tools, maintenance tips, and battery platform selection for everyday work.',
    ),
  );

  $input = is_array( $input ) ? $input : array();

  return array(
    'shop' => array(
      'products_per_page' => max( 1, absint( $input['shop']['products_per_page'] ?? $defaults['shop']['products_per_page'] ) ),
      'max_categories'    => max( 1, absint( $input['shop']['max_categories'] ?? $defaults['shop']['max_categories'] ) ),
      'cache_ttl'         => max( 60, absint( $input['shop']['cache_ttl'] ?? $defaults['shop']['cache_ttl'] ) ),
      'category_sync_enabled' => ! empty( $input['shop']['category_sync_enabled'] ) ? 1 : 0,
    ),
    'contact' => array(
      'whatsapp_number' => sanitize_text_field( $input['contact']['whatsapp_number'] ?? $defaults['contact']['whatsapp_number'] ),
      'whatsapp_qr_image_url' => esc_url_raw( $input['contact']['whatsapp_qr_image_url'] ?? $defaults['contact']['whatsapp_qr_image_url'] ),
      'support_email' => sanitize_email( $input['contact']['support_email'] ?? $defaults['contact']['support_email'] ),
      'support_hours' => sanitize_text_field( $input['contact']['support_hours'] ?? $defaults['contact']['support_hours'] ),
    ),
    'about' => array(
      'prompts' => array(
        'mission'        => sanitize_text_field( $input['about']['prompts']['mission'] ?? $defaults['about']['prompts']['mission'] ),
        'team'           => sanitize_text_field( $input['about']['prompts']['team'] ?? $defaults['about']['prompts']['team'] ),
        'chainsaw'       => sanitize_text_field( $input['about']['prompts']['chainsaw'] ?? $defaults['about']['prompts']['chainsaw'] ),
        'hedge_trimmer'  => sanitize_text_field( $input['about']['prompts']['hedge_trimmer'] ?? $defaults['about']['prompts']['hedge_trimmer'] ),
        'string_trimmer' => sanitize_text_field( $input['about']['prompts']['string_trimmer'] ?? $defaults['about']['prompts']['string_trimmer'] ),
        'leaf_blower'    => sanitize_text_field( $input['about']['prompts']['leaf_blower'] ?? $defaults['about']['prompts']['leaf_blower'] ),
      ),
    ),
    'media' => array(
      'generated_image_base_url' => esc_url_raw( $input['media']['generated_image_base_url'] ?? $defaults['media']['generated_image_base_url'] ),
    ),
    'seo' => array(
      'home_description' => sanitize_textarea_field( $input['seo']['home_description'] ?? $defaults['seo']['home_description'] ),
      'shop_description' => sanitize_textarea_field( $input['seo']['shop_description'] ?? $defaults['seo']['shop_description'] ),
      'blog_description' => sanitize_textarea_field( $input['seo']['blog_description'] ?? $defaults['seo']['blog_description'] ),
    ),
  );
}

function powerup_theme_register_runtime_settings() {
  register_setting(
    'powerup_theme_runtime_config_group',
    'powerup_theme_runtime_config',
    array(
      'type'              => 'array',
      'sanitize_callback' => 'powerup_theme_sanitize_runtime_config',
      'default'           => array(),
    )
  );
}
add_action( 'admin_init', 'powerup_theme_register_runtime_settings' );

function powerup_theme_migrate_runtime_config_placeholders() {
  $option_key = 'powerup_theme_runtime_config';
  $config     = get_option( $option_key, null );

  if ( ! is_array( $config ) ) {
    return;
  }

  $did_change = false;

  if (
    isset( $config['contact']['support_email'] ) &&
    in_array( $config['contact']['support_email'], array( 'support@example.com', 'support@poweruptools.com' ), true )
  ) {
    $config['contact']['support_email'] = 'randian5757@gmail.com';
    $did_change = true;
  }

  if (
    isset( $config['media']['generated_image_base_url'] ) &&
    'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image' === $config['media']['generated_image_base_url']
  ) {
    $config['media']['generated_image_base_url'] = '';
    $did_change = true;
  }

  if ( $did_change ) {
    update_option( $option_key, $config, false );
  }
}
add_action( 'after_setup_theme', 'powerup_theme_migrate_runtime_config_placeholders', 20 );

function powerup_theme_get_seo_meta_image_url( $post_id ) {
  $post_id = (int) $post_id;
  if ( $post_id <= 0 ) {
    return '';
  }

  $image = get_the_post_thumbnail_url( $post_id, 'full' );

  if ( ! $image ) {
    $image = get_post_meta( $post_id, '_powerup_cover_image_url', true );
  }

  if ( ! $image ) {
    $image = get_template_directory_uri() . '/assets/images/product-placeholder.svg';
  }

  return esc_url( $image );
}

function powerup_theme_get_post_reading_time_data( $post_id ) {
  $post_id = (int) $post_id;
  if ( $post_id <= 0 ) {
    return array(
      'minutes'      => 1,
      'word_count'   => 0,
      'iso_duration' => 'PT1M',
      'label'        => powerup_theme_format_reading_time_label( 1 ),
    );
  }

  $content = (string) get_post_field( 'post_content', $post_id );
  $text    = wp_strip_all_tags( $content );

  if ( '' === trim( $text ) ) {
    $word_count = 0;
  } else {
    $word_count = str_word_count( $text );
  }

  // Average reading speed for article content.
  $minutes = max( 1, (int) ceil( $word_count / 220 ) );

  return array(
    'minutes'      => $minutes,
    'word_count'   => $word_count,
    'iso_duration' => 'PT' . $minutes . 'M',
    'label'        => powerup_theme_format_reading_time_label( $minutes ),
  );
}

function powerup_theme_format_reading_time_label( $minutes ) {
  $minutes = max( 1, (int) $minutes );
  return sprintf( _n( '%d min read', '%d min read', $minutes, 'powerup-theme' ), $minutes );
}

function powerup_theme_render_post_seo_meta_tags() {
  if ( is_admin() || ! is_singular( 'post' ) ) {
    return;
  }

  $post_id = get_queried_object_id();
  if ( ! $post_id ) {
    return;
  }

  $title = wp_strip_all_tags( get_the_title( $post_id ) );

  $excerpt = get_the_excerpt( $post_id );
  if ( ! $excerpt ) {
    $excerpt = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
  }
  $description = wp_trim_words( $excerpt, 34, '...' );
  $reading     = powerup_theme_get_post_reading_time_data( $post_id );
  $share_description = $description;

  if ( ! empty( $reading['label'] ) ) {
    $share_description .= ' ' . sprintf( __( 'Estimated reading time: %s.', 'powerup-theme' ), (string) $reading['label'] );
  }

  $canonical = get_permalink( $post_id );
  $image     = powerup_theme_get_seo_meta_image_url( $post_id );
  $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  $author    = wp_strip_all_tags( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) );
  $published = get_post_time( DATE_W3C, false, $post_id, true );
  $modified  = get_post_modified_time( DATE_W3C, false, $post_id, true );
  $locale    = str_replace( '_', '-', get_locale() );

  $category_names = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'names' ) );
  $post_tags      = get_the_tags( $post_id );

  if ( '' === $author ) {
    $author = $site_name;
  }

  if ( ! $canonical || ! $title || ! $description ) {
    return;
  }

  echo "\n";
  echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
  if ( '' !== $author ) {
    echo '<meta name="author" content="' . esc_attr( $author ) . '">' . "\n";
  }
  echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
  echo '<meta property="og:type" content="article">' . "\n";
  echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
  echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
  echo '<meta property="og:description" content="' . esc_attr( $share_description ) . '">' . "\n";
  echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
  echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
  if ( '' !== $published ) {
    echo '<meta property="article:published_time" content="' . esc_attr( $published ) . '">' . "\n";
  }
  if ( '' !== $modified ) {
    echo '<meta property="article:modified_time" content="' . esc_attr( $modified ) . '">' . "\n";
  }
  if ( '' !== $author ) {
    echo '<meta property="article:author" content="' . esc_attr( $author ) . '">' . "\n";
  }
  if ( is_array( $category_names ) && ! empty( $category_names ) ) {
    echo '<meta property="article:section" content="' . esc_attr( (string) $category_names[0] ) . '">' . "\n";
  }
  if ( is_array( $post_tags ) && ! empty( $post_tags ) ) {
    foreach ( $post_tags as $tag_item ) {
      if ( $tag_item instanceof WP_Term && ! empty( $tag_item->name ) ) {
        echo '<meta property="article:tag" content="' . esc_attr( (string) $tag_item->name ) . '">' . "\n";
      }
    }
  }

  if ( $image ) {
    echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
    echo '<meta property="og:image:alt" content="' . esc_attr( $title ) . '">' . "\n";
  }

  echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
  echo '<meta name="twitter:description" content="' . esc_attr( $share_description ) . '">' . "\n";

  if ( $image ) {
    echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
  }
}
add_action( 'wp_head', 'powerup_theme_render_post_seo_meta_tags', 1 );

function powerup_theme_get_sitewide_seo_context() {
  $context = array(
    'canonical'   => '',
    'title'       => wp_strip_all_tags( wp_get_document_title() ),
    'description' => '',
    'image'       => get_template_directory_uri() . '/assets/images/product-placeholder.svg',
    'og_type'     => 'website',
  );

  if ( is_front_page() || is_home() ) {
    $context['canonical'] = home_url( '/' );
    $context['description'] = (string) powerup_theme_get_config_value(
      'seo.home_description',
      __( 'PowerUp offers cordless chainsaws, paint sprayers, trimmers, and accessories with practical buying guides and support.', 'powerup-theme' )
    );
    return $context;
  }

  if ( is_page( 'blog' ) ) {
    $context['canonical'] = get_permalink( get_queried_object_id() );
    $context['description'] = (string) powerup_theme_get_config_value(
      'seo.blog_description',
      __( 'Read practical guides about cordless outdoor tools, maintenance tips, and battery platform selection for everyday work.', 'powerup-theme' )
    );
    return $context;
  }

  if ( function_exists( 'is_shop' ) && is_shop() ) {
    $shop_page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
    $context['canonical'] = $shop_page_id > 0 ? get_permalink( $shop_page_id ) : home_url( '/shop/' );
    $context['description'] = (string) powerup_theme_get_config_value(
      'seo.shop_description',
      __( 'Browse PowerUp product categories including electric chainsaws, paint sprayers, hedge trimmers, and compatible accessories.', 'powerup-theme' )
    );
    return $context;
  }

  if ( is_singular() ) {
    $post_id = (int) get_queried_object_id();
    if ( $post_id > 0 ) {
      $context['canonical'] = get_permalink( $post_id );
      $context['og_type']   = 'product' === get_post_type( $post_id ) ? 'product' : 'article';
      $context['image']     = powerup_theme_get_seo_meta_image_url( $post_id );

      $excerpt = get_the_excerpt( $post_id );
      if ( ! $excerpt ) {
        $excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
      }
      $context['description'] = wp_trim_words( $excerpt, 30, '...' );

      if ( '' === trim( (string) $context['description'] ) ) {
        $context['description'] = __( 'PowerUp provides practical cordless tool solutions for home and jobsite use.', 'powerup-theme' );
      }
    }
    return $context;
  }

  if ( is_page() ) {
    $post_id = (int) get_queried_object_id();
    if ( $post_id > 0 ) {
      $context['canonical'] = get_permalink( $post_id );
      $context['image']     = powerup_theme_get_seo_meta_image_url( $post_id );
      $context['description'] = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 30, '...' );

      if ( '' === trim( (string) $context['description'] ) ) {
        $context['description'] = __( 'PowerUp provides practical cordless tool solutions for home and jobsite use.', 'powerup-theme' );
      }
    }
    return $context;
  }

  return $context;
}

function powerup_theme_render_sitewide_seo_meta_tags() {
  if ( is_admin() || is_singular( 'post' ) ) {
    return;
  }

  if ( class_exists( 'WPSEO_Frontend' ) || defined( 'RANK_MATH_VERSION' ) ) {
    return;
  }

  $context = powerup_theme_get_sitewide_seo_context();

  $canonical   = isset( $context['canonical'] ) ? (string) $context['canonical'] : '';
  $title       = isset( $context['title'] ) ? (string) $context['title'] : '';
  $description = isset( $context['description'] ) ? (string) $context['description'] : '';
  $image       = isset( $context['image'] ) ? (string) $context['image'] : '';
  $og_type     = isset( $context['og_type'] ) ? (string) $context['og_type'] : 'website';

  if ( '' === $canonical || '' === $title || '' === $description ) {
    return;
  }

  $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  $locale    = str_replace( '_', '-', get_locale() );

  echo "\n";
  echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
  echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
  echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
  echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
  echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
  echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
  echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
  echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";

  if ( '' !== $image ) {
    echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
  }

  echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
  echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
}
add_action( 'wp_head', 'powerup_theme_render_sitewide_seo_meta_tags', 3 );

function powerup_theme_render_home_organization_schema() {
  if ( is_admin() || ( ! is_front_page() && ! is_home() ) ) {
    return;
  }

  $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  $home_url  = home_url( '/' );
  $logo_url  = get_site_icon_url( 512 );

  $schema = array(
    '@context' => 'https://schema.org',
    '@graph'   => array(
      array(
        '@type' => 'Organization',
        '@id'   => trailingslashit( $home_url ) . '#organization',
        'name'  => $site_name,
        'url'   => $home_url,
      ),
      array(
        '@type' => 'WebSite',
        '@id'   => trailingslashit( $home_url ) . '#website',
        'name'  => $site_name,
        'url'   => $home_url,
        'publisher' => array(
          '@id' => trailingslashit( $home_url ) . '#organization',
        ),
      ),
    ),
  );

  if ( $logo_url ) {
    $schema['@graph'][0]['logo'] = array(
      '@type' => 'ImageObject',
      'url'   => esc_url_raw( $logo_url ),
    );
  }

  echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerup_theme_render_home_organization_schema', 4 );

function powerup_theme_render_product_schema_json_ld() {
  if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() || ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  $product_id = (int) get_queried_object_id();
  $product    = wc_get_product( $product_id );

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $name        = wp_strip_all_tags( $product->get_name() );
  $url         = get_permalink( $product_id );
  $description = wp_strip_all_tags( $product->get_short_description() );
  if ( '' === $description ) {
    $description = wp_trim_words( wp_strip_all_tags( (string) $product->get_description() ), 36, '...' );
  }

  $image = powerup_theme_get_seo_meta_image_url( $product_id );
  $sku   = (string) $product->get_sku();
  $price = $product->get_price();

  $schema = array(
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $name,
    'description' => $description,
    'url'         => $url,
  );

  if ( '' !== $image ) {
    $schema['image'] = array( $image );
  }

  if ( '' !== $sku ) {
    $schema['sku'] = $sku;
  }

  if ( '' !== $price ) {
    $offers = array(
      '@type'         => 'Offer',
      'url'           => $url,
      'priceCurrency' => get_woocommerce_currency(),
      'price'         => (string) $price,
      'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      'itemCondition' => 'https://schema.org/NewCondition',
      'hasMerchantReturnPolicy' => array(
        '@type'                => 'MerchantReturnPolicy',
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays'   => 30,
        'returnMethod'         => 'https://schema.org/ReturnByMail',
        'returnFees'           => 'https://schema.org/FreeReturn',
      ),
    );

    $schema['offers'] = $offers;
  }

  $rating_value = (float) $product->get_average_rating();
  $review_count = (int) $product->get_review_count();
  if ( $rating_value > 0 && $review_count > 0 ) {
    $schema['aggregateRating'] = array(
      '@type'       => 'AggregateRating',
      'ratingValue' => (string) $rating_value,
      'reviewCount' => (string) $review_count,
    );
  }

  $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  if ( '' !== $site_name ) {
    $schema['brand'] = array(
      '@type' => 'Brand',
      'name'  => $site_name,
    );
  }

  echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerup_theme_render_product_schema_json_ld', 5 );

function powerup_theme_render_product_support_faq_schema_json_ld() {
  if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() || ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  $product_id = (int) get_queried_object_id();
  $product    = wc_get_product( $product_id );

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $shipping_text = trim( (string) get_post_meta( $product_id, '_powerup_shipping_delivery', true ) );
  if ( '' === $shipping_text ) {
    $shipping_text = __( 'Orders are typically processed within 1-2 business days, with standard shipping in 3-7 business days depending on destination.', 'powerup-theme' );
  }

  $product_name = strtolower( wp_strip_all_tags( $product->get_name() ) );
  $tool_only    = ( false !== strpos( $product_name, 'tool only' ) );

  $faq_items = array(
    array(
      '@type' => 'Question',
      'name'  => __( 'How long does shipping take?', 'powerup-theme' ),
      'acceptedAnswer' => array(
        '@type' => 'Answer',
        'text'  => $shipping_text,
      ),
    ),
    array(
      '@type' => 'Question',
      'name'  => __( 'Does this product include battery and charger?', 'powerup-theme' ),
      'acceptedAnswer' => array(
        '@type' => 'Answer',
        'text'  => $tool_only
          ? __( 'No. This listing is tool-only and does not include battery or charger unless explicitly stated.', 'powerup-theme' )
          : __( 'Please check the package contents in the product description. Kits typically include battery and charger, while tool-only listings do not.', 'powerup-theme' ),
      ),
    ),
    array(
      '@type' => 'Question',
      'name'  => __( 'What is the return and warranty policy?', 'powerup-theme' ),
      'acceptedAnswer' => array(
        '@type' => 'Answer',
        'text'  => __( 'Returns are supported within 30 days according to our return policy. Standard warranty support is 12 months from purchase date unless otherwise noted.', 'powerup-theme' ),
      ),
    ),
  );

  $schema = array(
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faq_items,
  );

  echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerup_theme_render_product_support_faq_schema_json_ld', 6 );

function powerup_theme_get_featured_blog_guide_faq_items() {
  return array(
    array(
      'question' => 'Should I choose 20V or 40V cordless outdoor tools?',
      'answer'   => '20V systems are suitable for light to medium yard tasks, while 40V systems are a better fit for heavier cutting, blowing, and mowing workloads.',
    ),
    array(
      'question' => 'How can I extend lithium-ion battery runtime and lifespan?',
      'answer'   => 'Keep batteries in a cool and dry place, avoid deep discharge, clean tools after use, and charge fully before long jobs with a spare battery on hand.',
    ),
    array(
      'question' => 'Can one battery platform be shared across multiple tools?',
      'answer'   => 'Yes. Choosing one compatible battery platform allows you to run chainsaws, trimmers, blowers, and mowers with fewer chargers and lower total cost.',
    ),
  );
}

function powerup_theme_get_related_posts_for_post( $post_id, $limit = 3 ) {
  $post_id = (int) $post_id;
  $limit   = max( 1, (int) $limit );

  if ( $post_id <= 0 ) {
    return array();
  }

  $related_category_ids = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
  $exclude_ids = array( $post_id );
  $hello_post  = get_page_by_path( 'hello-world', OBJECT, 'post' );
  if ( $hello_post instanceof WP_Post ) {
    $exclude_ids[] = (int) $hello_post->ID;
  }

  $query_args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => $limit,
    'post__not_in'        => array_values( array_unique( array_map( 'intval', $exclude_ids ) ) ),
    'ignore_sticky_posts' => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
  );

  if ( is_array( $related_category_ids ) && ! empty( $related_category_ids ) ) {
    $query_args['category__in'] = array_map( 'intval', $related_category_ids );
  }

  $related_query = new WP_Query( $query_args );
  if ( ! $related_query->have_posts() ) {
    unset( $query_args['category__in'] );
    $related_query = new WP_Query( $query_args );
  }

  $related_posts = array();

  if ( $related_query->have_posts() ) {
    while ( $related_query->have_posts() ) {
      $related_query->the_post();

      $related_id    = get_the_ID();
      $related_url   = get_permalink( $related_id );
      $related_title = wp_strip_all_tags( get_the_title( $related_id ) );

      if ( ! $related_url || '' === $related_title ) {
        continue;
      }

      $reading_data = powerup_theme_get_post_reading_time_data( $related_id );
      $thumb_url    = get_the_post_thumbnail_url( $related_id, 'medium_large' );
      if ( ! $thumb_url ) {
        $thumb_url = get_post_meta( $related_id, '_powerup_cover_image_url', true );
      }
      if ( ! $thumb_url ) {
        $thumb_url = get_template_directory_uri() . '/assets/images/product-placeholder.svg';
      }

      $related_posts[] = array(
        'id'      => $related_id,
        'url'     => $related_url,
        'title'   => $related_title,
        'date'    => get_the_date( '', $related_id ),
        'excerpt' => wp_trim_words( get_the_excerpt( $related_id ), 18, '...' ),
        'image'   => $thumb_url,
        'reading' => isset( $reading_data['label'] ) ? (string) $reading_data['label'] : powerup_theme_format_reading_time_label( 1 ),
      );
    }
  }

  wp_reset_postdata();

  return $related_posts;
}

function powerup_theme_get_related_blog_seed_payloads() {
  return array(
    array(
      'title'      => '20V vs 40V Cordless Outdoor Tools: Which Battery Platform Should You Choose?',
      'slug'       => '20v-vs-40v-cordless-outdoor-tools',
      'excerpt'    => 'A practical comparison of 20V and 40V platforms for chainsaws, trimmers, blowers, and mowers.',
      'cover_prompt'=> '20V and 40V cordless outdoor power tool batteries on a workbench with chainsaw and blower in orange black style',
      'cover_asset'=> 'assets/images/blog-cover-20v-40v.svg',
      'content'    => '<p>Choosing the right battery voltage is one of the biggest decisions for cordless yard care. In general, 20V tools are ideal for light trimming and detail work, while 40V tools are better for heavier cutting, blowing, and mowing sessions.</p><p>If your workload includes regular branch cutting, dense hedges, or larger lawns, prioritize 40V for power consistency. If portability and lower tool weight matter most, 20V is often the better daily platform.</p>',
      'categories' => array( 'Cordless Tools', 'DIY Yard Care' ),
      'tags'       => array( 'battery platform', '20v', '40v', 'cordless tools' ),
    ),
    array(
      'title'      => 'Cordless Chainsaw Maintenance Checklist: Keep Cutting Performance at Peak Level',
      'slug'       => 'cordless-chainsaw-maintenance-checklist',
      'excerpt'    => 'A step-by-step maintenance routine for chain tension, lubrication, battery care, and safe storage.',
      'cover_prompt'=> 'person maintaining cordless chainsaw chain and bar with tools on outdoor workbench orange black branding',
      'cover_asset'=> 'assets/images/blog-cover-chainsaw-maintenance.svg',
      'content'    => '<p>Consistent chainsaw performance depends on routine maintenance. Check chain tension before every session, keep bar oil topped up, and clean chips from the sprocket and bar groove after use.</p><p>For battery health, avoid full discharge, store packs in a dry and moderate environment, and recharge before long jobs. A simple weekly checklist significantly improves cutting speed, safety, and service life.</p>',
      'categories' => array( 'Outdoor Power Equipment', 'DIY Yard Care' ),
      'tags'       => array( 'chainsaw guide', 'maintenance', 'battery care', 'yard tools' ),
    ),
    array(
      'title'      => 'Leaf Blower Buying Basics: Airflow, Runtime, and Weight Explained',
      'slug'       => 'leaf-blower-buying-basics-airflow-runtime-weight',
      'excerpt'    => 'How to evaluate cordless blower specs and pick the right model for patios, lawns, and seasonal cleanup.',
      'cover_prompt'=> 'cordless leaf blower and batteries on patio with leaves in motion orange black outdoor equipment style',
      'cover_asset'=> 'assets/images/blog-cover-leaf-blower.svg',
      'content'    => '<p>When comparing cordless leaf blowers, focus on airflow balance rather than peak numbers alone. Higher airflow improves debris movement, but runtime and weight determine how practical a blower feels over longer sessions.</p><p>For compact properties, lightweight handheld models are often enough. For larger areas or wet leaves, choose stronger airflow, a larger battery pack, and variable speed control for better efficiency.</p>',
      'categories' => array( 'Outdoor Power Equipment', 'Cordless Tools' ),
      'tags'       => array( 'leaf blower', 'runtime', 'tool comparison', 'lithium-ion' ),
    ),
  );
}

function powerup_theme_sync_related_blog_seed_posts_once() {
  $payloads = powerup_theme_get_related_blog_seed_payloads();
  if ( empty( $payloads ) || ! is_array( $payloads ) ) {
    return;
  }

  foreach ( $payloads as $payload ) {
    $slug = isset( $payload['slug'] ) ? sanitize_title( (string) $payload['slug'] ) : '';
    if ( '' === $slug ) {
      continue;
    }

    $post = get_page_by_path( $slug, OBJECT, 'post' );
    $post_args = array(
      'post_type'    => 'post',
      'post_status'  => 'publish',
      'post_title'   => isset( $payload['title'] ) ? (string) $payload['title'] : $slug,
      'post_name'    => $slug,
      'post_excerpt' => isset( $payload['excerpt'] ) ? (string) $payload['excerpt'] : '',
      'post_content' => isset( $payload['content'] ) ? (string) $payload['content'] : '',
    );

    if ( $post instanceof WP_Post ) {
      $post_args['ID'] = $post->ID;
      $post_id = wp_update_post( $post_args, true );
    } else {
      $post_args['post_date']     = current_time( 'mysql' );
      $post_args['post_date_gmt'] = current_time( 'mysql', true );
      $post_id = wp_insert_post( $post_args, true );
    }

    if ( is_wp_error( $post_id ) || ! $post_id ) {
      continue;
    }

    $category_ids = array();
    $categories   = isset( $payload['categories'] ) && is_array( $payload['categories'] ) ? $payload['categories'] : array();
    foreach ( $categories as $category_name ) {
      $term = term_exists( (string) $category_name, 'category' );
      if ( ! $term ) {
        $term = wp_insert_term( (string) $category_name, 'category' );
      }
      if ( ! is_wp_error( $term ) ) {
        $category_ids[] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
      }
    }

    if ( ! empty( $category_ids ) ) {
      wp_set_post_categories( (int) $post_id, $category_ids, false );
    }

    $tags = isset( $payload['tags'] ) && is_array( $payload['tags'] ) ? $payload['tags'] : array();
    if ( ! empty( $tags ) ) {
      wp_set_post_terms( (int) $post_id, array_map( 'strval', $tags ), 'post_tag', false );
    }

    if ( ! empty( $payload['cover_asset'] ) ) {
      $cover_asset = ltrim( (string) $payload['cover_asset'], '/' );
      update_post_meta( (int) $post_id, '_powerup_cover_asset', $cover_asset );
      update_post_meta( (int) $post_id, '_powerup_cover_image_url', trailingslashit( get_template_directory_uri() ) . $cover_asset );
    }

    if ( ! empty( $payload['cover_prompt'] ) ) {
      update_post_meta( (int) $post_id, '_powerup_cover_prompt', (string) $payload['cover_prompt'] );
      if ( empty( $payload['cover_asset'] ) ) {
        update_post_meta(
          (int) $post_id,
          '_powerup_cover_image_url',
          powerup_theme_get_generated_image_url( (string) $payload['cover_prompt'], 'landscape_4_3' )
        );
      }
    }
  }
}
add_action( 'init', 'powerup_theme_sync_related_blog_seed_posts_once', 26 );

function powerup_theme_render_post_schema_json_ld() {
  if ( is_admin() || ! is_singular( 'post' ) ) {
    return;
  }

  $post_id = get_queried_object_id();
  if ( ! $post_id ) {
    return;
  }

  $canonical = get_permalink( $post_id );
  $title     = wp_strip_all_tags( get_the_title( $post_id ) );
  $image     = powerup_theme_get_seo_meta_image_url( $post_id );

  $excerpt = get_the_excerpt( $post_id );
  if ( ! $excerpt ) {
    $excerpt = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
  }
  $description = wp_trim_words( $excerpt, 34, '...' );

  if ( ! $canonical || ! $title || ! $description ) {
    return;
  }

  $author_name = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );
  if ( ! $author_name ) {
    $author_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  }

  $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
  $logo_url  = get_site_icon_url( 512 );
  $reading    = powerup_theme_get_post_reading_time_data( $post_id );

  $article_schema = array(
    '@type'            => 'Article',
    'mainEntityOfPage' => array(
      '@type' => 'WebPage',
      '@id'   => $canonical,
    ),
    'headline'         => $title,
    'description'      => $description,
    'datePublished'    => get_post_time( DATE_W3C, false, $post_id, true ),
    'dateModified'     => get_post_modified_time( DATE_W3C, false, $post_id, true ),
    'timeRequired'     => $reading['iso_duration'],
    'author'           => array(
      '@type' => 'Person',
      'name'  => $author_name,
    ),
    'publisher'        => array(
      '@type' => 'Organization',
      'name'  => $site_name,
    ),
    'inLanguage'       => str_replace( '_', '-', get_locale() ),
  );

  if ( $image ) {
    $article_schema['image'] = array( $image );
  }

  if ( $logo_url ) {
    $article_schema['publisher']['logo'] = array(
      '@type' => 'ImageObject',
      'url'   => esc_url( $logo_url ),
    );
  }

  $post_tags = get_the_tags( $post_id );
  if ( is_array( $post_tags ) && ! empty( $post_tags ) ) {
    $article_schema['keywords'] = implode( ', ', wp_list_pluck( $post_tags, 'name' ) );
  }

  $category_names = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'names' ) );
  if ( is_array( $category_names ) && ! empty( $category_names ) ) {
    $article_schema['articleSection'] = $category_names;
  }

  if ( $reading['word_count'] > 0 ) {
    $article_schema['wordCount'] = (int) $reading['word_count'];
  }

  $graph = array();
  $graph[] = $article_schema;

  $blog_url = home_url( '/blog/' );
  $graph[] = array(
    '@type'           => 'BreadcrumbList',
    'itemListElement' => array(
      array(
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => wp_strip_all_tags( get_bloginfo( 'name' ) ),
        'item'     => home_url( '/' ),
      ),
      array(
        '@type'    => 'ListItem',
        'position' => 2,
        'name'     => 'Blog',
        'item'     => $blog_url,
      ),
      array(
        '@type'    => 'ListItem',
        'position' => 3,
        'name'     => $title,
        'item'     => $canonical,
      ),
    ),
  );

  $related_posts = powerup_theme_get_related_posts_for_post( $post_id, 3 );
  if ( ! empty( $related_posts ) ) {
    $related_items = array();
    $position      = 1;

    foreach ( $related_posts as $related_post ) {
      $related_items[] = array(
        '@type'    => 'ListItem',
        'position' => $position,
        'item'     => array(
          '@type' => 'Article',
          'url'   => $related_post['url'],
          'name'  => $related_post['title'],
        ),
      );

      $position++;
    }

    if ( ! empty( $related_items ) ) {
      $graph[] = array(
        '@type'           => 'ItemList',
        'name'            => 'Related Articles',
        'itemListElement' => $related_items,
      );
    }
  }

  $featured_post = function_exists( 'powerup_theme_get_featured_blog_guide_post' ) ? powerup_theme_get_featured_blog_guide_post() : null;
  if ( $featured_post instanceof WP_Post && (int) $featured_post->ID === (int) $post_id ) {
    $faq_items = powerup_theme_get_featured_blog_guide_faq_items();
    if ( ! empty( $faq_items ) ) {
      $faq_entities = array();

      foreach ( $faq_items as $faq_item ) {
        if ( empty( $faq_item['question'] ) || empty( $faq_item['answer'] ) ) {
          continue;
        }

        $faq_entities[] = array(
          '@type'          => 'Question',
          'name'           => wp_strip_all_tags( (string) $faq_item['question'] ),
          'acceptedAnswer' => array(
            '@type' => 'Answer',
            'text'  => wp_strip_all_tags( (string) $faq_item['answer'] ),
          ),
        );
      }

      if ( ! empty( $faq_entities ) ) {
        $graph[] = array(
          '@type'      => 'FAQPage',
          'mainEntity' => $faq_entities,
        );
      }
    }
  }

  $schema = array(
    '@context' => 'https://schema.org',
    '@graph'   => $graph,
  );

  echo "\n";
  echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'powerup_theme_render_post_schema_json_ld', 2 );

function powerup_theme_add_runtime_settings_page() {
  add_theme_page(
    __( 'PowerUp Runtime Config', 'powerup-theme' ),
    __( 'PowerUp Config', 'powerup-theme' ),
    'manage_options',
    'powerup-theme-runtime-config',
    'powerup_theme_render_runtime_settings_page'
  );

  add_theme_page(
    __( 'PowerUp Health Check', 'powerup-theme' ),
    __( 'PowerUp Health', 'powerup-theme' ),
    'manage_options',
    'powerup-theme-health-check',
    'powerup_theme_render_health_check_page'
  );

  add_theme_page(
    __( 'PowerUp Leads', 'powerup-theme' ),
    __( 'PowerUp Leads', 'powerup-theme' ),
    'manage_options',
    'powerup-theme-leads',
    'powerup_theme_render_leads_page'
  );
}
add_action( 'admin_menu', 'powerup_theme_add_runtime_settings_page' );

function powerup_theme_get_series_leads() {
  $leads = get_option( 'powerup_series_leads', array() );
  return is_array( $leads ) ? $leads : array();
}

function powerup_theme_store_series_lead( $lead ) {
  if ( ! is_array( $lead ) || empty( $lead ) ) {
    return;
  }

  $leads = powerup_theme_get_series_leads();
  array_unshift( $leads, $lead );
  $leads = array_slice( $leads, 0, 100 );

  update_option( 'powerup_series_leads', $leads, false );
}

function powerup_theme_render_runtime_settings_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  $config = powerup_theme_get_runtime_config();
  ?>
  <div class="wrap">
    <h1><?php esc_html_e( 'PowerUp Theme Config', 'powerup-theme' ); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields( 'powerup_theme_runtime_config_group' ); ?>

      <h2><?php esc_html_e( 'Shop', 'powerup-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="powerup-shop-products-per-page"><?php esc_html_e( 'Products Per Page', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-shop-products-per-page" name="powerup_theme_runtime_config[shop][products_per_page]" type="number" min="1" value="<?php echo esc_attr( (string) ( $config['shop']['products_per_page'] ?? 9 ) ); ?>" class="small-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-shop-max-categories"><?php esc_html_e( 'Max Category Items', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-shop-max-categories" name="powerup_theme_runtime_config[shop][max_categories]" type="number" min="1" value="<?php echo esc_attr( (string) ( $config['shop']['max_categories'] ?? 6 ) ); ?>" class="small-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-shop-cache-ttl"><?php esc_html_e( 'Cache TTL (seconds)', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-shop-cache-ttl" name="powerup_theme_runtime_config[shop][cache_ttl]" type="number" min="60" step="60" value="<?php echo esc_attr( (string) ( $config['shop']['cache_ttl'] ?? 600 ) ); ?>" class="small-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-shop-category-sync-enabled"><?php esc_html_e( 'Auto Sync Product Category Tree', 'powerup-theme' ); ?></label></th>
          <td>
            <label for="powerup-shop-category-sync-enabled">
              <input id="powerup-shop-category-sync-enabled" name="powerup_theme_runtime_config[shop][category_sync_enabled]" type="checkbox" value="1" <?php checked( ! empty( $config['shop']['category_sync_enabled'] ) ); ?>>
              <?php esc_html_e( 'Enable theme-driven category tree sync (disable this if you want full manual category control).', 'powerup-theme' ); ?>
            </label>
          </td>
        </tr>
      </table>

      <h2><?php esc_html_e( 'Contact', 'powerup-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="powerup-contact-whatsapp-number"><?php esc_html_e( 'WhatsApp Number', 'powerup-theme' ); ?></label></th>
          <td>
            <input id="powerup-contact-whatsapp-number" name="powerup_theme_runtime_config[contact][whatsapp_number]" type="text" value="<?php echo esc_attr( (string) ( $config['contact']['whatsapp_number'] ?? '' ) ); ?>" class="regular-text" placeholder="8613800138000">
            <p class="description"><?php esc_html_e( 'Use international format without spaces, plus sign optional. Example: 8613800138000', 'powerup-theme' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-contact-whatsapp-qr"><?php esc_html_e( 'WhatsApp QR Image URL', 'powerup-theme' ); ?></label></th>
          <td>
            <input id="powerup-contact-whatsapp-qr" name="powerup_theme_runtime_config[contact][whatsapp_qr_image_url]" type="url" value="<?php echo esc_attr( (string) ( $config['contact']['whatsapp_qr_image_url'] ?? '' ) ); ?>" class="regular-text" placeholder="https://example.com/whatsapp-qr.png">
            <p class="description"><?php esc_html_e( 'Upload your QR image in Media Library and paste the image URL here.', 'powerup-theme' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-contact-email"><?php esc_html_e( 'Support Email', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-contact-email" name="powerup_theme_runtime_config[contact][support_email]" type="email" value="<?php echo esc_attr( (string) ( $config['contact']['support_email'] ?? '' ) ); ?>" class="regular-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-contact-hours"><?php esc_html_e( 'Support Hours', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-contact-hours" name="powerup_theme_runtime_config[contact][support_hours]" type="text" value="<?php echo esc_attr( (string) ( $config['contact']['support_hours'] ?? '' ) ); ?>" class="regular-text"></td>
        </tr>
      </table>

      <h2><?php esc_html_e( 'About Image Prompts', 'powerup-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="powerup-about-mission"><?php esc_html_e( 'Mission Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-mission" name="powerup_theme_runtime_config[about][prompts][mission]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['mission'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-about-team"><?php esc_html_e( 'Team Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-team" name="powerup_theme_runtime_config[about][prompts][team]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['team'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-about-chainsaw"><?php esc_html_e( 'Chainsaw Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-chainsaw" name="powerup_theme_runtime_config[about][prompts][chainsaw]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['chainsaw'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-about-hedge"><?php esc_html_e( 'Hedge Trimmer Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-hedge" name="powerup_theme_runtime_config[about][prompts][hedge_trimmer]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['hedge_trimmer'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-about-string"><?php esc_html_e( 'String Trimmer Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-string" name="powerup_theme_runtime_config[about][prompts][string_trimmer]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['string_trimmer'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-about-blower"><?php esc_html_e( 'Leaf Blower Prompt', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-about-blower" name="powerup_theme_runtime_config[about][prompts][leaf_blower]" type="text" value="<?php echo esc_attr( (string) ( $config['about']['prompts']['leaf_blower'] ?? '' ) ); ?>" class="large-text"></td>
        </tr>
      </table>

      <h2><?php esc_html_e( 'Media', 'powerup-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="powerup-generated-image-base-url"><?php esc_html_e( 'Generated Image Base URL', 'powerup-theme' ); ?></label></th>
          <td><input id="powerup-generated-image-base-url" name="powerup_theme_runtime_config[media][generated_image_base_url]" type="url" value="<?php echo esc_attr( (string) ( $config['media']['generated_image_base_url'] ?? '' ) ); ?>" class="large-text code"></td>
        </tr>
      </table>

      <h2><?php esc_html_e( 'SEO Defaults', 'powerup-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="powerup-seo-home-description"><?php esc_html_e( 'Home Description', 'powerup-theme' ); ?></label></th>
          <td>
            <textarea id="powerup-seo-home-description" name="powerup_theme_runtime_config[seo][home_description]" rows="3" class="large-text"><?php echo esc_textarea( (string) ( $config['seo']['home_description'] ?? '' ) ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Used for homepage meta description and social description.', 'powerup-theme' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-seo-shop-description"><?php esc_html_e( 'Shop Description', 'powerup-theme' ); ?></label></th>
          <td>
            <textarea id="powerup-seo-shop-description" name="powerup_theme_runtime_config[seo][shop_description]" rows="3" class="large-text"><?php echo esc_textarea( (string) ( $config['seo']['shop_description'] ?? '' ) ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Used for shop archive meta description and social description.', 'powerup-theme' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="powerup-seo-blog-description"><?php esc_html_e( 'Blog Description', 'powerup-theme' ); ?></label></th>
          <td>
            <textarea id="powerup-seo-blog-description" name="powerup_theme_runtime_config[seo][blog_description]" rows="3" class="large-text"><?php echo esc_textarea( (string) ( $config['seo']['blog_description'] ?? '' ) ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Used for blog listing page meta description and social description.', 'powerup-theme' ); ?></p>
          </td>
        </tr>
      </table>

      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

function powerup_theme_get_health_check_data() {
  $config = powerup_theme_get_runtime_config();

  $warnings = array();


  $fallback_url = home_url( '/shop/' );
  $redirect_url = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : $fallback_url;

  $nonce = isset( $_POST['powerup_pdp_callback_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_pdp_callback_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_pdp_callback_submit' ) ) {
    wp_safe_redirect( add_query_arg( 'pdp_callback', 'invalid', $redirect_url ) );
    exit;
  }

  $phone      = isset( $_POST['powerup_pdp_callback_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_pdp_callback_phone'] ) ) : '';
  $product_id = isset( $_POST['powerup_pdp_callback_product_id'] ) ? absint( wp_unslash( $_POST['powerup_pdp_callback_product_id'] ) ) : 0;

  if ( '' === $phone ) {
    wp_safe_redirect( add_query_arg( 'pdp_callback', 'missing', $redirect_url ) );
    exit;
  }

  $product        = $product_id > 0 ? wc_get_product( $product_id ) : null;
  $product_name   = $product instanceof WC_Product ? $product->get_name() : __( 'Product Detail Page', 'powerup-theme' );
  $support_emails = powerup_theme_get_support_email_recipients();
  $subject        = sprintf( __( 'PDP Callback Request: %s', 'powerup-theme' ), $product_name );
  $body_lines     = array(
    'Callback request received from product detail page.',
    '',
    'Product: ' . $product_name,
    'Phone: ' . $phone,
    'Product ID: ' . ( $product_id > 0 ? (string) $product_id : '-' ),
  );

  $sent = wp_mail( $support_emails, $subject, implode( "\n", $body_lines ) );

  powerup_theme_store_series_lead(
    array(
      'time'      => current_time( 'mysql' ),
      'source'    => 'pdp_callback',
      'product'   => $product_name,
      'name'      => __( 'Callback Request', 'powerup-theme' ),
      'email'     => '',
      'phone'     => $phone,
      'company'   => '',
      'message'   => __( 'Requested a callback from the product detail page.', 'powerup-theme' ),
      'mail_sent' => (bool) $sent,
    )
  );

  wp_safe_redirect( add_query_arg( 'pdp_callback', $sent ? 'success' : 'failed', $redirect_url ) );
  exit;

add_action( 'admin_post_powerup_pdp_callback_submit', 'powerup_theme_handle_pdp_callback_submit' );
add_action( 'admin_post_nopriv_powerup_pdp_callback_submit', 'powerup_theme_handle_pdp_callback_submit' );

  if ( ! $sitemap_ok ) {
    $warnings[] = __( 'wp-sitemap.xml is not reachable with status 200. Search engines may miss pages.', 'powerup-theme' );
  }

  $product_image_stats = array(
    'total'            => 0,
    'placeholder_like' => 0,
  );

  if ( class_exists( 'WooCommerce' ) ) {
    $product_ids = get_posts(
      array(
        'post_type'      => 'product',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
      )
    );

    $product_image_stats['total'] = is_array( $product_ids ) ? count( $product_ids ) : 0;

    if ( is_array( $product_ids ) ) {
      foreach ( $product_ids as $pid ) {
        $pid       = (int) $pid;
        $thumb_url = get_the_post_thumbnail_url( $pid, 'full' );

        if ( ! $thumb_url ) {
          $product_image_stats['placeholder_like']++;
          continue;
        }

        if ( false !== strpos( (string) $thumb_url, 'woocommerce-placeholder' ) || false !== strpos( (string) $thumb_url, 'product-placeholder' ) ) {
          $product_image_stats['placeholder_like']++;
        }
      }
    }
  }

  if ( $product_image_stats['total'] > 0 ) {
    $placeholder_ratio = $product_image_stats['placeholder_like'] / max( 1, $product_image_stats['total'] );
    if ( $placeholder_ratio >= 0.5 ) {
      $warnings[] = __( 'More than half of product images are placeholders. Replace key product images before launch.', 'powerup-theme' );
    }
  }

  return array(
    'environment' => array(
      'wordpress_version' => get_bloginfo( 'version' ),
      'theme_version'     => wp_get_theme()->get( 'Version' ),
      'php_version'       => PHP_VERSION,
      'woocommerce'       => class_exists( 'WooCommerce' ),
      'marketplace_plugin'=> class_exists( 'PowerUp_B2C_Marketplace' ),
      'gallery_plugin'    => class_exists( 'PowerUp_B2C_PDP_Gallery' ),
    ),
    'cache' => array(
      'shop_cache_version' => powerup_theme_get_shop_cache_version(),
      'hero_cached'        => false !== get_transient( 'powerup_shop_hero_image_v' . powerup_theme_get_shop_cache_version() ),
      'category_cached'    => false !== get_transient( 'powerup_shop_category_items_v' . powerup_theme_get_shop_cache_version() ),
    ),
    'seo_technical' => array(
      'robots_url'   => $robots_url,
      'robots_ok'    => $robots_ok,
      'sitemap_url'  => $sitemap_url,
      'sitemap_ok'   => $sitemap_ok,
      'products_total' => $product_image_stats['total'],
      'products_with_placeholder_images' => $product_image_stats['placeholder_like'],
    ),
    'config' => array(
      'shop_products_per_page' => $config['shop']['products_per_page'] ?? null,
      'shop_max_categories'    => $config['shop']['max_categories'] ?? null,
      'shop_cache_ttl'         => $config['shop']['cache_ttl'] ?? null,
      'shop_category_sync_enabled' => $config['shop']['category_sync_enabled'] ?? 1,
      'contact_support_email'  => $config['contact']['support_email'] ?? null,
      'media_image_base_url'   => $config['media']['generated_image_base_url'] ?? null,
      'seo_home_description'   => $config['seo']['home_description'] ?? null,
      'seo_shop_description'   => $config['seo']['shop_description'] ?? null,
      'seo_blog_description'   => $config['seo']['blog_description'] ?? null,
      'series_lead_count'      => count( powerup_theme_get_series_leads() ),
    ),
    'warnings' => $warnings,
  );
}

function powerup_theme_check_url_contains_patterns( $url, $patterns ) {
  $result = array(
    'ok'      => false,
    'matches' => array(),
  );

  $response = wp_remote_get(
    $url,
    array(
      'timeout'     => 8,
      'redirection' => 2,
    )
  );

  if ( is_wp_error( $response ) ) {
    return $result;
  }

  $code = (int) wp_remote_retrieve_response_code( $response );
  if ( 200 !== $code ) {
    return $result;
  }

  $body = (string) wp_remote_retrieve_body( $response );
  if ( '' === $body ) {
    return $result;
  }

  $all_ok = true;
  foreach ( $patterns as $key => $regex ) {
    $matched = 1 === preg_match( $regex, $body );
    $result['matches'][ $key ] = $matched;
    if ( ! $matched ) {
      $all_ok = false;
    }
  }

  $result['ok'] = $all_ok;
  return $result;
}

function powerup_theme_get_release_readiness_data() {
  $cache_key = 'powerup_release_readiness_v1';
  $cached    = get_transient( $cache_key );

  if ( is_array( $cached ) && ! empty( $cached ) ) {
    return $cached;
  }

  $health  = powerup_theme_get_health_check_data();
  $results = array();

  $home_url = home_url( '/' );
  $shop_url = home_url( '/shop/' );
  $blog_url = home_url( '/blog/' );

  $results['home_meta'] = powerup_theme_check_url_contains_patterns(
    $home_url,
    array(
      'description'   => '/<meta\s+name="description"\s+content="[^"]+"/i',
      'canonical'     => '/<link\s+rel="canonical"\s+href="[^"]+"/i',
      'og_description'=> '/<meta\s+property="og:description"\s+content="[^"]+"/i',
    )
  );

  $results['shop_meta'] = powerup_theme_check_url_contains_patterns(
    $shop_url,
    array(
      'description'   => '/<meta\s+name="description"\s+content="[^"]+"/i',
      'canonical'     => '/<link\s+rel="canonical"\s+href="[^"]+"/i',
      'og_description'=> '/<meta\s+property="og:description"\s+content="[^"]+"/i',
    )
  );

  $results['blog_meta'] = powerup_theme_check_url_contains_patterns(
    $blog_url,
    array(
      'description'   => '/<meta\s+name="description"\s+content="[^"]+"/i',
      'canonical'     => '/<link\s+rel="canonical"\s+href="[^"]+"/i',
      'og_description'=> '/<meta\s+property="og:description"\s+content="[^"]+"/i',
    )
  );

  $first_product_id = 0;
  if ( class_exists( 'WooCommerce' ) ) {
    $ids = wc_get_products(
      array(
        'status' => 'publish',
        'limit'  => 1,
        'return' => 'ids',
      )
    );
    if ( is_array( $ids ) && ! empty( $ids ) ) {
      $first_product_id = (int) $ids[0];
    }
  }

  if ( $first_product_id > 0 ) {
    $product_url = get_permalink( $first_product_id );
    if ( $product_url ) {
      $results['product_schema'] = powerup_theme_check_url_contains_patterns(
        $product_url,
        array(
          'product_type'   => '/"@type":"Product"/i',
          'offer'          => '/"@type":"Offer"/i',
          'faq_page'       => '/"@type":"FAQPage"/i',
        )
      );
    }
  }

  $checks = array(
    'robots_reachable'  => ! empty( $health['seo_technical']['robots_ok'] ),
    'sitemap_reachable' => ! empty( $health['seo_technical']['sitemap_ok'] ),
    'home_meta'         => ! empty( $results['home_meta']['ok'] ),
    'shop_meta'         => ! empty( $results['shop_meta']['ok'] ),
    'blog_meta'         => ! empty( $results['blog_meta']['ok'] ),
    'product_schema'    => ! empty( $results['product_schema']['ok'] ),
  );

  $passed = 0;
  foreach ( $checks as $check_ok ) {
    if ( $check_ok ) {
      $passed++;
    }
  }

  $total_checks = count( $checks );
  $score_ratio  = $total_checks > 0 ? ( $passed / $total_checks ) : 0;
  $warning_count = isset( $health['warnings'] ) && is_array( $health['warnings'] ) ? count( $health['warnings'] ) : 0;

  $risk_level = 'high';
  if ( $score_ratio >= 0.9 && 0 === $warning_count ) {
    $risk_level = 'low';
  } elseif ( $score_ratio >= 0.7 ) {
    $risk_level = 'medium';
  }

  $risk_labels = array(
    'low'    => array( 'zh' => '低风险', 'en' => 'Low Risk' ),
    'medium' => array( 'zh' => '中风险', 'en' => 'Medium Risk' ),
    'high'   => array( 'zh' => '高风险', 'en' => 'High Risk' ),
  );

  $data = array(
    'time'    => current_time( 'mysql' ),
    'checks'  => $checks,
    'passed'  => $passed,
    'total'   => $total_checks,
    'risk_level' => $risk_level,
    'risk_label' => isset( $risk_labels[ $risk_level ] ) ? $risk_labels[ $risk_level ] : $risk_labels['high'],
    'details' => $results,
    'health'  => $health,
  );

  set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );

  return $data;
}

function powerup_theme_export_release_readiness_report_lines( $readiness ) {
  $lines   = array();
  $checks  = isset( $readiness['checks'] ) && is_array( $readiness['checks'] ) ? $readiness['checks'] : array();
  $health  = isset( $readiness['health'] ) && is_array( $readiness['health'] ) ? $readiness['health'] : array();
  $recommendations = powerup_theme_get_release_readiness_recommendations( $readiness );

  $lines[] = 'PowerUp Release Readiness Report';
  $lines[] = 'PowerUp 发布就绪检查报告';
  $lines[] = 'Generated: ' . (string) ( $readiness['time'] ?? current_time( 'mysql' ) );
  $lines[] = '生成时间: ' . (string) ( $readiness['time'] ?? current_time( 'mysql' ) );
  $lines[] = 'Score: ' . (string) ( $readiness['passed'] ?? 0 ) . '/' . (string) ( $readiness['total'] ?? 0 );
  $lines[] = '评分: ' . (string) ( $readiness['passed'] ?? 0 ) . '/' . (string) ( $readiness['total'] ?? 0 );
  $risk_label = isset( $readiness['risk_label'] ) && is_array( $readiness['risk_label'] ) ? $readiness['risk_label'] : array( 'zh' => '高风险', 'en' => 'High Risk' );
  $lines[] = 'Risk: ' . (string) ( $risk_label['en'] ?? 'High Risk' );
  $lines[] = '风险等级: ' . (string) ( $risk_label['zh'] ?? '高风险' );
  $lines[] = '';
  $lines[] = 'Checks:';
  $lines[] = '检查项:';

  foreach ( $checks as $name => $ok ) {
    $lines[] = '- ' . $name . ': ' . ( $ok ? 'PASS' : 'FAIL' );
  }

  $lines[] = '';
  $lines[] = 'Warnings:';
  $lines[] = '告警:';
  $warnings = isset( $health['warnings'] ) && is_array( $health['warnings'] ) ? $health['warnings'] : array();
  if ( empty( $warnings ) ) {
    $lines[] = '- none';
  } else {
    foreach ( $warnings as $warning ) {
      $lines[] = '- ' . wp_strip_all_tags( (string) $warning );
    }
  }

  $lines[] = '';
  $lines[] = 'SEO Technical:';
  $lines[] = 'SEO 技术项:';
  $tech = isset( $health['seo_technical'] ) && is_array( $health['seo_technical'] ) ? $health['seo_technical'] : array();
  $lines[] = '- robots: ' . ( ! empty( $tech['robots_ok'] ) ? 'reachable' : 'not reachable' ) . ' (' . (string) ( $tech['robots_url'] ?? '' ) . ')';
  $lines[] = '- sitemap: ' . ( ! empty( $tech['sitemap_ok'] ) ? 'reachable' : 'not reachable' ) . ' (' . (string) ( $tech['sitemap_url'] ?? '' ) . ')';
  $lines[] = '- products total: ' . (string) ( $tech['products_total'] ?? 0 );
  $lines[] = '- placeholder-like product images: ' . (string) ( $tech['products_with_placeholder_images'] ?? 0 );

  $lines[] = '';
  $lines[] = 'Recommended Fixes:';
  $lines[] = '建议修复项:';
  if ( empty( $recommendations ) ) {
    $lines[] = '- none / 无';
  } else {
    foreach ( $recommendations as $item ) {
      $lines[] = '- ' . wp_strip_all_tags( (string) $item );
    }
  }

  return $lines;
}

function powerup_theme_get_release_readiness_recommendations( $readiness ) {
  $checks = isset( $readiness['checks'] ) && is_array( $readiness['checks'] ) ? $readiness['checks'] : array();
  $health = isset( $readiness['health'] ) && is_array( $readiness['health'] ) ? $readiness['health'] : array();
  $tech   = isset( $health['seo_technical'] ) && is_array( $health['seo_technical'] ) ? $health['seo_technical'] : array();

  $items = array();

  if ( isset( $checks['robots_reachable'] ) && ! $checks['robots_reachable'] ) {
    $items[] = 'Fix robots.txt accessibility. Ensure it returns 200 and includes the sitemap URL.';
  }
  if ( isset( $checks['sitemap_reachable'] ) && ! $checks['sitemap_reachable'] ) {
    $items[] = 'Fix sitemap accessibility. Ensure /wp-sitemap.xml returns 200.';
  }
  if ( isset( $checks['home_meta'] ) && ! $checks['home_meta'] ) {
    $items[] = 'Complete homepage SEO meta fields in PowerUp Config (description, OG description, canonical).';
  }
  if ( isset( $checks['shop_meta'] ) && ! $checks['shop_meta'] ) {
    $items[] = 'Complete shop SEO meta fields and verify canonical URL consistency.';
  }
  if ( isset( $checks['blog_meta'] ) && ! $checks['blog_meta'] ) {
    $items[] = 'Complete blog listing SEO meta fields in PowerUp Config.';
  }
  if ( isset( $checks['product_schema'] ) && ! $checks['product_schema'] ) {
    $items[] = 'Verify product JSON-LD output includes Product, Offer, and FAQPage blocks.';
  }

  $products_total = (int) ( $tech['products_total'] ?? 0 );
  $placeholder_count = (int) ( $tech['products_with_placeholder_images'] ?? 0 );
  if ( $products_total > 0 && $placeholder_count > 0 ) {
    $ratio = $placeholder_count / max( 1, $products_total );
    if ( $ratio >= 0.5 ) {
      $items[] = 'Replace placeholder product images for top-selling SKUs first (target under 20%).';
    }
  }

  $warnings = isset( $health['warnings'] ) && is_array( $health['warnings'] ) ? $health['warnings'] : array();
  foreach ( $warnings as $warning ) {
    if ( false !== stripos( (string) $warning, 'WP_ENVIRONMENT_TYPE' ) ) {
      $items[] = 'Switch WP_ENVIRONMENT_TYPE from local before production deployment.';
    }
    if ( false !== stripos( (string) $warning, 'Database user is root' ) ) {
      $items[] = 'Use dedicated production DB credentials instead of root.';
    }
  }

  return array_values( array_unique( $items ) );
}

function powerup_theme_get_release_readiness_fix_links( $readiness ) {
  $checks = isset( $readiness['checks'] ) && is_array( $readiness['checks'] ) ? $readiness['checks'] : array();
  $health = isset( $readiness['health'] ) && is_array( $readiness['health'] ) ? $readiness['health'] : array();

  $items = array();

  if ( isset( $checks['home_meta'] ) && ! $checks['home_meta'] ) {
    $items[] = array(
      'label'  => 'Edit Home SEO Fields',
      'reason' => 'Homepage meta fields are incomplete.',
      'url'    => admin_url( 'themes.php?page=powerup-theme-config' ),
    );
  }
  if ( isset( $checks['shop_meta'] ) && ! $checks['shop_meta'] ) {
    $items[] = array(
      'label'  => 'Edit Shop SEO Fields',
      'reason' => 'Shop listing meta fields are incomplete.',
      'url'    => admin_url( 'themes.php?page=powerup-theme-config' ),
    );
  }
  if ( isset( $checks['blog_meta'] ) && ! $checks['blog_meta'] ) {
    $items[] = array(
      'label'  => 'Edit Blog SEO Fields',
      'reason' => 'Blog listing meta fields are incomplete.',
      'url'    => admin_url( 'themes.php?page=powerup-theme-config' ),
    );
  }
  if ( isset( $checks['robots_reachable'] ) && ! $checks['robots_reachable'] ) {
    $items[] = array(
      'label'  => 'Check Reading Visibility',
      'reason' => 'robots.txt is not reachable or misconfigured.',
      'url'    => admin_url( 'options-reading.php' ),
    );
  }
  if ( isset( $checks['sitemap_reachable'] ) && ! $checks['sitemap_reachable'] ) {
    $items[] = array(
      'label'  => 'Check Permalink Setup',
      'reason' => 'Sitemap is not reachable; permalink rewrite may be broken.',
      'url'    => admin_url( 'options-permalink.php' ),
    );
  }
  if ( isset( $checks['product_schema'] ) && ! $checks['product_schema'] ) {
    $items[] = array(
      'label'  => 'Review Product Pages',
      'reason' => 'Product schema validation failed for sampled product pages.',
      'url'    => admin_url( 'edit.php?post_type=product' ),
    );
  }

  $warnings = isset( $health['warnings'] ) && is_array( $health['warnings'] ) ? $health['warnings'] : array();
  foreach ( $warnings as $warning ) {
    $warning_text = (string) $warning;
    if ( false !== stripos( $warning_text, 'placeholder product images' ) ) {
      $items[] = array(
        'label'  => 'Update Product Media',
        'reason' => 'Too many products still use placeholder-like images.',
        'url'    => admin_url( 'edit.php?post_type=product' ),
      );
    }
    if ( false !== stripos( $warning_text, 'Contact support email' ) ) {
      $items[] = array(
        'label'  => 'Update Support Email',
        'reason' => 'Support email is still using placeholder value.',
        'url'    => admin_url( 'themes.php?page=powerup-theme-config' ),
      );
    }
  }

  $normalized = array();
  foreach ( $items as $item ) {
    $key = (string) $item['label'] . '|' . (string) $item['url'];
    $normalized[ $key ] = $item;
  }

  return array_values( $normalized );
}

function powerup_theme_get_release_readiness_json_payload( $readiness ) {
  $checks          = isset( $readiness['checks'] ) && is_array( $readiness['checks'] ) ? $readiness['checks'] : array();
  $health          = isset( $readiness['health'] ) && is_array( $readiness['health'] ) ? $readiness['health'] : array();
  $recommendations = powerup_theme_get_release_readiness_recommendations( $readiness );
  $fix_links       = powerup_theme_get_release_readiness_fix_links( $readiness );

  return array(
    'report_name'      => 'powerup_release_readiness',
    'generated_at'     => (string) ( $readiness['time'] ?? current_time( 'mysql' ) ),
    'score'            => array(
      'passed' => (int) ( $readiness['passed'] ?? 0 ),
      'total'  => (int) ( $readiness['total'] ?? 0 ),
    ),
    'risk'             => array(
      'level' => (string) ( $readiness['risk_level'] ?? 'high' ),
      'label' => isset( $readiness['risk_label'] ) && is_array( $readiness['risk_label'] ) ? $readiness['risk_label'] : array( 'zh' => '高风险', 'en' => 'High Risk' ),
    ),
    'checks'           => $checks,
    'warnings'         => isset( $health['warnings'] ) && is_array( $health['warnings'] ) ? array_values( $health['warnings'] ) : array(),
    'seo_technical'    => isset( $health['seo_technical'] ) && is_array( $health['seo_technical'] ) ? $health['seo_technical'] : array(),
    'recommended_fixes'=> array_values( $recommendations ),
    'quick_fix_links'  => array_values( $fix_links ),
  );
}

function powerup_theme_render_health_check_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  $data                        = powerup_theme_get_health_check_data();
  $readiness                   = powerup_theme_get_release_readiness_data();
  $reference_sync_status_29474 = get_option( 'powerup_reference_product_29474_synced', false );
  $reference_sync_status_123   = get_option( 'powerup_reference_product_123_synced', false );
  $reference_sync_status_4567  = get_option( 'powerup_reference_product_4567_synced', false );
  $risk_label                  = isset( $readiness['risk_label'] ) && is_array( $readiness['risk_label'] ) ? $readiness['risk_label'] : array( 'zh' => '高风险', 'en' => 'High Risk' );
  $risk_level                  = isset( $readiness['risk_level'] ) ? (string) $readiness['risk_level'] : 'high';
  $risk_colors                 = array(
    'low'    => array( 'bg' => '#e8f7ee', 'fg' => '#1e7a3e', 'border' => '#9ed7b3' ),
    'medium' => array( 'bg' => '#fff7e6', 'fg' => '#9a6400', 'border' => '#f3d28c' ),
    'high'   => array( 'bg' => '#fdecec', 'fg' => '#9f1f1f', 'border' => '#efb3b3' ),
  );
  $risk_color                  = isset( $risk_colors[ $risk_level ] ) ? $risk_colors[ $risk_level ] : $risk_colors['high'];
  $failed_checks               = array();
  foreach ( (array) ( $readiness['checks'] ?? array() ) as $check_name => $check_ok ) {
    if ( ! $check_ok ) {
      $failed_checks[] = (string) $check_name;
    }
  }
  $recommendations             = powerup_theme_get_release_readiness_recommendations( $readiness );
  $quick_fix_links             = powerup_theme_get_release_readiness_fix_links( $readiness );
  ?>
  <div class="wrap">
    <h1><?php esc_html_e( 'PowerUp Health Check', 'powerup-theme' ); ?></h1>

    <style>
      .powerup-risk-panel {
        margin: 14px 0 18px;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid;
      }
      .powerup-risk-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid;
        font-weight: 700;
      }
      .powerup-failed-checks {
        margin: 12px 0 0;
        padding: 10px 12px;
        border-left: 4px solid #c62828;
        background: #fff7f7;
      }
      .powerup-fix-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 10px;
      }
      .powerup-fix-card {
        background: #fff;
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        padding: 10px 12px;
      }
      .powerup-fix-card p {
        margin: 6px 0 10px;
        color: #4a4f55;
      }
      .powerup-fix-card a.button {
        width: 100%;
        text-align: center;
      }
      .powerup-maintenance-group {
        margin-top: 10px;
        padding: 12px;
        border: 1px solid #d0d7de;
        border-radius: 8px;
        background: #fff;
      }
      .powerup-maintenance-group h3 {
        margin: 0 0 10px;
      }
      .powerup-maintenance-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }
    </style>

    <?php if ( isset( $_GET['powerup_ref_sync'] ) ) : ?>
      <?php if ( 'done' === $_GET['powerup_ref_sync'] ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Reference product re-sync completed.', 'powerup-theme' ); ?></p></div>
      <?php elseif ( 'forbidden' === $_GET['powerup_ref_sync'] ) : ?>
        <div class="notice notice-error"><p><?php esc_html_e( 'You do not have permission to re-sync reference product data.', 'powerup-theme' ); ?></p></div>
      <?php else : ?>
        <div class="notice notice-warning"><p><?php esc_html_e( 'Reference product re-sync did not complete. Please try again.', 'powerup-theme' ); ?></p></div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ( isset( $_GET['powerup_maintenance'] ) ) : ?>
      <?php if ( 'done' === $_GET['powerup_maintenance'] ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Maintenance task completed.', 'powerup-theme' ); ?></p></div>
      <?php elseif ( 'disabled' === $_GET['powerup_maintenance'] ) : ?>
        <div class="notice notice-warning"><p><?php esc_html_e( 'Category tree sync is currently disabled in PowerUp Config. Enable it first if you want to run category tree re-sync.', 'powerup-theme' ); ?></p></div>
      <?php elseif ( 'forbidden' === $_GET['powerup_maintenance'] ) : ?>
        <div class="notice notice-error"><p><?php esc_html_e( 'You do not have permission to run maintenance tools.', 'powerup-theme' ); ?></p></div>
      <?php else : ?>
        <div class="notice notice-warning"><p><?php esc_html_e( 'Maintenance task did not complete. Please try again.', 'powerup-theme' ); ?></p></div>
      <?php endif; ?>
    <?php endif; ?>

    <h2><?php esc_html_e( 'Launch Warnings', 'powerup-theme' ); ?></h2>
    <?php if ( empty( $data['warnings'] ) ) : ?>
      <div class="notice notice-success inline"><p><?php esc_html_e( 'No obvious launch blockers detected in theme configuration.', 'powerup-theme' ); ?></p></div>
    <?php else : ?>
      <div class="notice notice-warning inline">
        <ul style="margin: 0.5rem 0 0 1.2rem; list-style: disc;">
          <?php foreach ( $data['warnings'] as $warning ) : ?>
            <li><?php echo esc_html( $warning ); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <h2><?php esc_html_e( 'Release Readiness', 'powerup-theme' ); ?></h2>
    <div class="powerup-risk-panel" style="background: <?php echo esc_attr( $risk_color['bg'] ); ?>; color: <?php echo esc_attr( $risk_color['fg'] ); ?>; border-color: <?php echo esc_attr( $risk_color['border'] ); ?>;">
      <span class="powerup-risk-badge" style="background: <?php echo esc_attr( $risk_color['bg'] ); ?>; color: <?php echo esc_attr( $risk_color['fg'] ); ?>; border-color: <?php echo esc_attr( $risk_color['border'] ); ?>;"><?php echo esc_html( '风险等级 / Risk Level: ' . (string) $risk_label['zh'] . ' (' . (string) $risk_label['en'] . ')' ); ?></span>
      <?php if ( ! empty( $failed_checks ) ) : ?>
        <div class="powerup-failed-checks">
          <strong><?php esc_html_e( 'Priority Failed Checks', 'powerup-theme' ); ?></strong>
          <ul style="margin: 8px 0 0 16px; list-style: disc;">
            <?php foreach ( $failed_checks as $failed_check ) : ?>
              <li><?php echo esc_html( ucwords( str_replace( '_', ' ', (string) $failed_check ) ) ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
    <p>
      <?php
      echo esc_html(
        sprintf(
          /* translators: 1: passed checks 2: total checks */
          __( 'Current release score: %1$d/%2$d checks passed.', 'powerup-theme' ),
          (int) ( $readiness['passed'] ?? 0 ),
          (int) ( $readiness['total'] ?? 0 )
        )
      );
      ?>
    </p>
    <table class="widefat striped" role="presentation">
      <tbody>
        <?php foreach ( (array) ( $readiness['checks'] ?? array() ) as $check_name => $check_ok ) : ?>
          <tr>
            <td><?php echo esc_html( ucwords( str_replace( '_', ' ', (string) $check_name ) ) ); ?></td>
            <td><?php echo $check_ok ? esc_html__( 'PASS', 'powerup-theme' ) : esc_html__( 'FAIL', 'powerup-theme' ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3><?php esc_html_e( 'Recommended Fixes', 'powerup-theme' ); ?></h3>
    <?php if ( empty( $recommendations ) ) : ?>
      <p><?php esc_html_e( 'No immediate fixes recommended. Great job.', 'powerup-theme' ); ?></p>
    <?php else : ?>
      <ul style="list-style: disc; margin-left: 18px;">
        <?php foreach ( $recommendations as $item ) : ?>
          <li><?php echo esc_html( (string) $item ); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <h3><?php esc_html_e( 'Quick Fix Links', 'powerup-theme' ); ?></h3>
    <?php if ( empty( $quick_fix_links ) ) : ?>
      <p><?php esc_html_e( 'No quick-fix links required at this time.', 'powerup-theme' ); ?></p>
    <?php else : ?>
      <div class="powerup-fix-grid">
        <?php foreach ( $quick_fix_links as $fix_item ) : ?>
          <div class="powerup-fix-card">
            <strong><?php echo esc_html( (string) ( $fix_item['label'] ?? '' ) ); ?></strong>
            <p><?php echo esc_html( (string) ( $fix_item['reason'] ?? '' ) ); ?></p>
            <a class="button button-secondary" href="<?php echo esc_url( (string) ( $fix_item['url'] ?? admin_url() ) ); ?>"><?php esc_html_e( 'Open Fix Page', 'powerup-theme' ); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 10px; display: inline-block;">
      <input type="hidden" name="action" value="powerup_export_readiness_report">
      <?php wp_nonce_field( 'powerup_export_readiness_report_action', 'powerup_export_readiness_report_nonce' ); ?>
      <?php submit_button( __( 'Export Readiness Report', 'powerup-theme' ), 'primary', 'submit', false ); ?>
    </form>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 10px; display: inline-block; margin-left: 10px;">
      <input type="hidden" name="action" value="powerup_export_readiness_report_json">
      <?php wp_nonce_field( 'powerup_export_readiness_report_json_action', 'powerup_export_readiness_report_json_nonce' ); ?>
      <?php submit_button( __( 'Export Readiness Report (JSON)', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
    </form>

    <h2><?php esc_html_e( 'Environment', 'powerup-theme' ); ?></h2>
    <table class="widefat striped" role="presentation">
      <tbody>
        <tr><td><?php esc_html_e( 'WordPress Version', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['environment']['wordpress_version'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Theme Version', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['environment']['theme_version'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'PHP Version', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['environment']['php_version'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'WooCommerce', 'powerup-theme' ); ?></td><td><?php echo $data['environment']['woocommerce'] ? esc_html__( 'Available', 'powerup-theme' ) : esc_html__( 'Missing', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Marketplace Plugin', 'powerup-theme' ); ?></td><td><?php echo $data['environment']['marketplace_plugin'] ? esc_html__( 'Available', 'powerup-theme' ) : esc_html__( 'Fallback in use', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Gallery Plugin', 'powerup-theme' ); ?></td><td><?php echo $data['environment']['gallery_plugin'] ? esc_html__( 'Available', 'powerup-theme' ) : esc_html__( 'Fallback in use', 'powerup-theme' ); ?></td></tr>
      </tbody>
    </table>

    <h2><?php esc_html_e( 'Cache State', 'powerup-theme' ); ?></h2>
    <table class="widefat striped" role="presentation">
      <tbody>
        <tr><td><?php esc_html_e( 'Shop Cache Version', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['cache']['shop_cache_version'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Hero Cache', 'powerup-theme' ); ?></td><td><?php echo $data['cache']['hero_cached'] ? esc_html__( 'Warm', 'powerup-theme' ) : esc_html__( 'Cold', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Category Cache', 'powerup-theme' ); ?></td><td><?php echo $data['cache']['category_cached'] ? esc_html__( 'Warm', 'powerup-theme' ) : esc_html__( 'Cold', 'powerup-theme' ); ?></td></tr>
      </tbody>
    </table>

    <h2><?php esc_html_e( 'SEO Technical Status', 'powerup-theme' ); ?></h2>
    <table class="widefat striped" role="presentation">
      <tbody>
        <tr><td><?php esc_html_e( 'robots.txt URL', 'powerup-theme' ); ?></td><td><code><?php echo esc_html( (string) $data['seo_technical']['robots_url'] ); ?></code></td></tr>
        <tr><td><?php esc_html_e( 'robots.txt Reachable', 'powerup-theme' ); ?></td><td><?php echo $data['seo_technical']['robots_ok'] ? esc_html__( 'Yes', 'powerup-theme' ) : esc_html__( 'No', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Sitemap URL', 'powerup-theme' ); ?></td><td><code><?php echo esc_html( (string) $data['seo_technical']['sitemap_url'] ); ?></code></td></tr>
        <tr><td><?php esc_html_e( 'Sitemap Reachable', 'powerup-theme' ); ?></td><td><?php echo $data['seo_technical']['sitemap_ok'] ? esc_html__( 'Yes', 'powerup-theme' ) : esc_html__( 'No', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Product Count', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['seo_technical']['products_total'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Products Using Placeholder-like Images', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['seo_technical']['products_with_placeholder_images'] ); ?></td></tr>
      </tbody>
    </table>

    <h2><?php esc_html_e( 'Maintenance Tools', 'powerup-theme' ); ?></h2>
    <p><?php esc_html_e( 'Use these one-click tools when data looks stale after large content or category updates.', 'powerup-theme' ); ?></p>

    <div class="powerup-maintenance-group">
      <h3><?php esc_html_e( 'Cache & Performance', 'powerup-theme' ); ?></h3>
      <div class="powerup-maintenance-actions">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <input type="hidden" name="action" value="powerup_health_maintenance">
          <input type="hidden" name="tool" value="clear_shop_cache">
          <?php wp_nonce_field( 'powerup_health_maintenance_action', 'powerup_health_maintenance_nonce' ); ?>
          <?php submit_button( __( 'Clear Shop Cache', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
        </form>
      </div>
    </div>

    <div class="powerup-maintenance-group">
      <h3><?php esc_html_e( 'Catalog Sync', 'powerup-theme' ); ?></h3>
      <div class="powerup-maintenance-actions">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <input type="hidden" name="action" value="powerup_health_maintenance">
          <input type="hidden" name="tool" value="resync_category_tree">
          <?php wp_nonce_field( 'powerup_health_maintenance_action', 'powerup_health_maintenance_nonce' ); ?>
          <?php submit_button( __( 'Re-sync Product Category Tree', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <input type="hidden" name="action" value="powerup_health_maintenance">
          <input type="hidden" name="tool" value="resync_auto_categories">
          <?php wp_nonce_field( 'powerup_health_maintenance_action', 'powerup_health_maintenance_nonce' ); ?>
          <?php submit_button( __( 'Re-run Auto Category Assignment', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <input type="hidden" name="action" value="powerup_health_maintenance">
          <input type="hidden" name="tool" value="resync_reference_by_slug">
          <?php wp_nonce_field( 'powerup_health_maintenance_action', 'powerup_health_maintenance_nonce' ); ?>
          <?php submit_button( __( 'Re-sync Reference Products (by slug)', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
        </form>
      </div>
    </div>

    <h2><?php esc_html_e( 'Runtime Config Snapshot', 'powerup-theme' ); ?></h2>
    <table class="widefat striped" role="presentation">
      <tbody>
        <tr><td><?php esc_html_e( 'Shop Products Per Page', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['config']['shop_products_per_page'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Shop Max Categories', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['config']['shop_max_categories'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Shop Cache TTL', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['config']['shop_cache_ttl'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Category Tree Auto Sync', 'powerup-theme' ); ?></td><td><?php echo ! empty( $data['config']['shop_category_sync_enabled'] ) ? esc_html__( 'Enabled', 'powerup-theme' ) : esc_html__( 'Disabled', 'powerup-theme' ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Contact Support Email', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['config']['contact_support_email'] ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Generated Image Base URL', 'powerup-theme' ); ?></td><td><code><?php echo esc_html( (string) $data['config']['media_image_base_url'] ); ?></code></td></tr>
        <tr><td><?php esc_html_e( 'SEO Home Description', 'powerup-theme' ); ?></td><td><?php echo esc_html( wp_trim_words( (string) $data['config']['seo_home_description'], 18, '...' ) ); ?></td></tr>
        <tr><td><?php esc_html_e( 'SEO Shop Description', 'powerup-theme' ); ?></td><td><?php echo esc_html( wp_trim_words( (string) $data['config']['seo_shop_description'], 18, '...' ) ); ?></td></tr>
        <tr><td><?php esc_html_e( 'SEO Blog Description', 'powerup-theme' ); ?></td><td><?php echo esc_html( wp_trim_words( (string) $data['config']['seo_blog_description'], 18, '...' ) ); ?></td></tr>
        <tr><td><?php esc_html_e( 'Series Lead Count', 'powerup-theme' ); ?></td><td><?php echo esc_html( (string) $data['config']['series_lead_count'] ); ?></td></tr>
      </tbody>
    </table>

    <h2><?php esc_html_e( 'Reference Product Sync', 'powerup-theme' ); ?></h2>
    <table class="widefat striped" role="presentation">
      <tbody>
        <tr>
          <td><?php esc_html_e( 'Product 29474 Status', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_29474 ) ? esc_html__( 'Completed', 'powerup-theme' ) : esc_html__( 'Not synced', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 29474 Last Synced', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_29474 ) && ! empty( $reference_sync_status_29474['time'] ) ? esc_html( (string) $reference_sync_status_29474['time'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 29474 ID', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_29474 ) && ! empty( $reference_sync_status_29474['product_id'] ) ? esc_html( (string) $reference_sync_status_29474['product_id'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 123 Status', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_123 ) ? esc_html__( 'Completed', 'powerup-theme' ) : esc_html__( 'Not synced', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 123 Last Synced', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_123 ) && ! empty( $reference_sync_status_123['time'] ) ? esc_html( (string) $reference_sync_status_123['time'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 123 ID', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_123 ) && ! empty( $reference_sync_status_123['product_id'] ) ? esc_html( (string) $reference_sync_status_123['product_id'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 4567 Status', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_4567 ) ? esc_html__( 'Completed', 'powerup-theme' ) : esc_html__( 'Not synced', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 4567 Last Synced', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_4567 ) && ! empty( $reference_sync_status_4567['time'] ) ? esc_html( (string) $reference_sync_status_4567['time'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Product 4567 ID', 'powerup-theme' ); ?></td>
          <td><?php echo is_array( $reference_sync_status_4567 ) && ! empty( $reference_sync_status_4567['product_id'] ) ? esc_html( (string) $reference_sync_status_4567['product_id'] ) : esc_html__( 'N/A', 'powerup-theme' ); ?></td>
        </tr>
      </tbody>
    </table>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 12px; display: inline-block; margin-right: 12px;">
      <input type="hidden" name="action" value="powerup_reference_product_resync">
      <input type="hidden" name="sync_target" value="29474">
      <?php wp_nonce_field( 'powerup_reference_product_resync_action', 'powerup_reference_product_resync_nonce' ); ?>
      <?php submit_button( __( 'Re-sync Product 29474 Data', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 12px; display: inline-block;">
      <input type="hidden" name="action" value="powerup_reference_product_resync">
      <input type="hidden" name="sync_target" value="123">
      <?php wp_nonce_field( 'powerup_reference_product_resync_action', 'powerup_reference_product_resync_nonce' ); ?>
      <?php submit_button( __( 'Re-sync Product 123 Data', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 12px; display: inline-block; margin-left: 12px;">
      <input type="hidden" name="action" value="powerup_reference_product_resync">
      <input type="hidden" name="sync_target" value="4567">
      <?php wp_nonce_field( 'powerup_reference_product_resync_action', 'powerup_reference_product_resync_nonce' ); ?>
      <?php submit_button( __( 'Re-sync Product 4567 Data', 'powerup-theme' ), 'secondary', 'submit', false ); ?>
    </form>
  </div>
  <?php
}

function powerup_theme_render_leads_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  $leads = powerup_theme_get_series_leads();
  ?>
  <div class="wrap">
    <h1><?php esc_html_e( 'PowerUp Leads', 'powerup-theme' ); ?></h1>
    <p><?php esc_html_e( 'Recent submissions captured from the Chainsaw Series landing page.', 'powerup-theme' ); ?></p>

    <?php if ( empty( $leads ) ) : ?>
      <div class="notice notice-info inline"><p><?php esc_html_e( 'No landing page leads have been captured yet.', 'powerup-theme' ); ?></p></div>
    <?php else : ?>
      <table class="widefat striped" role="presentation">
        <thead>
          <tr>
            <th><?php esc_html_e( 'Time', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Name', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Email', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Phone', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Company', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Message', 'powerup-theme' ); ?></th>
            <th><?php esc_html_e( 'Mail', 'powerup-theme' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $leads as $lead ) : ?>
            <tr>
              <td><?php echo esc_html( (string) ( $lead['time'] ?? '' ) ); ?></td>
              <td><?php echo esc_html( (string) ( $lead['name'] ?? '' ) ); ?></td>
              <td><a href="mailto:<?php echo esc_attr( (string) ( $lead['email'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $lead['email'] ?? '' ) ); ?></a></td>
              <td><?php echo esc_html( (string) ( $lead['phone'] ?? '' ) ); ?></td>
              <td><?php echo esc_html( (string) ( $lead['company'] ?? '' ) ); ?></td>
              <td style="max-width: 360px;"><?php echo esc_html( (string) ( $lead['message'] ?? '' ) ); ?></td>
              <td><?php echo ! empty( $lead['mail_sent'] ) ? esc_html__( 'Sent', 'powerup-theme' ) : esc_html__( 'Failed', 'powerup-theme' ); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <?php
}

function powerup_theme_handle_reference_product_resync_action() {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_ref_sync=forbidden' ) );
    exit;
  }

  $nonce = isset( $_POST['powerup_reference_product_resync_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_reference_product_resync_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_reference_product_resync_action' ) ) {
    wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_ref_sync=failed' ) );
    exit;
  }

  $sync_target = isset( $_POST['sync_target'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_target'] ) ) : '29474';

  if ( '123' === $sync_target ) {
    delete_option( 'powerup_reference_product_123_synced' );
    delete_transient( 'powerup_reference_product_123_sync_lock' );
    powerup_theme_sync_reference_product_123_once();
    $status = get_option( 'powerup_reference_product_123_synced', false );
  } elseif ( '4567' === $sync_target ) {
    delete_option( 'powerup_reference_product_4567_synced' );
    delete_transient( 'powerup_reference_product_4567_sync_lock' );
    powerup_theme_sync_reference_product_4567_once();
    $status = get_option( 'powerup_reference_product_4567_synced', false );
  } else {
    delete_option( 'powerup_reference_product_29474_synced' );
    delete_transient( 'powerup_reference_product_29474_sync_lock' );
    powerup_theme_sync_reference_product_29474_once();
    $status = get_option( 'powerup_reference_product_29474_synced', false );
  }

  $result = is_array( $status ) ? 'done' : 'failed';

  wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_ref_sync=' . rawurlencode( $result ) ) );
  exit;
}
add_action( 'admin_post_powerup_reference_product_resync', 'powerup_theme_handle_reference_product_resync_action' );

function powerup_theme_handle_health_maintenance_action() {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_maintenance=forbidden' ) );
    exit;
  }

  $nonce = isset( $_POST['powerup_health_maintenance_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_health_maintenance_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_health_maintenance_action' ) ) {
    wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_maintenance=failed' ) );
    exit;
  }

  $tool = isset( $_POST['tool'] ) ? sanitize_text_field( wp_unslash( $_POST['tool'] ) ) : '';

  $result = 'done';

  if ( 'clear_shop_cache' === $tool ) {
    powerup_theme_clear_shop_price_range_cache();
  } elseif ( 'resync_category_tree' === $tool ) {
    if ( ! powerup_theme_is_product_category_tree_sync_enabled() ) {
      $result = 'disabled';
    } else {
      delete_option( 'powerup_product_category_tree_synced_v1' );
      powerup_theme_sync_product_category_tree_once();
    }
  } elseif ( 'resync_auto_categories' === $tool ) {
    delete_option( 'powerup_product_auto_category_assignment_v8' );
    powerup_theme_sync_product_auto_categories_once();
  } elseif ( 'resync_reference_by_slug' === $tool ) {
    delete_option( 'powerup_reference_products_by_slug_synced_v3' );
    powerup_theme_ensure_reference_products_by_slug_once();
  } else {
    wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_maintenance=failed' ) );
    exit;
  }

  wp_safe_redirect( admin_url( 'themes.php?page=powerup-theme-health-check&powerup_maintenance=' . rawurlencode( $result ) ) );
  exit;
}
add_action( 'admin_post_powerup_health_maintenance', 'powerup_theme_handle_health_maintenance_action' );

function powerup_theme_handle_export_readiness_report_action() {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to export the report.', 'powerup-theme' ) );
  }

  $nonce = isset( $_POST['powerup_export_readiness_report_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_export_readiness_report_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_export_readiness_report_action' ) ) {
    wp_die( esc_html__( 'Invalid report export request.', 'powerup-theme' ) );
  }

  $readiness = powerup_theme_get_release_readiness_data();
  $lines     = powerup_theme_export_release_readiness_report_lines( $readiness );
  $content   = implode( "\n", $lines ) . "\n";

  nocache_headers();
  header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
  header( 'Content-Disposition: attachment; filename="powerup-release-readiness-' . gmdate( 'Ymd-His' ) . '.txt"' );
  echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  exit;
}
add_action( 'admin_post_powerup_export_readiness_report', 'powerup_theme_handle_export_readiness_report_action' );

function powerup_theme_handle_export_readiness_report_json_action() {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to export the JSON report.', 'powerup-theme' ) );
  }

  $nonce = isset( $_POST['powerup_export_readiness_report_json_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_export_readiness_report_json_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_export_readiness_report_json_action' ) ) {
    wp_die( esc_html__( 'Invalid JSON report export request.', 'powerup-theme' ) );
  }

  $readiness = powerup_theme_get_release_readiness_data();
  $payload   = powerup_theme_get_release_readiness_json_payload( $readiness );

  nocache_headers();
  header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
  header( 'Content-Disposition: attachment; filename="powerup-release-readiness-' . gmdate( 'Ymd-His' ) . '.json"' );
  echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  exit;
}
add_action( 'admin_post_powerup_export_readiness_report_json', 'powerup_theme_handle_export_readiness_report_json_action' );

function powerup_theme_handle_series_lead_submit() {
  $redirect_url = function_exists( 'powerup_theme_get_reference_series_page_url' )
    ? powerup_theme_get_reference_series_page_url()
    : home_url( '/chainsaw-series/' );

  $nonce = isset( $_POST['powerup_series_lead_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_series_lead_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_series_lead_submit' ) ) {
    wp_safe_redirect( add_query_arg( 'series_lead', 'invalid', $redirect_url ) );
    exit;
  }

  $name    = isset( $_POST['series_lead_name'] ) ? sanitize_text_field( wp_unslash( $_POST['series_lead_name'] ) ) : '';
  $email   = isset( $_POST['series_lead_email'] ) ? sanitize_email( wp_unslash( $_POST['series_lead_email'] ) ) : '';
  $phone   = isset( $_POST['series_lead_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['series_lead_phone'] ) ) : '';
  $company = isset( $_POST['series_lead_company'] ) ? sanitize_text_field( wp_unslash( $_POST['series_lead_company'] ) ) : '';
  $message = isset( $_POST['series_lead_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['series_lead_message'] ) ) : '';

  if ( '' === $name || '' === $email || ! is_email( $email ) ) {
    wp_safe_redirect( add_query_arg( 'series_lead', 'missing', $redirect_url ) );
    exit;
  }

  $support_emails = powerup_theme_get_support_email_recipients();
  $subject        = sprintf( __( 'Chainsaw Series Lead: %s', 'powerup-theme' ), $name );
  $body_lines     = array(
    'Landing page lead received from Chainsaw Series page.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Phone: ' . ( '' !== $phone ? $phone : '-' ),
    'Company: ' . ( '' !== $company ? $company : '-' ),
    'Message:',
    $message,
  );
  $headers        = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

  $sent = wp_mail( $support_emails, $subject, implode( "\n", $body_lines ), $headers );

  powerup_theme_store_series_lead(
    array(
      'time'      => current_time( 'mysql' ),
      'source'    => 'series_page',
      'product'   => 'Chainsaw Series',
      'name'      => $name,
      'email'     => $email,
      'phone'     => $phone,
      'company'   => $company,
      'message'   => $message,
      'mail_sent' => (bool) $sent,
    )
  );

  wp_safe_redirect( add_query_arg( 'series_lead', $sent ? 'success' : 'failed', $redirect_url ) );
  exit;
}
add_action( 'admin_post_powerup_series_lead_submit', 'powerup_theme_handle_series_lead_submit' );
add_action( 'admin_post_nopriv_powerup_series_lead_submit', 'powerup_theme_handle_series_lead_submit' );

function powerup_theme_handle_pdp_callback_submit() {
  $fallback_url = home_url( '/shop/' );
  $redirect_url = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : $fallback_url;

  $nonce = isset( $_POST['powerup_pdp_callback_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_pdp_callback_nonce'] ) ) : '';
  if ( ! wp_verify_nonce( $nonce, 'powerup_pdp_callback_submit' ) ) {
    wp_safe_redirect( add_query_arg( 'pdp_callback', 'invalid', $redirect_url ) );
    exit;
  }

  $phone      = isset( $_POST['powerup_pdp_callback_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['powerup_pdp_callback_phone'] ) ) : '';
  $product_id = isset( $_POST['powerup_pdp_callback_product_id'] ) ? absint( wp_unslash( $_POST['powerup_pdp_callback_product_id'] ) ) : 0;

  if ( '' === $phone ) {
    wp_safe_redirect( add_query_arg( 'pdp_callback', 'missing', $redirect_url ) );
    exit;
  }

  $product        = $product_id > 0 ? wc_get_product( $product_id ) : null;
  $product_name   = $product instanceof WC_Product ? $product->get_name() : __( 'Product Detail Page', 'powerup-theme' );
  $support_emails = powerup_theme_get_support_email_recipients();
  $subject        = sprintf( __( 'PDP Callback Request: %s', 'powerup-theme' ), $product_name );
  $body_lines     = array(
    'Callback request received from product detail page.',
    '',
    'Product: ' . $product_name,
    'Phone: ' . $phone,
    'Product ID: ' . ( $product_id > 0 ? (string) $product_id : '-' ),
  );

  $sent = wp_mail( $support_emails, $subject, implode( "\n", $body_lines ) );

  powerup_theme_store_series_lead(
    array(
      'time'      => current_time( 'mysql' ),
      'source'    => 'pdp_callback',
      'product'   => $product_name,
      'name'      => __( 'Callback Request', 'powerup-theme' ),
      'email'     => '',
      'phone'     => $phone,
      'company'   => '',
      'message'   => __( 'Requested a callback from the product detail page.', 'powerup-theme' ),
      'mail_sent' => (bool) $sent,
    )
  );

  wp_safe_redirect( add_query_arg( 'pdp_callback', $sent ? 'success' : 'failed', $redirect_url ) );
  exit;
}
add_action( 'admin_post_powerup_pdp_callback_submit', 'powerup_theme_handle_pdp_callback_submit' );
add_action( 'admin_post_nopriv_powerup_pdp_callback_submit', 'powerup_theme_handle_pdp_callback_submit' );

function powerup_theme_get_featured_blog_guide_payload() {
  return array(
    'title'   => 'The Complete Guide to Lithium-Ion Cordless Outdoor Power Tools: Chainsaws, Hedge Trimmers, Blowers, Lawn Mowers & More',
    'slug'    => 'complete-guide-lithium-ion-cordless-outdoor-power-tools',
    'excerpt' => 'A practical guide to choosing, using, and maintaining lithium-ion cordless outdoor power tools, including chainsaws, hedge trimmers, leaf blowers, lawn mowers, and pruning tools.',
    'cover_asset' => 'assets/images/blog-cover-complete-guide.svg',
    'cover_prompt' => 'professional cordless outdoor power tools lineup on a clean lawn including chainsaw hedge trimmer blower mower in orange and black branding',
    'toc'     => array(
      array( 'id' => 'why-lithium-ion-tools', 'label' => 'Why Lithium-Ion Cordless Tools Outperform Gas and Corded' ),
      array( 'id' => 'cordless-chainsaw-guide', 'label' => 'Cordless Chainsaw' ),
      array( 'id' => 'cordless-hedge-trimmer-guide', 'label' => 'Cordless Hedge Trimmer' ),
      array( 'id' => 'cordless-leaf-blower-guide', 'label' => 'Cordless Leaf Blower' ),
      array( 'id' => 'cordless-lawn-mower-guide', 'label' => 'Cordless Lawn Mower' ),
      array( 'id' => 'precision-detail-tools-guide', 'label' => 'Pruning Shears and Grass Shears' ),
      array( 'id' => 'choose-the-right-tool-guide', 'label' => 'How to Choose the Right Tool' ),
      array( 'id' => 'maintenance-tips-guide', 'label' => 'Maintenance Tips' ),
      array( 'id' => 'cordless-conclusion-guide', 'label' => 'Conclusion' ),
    ),
    'content' => implode(
      "\n\n",
      array(
        '<p>Tending to your lawn, trees, and landscape no longer means wrestling with heavy gas engines, tangled cords, or noisy, smelly machines. Today\'s 40V and 20V lithium-ion cordless outdoor power tools deliver professional-grade power, zero emissions, and total freedom to work anywhere.</p>',
        '<p>From trimming branches and shaping hedges to clearing leaves and mowing lawns, a full lineup of battery-powered tools turns yard work from a chore into an efficient, quiet, and enjoyable task. This guide breaks down every essential tool so you can choose the right gear for your property.</p>',
        '<h2 id="why-lithium-ion-tools">Why Lithium-Ion Cordless Tools Outperform Gas and Corded</h2>',
        '<ul><li>Unlimited mobility with no cords and no gas cans.</li><li>Low noise and zero fumes for cleaner operation.</li><li>Instant start with one trigger.</li><li>Low maintenance with no spark plugs or fuel stabilizers.</li><li>Long runtime and fast charge on modern lithium-ion packs.</li><li>Shared battery platform across multiple tools.</li></ul>',
        '<h2 id="cordless-chainsaw-guide">1. Cordless Chainsaw: Cut Branches and Wood Safely and Easily</h2>',
        '<p>Ideal for pruning trees, cutting firewood, cleaning storm debris, and small construction projects.</p>',
        '<ul><li>Brushless motor for high torque and chain speed.</li><li>Auto-oiling and tool-free chain tensioning.</li><li>Safety lock and handguard to help reduce kickback risk.</li><li>Lightweight design for easier control.</li><li>10 to 16-inch bar options for homeowners and pros.</li></ul>',
        '<p><strong>Best for:</strong> Tree pruning, firewood cutting, fence building, garden cleanup, and emergency branch removal.</p>',
        '<h2 id="cordless-hedge-trimmer-guide">2. Cordless Hedge Trimmer: Shape Hedges Like a Pro</h2>',
        '<p>Perfect for sculpting boxwood, shrubs, and decorative hedges into clean, sharp lines.</p>',
        '<ul><li>Dual-action hardened steel blades with rust resistance.</li><li>18 to 24-inch cutting length options.</li><li>Lightweight and balanced handling for reduced fatigue.</li><li>Low vibration for more precise shaping.</li><li>Suitable for thicker branches up to around 0.75 inches.</li></ul>',
        '<p><strong>Best for:</strong> Residential hedging, garden landscaping, commercial property maintenance, and topiary work.</p>',
        '<h2 id="cordless-leaf-blower-guide">3. Cordless Leaf Blower: Clear Leaves, Dust and Debris Fast</h2>',
        '<p>Replace raking with powerful, quiet airflow to clean patios, driveways, lawns, and gutters.</p>',
        '<ul><li>Turbo mode for heavier debris.</li><li>Variable speed control.</li><li>Ergonomic handheld or backpack form factors.</li><li>Compact footprint for easier storage.</li><li>Zero emissions for year-round use.</li></ul>',
        '<p><strong>Best for:</strong> Fall leaf cleanup, patio cleaning, garage dusting, lawn clipping removal, and gutter clearing.</p>',
        '<h2 id="cordless-lawn-mower-guide">4. Cordless Lawn Mower: A Clean, Quiet Lawn Every Time</h2>',
        '<p>Modern battery mowers match gas mower power without the noise or hassle.</p>',
        '<ul><li>14 to 21-inch cutting decks.</li><li>6 to 10 height adjustments.</li><li>Brushless motor for consistent power.</li><li>Mulch, bag, or side discharge options.</li><li>Foldable design for easier storage.</li></ul>',
        '<p><strong>Best for:</strong> Small to medium yards, urban lawns, eco-friendly mowing, and noise-sensitive neighborhoods.</p>',
        '<h2 id="precision-detail-tools-guide">5. Cordless Pruning Shears and Grass Shears: Precision for Detail Work</h2>',
        '<p>Lightweight, powerful, and perfect for tight spaces.</p>',
        '<ul><li>Pruning shears can cut branches up to about 1 inch thick.</li><li>Grass shears help edge lawns and trim around flower beds.</li><li>Compact size works well in tight spaces and around delicate plants.</li></ul>',
        '<p><strong>Best for:</strong> Flower bed edging, small branch pruning, potted plant care, and detailed lawn finishing.</p>',
        '<h2 id="choose-the-right-tool-guide">How to Choose the Right Lithium-Ion Tool for Your Yard</h2>',
        '<ol><li>Battery voltage: 20V for light tasks; 40V for heavier cutting and mowing.</li><li>Battery platform: choose a brand with shared batteries to save money.</li><li>Weight and balance: lighter tools reduce fatigue on long jobs.</li><li>Runtime: look for 40+ minutes per charge and keep a spare battery ready.</li><li>Blade and bar quality: hardened steel, rust resistance, and low-friction coating matter.</li><li>Safety features: lock-off switches, handguards, and anti-kickback design are worth prioritizing.</li></ol>',
        '<h2 id="maintenance-tips-guide">Maintenance Tips for Long-Lasting Performance</h2>',
        '<ul><li>Keep blades clean and sharp for cleaner cuts and less strain.</li><li>Store batteries in a cool, dry place and avoid full discharge.</li><li>Wipe debris from motors and air vents after each use.</li><li>Charge fully before long jobs and keep a backup battery ready.</li><li>Follow the user manual for chainsaw bar and chain oiling.</li></ul>',
        '<h2 id="cordless-conclusion-guide">Conclusion: Upgrade to Cordless and Transform Your Yard Work</h2>',
        '<p>Lithium-ion outdoor power tools have redefined lawn and garden care. Whether you need a chainsaw for pruning, a hedge trimmer for shaping, a blower for cleanup, or a lawn mower for a perfect lawn, cordless tools deliver power, convenience, and eco-friendliness in one package.</p>',
        '<p>No gas, no cords, no noise. Just clean, reliable performance all year long.</p>',
        '<p><strong>Ready to Upgrade Your Yard Tools?</strong> Explore our full lineup of high-performance lithium-ion cordless outdoor power tools designed for durability, efficiency, and ease of use.</p>'
      )
    ),
    'categories' => array( 'Outdoor Power Equipment', 'DIY Yard Care', 'Cordless Tools' ),
    'tags'       => array( 'lithium-ion', 'cordless tools', 'outdoor power equipment', 'chainsaw guide', 'hedge trimmer', 'leaf blower', 'lawn mower' ),
  );
}

function powerup_theme_get_featured_blog_guide_toc() {
  $payload = powerup_theme_get_featured_blog_guide_payload();
  return isset( $payload['toc'] ) && is_array( $payload['toc'] ) ? $payload['toc'] : array();
}

function powerup_theme_get_featured_blog_guide_post() {
  $status = get_option( 'powerup_featured_blog_guide_synced', false );

  if ( is_array( $status ) && ! empty( $status['post_id'] ) ) {
    $post = get_post( (int) $status['post_id'] );
    if ( $post instanceof WP_Post && 'post' === $post->post_type ) {
      return $post;
    }
  }

  $payload = powerup_theme_get_featured_blog_guide_payload();
  $post    = get_page_by_path( $payload['slug'], OBJECT, 'post' );

  return $post instanceof WP_Post ? $post : null;
}

function powerup_theme_sync_featured_blog_guide_once() {
  $payload = powerup_theme_get_featured_blog_guide_payload();
  $post    = get_page_by_path( $payload['slug'], OBJECT, 'post' );
  $now_local = current_time( 'mysql' );
  $now_gmt   = current_time( 'mysql', true );

  $post_args = array(
    'post_type'    => 'post',
    'post_status'  => 'publish',
    'post_title'   => $payload['title'],
    'post_name'    => $payload['slug'],
    'post_excerpt' => $payload['excerpt'],
    'post_content' => $payload['content'],
  );

  if ( $post instanceof WP_Post ) {
    $post_args['ID'] = $post->ID;

    if ( 'publish' !== $post->post_status ) {
      $post_args['post_date']     = $now_local;
      $post_args['post_date_gmt'] = $now_gmt;
    }

    $post_id = wp_update_post( $post_args, true );
  } else {
    $post_args['post_date']     = $now_local;
    $post_args['post_date_gmt'] = $now_gmt;
    $post_id = wp_insert_post( $post_args, true );
  }

  if ( is_wp_error( $post_id ) || ! $post_id ) {
    return;
  }

  $category_ids = array();
  foreach ( $payload['categories'] as $category_name ) {
    $term = term_exists( $category_name, 'category' );
    if ( ! $term ) {
      $term = wp_insert_term( $category_name, 'category' );
    }
    if ( ! is_wp_error( $term ) ) {
      $category_ids[] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
    }
  }

  if ( ! empty( $category_ids ) ) {
    wp_set_post_categories( (int) $post_id, $category_ids, false );
  }

  if ( ! empty( $payload['tags'] ) && is_array( $payload['tags'] ) ) {
    wp_set_post_terms( (int) $post_id, array_map( 'strval', $payload['tags'] ), 'post_tag', false );
  }

  if ( ! empty( $payload['cover_asset'] ) ) {
    $cover_asset = ltrim( (string) $payload['cover_asset'], '/' );
    update_post_meta( (int) $post_id, '_powerup_cover_asset', $cover_asset );
    update_post_meta( (int) $post_id, '_powerup_cover_image_url', trailingslashit( get_template_directory_uri() ) . $cover_asset );
  }

  if ( ! empty( $payload['cover_prompt'] ) ) {
    update_post_meta( (int) $post_id, '_powerup_cover_prompt', (string) $payload['cover_prompt'] );
    if ( empty( $payload['cover_asset'] ) ) {
      update_post_meta(
        (int) $post_id,
        '_powerup_cover_image_url',
        powerup_theme_get_generated_image_url( (string) $payload['cover_prompt'], 'landscape_4_3' )
      );
    }
  }

  update_option(
    'powerup_featured_blog_guide_synced',
    array(
      'post_id' => (int) $post_id,
      'time'    => current_time( 'mysql' ),
    ),
    false
  );
}
add_action( 'init', 'powerup_theme_sync_featured_blog_guide_once', 25 );

function powerup_theme_scripts() {
  $theme_version = wp_get_theme()->get( 'Version' );

  $asset_version = static function( $relative_path ) use ( $theme_version ) {
    $full_path = trailingslashit( get_template_directory() ) . ltrim( $relative_path, '/' );
    if ( file_exists( $full_path ) ) {
      $mtime = filemtime( $full_path );
      if ( false !== $mtime ) {
        return (string) $mtime;
      }
    }

    return $theme_version;
  };

  wp_enqueue_style( 'powerup-style', get_stylesheet_uri(), array(), $asset_version( 'style.css' ) );
  wp_enqueue_style( 'powerup-main-style', get_template_directory_uri() . '/assets/css/style.css', array( 'powerup-style' ), $asset_version( 'assets/css/style.css' ) );
  wp_enqueue_style( 'powerup-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&display=swap', array(), null );
  if ( function_exists( 'is_product' ) && is_product() ) {
    wp_enqueue_style( 'powerup-amazon-reviews', get_template_directory_uri() . '/assets/css/amazon-reviews.css', array( 'powerup-main-style' ), $asset_version( 'assets/css/amazon-reviews.css' ) );
  }
  wp_enqueue_script( 'powerup-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $asset_version( 'assets/js/navigation.js' ), true );

  wp_enqueue_script(
    'powerup-image-fallback',
    get_template_directory_uri() . '/assets/js/image-fallback.js',
    array(),
    $asset_version( 'assets/js/image-fallback.js' ),
    true
  );

  wp_localize_script(
    'powerup-image-fallback',
    'powerupImageFallbackConfig',
    array(
      'fallbackUrl' => get_template_directory_uri() . '/assets/images/product-placeholder.svg',
    )
  );

  if ( is_page_template( 'page-shop.php' ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
    wp_enqueue_script(
      'powerup-shop-filters',
      get_template_directory_uri() . '/assets/js/shop-filters.js',
      array(),
      $theme_version,
      true
    );
  }

  if ( is_singular( 'post' ) && function_exists( 'powerup_theme_get_featured_blog_guide_post' ) ) {
    $featured_guide_post = powerup_theme_get_featured_blog_guide_post();
    $current_post_id     = (int) get_queried_object_id();

    if ( $featured_guide_post instanceof WP_Post && $current_post_id > 0 && (int) $featured_guide_post->ID === $current_post_id ) {
      wp_enqueue_script(
        'powerup-post-guide-toc',
        get_template_directory_uri() . '/assets/js/post-guide-toc.js',
        array(),
        $theme_version,
        true
      );
    }
  }
}
add_action( 'wp_enqueue_scripts', 'powerup_theme_scripts' );

function powerup_theme_exclude_shop_from_wc_coming_soon( $exclude ) {
  if ( is_admin() ) {
    return $exclude;
  }

  if ( function_exists( 'is_shop' ) && is_shop() ) {
    return true;
  }

  if ( function_exists( 'is_product' ) && is_product() ) {
    return true;
  }

  if ( function_exists( 'is_product_category' ) && is_product_category() ) {
    return true;
  }

  if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
    return true;
  }

  return $exclude;
}
add_filter( 'woocommerce_coming_soon_exclude', 'powerup_theme_exclude_shop_from_wc_coming_soon', 20 );

function powerup_theme_build_shop_normalized_query_args() {
  $normalized = array();

  $search_query = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';
  if ( '' !== $search_query ) {
    $normalized['q'] = $search_query;
  }

  $selected_categories = array();
  if ( isset( $_GET['cat'] ) ) {
    $raw_cats = wp_unslash( (array) $_GET['cat'] );
    foreach ( $raw_cats as $cat_slug ) {
      $cat_slug = sanitize_title( (string) $cat_slug );
      if ( '' !== $cat_slug ) {
        $selected_categories[] = $cat_slug;
      }
    }
  }

  $selected_categories = array_values( array_unique( $selected_categories ) );
  sort( $selected_categories, SORT_STRING );

  $allowed_category_slugs = array();
  if ( taxonomy_exists( 'product_cat' ) ) {
    $category_terms = get_terms(
      array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'fields'     => 'slugs',
      )
    );

    if ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) {
      $allowed_category_slugs = array_map( 'strval', $category_terms );
    }
  }

  if ( ! empty( $allowed_category_slugs ) ) {
    $selected_categories = array_values( array_intersect( $selected_categories, $allowed_category_slugs ) );
  }

  if ( ! empty( $selected_categories ) ) {
    $normalized['cat'] = $selected_categories;
  }

  $selected_prices = array();
  if ( isset( $_GET['price'] ) ) {
    $raw_prices = wp_unslash( (array) $_GET['price'] );
    foreach ( $raw_prices as $price_idx ) {
      $price_idx = (int) $price_idx;
      if ( $price_idx >= 0 && $price_idx <= 3 ) {
        $selected_prices[] = $price_idx;
      }
    }
  }

  $selected_prices = array_values( array_unique( $selected_prices ) );
  sort( $selected_prices, SORT_NUMERIC );
  if ( ! empty( $selected_prices ) ) {
    $normalized['price'] = $selected_prices;
  }

  $paged = isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 0;
  if ( $paged > 1 ) {
    $normalized['paged'] = $paged;
  }

  return $normalized;
}

function powerup_theme_enforce_shop_canonical_query() {
  if ( is_admin() || ! is_page_template( 'page-shop.php' ) ) {
    return;
  }

  $normalized = powerup_theme_build_shop_normalized_query_args();

  $allowed_keys = array( 'q', 'cat', 'price', 'paged' );
  $current_has_unknown = false;
  foreach ( array_keys( $_GET ) as $key ) {
    if ( ! in_array( $key, $allowed_keys, true ) ) {
      $current_has_unknown = true;
      break;
    }
  }

  $current = array();
  if ( isset( $_GET['q'] ) ) {
    $current['q'] = sanitize_text_field( wp_unslash( (string) $_GET['q'] ) );
  }

  if ( isset( $_GET['cat'] ) ) {
    $current['cat'] = array_values( array_map( 'sanitize_title', (array) wp_unslash( $_GET['cat'] ) ) );
  }

  if ( isset( $_GET['price'] ) ) {
    $current['price'] = array_values( array_map( 'intval', (array) wp_unslash( $_GET['price'] ) ) );
  }

  if ( isset( $_GET['paged'] ) ) {
    $current['paged'] = (int) $_GET['paged'];
  }

  $needs_redirect = $current_has_unknown || $current !== $normalized;
  if ( ! $needs_redirect ) {
    return;
  }

  global $wp;
  $request_path = isset( $wp->request ) ? $wp->request : '';
  $base_url     = home_url( $request_path ? user_trailingslashit( $request_path ) : '/' );
  $target_url   = empty( $normalized ) ? $base_url : add_query_arg( $normalized, $base_url );

  wp_safe_redirect( $target_url, 301 );
  exit;
}

function powerup_theme_filter_shop_query_by_category_params( $query ) {
  if ( is_admin() || ! $query instanceof WP_Query || ! $query->is_main_query() ) {
    return;
  }

  if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
    return;
  }

  if ( empty( $_GET['cat'] ) ) {
    return;
  }

  $selected_categories = array_values(
    array_filter(
      array_map(
        'sanitize_title',
        (array) wp_unslash( $_GET['cat'] )
      )
    )
  );

  if ( empty( $selected_categories ) ) {
    return;
  }

  $existing_tax_query = $query->get( 'tax_query' );
  if ( ! is_array( $existing_tax_query ) ) {
    $existing_tax_query = array();
  }

  $existing_tax_query[] = array(
    'taxonomy' => 'product_cat',
    'field'    => 'slug',
    'terms'    => $selected_categories,
  );

  if ( count( $existing_tax_query ) > 1 && ! isset( $existing_tax_query['relation'] ) ) {
    $existing_tax_query['relation'] = 'AND';
  }

  $query->set( 'tax_query', $existing_tax_query );
}

function powerup_theme_render_wc_shop_category_filters() {
  if ( ! function_exists( 'is_shop' ) || ! is_shop() || is_admin() ) {
    return;
  }

  $terms = get_terms(
    array(
      'taxonomy'   => 'product_cat',
      'hide_empty' => false,
      'orderby'    => 'name',
      'order'      => 'ASC',
    )
  );

  if ( is_wp_error( $terms ) || empty( $terms ) ) {
    return;
  }

  $selected_categories = array();
  if ( isset( $_GET['cat'] ) ) {
    $selected_categories = array_values(
      array_filter(
        array_map(
          'sanitize_title',
          (array) wp_unslash( $_GET['cat'] )
        )
      )
    );
  }

  $terms_by_parent = array();
  foreach ( $terms as $term ) {
    $parent_id = (int) $term->parent;
    if ( ! isset( $terms_by_parent[ $parent_id ] ) ) {
      $terms_by_parent[ $parent_id ] = array();
    }
    $terms_by_parent[ $parent_id ][] = $term;
  }

  $root_terms = isset( $terms_by_parent[0] ) ? $terms_by_parent[0] : array();
  if ( empty( $root_terms ) ) {
    return;
  }

  $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
  if ( ! $shop_page_url ) {
    $shop_page_url = home_url( '/shop/' );
  }

  echo '<form class="powerup-shop-tax-filter" method="get" action="' . esc_url( $shop_page_url ) . '">';
  echo '<div class="powerup-shop-tax-filter__head"><h3>' . esc_html__( 'Filter By Category', 'powerup-theme' ) . '</h3><button type="submit">' . esc_html__( 'Apply', 'powerup-theme' ) . '</button></div>';
  echo '<ul class="powerup-shop-tax-filter__list">';

  foreach ( $root_terms as $root_term ) {
    if ( 'uncategorized' === $root_term->slug ) {
      continue;
    }

    $child_terms = isset( $terms_by_parent[ (int) $root_term->term_id ] ) ? $terms_by_parent[ (int) $root_term->term_id ] : array();
    $visible_child_terms = array();
    foreach ( $child_terms as $child_term ) {
      $is_selected = in_array( $child_term->slug, $selected_categories, true );
      if ( (int) $child_term->count > 0 || $is_selected ) {
        $visible_child_terms[] = $child_term;
      }
    }

    $show_parent = ( (int) $root_term->count > 0 )
      || in_array( $root_term->slug, $selected_categories, true )
      || ! empty( $visible_child_terms );

    if ( ! $show_parent ) {
      continue;
    }

    echo '<li class="powerup-shop-tax-filter__parent">';
    echo '<label><input type="checkbox" name="cat[]" value="' . esc_attr( $root_term->slug ) . '" ' . checked( in_array( $root_term->slug, $selected_categories, true ), true, false ) . '> <span>' . esc_html( $root_term->name ) . '</span></label>';
    echo '<em>' . esc_html( (string) $root_term->count ) . '</em>';
    echo '</li>';

    foreach ( $visible_child_terms as $child_term ) {
      echo '<li class="powerup-shop-tax-filter__child">';
      echo '<label><input type="checkbox" name="cat[]" value="' . esc_attr( $child_term->slug ) . '" ' . checked( in_array( $child_term->slug, $selected_categories, true ), true, false ) . '> <span>' . esc_html( $child_term->name ) . '</span></label>';
      echo '<em>' . esc_html( (string) $child_term->count ) . '</em>';
      echo '</li>';
    }
  }

  if ( isset( $_GET['orderby'] ) ) {
    echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( (string) $_GET['orderby'] ) ) ) . '">';
  }

  echo '</ul>';
  echo '</form>';
}

function powerup_widgets_init() {
  register_sidebar( array(
    'name'          => esc_html__( 'Footer Widget Area', 'powerup-theme' ),
    'id'            => 'footer-1',
    'description'   => esc_html__( 'Widgets in this area will appear in the footer.', 'powerup-theme' ),
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h2 class="widget-title">',
    'after_title'   => '</h2>',
  ) );
}
add_action( 'widgets_init', 'powerup_widgets_init' );

function powerup_body_classes( $classes ) {
  if ( is_front_page() ) {
    $classes[] = 'home-page';
  }
  return $classes;
}
add_filter( 'body_class', 'powerup_body_classes' );

function powerup_excerpt_more( $more ) {
  return ' &hellip;';
}
add_filter( 'excerpt_more', 'powerup_excerpt_more' );

function powerup_get_product_url( $slug ) {
  if ( class_exists( 'WooCommerce' ) ) {
    $product = get_page_by_path( $slug, OBJECT, 'product' );
    if ( $product instanceof WP_Post ) {
      return get_permalink( $product );
    }
  }

  $page = get_page_by_path( $slug, OBJECT, 'page' );
  if ( $page instanceof WP_Post ) {
    return get_permalink( $page );
  }

  return home_url( '/shop/' );
}

function powerup_theme_pdp_gallery_fallback_init() {
  if ( class_exists( 'PowerUp_B2C_PDP_Gallery' ) || ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
  add_action( 'woocommerce_before_single_product_summary', 'powerup_theme_render_amazon_like_gallery', 20 );
}
add_action( 'init', 'powerup_theme_pdp_gallery_fallback_init' );

function powerup_theme_enqueue_pdp_gallery_fallback_assets() {
  if ( class_exists( 'PowerUp_B2C_PDP_Gallery' ) || ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  wp_enqueue_style(
    'powerup-theme-pdp-gallery-fallback',
    get_template_directory_uri() . '/assets/css/pdp-gallery-fallback.css',
    array(),
    file_exists( get_template_directory() . '/assets/css/pdp-gallery-fallback.css' ) ? (string) filemtime( get_template_directory() . '/assets/css/pdp-gallery-fallback.css' ) : wp_get_theme()->get( 'Version' )
  );

  wp_enqueue_script(
    'powerup-theme-pdp-gallery-fallback',
    get_template_directory_uri() . '/assets/js/pdp-gallery-fallback.js',
    array(),
    file_exists( get_template_directory() . '/assets/js/pdp-gallery-fallback.js' ) ? (string) filemtime( get_template_directory() . '/assets/js/pdp-gallery-fallback.js' ) : wp_get_theme()->get( 'Version' ),
    true
  );
}
add_action( 'wp_enqueue_scripts', 'powerup_theme_enqueue_pdp_gallery_fallback_assets', 30 );

function powerup_theme_compare_price_fallback_init() {
  if ( class_exists( 'PowerUp_B2C_Marketplace' ) || ! class_exists( 'WooCommerce' ) ) {
    return;
  }

  add_filter( 'woocommerce_get_price_html', 'powerup_theme_single_product_compare_price_html_fallback', 20, 2 );
}
add_action( 'init', 'powerup_theme_compare_price_fallback_init' );

function powerup_theme_get_marketplace_platform_options() {
  return array(
    'amazon'     => array( 'label' => 'Amazon', 'class' => 'marketplace-amazon' ),
    'walmart'    => array( 'label' => 'Walmart', 'class' => 'marketplace-walmart' ),
    'tiktok'     => array( 'label' => 'TikTok Shop', 'class' => 'marketplace-tiktok' ),
    'ebay'       => array( 'label' => 'eBay', 'class' => 'marketplace-ebay' ),
    'etsy'       => array( 'label' => 'Etsy', 'class' => 'marketplace-etsy' ),
    'bestbuy'    => array( 'label' => 'Best Buy', 'class' => 'marketplace-bestbuy' ),
    'target'     => array( 'label' => 'Target', 'class' => 'marketplace-target' ),
    'aliexpress' => array( 'label' => 'AliExpress', 'class' => 'marketplace-aliexpress' ),
    'temu'       => array( 'label' => 'Temu', 'class' => 'marketplace-temu' ),
    'newegg'     => array( 'label' => 'Newegg', 'class' => 'marketplace-newegg' ),
  );
}

function powerup_theme_get_product_marketplace_links( $product_id ) {
  $product_id = (int) $product_id;
  if ( $product_id <= 0 ) {
    return array();
  }

  $raw_links = get_post_meta( $product_id, '_powerup_marketplace_links', true );
  if ( ! is_array( $raw_links ) ) {
    return array();
  }

  $platform_options = powerup_theme_get_marketplace_platform_options();
  $links            = array();

  foreach ( $raw_links as $raw_link ) {
    if ( ! is_array( $raw_link ) ) {
      continue;
    }

    $platform = isset( $raw_link['platform'] ) ? sanitize_key( (string) $raw_link['platform'] ) : '';
    $url      = isset( $raw_link['url'] ) ? esc_url_raw( (string) $raw_link['url'] ) : '';

    if ( '' === $platform || '' === $url || ! isset( $platform_options[ $platform ] ) ) {
      continue;
    }

    $links[] = array(
      'platform' => $platform,
      'label'    => $platform_options[ $platform ]['label'],
      'class'    => $platform_options[ $platform ]['class'],
      'url'      => $url,
    );
  }

  return $links;
}

function powerup_theme_add_marketplace_meta_box() {
  add_meta_box(
    'powerup-marketplace-links',
    '第三方平台跳转链接',
    'powerup_theme_render_marketplace_meta_box',
    'product',
    'normal',
    'default'
  );
}
add_action( 'add_meta_boxes_product', 'powerup_theme_add_marketplace_meta_box' );

function powerup_theme_render_marketplace_meta_box( $post ) {
  if ( ! $post instanceof WP_Post ) {
    return;
  }

  $platform_options = powerup_theme_get_marketplace_platform_options();
  $saved_links      = powerup_theme_get_product_marketplace_links( (int) $post->ID );

  if ( empty( $saved_links ) ) {
    $saved_links = array(
      array(
        'platform' => 'amazon',
        'url'      => '',
      ),
    );
  }

  wp_nonce_field( 'powerup_marketplace_links_save', 'powerup_marketplace_links_nonce' );
  ?>
  <p>请选择平台并填写对应商品链接，可添加多条。</p>
  <div style="display:grid;grid-template-columns:minmax(180px,220px) 1fr auto;gap:10px;align-items:center;margin:8px 0 6px;font-weight:600;">
    <span>平台名称</span>
    <span>跳转链接 URL</span>
    <span>操作</span>
  </div>
  <div id="powerup-marketplace-rows">
    <?php foreach ( $saved_links as $index => $link ) : ?>
      <?php
      $selected_platform = isset( $link['platform'] ) ? sanitize_key( (string) $link['platform'] ) : '';
      $link_url          = isset( $link['url'] ) ? (string) $link['url'] : '';
      ?>
      <div class="powerup-marketplace-row" style="display:grid;grid-template-columns:minmax(180px,220px) 1fr auto;gap:10px;align-items:center;margin-bottom:8px;">
        <select name="powerup_marketplace_links[<?php echo esc_attr( (string) $index ); ?>][platform]">
          <?php foreach ( $platform_options as $platform_key => $platform_data ) : ?>
            <option value="<?php echo esc_attr( $platform_key ); ?>" <?php selected( $selected_platform, $platform_key ); ?>><?php echo esc_html( (string) $platform_data['label'] ); ?></option>
          <?php endforeach; ?>
        </select>
        <input type="url" name="powerup_marketplace_links[<?php echo esc_attr( (string) $index ); ?>][url]" value="<?php echo esc_attr( $link_url ); ?>" placeholder="https://example.com/product" style="width:100%;" />
        <button type="button" class="button powerup-remove-marketplace-row">删除</button>
      </div>
    <?php endforeach; ?>
  </div>

  <button type="button" class="button" id="powerup-add-marketplace-row">添加一条</button>

  <script>
    (function () {
      const wrap = document.getElementById('powerup-marketplace-rows');
      const addBtn = document.getElementById('powerup-add-marketplace-row');
      if (!wrap || !addBtn) return;

      const optionHtml = <?php echo wp_json_encode( implode( '', array_map( static function ( $key, $data ) {
        return '<option value="' . esc_attr( (string) $key ) . '">' . esc_html( (string) $data['label'] ) . '</option>';
      }, array_keys( $platform_options ), $platform_options ) ) ); ?>;

      function reindexRows() {
        const rows = wrap.querySelectorAll('.powerup-marketplace-row');
        rows.forEach(function (row, idx) {
          const select = row.querySelector('select');
          const input = row.querySelector('input[type="url"]');
          if (select) select.name = 'powerup_marketplace_links[' + idx + '][platform]';
          if (input) input.name = 'powerup_marketplace_links[' + idx + '][url]';
        });
      }

      function bindRemove(btn) {
        btn.addEventListener('click', function () {
          const row = btn.closest('.powerup-marketplace-row');
          if (row) row.remove();
          reindexRows();
        });
      }

      wrap.querySelectorAll('.powerup-remove-marketplace-row').forEach(bindRemove);

      addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'powerup-marketplace-row';
        row.style.display = 'grid';
        row.style.gridTemplateColumns = 'minmax(180px,220px) 1fr auto';
        row.style.gap = '10px';
        row.style.alignItems = 'center';
        row.style.marginBottom = '8px';
        row.innerHTML = '<select>' + optionHtml + '</select>'
          + '<input type="url" placeholder="https://example.com/product" style="width:100%;" />'
          + '<button type="button" class="button powerup-remove-marketplace-row">删除</button>';
        wrap.appendChild(row);
        bindRemove(row.querySelector('.powerup-remove-marketplace-row'));
        reindexRows();
      });
    })();
  </script>
  <?php
}

function powerup_theme_save_marketplace_meta_box( $post_id, $post ) {
  if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  if ( empty( $_POST['powerup_marketplace_links_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( (string) $_POST['powerup_marketplace_links_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_marketplace_links_save' ) ) {
    return;
  }

  $input_links = isset( $_POST['powerup_marketplace_links'] ) ? (array) wp_unslash( $_POST['powerup_marketplace_links'] ) : array();
  $platforms   = powerup_theme_get_marketplace_platform_options();
  $save_links  = array();

  foreach ( $input_links as $input_link ) {
    if ( ! is_array( $input_link ) ) {
      continue;
    }

    $platform = isset( $input_link['platform'] ) ? sanitize_key( (string) $input_link['platform'] ) : '';
    $url      = isset( $input_link['url'] ) ? esc_url_raw( (string) $input_link['url'] ) : '';

    if ( '' === $platform || '' === $url || ! isset( $platforms[ $platform ] ) ) {
      continue;
    }

    $save_links[] = array(
      'platform' => $platform,
      'url'      => $url,
    );
  }

  if ( empty( $save_links ) ) {
    delete_post_meta( $post_id, '_powerup_marketplace_links' );
    return;
  }

  update_post_meta( $post_id, '_powerup_marketplace_links', $save_links );
}
add_action( 'save_post_product', 'powerup_theme_save_marketplace_meta_box', 20, 2 );

function powerup_theme_get_default_tier_pricing_rules() {
  return array(
    array(
      'min_qty'  => 2,
      'discount' => 5,
    ),
    array(
      'min_qty'  => 4,
      'discount' => 8,
    ),
    array(
      'min_qty'  => 10,
      'discount' => 10,
    ),
  );
}

function powerup_theme_normalize_tier_pricing_rules( $rules ) {
  $rules = is_array( $rules ) ? $rules : array();
  $normalized = array();

  foreach ( $rules as $rule ) {
    if ( ! is_array( $rule ) ) {
      continue;
    }

    $min_qty  = isset( $rule['min_qty'] ) ? absint( $rule['min_qty'] ) : 0;
    $discount = isset( $rule['discount'] ) ? (float) $rule['discount'] : 0;

    if ( $min_qty < 2 || $discount <= 0 ) {
      continue;
    }

    $discount = min( 99, max( 0, $discount ) );
    $normalized[] = array(
      'min_qty'  => $min_qty,
      'discount' => round( $discount, 2 ),
    );
  }

  usort(
    $normalized,
    static function ( $left, $right ) {
      return (int) $left['min_qty'] <=> (int) $right['min_qty'];
    }
  );

  $unique = array();
  foreach ( $normalized as $rule ) {
    $unique[ (string) $rule['min_qty'] ] = $rule;
  }

  return array_values( $unique );
}

function powerup_theme_is_tier_pricing_enabled( $product_id ) {
  $product_id = (int) $product_id;
  if ( $product_id <= 0 ) {
    return true;
  }

  $stored = get_post_meta( $product_id, '_powerup_tier_pricing_enabled', true );
  if ( '' === $stored ) {
    return true;
  }

  return '0' !== (string) $stored;
}

function powerup_theme_get_product_tier_pricing_rules( $product_id ) {
  $product_id = (int) $product_id;
  if ( $product_id <= 0 || ! powerup_theme_is_tier_pricing_enabled( $product_id ) ) {
    return array();
  }

  $saved_rules = get_post_meta( $product_id, '_powerup_tier_pricing_rules', true );
  $rules       = powerup_theme_normalize_tier_pricing_rules( $saved_rules );

  if ( empty( $rules ) ) {
    $rules = powerup_theme_normalize_tier_pricing_rules( powerup_theme_get_default_tier_pricing_rules() );
  }

  return $rules;
}

function powerup_theme_add_tier_pricing_meta_box() {
  add_meta_box(
    'powerup-tier-pricing',
    '阶梯价设置',
    'powerup_theme_render_tier_pricing_meta_box',
    'product',
    'normal',
    'default'
  );
}
add_action( 'add_meta_boxes_product', 'powerup_theme_add_tier_pricing_meta_box' );

function powerup_theme_render_tier_pricing_meta_box( $post ) {
  if ( ! $post instanceof WP_Post ) {
    return;
  }

  $enabled = powerup_theme_is_tier_pricing_enabled( (int) $post->ID );
  $rules   = powerup_theme_get_product_tier_pricing_rules( (int) $post->ID );

  if ( empty( $rules ) ) {
    $rules = powerup_theme_get_default_tier_pricing_rules();
  }

  wp_nonce_field( 'powerup_tier_pricing_save', 'powerup_tier_pricing_nonce' );
  ?>
  <p><label><input type="checkbox" name="powerup_tier_pricing_enabled" value="1" <?php checked( $enabled ); ?>> 启用该商品阶梯价</label></p>
  <p>建议保留三档：2件、4件、10件。折扣填百分比，例如 5 表示 95 折。</p>
  <div style="display:grid;grid-template-columns:minmax(160px,220px) minmax(160px,220px);gap:10px;align-items:center;margin:8px 0 6px;font-weight:600;">
    <span>购买数量达到</span>
    <span>折扣百分比（%）</span>
  </div>
  <div id="powerup-tier-pricing-rows">
    <?php foreach ( $rules as $index => $rule ) : ?>
      <div class="powerup-tier-pricing-row" style="display:grid;grid-template-columns:minmax(160px,220px) minmax(160px,220px);gap:10px;align-items:center;margin-bottom:8px;">
        <input type="number" min="2" step="1" name="powerup_tier_pricing_rules[<?php echo esc_attr( (string) $index ); ?>][min_qty]" value="<?php echo esc_attr( (string) ( $rule['min_qty'] ?? '' ) ); ?>" />
        <input type="number" min="0" max="99" step="0.01" name="powerup_tier_pricing_rules[<?php echo esc_attr( (string) $index ); ?>][discount]" value="<?php echo esc_attr( (string) ( $rule['discount'] ?? '' ) ); ?>" />
      </div>
    <?php endforeach; ?>
  </div>
  <?php
}

function powerup_theme_save_tier_pricing_meta_box( $post_id, $post ) {
  if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  if ( empty( $_POST['powerup_tier_pricing_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( (string) $_POST['powerup_tier_pricing_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_tier_pricing_save' ) ) {
    return;
  }

  $enabled = ! empty( $_POST['powerup_tier_pricing_enabled'] ) ? '1' : '0';
  update_post_meta( $post_id, '_powerup_tier_pricing_enabled', $enabled );

  $rules = isset( $_POST['powerup_tier_pricing_rules'] ) ? (array) wp_unslash( $_POST['powerup_tier_pricing_rules'] ) : array();
  $rules = powerup_theme_normalize_tier_pricing_rules( $rules );

  if ( empty( $rules ) ) {
    delete_post_meta( $post_id, '_powerup_tier_pricing_rules' );
    return;
  }

  update_post_meta( $post_id, '_powerup_tier_pricing_rules', $rules );
}
add_action( 'save_post_product', 'powerup_theme_save_tier_pricing_meta_box', 21, 2 );

function powerup_theme_add_product_video_meta_box() {
  add_meta_box(
    'powerup-product-video',
    '产品展示视频',
    'powerup_theme_render_product_video_meta_box',
    'product',
    'side',
    'default'
  );
}
add_action( 'add_meta_boxes_product', 'powerup_theme_add_product_video_meta_box' );

function powerup_theme_render_product_video_meta_box( $post ) {
  if ( ! $post instanceof WP_Post ) {
    return;
  }

  wp_enqueue_media();

  $video_id   = (int) get_post_meta( $post->ID, '_powerup_product_video_id', true );
  $video_url  = $video_id > 0 ? wp_get_attachment_url( $video_id ) : '';
  $video_name = $video_id > 0 ? get_the_title( $video_id ) : '';

  wp_nonce_field( 'powerup_product_video_save', 'powerup_product_video_nonce' );
  ?>
  <p>上传或选择一个视频，前台会自动在商品图片列表最后显示该视频。</p>
  <input type="hidden" id="powerup-product-video-id" name="powerup_product_video_id" value="<?php echo esc_attr( (string) $video_id ); ?>" />
  <p>
    <button type="button" class="button" id="powerup-select-product-video">选择/上传视频</button>
    <button type="button" class="button" id="powerup-remove-product-video" <?php echo $video_id > 0 ? '' : 'style="display:none;"'; ?>>移除视频</button>
  </p>
  <div id="powerup-product-video-preview" <?php echo $video_url ? '' : 'style="display:none;"'; ?>>
    <video controls preload="metadata" playsinline style="width:100%;height:auto;display:block;border:1px solid #ddd;border-radius:6px;background:#000;">
      <source id="powerup-product-video-source" src="<?php echo esc_url( (string) $video_url ); ?>" type="video/mp4" />
    </video>
  </div>
  <p id="powerup-product-video-label" style="color:#555;word-break:break-word;margin-top:8px;">
    <?php echo $video_name ? esc_html( $video_name ) : '未选择视频'; ?>
  </p>
  <script>
    (function () {
      var selectBtn = document.getElementById('powerup-select-product-video');
      var removeBtn = document.getElementById('powerup-remove-product-video');
      var input = document.getElementById('powerup-product-video-id');
      var preview = document.getElementById('powerup-product-video-preview');
      var source = document.getElementById('powerup-product-video-source');
      var label = document.getElementById('powerup-product-video-label');
      var frame;

      if (!selectBtn || !removeBtn || !input || !preview || !source || !label) {
        return;
      }

      function clearVideo() {
        input.value = '';
        source.setAttribute('src', '');
        var video = preview.querySelector('video');
        if (video) {
          video.load();
        }
        preview.style.display = 'none';
        removeBtn.style.display = 'none';
        label.textContent = '未选择视频';
      }

      selectBtn.addEventListener('click', function () {
        if (frame) {
          frame.open();
          return;
        }

        frame = wp.media({
          title: '选择产品视频',
          button: { text: '使用此视频' },
          library: { type: 'video' },
          multiple: false
        });

        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          if (!attachment || !attachment.id || !attachment.url) {
            return;
          }

          input.value = attachment.id;
          source.setAttribute('src', attachment.url);
          var video = preview.querySelector('video');
          if (video) {
            video.load();
          }
          preview.style.display = 'block';
          removeBtn.style.display = '';
          label.textContent = attachment.filename || attachment.title || '已选择视频';
        });

        frame.open();
      });

      removeBtn.addEventListener('click', function () {
        clearVideo();
      });
    })();
  </script>
  <?php
}

function powerup_theme_save_product_video_meta_box( $post_id, $post ) {
  if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
    return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }

  if ( empty( $_POST['powerup_product_video_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( (string) $_POST['powerup_product_video_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_product_video_save' ) ) {
    return;
  }

  $video_id = isset( $_POST['powerup_product_video_id'] ) ? absint( wp_unslash( (string) $_POST['powerup_product_video_id'] ) ) : 0;

  if ( $video_id > 0 ) {
    $mime = (string) get_post_mime_type( $video_id );
    if ( 0 !== strpos( $mime, 'video/' ) ) {
      $video_id = 0;
    }
  }

  if ( $video_id > 0 ) {
    $video_url = wp_get_attachment_url( $video_id );
    update_post_meta( $post_id, '_powerup_product_video_id', $video_id );
    if ( $video_url ) {
      update_post_meta( $post_id, '_powerup_product_video_url', esc_url_raw( $video_url ) );
    }
  } else {
    delete_post_meta( $post_id, '_powerup_product_video_id' );
    delete_post_meta( $post_id, '_powerup_product_video_url' );
  }
}
add_action( 'save_post_product', 'powerup_theme_save_product_video_meta_box', 22, 2 );

function powerup_theme_get_applicable_tier_pricing_rule( $rules, $quantity ) {
  $rules    = powerup_theme_normalize_tier_pricing_rules( $rules );
  $quantity = absint( $quantity );
  $matched  = null;

  foreach ( $rules as $rule ) {
    if ( $quantity >= (int) $rule['min_qty'] ) {
      $matched = $rule;
    }
  }

  return $matched;
}

function powerup_theme_calculate_tier_price( $base_price, $discount_percent ) {
  $base_price       = (float) $base_price;
  $discount_percent = (float) $discount_percent;

  if ( $base_price <= 0 || $discount_percent <= 0 ) {
    return $base_price;
  }

  $discounted_price = $base_price * ( 1 - ( $discount_percent / 100 ) );
  return round( $discounted_price, wc_get_price_decimals() );
}

function powerup_theme_get_cart_item_base_product( $cart_item ) {
  $product_id = ! empty( $cart_item['variation_id'] ) ? (int) $cart_item['variation_id'] : (int) ( $cart_item['product_id'] ?? 0 );
  if ( $product_id <= 0 ) {
    return null;
  }

  $product = wc_get_product( $product_id );
  return $product instanceof WC_Product ? $product : null;
}

function powerup_theme_get_tier_pricing_mix_group_key( $rules ) {
  $rules = powerup_theme_normalize_tier_pricing_rules( $rules );
  if ( empty( $rules ) ) {
    return '';
  }

  return 'rules_' . md5( (string) wp_json_encode( $rules ) );
}

function powerup_theme_collect_cart_tier_pricing_mix_quantities( $cart ) {
  if ( ! $cart instanceof WC_Cart ) {
    return array();
  }

  $mix_quantities = array();

  foreach ( $cart->get_cart() as $cart_item ) {
    if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
      continue;
    }

    $base_product = powerup_theme_get_cart_item_base_product( $cart_item );
    if ( ! $base_product instanceof WC_Product ) {
      continue;
    }

    $base_price = (float) $base_product->get_price( 'edit' );
    if ( $base_price <= 0 ) {
      continue;
    }

    $rules = powerup_theme_get_product_tier_pricing_rules( (int) $base_product->get_id() );
    if ( empty( $rules ) ) {
      continue;
    }

    $mix_group_key = powerup_theme_get_tier_pricing_mix_group_key( $rules );
    if ( '' === $mix_group_key ) {
      continue;
    }

    $quantity = absint( $cart_item['quantity'] ?? 0 );
    if ( $quantity <= 0 ) {
      continue;
    }

    if ( ! isset( $mix_quantities[ $mix_group_key ] ) ) {
      $mix_quantities[ $mix_group_key ] = 0;
    }

    $mix_quantities[ $mix_group_key ] += $quantity;
  }

  return $mix_quantities;
}

function powerup_theme_apply_tier_pricing_to_cart( $cart ) {
  if ( is_admin() && ! wp_doing_ajax() ) {
    return;
  }

  if ( ! $cart instanceof WC_Cart ) {
    return;
  }

  $mix_quantities = powerup_theme_collect_cart_tier_pricing_mix_quantities( $cart );

  foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
    if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
      continue;
    }

    $base_product = powerup_theme_get_cart_item_base_product( $cart_item );
    if ( ! $base_product instanceof WC_Product ) {
      continue;
    }

    $base_price = (float) $base_product->get_price( 'edit' );
    if ( $base_price <= 0 ) {
      continue;
    }

    $rules = powerup_theme_get_product_tier_pricing_rules( (int) $base_product->get_id() );
    if ( empty( $rules ) ) {
      unset( $cart->cart_contents[ $cart_item_key ]['powerup_tier_pricing'] );
      $cart->cart_contents[ $cart_item_key ]['data']->set_price( $base_price );
      continue;
    }

    $line_quantity = absint( $cart_item['quantity'] ?? 0 );
    $mix_group_key = powerup_theme_get_tier_pricing_mix_group_key( $rules );
    $qualified_qty = $line_quantity;

    if ( '' !== $mix_group_key && isset( $mix_quantities[ $mix_group_key ] ) ) {
      $qualified_qty = absint( $mix_quantities[ $mix_group_key ] );
    }

    $rule = powerup_theme_get_applicable_tier_pricing_rule( $rules, $qualified_qty );

    if ( is_array( $rule ) ) {
      $discounted_price = powerup_theme_calculate_tier_price( $base_price, (float) $rule['discount'] );
      $cart->cart_contents[ $cart_item_key ]['powerup_tier_pricing'] = array(
        'min_qty'          => (int) $rule['min_qty'],
        'discount'         => (float) $rule['discount'],
        'base_price'       => $base_price,
        'discounted_price' => $discounted_price,
        'line_qty'         => $line_quantity,
        'qualified_qty'    => $qualified_qty,
      );
      $cart->cart_contents[ $cart_item_key ]['data']->set_price( $discounted_price );
    } else {
      unset( $cart->cart_contents[ $cart_item_key ]['powerup_tier_pricing'] );
      $cart->cart_contents[ $cart_item_key ]['data']->set_price( $base_price );
    }
  }
}
add_action( 'woocommerce_before_calculate_totals', 'powerup_theme_apply_tier_pricing_to_cart', 20 );

function powerup_theme_cart_item_tier_pricing_data( $item_data, $cart_item ) {
  if ( empty( $cart_item['powerup_tier_pricing'] ) || ! is_array( $cart_item['powerup_tier_pricing'] ) ) {
    return $item_data;
  }

  $tier_data = $cart_item['powerup_tier_pricing'];
  $line_qty  = absint( $tier_data['line_qty'] ?? ( $cart_item['quantity'] ?? 0 ) );
  $mix_qty   = absint( $tier_data['qualified_qty'] ?? $line_qty );

  $item_data[] = array(
    'key'   => __( 'Bulk discount', 'powerup-theme' ),
    'value' => sprintf(
      /* translators: 1: quantity, 2: percentage. */
      __( 'Buy %1$d+ save %2$s%%', 'powerup-theme' ),
      (int) $tier_data['min_qty'],
      wc_format_decimal( (float) $tier_data['discount'], 0 )
    ),
  );

  if ( $mix_qty > $line_qty ) {
    $item_data[] = array(
      'key'   => __( 'Mix & match qty', 'powerup-theme' ),
      'value' => sprintf(
        /* translators: %d: quantity count. */
        __( 'Tier triggered by mixed SKU total: %d', 'powerup-theme' ),
        $mix_qty
      ),
    );
  }

  return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'powerup_theme_cart_item_tier_pricing_data', 20, 2 );

function powerup_theme_cart_item_price_html( $price_html, $cart_item, $cart_item_key ) {
  if ( empty( $cart_item['powerup_tier_pricing'] ) || ! is_array( $cart_item['powerup_tier_pricing'] ) ) {
    return $price_html;
  }

  $tier_data = $cart_item['powerup_tier_pricing'];
  if ( empty( $tier_data['base_price'] ) || empty( $tier_data['discounted_price'] ) ) {
    return $price_html;
  }

  return '<span class="powerup-cart-tier-price"><del>' . wc_price( (float) $tier_data['base_price'] ) . '</del> <ins>' . wc_price( (float) $tier_data['discounted_price'] ) . '</ins></span>';
}
add_filter( 'woocommerce_cart_item_price', 'powerup_theme_cart_item_price_html', 20, 3 );

function powerup_theme_cart_item_subtotal_html( $subtotal_html, $cart_item, $cart_item_key ) {
  if ( empty( $cart_item['powerup_tier_pricing'] ) || ! is_array( $cart_item['powerup_tier_pricing'] ) ) {
    return $subtotal_html;
  }

  $tier_data = $cart_item['powerup_tier_pricing'];
  $quantity  = absint( $cart_item['quantity'] ?? 0 );
  if ( empty( $tier_data['base_price'] ) || empty( $tier_data['discounted_price'] ) || $quantity <= 0 ) {
    return $subtotal_html;
  }

  $base_subtotal       = (float) $tier_data['base_price'] * $quantity;
  $discounted_subtotal = (float) $tier_data['discounted_price'] * $quantity;

  return '<span class="powerup-cart-tier-subtotal"><del>' . wc_price( $base_subtotal ) . '</del> <ins>' . wc_price( $discounted_subtotal ) . '</ins></span>';
}
add_filter( 'woocommerce_cart_item_subtotal', 'powerup_theme_cart_item_subtotal_html', 20, 3 );

function powerup_theme_get_cart_item_tier_pricing_savings( $cart_item ) {
  if ( empty( $cart_item['powerup_tier_pricing'] ) || ! is_array( $cart_item['powerup_tier_pricing'] ) ) {
    return 0;
  }

  $tier_data = $cart_item['powerup_tier_pricing'];
  $quantity  = absint( $cart_item['quantity'] ?? 0 );
  if ( empty( $tier_data['base_price'] ) || empty( $tier_data['discounted_price'] ) || $quantity <= 0 ) {
    return 0;
  }

  $saved_amount = ( (float) $tier_data['base_price'] - (float) $tier_data['discounted_price'] ) * $quantity;
  return max( 0, round( $saved_amount, wc_get_price_decimals() ) );
}

function powerup_theme_cart_item_name_with_savings( $product_name, $cart_item, $cart_item_key ) {
  if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
    return $product_name;
  }

  $saved_amount = powerup_theme_get_cart_item_tier_pricing_savings( $cart_item );
  if ( $saved_amount <= 0 ) {
    return $product_name;
  }

  $savings_html = sprintf(
    '<div class="powerup-cart-savings-note">%s</div>',
    esc_html(
      sprintf(
        __( 'You saved %s with bulk pricing', 'powerup-theme' ),
        wp_strip_all_tags( wc_price( $saved_amount ) )
      )
    )
  );

  return $product_name . $savings_html;
}
add_filter( 'woocommerce_cart_item_name', 'powerup_theme_cart_item_name_with_savings', 20, 3 );

function powerup_theme_get_cart_total_tier_pricing_savings( $cart = null ) {
  if ( ! $cart instanceof WC_Cart ) {
    if ( ! function_exists( 'WC' ) || ! WC()->cart instanceof WC_Cart ) {
      return 0;
    }

    $cart = WC()->cart;
  }

  $total_saved = 0;

  foreach ( $cart->get_cart() as $cart_item ) {
    $total_saved += powerup_theme_get_cart_item_tier_pricing_savings( $cart_item );
  }

  return max( 0, round( $total_saved, wc_get_price_decimals() ) );
}

function powerup_theme_render_cart_tier_pricing_total_savings() {
  if ( ! function_exists( 'WC' ) || ! WC()->cart instanceof WC_Cart ) {
    return;
  }

  $total_saved = powerup_theme_get_cart_total_tier_pricing_savings( WC()->cart );
  if ( $total_saved <= 0 ) {
    return;
  }

  echo '<div class="powerup-cart-total-savings" role="status" aria-live="polite">';
  echo '<strong>' . esc_html__( 'Bulk savings applied', 'powerup-theme' ) . '</strong>';
  echo '<span>' . esc_html( sprintf( __( 'You saved %s in this order', 'powerup-theme' ), wp_strip_all_tags( wc_price( $total_saved ) ) ) ) . '</span>';
  echo '</div>';
}
add_action( 'woocommerce_before_cart_totals', 'powerup_theme_render_cart_tier_pricing_total_savings', 5 );
add_action( 'woocommerce_review_order_before_order_total', 'powerup_theme_render_cart_tier_pricing_total_savings', 5 );

function powerup_theme_single_product_compare_price_html_fallback( $price_html, $product ) {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return $price_html;
  }

  if ( ! $product instanceof WC_Product ) {
    return $price_html;
  }

  if ( false !== strpos( $price_html, '<del' ) ) {
    return $price_html;
  }

  $current_price = (float) $product->get_price();
  if ( $current_price <= 0 ) {
    return $price_html;
  }

  $fixed_discount_percent = 30.0;
  $compare_price          = round( $current_price / ( 1 - ( $fixed_discount_percent / 100 ) ), 2 );

  if ( $compare_price <= $current_price ) {
    return $price_html;
  }

  $compare_html = '<del class="powerup-compare-price">' . wc_price( $compare_price ) . '</del>';
  $current_html = '<ins class="powerup-current-price">' . wc_price( $current_price ) . '</ins>';

  return '<span class="price powerup-price-with-compare">' . $compare_html . ' ' . $current_html . '</span>';
}

function powerup_theme_render_amazon_like_gallery() {
  global $product;

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $main_image_id = $product->get_image_id();
  $gallery_ids   = $product->get_gallery_image_ids();
  $image_ids     = array();

  if ( $main_image_id ) {
    $image_ids[] = $main_image_id;
  }

  if ( ! empty( $gallery_ids ) && is_array( $gallery_ids ) ) {
    foreach ( $gallery_ids as $gallery_id ) {
      if ( ! in_array( $gallery_id, $image_ids, true ) ) {
        $image_ids[] = $gallery_id;
      }
    }
  }

  $items = array();
  foreach ( $image_ids as $image_id ) {
    $thumb = wp_get_attachment_image_url( $image_id, 'thumbnail' );
    $full  = wp_get_attachment_image_url( $image_id, 'large' );
    $zoom  = wp_get_attachment_image_url( $image_id, 'full' );
    $alt   = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

    if ( ! $thumb || ! $full ) {
      continue;
    }

    $items[] = array(
      'type'  => 'image',
      'thumb' => $thumb,
      'full'  => $full,
      'zoom'  => $zoom ? $zoom : $full,
      'alt'   => $alt,
    );
  }

  if ( count( $items ) < 7 ) {
    $demo_items = array(
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/71j-ADcAUoL-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/71j-ADcAUoL.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/71j-ADcAUoL-1536x1229.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 1',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/81e02eBhrxL._AC_SL1500_-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81e02eBhrxL._AC_SL1500_.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81e02eBhrxL._AC_SL1500_.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 2',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/81t5E9XZuxL._AC_SL1500_-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81t5E9XZuxL._AC_SL1500_.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81t5E9XZuxL._AC_SL1500_.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 3',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/8186lVmrS4L._AC_SL1500_-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/8186lVmrS4L._AC_SL1500_.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/8186lVmrS4L._AC_SL1500_.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 4',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/81d4xNfYeBL._AC_SL1500_-1-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81d4xNfYeBL._AC_SL1500_-1.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81d4xNfYeBL._AC_SL1500_-1.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 5',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/81Fv4Nyb-tL._AC_SL1500_-1-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81Fv4Nyb-tL._AC_SL1500_-1.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81Fv4Nyb-tL._AC_SL1500_-1.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 6',
      ),
      array(
        'thumb' => 'https://sopowerpro.com/wp-content/uploads/2026/03/81gLyT-e-lL._AC_SL1500_-300x300.jpg',
        'full'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81gLyT-e-lL._AC_SL1500_.jpg',
        'zoom'  => 'https://sopowerpro.com/wp-content/uploads/2026/03/81gLyT-e-lL._AC_SL1500_.jpg',
        'alt'   => '12-inch Electric Chainsaw - Image 7',
      ),
    );

    $needed = 7 - count( $items );
    for ( $i = 0; $i < $needed && isset( $demo_items[ $i ] ); $i++ ) {
      $items[] = $demo_items[ $i ];
    }
  }

  $video_id  = (int) get_post_meta( $product->get_id(), '_powerup_product_video_id', true );
  $video_url = '';

  if ( $video_id > 0 ) {
    $video_url = (string) wp_get_attachment_url( $video_id );
  }

  if ( '' === $video_url ) {
    $video_url_raw = get_post_meta( $product->get_id(), '_powerup_product_video_url', true );
    if ( is_string( $video_url_raw ) && '' !== trim( $video_url_raw ) ) {
      $video_url = esc_url_raw( trim( $video_url_raw ) );
    }
  }

  if ( '' !== $video_url ) {
    $video_thumb = '';
    $video_thumb_id = (int) get_post_meta( $product->get_id(), '_powerup_product_video_thumbnail_id', true );
    if ( $video_thumb_id > 0 ) {
      $video_thumb = wp_get_attachment_image_url( $video_thumb_id, 'thumbnail' );
    }

    if ( ! $video_thumb && $video_id > 0 ) {
      $video_thumb = wp_get_attachment_image_url( $video_id, 'thumbnail' );
    }

    if ( ! $video_thumb && ! empty( $items[0]['thumb'] ) ) {
      $video_thumb = $items[0]['thumb'];
    }

    if ( ! $video_thumb ) {
      $video_thumb = wc_placeholder_img_src( 'thumbnail' );
    }

    $items[] = array(
      'type'  => 'video',
      'thumb' => $video_thumb,
      'video' => $video_url,
      'alt'   => __( 'Product video', 'powerup-theme' ),
    );
  }

  if ( empty( $items ) ) {
    echo '<div class="powerup-amz-gallery"><div class="powerup-amz-main"><div class="powerup-amz-empty">No product image available.</div></div></div>';
    return;
  }

  $first = $items[0];

  echo '<div class="powerup-amz-gallery" data-powerup-pdp-gallery="1">';
  echo '<div class="powerup-amz-thumbs" role="list">';

  foreach ( $items as $index => $item ) {
    $item_type = isset( $item['type'] ) ? $item['type'] : 'image';
    $is_active = 0 === $index ? ' is-active' : '';
    $thumb_class = 'powerup-amz-thumb' . $is_active;
    if ( 'video' === $item_type ) {
      $thumb_class .= ' is-video';
    }

    echo '<button type="button" class="' . esc_attr( $thumb_class ) . '" data-type="' . esc_attr( $item_type ) . '" data-image="' . esc_url( isset( $item['full'] ) ? $item['full'] : '' ) . '" data-zoom="' . esc_url( isset( $item['zoom'] ) ? $item['zoom'] : '' ) . '" data-video="' . esc_url( isset( $item['video'] ) ? $item['video'] : '' ) . '" data-alt="' . esc_attr( $item['alt'] ) . '" aria-label="Switch product media">';
    echo '<img src="' . esc_url( $item['thumb'] ) . '" alt="' . esc_attr( $item['alt'] ) . '" loading="lazy" />';
    if ( 'video' === $item_type ) {
      echo '<span class="powerup-amz-thumb-play" aria-hidden="true"></span>';
    }
    echo '</button>';
  }

  echo '</div>';
  $first_type = isset( $first['type'] ) ? $first['type'] : 'image';
  $main_class = 'powerup-amz-main';
  if ( 'video' === $first_type ) {
    $main_class .= ' is-video-active';
  }

  $main_zoom = 'video' === $first_type ? '' : ( isset( $first['zoom'] ) ? $first['zoom'] : '' );
  $main_img  = 'video' === $first_type ? '' : ( isset( $first['full'] ) ? $first['full'] : '' );
  $main_alt  = isset( $first['alt'] ) ? $first['alt'] : '';
  $video_src = 'video' === $first_type && isset( $first['video'] ) ? $first['video'] : '';

  echo '<div class="' . esc_attr( $main_class ) . '" data-zoom-image="' . esc_url( $main_zoom ) . '">';
  echo '<img class="powerup-amz-main-image" src="' . esc_url( $main_img ) . '" alt="' . esc_attr( $main_alt ) . '"' . ( 'video' === $first_type ? ' hidden' : '' ) . ' />';
  echo '<video class="powerup-amz-main-video" controls playsinline preload="metadata"' . ( 'video' !== $first_type ? ' hidden' : '' ) . '>';
  if ( '' !== $video_src ) {
    echo '<source src="' . esc_url( $video_src ) . '" type="video/mp4" />';
  }
  echo '</video>';
  echo '<span class="powerup-amz-lens" aria-hidden="true"></span>';
  echo '<div class="powerup-amz-zoom-pane" aria-hidden="true"></div>';
  echo '</div>';
  echo '</div>';
}

function powerup_theme_get_reviewable_products_for_comments() {
  if ( ! class_exists( 'WooCommerce' ) ) {
    return array();
  }

  $product_ids = get_posts(
    array(
      'post_type'      => 'product',
      'post_status'    => 'publish',
      'numberposts'    => 100,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'fields'         => 'ids',
      'suppress_filters' => false,
    )
  );

  if ( empty( $product_ids ) ) {
    return array();
  }

  $products = array();
  foreach ( $product_ids as $product_id ) {
    $product = wc_get_product( $product_id );
    if ( $product instanceof WC_Product ) {
      $products[] = $product;
    }
  }

  return $products;
}

function powerup_theme_get_comment_review_fields_markup() {
  if ( ! is_singular() || is_singular( 'product' ) || ! class_exists( 'WooCommerce' ) ) {
    return '';
  }

  $products = powerup_theme_get_reviewable_products_for_comments();
  if ( empty( $products ) ) {
    return '';
  }

  $selected_product_id = isset( $_POST['powerup_review_product_id'] ) ? absint( wp_unslash( $_POST['powerup_review_product_id'] ) ) : 0;
  $selected_rating     = isset( $_POST['powerup_review_rating'] ) ? absint( wp_unslash( $_POST['powerup_review_rating'] ) ) : 5;
  $selected_rating     = min( 5, max( 1, $selected_rating ) );

  $html  = '<p class="comment-notes">' . esc_html__( 'Your comment will be published as a review on the selected product page.', 'powerup-theme' ) . '</p>';
  $html .= '<p class="comment-form-powerup-review-product">';
  $html .= '<label for="powerup_review_product_id">' . esc_html__( 'Reviewed Product', 'powerup-theme' ) . ' <span class="required">*</span></label>';
  $html .= '<select id="powerup_review_product_id" name="powerup_review_product_id" required>';
  $html .= '<option value="">' . esc_html__( 'Select a product', 'powerup-theme' ) . '</option>';

  foreach ( $products as $product ) {
    $html .= sprintf(
      '<option value="%1$d" %2$s>%3$s</option>',
      absint( $product->get_id() ),
      selected( $selected_product_id, $product->get_id(), false ),
      esc_html( $product->get_name() )
    );
  }

  $html .= '</select>';
  $html .= '</p>';
  $html .= '<p class="comment-form-powerup-review-rating">';
  $html .= '<label for="powerup_review_rating">' . esc_html__( 'Rating', 'powerup-theme' ) . ' <span class="required">*</span></label>';
  $html .= '<select id="powerup_review_rating" name="powerup_review_rating" required>';

  for ( $rating = 5; $rating >= 1; $rating-- ) {
    $html .= sprintf(
      '<option value="%1$d" %2$s>%3$d / 5</option>',
      $rating,
      selected( $selected_rating, $rating, false ),
      $rating
    );
  }

  $html .= '</select>';
  $html .= '</p>';
  $html .= '<p class="comment-form-powerup-review-video">';
  $html .= '<label for="powerup_review_video">' . esc_html__( 'Review Video (Optional)', 'powerup-theme' ) . '</label>';
  $html .= '<input id="powerup_review_video" name="powerup_review_video" type="file" accept="video/mp4,video/webm,video/ogg,video/quicktime">';
  $html .= '<small>' . esc_html__( 'Supported formats: MP4, WebM, OGV, MOV.', 'powerup-theme' ) . '</small>';
  $html .= '</p>';
  $html .= wp_nonce_field( 'powerup_review_video_upload', 'powerup_review_video_nonce', true, false );
  $html .= wp_nonce_field( 'powerup_link_comment_to_review', 'powerup_link_comment_to_review_nonce', true, false );

  return $html;
}

function powerup_theme_comment_form_link_product_review( $defaults ) {
  $defaults['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" formenctype="multipart/form-data">%4$s</button>';
  return $defaults;
}
add_filter( 'comment_form_defaults', 'powerup_theme_comment_form_link_product_review', 20 );

function powerup_theme_comment_form_field_comment_prepend_review_fields( $comment_field ) {
  if ( false !== strpos( $comment_field, 'powerup_review_product_id' ) ) {
    return $comment_field;
  }

  $fields_markup = powerup_theme_get_comment_review_fields_markup();
  if ( '' === $fields_markup ) {
    return $comment_field;
  }

  return $fields_markup . $comment_field;
}
add_filter( 'comment_form_field_comment', 'powerup_theme_comment_form_field_comment_prepend_review_fields', 20 );

function powerup_theme_route_page_comment_to_product_review( $commentdata ) {
  if ( ! class_exists( 'WooCommerce' ) ) {
    return $commentdata;
  }

  $original_post_id = isset( $commentdata['comment_post_ID'] ) ? absint( $commentdata['comment_post_ID'] ) : 0;
  if ( $original_post_id <= 0 || 'product' === get_post_type( $original_post_id ) ) {
    return $commentdata;
  }

  if ( empty( $_POST['powerup_review_product_id'] ) || empty( $_POST['powerup_link_comment_to_review_nonce'] ) ) {
    return $commentdata;
  }

  $nonce = sanitize_text_field( wp_unslash( $_POST['powerup_link_comment_to_review_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_link_comment_to_review' ) ) {
    return $commentdata;
  }

  $product_id = absint( wp_unslash( $_POST['powerup_review_product_id'] ) );
  $product    = wc_get_product( $product_id );

  if ( ! $product instanceof WC_Product || 'publish' !== get_post_status( $product_id ) ) {
    return $commentdata;
  }

  $commentdata['comment_post_ID'] = $product_id;
  $commentdata['comment_type']    = 'review';

  return $commentdata;
}
add_filter( 'preprocess_comment', 'powerup_theme_route_page_comment_to_product_review', 20 );

function powerup_theme_store_review_rating_from_comment_page( $comment_id ) {
  if ( empty( $_POST['powerup_review_rating'] ) ) {
    return;
  }

  $rating  = absint( wp_unslash( $_POST['powerup_review_rating'] ) );
  $rating  = min( 5, max( 1, $rating ) );
  $comment = get_comment( $comment_id );

  if ( ! $comment instanceof WP_Comment ) {
    return;
  }

  if ( 'review' !== $comment->comment_type ) {
    return;
  }

  update_comment_meta( $comment_id, 'rating', $rating );
}
add_action( 'comment_post', 'powerup_theme_store_review_rating_from_comment_page', 20 );

function powerup_theme_get_review_video_allowed_mimes() {
  return array(
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'ogv'  => 'video/ogg',
    'mov'  => 'video/quicktime',
  );
}

function powerup_theme_validate_review_video_before_comment( $commentdata ) {
  if ( empty( $_FILES['powerup_review_video'] ) || empty( $_FILES['powerup_review_video']['name'] ) ) {
    return $commentdata;
  }

  $target_post_id = isset( $commentdata['comment_post_ID'] ) ? absint( $commentdata['comment_post_ID'] ) : 0;
  if ( $target_post_id <= 0 ) {
    return $commentdata;
  }

  $is_product_review_target = 'product' === get_post_type( $target_post_id );
  if ( ! $is_product_review_target && ! empty( $_POST['powerup_review_product_id'] ) ) {
    $posted_product_id = absint( wp_unslash( $_POST['powerup_review_product_id'] ) );
    $is_product_review_target = $posted_product_id > 0 && 'product' === get_post_type( $posted_product_id );
  }

  if ( ! $is_product_review_target ) {
    return $commentdata;
  }

  if ( empty( $_POST['powerup_review_video_nonce'] ) ) {
    wp_die( esc_html__( 'Video upload verification failed. Please refresh and try again.', 'powerup-theme' ), esc_html__( 'Review submission error', 'powerup-theme' ), array( 'back_link' => true ) );
  }

  $nonce = sanitize_text_field( wp_unslash( $_POST['powerup_review_video_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_review_video_upload' ) ) {
    wp_die( esc_html__( 'Video upload verification failed. Please refresh and try again.', 'powerup-theme' ), esc_html__( 'Review submission error', 'powerup-theme' ), array( 'back_link' => true ) );
  }

  $video_file = $_FILES['powerup_review_video'];
  if ( ! isset( $video_file['error'] ) || UPLOAD_ERR_NO_FILE === (int) $video_file['error'] ) {
    return $commentdata;
  }

  if ( UPLOAD_ERR_OK !== (int) $video_file['error'] ) {
    wp_die( esc_html__( 'Video upload failed. Please try a smaller file or another format.', 'powerup-theme' ), esc_html__( 'Review submission error', 'powerup-theme' ), array( 'back_link' => true ) );
  }

  $max_size = (int) wp_max_upload_size();
  $file_size = isset( $video_file['size'] ) ? (int) $video_file['size'] : 0;
  if ( $max_size > 0 && $file_size > $max_size ) {
    wp_die(
      sprintf(
        /* translators: %s: max upload size in MB. */
        esc_html__( 'Video is too large. Maximum allowed size is %s MB.', 'powerup-theme' ),
        esc_html( number_format_i18n( $max_size / 1024 / 1024, 1 ) )
      ),
      esc_html__( 'Review submission error', 'powerup-theme' ),
      array( 'back_link' => true )
    );
  }

  $allowed_mimes = powerup_theme_get_review_video_allowed_mimes();
  $check_file    = wp_check_filetype_and_ext( $video_file['tmp_name'], $video_file['name'], $allowed_mimes );
  if ( empty( $check_file['ext'] ) || empty( $check_file['type'] ) ) {
    wp_die( esc_html__( 'Unsupported video format. Please upload MP4, WebM, OGV, or MOV.', 'powerup-theme' ), esc_html__( 'Review submission error', 'powerup-theme' ), array( 'back_link' => true ) );
  }

  return $commentdata;
}
add_filter( 'preprocess_comment', 'powerup_theme_validate_review_video_before_comment', 25 );

function powerup_theme_store_review_video_from_comment( $comment_id ) {
  if ( empty( $_FILES['powerup_review_video'] ) || empty( $_FILES['powerup_review_video']['name'] ) ) {
    return;
  }

  if ( empty( $_POST['powerup_review_video_nonce'] ) ) {
    return;
  }

  $nonce = sanitize_text_field( wp_unslash( $_POST['powerup_review_video_nonce'] ) );
  if ( ! wp_verify_nonce( $nonce, 'powerup_review_video_upload' ) ) {
    return;
  }

  $comment = get_comment( $comment_id );
  if ( ! $comment instanceof WP_Comment || 'review' !== $comment->comment_type ) {
    return;
  }

  $video_file = $_FILES['powerup_review_video'];
  if ( ! isset( $video_file['error'] ) || UPLOAD_ERR_OK !== (int) $video_file['error'] ) {
    return;
  }

  $allowed_mimes = powerup_theme_get_review_video_allowed_mimes();
  $check_file    = wp_check_filetype_and_ext( $video_file['tmp_name'], $video_file['name'], $allowed_mimes );
  if ( empty( $check_file['ext'] ) || empty( $check_file['type'] ) ) {
    return;
  }

  if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
  }

  $upload = wp_handle_upload(
    $video_file,
    array(
      'test_form' => false,
      'mimes'     => $allowed_mimes,
    )
  );

  if ( ! is_array( $upload ) || empty( $upload['url'] ) ) {
    return;
  }

  update_comment_meta( $comment_id, 'powerup_review_video_url', esc_url_raw( $upload['url'] ) );
  update_comment_meta( $comment_id, 'powerup_review_video_mime', sanitize_text_field( (string) ( $upload['type'] ?? '' ) ) );
}
add_action( 'comment_post', 'powerup_theme_store_review_video_from_comment', 25 );

function powerup_theme_comments_add_video_column( $columns ) {
  $new_columns = array();

  foreach ( $columns as $key => $label ) {
    $new_columns[ $key ] = $label;

    if ( 'author' === $key ) {
      $new_columns['powerup_review_video'] = esc_html__( 'Video', 'powerup-theme' );
    }
  }

  if ( ! isset( $new_columns['powerup_review_video'] ) ) {
    $new_columns['powerup_review_video'] = esc_html__( 'Video', 'powerup-theme' );
  }

  return $new_columns;
}
add_filter( 'manage_edit-comments_columns', 'powerup_theme_comments_add_video_column', 20 );

function powerup_theme_comments_render_video_column( $column, $comment_id ) {
  if ( 'powerup_review_video' !== $column ) {
    return;
  }

  $comment = get_comment( $comment_id );
  if ( ! $comment instanceof WP_Comment || 'review' !== $comment->comment_type ) {
    echo '—';
    return;
  }

  $video_url = (string) get_comment_meta( $comment_id, 'powerup_review_video_url', true );
  if ( '' === $video_url ) {
    esc_html_e( 'No', 'powerup-theme' );
    return;
  }

  echo '<a href="' . esc_url( $video_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Yes', 'powerup-theme' ) . '</a>';
}
add_action( 'manage_comments_custom_column', 'powerup_theme_comments_render_video_column', 20, 2 );

function powerup_theme_build_amazon_review_title( $comment_content ) {
  $plain = trim( wp_strip_all_tags( (string) $comment_content ) );
  if ( '' === $plain ) {
    return esc_html__( 'Customer review', 'powerup-theme' );
  }

  $first_sentence = preg_split( '/[.!?。！？]/u', $plain );
  $title          = isset( $first_sentence[0] ) ? trim( $first_sentence[0] ) : $plain;

  if ( '' === $title ) {
    $title = $plain;
  }

  return wp_html_excerpt( $title, 72, '...' );
}

function powerup_theme_render_star_icons( $rating ) {
  $rating = max( 0, min( 5, (int) $rating ) );
  $icons  = '';

  for ( $i = 1; $i <= 5; $i++ ) {
    $icons .= $i <= $rating ? '★' : '☆';
  }

  return $icons;
}

function powerup_theme_amazon_review_callback( $comment, $args, $depth ) {
  $GLOBALS['comment'] = $comment;

  $rating         = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
  $review_video   = (string) get_comment_meta( $comment->comment_ID, 'powerup_review_video_url', true );
  $review_video_mime = (string) get_comment_meta( $comment->comment_ID, 'powerup_review_video_mime', true );
  $review_title   = powerup_theme_build_amazon_review_title( $comment->comment_content );
  $review_country = function_exists( 'wc_get_base_location' ) ? wc_get_base_location() : array( 'country' => '' );
  $country_code   = isset( $review_country['country'] ) ? $review_country['country'] : '';
  $country_name   = $country_code ? WC()->countries->countries[ $country_code ] : '';

  $verified = false;
  if ( function_exists( 'wc_customer_bought_product' ) ) {
    $verified = wc_customer_bought_product( $comment->comment_author_email, $comment->user_id, $comment->comment_post_ID );
  }
  $initial = function_exists( 'mb_substr' ) ? mb_substr( $comment->comment_author, 0, 1 ) : substr( $comment->comment_author, 0, 1 );
  ?>
  <li <?php comment_class( 'powerup-amz-review-item' ); ?> id="li-comment-<?php comment_ID(); ?>">
    <article id="comment-<?php comment_ID(); ?>" class="powerup-amz-review-card">
      <header class="powerup-amz-review-head">
        <div class="powerup-amz-review-avatar"><?php echo esc_html( strtoupper( $initial ) ); ?></div>
        <div class="powerup-amz-review-author-wrap">
          <p class="powerup-amz-review-author"><?php comment_author(); ?></p>
          <p class="powerup-amz-review-meta">
            <?php
            printf(
              /* translators: 1: country, 2: date. */
              esc_html__( 'Reviewed in %1$s on %2$s', 'powerup-theme' ),
              $country_name ? esc_html( $country_name ) : esc_html__( 'your region', 'powerup-theme' ),
              esc_html( get_comment_date( get_option( 'date_format' ), $comment ) )
            );
            ?>
          </p>
        </div>
      </header>

      <div class="powerup-amz-review-rating-line">
        <span class="powerup-amz-review-stars" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', 'powerup-theme' ), $rating ) ); ?>"><?php echo esc_html( powerup_theme_render_star_icons( $rating ) ); ?></span>
        <strong class="powerup-amz-review-title"><?php echo esc_html( $review_title ); ?></strong>
      </div>

      <?php if ( $verified ) : ?>
        <p class="powerup-amz-review-verified"><?php esc_html_e( 'Verified Purchase', 'powerup-theme' ); ?></p>
      <?php endif; ?>

      <div class="powerup-amz-review-content"><?php comment_text(); ?></div>

      <?php if ( '' !== $review_video ) : ?>
        <div class="powerup-amz-review-video">
          <video controls preload="metadata" playsinline>
            <source src="<?php echo esc_url( $review_video ); ?>" type="<?php echo esc_attr( $review_video_mime ); ?>">
          </video>
        </div>
      <?php endif; ?>

      <footer class="powerup-amz-review-actions">
        <button type="button" class="powerup-amz-helpful-btn" disabled><?php esc_html_e( 'Helpful', 'powerup-theme' ); ?></button>
        <span class="powerup-amz-report"><?php esc_html_e( 'Report', 'powerup-theme' ); ?></span>
      </footer>
    </article>
  </li>
  <?php
}

function powerup_theme_render_marketplace_buttons_fallback() {
  if ( class_exists( 'PowerUp_B2C_Marketplace' ) ) {
    return;
  }

  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  global $product;
  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $platform_links = powerup_theme_get_product_marketplace_links( (int) $product->get_id() );

  if ( empty( $platform_links ) ) {
    $product_name = trim( wp_strip_all_tags( $product->get_name() ) );
    if ( '' === $product_name ) {
      return;
    }

    $query = rawurlencode( preg_replace( '/\s+/', ' ', $product_name ) );
    $platform_links = array(
      array(
        'label' => 'Amazon',
        'url'   => 'https://www.amazon.com/s?k=' . $query,
        'class' => 'marketplace-amazon',
      ),
    );
  }

  echo '<div class="powerup-marketplace-box">';
  echo '<p class="powerup-marketplace-title">' . esc_html__( 'Buy on Marketplace', 'powerup-theme' ) . '</p>';
  echo '<p class="powerup-marketplace-note">' . esc_html__( 'Fallback links are shown because the marketplace plugin is currently unavailable.', 'powerup-theme' ) . '</p>';
  echo '<div class="powerup-marketplace-links">';

  foreach ( $platform_links as $platform ) {
    echo '<a class="marketplace-btn ' . esc_attr( $platform['class'] ) . '" href="' . esc_url( $platform['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $platform['label'] ) . '</a>';
  }

  echo '</div>';
  echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'powerup_theme_render_marketplace_buttons_fallback', 31 );

function powerup_theme_enqueue_pdp_reference_assets() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  wp_enqueue_style(
    'powerup-theme-pdp-reference-layout',
    get_template_directory_uri() . '/assets/css/pdp-reference-layout.css',
    array(),
    file_exists( get_template_directory() . '/assets/css/pdp-reference-layout.css' ) ? (string) filemtime( get_template_directory() . '/assets/css/pdp-reference-layout.css' ) : wp_get_theme()->get( 'Version' )
  );
}
add_action( 'wp_enqueue_scripts', 'powerup_theme_enqueue_pdp_reference_assets', 31 );

function powerup_theme_get_product_sale_percentage( $product ) {
  if ( ! $product instanceof WC_Product ) {
    return 0;
  }

  $max_percentage = 0;

  if ( $product->is_type( 'variable' ) && $product instanceof WC_Product_Variable ) {
    $prices = $product->get_variation_prices( false );

    if ( ! empty( $prices['regular_price'] ) && ! empty( $prices['sale_price'] ) ) {
      foreach ( $prices['regular_price'] as $variation_id => $regular_price_raw ) {
        $regular_price = (float) $regular_price_raw;
        $sale_price    = isset( $prices['sale_price'][ $variation_id ] ) ? (float) $prices['sale_price'][ $variation_id ] : 0;

        if ( $regular_price <= 0 || $sale_price <= 0 || $sale_price >= $regular_price ) {
          continue;
        }

        $discount = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
        if ( $discount > $max_percentage ) {
          $max_percentage = $discount;
        }
      }
    }
  } else {
    $regular_price = (float) $product->get_regular_price();
    $sale_price    = (float) $product->get_sale_price();

    if ( $regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price ) {
      $max_percentage = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
    }
  }

  return max( 0, min( 99, $max_percentage ) );
}

function powerup_theme_render_sale_percentage_badge( $html, $post, $product ) {
  if ( ! $product instanceof WC_Product ) {
    return $html;
  }

  $percentage = powerup_theme_get_product_sale_percentage( $product );
  if ( $percentage <= 0 ) {
    return $html;
  }

  return '<span class="onsale powerup-sale-badge">-' . esc_html( (string) $percentage ) . '%</span>';
}
add_filter( 'woocommerce_sale_flash', 'powerup_theme_render_sale_percentage_badge', 20, 3 );

function powerup_theme_render_pdp_amazon_choice_badge() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  echo '<div class="powerup-pdp-choice-badge-wrap">';
  echo '<div class="powerup-pdp-choice-badge">Amazon\'s Choice</div>';
  echo '<p class="powerup-pdp-choice-sub">' . esc_html__( 'for "cordless chainsaw 20v"', 'powerup-theme' ) . '</p>';
  echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'powerup_theme_render_pdp_amazon_choice_badge', 4 );

function powerup_theme_render_pdp_shipping_guarantee() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  echo '<section class="powerup-pdp-shipping-guarantee" aria-label="Shipping and guarantee">';
  echo '<h3>' . esc_html__( 'Shipping & Guarantee', 'powerup-theme' ) . '</h3>';
  echo '<ul class="powerup-pdp-guarantee-list">';
  echo '<li class="powerup-pdp-guarantee-item"><span class="powerup-pdp-guarantee-icon powerup-pdp-guarantee-icon--ship" aria-hidden="true">FS</span><strong>Free Shipping</strong><span>' . esc_html__( 'Orders over $59 ship free.', 'powerup-theme' ) . '</span></li>';
  echo '<li class="powerup-pdp-guarantee-item"><span class="powerup-pdp-guarantee-icon powerup-pdp-guarantee-icon--warranty" aria-hidden="true">WR</span><strong>1 Year Warranty</strong><span>' . esc_html__( 'Quality issues covered with replacement support.', 'powerup-theme' ) . '</span></li>';
  echo '<li class="powerup-pdp-guarantee-item"><span class="powerup-pdp-guarantee-icon powerup-pdp-guarantee-icon--secure" aria-hidden="true">SC</span><strong>Secure Checkout</strong><span>' . esc_html__( 'Encrypted payment and buyer protection.', 'powerup-theme' ) . '</span></li>';
  echo '</ul>';
  echo '</section>';
}
add_action( 'woocommerce_single_product_summary', 'powerup_theme_render_pdp_shipping_guarantee', 36 );

function powerup_theme_render_pdp_tier_pricing() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  global $product;
  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $rules      = powerup_theme_get_product_tier_pricing_rules( (int) $product->get_id() );
  $base_price = (float) $product->get_price();

  if ( empty( $rules ) || $base_price <= 0 ) {
    return;
  }

  echo '<section class="powerup-pdp-tier-pricing" aria-label="Bulk pricing">';
  echo '<h3>' . esc_html__( 'Bulk Savings', 'powerup-theme' ) . '</h3>';
  echo '<ul class="powerup-pdp-tier-pricing__list">';

  foreach ( $rules as $rule ) {
    $min_qty          = (int) $rule['min_qty'];
    $discount         = (float) $rule['discount'];
    $discounted_price = powerup_theme_calculate_tier_price( $base_price, $discount );

    echo '<li class="powerup-pdp-tier-pricing__item">';
    echo '<strong>' . esc_html( sprintf( __( 'Buy %d+', 'powerup-theme' ), $min_qty ) ) . '</strong>';
    echo '<span>' . esc_html( sprintf( __( 'Save %s%%', 'powerup-theme' ), wc_format_decimal( $discount, 0 ) ) ) . '</span>';
    echo '<em>' . wp_kses_post( wc_price( $discounted_price ) ) . '</em>';
    echo '</li>';
  }

  echo '</ul>';
  echo '</section>';
}
add_action( 'woocommerce_single_product_summary', 'powerup_theme_render_pdp_tier_pricing', 28 );





function powerup_theme_add_shipping_delivery_tab( $tabs ) {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return $tabs;
  }

  global $product;
  $review_count = 0;
  if ( $product instanceof WC_Product ) {
    $review_count = (int) $product->get_review_count();
  }

  if ( isset( $tabs['description'] ) ) {
    $tabs['description']['title'] = __( 'DESCRIPTION', 'powerup-theme' );
  }

  if ( isset( $tabs['reviews'] ) ) {
    $tabs['reviews']['title'] = sprintf(
      /* translators: %d: review count */
      __( 'REVIEWS (%d)', 'powerup-theme' ),
      $review_count
    );
  }


  $tabs['powerup_shipping_delivery'] = array(
    'title'    => __( 'SHIPPING AND DELIVERY', 'powerup-theme' ),
    'priority' => 35,
    'callback' => 'powerup_theme_render_shipping_delivery_tab',
  );

  return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'powerup_theme_add_shipping_delivery_tab', 30 );

function powerup_theme_related_products_heading_reference() {
  return __( 'RELATED PRODUCTS', 'powerup-theme' );
}
add_filter( 'woocommerce_product_related_products_heading', 'powerup_theme_related_products_heading_reference' );

function powerup_theme_get_reference_series_product_slugs() {
  return array(
    '12-inch-electric-chainsaw-cordless-20v-battery-powered-chain-saw-kit',
    'brushless-electric-chainsaw-12-inch-for-dewalt-20v-60v-tool-only',
    'brushless-electric-chainsaw-12-inch-for-milwaukee-m18-18v-tool-only',
  );
}

function powerup_theme_get_reference_series_page_url() {
  $page = get_page_by_path( 'chainsaw-series' );
  if ( $page instanceof WP_Post ) {
    return get_permalink( $page );
  }

  return home_url( '/chainsaw-series/' );
}

function powerup_theme_get_reference_series_product_ids() {
  $product_ids = array();

  foreach ( powerup_theme_get_reference_series_product_slugs() as $slug ) {
    $post = get_page_by_path( $slug, OBJECT, 'product' );
    if ( $post instanceof WP_Post ) {
      $product_ids[] = (int) $post->ID;
    }
  }

  return array_values( array_unique( array_filter( $product_ids ) ) );
}

function powerup_theme_is_reference_series_product( $product = null ) {
  if ( $product instanceof WC_Product ) {
    $slug = $product->get_slug();
    return in_array( $slug, powerup_theme_get_reference_series_product_slugs(), true );
  }

  if ( is_numeric( $product ) ) {
    $post = get_post( (int) $product );
    if ( $post instanceof WP_Post ) {
      return in_array( $post->post_name, powerup_theme_get_reference_series_product_slugs(), true );
    }
  }

  if ( $product instanceof WP_Post ) {
    return in_array( $product->post_name, powerup_theme_get_reference_series_product_slugs(), true );
  }

  return false;
}

function powerup_theme_get_reference_series_badge_html( $variant = 'default' ) {
  $class_name = 'powerup-series-badge';
  if ( 'compact' === $variant ) {
    $class_name .= ' powerup-series-badge--compact';
  } elseif ( 'pdp' === $variant ) {
    $class_name .= ' powerup-series-badge--pdp';
  }

  return '<span class="' . esc_attr( $class_name ) . '">' . esc_html__( 'Chainsaw Series', 'powerup-theme' ) . '</span>';
}

function powerup_theme_render_reference_series_badge_single() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  global $product;
  if ( ! $product instanceof WC_Product || ! powerup_theme_is_reference_series_product( $product ) ) {
    return;
  }

  echo powerup_theme_get_reference_series_badge_html( 'pdp' );
}
add_action( 'woocommerce_single_product_summary', 'powerup_theme_render_reference_series_badge_single', 4 );

function powerup_theme_render_reference_series_badge_loop() {
  global $product;
  if ( ! $product instanceof WC_Product || ! powerup_theme_is_reference_series_product( $product ) ) {
    return;
  }

  echo powerup_theme_get_reference_series_badge_html( 'compact' );
}
add_action( 'woocommerce_after_shop_loop_item_title', 'powerup_theme_render_reference_series_badge_loop', 4 );

function powerup_theme_get_reference_series_payload_map() {
  $payloads = array();

  if ( function_exists( 'powerup_theme_get_reference_product_payload_29474' ) ) {
    $payload = powerup_theme_get_reference_product_payload_29474();
    if ( ! empty( $payload['slug'] ) ) {
      $payloads[ (string) $payload['slug'] ] = $payload;
    }
  }

  if ( function_exists( 'powerup_theme_get_reference_product_payload_123' ) ) {
    $payload = powerup_theme_get_reference_product_payload_123();
    if ( ! empty( $payload['slug'] ) ) {
      $payloads[ (string) $payload['slug'] ] = $payload;
    }
  }

  if ( function_exists( 'powerup_theme_get_reference_product_payload_4567' ) ) {
    $payload = powerup_theme_get_reference_product_payload_4567();
    if ( ! empty( $payload['slug'] ) ) {
      $payloads[ (string) $payload['slug'] ] = $payload;
    }
  }

  return $payloads;
}

function powerup_theme_get_reference_series_nav_items() {
  $items       = array();
  $payload_map = powerup_theme_get_reference_series_payload_map();

  foreach ( powerup_theme_get_reference_series_product_slugs() as $slug ) {
    $post    = get_page_by_path( $slug, OBJECT, 'product' );
    $payload = isset( $payload_map[ $slug ] ) ? $payload_map[ $slug ] : array();

    if ( $post instanceof WP_Post ) {
      $product     = wc_get_product( $post->ID );
      $thumb       = get_the_post_thumbnail_url( $post->ID, 'woocommerce_thumbnail' );
      $items[ $slug ] = array(
        'id'        => (int) $post->ID,
        'slug'      => $slug,
        'title'     => get_the_title( $post ),
        'url'       => get_permalink( $post ),
        'price'     => $product instanceof WC_Product ? wp_strip_all_tags( $product->get_price_html() ) : '',
        'excerpt'   => has_excerpt( $post ) ? wp_trim_words( $post->post_excerpt, 16, '...' ) : ( isset( $payload['excerpt'] ) ? (string) $payload['excerpt'] : '' ),
        'image'     => $thumb ? $thumb : ( ! empty( $payload['image_urls'][0] ) ? (string) $payload['image_urls'][0] : '' ),
        'is_live'   => true,
      );
      continue;
    }

    if ( empty( $payload ) ) {
      continue;
    }

    $items[ $slug ] = array(
      'id'      => 0,
      'slug'    => $slug,
      'title'   => (string) $payload['title'],
      'url'     => function_exists( 'powerup_get_product_url' ) ? powerup_get_product_url( $slug ) : home_url( '/shop/' ),
      'price'   => '$' . (string) $payload['sale_price'],
      'excerpt' => isset( $payload['excerpt'] ) ? (string) $payload['excerpt'] : '',
      'image'   => ! empty( $payload['image_urls'][0] ) ? (string) $payload['image_urls'][0] : '',
      'is_live' => false,
    );
  }

  return $items;
}

function powerup_theme_ensure_reference_series_page() {
  if ( get_option( 'powerup_reference_series_page_ready', false ) ) {
    return;
  }

  $existing = get_page_by_path( 'chainsaw-series', OBJECT, 'page' );
  $page_args = array(
    'post_type'    => 'page',
    'post_status'  => 'publish',
    'post_title'   => 'Chainsaw Series',
    'post_name'    => 'chainsaw-series',
    'post_content' => '',
  );

  if ( $existing instanceof WP_Post ) {
    $page_args['ID'] = $existing->ID;
    wp_update_post( $page_args );
    $page_id = (int) $existing->ID;
  } else {
    $page_id = wp_insert_post( $page_args, true );
    if ( is_wp_error( $page_id ) || ! $page_id ) {
      return;
    }
    $page_id = (int) $page_id;
  }

  update_post_meta( $page_id, '_wp_page_template', 'page-chainsaw-series.php' );
  update_option(
    'powerup_reference_series_page_ready',
    array(
      'page_id' => $page_id,
      'time'    => current_time( 'mysql' ),
    ),
    false
  );
}
add_action( 'init', 'powerup_theme_ensure_reference_series_page', 18 );

function powerup_theme_prioritize_reference_series_related_products( $related_posts, $product_id, $args ) {
  $series_ids = powerup_theme_get_reference_series_product_ids();
  if ( count( $series_ids ) < 2 ) {
    return $related_posts;
  }

  $product_id = (int) $product_id;
  if ( ! in_array( $product_id, $series_ids, true ) ) {
    return $related_posts;
  }

  $preferred_ids = array_values(
    array_filter(
      $series_ids,
      static function ( $series_id ) use ( $product_id ) {
        return (int) $series_id !== $product_id;
      }
    )
  );

  $merged_ids = array_values( array_unique( array_merge( $preferred_ids, $related_posts ) ) );
  $limit      = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : count( $merged_ids );

  if ( $limit > 0 ) {
    return array_slice( $merged_ids, 0, $limit );
  }

  return $merged_ids;
}
add_filter( 'woocommerce_related_products', 'powerup_theme_prioritize_reference_series_related_products', 20, 3 );

function powerup_theme_render_reference_series_nav() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  global $product;
  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $current_slug = $product->get_slug();
  if ( ! in_array( $current_slug, powerup_theme_get_reference_series_product_slugs(), true ) ) {
    return;
  }

  $series_items = powerup_theme_get_reference_series_nav_items();
  if ( count( $series_items ) < 2 ) {
    return;
  }

  echo '<section class="powerup-pdp-series-nav" aria-label="Chainsaw series">';
  echo '<div class="powerup-pdp-series-nav__head">';
  echo '<span class="powerup-pdp-series-nav__eyebrow">' . esc_html__( 'Chainsaw Series', 'powerup-theme' ) . '</span>';
  echo '<h2>' . esc_html__( 'Compare Other Models In This Line', 'powerup-theme' ) . '</h2>';
  echo '<p>' . esc_html__( 'Jump across the featured cordless chainsaw lineup without leaving the product flow.', 'powerup-theme' ) . '</p>';
  echo '<a class="powerup-pdp-series-nav__link" href="' . esc_url( powerup_theme_get_reference_series_page_url() ) . '">' . esc_html__( 'View Full Series Page', 'powerup-theme' ) . '</a>';
  echo '</div>';
  echo '<div class="powerup-pdp-series-nav__grid">';

  foreach ( powerup_theme_get_reference_series_product_slugs() as $slug ) {
    if ( empty( $series_items[ $slug ] ) ) {
      continue;
    }

    $item        = $series_items[ $slug ];
    $is_current  = $slug === $current_slug;
    $card_class  = $is_current ? 'powerup-pdp-series-card is-current' : 'powerup-pdp-series-card';

    echo '<article class="' . esc_attr( $card_class ) . '">';
    echo '<a class="powerup-pdp-series-card__media" href="' . esc_url( $item['url'] ) . '">';
    if ( ! empty( $item['image'] ) ) {
      echo '<img src="' . esc_url( $item['image'] ) . '" alt="' . esc_attr( $item['title'] ) . '" loading="lazy" decoding="async">';
    }
    echo '</a>';
    echo '<div class="powerup-pdp-series-card__body">';
    echo '<div class="powerup-pdp-series-card__topline">';
    echo '<span>' . esc_html__( 'Series Model', 'powerup-theme' ) . '</span>';
    if ( $is_current ) {
      echo '<strong>' . esc_html__( 'Current', 'powerup-theme' ) . '</strong>';
    }
    echo '</div>';
    echo '<h3><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a></h3>';
    if ( ! empty( $item['excerpt'] ) ) {
      echo '<p>' . esc_html( $item['excerpt'] ) . '</p>';
    }
    echo '<div class="powerup-pdp-series-card__meta">';
    echo '<strong>' . esc_html( $item['price'] ) . '</strong>';
    if ( $is_current ) {
      echo '<span>' . esc_html__( 'Viewing now', 'powerup-theme' ) . '</span>';
    } else {
      echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html__( 'Open Model', 'powerup-theme' ) . '</a>';
    }
    echo '</div>';
    echo '</div>';
    echo '</article>';
  }

  echo '</div>';
  echo '</section>';
}

function powerup_theme_render_about_item_tab() {
  global $product;

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $about_points = get_post_meta( $product->get_id(), '_powerup_about_item_points', true );
  if ( ! is_string( $about_points ) || '' === trim( $about_points ) ) {
    return;
  }

  $points_array = explode( "\n", $about_points );
  $points_array = array_filter( array_map( 'trim', $points_array ) );

  if ( empty( $points_array ) ) {
    return;
  }

  echo '<div class="powerup-pdp-about-item-tab">';
  echo '<h3>' . esc_html__( 'About this item', 'powerup-theme' ) . '</h3>';
  echo '<ul>';
  foreach ( $points_array as $point ) {
    echo '<li>' . esc_html( $point ) . '</li>';
  }
  echo '</ul>';
  echo '</div>';
}

/**
 * Open media stack wrapper before product gallery.
 */
function powerup_theme_open_media_stack_wrapper() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  echo '<div class="powerup-pdp-media-stack">';
}
add_action( 'woocommerce_before_single_product_summary', 'powerup_theme_open_media_stack_wrapper', 15 );

/**
 * Render About this item panel below product images on desktop only
 */
function powerup_theme_render_about_item_panel() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  global $product;
  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $about_points = get_post_meta( $product->get_id(), '_powerup_about_item_points', true );
  if ( ! is_string( $about_points ) || '' === trim( $about_points ) ) {
    return;
  }

  $points_array = explode( "\n", $about_points );
  $points_array = array_filter( array_map( 'trim', $points_array ) );

  if ( empty( $points_array ) ) {
    return;
  }

  echo '<div class="powerup-pdp-about-item-panel" style="background:#ffffff !important; display:block !important;">';
  echo '<h3 class="powerup-pdp-about-item-panel__title" style="color:#000000 !important; opacity:1 !important; text-shadow:none !important;">' . esc_html__( 'About this item', 'powerup-theme' ) . '</h3>';
  echo '<ul class="powerup-pdp-about-item-panel__list" style="color:#000000 !important; opacity:1 !important;">';
  foreach ( $points_array as $point ) {
    echo '<li style="color:#000000 !important; opacity:1 !important; text-shadow:none !important;">' . esc_html( $point ) . '</li>';
  }
  echo '</ul>';
  echo '</div>';
}
add_action( 'woocommerce_before_single_product_summary', 'powerup_theme_render_about_item_panel', 25 );

/**
 * Close media stack wrapper after About panel.
 */
function powerup_theme_close_media_stack_wrapper() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  echo '</div>';
}
add_action( 'woocommerce_before_single_product_summary', 'powerup_theme_close_media_stack_wrapper', 30 );


function powerup_theme_render_shipping_delivery_tab() {
  global $product;

  if ( ! $product instanceof WC_Product ) {
    return;
  }

  $content = get_post_meta( $product->get_id(), '_powerup_shipping_delivery', true );
  if ( ! is_string( $content ) || '' === trim( $content ) ) {
    $content = __( 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.', 'powerup-theme' );
  }

  echo '<div class="powerup-pdp-shipping-tab">' . wpautop( esc_html( $content ) ) . '</div>';
}



function powerup_theme_get_reference_product_payload_29474() {
  return array(
    'source_key'      => '29474',
    'title'          => '12-inch Electric Chainsaw Cordless, 20V Battery Powered Cordless Chain Saw, Chainsaw with 2 x 4.0Ah Lithium ion Battery and Charger for Wood Cutting, Tree Saw Trimming and Branch Pruning',
    'slug'           => '12-inch-electric-chainsaw-cordless-20v-battery-powered-chain-saw-kit',
    'search_term'    => 'chainsaw',
    'excerpt'        => 'Brushless 12-inch cordless chainsaw kit with dual 4.0Ah batteries, auto oiling, and fast-cutting performance for wood cutting and pruning.',
    'category'       => 'electric-chainsaw',
    'category_name'  => 'Electric Chainsaw',
    'specs'          => array(
      'bar_length_inch' => '12',
      'battery_platform' => '20v',
    ),
    'tags'           => array( 'cordless chainsaw', '20v chainsaw', 'battery chainsaw' ),
    'regular_price'  => '105.99',
    'sale_price'     => '95.99',
    'about_points'   => array(
      'Brushless motor: cordless chainsaw has a 2.56 hp engine with 12,000 RPM and chain speed up to 10 m/s for efficient cutting.',
      '2*4000mAh battery: equipped with two 4.0Ah batteries plus overload and temperature protection for longer runtime.',
      'Multiple safety protection: includes safety hand guard and double-button start to reduce accidental activation risk.',
      'Automatic oiling and tool-less adjustment: 0.15 qt (140 ml) oil tank, auto lubrication, and easy tension dial for smoother cuts.',
    ),
    'shipping_text'  => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'     => array(
      'https://sopowerpro.com/wp-content/uploads/2026/03/71j-ADcAUoL.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81e02eBhrxL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81t5E9XZuxL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/8186lVmrS4L._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81d4xNfYeBL._AC_SL1500_-1.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81Fv4Nyb-tL._AC_SL1500_-1.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81gLyT-e-lL._AC_SL1500_.jpg',
    ),
  );
}

function powerup_theme_get_reference_product_payload_123() {
  return array(
    'source_key'      => '123',
    'title'          => 'Brushless Electric Chainsaw 12-inch Cordless Tool Only for Dewalt 20V/60V Battery with Auto Oiler & Security Lock, for Tree Saw Trimming and Branch Wood Cutting (Tool Only)',
    'slug'           => 'brushless-electric-chainsaw-12-inch-for-dewalt-20v-60v-tool-only',
    'search_term'    => 'dewalt chainsaw',
    'excerpt'        => '12-inch brushless cordless chainsaw tool-only model for Dewalt 20V/60V batteries with auto oiler, safety lock, and lightweight design.',
    'category'       => 'electric-chainsaw',
    'category_name'  => 'Electric Chainsaw',
    'specs'          => array(
      'bar_length_inch' => '12',
      'battery_platform' => 'dewalt',
    ),
    'tags'           => array( 'dewalt chainsaw', 'cordless chainsaw', 'tool only chainsaw' ),
    'regular_price'  => '85.99',
    'sale_price'     => '75.99',
    'about_points'   => array(
      'Upgraded copper brushless motor: features a 1000W pure copper motor for powerful, durable cutting that helps prevent jamming.',
      'Cordless battery compatibility: designed for Dewalt 20V/60V batteries including DCB204, DCB205, DCB206, DCB184, DCB606, and DCB609.',
      'Automatic oiler system: includes a pure copper oil pump that automatically lubricates the chain for smoother cutting and reduced wear.',
      'Quick and secure installation: tool-free chain tension adjustment with double-nut guide plate fixing for easier maintenance.',
      'Lightweight and user-friendly: weighs about 5 lbs, reaches 13 m/s chain speed, and includes storage bag, gloves, two 12-inch chains, and a protective cover.',
    ),
    'shipping_text'  => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'     => array(
      'https://sopowerpro.com/wp-content/uploads/2026/03/71WgfIqcIVL._AC_SL1500_.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/3a112489-9c17-4e94-b7f9-34461ad0b082.__CR0,0,300,300_PT0_SX220_V1___.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/763af907-4733-44aa-996e-6d29ed912ede.__CR0,0,300,300_PT0_SX220_V1___.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/ec5d22b1-3c70-4186-836e-644723169123.__CR0,0,300,300_PT0_SX220_V1___.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/133e2216-3bed-48c3-8192-8f31d5258256.__CR0,0,970,600_PT0_SX970_V1___.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/af022674-26d9-4cfd-bc95-e357e9452819.__CR0,0,970,600_PT0_SX970_V1___.jpg',
      'https://m.media-amazon.com/images/S/aplus-media-library-service-media/6cc34d28-df4a-4db0-ae44-dae8c2b63c74.__CR0,0,970,600_PT0_SX970_V1___.jpg',
    ),
  );
}

function powerup_theme_get_reference_product_payload_4567() {
  return array(
    'source_key'      => '4567',
    'title'           => 'Brushless Electric Chainsaw 12-inch Cordless Tool Only for Milwaukee M18 18V Battery with Auto Oiler & Security Lock, for Tree Saw Trimming and Branch Wood Cutting (Tool Only)',
    'slug'            => 'brushless-electric-chainsaw-12-inch-for-milwaukee-m18-18v-tool-only',
    'search_term'     => 'milwaukee chainsaw',
    'excerpt'         => '12-inch brushless cordless chainsaw tool-only model for Milwaukee M18 18V batteries with auto oiler, safety lock, and lightweight design.',
    'category'        => 'electric-chainsaw',
    'category_name'   => 'Electric Chainsaw',
    'specs'           => array(
      'bar_length_inch' => '12',
      'battery_platform' => 'milwaukee',
    ),
    'tags'            => array( 'milwaukee chainsaw', 'cordless chainsaw', 'tool only chainsaw' ),
    'regular_price'   => '85.99',
    'sale_price'      => '75.99',
    'about_points'    => array(
      'Upgraded copper brushless motor: features a 1000W pure copper motor for powerful, durable cutting that helps prevent jamming.',
      'Cordless battery compatibility: operates for Milwaukee M18 18V batteries (tool only, no battery or charger included).',
      'Automatic oiler system: includes a pure copper oil pump that auto-lubricates the chain during use for smoother cutting and reduced wear.',
      'Quick and secure installation: tool-free chain tension adjustment with double-nut guide plate fixing for easier maintenance.',
      'Lightweight and user-friendly: weighs about 5 lbs, reaches 13 m/s chain speed, and includes storage bag, gloves, two 12-inch chains, and a protective cover.',
    ),
    'shipping_text'   => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'      => array(
      'https://sopowerpro.com/wp-content/uploads/2026/03/71Z5FRZgOwL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81XmvW50kqL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81iF-2n6WxL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/71ShVDSldNL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81gJDGkf1VL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81AcmElazTL._AC_SL1500_.jpg',
      'https://sopowerpro.com/wp-content/uploads/2026/03/81ohjK6JBBL._AC_SL1500_.jpg',
    ),
  );
}

function powerup_theme_get_reference_product_payload_makita() {
  return array(
    'source_key'      => 'makita-1200',
    'title'           => 'Brushless Electric Chainsaw 12-inch Cordless Tool Only for Makita 18V Battery with Auto Oiler & Security Lock, for Tree Saw Trimming and Branch Wood Cutting (Tool Only)',
    'slug'            => 'brushless-electric-chainsaw-12-inch-for-makita-18v-tool-only',
    'search_term'     => 'makita chainsaw',
    'excerpt'         => '12-inch brushless cordless chainsaw tool-only model for Makita 18V batteries with auto oiler, safety lock, and lightweight design.',
    'category'        => 'electric-chainsaw',
    'category_name'   => 'Electric Chainsaw',
    'specs'           => array(
      'bar_length_inch' => '12',
      'battery_platform' => 'makita',
    ),
    'tags'            => array( 'makita chainsaw', 'cordless chainsaw', 'tool only chainsaw' ),
    'regular_price'   => '85.99',
    'sale_price'      => '75.99',
    'about_points'    => array(
      'Upgraded copper brushless motor: features a 1000W pure copper motor for powerful, durable cutting that helps prevent jamming.',
      'Cordless battery compatibility: designed for Makita 18V battery platform (tool only, no battery or charger included).',
      'Automatic oiler system: includes a pure copper oil pump that auto-lubricates the chain during use for smoother cutting and reduced wear.',
      'Quick and secure installation: tool-free chain tension adjustment with double-nut guide plate fixing for easier maintenance.',
      'Lightweight and user-friendly: weighs about 5 lbs, reaches 13 m/s chain speed, and includes storage bag, gloves, two 12-inch chains, and a protective cover.',
    ),
    'shipping_text'   => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'      => array(),
  );
}

function powerup_theme_get_reference_product_payload_paint_sprayer_dewalt() {
  return array(
    'source_key'      => 'ps-dewalt-001',
    'title'           => 'Cordless Paint Sprayer Tool Only for DeWalt 20V Battery, HVLP Electric Spray Gun for Furniture, Fence, and Wall Painting',
    'slug'            => 'cordless-paint-sprayer-for-dewalt-20v-tool-only',
    'search_term'     => 'dewalt paint sprayer',
    'excerpt'         => 'Cordless HVLP paint sprayer tool-only model compatible with DeWalt 20V batteries for home and workshop painting jobs.',
    'category'        => 'paint-sprayer',
    'category_name'   => 'Paint Sprayer',
    'specs'           => array(
      'battery_platform' => 'dewalt',
    ),
    'tags'            => array( 'paint sprayer', 'dewalt sprayer', 'hvlp sprayer' ),
    'regular_price'   => '89.99',
    'sale_price'      => '79.99',
    'about_points'    => array(
      'Cordless HVLP atomization design for smoother and more even coating on wood, metal, and walls.',
      'Compatible with DeWalt 20V battery platform (tool only, battery and charger not included).',
      'Adjustable spray pattern and flow control for primer, stain, and finishing coats.',
      'Detachable nozzle assembly for faster cleaning and easier maintenance after each use.',
    ),
    'shipping_text'   => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'      => array(),
  );
}

function powerup_theme_get_reference_product_payload_paint_sprayer_milwaukee() {
  return array(
    'source_key'      => 'ps-milwaukee-001',
    'title'           => 'Cordless Paint Sprayer Tool Only for Milwaukee M18 Battery, HVLP Electric Spray Gun for Cabinet, Fence, and Wall Painting',
    'slug'            => 'cordless-paint-sprayer-for-milwaukee-m18-tool-only',
    'search_term'     => 'milwaukee paint sprayer',
    'excerpt'         => 'Cordless HVLP paint sprayer tool-only model compatible with Milwaukee M18 batteries for interior and exterior painting.',
    'category'        => 'paint-sprayer',
    'category_name'   => 'Paint Sprayer',
    'specs'           => array(
      'battery_platform' => 'milwaukee',
    ),
    'tags'            => array( 'paint sprayer', 'milwaukee sprayer', 'hvlp sprayer' ),
    'regular_price'   => '89.99',
    'sale_price'      => '79.99',
    'about_points'    => array(
      'Cordless HVLP atomization design for smoother and more even coating on wood, metal, and walls.',
      'Compatible with Milwaukee M18 battery platform (tool only, battery and charger not included).',
      'Adjustable spray pattern and flow control for primer, stain, and finishing coats.',
      'Detachable nozzle assembly for faster cleaning and easier maintenance after each use.',
    ),
    'shipping_text'   => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'      => array(),
  );
}

function powerup_theme_get_reference_product_payload_paint_sprayer_makita() {
  return array(
    'source_key'      => 'ps-makita-001',
    'title'           => 'Cordless Paint Sprayer Tool Only for Makita 18V Battery, HVLP Electric Spray Gun for Furniture, Deck, and Wall Painting',
    'slug'            => 'cordless-paint-sprayer-for-makita-18v-tool-only',
    'search_term'     => 'makita paint sprayer',
    'excerpt'         => 'Cordless HVLP paint sprayer tool-only model compatible with Makita 18V batteries for household and workshop projects.',
    'category'        => 'paint-sprayer',
    'category_name'   => 'Paint Sprayer',
    'specs'           => array(
      'battery_platform' => 'makita',
    ),
    'tags'            => array( 'paint sprayer', 'makita sprayer', 'hvlp sprayer' ),
    'regular_price'   => '89.99',
    'sale_price'      => '79.99',
    'about_points'    => array(
      'Cordless HVLP atomization design for smoother and more even coating on wood, metal, and walls.',
      'Compatible with Makita 18V battery platform (tool only, battery and charger not included).',
      'Adjustable spray pattern and flow control for primer, stain, and finishing coats.',
      'Detachable nozzle assembly for faster cleaning and easier maintenance after each use.',
    ),
    'shipping_text'   => 'Orders are typically processed within 1-2 business days. Standard shipping takes 3-7 business days depending on destination. Expedited options may be available at checkout. Warranty support is provided for 12 months from purchase date.',
    'image_urls'      => array(),
  );
}

function powerup_theme_find_or_create_target_product_for_reference_sync( $payload ) {
  $candidate_slugs = array();
  if ( ! empty( $payload['slug'] ) ) {
    $candidate_slugs[] = (string) $payload['slug'];
  }
  if ( '29474' === ( $payload['source_key'] ?? '' ) ) {
    $candidate_slugs[] = 'cordless-chainsaw-pro';
    $candidate_slugs[] = '12-electric-chainsaw-cordless-20v-battery-powered-cordless-chain-saw';
  }

  foreach ( $candidate_slugs as $slug ) {
    $post = get_page_by_path( $slug, OBJECT, 'product' );
    if ( $post instanceof WP_Post ) {
      return $post;
    }
  }

  $posts = get_posts(
    array(
      'post_type'      => 'product',
      'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
      'posts_per_page' => 1,
      's'              => isset( $payload['search_term'] ) ? (string) $payload['search_term'] : 'chainsaw',
      'orderby'        => 'date',
      'order'          => 'DESC',
    )
  );

  if ( ! empty( $posts ) && $posts[0] instanceof WP_Post ) {
    return $posts[0];
  }

  $post_id = wp_insert_post(
    array(
      'post_type'    => 'product',
      'post_status'  => 'publish',
      'post_title'   => (string) $payload['title'],
      'post_name'    => sanitize_title( (string) $candidate_slugs[0] ),
      'post_content' => '',
      'post_excerpt' => '',
    ),
    true
  );

  if ( is_wp_error( $post_id ) || ! $post_id ) {
    return null;
  }

  wp_set_object_terms( $post_id, 'simple', 'product_type' );

  return get_post( $post_id );

}

function powerup_theme_apply_reference_payload_to_product( $payload, $target ) {
  if ( ! $target instanceof WP_Post ) {
    return false;
  }

  $content_blocks = array();
  if ( ! empty( $payload['about_points'] ) && is_array( $payload['about_points'] ) ) {
    $content_blocks[] = '<h3>About this item</h3>';
    $content_blocks[] = '<ul><li>' . implode( '</li><li>', array_map( 'esc_html', $payload['about_points'] ) ) . '</li></ul>';
  }
  if ( ! empty( $payload['shipping_text'] ) ) {
    $content_blocks[] = '<h3>Shipping and delivery</h3>';
    $content_blocks[] = wpautop( esc_html( (string) $payload['shipping_text'] ) );
  }

  wp_update_post(
    array(
      'ID'         => $target->ID,
      'post_title' => $payload['title'],
      'post_name'  => sanitize_title( (string) $payload['slug'] ),
      'post_excerpt' => isset( $payload['excerpt'] ) ? (string) $payload['excerpt'] : '',
      'post_content' => implode( "\n\n", $content_blocks ),
    )
  );

  if ( ! empty( $payload['category'] ) ) {
    $term = term_exists( (string) $payload['category'], 'product_cat' );
    if ( ! $term ) {
      $term = wp_insert_term(
        isset( $payload['category_name'] ) ? (string) $payload['category_name'] : (string) $payload['category'],
        'product_cat',
        array(
          'slug' => (string) $payload['category'],
        )
      );
    }

    if ( ! is_wp_error( $term ) ) {
      $term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
      if ( $term_id > 0 ) {
        wp_set_object_terms( $target->ID, array( $term_id ), 'product_cat', false );
      }
    }
  }

  if ( ! empty( $payload['tags'] ) && is_array( $payload['tags'] ) ) {
    wp_set_object_terms( $target->ID, array_map( 'strval', $payload['tags'] ), 'product_tag', false );
  }

  update_post_meta( $target->ID, '_regular_price', $payload['regular_price'] );
  update_post_meta( $target->ID, '_sale_price', $payload['sale_price'] );
  update_post_meta( $target->ID, '_price', $payload['sale_price'] );
  update_post_meta( $target->ID, '_powerup_about_item_points', implode( "\n", $payload['about_points'] ) );
  update_post_meta( $target->ID, '_powerup_shipping_delivery', $payload['shipping_text'] );

  if ( ! empty( $payload['specs'] ) && is_array( $payload['specs'] ) ) {
    foreach ( $payload['specs'] as $spec_key => $spec_value ) {
      if ( '' === (string) $spec_key || '' === (string) $spec_value ) {
        continue;
      }
      update_post_meta( $target->ID, '_powerup_spec_' . sanitize_key( (string) $spec_key ), sanitize_text_field( (string) $spec_value ) );
    }
  }

  $gallery_ids = array();
  foreach ( $payload['image_urls'] as $index => $image_url ) {
    $attachment_id = powerup_theme_get_or_import_attachment_from_url( $image_url, $target->ID );
    if ( $attachment_id <= 0 ) {
      continue;
    }

    if ( 0 === $index ) {
      set_post_thumbnail( $target->ID, $attachment_id );
    } else {
      $gallery_ids[] = $attachment_id;
    }
  }

  if ( ! empty( $gallery_ids ) ) {
    update_post_meta( $target->ID, '_product_image_gallery', implode( ',', array_map( 'absint', $gallery_ids ) ) );
  }

  return true;
}

function powerup_theme_sync_reference_product_once( $payload, $option_key, $lock_key ) {
  if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( get_option( $option_key, false ) ) {
    return;
  }

  if ( get_transient( $lock_key ) ) {
    return;
  }
  set_transient( $lock_key, 1, 5 * MINUTE_IN_SECONDS );

  $target = powerup_theme_find_or_create_target_product_for_reference_sync( $payload );

  if ( ! $target instanceof WP_Post ) {
    delete_transient( $lock_key );
    return;
  }

  $applied = powerup_theme_apply_reference_payload_to_product( $payload, $target );
  if ( $applied ) {
    update_option(
      $option_key,
      array(
        'product_id' => $target->ID,
        'time'       => current_time( 'mysql' ),
        'source_key' => (string) $payload['source_key'],
      ),
      false
    );
  }

  delete_transient( $lock_key );
}

function powerup_theme_sync_reference_product_29474_once() {
  powerup_theme_sync_reference_product_once(
    powerup_theme_get_reference_product_payload_29474(),
    'powerup_reference_product_29474_synced',
    'powerup_reference_product_29474_sync_lock'
  );
}

function powerup_theme_sync_reference_product_123_once() {
  powerup_theme_sync_reference_product_once(
    powerup_theme_get_reference_product_payload_123(),
    'powerup_reference_product_123_synced',
    'powerup_reference_product_123_sync_lock'
  );
}

function powerup_theme_sync_reference_product_4567_once() {
  powerup_theme_sync_reference_product_once(
    powerup_theme_get_reference_product_payload_4567(),
    'powerup_reference_product_4567_synced',
    'powerup_reference_product_4567_sync_lock'
  );
}

function powerup_theme_ensure_reference_products_by_slug_once() {
  if ( ! taxonomy_exists( 'product_cat' ) ) {
    return;
  }

  $state_option_key = 'powerup_reference_products_by_slug_synced_v3';
  $state = get_option( $state_option_key, array() );
  if ( is_array( $state ) && ! empty( $state['done'] ) ) {
    return;
  }

  $payloads = array(
    powerup_theme_get_reference_product_payload_123(),
    powerup_theme_get_reference_product_payload_4567(),
    powerup_theme_get_reference_product_payload_makita(),
    powerup_theme_get_reference_product_payload_paint_sprayer_dewalt(),
    powerup_theme_get_reference_product_payload_paint_sprayer_milwaukee(),
    powerup_theme_get_reference_product_payload_paint_sprayer_makita(),
  );

  $synced = array();

  foreach ( $payloads as $payload ) {
    $slug = isset( $payload['slug'] ) ? sanitize_title( (string) $payload['slug'] ) : '';
    if ( '' === $slug ) {
      continue;
    }

    $target = get_page_by_path( $slug, OBJECT, 'product' );
    if ( ! $target instanceof WP_Post ) {
      $post_id = wp_insert_post(
        array(
          'post_type'    => 'product',
          'post_status'  => 'publish',
          'post_title'   => (string) $payload['title'],
          'post_name'    => $slug,
          'post_excerpt' => isset( $payload['excerpt'] ) ? (string) $payload['excerpt'] : '',
          'post_content' => '',
        ),
        true
      );

      if ( is_wp_error( $post_id ) || ! $post_id ) {
        continue;
      }

      wp_set_object_terms( (int) $post_id, 'simple', 'product_type', false );
      $target = get_post( (int) $post_id );
    }

    if ( ! $target instanceof WP_Post ) {
      continue;
    }

    $lightweight_payload = $payload;
    $lightweight_payload['image_urls'] = array();

    if ( powerup_theme_apply_reference_payload_to_product( $lightweight_payload, $target ) ) {
      $synced[] = array(
        'id'   => (int) $target->ID,
        'slug' => $slug,
      );
    }
  }

  update_option(
    $state_option_key,
    array(
      'done'   => 1,
      'time'   => current_time( 'mysql' ),
      'items'  => $synced,
    ),
    false
  );
}

function powerup_theme_get_or_import_attachment_from_url( $url, $parent_post_id ) {
  $existing = get_posts(
    array(
      'post_type'      => 'attachment',
      'post_status'    => 'inherit',
      'posts_per_page' => 1,
      'meta_key'       => '_powerup_source_image_url',
      'meta_value'     => (string) $url,
      'fields'         => 'ids',
    )
  );

  if ( ! empty( $existing ) ) {
    return (int) $existing[0];
  }

  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';
  require_once ABSPATH . 'wp-admin/includes/image.php';

  $attachment_id = media_sideload_image( $url, $parent_post_id, null, 'id' );
  if ( is_wp_error( $attachment_id ) ) {
    return 0;
  }

  $attachment_id = (int) $attachment_id;
  if ( $attachment_id > 0 ) {
    update_post_meta( $attachment_id, '_powerup_source_image_url', (string) $url );
  }

  return $attachment_id;
}

function powerup_theme_get_product_category_tree_payload() {
  return array(
    array(
      'name'     => 'Chainsaw',
      'slug'     => 'chainsaw',
      'children' => array(
        array(
          'name' => '8-inch Electric Chainsaw',
          'slug' => '8-inch-electric-chainsaw',
          'children' => array(
            array( 'name' => 'Fits DeWalt', 'slug' => '8-inch-electric-chainsaw-fits-dewalt' ),
            array( 'name' => 'Fits Milwaukee', 'slug' => '8-inch-electric-chainsaw-fits-milwaukee' ),
            array( 'name' => 'Fits Makita', 'slug' => '8-inch-electric-chainsaw-fits-makita' ),
            array( 'name' => 'Fits RYOBI', 'slug' => '8-inch-electric-chainsaw-fits-ryobi' ),
          ),
        ),
        array(
          'name' => '12-inch Electric Chainsaw',
          'slug' => '12-inch-electric-chainsaw',
          'children' => array(
            array( 'name' => 'Fits DeWalt', 'slug' => '12-inch-electric-chainsaw-fits-dewalt' ),
            array( 'name' => 'Fits Milwaukee', 'slug' => '12-inch-electric-chainsaw-fits-milwaukee' ),
            array( 'name' => 'Fits Makita', 'slug' => '12-inch-electric-chainsaw-fits-makita' ),
            array( 'name' => 'Fits RYOBI', 'slug' => '12-inch-electric-chainsaw-fits-ryobi' ),
          ),
        ),
        array(
          'name' => '16-inch Electric Chainsaw',
          'slug' => '16-inch-electric-chainsaw',
          'children' => array(
            array( 'name' => 'Fits DeWalt', 'slug' => '16-inch-electric-chainsaw-fits-dewalt' ),
            array( 'name' => 'Fits Milwaukee', 'slug' => '16-inch-electric-chainsaw-fits-milwaukee' ),
            array( 'name' => 'Fits Makita', 'slug' => '16-inch-electric-chainsaw-fits-makita' ),
            array( 'name' => 'Fits RYOBI', 'slug' => '16-inch-electric-chainsaw-fits-ryobi' ),
          ),
        ),
      ),
    ),
    array(
      'name'     => 'Leaf Blower',
      'slug'     => 'leaf-blower',
      'children' => array(
        array( 'name' => 'Fits DeWalt', 'slug' => 'leaf-blower-fits-dewalt' ),
        array( 'name' => 'Fits Milwaukee', 'slug' => 'leaf-blower-fits-milwaukee' ),
        array( 'name' => 'Fits Makita', 'slug' => 'leaf-blower-fits-makita' ),
        array( 'name' => 'Fits RYOBI', 'slug' => 'leaf-blower-fits-ryobi' ),
      ),
    ),
    array(
      'name'     => 'Hedge Trimmer',
      'slug'     => 'hedge-trimmer',
      'children' => array(
        array( 'name' => 'Fits DeWalt', 'slug' => 'hedge-trimmer-fits-dewalt' ),
        array( 'name' => 'Fits Milwaukee', 'slug' => 'hedge-trimmer-fits-milwaukee' ),
        array( 'name' => 'Fits Makita', 'slug' => 'hedge-trimmer-fits-makita' ),
        array( 'name' => 'Fits RYOBI', 'slug' => 'hedge-trimmer-fits-ryobi' ),
      ),
    ),
    array(
      'name'     => 'Pruning Shears',
      'slug'     => 'pruning-shears',
      'children' => array(),
    ),
    array(
      'name'     => 'Electric Weed Wacker',
      'slug'     => 'electric-weed-wacker',
      'children' => array(
        array( 'name' => 'Fits DeWalt', 'slug' => 'electric-weed-wacker-fits-dewalt' ),
        array( 'name' => 'Fits Milwaukee', 'slug' => 'electric-weed-wacker-fits-milwaukee' ),
        array( 'name' => 'Fits Makita', 'slug' => 'electric-weed-wacker-fits-makita' ),
        array( 'name' => 'Fits RYOBI', 'slug' => 'electric-weed-wacker-fits-ryobi' ),
      ),
    ),
    array(
      'name'     => 'Laser Level',
      'slug'     => 'laser-level',
      'children' => array(
        array( 'name' => 'Fits DeWalt', 'slug' => 'laser-level-fits-dewalt' ),
        array( 'name' => 'Fits Milwaukee', 'slug' => 'laser-level-fits-milwaukee' ),
        array( 'name' => 'Fits Makita', 'slug' => 'laser-level-fits-makita' ),
        array( 'name' => 'Fits RYOBI', 'slug' => 'laser-level-fits-ryobi' ),
      ),
    ),
    array(
      'name'     => 'Battery Packs & Chargers',
      'slug'     => 'battery-packs-chargers',
      'children' => array(
        array( 'name' => 'Fits DeWalt', 'slug' => 'battery-packs-chargers-fits-dewalt' ),
        array( 'name' => 'Fits Milwaukee', 'slug' => 'battery-packs-chargers-fits-milwaukee' ),
        array( 'name' => 'Fits Makita', 'slug' => 'battery-packs-chargers-fits-makita' ),
        array( 'name' => 'Fits RYOBI', 'slug' => 'battery-packs-chargers-fits-ryobi' ),
      ),
    ),
    array(
      'name'     => 'Chainsaw Guide Bar',
      'slug'     => 'chainsaw-guide-bar',
      'children' => array(
        array(
          'name' => '6-inch Chainsaw Guide Bar',
          'slug' => '6-inch-chainsaw-guide-bar',
        ),
        array(
          'name' => '8-inch Chainsaw Guide Bar',
          'slug' => '8-inch-chainsaw-guide-bar',
        ),
        array(
          'name' => '12-inch Chainsaw Guide Bar',
          'slug' => '12-inch-chainsaw-guide-bar',
        ),
        array(
          'name' => '16-inch Chainsaw Guide Bar',
          'slug' => '16-inch-chainsaw-guide-bar',
        ),
      ),
    ),
    array(
      'name'     => 'Chainsaw Chain',
      'slug'     => 'chainsaw-chain',
      'children' => array(
        array(
          'name' => '6-inch Chainsaw Chain',
          'slug' => '6-inch-chainsaw-chain',
        ),
        array(
          'name' => '8-inch Chainsaw Chain',
          'slug' => '8-inch-chainsaw-chain',
        ),
        array(
          'name' => '12-inch Chainsaw Chain',
          'slug' => '12-inch-chainsaw-chain',
        ),
        array(
          'name' => '16-inch Chainsaw Chain',
          'slug' => '16-inch-chainsaw-chain',
        ),
      ),
    ),
  );
}

function powerup_theme_is_product_category_tree_sync_enabled() {
  $value = powerup_theme_get_config_value( 'shop.category_sync_enabled', 1 );
  return ! empty( $value );
}

function powerup_theme_upsert_product_category_term( $name, $slug, $parent_id = 0 ) {
  $existing = get_term_by( 'slug', (string) $slug, 'product_cat' );

  if ( $existing instanceof WP_Term ) {
    wp_update_term(
      (int) $existing->term_id,
      'product_cat',
      array(
        'name'   => (string) $name,
        'parent' => (int) $parent_id,
      )
    );
    return (int) $existing->term_id;
  }

  $created = wp_insert_term(
    (string) $name,
    'product_cat',
    array(
      'slug'   => (string) $slug,
      'parent' => (int) $parent_id,
    )
  );

  if ( is_wp_error( $created ) ) {
    return 0;
  }

  return is_array( $created ) ? (int) $created['term_id'] : (int) $created;
}

function powerup_theme_sync_product_category_nodes( $nodes, $parent_id = 0 ) {
  if ( empty( $nodes ) || ! is_array( $nodes ) ) {
    return;
  }

  foreach ( $nodes as $node ) {
    $term_id = powerup_theme_upsert_product_category_term(
      (string) ( $node['name'] ?? '' ),
      (string) ( $node['slug'] ?? '' ),
      (int) $parent_id
    );

    if ( $term_id <= 0 ) {
      continue;
    }

    if ( ! empty( $node['children'] ) && is_array( $node['children'] ) ) {
      powerup_theme_sync_product_category_nodes( $node['children'], $term_id );
    }
  }
}

function powerup_theme_collect_product_category_tree_slugs( $nodes ) {
  $slugs = array();

  if ( empty( $nodes ) || ! is_array( $nodes ) ) {
    return $slugs;
  }

  foreach ( $nodes as $node ) {
    if ( ! empty( $node['slug'] ) ) {
      $slugs[] = sanitize_title( (string) $node['slug'] );
    }

    if ( ! empty( $node['children'] ) && is_array( $node['children'] ) ) {
      $slugs = array_merge( $slugs, powerup_theme_collect_product_category_tree_slugs( $node['children'] ) );
    }
  }

  return array_values( array_unique( array_filter( $slugs ) ) );
}

function powerup_theme_prune_product_category_terms( $allowed_slugs ) {
  $terms = get_terms(
    array(
      'taxonomy'   => 'product_cat',
      'hide_empty' => false,
    )
  );

  if ( is_wp_error( $terms ) || empty( $terms ) ) {
    return;
  }

  usort(
    $terms,
    static function ( $left, $right ) {
      $left_depth  = count( get_ancestors( (int) $left->term_id, 'product_cat', 'taxonomy' ) );
      $right_depth = count( get_ancestors( (int) $right->term_id, 'product_cat', 'taxonomy' ) );
      return $right_depth <=> $left_depth;
    }
  );

  foreach ( $terms as $term ) {
    if ( ! $term instanceof WP_Term ) {
      continue;
    }

    if ( in_array( (string) $term->slug, $allowed_slugs, true ) ) {
      continue;
    }

    wp_delete_term( (int) $term->term_id, 'product_cat' );
  }
}

function powerup_theme_sync_product_category_tree_once() {
  if ( ! taxonomy_exists( 'product_cat' ) ) {
    return;
  }

  if ( ! powerup_theme_is_product_category_tree_sync_enabled() ) {
    return;
  }

  $payload   = powerup_theme_get_product_category_tree_payload();
  $signature = md5( wp_json_encode( $payload ) );
  $state     = get_option( 'powerup_product_category_tree_synced_v1', array() );

  if ( is_array( $state ) && isset( $state['signature'] ) && (string) $state['signature'] === $signature ) {
    return;
  }

  powerup_theme_sync_product_category_nodes( $payload, 0 );
  powerup_theme_prune_product_category_terms( powerup_theme_collect_product_category_tree_slugs( $payload ) );

  update_option(
    'powerup_product_category_tree_synced_v1',
    array(
      'signature' => $signature,
      'time'      => current_time( 'mysql' ),
    ),
    false
  );
}

function powerup_theme_detect_size_slug_from_text( $text ) {
  $normalized = strtolower( (string) $text );
  $map = array(
    '6-inch'  => array( '6(?:\s*|-|\s*[-]\s*)(?:"|in|inch)' ),
    '8-inch'  => array( '8(?:\s*|-|\s*[-]\s*)(?:"|in|inch)' ),
    '12-inch' => array( '12(?:\s*|-|\s*[-]\s*)(?:"|in|inch)' ),
    '14-inch' => array( '14(?:\s*|-|\s*[-]\s*)(?:"|in|inch)' ),
    '16-inch' => array( '16(?:\s*|-|\s*[-]\s*)(?:"|in|inch)' ),
  );

  foreach ( $map as $size_slug => $patterns ) {
    foreach ( $patterns as $pattern ) {
      if ( preg_match( '/(?:^|[^0-9])' . $pattern . '(?:[^a-z]|$)/i', $normalized ) ) {
        return $size_slug;
      }
    }
  }

  return '';
}

function powerup_theme_get_manual_product_specs_map() {
  return array(
    'cordless-chainsaw-pro' => array(
      'bar_length_inch' => '12',
    ),
  );
}

function powerup_theme_get_product_specs_from_post( $product_post ) {
  if ( ! $product_post instanceof WP_Post ) {
    return array();
  }

  $specs = array();

  $bar_length = get_post_meta( (int) $product_post->ID, '_powerup_spec_bar_length_inch', true );
  if ( '' !== (string) $bar_length ) {
    $specs['bar_length_inch'] = (string) $bar_length;
  }

  $battery_platform = get_post_meta( (int) $product_post->ID, '_powerup_spec_battery_platform', true );
  if ( '' !== (string) $battery_platform ) {
    $specs['battery_platform'] = strtolower( (string) $battery_platform );
  }

  $manual_map = powerup_theme_get_manual_product_specs_map();
  $post_slug  = (string) $product_post->post_name;
  if ( isset( $manual_map[ $post_slug ] ) && is_array( $manual_map[ $post_slug ] ) ) {
    $specs = array_merge( $manual_map[ $post_slug ], $specs );
  }

  return $specs;
}

function powerup_theme_get_auto_category_slugs_for_product( $product_post ) {
  if ( ! $product_post instanceof WP_Post ) {
    return array();
  }

  $specs = powerup_theme_get_product_specs_from_post( $product_post );

  $haystack = strtolower(
    implode(
      ' ',
      array(
        (string) $product_post->post_title,
        (string) $product_post->post_name,
        wp_strip_all_tags( (string) $product_post->post_excerpt ),
        wp_strip_all_tags( (string) $product_post->post_content ),
      )
    )
  );

  $slugs = array();

  $is_guide_bar     = ( false !== strpos( $haystack, 'guide bar' ) || false !== strpos( $haystack, 'bar for chainsaw' ) );
  $is_chain         = ( false !== strpos( $haystack, 'chainsaw chain' ) || false !== strpos( $haystack, 'saw chain' ) );
  $is_leaf_blower   = ( false !== strpos( $haystack, 'leaf blower' ) || false !== strpos( $haystack, 'blower' ) );
  $is_hedge_trimmer = ( false !== strpos( $haystack, 'hedge trimmer' ) || false !== strpos( $haystack, 'trimmer' ) );
  $is_pruning_shear = ( false !== strpos( $haystack, 'pruning shears' ) || false !== strpos( $haystack, 'pruner' ) || false !== strpos( $haystack, 'pruning shear' ) );
  $is_weed_wacker   = ( false !== strpos( $haystack, 'weed wacker' ) || false !== strpos( $haystack, 'grass trimmer' ) || false !== strpos( $haystack, 'string trimmer' ) );
  $is_laser_level   = ( false !== strpos( $haystack, 'laser level' ) || false !== strpos( $haystack, 'lasers level' ) );
  $is_tool_only     = ( false !== strpos( $haystack, 'tool only' ) || false !== strpos( $haystack, 'tool-only' ) );
  $is_battery_pack  = ! $is_tool_only && ( false !== strpos( $haystack, 'battery pack' ) || false !== strpos( $haystack, 'battery packs' ) || false !== strpos( $haystack, 'battery charger' ) || false !== strpos( $haystack, 'replacement battery' ) || false !== strpos( $haystack, 'charger only' ) || false !== strpos( $haystack, 'fast charger' ) );
  $is_chainsaw      = ( false !== strpos( $haystack, 'chainsaw' ) || false !== strpos( $haystack, 'chain saw' ) );

  $platform_slug = '';
  if ( false !== strpos( $haystack, 'dewalt' ) ) {
    $platform_slug = 'dewalt';
  } elseif ( false !== strpos( $haystack, 'milwaukee' ) ) {
    $platform_slug = 'milwaukee';
  } elseif ( false !== strpos( $haystack, 'makita' ) ) {
    $platform_slug = 'makita';
  } elseif ( false !== strpos( $haystack, 'ryobi' ) ) {
    $platform_slug = 'ryobi';
  }

  if ( isset( $specs['battery_platform'] ) ) {
    $platform = strtolower( (string) $specs['battery_platform'] );
    if ( in_array( $platform, array( 'dewalt', 'milwaukee', 'makita', 'ryobi' ), true ) ) {
      $platform_slug = $platform;
    }
  }

  if ( $is_guide_bar ) {
    $slugs[] = 'chainsaw-guide-bar';
  } elseif ( $is_chain ) {
    $slugs[] = 'chainsaw-chain';
  } elseif ( $is_leaf_blower ) {
    $slugs[] = 'leaf-blower';
  } elseif ( $is_hedge_trimmer ) {
    $slugs[] = 'hedge-trimmer';
  } elseif ( $is_weed_wacker ) {
    $slugs[] = 'electric-weed-wacker';
  } elseif ( $is_laser_level ) {
    $slugs[] = 'laser-level';
  } elseif ( $is_battery_pack ) {
    $slugs[] = 'battery-packs-chargers';
  } elseif ( $is_pruning_shear ) {
    $slugs[] = 'pruning-shears';
  } elseif ( $is_chainsaw ) {
    $slugs[] = 'chainsaw';
  }

  $size_slug = powerup_theme_detect_size_slug_from_text( $haystack );
  if ( isset( $specs['bar_length_inch'] ) && '' !== (string) $specs['bar_length_inch'] ) {
    $size_slug = sanitize_title( (string) $specs['bar_length_inch'] ) . '-inch';
  }
  if ( '' !== $size_slug ) {
    if ( in_array( 'chainsaw', $slugs, true ) ) {
      $slugs[] = $size_slug . '-electric-chainsaw';
    }
    if ( in_array( 'chainsaw-chain', $slugs, true ) ) {
      $slugs[] = $size_slug . '-chainsaw-chain';
    }
    if ( in_array( 'chainsaw-guide-bar', $slugs, true ) ) {
      $slugs[] = $size_slug . '-chainsaw-guide-bar';
    }
  }

  if ( '' !== $platform_slug ) {
    if ( in_array( 'chainsaw', $slugs, true ) && '' !== $size_slug ) {
      $slugs[] = $size_slug . '-electric-chainsaw-fits-' . $platform_slug;
    }
    if ( in_array( 'leaf-blower', $slugs, true ) ) {
      $slugs[] = 'leaf-blower-fits-' . $platform_slug;
    }
    if ( in_array( 'hedge-trimmer', $slugs, true ) ) {
      $slugs[] = 'hedge-trimmer-fits-' . $platform_slug;
    }
    if ( in_array( 'electric-weed-wacker', $slugs, true ) ) {
      $slugs[] = 'electric-weed-wacker-fits-' . $platform_slug;
    }
    if ( in_array( 'laser-level', $slugs, true ) ) {
      $slugs[] = 'laser-level-fits-' . $platform_slug;
    }
    if ( in_array( 'battery-packs-chargers', $slugs, true ) ) {
      $slugs[] = 'battery-packs-chargers-fits-' . $platform_slug;
    }
  }

  return array_values( array_unique( array_filter( array_map( 'sanitize_title', $slugs ) ) ) );
}

function powerup_theme_sync_product_auto_categories_once() {
  if ( ! taxonomy_exists( 'product_cat' ) ) {
    return;
  }

  $state_option_key = 'powerup_product_auto_category_assignment_v10';
  $state = get_option( $state_option_key, array() );
  if ( is_array( $state ) && ! empty( $state['done'] ) ) {
    return;
  }

  $products = get_posts(
    array(
      'post_type'      => 'product',
      'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
      'posts_per_page' => -1,
      'fields'         => 'ids',
      'no_found_rows'  => true,
    )
  );

  if ( empty( $products ) ) {
    update_option( $state_option_key, array( 'done' => 1, 'time' => current_time( 'mysql' ), 'assigned' => 0 ), false );
    return;
  }

  $assigned_count = 0;

  foreach ( $products as $product_id ) {
    $product_id = (int) $product_id;
    $post       = get_post( $product_id );
    if ( ! $post instanceof WP_Post ) {
      continue;
    }

    $mapped_slugs = powerup_theme_get_auto_category_slugs_for_product( $post );
    if ( empty( $mapped_slugs ) ) {
      $mapped_slugs = array( 'chainsaw' );
    }

    $mapped_term_ids = array();
    foreach ( $mapped_slugs as $slug ) {
      $term = get_term_by( 'slug', $slug, 'product_cat' );
      if ( $term instanceof WP_Term ) {
        $mapped_term_ids[] = (int) $term->term_id;
      }
    }

    if ( empty( $mapped_term_ids ) ) {
      continue;
    }

    $final_term_ids = array_values( array_unique( array_map( 'intval', $mapped_term_ids ) ) );
    if ( empty( $final_term_ids ) ) {
      continue;
    }

    wp_set_object_terms( $product_id, $final_term_ids, 'product_cat', false );
    $assigned_count++;
  }

  update_option(
    $state_option_key,
    array(
      'done'     => 1,
      'time'     => current_time( 'mysql' ),
      'assigned' => (int) $assigned_count,
    ),
    false
  );

  powerup_theme_clear_shop_price_range_cache();
}

add_action( 'admin_init', 'powerup_theme_sync_reference_product_29474_once', 15 );
add_action( 'admin_init', 'powerup_theme_sync_reference_product_123_once', 16 );
add_action( 'admin_init', 'powerup_theme_sync_reference_product_4567_once', 17 );
add_action( 'init', 'powerup_theme_ensure_reference_products_by_slug_once', 28 );
add_action( 'init', 'powerup_theme_sync_product_category_tree_once', 29 );
add_action( 'init', 'powerup_theme_sync_product_auto_categories_once', 30 );

function powerup_theme_clear_shop_price_range_cache() {
  delete_transient( 'powerup_shop_price_ranges_v1' );
  delete_transient( 'powerup_shop_hero_image_v1' );
  delete_transient( 'powerup_shop_category_items_v1' );
  powerup_theme_bump_shop_cache_version();
}

function powerup_theme_get_shop_cache_version() {
  $version = (int) get_option( 'powerup_shop_cache_version', 1 );
  return max( 1, $version );
}

function powerup_theme_bump_shop_cache_version() {
  $version = powerup_theme_get_shop_cache_version() + 1;
  update_option( 'powerup_shop_cache_version', $version, false );
}

function powerup_theme_get_generated_image_url( $prompt, $size ) {
  $base_url = trim( (string) powerup_theme_get_config_value( 'media.generated_image_base_url', '' ) );

  if ( '' === $base_url ) {
    return get_template_directory_uri() . '/assets/images/product-placeholder.svg';
  }

  return add_query_arg(
    array(
      'prompt'     => (string) $prompt,
      'image_size' => (string) $size,
    ),
    $base_url
  );
}

function powerup_theme_clear_shop_price_range_cache_on_product_save( $post_id, $post ) {
  if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
    return;
  }

  if ( wp_is_post_revision( $post_id ) ) {
    return;
  }

  powerup_theme_clear_shop_price_range_cache();
}
add_action( 'save_post_product', 'powerup_theme_clear_shop_price_range_cache_on_product_save', 10, 2 );

function powerup_theme_clear_shop_price_range_cache_on_deleted_post( $post_id ) {
  if ( 'product' !== get_post_type( $post_id ) ) {
    return;
  }

  powerup_theme_clear_shop_price_range_cache();
}
add_action( 'deleted_post', 'powerup_theme_clear_shop_price_range_cache_on_deleted_post' );
add_action( 'woocommerce_update_product', 'powerup_theme_clear_shop_price_range_cache' );
