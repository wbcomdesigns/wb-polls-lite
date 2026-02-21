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
	<div class="wbcom-welcome-main-wrapper">
		<div class="wbcom-welcome-head" style="text-align:center;">
			<h2 style="font-size:1.5em;margin-bottom:8px;"><?php esc_html_e( 'Upgrade to WB Polls Pro', 'buddypress-polls' ); ?></h2>
			<p class="wbcom-welcome-description"><?php esc_html_e( 'Unlock image, video & audio polls, surveys, CSV export, and more.', 'buddypress-polls' ); ?></p>
		</div>

		<table class="widefat striped" style="max-width:700px;margin:20px auto;">
			<thead>
				<tr>
					<th style="width:55%;"><?php esc_html_e( 'Feature', 'buddypress-polls' ); ?></th>
					<th style="text-align:center;"><?php esc_html_e( 'Lite', 'buddypress-polls' ); ?></th>
					<th style="text-align:center;background:#f0f6fc;"><?php esc_html_e( 'Pro', 'buddypress-polls' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $features as $feature ) : ?>
				<tr>
					<td><?php echo esc_html( $feature['name'] ); ?></td>
					<td style="text-align:center;">
						<?php if ( $feature['lite'] ) : ?>
							<span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
						<?php else : ?>
							<span class="dashicons dashicons-minus" style="color:#ccc;"></span>
						<?php endif; ?>
					</td>
					<td style="text-align:center;background:#f0f6fc;">
						<span class="dashicons dashicons-yes-alt" style="color:#00a32a;"></span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div style="text-align:center;margin:30px 0;">
			<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener">
				<?php esc_html_e( 'Get WB Polls Pro', 'buddypress-polls' ); ?>
			</a>
		</div>
	</div>
</div>
