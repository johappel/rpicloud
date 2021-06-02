<?php


use function Sabre\HTTP\encodePath;

class Cloud_Download {
	public static function display(){

		# get path
		$cfg = Cloud_Config::get_Instance();


		$client = $cfg->get_client();

		$file =  $cfg->get_uriPrefix().urldecode($_GET['file']);

		$file = encodePath($file);

		# load file
		$response = $client->request('GET', $file);

		if(isset($_GET['download'])){
			$response['headers']['content-type'][0]='application/download';
		}

//		var_dump($response['headers']['content-type'][0]);
//		var_dump($response['body']);die();

		switch($response['headers']['content-type'][0]){
			case 'text/markdown':
			case 'text/markdown;charset=UTF-8':
				self::markdown($response, $file);
				break;
			case 'text/plain;charset=UTF-8':
			case 'text/html;charset=UTF-8':
				$ext =strrchr($file,'.');
				if(in_array($ext,array('.htm','.html','.xhtml') )){
					self::html($response, $file);
				}else{
					self::sendfile($response, $file);
				}
				break;
			case 'application/internet-shortcut':
				$link = strchr($response['body'],'http');
				header('location: '.$link);
				die();
				break;
			default:
				self::sendfile($response, $file);
		}



	}
	static function sendfile($response,$file){
		# get file name
		$parts = explode('/', $file);
		$filename = $parts[count($parts) - 1];
		status_header(200);
		header('Content-Type: ' . $response['headers']['content-type'][0]);
		header('Content-Disposition: inline;filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $response['headers']['content-length'][0]);
		header('Accept-Ranges: bytes');
		echo $response['body'];
		die();
	}

	static function markdown($response,$file){
		# get file name
		$parts = explode('/', $file);
		$filename = $parts[count($parts) - 1];
		status_header(200);
		header('Content-Type: text/html');
		header('Content-Disposition: inline;filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $response['headers']['content-length'][0]);
		header('Accept-Ranges: bytes');

		echo '<html><head><style>*{font-family:Arial} body{margin:2%}</style><title>'.$filename.'</title></head><body>';
		$Parsedown = new Parsedown();
		echo $Parsedown->text($response['body']);
		echo '<hr><a href="?'.$_SERVER['QUERY_STRING'].'&download">Download</a>';
		echo '</body></html>';
		die();

	}
	static function html($response,$file){
		# get file name
		$parts = explode('/', $file);
		$filename = $parts[count($parts) - 1];
		status_header(200);
		header('Content-Type: text/html');
		header('Content-Disposition: inline;filename="' . $filename . '"');
		//header('Content-Transfer-Encoding: binary');
		header('Content-Length: text/html;charset=UTF-8');
		header('Accept-Ranges: bytes');

		echo $response['body'];
		die();

	}
}
