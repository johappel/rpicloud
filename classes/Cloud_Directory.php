<?php


use function Sabre\HTTP\decodePath;

class Cloud_Directory {

	public $_name = '';

	public $_dirs = [];
	//public $_folders = [];
	public $_files = [];
	//protected $key;
	protected $cfg;


	public function __construct($name)
	{
		$this->_name = $name;

		$this->cfg = Cloud_Config::get_Instance();

		if($this->cfg === null){
			$this->cfg = new Cloud_Config(false);
		}

		//$this->key = $this->cfg->get_transkey();
	}

	public function get_name(){
		return decodePath($this->_name);
	}

	public function create_dir($dir,$key)
	{
		if (!isset($this->_dirs[$dir])) {
			$this->_dirs[$dir] = new Cloud_Directory($dir);
			$this->_dirs[$dir]->_key=$key;
		}
		return $this->_dirs[$dir];
	}

	public function add_dir($dir)
	{
		if (!in_array($dir, $this->_dirs)) {
			array_push($this->_dirs, $dir);
		}
		return $dir;
	}

	public function create_file($file, $key = null, $props)
	{

		if (!isset($this->_files[$file])) {
			$this->_files[$file] = new Cloud_File($file, $props);
			$this->_files[$file]->_key = $key;
		}
		return $this->_files[$file];
	}

	public function add_file($file)
	{
		if (!in_array($file, $this->_files)) {
			array_push($this->_files, $file);
		}
	}

	public function print_tree($currdir = '/')
	{

		$echo = '<ul>';
		foreach ($this->_dirs as $dir) {

			$currdir .= $dir->get_name().'/';

			$keydata = '';
			if($this->cfg->is_allow_del()){
				$start = strlen($this->cfg->get_uriPrefix())-1;
				$keydata = substr($dir->_key,$start);
				$keydata = base64_encode($keydata);
				$keydata = ' data-file="'.$keydata.'"';
			}
			$echo .= "\n".'<li class="folder"'.$keydata.'>' . $dir->get_name();
			$echo .= $dir->print_tree($currdir);
			$echo .= '</li>';
		}
		foreach ($this->_files as $file) {

			$keydata = '';
			if($this->cfg->is_allow_del()){
				$start = strlen($this->cfg->get_uriPrefix())-1;
				$keydata = substr($file->_key,$start);
				$keydata = base64_encode($keydata);
				$keydata = ' data-file="'.$keydata.'"';
			}
			$echo .= '<li class="file" data-mimetype="'.$file->get_type().'"'.$keydata .'><a href="' . $file->get_link() . '">' . $file->get_name() . '</a></li>';
		}
		$echo .= '</ul>';
		return $echo;
	}
}
