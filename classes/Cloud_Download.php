<?php


use function Sabre\HTTP\encodePath;

class Cloud_Download {
	public static function display($transkey,$file){

		ini_set('display_errors', 1);
		error_reporting(E_ALL);

		# get path
		$cfg = Cloud_Config::get_Instance($transkey);

		$url = $cfg->get_baseUri() .'/'. \Sabre\HTTP\encodePath(urldecode($file));
		$parts    = explode( '/', $url );
		$filename = $parts[ count( $parts ) - 1 ];


		$temp = fopen('php://temp', 'rw+');
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_FILE => $temp,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_COOKIE => "ocrvzaedddzo=92320f3c34056c4ab98db971f5c747af; oc_sessionPassphrase=jUpIaQ3L44Ky7mNyMLD8GpP3OtCXk9VpmbQeoaofXc115ELeNq9AWzOtjLSVKayz2lUDQWO3stV78VEqNwD3Q7PLqu2BHi3aBndE3dbXAiy%252FBdI5jP7G01WuTbgNSkPo; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true",
			CURLOPT_HTTPHEADER => [
				"Authorization: Basic ". base64_encode($cfg->get_userName().':'.$cfg->get_password())
			],
		]);
		curl_exec($curl);

		if(!curl_errno($curl)) {


			$response = curl_getinfo( $curl );
			$ext      = strrchr( $file, '.' );
			if ( in_array( $ext, array( '.htm', '.html', '.xhtml', '.md', '.txt' ) ) ) {
				$response['content_type'] = 'text/html;charset=UTF-8';
			}

			status_header( 200 );
			//header('Content-Type: ' . $response['content_type']);
			header( 'Content-Disposition: inline;filename="' . $filename . '"' );
			header('Content-Transfer-Encoding: binary');
			header( 'Content-Length: ' . $response['download_content_length'] );
			header( 'Accept-Ranges: bytes' );


			if ( isset( $_GET['download'] ) ) {
				$response['content_type'] = 'application/download';
			}


			rewind( $temp );

			switch ( $response['content_type'] ) {
				case 'text/markdown':
				case 'text/markdown;charset=UTF-8':
					header('Content-Type: text/html');
					while (!feof($temp)) {
						$body .= fread($temp, (2<<20));
					}


					echo '<html><head><style>*{font-family:Arial} body{margin:2%}</style><title>'.$filename.'</title></head><body>';
					$Parsedown = new Parsedown();
					echo $Parsedown->text($body);
					echo '<hr><a href="?'.$_SERVER['QUERY_STRING'].'&download">Download</a>';
					echo '</body></html>';
					break;
				case 'text/plain;charset=UTF-8':
				case 'text/html;charset=UTF-8':
					$ext = strrchr( $file, '.' );
					if ( in_array( $ext, array( '.htm', '.html', '.xhtml' ) ) ) {
						header('Content-Type: text/html');
						while (!feof($temp)) {
							echo fread($temp, (2<<13));
							ob_flush();
						}
					} else {
						header('Content-Type: ' .$response['content_type']);
						ob_end_clean();
						while (!feof($temp)) {
							echo fread($temp, (2<<20));
							ob_flush();
						}
					}
					break;
				case 'application/internet-shortcut':
					while (!feof($temp)) {
						$body .= fread($temp, (2<<13));
					}
					$link = strchr($body,'http');
					curl_close($curl);
					fclose($temp);
					header('location: '.$link);
					die();
					break;
				default:
					header('Content-Type: ' .$response['content_type']);
					ob_end_clean();
					while (!feof($temp)) {
						echo fread($temp, (2<<13));
						ob_flush();
					}
			}
		}else{
			echo 'Fehler: ' . curl_error($curl);
		}
		curl_close($curl);
		fclose($temp);
		die();

	}

}
