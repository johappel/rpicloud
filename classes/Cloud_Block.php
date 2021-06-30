<?php

/**
 * Class Cloud_Block
 *
 * stellt einen Gutenberg Block für die Dartstellung eines geteilten Nextcloudordners zur Verfügung.
 * benötigt die Bibliotheken des Plugins https://wordpress.org/plugins/lazy-blocks/
 *
 * Author: Joachim Happel
 */
class Cloud_Block {
	function __construct(){
		// Define path and URL to the LZB plugin.
		define( 'LZB_PATH', WP_PLUGIN_DIR . '/lazy-blocks/' );

		if(!file_exists(LZB_PATH . 'lazy-blocks.php')){

			function dependency_error() {
				$class = 'notice notice-error is-dismissible';
				//$message = __( 'rpicloud Error! The Gutenberg block "Nextcloud folder" requires the plugin Custom Blocks Constructor - Lazy Blocks. Please install it now. Activation is not necessary!', 'rpicloud' );
				$message = __( 'rpicloud Fehler! Für den Gutenberg Block "Nextcloud Ordner" wird das Plugin Custom Blocks Constructor - Lazy Blocks benötigt. Bitte installiere es jetzt. Aktivieren ist nicht notwendig!', 'rpicloud' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			}
			add_action( 'admin_notices', 'dependency_error' );

			return;
		}
		// Include the LZB plugin.
		require_once LZB_PATH . 'lazy-blocks.php';

		if ( function_exists( 'lazyblocks' ) ){
			add_action('init', array($this,'add_blocks'));
		}


		if (function_exists( 'is_plugin_active' ) && is_plugin_active( 'lazy-blocks/lazy-blocks.php' )){
			return;
		}

		//hide lazyblocks from admin_menu

		add_filter( 'lzb/show_admin_menu', '__return_false' );

		function remove_lazy_blocks_from_admin($args, $post_type){
			if ($post_type == 'lazyblocks'){
				$args['show_in_admin_bar'] = false;
			}
			return $args;
		}
		add_filter('register_post_type_args', 'remove_lazy_blocks_from_admin', 10, 2);

		function admin_style() {
			wp_enqueue_style('admin-styles', Cloud_Core::$pluginurl .  '/css/admin.css');
		}
		add_action('admin_enqueue_scripts', 'admin_style');



	}


	function cloud_editor_output($output, $attributes){

		$iup = '<span class="dashicons dashicons-upload"></span>';
		$ifo = '<span class="dashicons dashicons-plus"></span>';
		$idl = '<span class="dashicons dashicons-no"></span>';
		$ilg = '<span class="dashicons dashicons-backup"></span>';
		$ius = '<span class="dashicons dashicons-admin-users"></span>';

		$del = $attributes['allowdelete'] ? $idl :'';
		$createdir = $attributes['allowcreatedir'] ?  $ifo:'';
		$upl = $attributes['allowupload'] ?  '<div class="in-editor-toolbar">'.$ius.$createdir.$iup. $del . $ilg .'</div>':'';


		;

		$home =  get_home_url() .'/rpicloud/?url='.$attributes['url'].':'.$attributes['password']. '&dir='.urlencode($attributes['dir']);
		$html  = '<div class="rpicloud rpicloud-container" style="border:1px solid #ddd">';
		$html .=  $upl;
		$html .=  '<iframe frameBorder="0" width="100%" height="100" src="'.$home.'"></iframe>';
		$html .= '<script>console.log("'.$home.'");</script>';


		return $html;
	}
	function cloud_share_editor_output($output, $attributes){

		$output = '[rpicloud dir="/" '.
		          'url="' .$attributes['url'] . '"  '.
		          'password="' .$attributes['password']. '" '.
		          'allow_viewer="' .$attributes['allowviewer']. '" '.
		          'height="' .$attributes['height']. '" '.
		          ']';

		return do_shortcode($output);

	}

	function cloud_share_frontend_output($output, $attributes){
		$output = '[rpicloud dir="/" '.
			     'url="' .$attributes['url'] . '"  '.
			     'password="' .$attributes['password']. '" '.
			     'allow_viewer="' .$attributes['allowviewer']. '" '.
			     'height="' .$attributes['height']. '" '.
			     ']';

		return $output;
	}
	function cloud_frontend_output($output, $attributes){
		$output = '[rpicloud dir="/" '.
		          'url="' .$attributes['url'] . '"  '.
		          'password="' .$attributes['password']. '" '.
		          'dir="' .$attributes['dir']. '" '.
		          'upload="' .$attributes['allowupload']. '" '.
		          'allow_createdir="' .$attributes['allowcreatedir']. '" '.
		          'allow_delete="' .$attributes['allowdelete']. '" '.
		          'allow_viewer="' .$attributes['allowviewer']. '" '.
		          'login-to-upload="' .$attributes['onlyloggedin-upload']. '" '.
		          'only-login="' .$attributes['onlyloggedin']. '" '.
		          'allowed_extensions="' .$attributes['allowed_extensions']. '" '.
		          ']';

		return $output;
	}


	function add_blocks(){

		add_filter( 'lazyblock/nextcloud-tree/editor_callback', array($this,'cloud_editor_output'), 10, 2 );
		add_filter( 'lazyblock/nextcloud-share/editor_callback', array($this,'cloud_share_editor_output'), 10, 2 );

		add_filter( 'lazyblock/nextcloud-tree/frontend_callback', array($this,'cloud_frontend_output'), 10, 2 );
		add_filter( 'lazyblock/nextcloud-share/frontend_callback', array($this,'cloud_share_frontend_output'), 10, 2 );


		lazyblocks()->add_block( array(
			'id' => 403,
			'title' => 'Nectcloud Ordner',
			'icon' => '<svg width="100%" height="100%" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(0.286615,0,0,0.307602,-3.04461,-1.85648)"><path d="M50.35,71.5L50.351,75L37.925,75C37.461,75 37.016,74.815 36.688,74.487L36.513,74.312C36.185,73.984 36,73.54 36,73.075L36,30.55L19.909,30.55C18.174,30.55 17.1,28.875 17.1,27.322L17.1,14.357C17.1,12.643 18.334,11.299 19.909,11.299L20.6,11.299L20.6,9.684C20.6,8.011 21.793,6.75 23.376,6.75L31.259,6.75C33.483,6.75 33.871,7 34.347,8.255L34.432,11.168C34.492,11.224 34.637,11.294 34.711,11.3L46.894,11.3C48.465,11.3 49.65,12.614 49.65,14.358L49.65,27.323C49.65,29.133 48.439,30.551 46.894,30.551L39.5,30.551L39.5,42.8L50.35,42.8L50.351,46.3L39.5,46.3L39.5,71.5L50.35,71.5ZM20.6,27.05L46.15,27.05L46.15,14.8L34.703,14.8C33.369,14.8 31.915,14.974 31.614,13.667L31.517,10.372C31.485,10.341 31.326,10.258 31.246,10.249L24.101,10.249L24.101,11.945C24.101,13.519 22.694,14.799 20.965,14.799L20.6,14.799L20.6,27.05Z" style="fill:rgb(20,111,202);fill-rule:nonzero;"/></g><g transform="matrix(0.286615,0,0,0.307602,6.48563,5.30203)"><path d="M39.5,30.551L36,30.55L19.909,30.55C18.174,30.55 17.1,28.875 17.1,27.322L17.1,14.357C17.1,12.643 18.334,11.299 19.909,11.299L20.6,11.299L20.6,9.684C20.6,8.011 21.793,6.75 23.376,6.75L31.259,6.75C33.483,6.75 33.871,7 34.347,8.255L34.432,11.168C34.492,11.224 34.637,11.294 34.711,11.3L46.894,11.3C48.465,11.3 49.65,12.614 49.65,14.358L49.65,27.323C49.65,29.133 48.439,30.551 46.894,30.551L39.5,30.551ZM20.6,27.05L46.15,27.05L46.15,14.8L34.703,14.8C33.369,14.8 31.915,14.974 31.614,13.667L31.517,10.372C31.485,10.341 31.326,10.258 31.246,10.249L24.101,10.249L24.101,11.945C24.101,13.519 22.694,14.799 20.965,14.799L20.6,14.799L20.6,27.05Z" style="fill:rgb(20,111,202);fill-rule:nonzero;"/></g><g transform="matrix(0.286615,0,0,0.307602,6.48563,14.2609)"><path d="M39.5,30.551L36,30.55L19.909,30.55C18.174,30.55 17.1,28.875 17.1,27.322L17.1,14.357C17.1,12.643 18.334,11.299 19.909,11.299L20.6,11.299L20.6,9.684C20.6,8.011 21.793,6.75 23.376,6.75L31.259,6.75C33.483,6.75 33.871,7 34.347,8.255L34.432,11.168C34.492,11.224 34.637,11.294 34.711,11.3L46.894,11.3C48.465,11.3 49.65,12.614 49.65,14.358L49.65,27.323C49.65,29.133 48.439,30.551 46.894,30.551L39.5,30.551ZM20.6,27.05L46.15,27.05L46.15,14.8L34.703,14.8C33.369,14.8 31.915,14.974 31.614,13.667L31.517,10.372C31.485,10.341 31.326,10.258 31.246,10.249L24.101,10.249L24.101,11.945C24.101,13.519 22.694,14.799 20.965,14.799L20.6,14.799L20.6,27.05Z" style="fill:rgb(20,111,202);fill-rule:nonzero;"/></g></svg>',
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

				'control_b4a7254d4g' => array(
					'type' => 'toggle',
					'name' => 'allowviewer',
					'default' => '',
					'label' => 'Office Viewer',
					'help' => 'Office Dateien werden über den MS Office365 Viewer im Browser nach Zustimmung zur Datenschutzerklärung angezeigt.',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'checked' => 'false',
					'alongside_text' => 'MS Office Viewer verwenden',
					'placeholder' => '',
					'characters_limit' => '',
				),

				'control_467a294340' => array(
					'type' => 'toggle',
					'name' => 'allowupload',
					'default' => '',
					'label' => 'Upload erlauben',
					'help' => 'Voraussetzung ist, dass das Hochladen und Bearbeiten im Nextcloud Ordner aktiviert wurde',
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
				'control_98199f4341' => array(
					'type' => 'toggle',
					'name' => 'allowcreatedir',
					'default' => '',
					'label' => 'Neue Ordner erlauben',
					'help' => 'Upload muss erlaubt sein',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'checked' => 'false',
					'alongside_text' => 'Erstellen von neuen Ordnern erlauben',
					'placeholder' => '',
					'characters_limit' => '',
				),
				'control_981996462a' => array(
					'type' => 'toggle',
					'name' => 'allowdelete',
					'default' => '',
					'label' => 'Löschen erlauben',
					'help' => 'Upload muss erlaubt sein',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'checked' => 'false',
					'alongside_text' => 'Löschen von Dateien und Ordnern',
					'placeholder' => '',
					'characters_limit' => '',
				),
				'control_2309184dbf' => array(
					'type' => 'toggle',
					'name' => 'onlyloggedin',
					'default' => '',
					'label' => 'Login erforderlich',
					'help' => 'Nur angemeldete Nutzer sehen die Dateien',
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
					'help' => 'Upload muss erlaubt sein',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'checked' => 'false',
					'alongside_text' => 'Login für das Bearbeiten erzwingen',
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
				'editor_html' => '',
				'editor_callback' => array('',''),
				'editor_css' => '',
				'frontend_html' => '',
				'frontend_callback' => '',
				'frontend_css' => '',
				'show_preview' => 'always',
				'single_output' => false,
			),
			'condition' => array(
			),
		) );

		lazyblocks()->add_block( array(
			'id' => 404,
			'title' => 'Nextcloud Freigabe',
			'icon' => '<svg width="100%" height="100%" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"><g transform="matrix(0.283672,0,0,0.296115,-2.18681,-3.94906)"><path d="M46,19C34.077,19 24.168,27.395 21.688,38.594C13.841,40.535 8,47.561 8,56C8,65.937 16.063,74 26,74L28.563,74C28.597,80.842 28.756,85.825 28.756,85.825L71.763,85.848L71.856,73.721L74,74C83.937,74 92,64.937 92,55C92,47.925 87.854,42.905 81.906,39.969C81.367,32.192 74.91,26 67,26C65.872,26 64.814,26.263 63.75,26.5C59.215,21.907 52.951,19 46,19ZM46,23C52.204,23 57.75,25.645 61.594,29.906L62.469,30.875L63.688,30.5C64.723,30.175 65.848,30 67,30C73.099,30 78,34.901 78,41C78,41.068 78,41.105 78,41.125L77.969,42.5L79.25,43C84.388,45.065 88,49.072 88,55C88,62.791 81.791,70 74,70L71.969,70C71.969,70 72,64.328 72,64C72,57.729 72.101,41.918 72.101,41.918L28.442,42.207L28.418,69.836L26,70C18.209,70 12,63.791 12,56C12,49.025 17.03,43.34 23.656,42.219L25.063,41.969L25.281,40.563C26.919,30.599 35.55,23 46,23ZM50,46C59.965,46 68,54.035 68,64C68,73.965 59.965,82 50,82C40.035,82 32,73.965 32,64C32,54.035 40.035,46 50,46ZM44,53.5L44,74.469L59,65.719L61.969,64L59,62.25L44,53.5ZM48,60.469L54.031,64L48,67.5L48,60.469Z" style="fill:rgb(20,111,202);fill-rule:nonzero;"/></g></svg>',
			'keywords' => array(
				0 => 'Netxtcloud',
				1 => 'Fileshare',
				2 => 'Embed',
			),
			'slug' => 'lazyblock/nextcloud-share',
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
				'control_0c5bee435es' => array(
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
				'control_a4ab654d41s' => array(
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
				'control_b4a7254d4g' => array(
					'type' => 'toggle',
					'name' => 'allowviewer',
					'default' => '',
					'label' => 'Viewer',
					'help' => '',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'checked' => 'false',
					'alongside_text' => 'Dokumente einbetten',
					'placeholder' => '',
					'characters_limit' => '',
				),
				'control_55c9114578s' => array(
					'type' => 'range',
					'name' => 'height',
					'default' => '970',
					'label' => 'Höhe',
					'help' => '',
					'child_of' => '',
					'placement' => 'inspector',
					'width' => '100',
					'hide_if_not_selected' => 'false',
					'save_in_meta' => 'false',
					'save_in_meta_name' => '',
					'required' => 'false',
					'min' => '150',
					'max' => '1200',
					'step' => '10',
					'placeholder' => '',
					'characters_limit' => '',
				),
			),
			'code' => array(
				'output_method' => 'php',
				'editor_html' => '',
				'editor_callback' => array('',''),
				'editor_css' => '',
				'frontend_html' => '',
				'frontend_callback' => '',
				'frontend_css' => '',
				'show_preview' => 'always',
				'single_output' => false,
			),
			'condition' => array(
			),
		) );




	}  // end nextcloud-tree



	/* ---------------  */
}
new Cloud_Block();

