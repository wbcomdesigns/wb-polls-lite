if (typeof wp !== 'undefined' && wp.i18n) {
	const { __ } = wp.i18n;
}

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

	
	$( document ).ready( function() {
		
		$('#polls_background_color').wpColorPicker();
		
		
		var bpolls_elmt = document.getElementsByClassName( "wbcom-faq-accordion" );
		var k;
		var bpolls_elmt_len = bpolls_elmt.length;
		for (k = 0; k < bpolls_elmt_len; k++) {
			bpolls_elmt[k].onclick = function() {
				this.classList.toggle( "active" );
				var panel = this.nextElementSibling;
				if (panel.style.maxHeight) {
					panel.style.maxHeight = null;
				} else {
					panel.style.maxHeight = panel.scrollHeight + "px";
				}
			}
		}

		$('input[name="bpolls_settings[poll_list_voters]"]').on('change', function(){
			if ($(this).is(":checked")) {
				$( '#poll_limit_voters_options' ).show();				
			}else {
				$( '#poll_limit_voters_options' ).hide();				
			}
		});

		$('input[name="bpolls_settings[limit_poll_activity]"]').on('change', function(){
			var $val = $(this).val();
			if ($val == 'user_role') {
				$( '#bpolls_user_role' ).show();
				$( '#bpolls_member_type' ).hide();
			} else if($val == 'member_type') {
				$( '#bpolls_user_role' ).hide();
				$( '#bpolls_member_type' ).show();
			} else {
				$( '#bpolls_user_role' ).hide();
				$( '#bpolls_member_type' ).hide();
			}
		});

		
	});

})( jQuery );
