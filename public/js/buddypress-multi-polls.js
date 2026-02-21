if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}
(function($) {
	'use strict';
	
	$( document ).ready(function() { 
	
		$( document ).on(
			'submit',
			'.wbpoll-form',
			function (e) {
				e.preventDefault();

				var $element = $( this );

				let defaultConfig = {
					// class of the parent element where the error/success class is added
					classTo: 'wbpoll_extra_field_wrap',
					errorClass: 'has-danger',
					successClass: 'has-success',
					// class of the parent element where error text element is appended
					errorTextParent: 'wbpoll_extra_field_wrap',
					// type of element to create for the error text
					errorTextTag: 'p',
					// class of the error text element
					errorTextClass: 'text-help'
				};

				var pristine = new Pristine( $element[0], defaultConfig );
				var valid    = pristine.validate(); // returns true or false

				if ( ! valid) {
					e.preventDefault();
				} else {
					wbpoll_formsubmit( $element, $ );
				}

			}
		);

		function wbpoll_formsubmit($element, $) {
			var $submit_btn = $element.find( '.wbpoll_vote_btn' );
			var wrapper     = $element.closest( '.wbpoll_wrapper' );
			var $_this_busy = Number( $submit_btn.attr( 'data-busy' ) );

			var poll_id    = $submit_btn.attr( 'data-post-id' );
			var reference  = $submit_btn.attr( 'data-reference' );
			var chart_type = $submit_btn.attr( 'data-charttype' );
			var security   = $submit_btn.attr( 'data-security' );

			var user_answer = $element.find( 'input.wbpoll_single_answer:checked' ).serialize();

			if ($_this_busy === 0) {

				$submit_btn.attr( 'data-busy', '1' );
				$submit_btn.prop( 'disabled', true );

				wrapper.find( '.wbvoteajaximage' ).removeClass( 'wbvoteajaximagecustom' );

				var user_answer_trim = user_answer.trim();

				if (typeof user_answer !== 'undefined' && user_answer_trim.length !== 0) { // if one answer given
					wrapper.find( '.wbpoll-qresponse' ).hide();

					$.ajax(
						{
							type: 'post',
							dataType: 'json',
							url: wbpollpublic.ajaxurl,
							data: $element.serialize() + '&user_answer=' + $.base64.btoa( user_answer ),
							success: function (data, textStatus, XMLHttpRequest) {
								if (Number( data.error ) === 0) {
									try { //the data for all graphs
										if (data.show_result === 1) {
											wrapper.append( data.html );
										}

										wrapper.find( '.wbpoll-qresponse' ).show();
										wrapper.find( '.wbpoll-qresponse' ).removeClass( 'wbpoll-qresponse-alert wbpoll-qresponse-error wbpoll-qresponse-success' );
										wrapper.find( '.wbpoll-qresponse' ).addClass( 'wbpoll-qresponse-success' );
										// Use .text() for safety - prevents XSS from server response.
										wrapper.find( '.wbpoll-qresponse' ).empty().append( $( '<p>' ).text( data.text ) );

										wrapper.find( '.wbpoll_answer_wrapper' ).hide();
									} catch (e) {

									}

								}// end of if not voted
								else {
									wrapper.find( '.wbpoll-qresponse' ).show();
									wrapper.find( '.wbpoll-qresponse' ).removeClass( 'wbpoll-qresponse-alert wbpoll-qresponse-error wbpoll-qresponse-success' );
									wrapper.find( '.wbpoll-qresponse' ).addClass( 'wbpoll-qresponse-error' );
									// Use .text() for safety - prevents XSS from server response.
									wrapper.find( '.wbpoll-qresponse' ).empty().append( $( '<p>' ).text( data.text ) );
								}

								$submit_btn.attr( 'data-busy', '0' );
								$submit_btn.prop( 'disabled', false );
								wrapper.find( '.wbvoteajaximage' ).addClass( 'wbvoteajaximagecustom' );
							}//end of success
						}
					)//end of ajax

				} else {

					//if no answer given
					$submit_btn.show();
					$submit_btn.attr( 'data-busy', 0 );
					$submit_btn.prop( 'disabled', false );
					wrapper.find( '.wbvoteajaximage' ).addClass( 'wbvoteajaximagecustom' );

					var error_result = wbpollpublic.no_answer_error;

					wrapper.find( '.wbpoll-qresponse' ).show();
					wrapper.find( '.wbpoll-qresponse' ).removeClass( 'wbpoll-qresponse-alert wbpoll-qresponse-error wbpoll-qresponse-success' );
					wrapper.find( '.wbpoll-qresponse' ).addClass( 'wbpoll-qresponse-alert' );
					// Use .text() for safety.
					wrapper.find( '.wbpoll-qresponse' ).text( error_result );
				}
			}// end of this data busy
		}


		// Initialize GLightbox for poll media (images, videos, audio).
		if (typeof GLightbox !== 'undefined') {
			var pollLightbox = GLightbox({
				selector: '.glightbox',
				touchNavigation: true,
				loop: false,
				autoplayVideos: true,
				openEffect: 'zoom',
				closeEffect: 'fade',
				cssEfects: {
					fade: { in: 'fadeIn', out: 'fadeOut' },
					zoom: { in: 'zoomIn', out: 'zoomOut' }
				}
			});

			// Reinitialize on AJAX content load.
			$(document).ajaxComplete(function() {
				if (pollLightbox) {
					pollLightbox.reload();
				}
			});
		}

		// Prevent click on media link from triggering radio/checkbox selection.
		$(document).on('click', '.wbpoll-media-link', function(e) {
			e.stopPropagation();
		});


	
		$( '.load-more' ).on("click",	function() {
			var dataid = $( this ).data( 'id' );
			$( '.user-profile-image-modal-' + dataid ).show();
		});
		$( '.close-profiles' ).on("click", function() {
			var dataid = $( this ).data( 'id' );
			$( '.user-profile-image-modal-' + dataid ).hide();
		});
		//text additional field

		$('#text_field').on('click', function(){
			$('#type_text').show();
		});

		//image additional field

		$('#image_field').on('click', function(){
			$('#type_image').show();
		});

		//video additional field

		$('#video_field').on('click', function(){
			$('#type_video').show();
		});

		//audio additional field

		$('#audio_field').on('click', function(){
			$('#type_audio').show();
		});

		//html additional field

		$('#html_field').on('click', function(){
			$('#type_html').show();
		});

		$('#post_text_field').on('click', function (event) {
			event.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();

			// Disable button and show loading state.
			$btn.prop('disabled', true).text(wbpollpublic.saving_message || 'Saving...');

			const answer = $('[name="_wbpoll_answer[]"]').map(function () {
				return $(this).val();
			}).get();
			const answertype = $('[name="_wbpoll_answer_extra[][type]"]').map(function () {
				return $(this).val();
			}).get();
			const post_id = $('#post_id').val();

			$.ajax({
				url: wbpollpublic.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wbpoll_additional_field',
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					post_id: post_id,
					security: wbpollpublic.nonce,
				},
				success: function (response) {
					location.reload();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$btn.prop('disabled', false).text(originalText);
					alert(wbpollpublic.error_message || 'An error occurred. Please try again.');
				}
			});
		});

		$('#post_image_field').on('click', function (event) {
			event.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();

			$btn.prop('disabled', true).text(wbpollpublic.saving_message || 'Saving...');

			const answer = $('[name="_wbpoll_answer[]"]').map(function () {
				return $(this).val();
			}).get();
			const answertype = $('[name="_wbpoll_answer_extra[][type]"]').map(function () {
				return $(this).val();
			}).get();

			const full_size_image_answer = $('[name="_wbpoll_full_size_image_answer[]"]').map(function () {
				return $(this).val();
			}).get();

			const post_id = $('#post_id').val();

			$.ajax({
				url: wbpollpublic.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wbpoll_additional_field_image',
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					_wbpoll_full_size_image_answer: full_size_image_answer,
					post_id: post_id,
					security: wbpollpublic.nonce,
				},
				success: function (response) {
					location.reload();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$btn.prop('disabled', false).text(originalText);
					alert(wbpollpublic.error_message || 'An error occurred. Please try again.');
				}
			});
		});
		
		$('#post_video_field').on('click', function (event) {
			event.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();

			$btn.prop('disabled', true).text(wbpollpublic.saving_message || 'Saving...');

			const answer = $('[name="_wbpoll_answer[]"]').map(function () {
				return $(this).val();
			}).get();
			const answertype = $('[name="_wbpoll_answer_extra[][type]"]').map(function () {
				return $(this).val();
			}).get();

			const video_answer_url = $('[name="_wbpoll_video_answer_url[]"]').map(function () {
				return $(this).val();
			}).get();
			const video_import_info = $('input[name="_wbpoll_video_import_info[]"]:checked').map(function () {
				return $(this).val();
			}).get();

			const post_id = $('#post_id').val();

			$.ajax({
				url: wbpollpublic.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wbpoll_additional_field_video',
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					_wbpoll_video_answer_url: video_answer_url,
					_wbpoll_video_import_info: video_import_info,
					post_id: post_id,
					security: wbpollpublic.nonce,
				},
				success: function (response) {
					location.reload();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$btn.prop('disabled', false).text(originalText);
					alert(wbpollpublic.error_message || 'An error occurred. Please try again.');
				}
			});
		});
	
		$('#post_audio_field').on('click', function (event) {
			event.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();

			$btn.prop('disabled', true).text(wbpollpublic.saving_message || 'Saving...');

			const answer = $('[name="_wbpoll_answer[]"]').map(function () {
				return $(this).val();
			}).get();
			const answertype = $('[name="_wbpoll_answer_extra[][type]"]').map(function () {
				return $(this).val();
			}).get();

			const audio_answer_url = $('[name="_wbpoll_audio_answer_url[]"]').map(function () {
				return $(this).val();
			}).get();
			const audio_import_info = $('input[name="_wbpoll_audio_import_info[]"]:checked').map(function() {
				return $(this).val();
			}).get();

			const post_id = $('#post_id').val();

			$.ajax({
				url: wbpollpublic.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wbpoll_additional_field_audio',
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					_wbpoll_audio_answer_url: audio_answer_url,
					_wbpoll_audio_import_info: audio_import_info,
					post_id: post_id,
					security: wbpollpublic.nonce,
				},
				success: function (response) {
					location.reload();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$btn.prop('disabled', false).text(originalText);
					alert(wbpollpublic.error_message || 'An error occurred. Please try again.');
				}
			});
		});
	
	
		$('#post_html_field').on('click', function (event) {
			event.preventDefault();
			var $btn = $(this);
			var originalText = $btn.text();

			$btn.prop('disabled', true).text(wbpollpublic.saving_message || 'Saving...');

			const answer = $('[name="_wbpoll_answer[]"]').map(function () {
				return $(this).val();
			}).get();
			const answertype = $('[name="_wbpoll_answer_extra[][type]"]').map(function () {
				return $(this).val();
			}).get();
			const html_answer = $('[name="_wbpoll_html_answer[]"]').map(function () {
				return $(this).val();
			}).get();

			const post_id = $('#post_id').val();

			$.ajax({
				url: wbpollpublic.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wbpoll_additional_field_html',
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					_wbpoll_html_answer: html_answer,
					post_id: post_id,
					security: wbpollpublic.nonce,
				},
				success: function (response) {
					location.reload();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					$btn.prop('disabled', false).text(originalText);
					alert(wbpollpublic.error_message || 'An error occurred. Please try again.');
				}
			});
		});

	});
})( jQuery );