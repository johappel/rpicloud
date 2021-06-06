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


		if(is_plugin_active( 'lazy-blocks/lazy-blocks.php' )){
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


		$home =  'https://' . $_SERVER['HTTP_HOST'] .'/rpicloud/?url='.$attributes['url'].':'.$attributes['password']. '&dir='.urlencode($attributes['dir']);
		$html  = '<div class="rpicloud rpicloud-container" style="border:1px solid #ddd">';
		$html .=  $upl;
		$html .=  '<iframe frameBorder="0" width="100%" height="100" src="'.$home.'"></iframe>';
		//$html .= '<script src="/wp-content/plugins/rpicloud/js/cloudframe.js"></script>';


		return $html;
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

			add_filter( 'lazyblock/nextcloud-tree/frontend_callback', array($this,'cloud_frontend_output'), 10, 2 );
			add_filter( 'lazyblock/nextcloud-tree/editor_callback', array($this,'cloud_editor_output'), 10, 2 );


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




	}  // end nextcloud-tree



	/* ---------------  */
}
new Cloud_Block();

