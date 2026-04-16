<?php
/**
 * Template Name: About Us Page
 *
 * @package PowerUp_Theme
 */
get_header(); ?>
<?php
$shop_url    = home_url('/shop/');
$contact_url = home_url('/contact-us/');

$about_mission_prompt = powerup_theme_get_config_value( 'about.prompts.mission', 'three men working with power tools and laptops in a workshop' );
$about_team_prompt = powerup_theme_get_config_value( 'about.prompts.team', 'professional team in business attire posing for a photo' );
$about_chainsaw_prompt = powerup_theme_get_config_value( 'about.prompts.chainsaw', 'chainsaw with orange and black design' );
$about_hedge_prompt = powerup_theme_get_config_value( 'about.prompts.hedge_trimmer', 'hedge trimmer with orange and black design' );
$about_string_prompt = powerup_theme_get_config_value( 'about.prompts.string_trimmer', 'string trimmer with orange and black design' );
$about_blower_prompt = powerup_theme_get_config_value( 'about.prompts.leaf_blower', 'leaf blower with orange and black design' );
$about_image_fallback = get_template_directory_uri() . '/assets/images/product-placeholder.svg';

$mission_image = get_template_directory_uri() . '/assets/images/about-highlight-custom.jpg';

$team_image = get_template_directory_uri() . '/assets/images/about-team-custom.jpg';

$chainsaw_image = function_exists( 'powerup_theme_get_generated_image_url' )
  ? powerup_theme_get_generated_image_url( $about_chainsaw_prompt, 'square' )
  : $about_image_fallback;

$hedge_image = function_exists( 'powerup_theme_get_generated_image_url' )
  ? powerup_theme_get_generated_image_url( $about_hedge_prompt, 'square' )
  : $about_image_fallback;

$string_trimmer_image = function_exists( 'powerup_theme_get_generated_image_url' )
  ? powerup_theme_get_generated_image_url( $about_string_prompt, 'square' )
  : $about_image_fallback;

$blower_image = function_exists( 'powerup_theme_get_generated_image_url' )
  ? powerup_theme_get_generated_image_url( $about_blower_prompt, 'square' )
  : $about_image_fallback;
?>

<!-- Hero Section -->
<section class="hero-section about-hero about-hero-main-bg">
  <div class="site-inner about-inner">
    <h1 class="about-title">ABOUT US</h1>
    <h2 class="about-subtitle">Unrivaled Power &amp; Portability for Every Cut</h2>
    <div class="about-hero-actions">
      <a href="<?php echo esc_url($shop_url); ?>" class="about-hero-btn about-hero-btn-primary">SHOP NOW</a>
      <a href="<?php echo esc_url($shop_url); ?>" class="about-hero-btn about-hero-btn-outline">LEARN MORE</a>
    </div>
  </div>
</section>

<!-- Features Icons -->
<section class="about-feature-band">
  <div class="site-inner about-inner">
    <div class="about-feature-grid">
      <div class="about-feature-item">
        <div class="about-feature-icon">🔋</div>
        <p>High-Density Lithium Platform</p>
      </div>
      <div class="about-feature-item">
        <div class="about-feature-icon">🔧</div>
        <p>Long Runtime, Fast Charging</p>
      </div>
      <div class="about-feature-item">
        <div class="about-feature-icon">⚙️</div>
        <p>Brushless Motor Efficiency</p>
      </div>
      <div class="about-feature-item">
        <div class="about-feature-icon">✅</div>
        <p>Built for Real-World Durability</p>
      </div>
    </div>
  </div>
</section>

