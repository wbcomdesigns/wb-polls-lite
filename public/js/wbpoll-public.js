if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}
'use strict';

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
			wrapper.find( '.wbpoll-qresponse' ).html( error_result );
		}
	}// end of this data busy
}

jQuery( document ).ready(
	function ($) {

		$.base64.utf8encode = true;

		$( document.body ).on(
			'click',
			'.wbpoll-listing-trig',
			function (e) {

				e.preventDefault();

				var $this  = $( this );
				var parent = $this.closest( '.wbpoll-listing-wrap' );

				var busy     = Number( $this.attr( 'data-busy' ) );
				var page_no  = Number( $this.attr( 'data-page-no' ) );
				var per_page = Number( $this.attr( 'data-per-page' ) );
				var nonce    = $this.attr( 'data-security' );
				var user_id  = Number( $this.attr( 'data-user_id' ) );

				if (Number( busy ) === 0) {
					$this.attr( 'data-busy', 1 );

					$this.find( '.wbvoteajaximage' ).removeClass( 'wbvoteajaximagecustom' );

					$.ajax(
						{

							type: 'post',
							dataType: 'json',
							url: wbpollpublic.ajaxurl,
							data: {
								action: 'wbpoll_list_pagination',
								page_no: page_no,
								per_page: per_page,
								security: nonce,
								user_id: user_id
							},
							success: function (data, textStatus, XMLHttpRequest) {

								$this.attr( 'data-busy', 0 );

								if (data.found) {
									var content = data.content;
									parent.find( '.wbpoll-listing' ).append( content );
								}

								//check if we reached at last page
								var max_num_pages = data.max_num_pages;
								if ((page_no === max_num_pages) || (data.found === 0)) {
									$this.parent( '.wbpoll-listing-more' ).remove();
								}

								page_no++;
								$this.attr( 'data-page-no', page_no );

								$this.find( '.wbvoteajaximage' ).addClass( 'wbvoteajaximagecustom' );

							}
						}
					);

				}

			}
		);//end on click

		$( document.body ).on(
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

		$( '.wbpoll-guest-wrap' ).on(
			'click',
			'.wbpoll-title-login a',
			function (e) {
				e.preventDefault();

				let $this   = $( this );
				let $parent = $this.closest( '.wbpoll-guest-wrap' );
				$parent.find( '.wbpoll-guest-login-wrap' ).toggle();
			}
		);

	}
);//end dom ready


