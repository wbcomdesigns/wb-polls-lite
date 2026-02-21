<?php
/**
 * Upgrade to Pro comparison tab.
 *
 * @package WB_Polls_Lite
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	.bpolls-upgrade-wrap .wbcom-support-info-widgets {
		max-width: 420px !important;
	}
	.bpolls-upgrade-wrap .wbcom-support-inner ul {
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.bpolls-upgrade-wrap .wbcom-support-inner ul li {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 0;
		font-size: 14px;
		color: #1D2A4F;
		border-bottom: 1px solid #f0f0f1;
	}
	.bpolls-upgrade-wrap .wbcom-support-inner ul li:last-child {
		border-bottom: none;
	}
	.bpolls-upgrade-wrap .wbcom-support-inner ul li .dashicons {
		flex-shrink: 0;
		width: 20px;
		height: 20px;
		font-size: 20px;
	}
	.bpolls-upgrade-wrap .wbcom-support-inner h3 {
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.bpolls-upgrade-wrap .wbcom-welcome-support-info .wbcom-support-inner p {
		height: auto;
	}
	.bpolls-upgrade-wrap .wbcom-support-info-wrap {
		margin-top: 10px;
		margin-bottom: 0;
	}
	.bpolls-upgrade-wrap .bpolls-cta {
		text-align: center;
		margin: 25px 0 10px;
	}
	.bpolls-upgrade-wrap .bpolls-cta .button-welcome-support {
		font-size: 16px !important;
		padding: 12px 32px !important;
		height: auto !important;
		line-height: 1.5 !important;
	}
</style>
<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper bpolls-upgrade-wrap">
		<div class="wbcom-welcome-head">
			<p class="wbcom-welcome-description"><?php esc_html_e( 'WB Polls Lite includes everything you need to create engaging polls. Upgrade to Pro for media polls, surveys, CSV export, and more.', 'buddypress-polls' ); ?></p>
		</div>

		<div class="wbcom-welcome-content">
			<div class="wbcom-welcome-support-info">
				<div class="wbcom-support-info-wrap">
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
							<h3><span class="dashicons dashicons-yes-alt" style="color:#46b450;"></span><?php esc_html_e( 'Included in Lite', 'buddypress-polls' ); ?></h3>
							<ul>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Create & Manage Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Frontend Poll Dashboard', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Guest Voting', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Poll Scheduling', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Multi-Select Options', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'AJAX Live Results', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'BuddyPress Activity Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'BuddyPress Group Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-yes" style="color:#46b450;"></span><?php esc_html_e( 'Shortcodes & REST API', 'buddypress-polls' ); ?></li>
							</ul>
						</div>
					</div>

					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
							<h3><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Only in Pro', 'buddypress-polls' ); ?></h3>
							<ul>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Image Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Video Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Audio Polls', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Surveys', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'CSV Export', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'WP-CLI Commands', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Sample Data Generator', 'buddypress-polls' ); ?></li>
								<li><span class="dashicons dashicons-star-filled" style="color:#ffb900;"></span><?php esc_html_e( 'Priority Support', 'buddypress-polls' ); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="bpolls-cta">
					<a href="<?php echo esc_url( 'https://wbcomdesigns.com/downloads/buddypress-polls/' ); ?>" class="button button-primary button-welcome-support" target="_blank" rel="noopener"><?php esc_html_e( 'Get WB Polls Pro', 'buddypress-polls' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
