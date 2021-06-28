<?php


class Cloud_Client {

	protected $client;
	public $cfg;

	function __construct($trans_key, $trans_value = false) {

		$this->cfg = new Cloud_Config($trans_key, $trans_value);

		$this->client = $this->cfg->get_client();

		return $this->client;

	}
	function get_file($url , $props, $atts){

		$ctype = $props['{DAV:}getcontenttype'];
		if(strpos($ctype,'image/')===0){


			$size = getimagesize($url.'/download');
			if($size && is_array($size)){
				$w = $size[0];
				$h = $size[1];
			}
			$img = "<img src=\"{$url}/download\" width=\"$w\" height=\"$h\" style=\"display: block; max-width:{$w}px; max-height:{$h}px; width: 100%; height: auto;\">";

			$script = '<script> iFrameResize();</script>';

			return '<div onclick="window.open(\''.$url.'\',\'_blank\')" style="cursor:pointer; width:500px; height:500px">'.$img.'</div>'.$script;

		}elseif(strpos($ctype,'video/')===0){

				$url .= '/download';

				$html = '<video preload="metadata" width="100%" height="500" style="height: 100%; min-height: 500px" controls id="video" style="background-color: #444">
                    <source  src="'.$url.'" type="'.$ctype.'">
                </video>';

				return $html;

		}elseif(strpos($ctype,'audio/')===0){

				$url .= '/download';

				$html = '<audio preload="metadata" width="100%" height="500" style="height: 100%; min-height: 500px" controls id="video" style="background-color: #444">
                    <source  src="'.$url.'" type="'.$ctype.'">
                </audio>';

				return $html;
		}elseif($ctype == 'application/pdf'){

			$url .= '/download';

			$src = Cloud_Core::$shorturl .'/temp/'.base64_encode($url);

			$html = '<!-- wp:pdfb/pdf-block {"showToolbar":true,"url":"'.$src.'"} -->'."\n";
			$html .= '<div class="wp-block-pdfb-pdf-block" style="height:1000px"><iframe src="'.$src.'" width="100%" height="100%"></iframe></div>'."\n";
			$html .= '<!-- /wp:pdfb/pdf-block -->'."\n";
			$html .= '<p><a href="'.$url.'">Download</a></p>';

			return $html;
		}else{

			$url .='/download';

			$officedocs = array(
				'application/vnd.oasis.opendocument.text',
				'application/vnd.oasis.opendocument.text-template',
				'application/vnd.oasis.opendocument.presentation',
				'application/vnd.oasis.opendocument.presentation-template',
				'application/msword',
				'application/vnd.ms-powerpoint',
				'application/vnd.ms-excel',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'application/vnd.visio'

			);
			if(in_array($ctype,$officedocs)){

				if(isset($_COOKIE['rpi_cloud_viewer']) || isset($_GET['rpi_cloud_viewer'])){
					$src = 'https://view.officeapps.live.com/op/embed.aspx?src='.$url;
					setcookie('rpi_cloud_viewer',"1",time()+(60*60*24*30));
					$html = '<iframe id="viewer" scrolling="none" frameborder="0" width="100%" height="500" class="fullscreen" src="'.$src.'"></iframe>';
				}else{
					$uri = $url.'?rpi_cloud_viewer=1' ;
					$html =   '<div class="view" style="border:1px solid #ddd">';
					$html .=  '<p><a class="button" href="'.$url.'">Dokument herunterladen</a></p>';
					$html .=  '<form>';

					$html .=  '<div class="privacy" style="float: right; width: 90%">';
					$html .=  '<p>Direkt im Microsoft Viewer anzeigen: ';
					$html .=  'Ich akzeptiere die <a href="https://privacy.microsoft.com/de-de/privacystatement">Datenschutzbestimmungen</a></p></div>';
					$html .=  '<input type="checkbox" name="rpi_cloud_viewer" id="checkbox-1-1" class="regular-checkbox big-checkbox" /><label for="checkbox-1-1"></label> ';
					$html .=  '<p><input type="submit"  class="button" value="Dokument anzeigen"></p></form>';
					$html .=  '</div>';
				}
				return $html;
			}

			$file = wp_safe_remote_head($url);
			$disposition = $file['headers']->offsetGet('content-disposition');
			preg_match('/filename="([^"]+)"/',$disposition, $matches);
			$filename = urldecode($matches[1]);

			$html = "<ul><li><a href=\"$url\">$filename</a></li></ul>";

			return $html;
		}


		return $ctype;

	}

	function get_folder_tree($dir = '/'){

		$dir = Sabre\HTTP\encodePath($dir);

		try{

			// Get folder content
			$folder_content = $this->client->propFind('', array(
				'{DAV:}displayname',
				'{DAV:}getcontentlength',
				'{DAV:}getlastmodified',
				'{DAV:}getcontenttype',
			), 10);

		}catch (Exception $e){
			if($e->getCode()){
				return '<div class="rpicloud-wrapper">Die Freigabe Link für deinen Cloud-Ordner ist ungültig oder das Passwort ist falsch</div>';
			}
			return('<div class="rpicloud-wrapper">Fehler ' . $e->getCode() . ' ist aufgetreten: '. $e->getMessage() . '</div>');
		}

		if(count($folder_content) ==1 ){
			foreach ($folder_content as $key => $props);
			if(isset($props['{DAV:}getcontenttype'])){
				return array(
					'file'=>'fileshare',
					'props'=>$props
				);
			}
		}


		$root = new Cloud_Directory($dir);

		foreach ($folder_content as $key => $value) {

			if( strpos($key,$dir) === false ){
				continue;
			}else{

			}


			$name = str_replace($this->cfg->get_uriPrefix(), '', $key);

			if( strlen(substr($dir,1))>0){

				if( strpos($name, substr($dir,1)) == 0 ){

					$name = substr($name,strlen($dir)-1);

				}

			}

			$parts = explode('/', $name);


			$current = $root;
			$j = 0;
			foreach ($parts as $part) {
				if (empty($part)) {
					continue;
				}
				if (!(count($parts) - 1 == $j && isset($value['{DAV:}getcontenttype']))) {
					$current = $current->create_dir($part,$key);
				} else {
					$current->create_file($part, $key, $value);
				}
				$j++;
			}
		}

		return $root->print_tree($this->cfg->get_baseDir());


	}
}
