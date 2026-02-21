<?php
/**
 *
 * This file is used for rendering and saving plugin welcome settings.
 *
 * @package    Buddypress_Polls
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wp_rewrite;
$wp_rewrite->flush_rules();
?>
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="wbcom-welcome-head">
			<p class="wbcom-welcome-description"><?php esc_html_e( 'WB Polls lets you create engaging polls for your community. Use standalone polls via shortcodes and REST API, or integrate with BuddyPress activity streams and groups.', 'buddypress-polls' ); ?></p>
		</div><!-- .wbcom-welcome-head -->

		<div class="wbcom-welcome-content">
			<div class="wbcom-welcome-support-info">
				<h3><?php esc_html_e( 'Help &amp; Support Resources', 'buddypress-polls' ); ?></h3>
				<p><?php esc_html_e( 'Here are all the resources you may need to get help from us. Documentation is usually the best place to start. Should you require help anytime, our customer care team is available to assist you at the support center.', 'buddypress-polls' ); ?></p>

				<div class="wbcom-support-info-wrap">
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-book"></span><?php esc_html_e( 'Documentation', 'buddypress-polls' ); ?></h3>
						<p><?php esc_html_e( 'We have prepared an extensive guide on WB Polls to learn all aspects of the plugin. You will find most of your answers here.', 'buddypress-polls' ); ?></p>
						<a href="<?php echo esc_url( 'https://wbcomdesigns.com/docs/free-starter-starter-plugins/wb-polls-lite/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Read Documentation', 'buddypress-polls' ); ?></a>
						</div>
					</div>

					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-sos"></span><?php esc_html_e( 'Support Center', 'buddypress-polls' ); ?></h3>
						<p><?php esc_html_e( 'We strive to offer the best customer care via our support center. Once the plugin is activated, you can ask us for help anytime.', 'buddypress-polls' ); ?></p>
						<a href="<?php echo esc_url( 'https://wbcomdesigns.com/support/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Get Support', 'buddypress-polls' ); ?></a>
					</div>
					</div>
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
						<h3><span class="dashicons dashicons-admin-comments"></span><?php esc_html_e( 'Got Feedback?', 'buddypress-polls' ); ?></h3>
						<p><?php esc_html_e( 'We want to hear about your experience with the plugin. We would also love to hear any suggestions you may for future updates.', 'buddypress-polls' ); ?></p>
						<a href="<?php echo esc_url( 'https://wbcomdesigns.com/contact/' ); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e( 'Send Feedback', 'buddypress-polls' ); ?></a>
					</div>
					</div>
				</div>
			</div>
		</div>

	</div><!-- .wbcom-welcome-content -->
</div><!-- .wbcom-welcome-main-wrapper -->
