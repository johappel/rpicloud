<?php



Class Cloud_Core{
	static $pluginurl;
	static $shorturl;
	static $plugindir;
	static $wpdir;
	static $officeurl;
	static $frameurl;

	static function setup_lazzy_blocks(){


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



		$atts['upload'] = ($atts['upload']==1)?'true':'false';
		$atts['only-login'] = ($atts['only-login']==1)?'true':'false';
		$atts['allow_delete'] = ($atts['allow_delete']==1)?'true':'false';
		$atts['login-to-upload'] = ($atts['login-to-upload']==1)?'true':'false';

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

	static function add_block() {

		if ( function_exists( 'lazyblocks' ) ) :

			lazyblocks()->add_block( array(
				'id' => 403,
				'title' => 'Nectcloud Ordner',
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z" /></svg>',
				'keywords' => array(
					0 => 'Cloud',
					1 => ' Folder',
					2 => 'Filetree',
				),
				'slug' => 'lazyblock/nextcloud-tree',
				'description' => '',
				'category' => 'embed',
				'category_label' => 'embed',
				'supports' => array(
					'customClassName' => true,
					'anchor' => false,
					'align' => array(
						0 => 'wide',
						1 => 'full',
					),
					'html' => false,
					'multiple' => true,
					'inserter' => true,
				),
				'ghostkit' => array(
					'supports' => array(
						'spacings' => false,
						'display' => false,
						'scrollReveal' => false,
						'frame' => false,
						'customCSS' => false,
					),
				),
				'controls' => array(
					'control_0c5bee435e' => array(
						'type' => 'url',
						'name' => 'url',
						'default' => '',
						'label' => 'Url',
						'help' => '',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'true',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_a4ab654d41' => array(
						'type' => 'text',
						'name' => 'password',
						'default' => '',
						'label' => 'Passwort',
						'help' => 'Das Passwort, welches zum Anzeigen deiner Nextcloud Freigabe erforderlich ist.',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_1c4b844e93' => array(
						'type' => 'text',
						'name' => 'dir',
						'default' => '/',
						'label' => 'Root',
						'help' => 'Startverzeichnis innerhalb deines Ordners',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => '/Untergerordneter Ordner/Anzuzeigender Ordner/',
						'characters_limit' => '',
					),
					'control_467a294340' => array(
						'type' => 'toggle',
						'name' => 'allowupload',
						'default' => '',
						'label' => 'Upload erlaubt',
						'help' => 'Voraussetzung ist, dass du das Hochladen und bearbeiten von Dateien in dem Nextcloud Ordner aktiviert hast',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'checked' => 'false',
						'alongside_text' => 'Erlaube Uploads',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_981996462a' => array(
						'type' => 'toggle',
						'name' => 'allowdelete',
						'default' => '',
						'label' => 'Löschen erlaubt',
						'help' => 'Voraussetzung ist, dass du das Hochladen und bearbeiten von Dateien in dem Nextcloud Ordner aktiviert hast',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'checked' => 'false',
						'alongside_text' => 'Erlaube Löschen von Dateien und Ordnern',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_2309184dbf' => array(
						'type' => 'toggle',
						'name' => 'onlyloggedin',
						'default' => '',
						'label' => 'Login erforderlich',
						'help' => 'Voraussetzung ist, dass du das Hochladen und bearbeiten von Dateien in dem Nextcloud Ordner aktiviert hast',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'checked' => 'false',
						'alongside_text' => 'Nur eingeloggte User dürfen Ordner und Dateien sehen',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_4d7a0541b4' => array(
						'type' => 'toggle',
						'name' => 'onlyloggedin-upload',
						'default' => '',
						'label' => 'Login für Uploads erforderlich',
						'help' => 'Voraussetzung ist, dass du das Hochladen und bearbeiten von Dateien in dem Nextcloud Ordner aktiviert hast',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'checked' => 'false',
						'alongside_text' => 'Login für Bearbeiten erzwingen',
						'placeholder' => '',
						'characters_limit' => '',
					),
					'control_a1c9b44bbf' => array(
						'type' => 'text',
						'name' => 'allowed_extensions',
						'default' => '',
						'label' => 'Erlaubte Dateiendungen',
						'help' => 'Leer lassen um alles den Upload aller dateien zu erlauben',
						'child_of' => '',
						'placement' => 'inspector',
						'width' => '100',
						'hide_if_not_selected' => 'false',
						'save_in_meta' => 'false',
						'save_in_meta_name' => '',
						'required' => 'false',
						'placeholder' => 'jpg,jpeg,png',
						'characters_limit' => '',
					),
				),
				'code' => array(
					'output_method' => 'php',
					'editor_html' => '<?php
    $del = $attributes[\'allowdelete\'] ?    \' x |\':\'\';
    $upl = $attributes[\'allowupload\'] ?    \'<div class="rpicloud rpicloud-toolbar">| + |\'. $del . \' o | &nbsp; </div>\':\'\';
    
    $home =    \'https://\' . $_SERVER[\'HTTP_HOST\'] .\'/rpicloud/?url=\'.$attributes[\'url\'].\':\'.$attributes[\'password\'];
    $html    = \'<div class="rpicloud rpicloud-container" style="border:1px solid #ddd">\';
    $html .=    $upl;
    $html .=    \'<iframe frameBorder="0" width="100%" height="100" src="\'.$home.\'"></iframe>\';
    $html .= \'<script src="/wp-content/plugins/rpicloud/js/cloudframe.js"></script>\';
    echo $html;
    ?>',
					'editor_callback' => '',
					'editor_css' => '',
					'frontend_html' => '<?php 
    echo \'[rpicloud dir="/" \'.
    \'url="\' .$attributes[\'url\'] . \'"    \'.
    \'password="\' .$attributes[\'password\']. \'" \'.
    \'dir="\' .$attributes[\'dir\']. \'" \'.
    \'upload="\' .$attributes[\'allowupload\']. \'" \'.
    \'allow_delete="\' .$attributes[\'allowdelete\']. \'" \'.
    \'login-to-upload="\' .$attributes[\'onlyloggedin-upload\']. \'" \'.
    \'only-login="\' .$attributes[\'onlyloggedin\']. \'" \'.
    \'allowed_extensions="\' .$attributes[\'allowed_extensions\']. \'" \'.
    \']\';
    
    ?>',
					'frontend_callback' => '',
					'frontend_css' => '',
					'show_preview' => 'always',
					'single_output' => false,
				),
				'condition' => array(
				),
			) );

		endif;
	}

}
