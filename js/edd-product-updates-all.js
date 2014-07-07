jQuery(document).ready(function ($) {

	if( $('#prod-updates-email-preview-wrap').length ) {
		var emailPreview = $('#prod-updates-email-preview');
		$('#prod-updates-open-email-preview').colorbox({
			inline: true,
			href: emailPreview,
			width: '80%',
			height: 'auto'
		});
	}
	
	if( $('#prod-updates-email-preview-wrap-confirm').length ) {
		var emailPreview = $('#prod-updates-email-preview-confirm');
		// Uncomment to show automatically on page load
		//$.colorbox({
		$('#prod-updates-email-send').colorbox({
			inline: true,
			href: emailPreview,
			width: '80%',
			height: 'auto'
		});
		$('#cboxContent .closebutton').live('click', function(){
			$.fn.colorbox.close();
		});
	}
 function email_confirm_preview() {
	 var message = $('#edd_settings_prod_updates_message').html();
	 
	 $('#ajax-test-edd').html(message);
 }
 
 function email_confirm_get_preview_html() {
	 	var data = {
			'action': 'edd_prod_updates_confirm_ajax'
		};
		
	 	$.post(ajaxurl, data, function(response) {
			$.colorbox({html:response});
			$('.edd-pu-spin').toggleClass('loading');
			$('#send-prod-updates').prop("disabled", false);			
		});
 }
 	
	 function save_main_options_ajax() {
           $('#send-prod-updates').click( function () {
           		$(this).prop("disabled", true);
           		$('.edd-pu-spin').toggleClass('loading');
                var b =  $('#tab_container form').serialize();
                $.post( 'options.php', b ).error( 
                    function() {
                        alert('error');
						$('.edd-pu-spin').toggleClass('loading');
						$('#send-prod-updates').prop("disabled", false);					
                    }).success( function() { 
                        email_confirm_get_preview_html();
                    });
                    return false;    
                });
            }
 save_main_options_ajax();

});