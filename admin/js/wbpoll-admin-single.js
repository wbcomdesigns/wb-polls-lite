
if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}

(function ($) {
	'use strict';
	$(document).ready(function () {

		if ($('input[name="_wbpoll_never_expire"]:checked').val() == 1) {
			$('._wbpoll_start_date,._wbpoll_end_date,._wbpoll_show_result_before_expire').hide();
			$("input[name=_wbpoll_show_result_before_expire][value='0']").prop("checked", true);
		}
		$('input[name="_wbpoll_never_expire"]').on('change', function (e) {
			if ($(this).val() == 1) {
				$('._wbpoll_start_date,._wbpoll_end_date,._wbpoll_show_result_before_expire').hide();
				$("input[name=_wbpoll_show_result_before_expire][value='0']").prop("checked", true);
			} else {
				$('._wbpoll_start_date,._wbpoll_end_date,._wbpoll_show_result_before_expire').show();
			}
		});

		function wbpoll_copyStringToClipboard(str) {
			// Create new element
			var el = document.createElement('textarea');
			// Set value (string to be copied)
			el.value = str;
			// Set non-editable to avoid focus and move outside of view
			el.setAttribute('readonly', '');
			el.style = { position: 'absolute', left: '-9999px' };
			document.body.appendChild(el);
			// Select text inside element
			el.select();
			// Copy text to clipboard
			document.execCommand('copy');
			// Remove temporary element
			document.body.removeChild(el);
		}
		if ($('#preloader').length == 1) {
			setTimeout(function () { document.getElementById("preloader").style.display = "none"; }, 3500);
		}

		$('.selecttwo-select').select2(
			{
				placeholder: wbpolladminsingleObj.please_select,
				allowClear: false
			}
		);

		// style the radio yes no
		$('.wbpollmetadatepicker').datetimepicker(
			{
				dateFormat: 'yy-mm-dd',
				timeFormat: 'HH:mm:ss',
				minDate: 0
			}
		);

		$.datepicker._gotoToday = function (id) {
			var inst = this._getInst($(id)[0]),
				$dp = inst.dpDiv;
			this._base_gotoToday(id);
			//var tp_inst = this._get(inst, 'timepicker');
			//removed -> selectLocalTimeZone(tp_inst);
			var now = new Date();
			var now_utc = new Date(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(), now.getUTCHours(), now.getUTCMinutes(), now.getUTCSeconds());
			this._setTime(inst, now_utc);
			$('.ui-datepicker-today', $dp).click();
		};

		//$( '.wbpoll_answer_color' ).wpColorPicker();
		//$( '.wbpoll-colorpicker' ).wpColorPicker();

		if ($('#wb_poll_answers_items').length) {
			$('#wb_poll_answers_items').sortable(
				{
					group: 'no-drop',
					placeholder: 'wb_poll_items wb_poll_items_placeholder',
					handle: '.cbpollmoveicon',
					onDragStart: function ($item, container, _super) {
						// Duplicate items of the no drop area
						if (!container.options.drop) {
							$item.clone().insertAfter($item);
						}
						_super($item, container);
					}
				}
			);

		}

		//config used to add color picker for newly added answer
		var colorOptions = {
			change: function (event, ui) {
			},
			// a callback to fire when the input is emptied or an invalid color
			clear: function () {
			},
			// hide the color picker controls on load
			hide: true,
			palettes: true
		};

		// add new answer
		$('#wbpoll_answer_wrap').on(
			'click',
			'.add-wb-poll-answer',
			function (event) {
				event.preventDefault();

				var $this = $(this);
				var $answer_wrap = $this.closest('#wbpoll_answer_wrap');
				var $answer_add_wrap = $this.parent('.add-wb-poll-answer-wrap');

				var $post_id = Number($answer_add_wrap.data('postid'));
				//var $index               = Number($answer_add_wrap.data('answercount'));
				var $index = Number($('#wbpoll_answer_extra_answercount').val());

				var $busy = Number($answer_add_wrap.data('busy'));
				var $type = $this.data('type');
				$('#poll_type').val($type);

				//get random answer color
				var answer_color = '#' + '0123456789abcdef'.split('').map(
					function (v, i, a) {
						return i > 5 ? null : a[Math.floor(Math.random() * 16)];
					}
				).join('');

				//sending ajax request to get the field template

				if ($busy === 0) {
					$answer_add_wrap.data('busy', 1);

					$.ajax(
						{
							type: 'post',
							dataType: 'json',
							url: wbpolladminsingleObj.ajaxurl,
							data: {
								action: 'wbpoll_get_answer_template',
								answer_counter: $index,
								answer_color: answer_color,
								is_voted: 0,
								poll_postid: $post_id,
								answer_type: $type,
								security: wbpolladminsingleObj.nonce
							},
							success: function (data, textStatus, XMLHttpRequest) {
								if (itemsType($type)) {
									$('#wb_poll_answers_items').append(data);
								} else {
									$('#wb_poll_answers_items').html(data);
								}
								$('.wbpoll-containable-list-item-toolbar.toolbar-' + $index).addClass('active');

								//$answer_wrap.find( '.wbpoll_answer_color' ).last().wpColorPicker( colorOptions );

								//helps to render the  editor properly
								//quicktags({id : '_wbpoll_answer_extra_'+$count+'_html'});
								//tinyMCE.execCommand('mceAddEditor', false, '_wbpoll_answer_extra_'+$count+'_html');

								wp.wbpolljshooks.doAction('wppoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

								$index++;
								//$answer_add_wrap.data('answercount', $index);
								$('#wbpoll_answer_extra_answercount').val($index);
								$answer_add_wrap.data('busy', 0);
							}
						}
					);
				}

			}
		);

		// add new answer image
		$('#wbpoll_answer_wrap').on(
			'click',
			'.add-wb-poll-image-answer',
			function (event) {
				event.preventDefault();

				var $this = $(this);
				var $answer_wrap = $this.closest('#wbpoll_answer_wrap');
				var $answer_add_wrap = $this.parent('.add-wb-poll-answer-image-wrap');

				var $post_id = Number($answer_add_wrap.data('postid'));
				//var $index               = Number($answer_add_wrap.data('answercount'));
				var $index = Number($('#wbpoll_answer_extra_answercount').val());
				var $busy = Number($answer_add_wrap.data('busy'));
				var $type = $this.data('type');
				$('#poll_type').val($type);

				//get random answer color
				var answer_color = '#' + '0123456789abcdef'.split('').map(
					function (v, i, a) {
						return i > 5 ? null : a[Math.floor(Math.random() * 16)];
					}
				).join('');

				//sending ajax request to get the field template

				if ($busy === 0) {
					$answer_add_wrap.data('busy', 1);

					$.ajax(
						{
							type: 'post',
							dataType: 'json',
							url: wbpolladminsingleObj.ajaxurl,
							data: {
								action: 'wbpoll_get_answer_template',
								answer_counter: $index,
								answer_color: answer_color,
								is_voted: 0,
								poll_postid: $post_id,
								answer_type: $type,
								security: wbpolladminsingleObj.nonce
							},
							success: function (data, textStatus, XMLHttpRequest) {
								if (itemsType($type)) {
									$('#wb_poll_answers_items').append(data);
								} else {
									$('#wb_poll_answers_items').html(data);
								}
								$('.wbpoll-containable-list-item-toolbar.toolbar-' + $index).addClass('active');
								//$answer_wrap.find( '.wbpoll_answer_color' ).last().wpColorPicker( colorOptions );

								//helps to render the  editor properly
								//quicktags({id : '_wbpoll_answer_extra_'+$count+'_html'});
								//tinyMCE.execCommand('mceAddEditor', false, '_wbpoll_answer_extra_'+$count+'_html');

								wp.wbpolljshooks.doAction('wbpoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

								$index++;
								//$answer_add_wrap.data('answercount', $index);
								$('#wbpoll_answer_extra_answercount').val($index);
								$answer_add_wrap.data('busy', 0);
							}
						}
					);
				}

			}
		);

		// add new answer video
		$('#wbpoll_answer_wrap').on(
			'click',
			'.add-wb-poll-video-answer',
			function (event) {
				event.preventDefault();

				var $this = $(this);
				var $answer_wrap = $this.closest('#wbpoll_answer_wrap');
				var $answer_add_wrap = $this.parent('.add-wb-poll-answer-video-wrap');

				var $post_id = Number($answer_add_wrap.data('postid'));
				//var $index               = Number($answer_add_wrap.data('answercount'));
				var $index = Number($('#wbpoll_answer_extra_answercount').val());
				var $busy = Number($answer_add_wrap.data('busy'));
				var $type = $this.data('type');
				$('#poll_type').val($type);

				//get random answer color
				var answer_color = '#' + '0123456789abcdef'.split('').map(
					function (v, i, a) {
						return i > 5 ? null : a[Math.floor(Math.random() * 16)];
					}
				).join('');

				//sending ajax request to get the field template

				if ($busy === 0) {
					$answer_add_wrap.data('busy', 1);

					$.ajax(
						{
							type: 'post',
							dataType: 'json',
							url: wbpolladminsingleObj.ajaxurl,
							data: {
								action: 'wbpoll_get_answer_template',
								answer_counter: $index,
								answer_color: answer_color,
								is_voted: 0,
								poll_postid: $post_id,
								answer_type: $type,
								security: wbpolladminsingleObj.nonce
							},
							success: function (data, textStatus, XMLHttpRequest) {
								if (itemsType('video')) {
									$('#wb_poll_answers_items').append(data);
								} else {
									$('#wb_poll_answers_items').html(data);
								}
								
								$('.wbpoll-containable-list-item-toolbar.toolbar-' + $index).addClass('active');
								//$answer_wrap.find( '.wbpoll_answer_color' ).last().wpColorPicker( colorOptions );

								//helps to render the  editor properly
								//quicktags({id : '_wbpoll_answer_extra_'+$count+'_html'});
								//tinyMCE.execCommand('mceAddEditor', false, '_wbpoll_answer_extra_'+$count+'_html');

								wp.wbpolljshooks.doAction('wbpoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

								$index++;
								//$answer_add_wrap.data('answercount', $index);
								$('#wbpoll_answer_extra_answercount').val($index);
								$answer_add_wrap.data('busy', 0);
								$('.yes_video').on('click', function () {
									var choice = $(this).val();
									var id = $(this).data('id');
									if (choice == 'yes') {
										var url = $('.wb-hide-' + id + ' .video_url').val();
										var videoclass = $('.wb-hide-' + id + ' .video_url').data('text');

										$.getJSON('https://noembed.com/embed', {
											format: 'json',
											url: url,
										}, function (response) {
											if (response.error) {

												$('.error-' + id).text("Please add correct link");
												$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
											} else {
												$('.error-' + id).text("");
												$('.video_' + videoclass).html(response.html);
												$('#wbpoll_answer-' + id).val(response.title);
												var iframe = $(response.html);
												var src = iframe.attr('src');
												$('#wbpoll_answer-url-' + id).val(src);
											}
										});

										$('.hide_suggestion-' + id).hide();
									} else {
										$('.hide_suggestion-' + id).hide();
									}
								});
							}
						}
					);
				}

			}
		);

		// add new answer Audio
		$('#wbpoll_answer_wrap').on(
			'click',
			'.add-wb-poll-audio-answer',
			function (event) {
				event.preventDefault();

				var $this = $(this);
				var $answer_wrap = $this.closest('#wbpoll_answer_wrap');
				var $answer_add_wrap = $this.parent('.add-wb-poll-answer-audio-wrap');

				var $post_id = Number($answer_add_wrap.data('postid'));
				//var $index               = Number($answer_add_wrap.data('answercount'));
				var $index = Number($('#wbpoll_answer_extra_answercount').val());
				var $busy = Number($answer_add_wrap.data('busy'));
				var $type = $this.data('type');
				$('#poll_type').val($type);

				//get random answer color
				var answer_color = '#' + '0123456789abcdef'.split('').map(
					function (v, i, a) {
						return i > 5 ? null : a[Math.floor(Math.random() * 16)];
					}
				).join('');

				//sending ajax request to get the field template

				if ($busy === 0) {
					$answer_add_wrap.data('busy', 1);

					$.ajax(
						{
							type: 'post',
							dataType: 'json',
							url: wbpolladminsingleObj.ajaxurl,
							data: {
								action: 'wbpoll_get_answer_template',
								answer_counter: $index,
								answer_color: answer_color,
								is_voted: 0,
								poll_postid: $post_id,
								answer_type: $type,
								security: wbpolladminsingleObj.nonce
							},
							success: function (data, textStatus, XMLHttpRequest) {
								if (itemsType('audio')) {
									$('#wb_poll_answers_items').append(data);
								} else {
									$('#wb_poll_answers_items').html(data);
								}
								$('.wbpoll-containable-list-item-toolbar.toolbar-' + $index).addClass('active');
								//$answer_wrap.find( '.wbpoll_answer_color' ).last().wpColorPicker( colorOptions );
								wp.wbpolljshooks.doAction('wbpoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

								$index++;
								//$answer_add_wrap.data('answercount', $index);
								$('#wbpoll_answer_extra_answercount').val($index);
								$answer_add_wrap.data('busy', 0);
								$('.yes_audio').on('click', function () {
									var choice = $(this).val();
									var id = $(this).data('id');
									if (choice == 'yes') {
										var url = $('.wb-hide-' + id + ' .audio_url').val();
										var audioclass = $('.wb-hide-' + id + ' .audio_url').data('text');

										$.getJSON('https://noembed.com/embed', {
											format: 'json',
											url: url,
										}, function (response) {
											if (response.error) {
												$('.error-' + id).text("Please add correct link");
												$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
											} else {
												$('.error-' + id).text("");
												$('.audio_' + audioclass).html(response.html);
												$('#wbpoll_answer-' + id).val(response.title);
												var iframe = $(response.html);
												var src = iframe.attr('src');
												$('#wbpoll_answer-url-' + id).val(src);
											}
										});
										$('.hide_suggestion-' + id).hide();
									} else {
										$('.hide_suggestion-' + id).hide();
									}
								});
							}
						}
					);
				}

			}
		);

		// add new answer HTML
		$('#wbpoll_answer_wrap').on(
			'click',
			'.add-wb-poll-html-answer',
			function (event) {
				event.preventDefault();

				var $this = $(this);
				var $answer_wrap = $this.closest('#wbpoll_answer_wrap');
				var $answer_add_wrap = $this.parent('.add-wb-poll-answer-html-wrap');

				var $post_id = Number($answer_add_wrap.data('postid'));
				//var $index               = Number($answer_add_wrap.data('answercount'));
				var $index = Number($('#wbpoll_answer_extra_answercount').val());
				var $busy = Number($answer_add_wrap.data('busy'));
				var $type = $this.data('type');
				$('#poll_type').val($type);

				//get random answer color
				var answer_color = '#' + '0123456789abcdef'.split('').map(
					function (v, i, a) {
						return i > 5 ? null : a[Math.floor(Math.random() * 16)];
					}
				).join('');

				//sending ajax request to get the field template

				if ($busy === 0) {
					$answer_add_wrap.data('busy', 1);

					$.ajax({
						type: 'post',
						dataType: 'json',
						url: wbpolladminsingleObj.ajaxurl,
						data: {
							action: 'wbpoll_get_answer_template',
							answer_counter: $index,
							answer_color: answer_color,
							is_voted: 0,
							poll_postid: $post_id,
							answer_type: $type,
							security: wbpolladminsingleObj.nonce
						},
						success: function (data, textStatus, XMLHttpRequest) {

							if (itemsType('html')) {
								$('#wb_poll_answers_items').append(data);
							} else {
								$('#wb_poll_answers_items').html(data);
							}
							$('.wbpoll-containable-list-item-toolbar.toolbar-' + $index).addClass('active');
							//$answer_wrap.find( '.wbpoll_answer_color' ).last().wpColorPicker( colorOptions );

							wp.wbpolljshooks.doAction('wbpoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

							$index++;
							//$answer_add_wrap.data('answercount', $index);
							$('#wbpoll_answer_extra_answercount').val($index);
							$answer_add_wrap.data('busy', 0);

							tinymce.init({
								selector: 'textarea.tiny',
								menubar: false,
								max_height: 500,
								max_width: 800,
								min_height: 200,
								min_width: 800,
								toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
							});
						}
					});
				}

			}
		);

		const itemsType = (type) => {
			let itemType = $('.wb_poll_items').data('type');
			if (type === itemType) {
				return true;
			} else {
				return false;
			}
		}

		//image uploader

		$('#wbpoll_answer_wrap').on(
			'click',
			'#upload-btn',
			function (e) {
				var mediaUploader;
				var imgclass = $(this).data('text');
				e.preventDefault();
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}
				mediaUploader = wp.media.frames.file_frame = wp.media(
					{
						title: 'Choose Image',
						button: {
							text: 'Choose Image'
						},
						library: {
							type: ['image']
						},
						multiple: false
					}
				);
				mediaUploader.on(
					'select',
					function () {
						var attachment = mediaUploader.state().get('selection').first().toJSON();
						$('.' + imgclass).val(attachment.url);
						$('.image_' + imgclass).html('<img width="266" height="266" src="' + attachment.url + '">');
					}
				);
				mediaUploader.open();
			}
		);

		//video uploader

		$('#wbpoll_answer_wrap').on(
			'click',
			'#upload-btn-video',
			function (e) {
				var mediaUploader;
				var imgclass = $(this).data('text');
				var id = $(this).data('id');
				e.preventDefault();
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}
				mediaUploader = wp.media.frames.file_frame = wp.media(
					{
						title: 'Choose video',
						button: {
							text: 'Choose video'
						},
						library: {
							type: ['video']
						},
						multiple: false
					}
				);
				mediaUploader.on(
					'select',
					function () {
						var attachment = mediaUploader.state().get('selection').first().toJSON();
						$('.' + imgclass).val(attachment.url);
						$('.video_' + imgclass).html('<video src="' + attachment.url + '" controls="" poster="" preload="none"></video>');
					}
				);
				mediaUploader.open();
				$('.hide_suggestion-' + id).hide();
				$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
			}
		);

		//audio uploader

		$('#wbpoll_answer_wrap').on(
			'click',
			'#upload-audio-btn',
			function (e) {
				var mediaUploader;
				var imgclass = $(this).data('text');

				var id = $(this).data('id');
				e.preventDefault();
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}
				mediaUploader = wp.media.frames.file_frame = wp.media(
					{
						title: 'Choose audio',
						button: {
							text: 'Choose audio'
						},
						library: {
							type: ['audio']
						},
						multiple: false
					}
				);
				mediaUploader.on(
					'select',
					function () {

						var attachment = mediaUploader.state().get('selection').first().toJSON();
						$('.' + imgclass).val(attachment.url);
						$('.audio_' + imgclass).html('<audio src="' + attachment.url + '" controls="" poster="" preload="none"></audio>');
					}
				);
				mediaUploader.open();
				$('.hide_suggestion-' + id).hide();
				$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
			}
		);


		//image add with url

		$('#wbpoll_answer_wrap').on(
			'keyup',
			'.image_url',
			function (e) {
				var imgclass = $(this).data('text');
				var url = $(this).val();
				$('.image_' + imgclass).html('<img width="266" height="266" src="' + url + '">');

			});

		//video add with url

		$('#wbpoll_answer_wrap').on(
			'keyup',
			'.video_url',
			function (e) {
				var url = $(this).val();
				if (url != '') {
					var videoclass = $(this).data('text');
					var id = $(this).data('id');
					$('.hide_suggestion-' + id).show();
					$('.video_' + videoclass).html('<video src="' + url + '" controls="" poster="" preload="none"></video>');
				}
			});

		$('.yes_video').on('click', function () {
			var choice = $(this).val();
			var id = $(this).data('id');
			if (choice == 'yes') {
				var url = $('.wb-hide-' + id + ' .video_url').val();
				var videoclass = $('.wb-hide-' + id + ' .video_url').data('text');

				$.getJSON('https://noembed.com/embed', {
					format: 'json',
					url: url,
				}, function (response) {
					if (response.error) {

						$('.error-' + id).text("Please add correct link");
						$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
					} else {
						$('.error-' + id).text("");
						$('.video_' + videoclass).html(response.html);
						$('#wbpoll_answer-' + id).val(response.title);
						var iframe = $(response.html);
						var src = iframe.attr('src');
						$('#wbpoll_answer-url-' + id).val(src);
					}
				});

				$('.hide_suggestion-' + id).hide();
			} else {
				$('.hide_suggestion-' + id).hide();
			}
		});


		//audio add with url

		$('#wbpoll_answer_wrap').on(
			'keyup',
			'.audio_url',
			function (e) {
				var url = $(this).val();
				if (url != '') {
					var id = $(this).data('id');
					var audioclass = $(this).data('text');
					$('.hide_suggestion-' + id).show();
					$('.audio_' + audioclass).html('<audio src="' + url + '" controls="" preload="none"></audio>');
				}

			});

		$('.yes_audio').on('click', function () {
			var choice = $(this).val();
			var id = $(this).data('id');
			if (choice == 'yes') {
				var url = $('.wb-hide-' + id + ' .audio_url').val();
				var audioclass = $('.wb-hide-' + id + ' .audio_url').data('text');

				$.getJSON('https://noembed.com/embed', {
					format: 'json',
					url: url,
				}, function (response) {
					if (response.error) {
						$('.error-' + id).text("Please add correct link");
						$('.hide_suggestion-' + id + ' input#no').prop('checked', true);
					} else {
						$('.error-' + id).text("");
						$('.audio_' + audioclass).html(response.html);
						$('#wbpoll_answer-' + id).val(response.title);
						var iframe = $(response.html);
						var src = iframe.attr('src');
						$('#wbpoll_answer-url-' + id).val(src);
					}
				});
				$('.hide_suggestion-' + id).hide();
			} else {
				$('.hide_suggestion-' + id).hide();
			}
		});

		//remove an answer
		$('#wbpoll_answer_wrap').on(
			'click',
			'.wb_pollremove',
			function (event) {
				event.preventDefault();

				var $this = $(this);

				// Use native confirm dialog instead of Ply library.
				if ( confirm( wbpolladminsingleObj.deleteconfirm ) ) {
					$this.parent().parent('.wb_poll_items').remove();
				}
			}
		);

		//click to copy shortcode
		$('.wbpoll_ctp').on(
			'click',
			function (e) {
				e.preventDefault();

				var $this = $(this);
				wbpoll_copyStringToClipboard($this.prev('.wbpollshortcode').text());

				$this.attr('aria-label', wbpolladminsingleObj.copied);

				window.setTimeout(
					function () {
						$this.attr('aria-label', wbpolladminsingleObj.copy);
					},
					1000
				);
			}
		);

		//click to copy shortcode
		$('.wbpoll_embed').on(
			'click',
			function (e) {
				e.preventDefault();

				var $this = $(this);
				wbpoll_copyStringToClipboard($this.prev('.wbpollemded').text());

				$this.attr('aria-label', wbpolladminsingleObj.copied);

				window.setTimeout(
					function () {
						$this.attr('aria-label', wbpolladminsingleObj.copy);
					},
					1000
				);
			}
		);

		$('.hidetab').hide();
		$('.wbpoll-containable-list-item-toolbar').on('click', function () {
			var dataid = $(this).data('id');
			$('.wb-hide-' + dataid).toggle();
			$(this).toggleClass('active');
		});

		$('.wbpoll-containable-list-item-toolbar').on(
			'click',
			function (e) {
				e.preventDefault();
				tinymce.init({
					selector: 'textarea.tiny',
					menubar: false,
					max_height: 500,
					max_width: 800,
					min_height: 200,
					min_width: 800,
					toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
				});

			});
	});
})(jQuery);