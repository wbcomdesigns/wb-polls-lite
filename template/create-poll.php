<?php
/**
 * The create poll page.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      4.3.0
 * @deprecated 4.5.0 Use the dashboard slide-in panel instead.
 *
 * @package    Buddypress_Polls
 * @subpackage Buddypress_Polls/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $current_user;
$temp_post           = $post;
$option_value        = get_site_option( 'wbpolls_settings' );
$wppolls_create_poll = ( isset( $option_value['wppolls_create_poll'] ) ) ? $option_value['wppolls_create_poll'] : '';

/**
 * Redirect to dashboard with create/edit panel.
 *
 * As of 4.5.0, the dashboard has a slide-in panel for creating and editing polls.
 * This provides a better UX. Redirect legacy URLs to the new dashboard.
 *
 * @since 4.5.0
 */
$dashboard_page_id = isset( $option_value['poll_dashboard_page'] ) ? absint( $option_value['poll_dashboard_page'] ) : 0;

if ( $dashboard_page_id > 0 ) {
	$dashboard_url = get_permalink( $dashboard_page_id );

	if ( $dashboard_url ) {
		// Check if editing an existing poll.
		if ( ! empty( $_GET['poll_id'] ) && isset( $_GET['_wpnonce'] ) ) {
			$poll_id = absint( $_GET['poll_id'] );
			// Verify nonce before redirecting.
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_poll_' . $poll_id ) ) {
				$dashboard_url = add_query_arg(
					array(
						'action'  => 'edit',
						'poll_id' => $poll_id,
					),
					$dashboard_url
				);
			}
		} else {
			// Creating a new poll.
			$dashboard_url = add_query_arg( 'create', '1', $dashboard_url );
		}

		wp_safe_redirect( $dashboard_url );
		exit;
	}
}

// Fallback: If no dashboard page is set, continue with the legacy template.

// Poll type settings - default to enabled for backwards compatibility.
$enable_image_poll = ! isset( $option_value['enable_image_poll'] ) || 'yes' === $option_value['enable_image_poll'];
$enable_video_poll = ! isset( $option_value['enable_video_poll'] ) || 'yes' === $option_value['enable_video_poll'];
$enable_audio_poll = ! isset( $option_value['enable_audio_poll'] ) || 'yes' === $option_value['enable_audio_poll'];
$enable_html_poll  = isset( $option_value['enable_html_poll'] ) && 'yes' === $option_value['enable_html_poll'];

// Editor settings.
$disable_html_editor = isset( $option_value['disable_html_editor'] ) && 'yes' === $option_value['disable_html_editor'];
$use_simple_textarea = isset( $option_value['use_simple_textarea'] ) && 'yes' === $option_value['use_simple_textarea'];

// Character limit settings.
$poll_title_limit       = isset( $option_value['poll_title_limit'] ) && intval( $option_value['poll_title_limit'] ) > 0 ? intval( $option_value['poll_title_limit'] ) : 0;
$poll_description_limit = isset( $option_value['poll_description_limit'] ) && intval( $option_value['poll_description_limit'] ) > 0 ? intval( $option_value['poll_description_limit'] ) : 0;
$poll_answer_limit      = isset( $option_value['poll_answer_limit'] ) && intval( $option_value['poll_answer_limit'] ) > 0 ? intval( $option_value['poll_answer_limit'] ) : 0;
if ( ! empty( $wppolls_create_poll ) ) {
	$roles  = $current_user->roles;
	$result = array_intersect( $wppolls_create_poll, $roles );

	if ( empty( $result ) ) {
		echo '<div class="main-poll-create">';
		echo esc_html__( 'You are not allowed to create the poll.', 'buddypress-polls' );
		echo '</div>';

		return;
	}
}
if ( ! empty( $_GET['poll_id'] ) ) {
	$edit_poll_id = absint( $_GET['poll_id'] );
	$edit_nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

	if ( ! wp_verify_nonce( $edit_nonce, 'edit_poll_' . $edit_poll_id ) ) {

		echo '<div class="main-poll-create">';
		echo esc_html__( 'You are not allowed to edit the poll.', 'buddypress-polls' );
		echo '</div>';

		return;
	}
	$post_id = isset( $_GET['poll_id'] ) ? absint( $_GET['poll_id'] ) : 0;
	$post    = get_post( $post_id );

	$poll_type                = get_post_meta( $post_id, 'poll_type', true );
	$start_time               = get_post_meta( $post_id, '_wbpoll_start_date', true );
	$end_date                 = get_post_meta( $post_id, '_wbpoll_end_date', true );
	$never_expire             = get_post_meta( $post_id, '_wbpoll_never_expire', true );
	$show_result_after_expire = get_post_meta( $post_id, '_wbpoll_show_result_before_expire', true );
	$multivote                = get_post_meta( $post_id, '_wbpoll_multivote', true );

	$answers          = get_post_meta( $post_id, '_wbpoll_answer', true );
	$image_answer_url = get_post_meta( $post_id, '_wbpoll_full_size_image_answer', true );
	$image_answer_url = isset( $image_answer_url ) ? $image_answer_url : array();

	$video_answer_url = get_post_meta( $post_id, '_wbpoll_video_answer_url', true );
	$video_answer_url = isset( $video_answer_url ) ? $video_answer_url : array();

	$audio_answer_url = get_post_meta( $post_id, '_wbpoll_audio_answer_url', true );
	$audio_answer_url = isset( $audio_answer_url ) ? $audio_answer_url : array();

	$html_content = get_post_meta( $post_id, '_wbpoll_html_answer', true );
	$html_content = isset( $html_content ) ? $html_content : array();

	$video_import_info = get_post_meta( $post_id, '_wbpoll_video_import_info', true );
	$video_import_info = isset( $video_import_info ) ? $video_import_info : array();

	$audio_import_info = get_post_meta( $post_id, '_wbpoll_audio_import_info', true );
	$audio_import_info = isset( $audio_import_info ) ? $audio_import_info : array();

	$options = array();

	if ( $poll_type == 'default' ) {
		foreach ( $answers as $key => $ans ) {
			$options[ $key ] = $ans;
		}
	} elseif ( $poll_type == 'image' ) {
		foreach ( $answers as $key => $ans ) {
			$options[ $key ] = array(
				'ans'   => $ans,
				'image' => $image_answer_url[ $key ],
			);
		}
	} elseif ( $poll_type == 'video' ) {
		foreach ( $answers as $key => $ans ) {
			$options[ $key ] = array(
				'ans'        => $ans,
				'video'      => $video_answer_url[ $key ],
				'suggestion' => ( isset( $video_import_info[ $key ] ) ) ? $video_import_info[ $key ] : 'no',
			);
		}
	} elseif ( $poll_type == 'audio' ) {
		foreach ( $answers as $key => $ans ) {
			$options[ $key ] = array(
				'ans'        => $ans,
				'audio'      => $audio_answer_url[ $key ],
				'suggestion' => ( isset( $audio_import_info[ $key ] ) ) ? $audio_import_info[ $key ] : 'no',
			);
		}
	} elseif ( $poll_type == 'html' ) {
		foreach ( $answers as $key => $ans ) {
			$options[ $key ] = array(
				'ans'  => $ans,
				'html' => $html_content[ $key ],
			);
		}
	}

	$add_additional_fields = get_post_meta( $post_id, '_wbpoll_add_additional_fields', true );
} else {
	// Defaults for new polls.
	$options                  = array();
	$never_expire             = '1'; // Default to "Yes" for simpler form.
	$show_result_after_expire = '0';
	$multivote                = '0';
	$add_additional_fields    = '0';
}

