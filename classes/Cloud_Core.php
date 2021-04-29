<?php



Class Cloud_Core{
	static $pluginurl;
	static $plugindir;
	static $wpdir;

	static function init(){

		//oembedder
		wp_oembed_add_provider('https://cloud.rpi-virtuell.de/index.php/s/*', Cloud_Core::$pluginurl .'oembed.php', false);

		$embed_code = wp_oembed_get( 'https://cloud.rpi-virtuell.de/index.php/s/mmoyLdxnLXLQ34W' );
		//var_dump($embed_code);die();

		add_shortcode('rpicloud', array('Cloud_Core','rpicloud_dir'));

		Cloud_Upload::handle_file_upload();

		wp_enqueue_style('fancytree_style', self::$pluginurl.'/fancytree/skin-win8/ui.fancytree.min.css');
		wp_enqueue_style('cloud_style', self::$pluginurl.'/css/form.css');
		wp_enqueue_script('jquery', self::$pluginurl.'js/jquery-3.6.0.min.js');
		wp_enqueue_script('fancytree', self::$pluginurl.'fancytree/jquery.fancytree-all-deps.js');
		wp_enqueue_script('cloud', self::$pluginurl.'js/cloud.js');

		$rel_pluginurl =substr(str_replace(home_url(),'',self::$pluginurl),1);
		add_rewrite_rule( '^cloud/([^/]*)/(.*)$', $rel_pluginurl.'download.php?key=$1&file=$2', 'bottom' );
		add_rewrite_rule( '^cloudview/([^/]*)$', $rel_pluginurl.'viewer.php?url=$1', 'bottom' );


	}

	static function rpicloud_dir($atts)
	{
		global $post;

		//number  the shortcodes in the posts
		if(isset($post->nc)){
			$post->nc ++;
		}else{
			$post->nc =1;
		}
		$tree_id = 'tree'.$post->nc;

		$atts = shortcode_atts( array(
			'url' => 'https://cloud.rpi-virtuell.de/index.php/s/mmoyLdxnLXLQ34W',
			'password' => '',
			'username' => '',
			'dir' => '/',
			'upload' => true,
			'header-label'=> 'Neue Dateien hinzufügen',
			'folder-label'=> 'Datei in neuen Unterordner kopieren?',
			'folder-placeholder'=> 'Ordnername oder frei lassen',
			'allowed_extensions' => 'jpg,jpeg,png',
			'anonym-upload' => 'true'

		),   $atts, 'rpicloud' );

		$txt_header = $atts['header-label'];
		$txt_folder = $atts['folder-label'];
		$txt_placeholder = $atts['folder-placeholder'];

		$option_key = self::add_key_to_options($atts, $post);

		$option_value = serialize($atts);

		$client = new Cloud_Client($option_key, $option_value);

		$dir_arr = explode('/', $atts['dir']);
		$arr =array();
		foreach ($dir_arr as $part){
			if(!$part) continue;
			$arr[]=$part;
		}
		$start_dir = implode('/',$arr);
		if($start_dir){
			$start_dir = '/'.$start_dir.'/';
		}else{
			$start_dir = '/';
		}

		$tree_content = $client->get_folder_tree($start_dir);


		$html = Cloud_Helper::display_tree($post->nc, $tree_id, $tree_content);

		if($atts['upload'] == 'true'){
			$form = Cloud_Upload::display_form(array(
				'key'=> $option_key,
				'dir'=> $start_dir,
				'prefix'=>$tree_id,
				'header-label'=> $txt_header,
				'folder-label'=> $txt_folder,
				'folder-placeholder'=> $txt_placeholder
			));
		}else{
			$form = '';
		}

		return $html.$form;
	}

	static function rpicloud_frame($url, $pass='')
	{
		$tree_id = 'tree';

		$atts = array(
			'url' => $url,
			'password' => $pass,
			'dir' => '/',
		);

		$option_value = serialize($atts);

		$client = new Cloud_Client('frame', $option_value);



		$tree_content = $client->get_folder_tree($atts['dir']);


		$html = Cloud_Helper::display_tree(1, $tree_id, $tree_content);

		return $html;
	}

	static function add_key_to_options($atts = null, $post){

		$url = $atts['url'].'?'.$post->ID.'='.$post->nc;
		$value = serialize($atts);

		if(!$atts || !$atts['url']) return false;
		$option = unserialize(get_option('rpicloud'));

		if(isset($option[$url])){
			if($option[$url][1]!=$value){
				$key = $option[$url][0];
				$option[$url] = array($key, $value);
				update_option('rpicloud', $option);
				set_transient($key,$value);
			}else{
				$key = $option[$url][0];
			}
		}else{
			$key = Cloud_Helper::struuid();
			$option[$url] = array($key, $value);
			update_option('rpicloud', serialize($option));
			set_transient($key,$value);
		}

		return $key;
	}


}
