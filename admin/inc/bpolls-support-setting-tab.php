<?php
/**
 * Faqs support template file.
 *
 * @package    Buddypress_Polls
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
						<?php esc_html_e( 'Does this plugin require BuddyPress?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'No. WB Polls works with or without BuddyPress. Standalone Polls use shortcodes and REST API on any WordPress site. BuddyPress integration adds polls to activity streams and groups.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What to expect when installing and activating WB Polls?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'WB Polls lets you create standalone polls using shortcodes and REST API, or integrate with BuddyPress activity streams and groups.', 'buddypress-polls' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'If BuddyPress is active, a poll icon is added to the post box in activity stream, user profiles and groups. You can also create polls via the Poll Dashboard page.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What is the use of Multi select polls setting provided under general settings section?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'When creating a poll users can set either a single select poll – users can pick just one answer or multiple select poll – users can pick more than one answer.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What is the use of Hide results setting provided under general settings section?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'With hide results setting enabled users can\'t see the poll results before voting. They can see the results once they vote on the poll.', 'buddypress-polls' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'With hide results setting disabled users can see the poll results before voting.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'What is the use of Poll closing date & time setting provided under general settings section?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'With Poll closing date & time setting enabled users can set poll closing date and time.', 'buddypress-polls' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'With Poll closing date & time setting disabled polls will always remain open for voting.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="wbcom-faq-accordion">
						<?php esc_html_e( 'How to show poll activity graph in sidebar?', 'buddypress-polls' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Poll activity graph can be listed in sidebar with the help of widget (BuddyPress) Poll Activity Graph widget provided by the plugin.', 'buddypress-polls' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
