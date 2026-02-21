if (typeof wp !== 'undefined' && wp.i18n) {
    const { __ } = wp.i18n;
}

(function( $ ) {
	'use strict';

	$(function() {
			$('.open_log').on('click', function(){
                var id = $(this).data('id');
                $('.opendetails-'+id).show();
            });
            $('.close').on('click', function(){
                $('.openmodal').hide();
            });

            $('.delete_log').on('click', function(){
                var log_id = $(this).data('id');
                var $btn = $(this);

                // Disable button to prevent double-clicks.
                $btn.prop('disabled', true);

                $.ajax({
                    url: wbpolladminsingleObj.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wbpoll_log_delete',
                        log_id: log_id,
                        ajax_nonce: wbpolladminsingleObj.nonce
                    },
                    success: function (response) {
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $btn.prop('disabled', false);
                        console.error('Log delete failed:', textStatus, errorThrown);
                    }
                });

            });
		});
        
})( jQuery );