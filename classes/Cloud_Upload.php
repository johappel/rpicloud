<?php


use function Sabre\HTTP\encodePath;

class Cloud_Upload {


	/**
	 * Output the form.
	 *
	 * @param      array  $atts   User defined attributes in shortcode tag
	 */

	static function display_userform(){

		$html = '<div class="rpicloud-user"><div class="form-field user-wrapper">';

			$username = Cloud_Helper::get_username();
			if(is_user_logged_in()){
				$user = wp_get_current_user();
				$username = $user->display_name;
			}elseif(isset($_COOKIE['rpicloud-user'])){
				$username = $_COOKIE['rpicloud-user'];
			}

			$html .= '<label for="rpicloud-user">Name</label>';
			$html .= '<input type="text" id="rpicloud-user" name="form_user" value="'.$username.'" onchange="rpicloud.check_name()" placeholder="Dein Name">';

			$html .= '<p class="help-text">';

			$html .= 'Bevor du etwas hochladen oder löschen kannst, gib deinen Namen an. ';
			$html .= 'Alle Aktivitäten und Akteure werden in der ';
			$html .= '<a href="javascipt:void()" onclick="rpicloud.togglelog()">Zeitleiste</a> ';
			$html .= 'dieses Ordners angezeigt.';

			$html .= '</p>';


		$html .= '</div></div>';
		return $html;
	}

	static function display_form($atts, $notallowed=false){

		$atts= array_merge(array(
			'key' => '0',
			'dir' => '/',
			'prefix' => 'tree1',
			'header-label'=> 'Neue Dateien hinzufügen',
			'folder-label'=> 'Datei in neuen Unterordner kopieren?',
			'folder-placeholder'=> 'Ordnername oder frei lassen'

		),$atts);

		$tree_id = $atts['prefix'];
		$txt_header = $atts['header-label'];
		$txt_folder = $atts['folder-label'];
		$txt_placeholder = $atts['folder-placeholder'];
		$html = '';

		if( $notallowed === false ) {

			$html .= '<div cloud-handle-wrapper>';
			$html .= '<a href="#up" title="'. $txt_header .'" class="rpicloud-handle upload" id="upload_'.$tree_id.'" onclick="rpicloud.showupload_dialog(\''.$tree_id.'\')"><span title="Datei hochladen" class="dashicons dashicons-plus"></span></a>';
			$html .= '</div>';

			$html .= '<div id="upload_'.$tree_id.'_container" class="cloud_upload"><div class="rpicloud-wrapper">';

			$html .= '<form class="wpcfu-form" method="POST" enctype="multipart/form-data" class="cloud_upload" onsubmit="return rpicloud.checkupload(\''.$tree_id.'\');">';

			//Fileupload
			$html .= '<p class="form-field" id="cloud-upload-field-'.$tree_id.'">';
			$html .= '<label for="cloud-upload-file-'.$tree_id.'" class="cloud-upload-file">';
			$html .= '<span class="cloud-upload-label">Datei wählen</span>';
			$html .= '<input type="file" id="cloud-upload-file-'.$tree_id.'" size="60" name="wpcfu_file" onchange="this.parentNode.parentElement.style.border=\'none\'">';
			$html .= '</label>';
			$html .= '</p>';

			$html .= '<p class="form-field">';
			$html .= '<label>' . $txt_folder . '</label>';
			$html .= '<input type="text" name="newdir" class="cloud-upload-dir" value="" placeholder="' . $txt_placeholder . '">';
			$html .= '</p>';

		}

		if($notallowed === false){

			//submit
			$html .= '<p class="form-field">';
			$html .=    '<input type="submit" name="submit_wpcfu_form" class="button button-primary" value="' . esc_html__( 'Upload' ) . '">';
			$html .= '</p>';


		}


		//hidden fields
		$html .= '<input type="hidden" id="' . $tree_id . '-cloud-upload-dir" name="dir" value="">';
		$html .= '<input type="hidden" id="' . $tree_id . '-cloud-upload-nodekey" name="nodekey" value="_0">';
		$html .= '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		$html .= '<input id="' . $tree_id . '-cloud-upload-startdir" type="hidden" name="startdir" value="'. $atts['dir'] .'">';
		$html .= '<input type="hidden"  class="cloud-username" name="form_user" value="">';


		//hidden fields
		$html .= '<input type="hidden" name="rpicloud_key" value="'. $atts['key'] .'">';
		$html .= '<input type="hidden" name="id" value="' . get_the_ID() . '">';
		// Output the nonce field
		$html .= wp_nonce_field( 'upload_wpcfu_file', 'wpcfu_nonce', true, false );
		$html .= '<input type="hidden" name="action" value="wp_handle_upload">';


		$html .= '</form>';
		$html .= '</div></div>';


		return $html;

	}