<main class="site-section about-main">
  <div class="site-inner about-inner">
    <div class="about-highlight-area">
    <!-- Our Mission -->
    <section class="about-section about-section--mission">
      <h2 class="about-section-title">OUR MISSION</h2>
      <div class="about-mission-layout">
        <div class="about-mission-left">
          <img src="<?php echo esc_url( $mission_image ); ?>" alt="Our Mission" width="960" height="720" loading="lazy" decoding="async" class="about-media">
        </div>

        <div class="about-mission-right">
          <div class="about-review-card">
            <div class="about-stars">★★★★★</div>
            <h3 class="about-subheading">Powerful and easy to use</h3>
            <p class="about-copy">Built for daily cutting work, our lithium tools combine fast startup, stable torque, and comfortable handling. You get consistent performance with lower maintenance and less noise compared with fuel tools.</p>
          </div>

          <div class="about-history-card">
            <h2 class="about-section-title about-history-title">OUR HISTORY</h2>
            <p class="about-copy about-copy-gap">From our first battery platform to a full cordless lineup, we have focused on reliability, safety, and practical innovation for real jobsite use.</p>
            <ul class="about-list about-history-list">
              <li>2018: First 40V battery system and brushless chainsaw prototype.</li>
              <li>2020: Expanded into hedge trimmers, string trimmers, and blowers.</li>
              <li>2023: Upgraded BMS and launched fast charging ecosystem.</li>
              <li>Today: Scaling global retail and distribution partnerships.</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Values -->
    <section class="about-section">
      <div class="about-values-with-team">
        <div class="about-values-left">
          <h2 class="about-section-title">OUR VALUES</h2>
          <div class="about-values-grid">
            <div class="about-value-card">
              <div class="about-value-icon">🌱</div>
              <h3>Sustainable Performance</h3>
              <p>Lower emissions, less maintenance, and longer product life through better design.</p>
            </div>
            <div class="about-value-card">
              <div class="about-value-icon">🔄</div>
              <h3>Platform Compatibility</h3>
              <p>One battery system across multiple tools to simplify inventory and field operations.</p>
            </div>
            <div class="about-value-card">
              <div class="about-value-icon">🛡️</div>
              <h3>Quality &amp; Safety First</h3>
              <p>Strict testing for battery protection, thermal control, and long-cycle reliability.</p>
            </div>
          </div>
        </div>
        <div class="about-values-right">
          <img src="<?php echo esc_url( $team_image ); ?>" alt="Our Team" width="960" height="720" loading="lazy" decoding="async" class="about-media about-team-photo">
        </div>
      </div>
    </section>
    </div>

    <!-- Featured Reviews -->
    <section class="about-section about-section--featured-reviews">
      <h2 class="about-section-title about-section-title--center">FEATURED REVIEWS</h2>
      <div class="about-products-grid">
        <div class="about-product-item">
          <img src="<?php echo esc_url( $chainsaw_image ); ?>" alt="Chainsaw" width="800" height="800" loading="lazy" decoding="async" class="about-media">
          <p>Cordless Chainsaws</p>
        </div>
        <div class="about-product-item">
          <img src="<?php echo esc_url( $hedge_image ); ?>" alt="Hedge Trimmer" width="800" height="800" loading="lazy" decoding="async" class="about-media">
          <p>Hedge Trimmers</p>
        </div>
        <div class="about-product-item">
          <img src="<?php echo esc_url( $string_trimmer_image ); ?>" alt="String Trimmer" width="800" height="800" loading="lazy" decoding="async" class="about-media">
          <p>String Trimmers</p>
        </div>
        <div class="about-product-item">
          <img src="<?php echo esc_url( $blower_image ); ?>" alt="Leaf Blower" width="800" height="800" loading="lazy" decoding="async" class="about-media">
          <p>Leaf Blowers</p>
        </div>
      </div>
    </section>

    <!-- Newsletter -->
    <section class="about-newsletter">
      <h2>SUBSCRIBE TO OUR NEWSLETTER</h2>
      <p>Get product launches, battery care tips, and dealer offers in your inbox.</p>
      <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'subscribe', 'is-inline-dark' ); } ?>
      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="about-newsletter-form">
        <input type="email" name="subscriber_email" placeholder="Enter your email address" class="about-newsletter-input" required>
        <?php wp_nonce_field( 'powerup_subscribe_submit', 'powerup_subscribe_nonce' ); ?>
        <button type="submit" class="about-newsletter-btn">SUBSCRIBE</button>
        <input type="hidden" name="action" value="powerup_subscribe">
      </form>
    </section>
  </div>
</main>

<?php get_footer(); ?>