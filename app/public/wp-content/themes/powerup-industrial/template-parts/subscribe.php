<?php
defined('ABSPATH') || exit;
?>
<section class="subscribe-section">
    <div class="subscribe-inner">
        <h2><?php _e('Subscribe to Our Newsletter', 'powerup-industrial'); ?></h2>
        <p><?php _e('Get the Latest Deals & Updates', 'powerup-industrial'); ?></p>
        <?php if ( function_exists( 'powerup_render_form_notice' ) ) { powerup_render_form_notice( 'subscribe', 'is-inline-dark' ); } ?>
        <form class="subscribe-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="email" name="subscriber_email" placeholder="<?php esc_attr_e('Enter your email address', 'powerup-industrial'); ?>" required>
            <button class="btn btn-primary" type="submit"><?php _e('Subscribe', 'powerup-industrial'); ?></button>
            <?php wp_nonce_field( 'powerup_subscribe_submit', 'powerup_subscribe_nonce' ); ?>
            <input type="hidden" name="action" value="powerup_subscribe">
        </form>
    </div>
</section>