	static function handle_file_upload() {
		// Stop immidiately if form is not submitted
		if ( ! isset( $_POST['submit_wpcfu_form'] ) ) {
			return;
		}
		if ( ! isset( $_POST['rpicloud_key'] ) ) {
			wp_die( esc_html__( 'Transient Key missing', 'theme-text-domain' ) );
		}
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['wpcfu_nonce'], 'upload_wpcfu_file' ) ) {
			wp_die( esc_html__( 'Nonce mismatched', 'theme-text-domain' ) );
		}

		// Throws a message if no file is selected
		if ( ! $_FILES['wpcfu_file']['name'] ) {
			wp_die( esc_html__( 'Please choose a file', 'theme-text-domain' ) );
		}

		$username = Cloud_Helper::get_username('Anonymous');

		$file_size = $_FILES['wpcfu_file']['size'];
		$allowed_file_size = 4096000; // Here we are setting the file size limit to 500 KB = 500 × 1024

		// Check for file size limit
		if ( $file_size >= $allowed_file_size ) {
			wp_die( sprintf( esc_html__( 'File size limit exceeded, file size should be smaller than %d KB', 'theme-text-domain' ), $allowed_file_size / 1000 ) );
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		//$files = array();

		$overrides = array( 'action' => 'wp_handle_upload' );

		$file = $_FILES['wpcfu_file'];
		$movefile     = wp_handle_upload( $file,$overrides);

		$c = file_get_contents($movefile['file']);


		$cfg = new Cloud_Config($_POST['rpicloud_key']);

		$file_type = wp_check_filetype( $_FILES['wpcfu_file']['name'] );
		$file_extension = $file_type['ext'];

		// Check for valid file extension
		if ( ! in_array( $file_extension, $cfg->get_allowedExtensions() ) ) {
			//	wp_die( sprintf(  esc_html__( 'Invalid file extension, only allowed: %s', 'theme-text-domain' ), implode( ', ', $allowed_extensions ) ) );
		}

		$uploaddir = isset($_POST['dir'])? $_POST['startdir'] . $_POST['dir'] : $_POST['startdir'];


		$arr = explode('/',$uploaddir);
		$dirs = [];
		foreach ($arr as $i=>$dir){
			if($dir){
				$dirs[]=$dir;
			}
		}
		$uploaddir = '/'.implode('/', $dirs). '/';
		$uploaddir =str_replace('//','/', $uploaddir);


		$create_dir = isset($_POST['newdir'])?str_replace('/','', $_POST['newdir']):false;

		if($create_dir){
			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => $cfg->get_baseUri() . encodePath($uploaddir.$create_dir),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "MKCOL",
				CURLOPT_POSTFIELDS => "",
				CURLOPT_COOKIE => "ocrvzaedddzo=5956e0affcdacc3282111f567c36f5bb; oc_sessionPassphrase=KGdLwwyOekgYXA0dBPBrzdpbtlzb7gjBHZu8cc8es%252BiebBxcGGZkoAH4ItiG89ngsr6UTfFQBE9VdssPfG1uESBBjaqU%252BkC7wOVKqx5SUn5WfO30f21YeENi0u1kPtdx; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true",
				CURLOPT_HTTPHEADER => [
					"Authorization: Basic ". base64_encode($cfg->get_userName().':'.$cfg->get_password())
				],
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				echo "cURL Error #:" . $err;
				die();
			} else {
				echo $response;
			}
			$uploaddir .= $create_dir.'/';


		}
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $cfg->get_baseUri() . encodePath($uploaddir .$_FILES['wpcfu_file']['name']),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $c,
			CURLOPT_COOKIE => "ocrvzaedddzo=5956e0affcdacc3282111f567c36f5bb; oc_sessionPassphrase=KGdLwwyOekgYXA0dBPBrzdpbtlzb7gjBHZu8cc8es%252BiebBxcGGZkoAH4ItiG89ngsr6UTfFQBE9VdssPfG1uESBBjaqU%252BkC7wOVKqx5SUn5WfO30f21YeENi0u1kPtdx; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true",
			CURLOPT_HTTPHEADER => [
				"Authorization: Basic ". base64_encode($cfg->get_userName().':'.$cfg->get_password())
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
			die();
		} else {
			echo $response;
		}

		unlink($movefile['file']);

		$username = Cloud_Helper::get_username();


		$tree_no = intval(str_replace('tree','',$_POST['tree_id']))-1;

		$dirlabel = substr($uploaddir, strlen($_POST['startdir']));
		if($dirlabel){

			$keyparts = explode('_',$_POST['nodekey']);
			$key = intval($keyparts[1])+$tree_no;
			$key = $keyparts[0].'_'.strval($key);
		}else{
			$key ='';
		}


		$event = new stdClass();
		$event->filename = $_FILES['wpcfu_file']['name'];
		$event->uploaddir = $uploaddir;
		$event->dirlabel = $dirlabel;
		$event->fileurl = Cloud_Core::$shorturl .  $cfg->get_transkey() .'/'. encodePath( $uploaddir . $_FILES['wpcfu_file']['name']);
		$event->username = $username;
		$event->date = date("j.n.Y H:i");
		$event->origin = get_permalink($_POST['id']).$key;


		$html = Cloud_Helper::get_activity_html($event,'upload');

		Cloud_Helper::write_log($html,$_POST['id'],  $_POST['tree_id']);



		wp_redirect(get_permalink($_POST['id']) .$key );

		die();
	}
}


