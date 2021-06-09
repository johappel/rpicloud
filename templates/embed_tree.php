<?php
/*
 * Template Name: rpi cloud
 * Description: displays nextcloud tree
 */
$passwd = '';

$url = isset($_REQUEST['url'])?$_REQUEST['url']:'';
$dir = isset($_REQUEST['dir'])? urldecode($_REQUEST['dir']):'/';

if(empty(str_replace(':','',$url))){
	$html = '<div style="width: 100%; text-align: center;padding: 10px;">Url des geteilten Nextcloud Ordners angeben</div>';
}else{

	$u = parse_url($url);

	$path = substr( strrchr($u['path'],'/'), 1);

	$params = explode(':', $path);

	$usr = $params[0];

	if(isset($params[1])){
		$passwd = $params[1];
	}
	$url = $u['scheme'].'://'.$u['host'].'/s/'.$usr;

	$html = do_shortcode('[rpicloud dir="/" upload="false" url="'.$url.'" password="'.$passwd.'" dir="'.$dir.'"]');

}


?>
<html>
	<head>
        <title>rpiCloud</title>
		<?php wp_print_head_scripts();?>
		<?php wp_print_styles();?>
		<style>
            *{
                font-family: sans-serif;
            }
		</style>
	</head>
	<body>
		<?php echo $html; ?>
        <script type="text/javascript" src="<?php echo plugin_dir_url(dirname(__FILE__));?>js/cloudframe.content.js"></script>
        <?php wp_print_footer_scripts(); ?>
        <script>if(parent != window && parent.jQuery){
                parent.jQuery('iframe').iFrameResize();

        }</script>
	</body>
</html>
