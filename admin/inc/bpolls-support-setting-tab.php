<?php
/**
 * FAQ tab for WB Polls Lite.
 *
 * @package WB_Polls_Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wbcom-tab-content">
<div class="wbcom-faq-adming-setting">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Have some questions?', 'buddypress-polls' ); ?></h3>
	</div>
<div class="wbcom-faq-admin-settings-block">
<div id="wbcom-faq-settings-section">
		<div class="wbcom-faq-block-contain">
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Does WB Polls Lite require BuddyPress?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'No. WB Polls Lite works as a standalone WordPress plugin. You can create and manage polls using shortcodes and the REST API on any WordPress site. If BuddyPress is active, polls can also appear in activity streams and groups.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'How do I create a poll?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Go to WB Polls > Add New in the admin dashboard. Enter your question, add answer options, and publish. You can also let users create polls from the frontend using the Poll Dashboard shortcode.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'How do I display a poll on a page?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Use the [wbcom_poll id="123"] shortcode to display a specific poll. Use [wbcom_polls_dashboard] to show the full poll dashboard where users can create and manage their polls.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can guests vote without logging in?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes. Guest voting is supported and uses cookies to prevent duplicate votes. You can enable or disable guest voting from the Polls Settings tab.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can I schedule a poll to close automatically?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes. When creating a poll you can set a closing date and time. Once the deadline passes, the poll stops accepting votes and only shows results.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What is a multi-select poll?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'A multi-select poll lets voters choose more than one option. When creating a poll, toggle the multi-select setting so users can pick multiple answers instead of just one.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'Can I hide poll results until a user votes?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes. Enable the "Hide results" setting in Polls Settings. Users will only see results after they have voted.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What is the difference between Lite and Pro?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'WB Polls Lite includes text-based polls, frontend dashboard, guest voting, scheduling, multi-select, and BuddyPress integration. The Pro version adds image, video, and audio polls, surveys, CSV export, WP-CLI commands, and priority support.', 'buddypress-polls' ); ?>
						</p>
						<p>
							<?php
							printf(
								/* translators: %s: upgrade tab URL */
								esc_html__( 'See the full comparison on the %s tab.', 'buddypress-polls' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=buddypress-polls&tab=upgrade' ) ) . '">' . esc_html__( 'Upgrade to Pro', 'buddypress-polls' ) . '</a>'
							);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
