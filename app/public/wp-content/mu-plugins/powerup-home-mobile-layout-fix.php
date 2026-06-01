<?php
/**
 * Plugin Name: PowerUp Home Mobile Layout Fix
 * Description: Adds narrow-screen homepage containment fixes.
 * Version: 2026.05.30.1
 */

defined( 'ABSPATH' ) || exit;

function powerup_home_mobile_layout_fix_output() {
	if ( ! is_front_page() ) {
		return;
	}
	?>
	<style id="powerup-home-mobile-layout-fix">
		@media (max-width: 680px) {
			.home-series-showcase__inner,
			.home-series-showcase__grid,
			.home-series-showcase__intro,
			.home-series-card {
				width: 100%;
				min-width: 0;
				max-width: 100%;
				box-sizing: border-box;
			}
		}
	</style>
	<?php
}
add_action( 'wp_head', 'powerup_home_mobile_layout_fix_output', 35 );
