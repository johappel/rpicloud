<?php



Class Cloud_Core{
	static $pluginurl;
	static $shorturl;
	static $plugindir;
	static $wpdir;
	static $officeurl;
	static $frameurl;


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

		$rel_pluginurl =substr(str_replace(home_url(),'',self::$pluginurl),1);
		add_rewrite_rule( '^cloud/([^/]*)/(.*)$', $rel_pluginurl.'download.php?rpicloud_key=$1&file=$2', 'bottom' );
		add_rewrite_rule( '^cloudview/([^/]*)$', $rel_pluginurl.'viewer.php?url=$1', 'bottom' );

		//rpicloud page generieren
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

		//number  the shortcodes in the posts
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
			'header-label'=> 'Neue Dateien hinzufügen',
			'folder-label'=> 'Datei in neuen Unterordner kopieren?',
			'folder-placeholder'=> 'Ordnername oder frei lassen',
			'confirm_delete'=> 'Möchtest du %name% wirklich löschen?',
			'allowed_extensions' => 'jpg,jpeg,png',
			'login-to-upload' => 'false',
			'only-login' => 'false'
		),   $atts, 'rpicloud' );



		$atts['upload'] = ($atts['upload']==1 || $atts['upload']=='true')?'true':'false';
		$atts['only-login'] = ($atts['only-login']==1 || $atts['only-login']=='true')?'true':'false';
		$atts['allow_delete'] = ($atts['allow_delete']==1 || $atts['allow_delete']=='true')?'true':'false';
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
		$option_key = self::add_key_to_options($atts, $post);

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

		// rpicloud file tree container
		$html  = '<div id="'.$tree_id.'-container" class="rpicloud rpicloud-container rpicloud-'.$tree_id.'">';

		// toolbar
		$html  .= '<div class="rpicloud rpicloud-toolbar"><span class="toolbar-username"></span></div>';

		if($atts['upload'] == 'true'){

			$html .= Cloud_Upload::display_userform();
			//section for activity log
			$html .= '<div id="' . $tree_id . '-cloud-log" class="rpicloud-log">';
			$html .= Cloud_Helper::include_log( $post->ID, $tree_id );
			$html .= '</div>'; //end log section


			//section file upload form
			$html .= Cloud_Upload::display_form(array(
				'key'=> $option_key,
				'dir'=> $start_dir,
				'prefix'=>$tree_id,
				'header-label'=> $txt_header,
				'folder-label'=> $txt_folder,
				'folder-placeholder'=> $txt_placeholder
			)); //end upload form section

			//section file delete confirmation
			if($atts['allow_delete'] == 'true') {
				$html .= Cloud_Delete::display_form( array(
					'transkey'=> $option_key,
					'tree_id'=>$tree_id,
					'confirm'=> $txt_confirm_delete,
					'post_id'=> $post->ID
				));
			} //end delete form section

			//toolbar button sho log section
			$html  .= '<a href="javascript:void(0)" onclick="rpicloud.togglelog(\''.$tree_id.'\')" class="rpicloud-handle log">';
			$html  .= '<span class="dashicons dashicons-clock"></span></a>';

		}

		//build file tree section with nexcloud client
		$html .= '<div id="'.$tree_id.'" class="tree">';
		$html .= $client->get_folder_tree($start_dir);
		$html .= '</div>'; //end file tree
		$html .= '</div>'; //end rpicloud file tree container

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

	static function getTemplate_dir(){

		return dirname(__DIR__).'/templates/';

	}



}
