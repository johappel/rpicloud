<?php


class Cloud_Download {
	public static function display(){

		# get path
		$cfg = Cloud_Config::get_Instance();


		$client = $cfg->get_client();

		$file =  $cfg->get_uriPrefix().urldecode($_GET['file']);

		$file = \Sabre\HTTP\encodePath($file);

		# load file
		$response = $client->request('GET', $file);

		//var_dump($response['headers']['content-type'][0]);die();


		self:self::sendfile($response, $file);



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
}
