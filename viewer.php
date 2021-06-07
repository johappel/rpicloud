<?php
    if(! defined('RPICLOUD')){
        die();
    }

    $src = 'https://view.officeapps.live.com/op/embed.aspx?src='.Cloud_Core::$officdoc;

    if(isset($_COOKIE['rpi_cloud_viewer']) || isset($_GET['rpi_cloud_viewer'])){

	    setcookie('rpi_cloud_viewer',"1",time()+(60*60*24*30));

    	$html = '<iframe id="viewer" scrolling="none" frameborder="0" class="fullscreen" src="'.$src.'"></iframe>';

    }else{
		$uri = $_SERVER['REQUEST_URI'].'?rpi_cloud_viewer=1' ;

		$html =   '<div class="view">';
		$html .=   '<h1>Download</h1>';
	    $html .=  '<p>'. substr(strrchr(urldecode(Cloud_Core::$officdoc),'/'),1).'</p><p><a class="button" href="'.Cloud_Core::$officdoc.'">Dokument herunterladen</a></p>';
	    $html .=  '<p><strong>Oder direkt im Microsoft Viewer anzeigen:</strong></p>';
	    $html .=  '<form>';
	    $html .=  '<div class="privacy">Ich akzeptiere die Microsoft <a href="https://privacy.microsoft.com/de-de/privacystatement">Datenschutzbestimmungen</a></div>';
	    $html .=  '<input type="checkbox" name="rpi_cloud_viewer" id="checkbox-1-1" class="regular-checkbox big-checkbox" /><label for="checkbox-1-1"></label> ';
	    $html .=  '<p><input type="submit"  class="button" value="Dokument anzeigen"></p></form>';
	    $html .=  '</div>';

    }

//var_dump($src);  die();
?>

<html>
	<head>
		<style>
			html,body {
				height: 100%;
			}
			body{
				margin: 0;
				padding: 0;
                font-family: Verdana;
			}
			body div.view{
                display: block;
                min-width:360px;
                margin-top: 10vh;
                margin-left: 10vw ;
                margin-right: 10vw ;
                padding: 20px 40px;
                border-radius: 10px;
                border:1px solid #ddd;
			}
            .privacy{
                font-size: 16px;
                width: 300px;
                float: right;
            }
            .regular-checkbox {
                display: none;
            }

            .regular-checkbox + label {
                background-color: #fafafa;
                border: 2px solid #337ab7;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px -15px 10px -12px rgba(0,0,0,0.05);
                padding: 9px;
                border-radius: 3px;
                display: inline-block;
                position: relative;
                margin-right: 20px;
                margin-bottom: -10px;
                margin-left: 5px;
            }

            .regular-checkbox + label:active, .regular-checkbox:checked + label:active {
                box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px 1px 3px rgba(0,0,0,0.1);
            }

            .regular-checkbox:checked + label {
                background-color: #e9ecee;
                border: 1px solid #adb8c0;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05), inset 0px -15px 10px -12px rgba(0,0,0,0.05), inset 15px 10px -12px rgba(255,255,255,0.1);
                color: #99a1a7;
            }

            .regular-checkbox:checked + label:after {
                content: '\2714';
                font-size: 14px;
                position: absolute;
                top: 0px;
                left: 3px;
                color: #99a1a7;
            }


            .big-checkbox + label {
                padding: 18px;
            }

            .big-checkbox:checked + label:after {
                font-size: 28px;
                left: 6px;
            }

            .button, input[type=submit]{
	            display: block;
	            padding: 20px 0px;
	            border-radius: 10px;
	            border:1px solid #ddd;
	            background-color: #337ab7;
	            color:#fff;
	            font-family: Verdana;
	            text-decoration: none;
	            text-align: center;
	            width:100%;
	            max-width: 100%;
                font-size: 24px;

            }
            .button:hover{
                background-color: #3399ff;
                border:2px solid red;
            }
            .fullscreen{
	            height: 100vh;
	            width: 100vw;
	            margin-left: auto;
	            margin-right: auto;
            }
		</style>
	</head>
<body>

<?php echo $html;?>
</body>
</html>

