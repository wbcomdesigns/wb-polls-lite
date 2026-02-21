<?php
/**
 * This file is used for rendering and saving plugin general settings.
 *
 * @package    Buddypress_Polls
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
	$bpolls_settings = get_site_option( 'wbpolls_settings' );
} else {
	$bpolls_settings = get_site_option( 'wbpolls_settings' );
}
global $wp_roles;
?>
<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3 style="margin: 0 0 5px"><?php esc_html_e( 'Poll Setting', 'buddypress-polls' ); ?></h3>
		<p class="description"><?php esc_html_e( 'This feature lets you create polls as a post type that works independently of BuddyPress Activity Polls.', 'buddypress-polls' ); ?></p>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
		<form method="post" action="admin.php?action=update_network_options">
			<div class="wbcom-wrapper-admin">
				<div class="wbcom-admin-option-wrap-bp-poll">
					<input name='wbpolls_settings[hidden]' type='hidden' value="" />
					<?php
					settings_fields( 'buddypress_wbpolls' );
					do_settings_sections( 'buddypress_wbpolls' );
					wp_nonce_field( 'bp_polls_settings_nonce_action', 'bp_polls_settings_nonce' );
					?>
					<div class="form-table polls-general-options">

						<?php $pages = get_pages(); ?>

						<!-- Dashboard Page -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Dashboard Page', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Select a page for poll dashboard.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 10px;">
								<select name="wbpolls_settings[poll_dashboard_page]" style="min-width: 200px;">									
									<?php
									foreach ( $pages as $dpage ) {
										$selected = ( isset( $bpolls_settings['poll_dashboard_page'] ) && $bpolls_settings['poll_dashboard_page'] == $dpage->ID ) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr( $dpage->ID ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $dpage->post_title ); ?></option>
									<?php } ?>
								</select>
								<?php if ( isset( $bpolls_settings['poll_dashboard_page'] ) && get_post( $bpolls_settings['poll_dashboard_page'] ) ) : ?>
									<a href="<?php echo esc_url( get_permalink( $bpolls_settings['poll_dashboard_page'] ) ); ?>" class="button-secondary" target="_blank">
										<?php esc_html_e( 'View', 'buddypress-polls' ); ?> <span class="dashicons dashicons-external"></span>
									</a>
								<?php endif; ?>
							</div>
						</div>

						<!-- Permissions: Who Can Create -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Who Can Create Polls?', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Select roles allowed to create polls.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
									<?php
									$roles = $wp_roles->get_names();
									foreach ( $roles as $role => $rname ) {
										$checked = ( ! empty( $bpolls_settings['wppolls_create_poll'] ) && in_array( $role, $bpolls_settings['wppolls_create_poll'], true ) ) ? 'checked' : '';
										?>
										<li style="display: flex; align-items: center; gap: 10px;">
											<label class="wb-switch">
												<input type="checkbox" name="wbpolls_settings[wppolls_create_poll][]" value="<?php echo esc_attr( $role ); ?>" <?php echo esc_attr( $checked ); ?> />
												<div class="wb-slider wb-round"></div>
											</label>
											<span><?php echo esc_html( $rname ); ?></span>
										</li>
									<?php } ?>
								</ul>
							</div>
						</div>

						<!-- Permissions: Who Can Vote -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Who Can Vote?', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Select roles allowed to vote on polls.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
									<?php
									$vote_roles          = $wp_roles->get_names();
									$vote_roles['guest'] = esc_html__( 'Guest', 'buddypress-polls' );
									foreach ( $vote_roles as $role => $rname ) {
										$checked = ( ! empty( $bpolls_settings['wppolls_who_can_vote'] ) && in_array( $role, $bpolls_settings['wppolls_who_can_vote'], true ) ) ? 'checked' : '';
										?>
										<li style="display: flex; align-items: center; gap: 10px;">
											<label class="wb-switch">
												<input type="checkbox" name="wbpolls_settings[wppolls_who_can_vote][]" value="<?php echo esc_attr( $role ); ?>" <?php echo esc_attr( $checked ); ?> />
												<div class="wb-slider wb-round"></div>
											</label>
											<span><?php echo esc_html( $rname ); ?></span>
										</li>
									<?php } ?>
								</ul>
							</div>
						</div>

						<!-- Poll Behavior -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Poll Behavior', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Configure how polls work.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[wbpolls_user_add_extra_op]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['wbpolls_user_add_extra_op'] ) ) ? checked( $bpolls_settings['wbpolls_user_add_extra_op'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Users can add options', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[wppolls_show_result]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['wppolls_show_result'] ) ) ? checked( $bpolls_settings['wppolls_show_result'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Hide results until voted', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[wppolls_show_comment]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['wppolls_show_comment'] ) ) ? checked( $bpolls_settings['wppolls_show_comment'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Enable comments', 'buddypress-polls' ); ?></span>
									</li>
								</ul>
							</div>
						</div>

						<!-- Submit Status & Color -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Display Settings', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Poll submission status and color scheme.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 30px; flex-wrap: wrap;">
								<div>
									<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'New Poll Status', 'buddypress-polls' ); ?></span>
									<select name="wbpolls_settings[wbpolls_submit_status]">
										<option value="pending" <?php selected( isset( $bpolls_settings['wbpolls_submit_status'] ) ? $bpolls_settings['wbpolls_submit_status'] : '', 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'buddypress-polls' ); ?></option>
										<option value="publish" <?php selected( isset( $bpolls_settings['wbpolls_submit_status'] ) ? $bpolls_settings['wbpolls_submit_status'] : '', 'publish' ); ?>><?php esc_html_e( 'Publish Immediately', 'buddypress-polls' ); ?></option>
									</select>
								</div>
								<div>
									<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Color Scheme', 'buddypress-polls' ); ?></span>
									<input id="polls_background_color" name='wbpolls_settings[wbpolls_background_color]' type='text' value='<?php echo isset( $bpolls_settings['wbpolls_background_color'] ) ? esc_attr( $bpolls_settings['wbpolls_background_color'] ) : '#4caf50'; ?>' />
								</div>
							</div>
						</div>

						<!-- Poll Types -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Poll Types', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Enable or disable different poll option types.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[enable_image_poll]' type='checkbox' value='yes' <?php checked( isset( $bpolls_settings['enable_image_poll'] ) ? $bpolls_settings['enable_image_poll'] : 'yes', 'yes' ); ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Image Poll', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[enable_video_poll]' type='checkbox' value='yes' <?php checked( isset( $bpolls_settings['enable_video_poll'] ) ? $bpolls_settings['enable_video_poll'] : 'yes', 'yes' ); ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Video Poll', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[enable_audio_poll]' type='checkbox' value='yes' <?php checked( isset( $bpolls_settings['enable_audio_poll'] ) ? $bpolls_settings['enable_audio_poll'] : 'yes', 'yes' ); ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Audio Poll', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[enable_html_poll]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['enable_html_poll'] ) ) ? checked( $bpolls_settings['enable_html_poll'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'HTML Poll', 'buddypress-polls' ); ?></span>
									</li>
								</ul>
							</div>
						</div>

						<!-- Editor Settings -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Editor Security', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Restrict HTML input in poll descriptions.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[disable_html_editor]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['disable_html_editor'] ) ) ? checked( $bpolls_settings['disable_html_editor'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Hide HTML tab', 'buddypress-polls' ); ?></span>
									</li>
									<li style="display: flex; align-items: center; gap: 10px;">
										<label class="wb-switch">
											<input name='wbpolls_settings[use_simple_textarea]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['use_simple_textarea'] ) ) ? checked( $bpolls_settings['use_simple_textarea'], 'yes' ) : ''; ?> />
											<div class="wb-slider wb-round"></div>
										</label>
										<span><?php esc_html_e( 'Plain textarea only', 'buddypress-polls' ); ?></span>
									</li>
								</ul>
							</div>
						</div>

						<!-- Character Limits -->
						<div class="wbcom-settings-section-wrap">
							<div class="wbcom-settings-section-options-heading">
								<label><?php esc_html_e( 'Character Limits', 'buddypress-polls' ); ?></label>
								<p class="description"><?php esc_html_e( 'Set maximum character limits for poll fields. Leave empty or 0 for unlimited.', 'buddypress-polls' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 30px; flex-wrap: wrap;">
								<div>
									<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Poll Title', 'buddypress-polls' ); ?></span>
									<input name='wbpolls_settings[poll_title_limit]' type='number' min="0" max="500" style="width: 80px;" value='<?php echo isset( $bpolls_settings['poll_title_limit'] ) ? esc_attr( $bpolls_settings['poll_title_limit'] ) : ''; ?>' placeholder="200" />
									<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'chars', 'buddypress-polls' ); ?></span>
								</div>
								<div>
									<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Poll Description', 'buddypress-polls' ); ?></span>
									<input name='wbpolls_settings[poll_description_limit]' type='number' min="0" max="10000" style="width: 80px;" value='<?php echo isset( $bpolls_settings['poll_description_limit'] ) ? esc_attr( $bpolls_settings['poll_description_limit'] ) : ''; ?>' placeholder="2000" />
									<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'chars', 'buddypress-polls' ); ?></span>
								</div>
								<div>
									<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Answer Option', 'buddypress-polls' ); ?></span>
									<input name='wbpolls_settings[poll_answer_limit]' type='number' min="0" max="500" style="width: 80px;" value='<?php echo isset( $bpolls_settings['poll_answer_limit'] ) ? esc_attr( $bpolls_settings['poll_answer_limit'] ) : ''; ?>' placeholder="200" />
									<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'chars', 'buddypress-polls' ); ?></span>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
</div>
