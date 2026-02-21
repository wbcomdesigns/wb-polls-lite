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

$pro_url = 'https://wbcomdesigns.com/downloads/buddypress-polls/';

$features = array(
	array(
		'name' => __( 'Create & Manage Polls', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Frontend Poll Dashboard', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Guest Voting', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Poll Scheduling', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Multi-Select Options', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'AJAX Live Results', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'BuddyPress Activity Polls', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'BuddyPress Group Polls', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Shortcodes & REST API', 'buddypress-polls' ),
		'lite' => true,
		'pro'  => true,
	),
	array(
		'name' => __( 'Image Polls', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'Video Polls', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'Audio Polls', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'Surveys', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'CSV Export', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'WP-CLI Commands', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'Sample Data Generator', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
	array(
		'name' => __( 'Priority Support', 'buddypress-polls' ),
		'lite' => false,
		'pro'  => true,
	),
);
?>
<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3 style="margin: 0 0 5px"><?php esc_html_e( 'Upgrade to WB Polls Pro', 'buddypress-polls' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Unlock image, video & audio polls, surveys, CSV export, and more.', 'buddypress-polls' ); ?></p>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<div class="wbcom-wrapper-admin">

			<!-- Comparison Header -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading" style="flex: 1;">
					<label><?php esc_html_e( 'Feature', 'buddypress-polls' ); ?></label>
				</div>
				<div class="wbcom-settings-section-options" style="display: flex; gap: 0; text-align: center;">
					<span style="display: inline-block; width: 80px; font-weight: 600;"><?php esc_html_e( 'Lite', 'buddypress-polls' ); ?></span>
					<span style="display: inline-block; width: 80px; font-weight: 600; color: #2271b1;"><?php esc_html_e( 'Pro', 'buddypress-polls' ); ?></span>
				</div>
			</div>

			<!-- Feature Rows -->
			<?php foreach ( $features as $feature ) : ?>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading" style="flex: 1;">
					<label><?php echo esc_html( $feature['name'] ); ?></label>
				</div>
				<div class="wbcom-settings-section-options" style="display: flex; gap: 0; text-align: center;">
					<span style="display: inline-block; width: 80px;">
						<?php if ( $feature['lite'] ) : ?>
							<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
						<?php else : ?>
							<span class="dashicons dashicons-minus" style="color: #ccc;"></span>
						<?php endif; ?>
					</span>
					<span style="display: inline-block; width: 80px;">
						<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
					</span>
				</div>
			</div>
			<?php endforeach; ?>

			<!-- CTA -->
			<div class="wbcom-settings-section-wrap" style="justify-content: center; padding: 25px 20px;">
				<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener">
					<?php esc_html_e( 'Get WB Polls Pro', 'buddypress-polls' ); ?>
				</a>
			</div>

		</div>
	</div>
</div>
