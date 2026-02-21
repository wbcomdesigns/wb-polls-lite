if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}
(function($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery( document ).ready(
		function() {
			var poll_html = '';
			var option_html = '';
			$( "form#whats-new-form" ).attr( "enctype", "multipart/form-data" );

			// Store initial poll HTML on first load for reset purposes.
			if ( $( '.bpolls-polls-option-html' ).length ) {
				poll_html = $( '.bpolls-polls-option-html' ).html();
				option_html = $( '.bpolls-option' ).html();
			}

			if (bpolls_ajax_object.reign_polls) {
				  var body = document.body;
				  body.classList.add( "reign-polls" );
			}

			if (bpolls_ajax_object.rt_poll_fix && bpolls_ajax_object.nouveau) {

				$( document ).on(
					'click',
					'.bpolls-icon',
					function() {
						$( '#rtmedia-add-media-button-post-update' ).hide();

					}
				);
				$( document ).on(
					'click',
					'.bpolls-cancel',
					function() {
						$( '#rtmedia-add-media-button-post-update' ).show();
					}
				);

				$( document ).on(
					'focus',
					'#whats-new',
					function() {
						if ($( '#rtmedia-add-media-button-post-update' ).is( ':hidden' )) {
								$( '#rtmedia-add-media-button-post-update' ).show();
						}
					}
				);

				$( document ).on(
					'click',
					'#rtmedia-add-media-button-post-update',
					function() {
						//$('.bpolls-html-container').hide();
					}
				);

				$( document ).on(
					'focus',
					'#whats-new',
					function() {
						if ($( '.bpolls-html-container' ).is( ':hidden' )) {
								$( '.bpolls-html-container' ).show();
						}
					}
				);
			}

			//Manage Poll icon with Buddyboss Plateform
			$( document ).on(
				'click focus',
				'#whats-new',
				function(){
					if (bpolls_ajax_object.buddyboss ) {
						$( '#whats-new-toolbar' ).append( $( '.bpolls-html-container' ) );
						$( '#whats-new-attachments' ).append( $( '.bpolls-polls-option-html' ) );

						if ( $( '.whats-new-form-footer #whats-new-toolbar .bpolls-html-container' ).length == 0 ) {
							$( '.bpolls-html-container' ).appendTo( $( '.whats-new-form-footer #whats-new-toolbar' ) );
						}
					}
				}
			);
			
			if (bpolls_ajax_object.buddyboss && bpolls_ajax_object.allowed_polls) {
				var bb_polls_Interval;

				function bb_pools_icon_push() {
					// Clear any existing interval first
					if (bb_polls_Interval) {
						clearInterval(bb_polls_Interval);
					}

					bb_polls_Interval = setInterval(
						function() {
							if (bpolls_ajax_object.buddyboss && $( '#whats-new-form:not(.focus-in) #whats-new-toolbar .bpolls-html-container-placeholder' ).length == 0 ) {
								var $toolbar = $( '#whats-new-form:not(.focus-in) #whats-new-toolbar' );
								if ($toolbar.length > 0) {
									$toolbar.append( '<div class="post-elements-buttons-item bpolls-html-container-placeholder"><span class="bpolls-icon bp-tooltip" data-bp-tooltip-pos="up" data-bp-tooltip="' + bpolls_ajax_object.add_poll_text + '"><i class="wb-icons wb-icon-bar-chart"></i></span></div>' );
									// Icon added, stop polling
									clearInterval(bb_polls_Interval);
								}
							} else {
								// Icon exists or condition not met, stop polling
								clearInterval(bb_polls_Interval);
							}
						},
						500  // Check every 500ms instead of 10ms
					);

				}

				bb_pools_icon_push();

				// Clean up interval on page unload to prevent memory leaks.
				$( window ).on( 'beforeunload', function() {
					clearInterval( bb_polls_Interval );
				});

				$( document ).on(
					'click',
					'.bb-model-close-button, .activity-update-form-overlay',
					function(){
						clearInterval( bb_polls_Interval );
						bb_pools_icon_push();
					}
				);

					 /* jQuery Ajax prefilter*/
					$.ajaxPrefilter(
						function( options, originalOptions, jqXHR ) {
							try {
								if ( originalOptions.data == null || typeof ( originalOptions.data ) == 'undefined' || typeof ( originalOptions.data.action ) == 'undefined' ) {
									 return true;
								}
							} catch ( e ) {
								return true;
							}

							if ( originalOptions.data.action == 'post_update' ) {
								clearInterval( bb_polls_Interval );
								bb_pools_icon_push();
							}

						}
					);
			}

			$( document ).on(
				'focus',
				'#whats-new',
				function() {
					
					$( '#whats-new-options' ).addClass( 'bpolls-rtm-class' );
				}
			);

			/*==============================================
				=            add new poll option js            =
				==============================================*/
			$( document ).on(
				'click',
				'.bpolls-icon-dialog-cancel',
				function() {
					$( '.bpolls-icon-dialog' ).removeClass( 'is-visible' );
				}
			);

			$( document ).on(
				'click',
				'.bpolls-add-option',
				function() {
					var max_options = bpolls_ajax_object.polls_option_lmit;
					if ( $(this).parents('.bpolls-polls-option-html').find( '.bpolls-option' ).length >= max_options ) {
						  $( '.bpolls-icon-dialog' ).addClass( 'is-visible' );

					} else {

						 var clonedObj = $( this ).parent().siblings().find( '.bpolls-option:first' ).clone().insertAfter( $( this ).parent().siblings().find( '.bpolls-option:last' ) );

						clonedObj.find( 'input' ).each(
							function() {
								this.value       = '';
								this.placeholder = '';
							}
						);

						if (clonedObj.length == 0 ) {
							$(this).parents('.bpolls-polls-option-html').find( '.bpolls-sortable' ).html( '<div class="bpolls-option">' + option_html + '</div>' );
						}
					}
				}
			);

			/*=====  End of add new poll option js  ======*/

			/*==========================================
				=            delete poll option            =
				==========================================*/

			$( document ).on(
				'click',
				'.bpolls-option-delete',
				function() {
					$( this ).parent( '.bpolls-option' ).remove();
				}
			);

			/*=====  End of delete poll option  ======*/

			/*============================================
				=            Show hide poll panel            =
				============================================*/

			$( document ).on(
				'click',
				'.bpolls-icon',
				function() {
					if ( $( '.quote-btn' ).length != 0 ) {
						  $( '.bg-type-input' ).val( '' );
						  $( '.bg-type-value' ).val( '' );
						  $( '#whats-new, #bppfa-whats-new' ).removeClass( 'quotesimg-bg-selected' );
						  $( '#whats-new, #bppfa-whats-new' ).removeClass( 'quotescolors-bg-selected' );
						  $( "#whats-new, #bppfa-whats-new" ).css( "background-image", '' );
						  $( "#whats-new, #bppfa-whats-new" ).css( "background", '' );
						  $( "#whats-new, #bppfa-whats-new" ).css( "color", '' );
						  $( '.bpquotes-selection' ).css( 'pointerEvents', 'auto' );
					}

					if ( $( '.bpchk-allow-checkin' ).length != 0  ) {
						if (typeof bpchk_public_js_obj !== 'undefined' ) {
							var data = {
								'action': 'bpchk_cancel_checkin'
							}
							$.ajax(
								{
									dataType: "JSON",
									url: bpchk_public_js_obj.ajaxurl,
									type: 'POST',
									data: data,
									success: function (response) {
										$( '.bpchk-checkin-temp-location' ).remove();
									},
									error: function(xhr, status, error) {
										console.error('Check-in cancel failed:', error);
									}
								}
							);
						}
						$( '#bpchk-autocomplete-place' ).val( '' );
						$( '#bpchk-checkin-place-lat' ).val( '' );
						$( '#bpchk-checkin-place-lng' ).val( '' );

						if ( typeof BPCHKPRO !== 'undefined' ) {
							BPCHKPRO.delete_cookie( 'bpchkpro_lat' );
							BPCHKPRO.delete_cookie( 'bpchkpro_lng' );
							BPCHKPRO.delete_cookie( 'bpchkpro_place' );
							BPCHKPRO.delete_cookie( 'add_place' );
						}
					}

					$( '.bpolls-polls-option-html' ).slideToggle( 500 );

					$( '.bpolls-datetimepicker' ).datetimepicker({
						minDate: new Date()
					});

					$(
						function() {
							$( '.bpolls-sortable' ).sortable(
								{
									handle: '.bpolls-sortable-handle'
								}
							);
							$( '.bpolls-sortable' ).disableSelection();
						}
					);
				}
			);

			/*=====  End of Show hide poll panel  ======*/

			/*==================================================================
				=            clear html and toggle on poll cancellation            =
				==================================================================*/

			$( document ).on(
				'click',
				'.bpolls-cancel',
				function() {
					$( "#aw-whats-new-reset" ).trigger( "click" );
					$( '.bpolls-input' ).each(
						function(){
							$( this ).val( '' );
						}
					);
					$( '.bpolls-polls-option-html' ).html( poll_html );
					$( '.bpolls-polls-option-html' ).slideUp( 500 );
					$( '.bpolls-sortable' ).sortable(
						{
							handle: '.bpolls-sortable-handle'
						}
					);
					$( '.bpolls-sortable' ).disableSelection();
				}
			);

			/*=====  End of clear html and toggle on poll cancellation  ======*/

			/*==================================================================
				=    Reset poll when BuddyPress/theme Cancel button is clicked    =
				==================================================================*/

			/**
			 * Helper function to reset poll form to initial state.
			 * Reusable for both Cancel Poll button and BuddyPress Cancel button.
			 */
			function bpollsResetPollForm() {
				$( '.bpolls-input' ).each( function() {
					$( this ).val( '' );
				});
				$( '.bpolls-polls-option-html' ).html( poll_html );
				if ( $( '.bpolls-polls-option-html' ).is( ':visible' ) ) {
					$( '.bpolls-polls-option-html' ).slideUp( 500 );
				}
				// Clear media previews.
				$( '#bpolls-image-preview' ).attr( 'src', '' );
				$( '#bpolls-attachment-url' ).val( '' );
				$( '.bpolls-image-upload' ).hide();
				$( '#bpolls-video-preview' ).attr( 'src', '' );
				$( '#bpolls-video-attachment-url' ).val( '' );
				$( '.bpolls-video-upload' ).hide();
				$( '#bpolls-audio-preview' ).attr( 'src', '' );
				$( '#bpolls-audio-attachment-url' ).val( '' );
				$( '.bpolls-audio-upload' ).hide();
				// Re-initialize sortable.
				$( '.bpolls-sortable' ).sortable({
					handle: '.bpolls-sortable-handle'
				});
				$( '.bpolls-sortable' ).disableSelection();
			}

			// Listen for BuddyPress/theme Cancel button clicks.
			// This handles the case where user clicks Cancel (not Cancel Poll).
			// Selectors cover: BuddyPress Legacy, Nouveau, BuddyX, BuddyBoss themes.
			$( document ).on(
				'click',
				'#whats-new-form button[type="reset"], #whats-new-form .activity-post-cancel, #aw-whats-new-reset',
				function() {
					// Small delay to let BuddyPress handle its own reset first.
					setTimeout( function() {
						bpollsResetPollForm();
					}, 100 );
				}
			);

			// Handle Cancel button clicks in BuddyX and similar themes.
			// The Cancel button is typically a sibling of Post Update button.
			$( document ).on( 'click', '#whats-new-form button', function() {
				var buttonText = $( this ).text().trim().toLowerCase();
				// Check if this is a Cancel button (not Cancel Poll or Post Update).
				if ( buttonText === 'cancel' && ! $( this ).hasClass( 'bpolls-cancel' ) ) {
					setTimeout( function() {
						bpollsResetPollForm();
					}, 100 );
				}
			});

			// Also listen for form reset event on the activity form.
			$( '#whats-new-form' ).on( 'reset', function() {
				setTimeout( function() {
					bpollsResetPollForm();
				}, 100 );
			});

			/*=====  End of Reset poll on BuddyPress Cancel  ======*/

			$( document ).on(
				'change',
				'input.bpolls_input_options',
				function() {

					var poll_option = [];
					$( "input.bpolls_input_options" ).each(
						function() {
							if ($( this ).val()) {
								poll_option.push( $( this ).val() );
							}
						}
					);
					var is_poll;
					if (poll_option.length !== 0) {
							is_poll = 'yes';
					} else {
						  is_poll = 'no'
					}

					var data = {
						'action': 'bpolls_set_poll_type_true',
						'poll_option': poll_option,
						'is_poll': is_poll,
						'ajax_nonce': bpolls_ajax_object.ajax_nonce
					};

					$.post(
						bpolls_ajax_object.ajax_url,
						data,
						function(response) {
							console.log( response );
						}
					).fail(function(xhr, status, error) {
						console.error('Poll type update failed:', error);
					});

				}
			);

			/*==========================================================
				=            solve glitch on post update submit            =
				==========================================================*/

			// Reset poll options after successful activity post.
			// Track when a post_update request is made.
			var pendingPostUpdate = false;

			$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
				// Check if this is a post_update action (BuddyPress activity post).
				// Handle both object data and string (serialized form) data.
				if ( originalOptions.data ) {
					if ( typeof originalOptions.data === 'object' &&
						 originalOptions.data.action === 'post_update' ) {
						pendingPostUpdate = true;
					} else if ( typeof originalOptions.data === 'string' &&
								originalOptions.data.indexOf( 'action=post_update' ) !== -1 ) {
						pendingPostUpdate = true;
					}
				}
			} );

			// Use ajaxComplete to handle the response.
			$( document ).ajaxComplete( function( event, xhr, settings ) {
				if ( ! pendingPostUpdate ) {
					return;
				}

				// Check if this response is for our post_update request.
				try {
					var response = xhr.responseJSON || JSON.parse( xhr.responseText );

					// Check for successful activity post.
					// BuddyPress can return success in different formats:
					// - { success: true } (standard WP JSON)
					// - { success: "true" } (string)
					// - { id: 123 } (activity ID indicates success)
					// - Responses with activity property
					// - { bp_activity_newest_activities: {...} } (BuddyPress activity stream response)
					var isSuccess = ( response && (
						response.success === true ||
						response.success === 'true' ||
						( response.id && typeof response.id === 'number' ) ||
						( response.activity && response.activity.id ) ||
						response.bp_activity_newest_activities
					) );

					if ( isSuccess ) {
						pendingPostUpdate = false;

						// Use setTimeout to let BuddyPress complete its own reset first.
						setTimeout( function() {
							// Reset poll options to initial state.
							$( '.bpolls-polls-option-html' ).html( poll_html );
							// Hide poll panel if visible.
							if ( $( '.bpolls-polls-option-html' ).is( ':visible' ) ) {
								$( '.bpolls-polls-option-html' ).slideUp( 300 );
							}
							// Clear media previews.
							$( '#bpolls-image-preview' ).attr( 'src', '' );
							$( '#bpolls-attachment-url' ).val( '' );
							$( '.bpolls-image-upload' ).hide();
							$( '#bpolls-video-preview' ).attr( 'src', '' );
							$( '#bpolls-video-attachment-url' ).val( '' );
							$( '.bpolls-video-upload' ).hide();
							$( '#bpolls-audio-preview' ).attr( 'src', '' );
							$( '#bpolls-audio-attachment-url' ).val( '' );
							$( '.bpolls-audio-upload' ).hide();
							// Trigger form reset to clear textarea.
							$( '#whats-new' ).val( '' );
							$( '#whats-new-form' ).trigger( 'reset' );
							// Also click the reset button if available.
							if ( $( '#aw-whats-new-reset' ).length ) {
								$( '#aw-whats-new-reset' ).trigger( 'click' );
							}
						}, 200 );
					}
				} catch ( e ) {
					// JSON parse error - do nothing.
				}
			} );

			/*=====  End of solve glitch on post update submit  ======*/

			/*======================================================
				=            Ajax request to save poll vote            =
				======================================================*/

			$( document ).on(
				'click',
				'.bpolls-vote-submit',
				function( e ) {
					e.preventDefault();
					var submit_event      = $( this );
					var submit_event_text = $( this ).html();
					var s_array           = $( this ).closest( '.bpolls-vote-submit-form' ).serializeArray();
					var len               = s_array.length;
					var dataObj           = {};
					for (var i = 0; i < len; i++) {
						  dataObj[s_array[i].name] = s_array[i].value;
					}
					var bpoll_activity_id = dataObj['bpoll_activity_id'];

					if (dataObj['bpolls_vote_optn[]'] == undefined) {
						 submit_event.html( bpolls_ajax_object.optn_empty_text + ' <i class="fa fa-exclamation-triangle"></i>' );
						 return;
					} else {
						submit_event.html( submit_event_text );
					}

					submit_event.html( bpolls_ajax_object.submit_text + ' <i class="fa fa-refresh fa-spin"></i>' );
					var poll_data = $( this ).closest( '.bpolls-vote-submit-form' ).serialize();

					var data = {
						'action': 'bpolls_save_poll_vote',
						'poll_data': poll_data,
						'ajax_nonce': bpolls_ajax_object.ajax_nonce
					};

					$.post(
						bpolls_ajax_object.ajax_url,
						data,
						function(response) {

							var res = JSON.parse( response );
							if (res.bpolls_thankyou_feedback != '' ) {
								// Use .text() for safety - prevents XSS from server response.
								submit_event.after( $( '<span class="bpolls-feedback-message">' ).text( res.bpolls_thankyou_feedback ) );
							}

							$.each(
								res,
								function(i, item) {

									var input_obj = submit_event.closest( '.bpolls-vote-submit-form' ).find( "#" + i );
									$( input_obj ).parents( '.bpolls-item' ).find( '.bpolls-item-width' ).animate(
										{
											width: item.vote_percent
										},
										500
									);

									$( input_obj ).parents( '.bpolls-item' ).find( '.bpolls-percent' ).text( item.vote_percent );
									$( input_obj ).parents( '.bpolls-check-radio-div' ).siblings( '.bpolls-votes' ).html( item.bpolls_votes_txt );
									$( input_obj ).parents().parents( '.bpolls-item' ).find( '.bpolls-result-votes' ).html( item.bpolls_votes_content );

								}
							);
							$( '#activity-' + bpoll_activity_id + ' .bpolls-item input' ).hide();
							$( '#activity-' + bpoll_activity_id + ' .bpolls-add-user-item' ).remove();
							submit_event.remove();
						}
					).fail(function(xhr, status, error) {
						console.error('Vote submission failed:', error);
						alert(bpolls_ajax_object.vote_error_msg || 'Vote submission failed. Please try again.');
					});
				}
			);

			/*=====  End of Ajax request to save poll vote  ======*/
			// Hide other panels when poll icon is clicked (using delegation to avoid rebinding).
			$( document ).on( 'click', '.bpolls-icon', function() {
				jQuery( '.bpquotes-bg-selection-div' ).hide();
				jQuery( '.bp-checkin-panel' ).hide();
			});

			$( document ).on(
				'click',
				'.bp-polls-view-all',
				function( e ) {
					e.preventDefault();
					var data = {
						'action': 'bpolls_activity_all_voters',
						'activity_id': $( this ).data( 'activity-id' ),
						'option_id': $( this ).data( 'option-id' ),
						'ajax_nonce': bpolls_ajax_object.ajax_nonce
					};

					$.post(
						bpolls_ajax_object.ajax_url,
						data,
						function(response) {
							// Only append if response is valid and successful.
							if ( response && response.success && response.data ) {
								$( 'body' ).append( response.data );
							}
						}
					).fail(function(xhr, status, error) {
						console.error('Failed to load voters:', error);
					});

				}
			);

			$( document ).on(
				'click',
				'.bpolls-modal-close.bpolls-modal-close-icon',
				function( e ) {
					$( '.bpolls-icon-dialog.bpolls-user-votes-dialog' ).remove();
				}
			);

			/* Add User Option */
			$( document ).on(
				'keydown',
				'.bpoll-add-user-option',
				function(e){
					if ( e.keyCode == 13 && $( this ).val() == '' ) {
						  e.preventDefault();
						  var bpoll_activity_id = $( this ).data( 'activity-id' );
						  $( '#activity-' + bpoll_activity_id + ' .bpolls-add-option-error' ).show();
						setTimeout(
							function() {
								$( '#activity-' + bpoll_activity_id + ' .bpolls-add-option-error' ).hide( 500 );
							},
							5000
						);
					}
					if ( e.keyCode == 13 && $( this ).val() != '' ) {
						 e.preventDefault();
						 var max_options       = bpolls_ajax_object.polls_option_lmit;
						 var user_option       = $( this ).val();
						 var bpoll_activity_id = $( this ).data( 'activity-id' );
						 var bpoll_user_id     = $( this ).data( 'user-id' );
						 var user_count        = 1;
						$( '#activity-' + bpoll_activity_id + ' .bpolls-item .bpolls-delete-user-option' ).each(
							function() {

								if ( bpoll_user_id == $( this ).data( 'user-id' )) {
									user_count++;
								}
							}
						);
						if ( user_count > max_options ) {
							console.log( max_options + " ==" + user_count );
							$( '.bpolls-icon-dialog' ).addClass( 'is-visible' );

						} else {

							var data       = {
								'action': 'bpolls_activity_add_user_option',
								'activity_id': bpoll_activity_id,
								'user_option': user_option,
								'ajax_nonce': bpolls_ajax_object.ajax_nonce
							};
							var add_option = $( this ).parent();
							$.post(
								bpolls_ajax_object.ajax_url,
								data,
								function(response) {
									response = $.parseJSON( response );
									if (response.add_poll_option !== "" ) {
										$( response.add_poll_option ).insertBefore( add_option );
										if (bpolls_ajax_object.poll_revoting != 'yes') {
											 $( '#activity-' + bpoll_activity_id + ' .bpolls-vote-submit' ).trigger( 'click' );
										}
									}
								}
							).fail(function(xhr, status, error) {
								console.error('Failed to add option:', error);
							});
							$( this ).val( '' );
						}
					}
				}
			);

			$( document ).on(
				'click',
				'.bpoll-add-option',
				function(e){
					e.preventDefault();
					var max_options       = bpolls_ajax_object.polls_option_lmit;
					var bpoll_activity_id = $( this ).data( 'activity-id' );
					var user_option       = $( '#activity-' + bpoll_activity_id + ' .bpoll-add-user-option' ).val();

					if (user_option != '' ) {
						  var bpoll_user_id = $( this ).data( 'user-id' );

						  var user_count = 1;
						$( '#activity-' + bpoll_activity_id + ' .bpolls-item .bpolls-delete-user-option' ).each(
							function() {

								if ( bpoll_user_id == $( this ).data( 'user-id' )) {
									user_count++;
								}
							}
						);

						if ( user_count > max_options ) {
							  console.log( max_options + " ==" + user_count );
							  $( '.bpolls-icon-dialog' ).addClass( 'is-visible' );

						} else {
							var data       = {
								'action': 'bpolls_activity_add_user_option',
								'activity_id': bpoll_activity_id,
								'user_option': user_option,
								'ajax_nonce': bpolls_ajax_object.ajax_nonce
							};
							var add_option = $( this ).parent();
							$.post(
								bpolls_ajax_object.ajax_url,
								data,
								function(response) {
									response = $.parseJSON( response );
									if (response.add_poll_option !== "" ) {
										$( response.add_poll_option ).insertBefore( add_option );
										if (bpolls_ajax_object.poll_revoting != 'yes') {
											 $( '#activity-' + bpoll_activity_id + ' .bpolls-vote-submit' ).trigger( 'click' );
										}
									}
								}
							).fail(function(xhr, status, error) {
								console.error('Failed to add option:', error);
							});
							$( '#activity-' + bpoll_activity_id + ' .bpoll-add-user-option' ).val();
						}
					} else {
						 $( '#activity-' + bpoll_activity_id + ' .bpolls-add-option-error' ).show();
						setTimeout(
							function() {
								$( '#activity-' + bpoll_activity_id + ' .bpolls-add-option-error' ).hide( 500 );
							},
							5000
						);
					}

				}
			);

			/* Delete user Option */
			$( document ).on(
				'click',
				'.bpolls-delete-user-option',
				function(e){
					e.preventDefault();
					if (confirm( bpolls_ajax_object.delete_polls_msg ) == true) {
						  var user_option = $( this ).data( 'option' );;
						  var bpoll_activity_id = $( this ).data( 'activity-id' );
						  var data              = {
								'action': 'bpolls_activity_delete_user_option',
								'activity_id': bpoll_activity_id,
								'user_option': user_option,
								'ajax_nonce': bpolls_ajax_object.ajax_nonce
						};
						  var submit_event      = $( '#activity-' + bpoll_activity_id + ' .bpolls-vote-submit' );
						  $( this ).parent().remove();
						$.post(
							bpolls_ajax_object.ajax_url,
							data,
							function(response) {
								console.log( response );
								var res = JSON.parse( response );

								$.each(
									res,
									function(i, item) {

										var input_obj = submit_event.closest( '.bpolls-vote-submit-form' ).find( "#" + i );
										$( input_obj ).parents( '.bpolls-item' ).find( '.bpolls-item-width' ).animate(
											{
												width: item.vote_percent
											},
											500
										);

										  $( input_obj ).parents( '.bpolls-item' ).find( '.bpolls-percent' ).text( item.vote_percent );
										  $( input_obj ).parents( '.bpolls-check-radio-div' ).siblings( '.bpolls-votes' ).html( item.bpolls_votes_txt );
										  $( input_obj ).parents().parents( '.bpolls-item' ).find( '.bpolls-result-votes' ).html( item.bpolls_votes_content );

									}
								);
							}
						).fail(function(xhr, status, error) {
							console.error('Failed to delete option:', error);
						});
					}

				}
			);
		}
	);
})( jQuery );

