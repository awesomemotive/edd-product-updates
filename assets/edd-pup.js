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
		$('#cboxContent .closebutton').live('click', function(){
			$.fn.colorbox.close();
		});
	
	function emailConfirmPreview() {
	
		var	button = $('#send-prod-updates'),
			spinner = $('.edd-pu-spin');
		
           button.click( function () {
           		var url = document.URL,
           			form = $('#tab_container form').serialize();

				$(this).prop("disabled",true);
				spinner.toggleClass('loading');                
                $.post( 'options.php', form ).error( function() {
                
                        alert('Could not process emails. Please try again.');
						spinner.toggleClass('loading');
						button.prop("disabled", false);
											
                    }).success( function() {
                    
						var data = {
							'action': 'edd_prod_updates_confirm_ajax',
							'url' : url
						};
						
						$.post(ajaxurl, data, function(response) {
							$.colorbox({html:response});
							spinner.toggleClass('loading');
							button.prop("disabled", false);			
						});
                    });
                    
                    return false;    
                    
                });
            }
            
	emailConfirmPreview();

});