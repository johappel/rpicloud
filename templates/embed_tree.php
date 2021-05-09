<?php
/*
 * Template Name: rpi cloud
 * Description: displays nextcloud tree
 */
$passwd = '';

$url = $_REQUEST['url'];

$u = parse_url($url);

$path = substr( strrchr($u['path'],'/'), 1);

$params = explode(':', $path);

$usr = $params[0];

if(isset($params[1])){
	$passwd = $params[1];
}
$url = $u['scheme'].'://'.$u['host'].'/s/'.$usr;



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
		<?php echo do_shortcode('[rpicloud dir="/" upload="false" url="'.$url.'" password="'.$passwd.'"]') ?>
        <script type="text/javascript" src="<?php echo plugin_dir_url(dirname(__FILE__));?>js/cloudframe.content.js"></script>
        <?php wp_print_footer_scripts(); ?>
	</body>
</html>