(function($) {

	$( document ).ready(
		function() {
			var file_frame_image, file_frame_video, file_frame_audio;

			// Image attachment handler
			$( document ).on(
				'click',
				'#bpolls-attach-image',
				function(event) {
					event.preventDefault();
					if (file_frame_image) {
						file_frame_image.open();
						return;
					}

					// Build library config - only add author filter if restrict_media_library is enabled
					var imageLibraryConfig = { type: 'image' };
					if ( bpolls_ajax_object.restrict_media_library === 'yes' ) {
						imageLibraryConfig.author = bpolls_ajax_object.poll_user;
					}

					file_frame_image = wp.media.frames.file_frame = wp.media(
						{
							title: bpolls_ajax_object.add_image_title || 'Select Image',
							button: {
								text: bpolls_ajax_object.add_image_button || 'Use Image',
							},
							multiple: false,
							library: imageLibraryConfig
						}
					);

					file_frame_image.on(
						'select',
						function() {
							var attachment = file_frame_image.state().get( 'selection' ).first().toJSON();

							// Check file size.
							if ( bpolls_ajax_object.max_upload_size && attachment.filesizeInBytes > bpolls_ajax_object.max_upload_size ) {
								alert( bpolls_ajax_object.file_too_large );
								return;
							}

							$( '#bpolls-image-preview' ).attr( 'src', attachment.url );
							$( '#bpolls-attachment-url' ).val( attachment.url );
							$( '.bpolls-image-upload' ).show();

							if (attachment.url) {
								var data = {
									'action': 'bpolls_save_image',
									'image_url': attachment.url,
									'ajax_nonce': bpolls_ajax_object.ajax_nonce
								};
								$.post(
									bpolls_ajax_object.ajax_url,
									data,
									function(response) {}
								).fail(function(xhr, status, error) {
									console.error('Failed to save image:', error);
								});
							}
						}
					);
					file_frame_image.open();
					$( '.media-router button:first-child' ).click();
				}
			);

			// Video attachment handler
			$( document ).on(
				'click',
				'#bpolls-attach-video',
				function(event) {
					event.preventDefault();
					if (file_frame_video) {
						file_frame_video.open();
						return;
					}

					// Build library config - only add author filter if restrict_media_library is enabled
					var videoLibraryConfig = { type: 'video' };
					if ( bpolls_ajax_object.restrict_media_library === 'yes' ) {
						videoLibraryConfig.author = bpolls_ajax_object.poll_user;
					}

					file_frame_video = wp.media.frames.file_frame = wp.media(
						{
							title: bpolls_ajax_object.add_video_title || 'Select Video',
							button: {
								text: bpolls_ajax_object.add_video_button || 'Use Video',
							},
							multiple: false,
							library: videoLibraryConfig
						}
					);

					file_frame_video.on(
						'select',
						function() {
							var attachment = file_frame_video.state().get( 'selection' ).first().toJSON();

							// Check file size.
							if ( bpolls_ajax_object.max_upload_size && attachment.filesizeInBytes > bpolls_ajax_object.max_upload_size ) {
								alert( bpolls_ajax_object.file_too_large );
								return;
							}

							$( '#bpolls-video-preview' ).attr( 'src', attachment.url );
							$( '#bpolls-video-attachment-url' ).val( attachment.url );
							$( '.bpolls-video-upload' ).show();
						}
					);
					file_frame_video.open();
					$( '.media-router button:first-child' ).click();
				}
			);

			// Audio attachment handler
			$( document ).on(
				'click',
				'#bpolls-attach-audio',
				function(event) {
					event.preventDefault();
					if (file_frame_audio) {
						file_frame_audio.open();
						return;
					}

					// Build library config - only add author filter if restrict_media_library is enabled
					var audioLibraryConfig = { type: 'audio' };
					if ( bpolls_ajax_object.restrict_media_library === 'yes' ) {
						audioLibraryConfig.author = bpolls_ajax_object.poll_user;
					}

					file_frame_audio = wp.media.frames.file_frame = wp.media(
						{
							title: bpolls_ajax_object.add_audio_title || 'Select Audio',
							button: {
								text: bpolls_ajax_object.add_audio_button || 'Use Audio',
							},
							multiple: false,
							library: audioLibraryConfig
						}
					);

					file_frame_audio.on(
						'select',
						function() {
							var attachment = file_frame_audio.state().get( 'selection' ).first().toJSON();

							// Check file size.
							if ( bpolls_ajax_object.max_upload_size && attachment.filesizeInBytes > bpolls_ajax_object.max_upload_size ) {
								alert( bpolls_ajax_object.file_too_large );
								return;
							}

							$( '#bpolls-audio-preview' ).attr( 'src', attachment.url );
							$( '#bpolls-audio-attachment-url' ).val( attachment.url );
							$( '.bpolls-audio-upload' ).show();
						}
					);
					file_frame_audio.open();
					$( '.media-router button:first-child' ).click();
				}
			);

			// Remove media handlers
			$( document ).on( 'click', '.bpolls-remove-media', function(e) {
				e.preventDefault();
				var mediaType = $( this ).data( 'media-type' );

				if ( mediaType === 'image' ) {
					$( '#bpolls-image-preview' ).attr( 'src', '' );
					$( '#bpolls-attachment-url' ).val( '' );
					$( '.bpolls-image-upload' ).hide();
				} else if ( mediaType === 'video' ) {
					var videoEl = $( '#bpolls-video-preview' )[0];
					if ( videoEl ) {
						videoEl.pause();
					}
					$( '#bpolls-video-preview' ).attr( 'src', '' );
					$( '#bpolls-video-attachment-url' ).val( '' );
					$( '.bpolls-video-upload' ).hide();
				} else if ( mediaType === 'audio' ) {
					var audioEl = $( '#bpolls-audio-preview' )[0];
					if ( audioEl ) {
						audioEl.pause();
					}
					$( '#bpolls-audio-preview' ).attr( 'src', '' );
					$( '#bpolls-audio-attachment-url' ).val( '' );
					$( '.bpolls-audio-upload' ).hide();
				}
			});

			// URL input preview handlers
			$( document ).on( 'click', '.bpolls-url-preview-btn', function(e) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.bpolls-url-input-wrap' );
				var mediaType = $wrap.data( 'media-type' );
				var url = $wrap.find( '.bpolls-media-url-input' ).val().trim();

				if ( !url ) {
					alert( 'Please enter a URL' );
					return;
				}

				if ( mediaType === 'image' ) {
					$( '#bpolls-image-preview' ).attr( 'src', url );
					$( '#bpolls-attachment-url' ).val( url );
					$( '.bpolls-image-upload' ).show();

					// Save to temp option as fallback (matching media library behavior).
					if ( url && typeof bpolls_ajax_object !== 'undefined' ) {
						$.post( bpolls_ajax_object.ajax_url, {
							'action': 'bpolls_save_image',
							'image_url': url,
							'ajax_nonce': bpolls_ajax_object.ajax_nonce
						});
					}
				} else if ( mediaType === 'video' ) {
					var videoEl = $( '#bpolls-video-preview' )[0];
					$( '#bpolls-video-preview' ).attr( 'src', url );
					$( '#bpolls-video-attachment-url' ).val( url );
					$( '.bpolls-video-upload' ).show();
					// Reload video element to play new source.
					if ( videoEl ) {
						videoEl.load();
					}

					// Save to temp option as fallback (matching media library behavior).
					if ( url && typeof bpolls_ajax_object !== 'undefined' ) {
						$.post( bpolls_ajax_object.ajax_url, {
							'action': 'bpolls_save_video',
							'video_url': url,
							'ajax_nonce': bpolls_ajax_object.ajax_nonce
						});
					}
				} else if ( mediaType === 'audio' ) {
					var audioEl = $( '#bpolls-audio-preview' )[0];
					$( '#bpolls-audio-preview' ).attr( 'src', url );
					$( '#bpolls-audio-attachment-url' ).val( url );
					$( '.bpolls-audio-upload' ).show();
					// Reload audio element to play new source.
					if ( audioEl ) {
						audioEl.load();
					}

					// Save to temp option as fallback (matching media library behavior).
					if ( url && typeof bpolls_ajax_object !== 'undefined' ) {
						$.post( bpolls_ajax_object.ajax_url, {
							'action': 'bpolls_save_audio',
							'audio_url': url,
							'ajax_nonce': bpolls_ajax_object.ajax_nonce
						});
					}
				}
			});

			/*==============================================
			=            Character Counter for Activity Polls            =
			==============================================*/

			/**
			 * Initialize character counter for an input element
			 * @param {HTMLElement} input - The input element to add counter to
			 */
			function initCharCounter( input ) {
				var $input = $( input );
				var limit = parseInt( $input.data( 'char-limit' ), 10 );

				if ( ! limit || limit <= 0 ) {
					return;
				}

				// Check if counter already exists
				if ( $input.next( '.bpolls-char-counter' ).length > 0 ) {
					return;
				}

				// Create counter element
				var $counter = $( '<div class="bpolls-char-counter"></div>' );
				$input.after( $counter );

				// Update counter
				updateCharCounter( $input, $counter, limit );

				// Bind input event
				$input.on( 'input keyup', function() {
					updateCharCounter( $input, $counter, limit );
				});
			}

			/**
			 * Update character counter display
			 */
			function updateCharCounter( $input, $counter, limit ) {
				var current = $input.val().length;
				var remaining = limit - current;
				var percentage = ( current / limit ) * 100;
				var text = current + ' / ' + limit;

				if ( typeof bpolls_ajax_object.characters_text !== 'undefined' ) {
					text += ' ' + bpolls_ajax_object.characters_text;
				}

				$counter.text( text );

				// Remove existing state classes
				$counter.removeClass( 'warning danger' );

				// Add state class based on percentage
				if ( percentage >= 100 ) {
					$counter.addClass( 'danger' );
				} else if ( percentage >= 80 ) {
					$counter.addClass( 'warning' );
				}
			}

			// Initialize counters for existing poll option inputs
			$( '.bpolls_input_options[data-char-limit]' ).each( function() {
				initCharCounter( this );
			});

			// Initialize counter for thank you message
			$( '#bpolls-thankyou-feedback[data-char-limit]' ).each( function() {
				initCharCounter( this );
			});

			// Initialize counters for dynamically added inputs (via event delegation)
			$( document ).on( 'focus', '.bpolls_input_options[data-char-limit]', function() {
				initCharCounter( this );
			});

			$( document ).on( 'focus', '#bpolls-thankyou-feedback[data-char-limit]', function() {
				initCharCounter( this );
			});

			/*=====  End of Character Counter for Activity Polls  ======*/
		}
	);
})( jQuery );
