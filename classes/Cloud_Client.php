<?php


class Cloud_Client {

	protected $client;
	protected $cfg;

	function __construct($trans_key, $trans_value = false) {

		$this->cfg = new Cloud_Config($trans_key, $trans_value);

		$this->client = $this->cfg->get_client();

		return $this->client;

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
