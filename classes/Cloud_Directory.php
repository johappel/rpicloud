<?php


use function Sabre\HTTP\decodePath;

class Cloud_Directory {

	public $_name = '';

	public $_dirs = [];
	public $_files = [];
	protected $key;

	public function __construct($name)
	{
		$this->_name = $name;

		$this->key = Cloud_Config::get_Instance()->get_transkey();
	}

	public function get_name(){
		return decodePath($this->_name);
	}

	public function create_dir($dir)
	{
		if (!isset($this->_dirs[$dir])) {
			$this->_dirs[$dir] = new Cloud_Directory($dir);
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

	public function print_tree()
	{
		$echo = '<ul>';
		foreach ($this->_dirs as $dir) {
			$echo .= '<li class="folder">' . $dir->get_name();
			$echo .= $dir->print_tree();
			$echo .= '</li>';
		}
		foreach ($this->_files as $file) {
			$echo .= '<li class="file" data-mimetype="'.$file->get_type().'"><a href="' . $file->get_link() . '">' . $file->get_name() . '</a></li>';
		}
		$echo .= '</ul>';
		return $echo;
	}
}
