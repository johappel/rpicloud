<?php


class Cloud_Config {

	protected $transkey;
	protected $baseUri                  ='https://cloud.rpi-virtuell.de/public.php/webdav';
	protected $userName                 = 'mmoyLdxnLXLQ34W';
	protected $password                 = '';
	protected $mod_rewrite_is_enabled   = true;
	protected $uri_prefix               = '/public.php/webdav/';
	protected $publicUri                = 'https://cloud.rpi-virtuell.de/s/';
	protected $allowedExtensions         = 'jpg,png,gif';


	static $objInstance;


	public function __construct($trans_key, $trans_value = false) {

		if($trans_value !== false){

			$atts = unserialize($trans_value);

		}else{
			$conf = get_transient($trans_key);

			if(!$conf){
				$this->restore_from_options($trans_key);
			}

			$atts = unserialize($conf);
		}

		$url = $atts['url'];
		$user = $atts['username'];
		$passwd = $atts['password'];

		$this->transkey = $trans_key;


		$u = parse_url($url);
		if(!isset($u['host']) || !isset($u['scheme'])){
			return;
		}

		if(strpos($url, '/s/')>0){

			//public shared
			$this->set_publicUri($u['scheme'].'://'.$u['host'].'/s/');
			$this->set_uriPrefix('/public.php/webdav/');
			$this->set_baseUri($u['scheme'].'://'.$u['host'].'/public.php/webdav');
			$userName = substr( strrchr($u['path'],'/'), 1);
			$this->set_userName($userName);

		}else{

			//remote webdav
			$this->set_userName($user);
			$this->set_baseUri($u['scheme'].'://'.$u['host'].'/remote.php/dav/files/'.$user.'/');
			$this->set_uriPrefix('/remote.php/dav/files/'.$user.'/');
			//$this->set_publicUri($u['scheme'].'://'.$u['host'].'/apps/files/');
		}

		$this->set_password($passwd);

		$this->set_allowedExtensions($atts['allowed_extensions']);

		self::$objInstance = $this;

	}
	public function get_userName(){
		return $this->userName;
	}

	public function get_allowedExtensions(){
		return explode(',', $this->allowedExtensions);
	}
	public function get_baseUri(){
		return $this->baseUri;
	}
	public function get_password(){
		return $this->password;
	}
	public function get_mod_rewrite_is_enabled(){
		return $this->mod_rewrite_is_enabled;
	}
	public function get_uriPrefix(){
		return $this->uri_prefix;
	}
	public function get_publicUri(){
		return $this->publicUri;
	}
	public function get_transkey(){
		return $this->transkey;
	}

	public function client_settings(){
		return array(
			'baseUri'   =>  $this->get_baseUri(),
			'userName'  =>  $this->get_userName(),
			'password'  =>  $this->get_password()
		);
	}

	public static function get_Instance(){

		if (self::$objInstance == null && isset($_GET['key'])) {
			self::$objInstance = new static($_GET['key']);
		}
		return self::$objInstance;

	}

	public function set_baseUri($uri){
		$this->baseUri = $uri;
	}
	public function set_uriPrefix($prefix){
		$this->uri_prefix = $prefix;
	}

	public function set_publicUri($publicUri){
		$this->publicUri = $publicUri;
	}
	public function set_userName($userName){
		$this->userName = $userName;
	}
	public function set_password($password){
		$this->password = $password;
	}
	public function set_key($param){
		$this->transkey = $param;
	}
	public function set_allowedExtensions($param){


		$exts = [];
		$arr = explode(',', $param);
		foreach($arr as $ext){
			$exts[] = trim($ext);
		}
		$this->allowedExtensions = implode(',',$exts);

	}

	public function get_client(){
		return new \Sabre\DAV\Client($this->client_settings());
	}
	protected function restore_from_options($key){

		$entrys = (array) get_option('rpicloud');

		foreach ($entrys  as $item ) {

			if($item[0] == $key){
				return $item[1];
			}

		}

		return false;

	}

}