if ( isset( $poll_type ) && ! empty( $poll_type ) ) {
	$poll_type = $poll_type;
} else {
	// Default to 'default' (Text) poll type for new polls.
	$poll_type = 'default';
}
?>

<?php
// Get dashboard page URL for back navigation.
$dashboard_page_id  = isset( $option_value['poll_dashboard_page'] ) ? $option_value['poll_dashboard_page'] : '';
$dashboard_page_url = $dashboard_page_id ? get_permalink( $dashboard_page_id ) : '';
?>
<div class="main-poll-create">
	<div class="dashboard-top">
		<?php if ( $dashboard_page_url ) : ?>
			<a href="<?php echo esc_url( $dashboard_page_url ); ?>" class="back-to-dashboard">
				<span class="dashicons dashicons-arrow-left-alt"></span>
				<?php esc_html_e( 'Back to Dashboard', 'buddypress-polls' ); ?>
			</a>
		<?php endif; ?>
		<?php if ( isset( $_GET['poll_id'] ) && ! empty( $_GET['poll_id'] ) ) { ?>
			<div class="main-title">
				<h3><?php esc_html_e( 'Edit Poll', 'buddypress-polls' ); ?></h3>
			</div>
		<?php } else { ?>
			<div class="main-title">
				<h3><?php esc_html_e( 'Add Poll', 'buddypress-polls' ); ?></h3>
			</div>
		<?php } ?>
	</div>
	<div class="poll-create">
		<?php if ( is_user_logged_in() ) { ?>
			<form id="wbpolls-create" class="wbpolls-create">
				<?php wp_nonce_field( 'wbpoll_create_poll', 'wbpoll_nonce' ); ?>
				<input type="hidden" name="author_id" id="author_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
				<input type="hidden" name="poll_id" id="poll_id" value="
				<?php
				if ( isset( $post_id ) && ! empty( $post_id ) ) {
					echo esc_attr( $post_id );
				}
				?>
				">
				<div class="form-group">
					<label for="polltitle"><?php esc_html_e( 'Poll Title', 'buddypress-polls' ); ?></label>
					<input type="text" class="form-control" name="title" id="polltitle" value="<?php echo ! empty( $_GET['poll_id'] ) ? esc_attr( $post->post_title ) : ''; ?>"<?php echo $poll_title_limit > 0 ? ' maxlength="' . esc_attr( $poll_title_limit ) . '" data-char-limit="' . esc_attr( $poll_title_limit ) . '"' : ''; ?>>
					<?php if ( $poll_title_limit > 0 ) : ?>
					<div class="wbpoll-char-counter" data-for="polltitle">
						<span class="char-count">0</span> / <?php echo esc_html( $poll_title_limit ); ?> <?php esc_html_e( 'characters', 'buddypress-polls' ); ?>
					</div>
					<?php endif; ?>
					<span id="error_title" style="color:red;"></span>
				</div>
				<div class="form-group">
					<label for="polltitle"><?php esc_html_e( 'Poll Description', 'buddypress-polls' ); ?></label>
					<?php
					$content = isset( $_GET['poll_id'] ) ? $post->post_content : ''; // Set initial content if needed.

					if ( $use_simple_textarea ) {
						// Simple textarea for maximum security.
						?>
						<textarea class="form-control" name="content" id="poll-content" rows="8" style="width:100%; min-height:200px;"<?php echo $poll_description_limit > 0 ? ' maxlength="' . esc_attr( $poll_description_limit ) . '" data-char-limit="' . esc_attr( $poll_description_limit ) . '"' : ''; ?>><?php echo esc_textarea( $content ); ?></textarea>
						<?php
					} else {
						// Configure wp_editor settings.
						$editor_settings = array(
							'textarea_name' => 'content',
							'editor_height' => 300,
							'media_buttons' => false, // Disable media upload button for security.
						);

						// Disable HTML/Text tab if setting is enabled.
						if ( $disable_html_editor ) {
							$editor_settings['quicktags'] = false;
						}

						// Output the Rich Textarea.
						wp_editor( $content, 'poll-content', $editor_settings );
					}
					?>
					<?php if ( $poll_description_limit > 0 ) : ?>
					<div class="wbpoll-char-counter" data-for="poll-content" data-limit="<?php echo esc_attr( $poll_description_limit ); ?>">
						<span class="char-count">0</span> / <?php echo esc_html( $poll_description_limit ); ?> <?php esc_html_e( 'characters', 'buddypress-polls' ); ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="form-group">
					<label for="polltitle"><?php esc_html_e( 'Poll Type', 'buddypress-polls' ); ?></label>
					<select class="form-control" name="poll_type" id="poll_type">
						<option value=""
						<?php
						if ( $poll_type == '' ) {
							echo 'selected';
						}
						?>
						><?php esc_html_e( 'Select Poll Type', 'buddypress-polls' ); ?></option>
						<option value="default"
						<?php
						if ( $poll_type == 'default' ) {
							echo 'selected';
						}
						?>
						><?php esc_html_e( 'Text', 'buddypress-polls' ); ?></option>
						<?php if ( $enable_image_poll ) : ?>
						<option value="image"
							<?php
							if ( $poll_type == 'image' ) {
								echo 'selected';
							}
							?>
						><?php esc_html_e( 'Image', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
						<?php if ( $enable_video_poll ) : ?>
						<option value="video"
							<?php
							if ( $poll_type == 'video' ) {
								echo 'selected';
							}
							?>
						><?php esc_html_e( 'Video', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
						<?php if ( $enable_audio_poll ) : ?>
						<option value="audio"
							<?php
							if ( $poll_type == 'audio' ) {
								echo 'selected';
							}
							?>
						><?php esc_html_e( 'Audio', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
						<?php if ( $enable_html_poll ) : ?>
						<option value="html"
							<?php
							if ( $poll_type == 'html' ) {
								echo 'selected';
							}
							?>
						><?php esc_html_e( 'HTML', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
					</select>
					<span id="error_type" style="color:red;"></span>
				</div>
				<?php if ( $poll_type == 'default' ) { ?>
					<div class="wbpolls-answer-wrap">
						<div class="row wbpoll-list-item" id="type_text" style="">
							<div class="ans-records text_records-edit">
								<div class="ans-records-wrap">
									<label><?php esc_html_e( 'Text Answer', 'buddypress-polls' ); ?></label>
									<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo ( isset( $options[0] ) ) ? esc_attr( $options[0] ) : ''; ?>">
									<input type="hidden" id="wbpoll_answer_extra_type" value="default" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
								</div>
								<a class="add-field extra-fields-text-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="text_records_dynamic-edit">
								<?php

								foreach ( $options as $key => $optn ) {
									if ( $key != 0 ) {
										?>
										<div class="remove remove2">
											<div class="ans-records-wrap">
												<label><?php esc_html_e( 'Text Answer', 'buddypress-polls' ); ?></label>
												<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo esc_attr( $optn ); ?>">
												<input type="hidden" id="wbpoll_answer_extra_type" value="default" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
											</div>
											<a class="add-field extra-fields-text-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
											<a href="#" class="remove-field btn-remove-text"><?php esc_html_e( 'Remove Fields', 'buddypress-polls' ); ?></a>
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				<?php } elseif ( $poll_type == 'image' ) { ?>
					<div class="wbpolls-answer-wrap">
						<div class="row wbpoll-list-item" id="type_image" style="">
							<div class="ans-records image_records_edit">
								<div class="ans-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail" id="wbpoll-image-input-preview-thumbnail">
											<img width="266" height="266" src="<?php echo esc_attr( $options[0]['image'] ); ?>">
										</div>

									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Image Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="<?php echo esc_attr( $options[0]['ans'] ); ?>">
										<input type="hidden" id="wbpoll_answer_extra_type" value="image" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Image URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_full_size_image_answer[]" data-name="_wbpoll_full_size_image_answer[]" class="wbpoll_image_answer_url" id="wbpoll_image_answer_url" type="url" value="<?php echo esc_attr( $options[0]['image'] ); ?>">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="image"></button>
									</div>
								</div>
								<a class="add-field extra-fields-image-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="image_records_dynamic_edit">
								<?php
								foreach ( $options as $key => $optn ) {
									if ( $key != 0 ) {
										?>
										<div class="remove remove<?php echo count( $options ); ?>">
											<div class="ans-records-wrap">
												<div class="wbpoll-image-input-preview">
													<div class="wbpoll-image-input-preview-thumbnail" id="wbpoll-image-input-preview-thumbnail"><img width="266" height="266" src="<?php echo esc_attr( $optn['image'] ); ?>"></div>
												</div>
												<div class="wbpoll-image-input-details">
													<label><?php esc_html_e( 'Image Answer', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="<?php echo esc_attr( $optn['ans'] ); ?>">
													<input type="hidden" id="wbpoll_answer_extra_type" value="image" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
													<label><?php esc_html_e( 'Image URL', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_full_size_image_answer[]" data-name="_wbpoll_full_size_image_answer[]" class="wbpoll_image_answer_url" id="wbpoll_image_answer_url" type="url" value="<?php echo esc_attr( $optn['image'] ); ?>">
													<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="image"></button>
												</div>
											</div>
											<a class="add-field extra-fields-image-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
											<a href="#" class="remove-field btn-remove-image"><?php esc_html_e( 'Remove Fields', 'buddypress-polls' ); ?></a>
										</div>

										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				<?php } elseif ( $poll_type == 'video' ) { ?>
					<div class="wbpolls-answer-wrap">
						<div class="row wbpoll-list-item" id="type_video" style="">
							<div class="ans-records video_records_edit">
								<div class="ans-records-wrap ans-video-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail">
											<?php if ( $options[0]['suggestion'] == 'yes' ) { ?>
												<iframe width="420" height="345" src="<?php echo $options[0]['video']; ?>"></iframe> <?php //phpcs:ignore  ?>
											<?php } else { ?>
												<video src="<?php echo esc_url( $options[0]['video'] ); ?>" controls="" poster="" preload="none"></video>
											<?php } ?>
										</div>
									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Video Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="<?php echo esc_attr( $options[0]['ans'] ); ?>">
										<input type="hidden" id="wbpoll_answer_extra_type" value="video" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Video URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_video_answer_url[]" data-name="_wbpoll_video_answer_url[]" id="wbpoll_video_answer_url" class="wbpoll_video_answer_url" type="url" value="<?php echo esc_attr( $options[0]['video'] ); ?>">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="video"></button>
										<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;">
											<span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
											<input type="radio" class="yes_video wbpoll_video_import_info" id="yes" name="_wbpoll_video_import_info[0]" data-name="_wbpoll_video_import_info[]" value="yes" 
											<?php
											if ( $options[0]['suggestion'] == 'yes' ) {
												echo 'checked="checked"';
											}
											?>
											>
											<label for="yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
											<input type="radio" id="no" name="_wbpoll_video_import_info[]" data-name="_wbpoll_video_import_info[0]" value="no" class="wbpoll_video_import_info"
											<?php
											if ( $options[0]['suggestion'] == 'no' ) {
												echo 'checked="checked"';
											}
											?>
											>
											<label for="no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label>
										</div>
									</div>
								</div>
								<a class="add-field extra-fields-video-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="video_records_dynamic_edit">
								<?php
								foreach ( $options as $key => $optn ) {
									if ( $key != 0 ) {
										?>
										<div class="remove remove2">
											<div class="ans-records-wrap ans-video-records-wrap">
												<div class="wbpoll-image-input-preview">
													<div class="wbpoll-image-input-preview-thumbnail">
														<?php if ( $optn['suggestion'] == 'yes' ) { ?>
															<iframe width="420" height="345" src="<?php echo esc_url( $optn['video'] ); ?>"></iframe>
														<?php } else { ?>
															<video src="<?php echo esc_url( $optn['video'] ); ?>" controls="" poster="" preload="none"></video>
														<?php } ?>
													</div>
												</div>
												<div class="wbpoll-image-input-details">
													<label><?php esc_html_e( 'Video Answer', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="<?php echo esc_attr( $optn['ans'] ); ?>">
													<input type="hidden" id="wbpoll_answer_extra_type" value="video" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
													<label><?php esc_html_e( 'Video URL', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_video_answer_url[]" data-name="_wbpoll_video_answer_url[]" id="wbpoll_video_answer_url" class="wbpoll_video_answer_url" type="url" value="<?php echo esc_attr( $optn['video'] ); ?>">
													<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="video"></button>
													<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;">
														<span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
														<input type="radio" class="yes_video wbpoll_video_import_info" id="yes" name="_wbpoll_video_import_info[<?php echo esc_attr( $key ); ?>]" data-name="_wbpoll_video_import_info[]" value="yes"
														<?php
														if ( $optn['suggestion'] == 'yes' ) {
															echo 'checked="checked"';
														}
														?>
														>
														<label for="yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
														<input type="radio" id="no" name="_wbpoll_video_import_info[<?php echo esc_attr( $key ); ?>]" data-name="_wbpoll_video_import_info[]" value="no" class="wbpoll_video_import_info"
														<?php
														if ( $optn['suggestion'] == 'no' ) {
															echo 'checked="checked"';
														}
														?>
														>
														<label for="no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label>
													</div>
												</div>
											</div>
											<a class="add-field extra-fields-video-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
											<a href="#" class="remove-field btn-remove-video"><?php esc_html_e( 'Remove Fields', 'buddypress-polls' ); ?></a>
										</div>

										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				<?php } elseif ( $poll_type == 'audio' ) { ?>
					<div class="wbpolls-answer-wrap">
						<div class="row wbpoll-list-item" id="type_audio" style="">
							<div class="ans-records audio_records_edit">
								<div class="ans-records-wrap ans-audio-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail">
											<?php if ( $options[0]['suggestion'] == 'yes' ) { ?>
												<iframe width="420" height="345" src="<?php echo esc_url( $options[0]['audio'] ); ?>"></iframe>
											<?php } else { ?>
												<audio src="<?php echo esc_url( $options[0]['audio'] ); ?>" controls="" preload="none"></audio>
											<?php } ?>
										</div>
									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Audio Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo esc_attr( $options[0]['ans'] ); ?>">
										<input type="hidden" id="wbpoll_answer_extra_type" value="audio" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Audio URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_audio_answer_url[]" data-name="_wbpoll_audio_answer_url[]" id="wbpoll_audio_answer_url" class="wbpoll_audio_answer_url" type="url" value="<?php echo esc_attr( $options[0]['audio'] ); ?>">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="audio"></button>
										<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;"><span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
											<input type="radio" class="yes_audio wbpoll_audio_import_info" id="yes" name="_wbpoll_audio_import_info[0]" data-name="_wbpoll_audio_import_info[]" value="yes"
											<?php
											if ( $options[0]['suggestion'] == 'yes' ) {
												echo 'checked="checked"';
											}
											?>
											>
											<label for="yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
											<input type="radio" id="no" name="_wbpoll_audio_import_info[0]" data-name="_wbpoll_audio_import_info[]" value="no" class="wbpoll_audio_import_info"
											<?php
											if ( $options[0]['suggestion'] == 'no' ) {
												echo 'checked="checked"';
											}
											?>
											>
											<label for="no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label><br>
										</div>
									</div>
								</div>
								<a class="add-field extra-fields-audio-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="audio_records_dynamic_edit">
								<?php
								foreach ( $options as $key => $optn ) {
									if ( $key != 0 ) {
										?>
										<div class="remove remove2">
											<div class="ans-records-wrap ans-audio-records-wrap">
												<div class="wbpoll-image-input-preview">
													<div class="wbpoll-image-input-preview-thumbnail">
														<?php if ( $optn['suggestion'] == 'yes' ) { ?>
															<iframe width="420" height="345" src="<?php echo esc_url( $optn['audio'] ); ?>"></iframe>
														<?php } else { ?>
															<audio src="<?php echo esc_url( $optn['audio'] ); ?>" controls="" preload="none"></audio>
														<?php } ?>
													</div>
												</div>
												<div class="wbpoll-image-input-details">
													<label><?php esc_html_e( 'Audio Answer', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo esc_attr( $optn['ans'] ); ?>">
													<input type="hidden" id="wbpoll_answer_extra_type" value="audio" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
													<label><?php esc_html_e( 'Audio URL', 'buddypress-polls' ); ?></label>
													<input name="_wbpoll_audio_answer_url[]" data-name="_wbpoll_audio_answer_url[]" id="wbpoll_audio_answer_url" class="wbpoll_audio_answer_url" type="url" value="<?php echo esc_attr( $optn['audio'] ); ?>">
													<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="audio"></button>
													<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;"><span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
														<input type="radio" class="yes_audio wbpoll_audio_import_info" id="yes" name="_wbpoll_audio_import_info[<?php echo esc_attr( $key ); ?>]" data-name="_wbpoll_audio_import_info[]" value="yes" 
														<?php
														if ( $optn['suggestion'] == 'yes' ) {
															echo 'checked="checked"';
														}
														?>
														>
														<label for="yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
														<input type="radio" id="no" name="_wbpoll_audio_import_info[<?php echo esc_attr( $key ); ?>]" data-name="_wbpoll_audio_import_info[]" value="no" class="wbpoll_audio_import_info"
														<?php
														if ( $optn['suggestion'] == 'no' ) {
															echo 'checked="checked"';
														}
														?>
														>
														<label for="no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label><br>
													</div>
												</div>
											</div>
											<a class="add-field extra-fields-audio-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
											<a href="#" class="remove-field btn-remove-audio"><?php esc_html_e( 'Remove Fields', 'buddypress-polls' ); ?></a>
										</div>

										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				<?php } elseif ( $poll_type == 'html' ) { ?>
					<div class="wbpolls-answer-wrap">
						<div class="row wbpoll-list-item" id="type_html" style="">
							<div class="ans-records html_records_edit">
								<div class="ans-records-wrap">
									<label><?php esc_html_e( 'HTML Answer', 'buddypress-polls' ); ?></label>
									<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo ( ! empty( $options ) ) ? esc_attr( $options[0]['ans'] ) : ''; ?>">
									<label><?php esc_html_e( 'HTML Content', 'buddypress-polls' ); ?></label>
									<textarea name="_wbpoll_html_answer[]" data-name="_wbpoll_html_answer[]" id="wbpoll_html_answer_textarea" class="wbpoll_html_answer_textarea tiny"><?php echo ( ! empty( $options ) ) ? esc_attr( $options[0]['html'] ) : ''; ?></textarea>
									<input type="hidden" id="wbpoll_answer_extra_type" value="html" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
								</div>
								<a class="add-field extra-fields-html-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="html_records_dynamic_edit">
								<?php
								foreach ( $options as $key => $optn ) {
									if ( $key != 0 ) {
										?>
										<div class="remove remove1">
											<div class="ans-records-wrap">
												<label><?php esc_html_e( 'HTML Answer', 'buddypress-polls' ); ?></label>
												<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="<?php echo esc_attr( $optn['ans'] ); ?>">
												<label><?php esc_html_e( 'HTML Content', 'buddypress-polls' ); ?></label>
												<textarea name="_wbpoll_html_answer[]" data-name="_wbpoll_html_answer[]" id="wbpoll_html_answer_textarea" class="wbpoll_html_answer_textarea tiny"><?php echo esc_html( $optn['html'] ); ?></textarea>
												<input type="hidden" id="wbpoll_answer_extra_type" value="html" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
											</div>
											<a class="add-field extra-fields-html-edit" data-id="<?php echo count( $options ); ?>" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
											<a href="#" class="remove-field btn-remove-html"><?php esc_html_e( 'Remove Fields', 'buddypress-polls' ); ?></a>
										</div>

										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				<?php } ?>

					<div class="wbpolls-answer-wrap">
						<!-- for text type -->
						<div class="row wbpoll-list-item" id="type_text" style="display:none;">
							<div class="ans-records text_records">
								<div class="ans-records-wrap">
									<label><?php esc_html_e( 'Text Answer', 'buddypress-polls' ); ?></label>
									<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="">
									<input type="hidden" id="wbpoll_answer_extra_type" value="default" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
								</div>
								<a class="add-field extra-fields-text" data-id="0" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="text_records_dynamic"></div>
						</div>

						<?php if ( $enable_image_poll ) : ?>
						<!-- for image type -->
						<div class="row wbpoll-list-item" id="type_image" style="display:none;">
							<div class="ans-records image_records">
								<div class="ans-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail" id="wbpoll-image-input-preview-thumbnail">
										</div>
									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Image Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="">
										<input type="hidden" id="wbpoll_answer_extra_type" value="image" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Image URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_full_size_image_answer[]" data-name="_wbpoll_full_size_image_answer[]" class="wbpoll_image_answer_url" id="wbpoll_image_answer_url" type="url" value="">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="image"></button>
									</div>
								</div>
								<a class="add-field extra-fields-image" data-id="0" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="image_records_dynamic"></div>
						</div>
						<?php endif; ?>

						<?php if ( $enable_video_poll ) : ?>
						<!-- for video type -->
						<div class="row wbpoll-list-item" id="type_video" style="display:none;">
							<div class="ans-records video_records ">
								<div class="ans-records-wrap ans-video-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail">
										</div>
									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Video Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" type="text" class="wbpoll_answer" value="">
										<input type="hidden" id="wbpoll_answer_extra_type" value="video" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Video URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_video_answer_url[]" data-name="_wbpoll_video_answer_url[]" id="wbpoll_video_answer_url" class="wbpoll_video_answer_url" type="url" value="">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="video"></button>
										<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;">
											<span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
											<input type="radio" class="yes_video wbpoll_video_import_info" id="video_import_yes" name="_wbpoll_video_import_info[]" data-name="_wbpoll_video_import_info[]" value="yes">
											<label for="video_import_yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
											<input type="radio" id="video_import_no" name="_wbpoll_video_import_info[]" data-name="_wbpoll_video_import_info[]" value="no" class="wbpoll_video_import_info">
											<label for="video_import_no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label>
										</div>
									</div>
								</div>
								<a class="add-field extra-fields-video" data-id="0" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="video_records_dynamic"></div>
						</div>
						<?php endif; ?>

						<?php if ( $enable_audio_poll ) : ?>
						<!-- for audio type -->
						<div class="row wbpoll-list-item" id="type_audio" style="display:none;">
							<div class="ans-records audio_records ans-audio-records-wrap">
								<div class="ans-records-wrap">
									<div class="wbpoll-image-input-preview">
										<div class="wbpoll-image-input-preview-thumbnail">
										</div>
									</div>
									<div class="wbpoll-image-input-details">
										<label><?php esc_html_e( 'Audio Answer', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="">
										<input type="hidden" id="wbpoll_answer_extra_type" value="audio" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
										<label><?php esc_html_e( 'Audio URL', 'buddypress-polls' ); ?></label>
										<input name="_wbpoll_audio_answer_url[]" data-name="_wbpoll_audio_answer_url[]" id="wbpoll_audio_answer_url" class="wbpoll_audio_answer_url" type="url" value="">
										<button type="button" class="bpolls-attach dashicons dashicons-admin-media" data-type="audio"></button>
										<div class="wbpoll-input-group-suggestions hide_suggestion" style="display:none;"><span><?php esc_html_e( 'Import information from ?', 'buddypress-polls' ); ?></span>
											<input type="radio" class="yes_audio wbpoll_audio_import_info" id="audio_import_yes" name="_wbpoll_audio_import_info[]" data-name="_wbpoll_audio_import_info[]" value="yes">
											<label for="audio_import_yes"><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></label>
											<input type="radio" id="audio_import_no" name="_wbpoll_audio_import_info[]" data-name="_wbpoll_audio_import_info[]" value="no" class="wbpoll_audio_import_info">
											<label for="audio_import_no"><?php esc_html_e( 'No', 'buddypress-polls' ); ?></label><br>
										</div>
									</div>
								</div>
								<a class="add-field extra-fields-audio" data-id="0" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="audio_records_dynamic"></div>
						</div>
						<?php endif; ?>

						<?php if ( $enable_html_poll ) : ?>
						<!-- for html type -->
						<div class="row wbpoll-list-item" id="type_html" style="display:none;">
							<div class="ans-records html_records">
								<div class="ans-records-wrap">

									<label><?php esc_html_e( 'HTML Answer', 'buddypress-polls' ); ?></label>
									<input name="_wbpoll_answer[]" data-name="_wbpoll_answer[]" id="wbpoll_answer" class="wbpoll_answer" type="text" value="">
									<label><?php esc_html_e( 'HTML Content', 'buddypress-polls' ); ?></label>
									<textarea name="_wbpoll_html_answer[]" data-name="_wbpoll_html_answer[]" id="wbpoll_html_answer_textarea" class="wbpoll_html_answer_textarea tiny"></textarea>
									<input type="hidden" id="wbpoll_answer_extra_type" value="html" name="_wbpoll_answer_extra[][type]" data-name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra" />
								</div>
								<a class="add-field extra-fields-html" data-id="0" href="#"><?php esc_html_e( 'Add More', 'buddypress-polls' ); ?></a>
							</div>
							<div class="html_records_dynamic"></div>
						</div>
						<?php endif; ?>
					</div>
				<span id="error_ans" style="color:red;"></span>

				<div class="wbcom-polls-option-wrap">
					<table class="form-table wbpoll-answer-options">
						<tbody>
							<tr>
								<th><label for="_wbpoll_never_expire"><?php esc_html_e( 'Never Expire', 'buddypress-polls' ); ?></label></th>
								<td>
									<fieldset class="radio_fields">
										<legend class="screen-reader-text"><span><?php esc_html_e( 'input type="radio"', 'buddypress-polls' ); ?></span></legend>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_never_expire-radio-yes">
											<input id="_wbpoll_never_expire-radio-yes" type="radio" name="_wbpoll_never_expire" value="1" <?php checked( $never_expire, 1 ); ?>>
											<span><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
										</label>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_never_expire-radio-no">
											<input id="_wbpoll_never_expire-radio-no" type="radio" name="_wbpoll_never_expire" value="0" <?php checked( $never_expire, 0 ); ?>>
											<span><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
										</label>
									</fieldset>
									<span class="description"><?php esc_html_e( 'Select if you want your poll to never expire.(can be override from shortcode param)', 'buddypress-polls' ); ?></span>
								</td>
							</tr>
							<tr class="wbpoll_show_date">
								<th><label for="_wbpoll_start_date"><?php esc_html_e( 'Start Date', 'buddypress-polls' ); ?></label></th>
								<td><input type="text" class="wbpollmetadatepicker hasDatepicker" name="_wbpoll_start_date" id="_wbpoll_start_date" value="<?php echo ! empty( $start_time ) ? esc_attr( $start_time ) : esc_attr( current_time( 'Y-m-d H:i:s' ) ); ?>" size="30">
									<span class="description"><?php esc_html_e( 'Poll Start Date. [Note: Field required. Default is today]', 'buddypress-polls' ); ?></span>
								</td>
							</tr>
							<tr class="wbpoll_show_date">
								<?php
								$current_date    = current_time( 'Y-m-d H:i:s' );
								$next_seven_days = date_i18n( 'Y-m-d H:i:s', strtotime( $current_date . ' +7 days' ) );
								?>
								<th><label for="_wbpoll_end_date"><?php esc_html_e( 'End Date', 'buddypress-polls' ); ?></label></th>
								<td><input type="text" class="wbpollmetadatepicker hasDatepicker" name="_wbpoll_end_date" id="_wbpoll_end_date" value="<?php echo ! empty( $end_date ) ? esc_attr( $end_date ) : esc_attr( $next_seven_days ); ?>" size="30">
									<span class="description"><?php esc_html_e( 'Poll End Date. [Note: Field required. Default is next seven days.]', 'buddypress-polls' ); ?></span>
								</td>
							</tr>
							<tr class="wbpoll_result_after_expires">
								<th><label for="_wbpoll_show_result_before_expire"><?php esc_html_e( 'Show Result After Expires', 'buddypress-polls' ); ?></label></th>
								<td>
									<fieldset class="radio_fields">
										<legend class="screen-reader-text"><span><?php esc_html_e( 'input type="radio"', 'buddypress-polls' ); ?></span></legend>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_show_result_before_expire-radio-yes">
											<input id="_wbpoll_show_result_before_expire-radio-yes" type="radio" name="_wbpoll_show_result_before_expire" value="1" <?php checked( $show_result_after_expire, 1 ); ?>>
											<span><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
										</label>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_show_result_before_expire-radio-no">
											<input id="_wbpoll_show_result_before_expire-radio-no" type="radio" name="_wbpoll_show_result_before_expire" value="0" <?php checked( $show_result_after_expire, 0 ); ?>>
											<span><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
										</label>
									</fieldset>
									<span class="description"><?php esc_html_e( 'Select if you want poll to show result After expires. After expires the result will be shown always.', 'buddypress-polls' ); ?></span>
								</td>
							</tr>
							<tr>
								<th><label for="_wbpoll_multivote"><?php esc_html_e( 'Enable Multi Choice', 'buddypress-polls' ); ?></label></th>
								<td>
									<fieldset class="radio_fields">
										<legend class="screen-reader-text"><span><?php esc_html_e( 'input type="radio"', 'buddypress-polls' ); ?></span></legend>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_multivote-radio-yes">
											<input id="_wbpoll_multivote-radio-yes" type="radio" name="_wbpoll_multivote" value="1" <?php checked( $multivote, 1 ); ?>>
											<span><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
										</label>
										<label class="wbpoll-answer-options-radio-field" title="g:i a" for="_wbpoll_multivote-radio-no">
											<input id="_wbpoll_multivote-radio-no" type="radio" name="_wbpoll_multivote" value="0" <?php checked( $multivote, 0 ); ?>>
											<span><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
										</label>
									</fieldset>
									<span class="description"><?php esc_html_e( 'Can user vote multiple option', 'buddypress-polls' ); ?></span>
								</td>
							</tr>
							<?php
							if ( ! empty( $option_value ) ) {
								$wbpolls_user_add_extra_op = isset( $option_value['wbpolls_user_add_extra_op'] ) ? $option_value['wbpolls_user_add_extra_op'] : '';
							}
							if ( $wbpolls_user_add_extra_op == 'yes' ) {
								?>
								<tr id="addtitonal_option" style="
								<?php
								if ( $poll_type != 'default' ) {
									echo 'display:none;'; }
								?>
								">
									<th><label for="_wbpoll_add_additional_fields"><?php esc_html_e( 'Add Additional poll option', 'buddypress-polls' ); ?></label></th>
									<td>
										<fieldset class="radio_fields">
											<legend class="screen-reader-text"><span><?php esc_html_e( 'Additional Fields', 'buddypress-polls' ); ?></span></legend>
											<label class="wbpoll-answer-options-radio-field" for="_wbpoll_add_additional_fields-radio-yes">
												<input id="_wbpoll_add_additional_fields-radio-yes" type="radio" name="_wbpoll_add_additional_fields" value="1" <?php checked( $add_additional_fields, '1' ); ?>>
												<span><?php esc_html_e( 'Yes', 'buddypress-polls' ); ?></span>
											</label>
											<label class="wbpoll-answer-options-radio-field" for="_wbpoll_add_additional_fields-radio-no">
												<input id="_wbpoll_add_additional_fields-radio-no" type="radio" name="_wbpoll_add_additional_fields" value="0" <?php checked( $add_additional_fields, '0' ); ?>>
												<span><?php esc_html_e( 'No', 'buddypress-polls' ); ?></span>
											</label>
										</fieldset>
										<span class="description"><?php esc_html_e( 'Add Additional poll option only for text poll.', 'buddypress-polls' ); ?></span>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<?php if ( isset( $post_id ) && ! empty( $post_id ) ) : ?>
					<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Poll Update', 'buddypress-polls' ); ?></button>
				<?php else : ?>
					<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Poll Create', 'buddypress-polls' ); ?></button>
				<?php endif ?>

			</form>
			<div class="wbpoll-voted-info wbpoll-success" id="pollsuccess" style="display:none;"></div>
		<?php } else { ?>
			<div class="wbpoll_wrapper wbpoll_wrapper-content_hook" data-reference="content_hook">
				<p class="wbpoll-voted-info wbpoll-alert"><?php esc_html_e( 'This page content only for login members.', 'buddypress-polls' ); ?> </p>
			</div>
		<?php } ?>
	</div>
</div>
<?php
$post = $temp_post;

// Output character counter JavaScript if any limits are set.
if ( $poll_title_limit > 0 || $poll_description_limit > 0 || $poll_answer_limit > 0 ) :
	?>
<style>
.wbpoll-char-counter {
	font-size: 12px;
	color: #666;
	margin-top: 5px;
	text-align: right;
}
.wbpoll-char-counter.warning {
	color: #f0ad4e;
}
.wbpoll-char-counter.danger {
	color: #d9534f;
	font-weight: bold;
}
.wbpoll-answer .wbpoll-char-counter {
	margin-top: 3px;
}
</style>
<script>
(function() {
	'use strict';

	var answerLimit = <?php echo intval( $poll_answer_limit ); ?>;
	var descriptionLimit = <?php echo intval( $poll_description_limit ); ?>;
	var charactersText = '<?php echo esc_js( __( 'characters', 'buddypress-polls' ) ); ?>';

	// Create character counter element safely
	function createCharCounter(currentCount, limit) {
		var counter = document.createElement('div');
		counter.className = 'wbpoll-char-counter wbpoll-answer';

		var countSpan = document.createElement('span');
		countSpan.className = 'char-count';
		countSpan.textContent = currentCount;

		counter.appendChild(countSpan);
		counter.appendChild(document.createTextNode(' / ' + limit + ' ' + charactersText));

		return counter;
	}

	// Update character counter display
	function updateCharCounter(input, counter, limit) {
		var length = input.value ? input.value.length : 0;
		var countSpan = counter.querySelector('.char-count');
		if (countSpan) {
			countSpan.textContent = length;
		}

		// Update counter color based on usage
		counter.classList.remove('warning', 'danger');
		var percentage = (length / limit) * 100;
		if (percentage >= 100) {
			counter.classList.add('danger');
		} else if (percentage >= 80) {
			counter.classList.add('warning');
		}
	}

	// Initialize character counters on page load
	document.addEventListener('DOMContentLoaded', function() {
		// Poll Title counter
		var titleInput = document.getElementById('polltitle');
		var titleCounter = document.querySelector('.wbpoll-char-counter[data-for="polltitle"]');
		if (titleInput && titleCounter) {
			var titleLimit = parseInt(titleInput.getAttribute('data-char-limit') || titleInput.getAttribute('maxlength'), 10);
			updateCharCounter(titleInput, titleCounter, titleLimit);
			titleInput.addEventListener('input', function() {
				updateCharCounter(titleInput, titleCounter, titleLimit);
			});
		}

		// Poll Description counter (simple textarea)
		var descTextarea = document.getElementById('poll-content');
		var descCounter = document.querySelector('.wbpoll-char-counter[data-for="poll-content"]');
		if (descTextarea && descCounter && descTextarea.tagName === 'TEXTAREA') {
			var descLimit = parseInt(descCounter.getAttribute('data-limit'), 10);
			updateCharCounter(descTextarea, descCounter, descLimit);
			descTextarea.addEventListener('input', function() {
				updateCharCounter(descTextarea, descCounter, descLimit);
			});
		}

		// Poll Description counter (TinyMCE editor)
		if (descCounter && typeof wp !== 'undefined' && wp.editor && typeof tinymce !== 'undefined') {
			var checkEditor = setInterval(function() {
				var editor = tinymce.get('poll-content');
				if (editor) {
					clearInterval(checkEditor);
					var descLimit = parseInt(descCounter.getAttribute('data-limit'), 10);

					// Initial count
					var content = editor.getContent({ format: 'text' });
					var countSpan = descCounter.querySelector('.char-count');
					if (countSpan) {
						countSpan.textContent = content.length;
					}

					// Update on keyup
					editor.on('keyup change', function() {
						var text = editor.getContent({ format: 'text' });
						if (countSpan) {
							countSpan.textContent = text.length;
						}
						descCounter.classList.remove('warning', 'danger');
						var percentage = (text.length / descLimit) * 100;
						if (percentage >= 100) {
							descCounter.classList.add('danger');
						} else if (percentage >= 80) {
							descCounter.classList.add('warning');
						}

						// Enforce limit in TinyMCE
						if (text.length > descLimit) {
							editor.setContent(text.substring(0, descLimit));
							editor.selection.select(editor.getBody(), true);
							editor.selection.collapse(false);
						}
					});
				}
			}, 500);
		}

		// Answer field character counters - add maxlength to all answer inputs
		if (answerLimit > 0) {
			var answerInputs = document.querySelectorAll('.wbpoll_answer');
			answerInputs.forEach(function(input) {
				input.setAttribute('maxlength', answerLimit);
				input.setAttribute('data-char-limit', answerLimit);

				// Create counter if not exists
				var parent = input.closest('.ans-records-wrap');
				if (parent && !parent.querySelector('.wbpoll-char-counter')) {
					var counter = createCharCounter(input.value ? input.value.length : 0, answerLimit);
					input.parentNode.insertBefore(counter, input.nextSibling);

					input.addEventListener('input', function() {
						updateCharCounter(input, counter, answerLimit);
					});
				}
			});

			// Observer for dynamically added answer fields
			var observer = new MutationObserver(function(mutations) {
				mutations.forEach(function(mutation) {
					mutation.addedNodes.forEach(function(node) {
						if (node.nodeType === 1) {
							var newInputs = node.querySelectorAll ? node.querySelectorAll('.wbpoll_answer') : [];
							newInputs.forEach(function(input) {
								if (!input.hasAttribute('maxlength')) {
									input.setAttribute('maxlength', answerLimit);
									input.setAttribute('data-char-limit', answerLimit);

									var parent = input.closest('.ans-records-wrap');
									if (parent && !parent.querySelector('.wbpoll-char-counter')) {
										var counter = createCharCounter(0, answerLimit);
										input.parentNode.insertBefore(counter, input.nextSibling);

										input.addEventListener('input', function() {
											updateCharCounter(input, counter, answerLimit);
										});
									}
								}
							});
						}
					});
				});
			});

			observer.observe(document.body, { childList: true, subtree: true });
		}
	});
})();
</script>
	<?php
endif;
