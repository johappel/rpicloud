<?php


class Cloud_Share {

	protected $auth;

	function __construct() {

		$this->user = base64_encode('nextcloud:(Z%inU6Pn0nxO&pcJ6fCiu$d');
	}

	function add_share($path = 'Documents/Test', $shareType = 3, $publicUpload = 'true', $password=''){
		$postData = array(
			'path' => $path,
			'shareType' => $shareType,
			'publicUpload' => $publicUpload,
			'password' =>  $password
		);

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://cloud.rpi-virtuell.de/ocs/v2.php/apps/files_sharing/api/v1/shares",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"path\"\r\n\r\nTest\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"shareType\"\r\n\r\n3\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"publicUpload\"\r\n\r\ntrue\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\ntest\r\n-----011000010111000001101001--\r\n",
			CURLOPT_COOKIE => "cookie_test=test; ocrvzaedddzo=bb81b9b9b42a79b02439f52c445b2799; oc_sessionPassphrase=jUpIaQ3L44Ky7mNyMLD8GpP3OtCXk9VpmbQeoaofXc115ELeNq9AWzOtjLSVKayz2lUDQWO3stV78VEqNwD3Q7PLqu2BHi3aBndE3dbXAiy%252FBdI5jP7G01WuTbgNSkPo; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true",
			CURLOPT_HTTPHEADER => [
				"Authorization: Basic ".$this->user,
				"Content-Type: multipart/form-data; boundary=---011000010111000001101001",
				"OCS-APIRequest: true"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}
	}

	protected function _get_form_field($key,$value){

		$pattern = "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n";
		return sprintf($pattern,$key,$value);
	}

}
