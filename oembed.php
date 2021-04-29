<?php
$url = $_GET['url'];
header('Content-Type: application/json');

if(
	preg_match('#https://cloud.rpi-virtuell.de/index.php/s/.*#',$url)

){

	$shortcode = '[rpicloud url="'.$url.'" password="" dir="/" upload="false" delete="false"]';

	$html = '<div class="wp-block">'.$shortcode.'</div>';

	$json = json_encode($html);

	?>
	{
	"version": "1.0",
	"type": "video",
	"title": "rpicloud",
	"url": "https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/Nextcloud_Logo.svg/320px-Nextcloud_Logo.svg.png",
	"author_name": "rpi-virtuell",
	"author_url": "http://rpi-virtuell.de",
	"provider_name": "rpi-virtuell",
	"provider_url": "https://rpi-virtuell.de",
	"html":<?php echo $json; ?>
	}
<?php } ?>
