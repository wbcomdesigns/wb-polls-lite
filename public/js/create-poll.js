if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}
(function( $ ) {
	'use strict';
	$( document ).ready( function() {
		if ( $('textarea.wbpoll_html_answer_textarea.tiny').length >= 1 && typeof tinymce !== 'undefined' ) {
			tinymce.init({
				selector: 'textarea.wbpoll_html_answer_textarea.tiny',
				menubar: false,
				max_height: 500,
				max_width: 800,
				min_height: 200,
				min_width: 800,
				toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
			});
		}
		
		if ( $('input[name="_wbpoll_never_expire"]:checked').val() == 1 ) {
			$('.wbpoll_show_date, .wbpoll_result_after_expires').hide();
		}
		$('input[name="_wbpoll_never_expire"]').on('change', function (e) {			
			if ($(this).val() == 1) {
				$('.wbpoll_show_date, .wbpoll_result_after_expires').hide();				
				$("input[name=_wbpoll_show_result_before_expire][value='0']").prop("checked",true);
			} else {
				$('.wbpoll_show_date, .wbpoll_result_after_expires').show();
			}
		});
						
		jQuery('#poll_type').on('change', function (e) {
			e.preventDefault();
			jQuery('#error_type').css('display', 'none');
			var type = jQuery(this).val();
			if (type == 'default') {
				jQuery('#addtitonal_option').show();
				jQuery('#type_text').show();
				jQuery('#type_image').hide();
				jQuery('div#type_image input#wbpoll_answer').val('');
				jQuery('div#type_image input#wbpoll_image_answer_url').val('');
				jQuery('#type_video').hide();
				jQuery('div#type_video input#wbpoll_answer').val('');
				jQuery('div#type_video input#wbpoll_video_answer_url').val('');
				jQuery('#type_audio').hide();
				jQuery('div#type_audio input#wbpoll_answer').val('');
				jQuery('div#type_audio input#wbpoll_audio_answer_url').val('');
				jQuery('#type_html').hide();
				jQuery('div#type_html input#wbpoll_answer').val('');
				jQuery('div#type_html #wbpoll_html_answer_textarea').val('');
			} else if (type == 'image') {
				jQuery('#addtitonal_option').hide();
				jQuery('#type_image').show();
				jQuery('#type_text').hide();
				jQuery('div#type_text input#wbpoll_answer').val('');
				jQuery('#type_video').hide();
				jQuery('div#type_video input#wbpoll_answer').val('');
				jQuery('div#type_video input#wbpoll_video_answer_url').val('');
				jQuery('#type_audio').hide();
				jQuery('div#type_audio input#wbpoll_answer').val('');
				jQuery('div#type_audio input#wbpoll_audio_answer_url').val('');
				jQuery('#type_html').hide();
				jQuery('div#type_html input#wbpoll_answer').val('');
				jQuery('div#type_html #wbpoll_html_answer_textarea').val('');
			} else if (type == 'video') {
				jQuery('#addtitonal_option').hide();
				jQuery('#type_video').show();
				jQuery('#type_image').hide();
				jQuery('div#type_image input#wbpoll_answer').val('');
				jQuery('div#type_image input#wbpoll_image_answer_url').val('');
				jQuery('#type_text').hide();
				jQuery('div#type_text input#wbpoll_answer').val('');
				jQuery('#type_audio').hide();
				jQuery('div#type_audio input#wbpoll_answer').val('');
				jQuery('div#type_audio input#wbpoll_audio_answer_url').val('');
				jQuery('#type_html').hide();
				jQuery('div#type_html input#wbpoll_answer').val('');
				jQuery('div#type_html #wbpoll_html_answer_textarea').val('');
			} else if (type == 'audio') {
				jQuery('#addtitonal_option').hide();
				jQuery('#type_audio').show();
				jQuery('#type_video').hide();
				jQuery('div#type_video input#wbpoll_answer').val('');
				jQuery('div#type_video input#wbpoll_video_answer_url').val('');
				jQuery('#type_image').hide();
				jQuery('div#type_image input#wbpoll_answer').val('');
				jQuery('div#type_image input#wbpoll_image_answer_url').val('');
				jQuery('#type_text').hide();
				jQuery('div#type_text input#wbpoll_answer').val('');
				jQuery('#type_html').hide();
				jQuery('div#type_html input#wbpoll_answer').val('');
				jQuery('div#type_html #wbpoll_html_answer_textarea').val('');
			} else if (type == 'html') {
				jQuery('#addtitonal_option').hide();
				jQuery('#type_html').show();
				jQuery('#type_video').hide();
				jQuery('div#type_video input#wbpoll_answer').val('');
				jQuery('div#type_video input#wbpoll_video_answer_url').val('');
				jQuery('#type_image').hide();
				jQuery('div#type_image input#wbpoll_answer').val('');
				jQuery('div#type_image input#wbpoll_image_answer_url').val('');
				jQuery('#type_text').hide();
				jQuery('div#type_text input#wbpoll_answer').val('');
				jQuery('#type_audio').hide();
				jQuery('div#type_audio input#wbpoll_answer').val('');
				jQuery('div#type_audio input#wbpoll_audio_answer_url').val('');

				if ( typeof tinymce !== 'undefined' ) {
					tinymce.init({
						selector: 'textarea.wbpoll_html_answer_textarea.tiny',
						menubar: false,
						max_height: 500,
						max_width: 800,
						min_height: 200,
						min_width: 800,
						toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
					});
				}

			} else {
				jQuery('#addtitonal_option').hide();
				jQuery('#type_text').hide();
				jQuery('div#type_text input#wbpoll_answer').val('');
				jQuery('#type_image').hide();
				jQuery('div#type_image input#wbpoll_answer').val('');
				jQuery('div#type_image input#wbpoll_image_answer_url').val('');
				jQuery('#type_video').hide();
				jQuery('div#type_video input#wbpoll_answer').val('');
				jQuery('div#type_video input#wbpoll_video_answer_url').val('');
				jQuery('#type_audio').hide();
				jQuery('div#type_audio input#wbpoll_answer').val('');
				jQuery('div#type_audio input#wbpoll_audio_answer_url').val('');
				jQuery('#type_html').hide();
				jQuery('div#type_html input#wbpoll_answer').val('');
				jQuery('div#type_html #wbpoll_html_answer_textarea').val('');
			}
		});

		var clickCount = 0;
		jQuery(document).on('click', 'a.add-field.extra-fields-text', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-text').attr('data-id', clickCount);
				// alert(idinc);
				
			jQuery('.text_records').clone().appendTo('.text_records_dynamic');
			jQuery('.text_records_dynamic .text_records').addClass('single remove');
			jQuery('.html_records_dynamic .extra-fields-text').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-text">Remove Fields</a>');
			jQuery('.text_records_dynamic > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.text_records_dynamic input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
		  
		});


		/*** edit text field ***/
		jQuery(document).on('click', 'a.add-field.extra-fields-text-edit', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-text-edit').attr('data-id', clickCount);
				// alert(idinc);
				
			jQuery('.text_records-edit').clone().appendTo('.text_records_dynamic-edit');
			jQuery('.text_records_dynamic-edit .text_records-edit').addClass('single remove');
			jQuery('.html_records_dynamic-edit .extra-fields-text-edit').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-text">Remove Fields</a>');
			jQuery('.text_records_dynamic-edit > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.text_records_dynamic-edit input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
		  
		});



		jQuery(document).on('click', 'a.add-field.extra-fields-image', function (e) {
			e.preventDefault();
			
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);
		   
			jQuery('.extra-fields-image').attr('data-id', clickCount);			
			jQuery('.image_records').clone().appendTo('.image_records_dynamic');
			jQuery('.image_records_dynamic .image_records').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-image').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-image">Remove Fields</a>');
			jQuery('.image_records_dynamic > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.image_records_dynamic input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_image_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		/*** edit image field ***/
		jQuery(document).on('click', 'a.add-field.extra-fields-image-edit', function (e) {
			e.preventDefault();
			
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);
		   
			jQuery('.extra-fields-image-edit').attr('data-id', clickCount);			
			jQuery('.image_records_edit').clone().appendTo('.image_records_dynamic_edit');
			jQuery('.image_records_dynamic_edit .image_records_edit').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-image-edit').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-image">Remove Fields</a>');
			jQuery('.image_records_dynamic_edit > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.image_records_dynamic_edit input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_image_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		jQuery(document).on('click', 'a.add-field.extra-fields-video', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-video').attr('data-id', clickCount);
			jQuery('.video_records').clone().html(function(i, oldHTML) {
							return oldHTML.replace(/\ name="/g, ' ');
						}).appendTo('.video_records_dynamic');
			jQuery('.video_records_dynamic .video_records').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-video').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-video">Remove Fields</a>');
			jQuery('.video_records_dynamic > .single').attr("class", 'remove remove'+clickCount);

			var count = 0;
			jQuery('.ans-video-records-wrap').each(function () {
				jQuery(this).find('input').each(function () {
					var dataName = jQuery(this).data("name");
					if (dataName) {
						var fieldname = dataName.replace("[]", "[" + count +"]" );
						jQuery(this).attr('name', fieldname);
					}
				});
				count++;
			});
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_video_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		/*** edit video field ***/
		var count = 0;
		jQuery('.ans-video-records-wrap').each(function () {
			jQuery(this).find('input').each(function () {
				var dataName = jQuery(this).data("name");
				if (dataName) {
					var fieldname = dataName.replace("[]", "[" + count +"]" );
					jQuery(this).attr('name', fieldname);
				}
			});
			count++;
		});
		jQuery(document).on('click', 'a.add-field.extra-fields-video-edit', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-video-edit').attr('data-id', clickCount);			
			jQuery('.video_records_edit').clone().html(function(i, oldHTML) {
							return oldHTML.replace(/\ name="/g, ' ');
						}).appendTo('.video_records_dynamic_edit');
			jQuery('.video_records_dynamic_edit .video_records_edit').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-video-edit').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-video">Remove Fields</a>');
			jQuery('.video_records_dynamic_edit > .single').attr("class", 'remove remove'+clickCount);
			
			var count = 0;
			jQuery('.ans-video-records-wrap').each(function () {
				jQuery(this).find('input').each(function () {
					var dataName = jQuery(this).data("name");
					if (dataName) {
						var fieldname = dataName.replace("[]", "[" + count +"]" );
						jQuery(this).attr('name', fieldname);
					}
				});
				count++;
			});
			/*
			jQuery('.video_records_dynamic_edit input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});
			*/
			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_video_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll_video_import_info[value="no"]').prop('checked', false);
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		jQuery(document).on('click', 'a.add-field.extra-fields-audio', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-audio').attr('data-id', clickCount);			
			jQuery('.audio_records').clone().html(function(i, oldHTML) {
							return oldHTML.replace(/\ name="/g, ' ');
						}).appendTo('.audio_records_dynamic');
			jQuery('.audio_records_dynamic .audio_records').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-audio').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-audio">Remove Fields</a>');
			jQuery('.audio_records_dynamic > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.ans-audio-records-wrap').each(function () {
				jQuery(this).find('input').each(function () {
					var dataName = jQuery(this).data("name");
					if (dataName) {
						var fieldname = dataName.replace("[]", "[" + count +"]" );
						jQuery(this).attr('name', fieldname);
					}
				});
				count++;
			});

			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_audio_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		/*** edit audio field ***/
		var count = 0;
		jQuery('.ans-audio-records-wrap').each(function () {
			jQuery(this).find('input').each(function () {
				var dataName = jQuery(this).data("name");
				if (dataName) {
					var fieldname = dataName.replace("[]", "[" + count +"]" );
					jQuery(this).attr('name', fieldname);
				}
			});
			count++;
		});
		jQuery(document).on('click', 'a.add-field.extra-fields-audio-edit', function (e) {
			e.preventDefault();
			
			
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);

			jQuery('.extra-fields-audio-edit').attr('data-id', clickCount);
			jQuery('.audio_records_edit').clone().html(function(i, oldHTML) {
							return oldHTML.replace(/\ name="/g, ' ');
						}).appendTo('.audio_records_dynamic_edit');

			jQuery('.audio_records_dynamic_edit .audio_records_edit').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-audio-edit').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-audio">Remove Fields</a>');
			jQuery('.audio_records_dynamic_edit > .single').attr("class", 'remove remove'+clickCount);
			var count = 0;
			jQuery('.ans-audio-records-wrap').each(function () {
				jQuery(this).find('input').each(function () {
					var dataName = jQuery(this).data("name");
					if (dataName) {
						var fieldname = dataName.replace("[]", "[" + count +"]" );
						jQuery(this).attr('name', fieldname);
					}
				});
				count++;
			});

			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_audio_answer_url').val('');
			jQuery('.remove'+clickCount+' .wbpoll_audio_import_info[value="no"]').prop('checked', true);
			jQuery('.remove'+clickCount+' .wbpoll-image-input-preview .wbpoll-image-input-preview-thumbnail').html('');
		});

		jQuery(document).on('click', 'a.add-field.extra-fields-html', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);
		   
			jQuery('.extra-fields-html').attr('data-id', clickCount);
			jQuery('.html_records').clone().appendTo('.html_records_dynamic');
			jQuery('.html_records_dynamic .html_records').addClass('single remove');
			//jQuery('.remove'+clickCount+' .extra-fields-html').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-html">Remove Fields</a>');
			jQuery('.html_records_dynamic > .single').attr("class", 'remove remove'+clickCount);
			
			var count = 0;
			jQuery('.html_records_dynamic input').each(function () {
				
				var fieldname = jQuery(this).attr("name");
				var fieldid = jQuery(this).attr("id");
				jQuery(this).attr('name', fieldname);
				jQuery(this).attr('id', fieldid + '_' + count);
				count++;
			});

			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_html_answer_textarea').attr('id','wbpoll_html_answer_textarea_' + clickCount).val('');
			jQuery('#type_html .remove'+clickCount+' .mce-tinymce.mce-container.mce-panel').remove();

			if ( typeof tinymce !== 'undefined' ) {
				jQuery('#type_html .ans-records-wrap .wbpoll_html_answer_textarea').each(function () {
					var tiny_id = jQuery(this).attr( 'id' );
					tinymce.remove('textarea.tiny#'+ tiny_id);
					tinymce.init({
						selector: 'textarea.tiny#'+ tiny_id,
						menubar: false,
						max_height: 500,
						max_width: 800,
						min_height: 200,
						min_width: 800,
						toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
					});
				});
				jQuery('#type_html .mce-tinymce.mce-container.mce-panel').show();
			}
		});

		/*** edit audio field ***/
		jQuery(document).on('click', 'a.add-field.extra-fields-html-edit', function (e) {
			e.preventDefault();
			var currentId = jQuery(this).data('id');
			var clickCount = currentId + 1; // Increase the value by 1
			jQuery(this).data('id', clickCount);
		   
			jQuery('.extra-fields-html-edit').attr('data-id', clickCount);
			jQuery('.html_records_edit').clone().appendTo('.html_records_dynamic_edit');
			jQuery('.html_records_dynamic_edit .html_records_edit').addClass('single remove');
			jQuery('.remove'+clickCount+' .extra-fields-html-edit').remove();
			jQuery('.single').append('<a href="#" class="remove-field btn-remove-html">Remove Fields</a>');
			jQuery('.html_records_dynamic_edit > .single').attr("class", 'remove remove'+clickCount);

			jQuery('.html_records_dynamic_edit input').each(function () {
				var count = 0;
				var fieldname = jQuery(this).attr("name");
				jQuery(this).attr('name', fieldname);
				count++;
			});

			jQuery('.remove'+clickCount+' .wbpoll_answer').val('');
			jQuery('.remove'+clickCount+' .wbpoll_html_answer_textarea').attr('id','wbpoll_html_answer_textarea_' + clickCount).val('');
			jQuery('#type_html .remove'+clickCount+' .mce-tinymce.mce-container.mce-panel').remove();

			if ( typeof tinymce !== 'undefined' ) {
				jQuery('#type_html .ans-records-wrap .wbpoll_html_answer_textarea').each(function () {
					var tiny_id = jQuery(this).attr( 'id' );
					tinymce.remove('textarea.tiny#'+ tiny_id);
					tinymce.init({
						selector: 'textarea.tiny#'+ tiny_id,
						menubar: false,
						max_height: 500,
						max_width: 800,
						min_height: 200,
						min_width: 800,
						toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
					});
				});
				jQuery('#type_html .mce-tinymce.mce-container.mce-panel').show();
			}

		});


		// Old direct bindings removed - delegated handlers are below at lines 735-818

		jQuery(document).on('click', '.remove-field', function (e) {
			jQuery(this).parent().remove();
			e.preventDefault();
		});

		
		jQuery('.wbpollmetadatepicker').datetimepicker({
			lazyInit: true,
			format: 'Y-m-d H:i:s',
			minDate: new Date()
		});
		// Media button handler for image attachments
		// Uses class-based selector for dynamic field support
		jQuery(document).on(
			'click',
			'.bpolls-attach[data-type="image"]',
			function (event) {
				event.preventDefault();
				var $button = jQuery(this);
				var $container = $button.closest('.polls-answer, .ans-records-wrap');
				var updateurl = $container.find('.wbpoll_image_answer_url');
				var previewEl = $container.find('.polls-answer__preview, .wbpoll-image-input-preview-thumbnail');

				var file_frame = wp.media({
					title: 'Choose Image',
					button: { text: 'Choose Image' },
					library: { type: ['image'] },
					multiple: false
				});

				file_frame.on('select', function () {
					var attachment = file_frame.state().get('selection').first().toJSON();
					if (attachment.url) {
						previewEl.html('<img width="266" height="266" src="' + attachment.url + '">');
						updateurl.val(attachment.url);
					}
				});
				file_frame.open();
			}
		);

		// Media button handler for video attachments
		jQuery(document).on(
			'click',
			'.bpolls-attach[data-type="video"]',
			function (event) {
				event.preventDefault();
				var $button = jQuery(this);
				var $container = $button.closest('.polls-answer, .ans-records-wrap');
				var updateurl = $container.find('.wbpoll_video_answer_url');
				var previewEl = $container.find('.polls-answer__preview, .wbpoll-image-input-preview-thumbnail');

				var file_frame = wp.media({
					title: 'Choose Video',
					button: { text: 'Choose Video' },
					library: { type: ['video'] },
					multiple: false
				});

				file_frame.on('select', function () {
					var attachment = file_frame.state().get('selection').first().toJSON();
					if (attachment.url) {
						previewEl.html('<video src="' + attachment.url + '" controls="" poster="" preload="none"></video>');
						updateurl.val(attachment.url);
					}
				});
				file_frame.open();
			}
		);

		// Media button handler for audio attachments
		jQuery(document).on(
			'click',
			'.bpolls-attach[data-type="audio"]',
			function (event) {
				event.preventDefault();
				var $button = jQuery(this);
				var $container = $button.closest('.polls-answer, .ans-records-wrap');
				var updateurl = $container.find('.wbpoll_audio_answer_url');
				var previewEl = $container.find('.polls-answer__preview, .wbpoll-image-input-preview-thumbnail');

				var file_frame = wp.media({
					title: 'Choose Audio',
					button: { text: 'Choose Audio' },
					library: { type: ['audio'] },
					multiple: false
				});

				file_frame.on('select', function () {
					var attachment = file_frame.state().get('selection').first().toJSON();
					if (attachment.url) {
						previewEl.html('<audio src="' + attachment.url + '" controls="" preload="none"></audio>');
						updateurl.val(attachment.url);
					}
				});
				file_frame.open();
			}
		);
			
		jQuery(document).on('keyup', '.wbpoll_answer', function (e) {
			e.preventDefault();
			jQuery('#error_ans').css('display', 'none');
		});

		// Delegated handler for image URL preview
		jQuery(document).on('keyup', '.wbpoll_image_answer_url', function (e) {
			e.preventDefault();
			var url = jQuery(this).val();
			var imagclass = jQuery(this).parent().parent().find('.wbpoll-image-input-preview-thumbnail');
			jQuery(imagclass).html('<img width="266" height="266" src="' + url + '">');
		});

		// Delegated handler for video URL preview
		jQuery(document).on('keyup', '.wbpoll_video_answer_url', function (e) {
			e.preventDefault();
			var url = jQuery(this).val();
			var suggestion = jQuery(this).parent().find('.hide_suggestion');
			var imagclass = jQuery(this).parent().parent().find('.wbpoll-image-input-preview-thumbnail');
			jQuery(imagclass).html('<video src="' + url + '" controls="" poster="" preload="none"></video>');
			jQuery(suggestion).show();
			jQuery(suggestion).find('#no').prop('checked', true);
			jQuery(suggestion).find('#yes').prop('checked', false);
		});

		// Delegated handler for audio URL preview
		jQuery(document).on('keyup', '.wbpoll_audio_answer_url', function (e) {
			e.preventDefault();
			var url = jQuery(this).val();
			var suggestion = jQuery(this).parent().find('.hide_suggestion');
			var imagclass = jQuery(this).parent().parent().find('.wbpoll-image-input-preview-thumbnail');
			jQuery(imagclass).html('<audio src="' + url + '" controls="" preload="none"></audio>');
			jQuery(suggestion).show();
			jQuery(suggestion).find('#no').prop('checked', true);
			jQuery(suggestion).find('#yes').prop('checked', false);
		});

		// Delegated handler for video import info
		jQuery(document).on('click', '.yes_video', function () {
			var $this = jQuery(this);
			var url = $this.parent().parent().find('.wbpoll_video_answer_url').val();
			var imagclass = $this.parent().parent().parent().find('.wbpoll-image-input-preview-thumbnail');
			var title = $this.parent().parent().find('.wbpoll_answer');
			var updateurl = $this.parent().parent().find('.wbpoll_video_answer_url');
			var suggestion = $this.parent();
			jQuery.getJSON('https://noembed.com/embed', {
				format: 'json',
				url: url,
			}, function (response) {
				if (response.error) {
					suggestion.find('#no').prop('checked', true);
					suggestion.find('#yes').prop('checked', false);
				} else {
					jQuery(imagclass).html(response.html);
					jQuery(title).val(response.title);
					suggestion.find('#no').prop('checked', false);
					var iframe = jQuery(response.html);
					var src = iframe.attr('src');
					jQuery(updateurl).val(src);
				}
			}).fail(function () {
				suggestion.find('#no').prop('checked', true);
				suggestion.find('#yes').prop('checked', false);
				console.error('Failed to fetch video embed info');
			});
			suggestion.hide();
		});

		// Delegated handler for audio import info
		jQuery(document).on('click', '.yes_audio', function () {
			var $this = jQuery(this);
			var url = $this.parent().parent().find('.wbpoll_audio_answer_url').val();
			var imagclass = $this.parent().parent().parent().find('.wbpoll-image-input-preview-thumbnail');
			var title = $this.parent().parent().find('.wbpoll_answer');
			var updateurl = $this.parent().parent().find('.wbpoll_audio_answer_url');
			var suggestion = $this.parent();
			jQuery.getJSON('https://noembed.com/embed', {
				format: 'json',
				url: url,
			}, function (response) {
				if (response.error) {
					suggestion.find('#no').prop('checked', true);
					suggestion.find('#yes').prop('checked', false);
				} else {
					jQuery(imagclass).html(response.html);
					jQuery(title).val(response.title);
					suggestion.find('#no').prop('checked', false);
					var iframe = jQuery(response.html);
					var src = iframe.attr('src');
					jQuery(updateurl).val(src);
				}
			}).fail(function () {
				suggestion.find('#no').prop('checked', true);
				suggestion.find('#yes').prop('checked', false);
				console.error('Failed to fetch audio embed info');
			});
			suggestion.hide();
		});

		jQuery('#wbpolls-create').submit(function (event) {
			event.preventDefault();
			const poll_id = jQuery('#poll_id').val();
			const author_id = jQuery('#author_id').val();
			const title = jQuery('#polltitle').val();
			var editor = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('poll-content') : null;
			var content = editor ? editor.getContent() : jQuery('#poll-content').val();
			const poll_type = jQuery('#poll_type').val();
			const answer = jQuery('input.wbpoll_answer').map(function () {
				return jQuery(this).val();
			}).get();
			const answertype = jQuery('input.wbpoll_answer_extra').map(function () {
				return jQuery(this).val();
			}).get();
			const full_size_image_answer = jQuery('input.wbpoll_image_answer_url').map(function () {
				return jQuery(this).val();
			}).get();
			const video_answer_url = jQuery('input.wbpoll_video_answer_url').map(function () {
				return jQuery(this).val();
			}).get();
			const audio_answer_url = jQuery('input.wbpoll_audio_answer_url').map(function () {
				return jQuery(this).val();
			}).get();
			const html_answer = jQuery('textarea.wbpoll_html_answer_textarea').map(function () {
				return jQuery(this).val();
			}).get();
			const video_import_info = jQuery('input.wbpoll_video_import_info:checked').map(function () {
				return jQuery(this).val();
			}).get();
			const audio_import_info = jQuery('input.wbpoll_audio_import_info:checked').map(function() {
				return jQuery(this).val();
			  }).get();
			  
			const _wbpoll_start_date = jQuery('#_wbpoll_start_date').val();
			const _wbpoll_end_date = jQuery('#_wbpoll_end_date').val();
			const _wbpoll_user_roles = jQuery('#_wbpoll_user_roles-chosen').val();
			const _wbpoll_content = jQuery('input[name="_wbpoll_content"]:checked').val();
			const _wbpoll_never_expire = jQuery('input[name="_wbpoll_never_expire"]:checked').val();
			const _wbpoll_show_result_before_expire = jQuery('input[name="_wbpoll_show_result_before_expire"]:checked').val();
			const _wbpoll_multivote = jQuery('input[name="_wbpoll_multivote"]:checked').val();
			const _wbpoll_vote_per_session = jQuery('#_wbpoll_vote_per_session-number').val();
			const _wbpoll_add_additional_fields = jQuery('input[name="_wbpoll_add_additional_fields"]:checked').val();

			var answerarray = jQuery('input.wbpoll_answer').map(function () {
				return jQuery(this).val();
			}).get();
		   
			var filteredArray = jQuery.grep(answerarray, function(value) {
				return value !== '';
			});
			
			// Use $.grep() to filter out duplicate values
			var uniqueArray = jQuery.grep(filteredArray, function(value, index) {
				return index === jQuery.inArray(value, filteredArray);
			});
			
			// Check if duplicate values exist
			var hasDuplicates = filteredArray.length !== uniqueArray.length;

			if(title == ""){
				jQuery('#error_title').text('Poll Title is required');
			}else if(poll_type == ""){
				jQuery('#error_type').text('Poll Type is required');
			}else if(answer == ",,,,"){
				jQuery('#error_ans').show();
				jQuery('#error_ans').text('Poll options is required');
			}else if(hasDuplicates){
				jQuery('#error_ans').show();
				jQuery('#error_ans').text("Poll options are duplicate's, Please add unique options");
			}else{
				const data = {
					poll_id:poll_id,
					author_id: author_id,
					title: title,
					content: content,
					poll_type: poll_type,
					_wbpoll_answer: answer,
					_wbpoll_answer_extra: answertype,
					_wbpoll_full_size_image_answer: full_size_image_answer,
					_wbpoll_video_answer_url: video_answer_url,
					_wbpoll_audio_answer_url: audio_answer_url,
					_wbpoll_video_import_info: video_import_info,
					_wbpoll_audio_import_info: audio_import_info,
					_wbpoll_html_answer: html_answer,
					_wbpoll_start_date: _wbpoll_start_date,
					_wbpoll_end_date: _wbpoll_end_date,
					_wbpoll_user_roles: _wbpoll_user_roles,
					_wbpoll_content: _wbpoll_content,
					_wbpoll_never_expire: _wbpoll_never_expire,
					_wbpoll_show_result_before_expire: _wbpoll_show_result_before_expire,
					_wbpoll_multivote: _wbpoll_multivote,
					_wbpoll_vote_per_session: _wbpoll_vote_per_session,
					_wbpoll_add_additional_fields:_wbpoll_add_additional_fields,
				};
				var siteUrl = wbpollpublic.url;
				jQuery.ajax({
					url: siteUrl + '/wp-json/wbpoll/v1/postpoll',
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(data),
					beforeSend: function (xhr) {
						xhr.setRequestHeader('X-WP-Nonce', wbpollpublic.rest_nonce);
					},
					success: function (response) {
						if (response.success) {
							jQuery('#pollsuccess').show();
							jQuery('#pollsuccess').text(response.message);
							window.setTimeout(
								function () {
									jQuery('#pollsuccess').hide();
									jQuery('#pollsuccess').text('');
									window.location.href = response.url;
								},
								3000
							);
						} else {
							jQuery('#pollsuccess').hide();
						}
					},
					error: function(xhr, status, error) {
						console.error('Poll creation failed:', error);
						jQuery('#pollerror').show().text(wbpollpublic.error_msg || 'Failed to create poll. Please try again.');
					}
				});
			}
		});
	});
})( jQuery );