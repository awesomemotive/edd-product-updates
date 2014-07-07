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
	 		//alert('here comes the update...');
			//$('#ajax-test-edd').html(response);
			//$('#prod-updates-email-preview-wrap-confirm').html(response);
			//alert('here comes the colorbox');
			$.colorbox({html:response});
		});
 }
 	
	 function save_main_options_ajax() {
           $('#tab_container form').submit( function () {
                var b =  $(this).serialize();
                $.post( 'options.php', b ).error( 
                    function() {
                        alert('error');
                    }).success( function() {
                        alert('success');   
                        email_confirm_get_preview_html();
                    });
                    return false;    
                });
            }
 save_main_options_ajax();

});