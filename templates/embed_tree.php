<?php
/*
 * Template Name: rpi cloud
 * Description: displays nextcloud tree
 */
$url = $_REQUEST['url'];
?>
<html>
	<head>
        <title>rpiCloud</title>
		<?php wp_print_head_scripts();?>
		<?php wp_print_styles();?>
		<style>


		</style>
	</head>
	<body>
		<?php echo do_shortcode('[rpicloud dir="/" upload="false" url="'.$url.'"]') ?>
        <script type="text/javascript" src="<?php echo plugin_dir_url(dirname(__FILE__));?>js/cloudframe.content.js"></script>
	</body>
</html>


