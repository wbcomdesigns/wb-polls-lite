<?php
/**
 * Create Poll Shortcode Template
 *
 * This template is used by the [wbpoll_create] shortcode to display
 * a standalone poll creation form.
 *
 * @package BuddyPress_Polls
 * @since 4.5.0
 *
 * Available variables:
 * - $form_title    (string) Form title
 * - $redirect_url  (string) URL to redirect after successful poll creation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get poll creation settings using centralized helper.
$creation_settings   = WBPollHelper::get_poll_creation_settings();
$enabled_types       = $creation_settings['enabled_types'];

// Extract enabled types for template use.
$enable_image_poll   = $enabled_types['image'];
$enable_video_poll   = $enabled_types['video'];
$enable_audio_poll   = $enabled_types['audio'];
$enable_html_poll    = $enabled_types['html'];
$disable_html_editor = $creation_settings['disable_html_editor'];
$use_simple_textarea = $creation_settings['use_simple_textarea'];

// Default values for new poll.
$poll_type    = 'default';
$never_expire = '1';
$start_time   = current_time( 'Y-m-d H:i:s' );
$end_date     = wp_date( 'Y-m-d H:i:s', strtotime( '+7 days' ) );
?>

<div class="polls-create-shortcode" data-redirect-url="<?php echo esc_url( $redirect_url ); ?>">
	<div class="polls-create-shortcode__header">
		<h2 class="polls-create-shortcode__title"><?php echo esc_html( $form_title ); ?></h2>
	</div>

	<div class="polls-create-shortcode__body">
		<form id="polls-form" class="polls-form polls-form--standalone">
			<?php wp_nonce_field( 'wbpoll_create_poll', 'wbpoll_nonce' ); ?>
			<input type="hidden" name="author_id" id="author_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
			<input type="hidden" name="poll_id" id="poll_id" value="">
			<input type="hidden" name="shortcode_redirect" id="shortcode_redirect" value="<?php echo esc_url( $redirect_url ); ?>">

			<!-- Basic Info Section -->
			<div class="polls-form__section">
				<div class="polls-form__group">
					<label class="polls-form__label" for="polltitle">
						<?php esc_html_e( 'Poll Title', 'buddypress-polls' ); ?>
						<span class="polls-form__required">*</span>
					</label>
					<input type="text" class="polls-form__input" name="title" id="polltitle" value="" placeholder="<?php esc_attr_e( 'Enter your poll question...', 'buddypress-polls' ); ?>" required>
					<span class="polls-form__error" id="error_title"></span>
				</div>

				<div class="polls-form__group">
					<label class="polls-form__label" for="poll-content">
						<?php esc_html_e( 'Description', 'buddypress-polls' ); ?>
						<span class="polls-form__optional"><?php esc_html_e( '(Optional)', 'buddypress-polls' ); ?></span>
					</label>
					<?php
					if ( $use_simple_textarea ) {
						?>
						<textarea class="polls-form__textarea" name="content" id="poll-content" rows="3" placeholder="<?php esc_attr_e( 'Add more context to your poll...', 'buddypress-polls' ); ?>"></textarea>
						<?php
					} else {
						$editor_settings = array(
							'textarea_name' => 'content',
							'editor_height' => 150,
							'media_buttons' => false,
							'teeny'         => true,
							'editor_class'  => 'polls-form__editor',
						);
						if ( $disable_html_editor ) {
							$editor_settings['quicktags'] = false;
						}
						wp_editor( '', 'poll-content', $editor_settings );
					}
					?>
				</div>
			</div>

			<!-- Poll Expiry Settings -->
			<div class="polls-form__section">
				<div class="polls-form__group">
					<label class="polls-form__label"><?php esc_html_e( 'Poll Duration', 'buddypress-polls' ); ?></label>
					<div class="polls-form__expiry-options">
						<label class="polls-form__expiry-option polls-form__expiry-option--selected">
							<input type="radio" name="_wbpoll_never_expire" value="1" checked>
							<span class="polls-form__expiry-icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
							</span>
							<span class="polls-form__expiry-text">
								<strong><?php esc_html_e( 'Never Expires', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Poll stays open indefinitely', 'buddypress-polls' ); ?></small>
							</span>
						</label>
						<label class="polls-form__expiry-option">
							<input type="radio" name="_wbpoll_never_expire" value="0">
							<span class="polls-form__expiry-icon">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
							</span>
							<span class="polls-form__expiry-text">
								<strong><?php esc_html_e( 'Set Duration', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Schedule start and end dates', 'buddypress-polls' ); ?></small>
							</span>
						</label>
					</div>
				</div>

				<!-- Date Fields -->
				<div class="polls-form__row polls-form__dates" id="polls-dates" style="display:none;">
					<div class="polls-form__group polls-form__group--half">
						<label class="polls-form__label" for="_wbpoll_start_date"><?php esc_html_e( 'Start Date', 'buddypress-polls' ); ?></label>
						<input type="text" class="polls-form__input wbpollmetadatepicker" name="_wbpoll_start_date" id="_wbpoll_start_date" value="<?php echo esc_attr( $start_time ); ?>">
					</div>
					<div class="polls-form__group polls-form__group--half">
						<label class="polls-form__label" for="_wbpoll_end_date"><?php esc_html_e( 'End Date', 'buddypress-polls' ); ?></label>
						<input type="text" class="polls-form__input wbpollmetadatepicker" name="_wbpoll_end_date" id="_wbpoll_end_date" value="<?php echo esc_attr( $end_date ); ?>">
					</div>
				</div>
			</div>

			<!-- Poll Type Selection -->
			<div class="polls-form__section">
				<div class="polls-form__group">
					<label class="polls-form__label">
						<?php esc_html_e( 'Poll Type', 'buddypress-polls' ); ?>
						<span class="polls-form__required">*</span>
					</label>
					<div class="polls-form__type-grid" id="poll-type-selector">
						<label class="polls-form__type-card polls-form__type-card--selected">
							<input type="radio" name="poll_type" value="default" checked required>
							<span class="polls-form__type-icon polls-form__type-icon--text">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M14 17H4v2h10v-2zm6-8H4v2h16V9zM4 15h16v-2H4v2zM4 5v2h16V5H4z"/></svg>
							</span>
							<span class="polls-form__type-info">
								<strong><?php esc_html_e( 'Text Only', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Simple text-based options', 'buddypress-polls' ); ?></small>
							</span>
						</label>
						<?php if ( $enable_image_poll ) : ?>
						<label class="polls-form__type-card">
							<input type="radio" name="poll_type" value="image">
							<span class="polls-form__type-icon polls-form__type-icon--image">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
							</span>
							<span class="polls-form__type-info">
								<strong><?php esc_html_e( 'With Images', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Visual options with photos', 'buddypress-polls' ); ?></small>
							</span>
						</label>
						<?php endif; ?>
						<?php if ( $enable_video_poll ) : ?>
						<label class="polls-form__type-card">
							<input type="radio" name="poll_type" value="video">
							<span class="polls-form__type-icon polls-form__type-icon--video">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
							</span>
							<span class="polls-form__type-info">
								<strong><?php esc_html_e( 'With Videos', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Embed video content', 'buddypress-polls' ); ?></small>
							</span>
						</label>
						<?php endif; ?>
						<?php if ( $enable_audio_poll ) : ?>
						<label class="polls-form__type-card">
							<input type="radio" name="poll_type" value="audio">
							<span class="polls-form__type-icon polls-form__type-icon--audio">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
							</span>
							<span class="polls-form__type-info">
								<strong><?php esc_html_e( 'With Audio', 'buddypress-polls' ); ?></strong>
								<small><?php esc_html_e( 'Audio clips or music', 'buddypress-polls' ); ?></small>
							</span>
						</label>
						<?php endif; ?>
					</div>
					<!-- Hidden select for backward compatibility -->
					<select class="polls-form__select" name="poll_type_select" id="poll_type" style="display:none;">
						<option value="default" selected><?php esc_html_e( 'Text Only', 'buddypress-polls' ); ?></option>
						<?php if ( $enable_image_poll ) : ?>
						<option value="image"><?php esc_html_e( 'With Images', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
						<?php if ( $enable_video_poll ) : ?>
						<option value="video"><?php esc_html_e( 'With Videos', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
						<?php if ( $enable_audio_poll ) : ?>
						<option value="audio"><?php esc_html_e( 'With Audio', 'buddypress-polls' ); ?></option>
						<?php endif; ?>
					</select>
				</div>
			</div>

			<!-- Answer Options Section -->
			<div class="polls-form__section">
				<div class="polls-form__section-header">
					<h3 class="polls-form__section-title"><?php esc_html_e( 'Answer Options', 'buddypress-polls' ); ?></h3>
					<span class="polls-form__section-hint"><?php esc_html_e( 'Drag to reorder', 'buddypress-polls' ); ?></span>
				</div>

				<div class="polls-answers" id="polls-answers">
					<!-- Text Poll Answers -->
					<div class="polls-answers__list" id="type_text" data-poll-type="default">
						<div class="polls-answer" data-index="0">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option 1', 'buddypress-polls' ); ?>">
							<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
						<div class="polls-answer" data-index="1">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option 2', 'buddypress-polls' ); ?>">
							<input type="hidden" value="default" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
					</div>

					<?php if ( $enable_image_poll ) : ?>
					<!-- Image Poll Answers -->
					<div class="polls-answers__list" id="type_image" data-poll-type="image" style="display:none;">
						<div class="polls-answer polls-answer--media" data-index="0">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<div class="polls-answer__preview"></div>
							<div class="polls-answer__fields">
								<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
								<input type="hidden" value="image" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
								<div class="polls-answer__url-row">
									<input type="url" class="polls-answer__input wbpoll_image_answer_url" name="_wbpoll_full_size_image_answer[]" value="" placeholder="<?php esc_attr_e( 'Image URL...', 'buddypress-polls' ); ?>">
									<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="image" aria-label="<?php esc_attr_e( 'Choose image', 'buddypress-polls' ); ?>">
										<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
									</button>
								</div>
							</div>
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $enable_video_poll ) : ?>
					<!-- Video Poll Answers -->
					<div class="polls-answers__list" id="type_video" data-poll-type="video" style="display:none;">
						<div class="polls-answer polls-answer--media" data-index="0">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<div class="polls-answer__preview"></div>
							<div class="polls-answer__fields">
								<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
								<input type="hidden" value="video" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
								<div class="polls-answer__url-row">
									<input type="url" class="polls-answer__input wbpoll_video_answer_url" name="_wbpoll_video_answer_url[]" value="" placeholder="<?php esc_attr_e( 'Video URL (YouTube, Vimeo, etc.)...', 'buddypress-polls' ); ?>">
									<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="video" aria-label="<?php esc_attr_e( 'Choose video', 'buddypress-polls' ); ?>">
										<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
									</button>
								</div>
							</div>
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $enable_audio_poll ) : ?>
					<!-- Audio Poll Answers -->
					<div class="polls-answers__list" id="type_audio" data-poll-type="audio" style="display:none;">
						<div class="polls-answer polls-answer--media" data-index="0">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<div class="polls-answer__preview"></div>
							<div class="polls-answer__fields">
								<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
								<input type="hidden" value="audio" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
								<div class="polls-answer__url-row">
									<input type="url" class="polls-answer__input wbpoll_audio_answer_url" name="_wbpoll_audio_answer_url[]" value="" placeholder="<?php esc_attr_e( 'Audio URL...', 'buddypress-polls' ); ?>">
									<button type="button" class="polls-answer__media-btn bpolls-attach" data-type="audio" aria-label="<?php esc_attr_e( 'Choose audio', 'buddypress-polls' ); ?>">
										<svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
									</button>
								</div>
							</div>
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $enable_html_poll ) : ?>
					<!-- HTML Poll Answers -->
					<div class="polls-answers__list" id="type_html" data-poll-type="html" style="display:none;">
						<div class="polls-answer polls-answer--html" data-index="0">
							<div class="polls-answer__handle" aria-label="<?php esc_attr_e( 'Drag to reorder', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 6h8M4 10h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</div>
							<div class="polls-answer__fields polls-answer__fields--full">
								<input type="text" class="polls-answer__input wbpoll_answer" name="_wbpoll_answer[]" value="" placeholder="<?php esc_attr_e( 'Option label...', 'buddypress-polls' ); ?>">
								<input type="hidden" value="html" name="_wbpoll_answer_extra[][type]" class="wbpoll_answer_extra">
								<textarea class="polls-answer__textarea wbpoll_html_answer" name="_wbpoll_html_answer[]" rows="3" placeholder="<?php esc_attr_e( 'Enter HTML content...', 'buddypress-polls' ); ?>"></textarea>
							</div>
							<button type="button" class="polls-answer__remove" aria-label="<?php esc_attr_e( 'Remove option', 'buddypress-polls' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</button>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<button type="button" class="polls-btn polls-btn--secondary polls-btn--add-answer" id="polls-add-answer">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 2a.75.75 0 01.75.75v4.5h4.5a.75.75 0 010 1.5h-4.5v4.5a.75.75 0 01-1.5 0v-4.5h-4.5a.75.75 0 010-1.5h4.5v-4.5A.75.75 0 018 2z"/></svg>
					<span><?php esc_html_e( 'Add Option', 'buddypress-polls' ); ?></span>
				</button>
			</div>

			<!-- Advanced Settings -->
			<div class="polls-form__section">
				<fieldset class="polls-form__fieldset">
					<legend class="polls-form__fieldset-legend">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M7.429 1.525a3.5 3.5 0 011.142 0 .75.75 0 01.57.553l.355 1.426a5.5 5.5 0 01.99.572l1.39-.455a.75.75 0 01.782.22 6.5 6.5 0 01.571.99l.108.188a.75.75 0 01-.213.773l-1.035.97a5.5 5.5 0 010 1.141l1.035.97a.75.75 0 01.213.774l-.108.187a6.5 6.5 0 01-.571.99.75.75 0 01-.782.22l-1.39-.455a5.5 5.5 0 01-.99.572l-.355 1.426a.75.75 0 01-.57.553 3.5 3.5 0 01-1.142 0 .75.75 0 01-.57-.553l-.355-1.426a5.5 5.5 0 01-.99-.572l-1.39.455a.75.75 0 01-.782-.22 6.5 6.5 0 01-.571-.99l-.108-.188a.75.75 0 01.213-.773l1.035-.97a5.5 5.5 0 010-1.141l-1.035-.97a.75.75 0 01-.213-.774l.108-.187a6.5 6.5 0 01.571-.99.75.75 0 01.782-.22l1.39.455a5.5 5.5 0 01.99-.572l.355-1.426a.75.75 0 01.57-.553zM8 11a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
						<?php esc_html_e( 'Advanced Settings', 'buddypress-polls' ); ?>
					</legend>

					<div class="polls-form__fieldset-content">
						<div class="polls-form__checkbox-group">
							<input type="checkbox" name="_wbpoll_multivote" id="_wbpoll_multivote" value="1">
							<label for="_wbpoll_multivote"><?php esc_html_e( 'Allow multiple selections', 'buddypress-polls' ); ?></label>
						</div>

						<div class="polls-form__checkbox-group">
							<input type="checkbox" name="_wbpoll_show_result_before_expire" id="_wbpoll_show_result_before_expire" value="1">
							<label for="_wbpoll_show_result_before_expire"><?php esc_html_e( 'Show results before voting', 'buddypress-polls' ); ?></label>
						</div>
					</div>
				</fieldset>
			</div>

			<!-- Form Actions -->
			<div class="polls-form__submit">
				<button type="submit" class="polls-btn polls-btn--primary polls-btn--lg" id="polls-form-submit">
					<span class="polls-btn__text"><?php esc_html_e( 'Create Poll', 'buddypress-polls' ); ?></span>
					<span class="polls-btn__loading" style="display:none;">
						<svg class="polls-btn__spinner" width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 100 14A7 7 0 008 1zM1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z" opacity=".25"/><path d="M8 1a7 7 0 017 7h-1.5A5.5 5.5 0 008 2.5V1z"/></svg>
						<?php esc_html_e( 'Creating...', 'buddypress-polls' ); ?>
					</span>
				</button>
			</div>
		</form>
	</div>
</div>

<script>
(function() {
	// Initialize the form when DOM is ready
	document.addEventListener('DOMContentLoaded', function() {
		// Always initialize the shortcode form - it has its own handlers
		// The shortcode form uses .polls-create-shortcode container
		const shortcodeContainer = document.querySelector('.polls-create-shortcode');
		if (shortcodeContainer) {
			initStandaloneForm();
		}
	});

	function initStandaloneForm() {
		const form = document.getElementById('polls-form');
		if (!form) return;

		const container = document.querySelector('.polls-create-shortcode');
		const redirectUrl = container ? container.dataset.redirectUrl : '';

		// Handle poll type change
		const typeRadios = form.querySelectorAll('input[name="poll_type"]');
		typeRadios.forEach(radio => {
			radio.addEventListener('change', function() {
				handlePollTypeChange(this.value);
			});
		});

		// Handle expiry change
		const expiryRadios = form.querySelectorAll('input[name="_wbpoll_never_expire"]');
		expiryRadios.forEach(radio => {
			radio.addEventListener('change', function() {
				handleExpiryChange(this.value);
			});
		});

		// Handle add option button
		const addOptionBtn = document.getElementById('polls-add-answer');
		if (addOptionBtn) {
			addOptionBtn.addEventListener('click', addOption);
		}

		// Handle form submission
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			submitForm(redirectUrl);
		});

		function handlePollTypeChange(pollType) {
			// Update type cards
			const typeCards = form.querySelectorAll('.polls-form__type-card');
			typeCards.forEach(card => {
				const radio = card.querySelector('input[type="radio"]');
				if (radio && radio.value === pollType) {
					card.classList.add('polls-form__type-card--selected');
				} else {
					card.classList.remove('polls-form__type-card--selected');
				}
			});

			// Update hidden select
			const hiddenSelect = document.getElementById('poll_type');
			if (hiddenSelect) {
				hiddenSelect.value = pollType;
			}

			// Show/hide answer lists
			const typeMap = {
				'default': 'type_text',
				'image': 'type_image',
				'video': 'type_video',
				'audio': 'type_audio',
				'html': 'type_html',
			};

			const answerLists = form.querySelectorAll('.polls-answers__list');
			answerLists.forEach(list => {
				const listType = list.dataset.pollType || list.id.replace('type_', '');
				if (listType === pollType || (pollType === 'default' && list.id === 'type_text')) {
					list.style.display = '';
				} else {
					list.style.display = 'none';
				}
			});
		}

		function handleExpiryChange(value) {
			// Update expiry option cards
			const expiryOptions = form.querySelectorAll('.polls-form__expiry-option');
			expiryOptions.forEach(option => {
				const radio = option.querySelector('input[type="radio"]');
				if (radio && radio.value === value) {
					option.classList.add('polls-form__expiry-option--selected');
				} else {
					option.classList.remove('polls-form__expiry-option--selected');
				}
			});

			// Show/hide date fields
			const dateFields = document.getElementById('polls-dates');
			if (dateFields) {
				dateFields.style.display = value === '1' ? 'none' : '';
			}
		}

		function addOption() {
			const pollType = form.querySelector('input[name="poll_type"]:checked')?.value || 'default';
			const typeMap = {
				'default': 'type_text',
				'image': 'type_image',
				'video': 'type_video',
				'audio': 'type_audio',
				'html': 'type_html',
			};
			const listId = typeMap[pollType] || 'type_text';
			const answerList = document.getElementById(listId);
			if (!answerList) return;

			const existingItems = answerList.querySelectorAll('.polls-answer');
			const newIndex = existingItems.length;
			const firstItem = existingItems[0];
			if (!firstItem) return;

			const newItem = firstItem.cloneNode(true);
			newItem.dataset.index = newIndex;

			// Clear input values
			newItem.querySelectorAll('input[type="text"], input[type="url"], textarea').forEach(input => {
				input.value = '';
			});

			// Update placeholder
			const labelInput = newItem.querySelector('.wbpoll_answer');
			if (labelInput) {
				labelInput.placeholder = 'Option ' + (newIndex + 1);
			}

			answerList.appendChild(newItem);
		}

		function submitForm(redirectUrl) {
			const submitBtn = document.getElementById('polls-form-submit');
			const btnText = submitBtn?.querySelector('.polls-btn__text');
			const btnLoading = submitBtn?.querySelector('.polls-btn__loading');

			// Show loading state
			if (btnText) btnText.style.display = 'none';
			if (btnLoading) btnLoading.style.display = 'inline-flex';
			if (submitBtn) submitBtn.disabled = true;

			// Collect form data
			const title = document.getElementById('polltitle')?.value?.trim();
			if (!title) {
				alert('<?php echo esc_js( __( 'Please enter a poll title', 'buddypress-polls' ) ); ?>');
				resetButton();
				return;
			}

			const pollType = form.querySelector('input[name="poll_type"]:checked')?.value || 'default';
			const typeMap = {
				'default': 'type_text',
				'image': 'type_image',
				'video': 'type_video',
				'audio': 'type_audio',
				'html': 'type_html',
			};
			const listId = typeMap[pollType] || 'type_text';
			const answerList = document.getElementById(listId);

			// Collect answers
			const answers = [];
			const answerExtras = [];
			const imageUrls = [];
			const videoUrls = [];
			const videoImportInfo = [];
			const audioUrls = [];
			const audioImportInfo = [];
			const htmlContents = [];

			/**
			 * Check if URL is from an embeddable video provider (WordPress default oEmbed providers)
			 * Returns 'yes' for embed providers (use iframe), empty for direct video files (use video tag)
			 */
			function isEmbedVideoUrl(url) {
				if (!url) return '';
				const lowerUrl = url.toLowerCase();
				// WordPress default oEmbed video providers
				const embedProviders = [
					'youtube.com', 'youtu.be',                    // YouTube
					'vimeo.com', 'player.vimeo.com',              // Vimeo
					'dailymotion.com', 'dai.ly',                  // DailyMotion
					'tiktok.com',                                 // TikTok
					'wordpress.tv',                               // WordPress.tv
					'videopress.com',                             // VideoPress
					'ted.com',                                    // TED
					'facebook.com/watch', 'fb.watch',             // Facebook
					'twitter.com', 'x.com',                       // Twitter/X
					'flickr.com',                                 // Flickr
					'tumblr.com',                                 // Tumblr
					'reddit.com',                                 // Reddit
					'imgur.com',                                  // Imgur
					'screencast.com',                             // Screencast
					'amazon.com', 'amzn.to',                      // Amazon
					'animoto.com',                                // Animoto
					'cloudup.com',                                // Cloudup
					'crowdsignal.com', 'polldaddy.com',           // Crowdsignal
					'issuu.com',                                  // Issuu
					'kickstarter.com',                            // Kickstarter
					'meetup.com',                                 // Meetup
					'mixcloud.com',                               // Mixcloud (video)
					'reverbnation.com',                           // ReverbNation
					'scribd.com',                                 // Scribd
					'slideshare.net',                             // SlideShare
					'smugmug.com',                                // SmugMug
					'speakerdeck.com',                            // Speaker Deck
					'twitch.tv',                                  // Twitch
					'wistia.com', 'wi.st',                        // Wistia
					'loom.com',                                   // Loom
					'streamable.com',                             // Streamable
					'bitchute.com',                               // BitChute
					'rumble.com',                                 // Rumble
					'odysee.com',                                 // Odysee
				];
				return embedProviders.some(provider => lowerUrl.includes(provider)) ? 'yes' : '';
			}

			/**
			 * Check if URL is from an embeddable audio provider
			 */
			function isEmbedAudioUrl(url) {
				if (!url) return '';
				const lowerUrl = url.toLowerCase();
				const embedProviders = [
					'spotify.com', 'open.spotify.com',            // Spotify
					'soundcloud.com',                             // SoundCloud
					'mixcloud.com',                               // Mixcloud
					'audiomack.com',                              // Audiomack
					'bandcamp.com',                               // Bandcamp
					'reverbnation.com',                           // ReverbNation
					'audioboom.com',                              // Audioboom
					'anchor.fm',                                  // Anchor (Podcasts)
					'podcasts.apple.com',                         // Apple Podcasts
				];
				return embedProviders.some(provider => lowerUrl.includes(provider)) ? 'yes' : '';
			}

			if (answerList) {
				answerList.querySelectorAll('.polls-answer').forEach(item => {
					const labelInput = item.querySelector('.wbpoll_answer');
					if (labelInput && labelInput.value.trim()) {
						answers.push(labelInput.value.trim());
						answerExtras.push({ type: pollType === 'default' ? 'default' : pollType });

						if (pollType === 'image') {
							const urlInput = item.querySelector('.wbpoll_image_answer_url');
							imageUrls.push(urlInput ? urlInput.value : '');
						} else if (pollType === 'video') {
							const urlInput = item.querySelector('.wbpoll_video_answer_url');
							const videoUrl = urlInput ? urlInput.value : '';
							videoUrls.push(videoUrl);
							// Detect if it's an embed provider or direct video file
							videoImportInfo.push(isEmbedVideoUrl(videoUrl));
						} else if (pollType === 'audio') {
							const urlInput = item.querySelector('.wbpoll_audio_answer_url');
							const audioUrl = urlInput ? urlInput.value : '';
							audioUrls.push(audioUrl);
							// Detect if it's an embed provider or direct audio file
							audioImportInfo.push(isEmbedAudioUrl(audioUrl));
						} else if (pollType === 'html') {
							const htmlInput = item.querySelector('.wbpoll_html_answer');
							htmlContents.push(htmlInput ? htmlInput.value : '');
						}
					}
				});
			}

			if (answers.length < 2) {
				alert('<?php echo esc_js( __( 'Please add at least 2 answer options', 'buddypress-polls' ) ); ?>');
				resetButton();
				return;
			}

			// Build form data
			const formData = {
				title: title,
				post_author: document.getElementById('author_id')?.value || '',
				content: getEditorContent(),
				_wbpoll_never_expire: form.querySelector('input[name="_wbpoll_never_expire"]:checked')?.value || '1',
				_wbpoll_start_date: document.getElementById('_wbpoll_start_date')?.value || '',
				_wbpoll_end_date: document.getElementById('_wbpoll_end_date')?.value || '',
				_wbpoll_show_result_before_expire: document.getElementById('_wbpoll_show_result_before_expire')?.checked ? '1' : '0',
				_wbpoll_multivote: document.getElementById('_wbpoll_multivote')?.checked ? '1' : '0',
				poll_type: pollType,
				_wbpoll_answer: answers,
				_wbpoll_answer_extra: answerExtras,
				_wbpoll_full_size_image_answer: imageUrls,
				_wbpoll_video_answer_url: videoUrls,
				_wbpoll_video_import_info: videoImportInfo,
				_wbpoll_audio_answer_url: audioUrls,
				_wbpoll_audio_import_info: audioImportInfo,
				_wbpoll_html_answer: htmlContents,
			};

			// Submit via REST API
			const siteUrl = typeof wbpollpublic !== 'undefined' ? wbpollpublic.url : '';
			const nonce = typeof wbpollpublic !== 'undefined' ? wbpollpublic.rest_nonce : '';

			fetch(siteUrl + '/wp-json/wbpoll/v1/postpoll', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
				body: JSON.stringify(formData),
			})
			.then(response => response.json())
			.then(data => {
				if (data.success || data.id) {
					// Redirect to dashboard
					if (redirectUrl) {
						window.location.href = redirectUrl;
					} else {
						alert('<?php echo esc_js( __( 'Poll created successfully!', 'buddypress-polls' ) ); ?>');
						resetButton();
					}
				} else {
					alert(data.message || '<?php echo esc_js( __( 'Failed to create poll.', 'buddypress-polls' ) ); ?>');
					resetButton();
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'buddypress-polls' ) ); ?>');
				resetButton();
			});

			function resetButton() {
				if (btnText) btnText.style.display = '';
				if (btnLoading) btnLoading.style.display = 'none';
				if (submitBtn) submitBtn.disabled = false;
			}
		}

		function getEditorContent() {
			if (typeof tinyMCE !== 'undefined') {
				const editor = tinyMCE.get('poll-content');
				if (editor) {
					return editor.getContent();
				}
			}
			const textarea = document.getElementById('poll-content');
			return textarea ? textarea.value : '';
		}
	}
})();
</script>
