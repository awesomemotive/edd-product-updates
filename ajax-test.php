<?php
function edd_prod_updates_email_preview_ajax(){

	global $edd_options;
	
	$email_body = isset( $edd_options['prod_updates_message'] ) ? stripslashes( $edd_options['prod_updates_message'] ) : $default_email_body;

	ob_start();
	?>
	<html>
	<body>
	<?php echo 'Does this work?';//edd_apply_email_template( $email_body, null, null );?>
	</body>
	</html>
	
	<?php
	
	echo ob_get_clean();

}
edd_prod_updates_email_preview_ajax();
	?>