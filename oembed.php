<?php
$url = $_GET['url'];
header('Content-Type: application/json');

if(
	preg_match('#https://cloud.rpi-virtuell.de/index.php/s/.*#',$url)

){

	$home = 'https://' . $_SERVER['HTTP_HOST'] .'/rpicloud/?url='.$url;

	$html = '<iframe id="rpicloud-oembed-frame" frameBorder="0" width="100%" height="150" src="'.$home.'"></iframe><script>iFrameResize();</script>';

	$json = json_encode($html);

	?>
	{
	"version": "1.0",
	"type": "video",
    "width":"100%",
    "height":"150",
    "title": "rpicloud",
	"thumbnail_url": "https://upload.wikimedia.org/wikipedia/commons/thumb/6/60/Nextcloud_Logo.svg/320px-Nextcloud_Logo.svg.png",
	"provider_name": "rpi-virtuell",
	"provider_url": "https://rpi-virtuell.de",
	"html":<?php echo $json; ?>
	}
<?php } ?>
