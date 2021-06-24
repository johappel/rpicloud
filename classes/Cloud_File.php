<?php


use function Sabre\HTTP\decodePath;

class Cloud_File {

	public $_name;
	public $_props;
	public $_key;
	protected $config;

	public function __construct($name,$props)
	{

		$this->_name = $name;
		$this->_props = $props;
		$this->config = Cloud_Config::get_Instance();
	}

	public function get_name(){
		return decodePath($this->_name);
	}

	public function get_type(){
		return $this->_props["{DAV:}getcontenttype"];
	}
	public function get_date(){
		return $this->_props["{DAV:}getlastmodified"];
	}
	public function get_size(){
		return $this->_props["{DAV:}getcontentlength"];
	}

	protected function get_short_key()
	{
		return str_replace($this->config->get_uriPrefix(), '', $this->_key);
	}

	public function get_link()
	{
		//https://github.com/erusev/parsedown
		$markdown = array();

		//'https://view.officeapps.live.com/op/embed.aspx?src='
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
			'application/vnd.visio',

		);
		$videos = array(

			'video/mp4',
			'video/ogg',

		);
		$audios = array(

			'audio/mpeg',
			'audio/ogg',

		);

		if ($this->config->is_mod_rewrite_is_enabled()) {



			//Link to Office Viewer
			if(in_array($this->get_type(),$videos)){

				$download_url =$this->config->get_transkey().'/'.$this->get_short_key() ;
				$download_url = Cloud_Core::$officeurl . $download_url.'?rpi_cloud_video='.$this->get_type();

			}elseif(in_array($this->get_type(),$audios)){

				$download_url =$this->config->get_transkey().'/'.$this->get_short_key() ;
				$download_url = Cloud_Core::$officeurl . $download_url.'?rpi_cloud_audio='.$this->get_type();

			}elseif(in_array($this->get_type(),$officedocs) && $this->config->is_allow_viewer()){

				$download_url =$this->config->get_transkey().'/'.$this->get_short_key() ;

				//$download_url = Cloud_Core::$officeurl . base64_encode(urlencode($download_url));
				$download_url = Cloud_Core::$officeurl . $download_url;

			}else{
				$download_url = Cloud_Core::$shorturl. $this->config->get_transkey().'/'.$this->get_short_key();

			}

			return $download_url;

		} else {
			return 'index.php?action=rpicloud_download&rpicloud_key='.$this->config->get_transkey().'&file=' . urlencode($this->get_short_key()) . '"';
		}

	}
}
