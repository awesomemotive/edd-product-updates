<?php //if (!defined('W3TC')) die();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/assets/edd-pup.css'); ?>" />
		<script type="text/javascript" src="<?php echo site_url('wp-includes/js/jquery/jquery.js'); ?>"></script>
		<script type="text/javascript" src="<?php echo plugins_url('/assets/edd-pup.js'); ?>"></script>
		
		<title>Send Emails - EDD Product Updates</title>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	</head>
	<body>
<script type="text/javascript">/*<![CDATA[*/
var files = [
<?php $files_count = count($files); foreach ($files as $index => $file): ?>
	'<?php echo addslashes($file); ?>'<?php if ($index < $files_count - 1): ?>,<?php endif; ?>
<?php endforeach; ?>
];

jQuery(function() {
    W3tc_Popup_Cdn_Export_File.nonce = '<?php echo wp_create_nonce('w3tc'); ?>';
    W3tc_Popup_Cdn_Export_File.files = files;
	W3tc_Popup_Cdn_Export_File.init();
});
/*]]>*/</script>-->

		<div id="progress-wrap">
		<h2>Sending Emails</h2>
		<p><strong>WARNING:</strong> Do not refresh this page or close this window until sending is complete.</p>
		<?php echo submit_button('Start AJAX Test', 'primary', 'edd-pup-ajax-start', false, array( 'data-email'=> $_GET['id'] ) );?>
		<div class="progress-wrap">
		<div class="progress">
		  <div class="progress-bar" data-complete="0"></div>
		</div>
		<div class="progress-text">
		<p><span class="progress-clock badge">00:00</span></p>
		<p><strong><span class="progress-sent">0</span> / <span class="progress-queue">100</span> emails processed</strong> (<span class="progress-percent">0%</span>)</p>
		<div class="progress-log">
			<p class="plog-1">Building email queue</p>
			<p class="plog-2">Sending emails</p>
			<p class="plog-3">Finishing up</p>								
		</div><!-- end .progress-log -->
		</div>
		</div>
		<div id="completion" style="display:none">
		<h3>Success!</h3>
		<p><span class="success-total">100</span> emails processed in <span class="success-time">1 hr 25 min.</span></p>
		<div class="button primary-button">Send Another Product Update</div>
		</div>
		</div>
	</body>
</html>