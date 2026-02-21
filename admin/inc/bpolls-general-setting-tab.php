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
if ( class_exists( 'Buddypress' ) ) {
	if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		$bpolls_settings = get_site_option( 'bpolls_settings' );
	} else {
		$bpolls_settings = get_site_option( 'bpolls_settings' );
	}
	if ( ! isset( $bpolls_settings['limit_poll_activity'] ) ) {
		$bpolls_settings['limit_poll_activity'] = 'no';
	}

	global $wp_roles;
	?>
	<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
	<div class="wbcom-admin-title-section">
		<h3 style="margin: 0 0 5px"><?php esc_html_e( 'Activity Polls Setting', 'buddypress-polls' ); ?></h3>
		<p class="description"><?php esc_html_e( "Activity polls allow members to create polls as activities visible on the newsfeed.", 'buddypress-polls' ); ?></p>
	</div>
	<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
	<form method="post" action="admin.php?action=update_network_options">
		<div class="wbcom-admin-option-wrap-bp-poll">
		<input name='bpolls_settings[hidden]' type='hidden' value=""/>
		<?php
		settings_fields( 'buddypress_polls_general' );
		do_settings_sections( 'buddypress_polls_general' );
		wp_nonce_field( 'bp_polls_general_settings_nonce_action', 'bp_polls_general_settings_nonce' );
		?>
		<div class="form-table polls-general-options">

			<!-- Voting Options -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Voting Options', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Configure voting behavior for activity polls.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[multiselect]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['multiselect'] ) ) ? checked( $bpolls_settings['multiselect'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Multi-select voting', 'buddypress-polls' ); ?></span>
						</li>
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[user_additional_option]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['user_additional_option'] ) ) ? checked( $bpolls_settings['user_additional_option'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Users can add options', 'buddypress-polls' ); ?></span>
						</li>
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[poll_revoting]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['poll_revoting'] ) ) ? checked( $bpolls_settings['poll_revoting'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Allow revoting', 'buddypress-polls' ); ?></span>
						</li>
					</ul>
				</div>
			</div>

			<!-- Results Display -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Results Display', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Configure how poll results are shown.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[hide_results]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['hide_results'] ) ) ? checked( $bpolls_settings['hide_results'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Hide until voted', 'buddypress-polls' ); ?></span>
						</li>
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[poll_options_result]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['poll_options_result'] ) ) ? checked( $bpolls_settings['poll_options_result'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Show percentage', 'buddypress-polls' ); ?></span>
						</li>
					</ul>
				</div>
			</div>

			<!-- Voters Display -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Voters Display', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Show who voted on polls.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap;">
					<div style="display: flex; align-items: center; gap: 10px;">
						<label class="wb-switch">
							<input name='bpolls_settings[poll_list_voters]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['poll_list_voters'] ) ) ? checked( $bpolls_settings['poll_list_voters'], 'yes' ) : ''; ?>/>
							<div class="wb-slider wb-round"></div>
						</label>
						<span><?php esc_html_e( 'Display voters list', 'buddypress-polls' ); ?></span>
					</div>
					<div id="poll_limit_voters_options" style="display: flex; align-items: center; gap: 10px; <?php echo ! isset( $bpolls_settings['poll_list_voters'] ) ? 'opacity: 0.5;' : ''; ?>">
						<span><?php esc_html_e( 'Show:', 'buddypress-polls' ); ?></span>
						<input name='bpolls_settings[poll_limit_voters]' type='number' min="1" max="20" style="width: 60px;" value='<?php echo ( isset( $bpolls_settings['poll_limit_voters'] ) ) ? esc_attr( $bpolls_settings['poll_limit_voters'] ) : '3'; ?>'/>
						<span><?php esc_html_e( 'voters', 'buddypress-polls' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Poll Options -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Poll Options', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Configure poll creation settings.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap;">
					<div style="display: flex; align-items: center; gap: 10px;">
						<span><?php esc_html_e( 'Max options:', 'buddypress-polls' ); ?></span>
						<input name='bpolls_settings[options_limit]' type='number' min="2" max="20" style="width: 60px;" value='<?php echo ( isset( $bpolls_settings['options_limit'] ) ) ? esc_attr( $bpolls_settings['options_limit'] ) : '5'; ?>'/>
					</div>
					<div style="display: flex; align-items: center; gap: 10px;">
						<label class="wb-switch">
							<input name='bpolls_settings[close_date]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['close_date'] ) ) ? checked( $bpolls_settings['close_date'], 'yes' ) : ''; ?>/>
							<div class="wb-slider wb-round"></div>
						</label>
						<span><?php esc_html_e( 'Enable closing date', 'buddypress-polls' ); ?></span>
					</div>
					<div style="display: flex; align-items: center; gap: 10px;">
						<label class="wb-switch">
							<input name='bpolls_settings[enable_thank_you_message]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['enable_thank_you_message'] ) ) ? checked( $bpolls_settings['enable_thank_you_message'], 'yes' ) : ''; ?>/>
							<div class="wb-slider wb-round"></div>
						</label>
						<span><?php esc_html_e( 'After-poll message', 'buddypress-polls' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Media Attachments -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Media Attachments', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Allow media attachments in poll options.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 0; padding: 0; list-style: none;">
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[enable_image]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['enable_image'] ) ) ? checked( $bpolls_settings['enable_image'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Images', 'buddypress-polls' ); ?></span>
						</li>
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[enable_video]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['enable_video'] ) ) ? checked( $bpolls_settings['enable_video'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Videos', 'buddypress-polls' ); ?></span>
						</li>
						<li style="display: flex; align-items: center; gap: 10px;">
							<label class="wb-switch">
								<input name='bpolls_settings[enable_audio]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['enable_audio'] ) ) ? checked( $bpolls_settings['enable_audio'], 'yes' ) : ''; ?>/>
								<div class="wb-slider wb-round"></div>
							</label>
							<span><?php esc_html_e( 'Audio', 'buddypress-polls' ); ?></span>
						</li>
					</ul>
				</div>
			</div>

			<!-- Media Settings (conditional) -->
			<div class="wbcom-settings-section-wrap" id="bpolls_media_options" <?php echo ( ! isset( $bpolls_settings['enable_image'] ) && ! isset( $bpolls_settings['enable_video'] ) && ! isset( $bpolls_settings['enable_audio'] ) ) ? 'style="display:none"' : ''; ?>>
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Media Settings', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Configure media upload behavior.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap;">
					<div style="display: flex; align-items: center; gap: 10px;">
						<label class="wb-switch">
							<input name='bpolls_settings[url_input_only]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['url_input_only'] ) ) ? checked( $bpolls_settings['url_input_only'], 'yes' ) : ''; ?>/>
							<div class="wb-slider wb-round"></div>
						</label>
						<span><?php esc_html_e( 'URL input only', 'buddypress-polls' ); ?></span>
					</div>
					<div style="display: flex; align-items: center; gap: 10px;">
						<label class="wb-switch">
							<input name='bpolls_settings[restrict_media_library]' type='checkbox' value='yes' <?php ( isset( $bpolls_settings['restrict_media_library'] ) ) ? checked( $bpolls_settings['restrict_media_library'], 'yes' ) : ''; ?>/>
							<div class="wb-slider wb-round"></div>
						</label>
						<span><?php esc_html_e( 'Restrict media library', 'buddypress-polls' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Display Settings -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Display Settings', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Visual settings for polls.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap;">
					<div>
						<span style="margin-right: 5px;"><?php esc_html_e( 'Color:', 'buddypress-polls' ); ?></span>
						<input id="polls_background_color" name='bpolls_settings[polls_background_color]' type='text' value='<?php echo isset( $bpolls_settings['polls_background_color'] ) ? esc_attr( $bpolls_settings['polls_background_color'] ) : '#4caf50'; ?>' />
					</div>
				</div>
			</div>

			<!-- Restrictions -->
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label><?php esc_html_e( 'Poll Creation Restrictions', 'buddypress-polls' ); ?></label>
					<p class="description"><?php esc_html_e( 'Limit who can create activity polls.', 'buddypress-polls' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<div class="wbcom-settings-section-options-flex" style="gap: 15px 30px; flex-wrap: wrap; margin-bottom: 15px;">
						<label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
							<input name='bpolls_settings[limit_poll_activity]' type='radio' value='no' <?php ( isset( $bpolls_settings['limit_poll_activity'] ) ) ? checked( $bpolls_settings['limit_poll_activity'], 'no' ) : ''; ?>/> <?php esc_html_e( 'No Limit', 'buddypress-polls' ); ?>
						</label>
						<label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
							<input name='bpolls_settings[limit_poll_activity]' type='radio' value='user_role' <?php ( isset( $bpolls_settings['limit_poll_activity'] ) ) ? checked( $bpolls_settings['limit_poll_activity'], 'user_role' ) : ''; ?>/> <?php esc_html_e( 'By User Role', 'buddypress-polls' ); ?>
						</label>
						<label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
							<input name='bpolls_settings[limit_poll_activity]' type='radio' value='member_type' <?php ( isset( $bpolls_settings['limit_poll_activity'] ) ) ? checked( $bpolls_settings['limit_poll_activity'], 'member_type' ) : ''; ?>/> <?php esc_html_e( 'By Member Type', 'buddypress-polls' ); ?>
						</label>
					</div>

					<!-- User Role Select -->
					<div id="bpolls_user_role" <?php if ( isset( $bpolls_settings['limit_poll_activity'] ) && 'user_role' !== $bpolls_settings['limit_poll_activity'] ) : ?>style="display:none"<?php endif; ?>>
						<p class="description" style="margin: 0 0 10px; color: #666;"><?php esc_html_e( 'Note: Administrators always have poll creation capability regardless of this setting.', 'buddypress-polls' ); ?></p>
						<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 10px 0 0; padding: 0; list-style: none;">
							<?php
							$roles = $wp_roles->get_names();
							foreach ( $roles as $role => $rname ) {
								// Skip administrator - they always have poll creation capability.
								if ( 'administrator' === $role ) {
									continue;
								}
								$checked = ( ! empty( $bpolls_settings['poll_user_role'] ) && in_array( $role, $bpolls_settings['poll_user_role'], true ) ) ? 'checked' : '';
								?>
								<li style="display: flex; align-items: center; gap: 10px;">
									<label class="wb-switch">
										<input type="checkbox" name="bpolls_settings[poll_user_role][]" value="<?php echo esc_attr( $role ); ?>" <?php echo esc_attr( $checked ); ?> />
										<div class="wb-slider wb-round"></div>
									</label>
									<span><?php echo esc_html( $rname ); ?></span>
								</li>
							<?php } ?>
						</ul>
					</div>

					<!-- Member Type Select -->
					<?php
					$types = bp_get_member_types( array(), 'objects' );
					if ( $types ) {
						?>
					<div id="bpolls_member_type" <?php if ( isset( $bpolls_settings['limit_poll_activity'] ) && 'member_type' !== $bpolls_settings['limit_poll_activity'] ) : ?>style="display:none"<?php endif; ?>>
						<ul class="wbcom-settings-section-options-flex" style="gap: 20px 40px; flex-wrap: wrap; margin: 10px 0 0; padding: 0; list-style: none;">
							<?php
							foreach ( $types as $typ ) {
								$checked = ( ! empty( $bpolls_settings['poll_member_type'] ) && in_array( $typ->name, $bpolls_settings['poll_member_type'], true ) ) ? 'checked' : '';
								?>
								<li style="display: flex; align-items: center; gap: 10px;">
									<label class="wb-switch">
										<input type="checkbox" name="bpolls_settings[poll_member_type][]" value="<?php echo esc_attr( $typ->name ); ?>" <?php echo esc_attr( $checked ); ?> />
										<div class="wb-slider wb-round"></div>
									</label>
									<span><?php echo esc_html( $typ->labels['singular_name'] ); ?></span>
								</li>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>
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
						<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Poll Option Text', 'buddypress-polls' ); ?></span>
						<input name='bpolls_settings[activity_option_limit]' type='number' min="0" max="500" style="width: 80px;" value='<?php echo isset( $bpolls_settings['activity_option_limit'] ) ? esc_attr( $bpolls_settings['activity_option_limit'] ) : ''; ?>' placeholder="100" />
						<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'chars', 'buddypress-polls' ); ?></span>
					</div>
					<div>
						<span style="display: block; margin-bottom: 5px; font-weight: 500;"><?php esc_html_e( 'Thank You Message', 'buddypress-polls' ); ?></span>
						<input name='bpolls_settings[thank_you_message_limit]' type='number' min="0" max="500" style="width: 80px;" value='<?php echo isset( $bpolls_settings['thank_you_message_limit'] ) ? esc_attr( $bpolls_settings['thank_you_message_limit'] ) : ''; ?>' placeholder="200" />
						<span class="description" style="margin-left: 5px;"><?php esc_html_e( 'chars', 'buddypress-polls' ); ?></span>
					</div>
				</div>
			</div>

		</div>
	</div>
		<?php submit_button(); ?>
	</form>
	</div>
	</div>
	</div>
<?php
}
?>
