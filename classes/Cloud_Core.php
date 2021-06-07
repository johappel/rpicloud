<?php



Class Cloud_Core{
	static $pluginurl;
	static $shorturl;
	static $plugindir;
	static $wpdir;
	static $officeurl;
	static $frameurl;
	static $officdoc;


	static function dispatch( $query ){

		$action = get_query_var('rpi_action', false);

		switch($action){
			case'rpicloud_download';
				$trans_key = get_query_var('rpicloud_key');
				$file = get_query_var('file');
				Cloud_Download::display($trans_key, $file);
			break;
			case'rpicloud_viewer';

				Cloud_Core::$officdoc = Cloud_Core::$shorturl.get_query_var('url');
				include_once (Cloud_Core::$plugindir.'viewer.php');
				die();
			break;
			default:

		}
	}

	static function add_query_vars($vars){

		array_push($vars, 'rpi_action','rpicloud_key','file','url');

		return $vars;

	}
	static function init(){


		//oembedder
		wp_oembed_add_provider('https://cloud.rpi-virtuell.de/index.php/s/*', self::$pluginurl .'oembed.php', false);

		$embed_code = wp_oembed_get( 'https://cloud.rpi-virtuell.de/index.php/s/mmoyLdxnLXLQ34W' );
		//var_dump($embed_code);die();

		add_shortcode('rpicloud', array('Cloud_Core','rpicloud_dir'));

		Cloud_Upload::handle_file_upload();
		Cloud_Delete::handle_delete();

		wp_enqueue_script('jquery', self::$pluginurl.'js/jquery-3.6.0.min.js',null,false,false);
		wp_enqueue_style('fancytree_style', self::$pluginurl.'fancytree/skin-win8/ui.fancytree.min.css');
		wp_enqueue_style('cloud_style', self::$pluginurl.'css/form.css');
		wp_enqueue_script('jquery', self::$pluginurl.'js/jquery-3.6.0.min.js');
		wp_enqueue_script('fancytree', self::$pluginurl.'fancytree/jquery.fancytree-all-deps.js');
		wp_enqueue_script('cloud', self::$pluginurl.'js/cloud.js',null,false,true);
		wp_enqueue_script('cloudframe', self::$pluginurl.'js/cloudframe.js');
		wp_enqueue_style( 'dashicons' );


		add_rewrite_rule( 'cloud/([^/]+)/(.*)$', 'index.php?rpi_action=rpicloud_download&rpicloud_key=$matches[1]&file=$matches[2]', 'top');
		add_rewrite_rule( 'cloudview/(.*)/?',  'index.php?rpi_action=rpicloud_viewer&url=$matches[1]', 'top' );

		if(is_admin()){
			flush_rewrite_rules(true);
		}

		//$rel_pluginurl =substr(str_replace(get_site_url( 1 ),'/',self::$pluginurl),1);
		//$rel_pluginurl =substr(str_replace(site_url(),'',self::$pluginurl),1);
		//var_dump($rel_pluginurl.'download.php');die();
		//add_rewrite_rule( '([_0-9a-zA-Z-]+/)?cloud/([^/]*)/(.*)', '$matches[1]'.$rel_pluginurl.'download.php?rpicloud_key=$matches[2]&file=$matches[3]', 'bottom' );
		//add_rewrite_rule( 'cloud/([^/]+)/(.*)$', $rel_pluginurl.'download.php?rpicloud_key=$matches[1]&file=$matches[2]', 'bottom' );
		//add_rewrite_rule( '^([_0-9a-zA-Z-]+/)?cloudview/([^/]*)$', '$matches[1]'.$rel_pluginurl.'viewer.php?url=$matches[2]', 'bottom' );

		//var_dump($rel_pluginurl);die();

		//add_rewrite_rule( 'cloud/([^/]+)/(.*)$', 'index.php?action=rpicloud_download&rpicloud_key=$matches[1]&file=$matches[2]', 'top');

		$args = array(
			'name'   => 'rpicloud',
			'post_type'   => 'page',
			'numberposts' => 1
		);
		$pages = get_posts($args);
		if($pages){
			$page = $pages[0];
			if($page->post_status != 'publish' ) {

				wp_update_post( array(
					'ID'          => $page->ID,
					'post_status' => 'publish'
				) );

			}elseif ($page->page_template != 'embed_tree.php'){

				update_post_meta( $page->ID, '_wp_page_template', 'embed_tree.php' );

			}
		}else{
			$args = array(
				'post_name'   => 'rpicloud',
				'post_type'   => 'page',
				'post_title'   => 'rpi-cloud (Seite zum automatischen Einbetten von nextcloud-ordnern)',
				'post_status'   => 'publish',
				'page_template'   => 'embed_tree.php',
			);
			$feedback = wp_insert_post($args);
			if(intval($feedback)>0){
				flush_rewrite_rules(true );
			}

		}


	}

	static function rpicloud_dir($atts)
	{
		global $post;

		if(!$post){
			return '';
		}

		//number  the rpicloud shortcodes in a posts
		if(isset($post->nc)){
			$post->nc ++;
		}else{
			$post->nc =1;
		}
		$tree_id = 'tree'.$post->nc;

		//read some params from sortcode

		$atts = shortcode_atts( array(
			'url' => 'https://cloud.rpi-virtuell.de/index.php/s/mmoyLdxnLXLQ34W',
			'password' => '',
			'username' => '',
			'dir' => '/',
			'upload' => 'false',
			'allow_delete' => 'false',
			'allow_createdir' => 'false',
			'allow_viewer' => 'false',
			'confirm_delete'=> 'Möchtest du %name% wirklich löschen?',
			'allowed_extensions' => 'jpg,jpeg,png',
			'login-to-upload' => 'false',
			'only-login' => 'false'
		),   $atts, 'rpicloud' );



		$atts['upload'] = ($atts['upload']==1 || $atts['upload']=='true')?'true':'false';
		$atts['only-login'] = ($atts['only-login']==1 || $atts['only-login']=='true')?'true':'false';
		$atts['allow_delete'] = ($atts['allow_delete']==1 || $atts['allow_delete']=='true')?'true':'false';
		$atts['allow_createdir'] = ($atts['allow_createdir']==1 || $atts['allow_createdir']=='true')?'true':'false';
		$atts['allow_viewer'] = ($atts['allow_viewer']==1 || $atts['allow_viewer']=='true')?'true':'false';
		$atts['login-to-upload'] = ($atts['login-to-upload']==1 || $atts['login-to-upload']=='true')?'true':'false';

		//var_dump($atts);

		if(!is_user_logged_in()){

			if($atts['only-login']==='true'){
				return '';
			}
			if($atts['login-to-upload']==='true'){
				$atts['upload'] = false;
			}

		}


		$txt_header = $atts['header-label'];
		$txt_folder = $atts['folder-label'];
		$txt_placeholder = $atts['folder-placeholder'];
		$txt_confirm_delete = $atts['confirm_delete'];

		//save the shortcode to options
		$option_key = self::add_key_to_postmeta($atts, $post);

		//set the sabre nextcloud client
		$option_value = serialize($atts);
		$client = new Cloud_Client($option_key, $option_value);

		// sanitize dirs
		$dir_arr = explode('/', $atts['dir']);
		$arr =array();
		foreach ($dir_arr as $part){
			if(!$part) continue;
			$arr[]=$part;
		}

		// define the root dir of the tree
		$start_dir = implode('/',$arr);
		if($start_dir){
			$start_dir = '/'.$start_dir.'/';
		}else{
			$start_dir = '/';
		}


		//now let's generate the output

		$attr = array(
			'key'=> $option_key,
			'dir'=> $start_dir,
			'tree_id'=>$tree_id,
			'post_id'=> $post->ID
		);

		// rpicloud file tree container
		$html  = '<div id="'.$tree_id.'-container" class="rpicloud rpicloud-container rpicloud-'.$tree_id.'">';

		// toolbar
		$html  .= '<div class="rpicloud rpicloud-toolbar" onclick="rpicloud.check_name();"><span class="dashicons dashicons-admin-users"></span><span class="toolbar-username"></span></div>';

		if($atts['upload'] == 'true'){

		$html .= Cloud_Upload::display_userform();
			//section for activity log
			$html .= '<div id="' . $tree_id . '-cloud-log" class="rpicloud-log">';
			$html .= Cloud_Helper::include_log( $post->ID, $tree_id );
			$html .= '</div>'; //end log section


			if($atts['allow_createdir'] == 'true') {
				$html .= Cloud_Upload::display_form($attr, true); //end upload form section
			}
			//section file upload form
			$html .= Cloud_Upload::display_form($attr); //end upload form section

			//section file delete confirmation
			if($atts['allow_delete'] == 'true') {
				$html .= Cloud_Delete::display_form( $attr );
			} //end delete form section

			//toolbar button sho log section
			$html  .= '<a href="javascript:void(0)" onclick="rpicloud.togglelog(\''.$tree_id.'\')" class="rpicloud-handle log">';
			//$html  .= '<span class="toolbar-icon history"></span></a>';
			$html  .= '<span class="dashicons dashicons-backup"></span></a>';
		}

		//build file tree section with nexcloud client
		$html .= '<div id="'.$tree_id.'" class="tree">';
		$html .= $client->get_folder_tree($start_dir);
		$html .= '</div>'; //end file tree
		$html .= '</div>'; //end rpicloud file tree container

		return $html;
	}



	static function add_key_to_postmeta($atts = null, $post){

		if(!$atts || !$atts['url']) return false;

		//make nextcloud url as unique id with params post_id and shortcode counter
		$url = $atts['url'].'?'.$post->ID.'='.$post->nc;
		$value = serialize($atts);

		$option = unserialize(Cloud_Config::get_postmeta('rpicloud'));

		if(isset($option[$url])){
			//update?
			if($option[$url][1]!=$value){
				//yes
				$key = $option[$url][0];
				$option[$url] = array($key, $value);
				Cloud_Config::update_postmeta('rpicloud', $option);
				set_transient($key,$value);
			}else{
				//no
				$key = $option[$url][0];
			}
		}else{
			//insert new
			$key = Cloud_Helper::struuid();
			$option[$url] = array($key, $value);
			Cloud_Config::update_postmeta('rpicloud', serialize($option));
			set_transient($key,$value);
		}

		return $key;
	}

	static function getTemplate_dir(){

		return dirname(__DIR__).'/templates/';

	}



}
