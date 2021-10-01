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

	static function display_form($atts, $createDir = false){

		$slug =  $createDir? 'createdir':'upload';

		$atts= array_merge(array(
			'key' => '0',
			'startdir' => '/',
			'post_id'=>'',
			'tree_id' => 'tree1'
		),$atts);

		$tree_id = $atts['tree_id'];
		$startdir = $atts['startdir'];
		$post_id = $atts['post_id'];
		$transkey = $atts['key'];
		$txt_header = 'Neue Dateien hinzufügen';
		$txt_folder = 'Neuen Unterordner erzeugen';
		$txt_placeholder = 'Ordner Name';
		$txt_submit = $createDir? esc_html__('Ordner anlegen'):esc_html__('Hochladen');
		$checker = $createDir? 'createdir':'upload';
		$iconclass = $createDir? 'plus':'upload';

		$html = '';
		$html .= '<div cloud-handle-wrapper>';
		$html .= '<a href="#up" title="'. $txt_header .'" class="rpicloud-handle '.$slug.'" id="upload_'.$tree_id.'" onclick="rpicloud.show'.$slug.'_dialog(\''.$tree_id.'\')"><span title="'.$txt_submit.'" class="dashicons dashicons-'.$iconclass.'"></span></a>';
		$html .= '</div>';

		$html .= '<div id="'.$slug.'_'.$tree_id.'_container" class="cloud_'.$slug.'"><div class="rpicloud-wrapper">';

		$html .= '<form class="rpicld-form" method="POST" enctype="multipart/form-data" class="cloud_upload" onsubmit="return rpicloud.check'.$checker.'(\''.$tree_id.'\');">';

		if($createDir === true) {

			//New Directory
			$html .= '<p class="form-field cloud-createdir">';
			$html .= '<label>' . $txt_folder . '</label>';
			$html .= '<input type="text" name="newdir" id="cloud-createdir-' . $tree_id . '" class="cloud-upload-dir" value="" placeholder="' . $txt_placeholder . '">';
			$html .= '</p>';

		}else{
			//Fileupload
			$html .= '<p class="form-field" id="cloud-upload-field-' . $tree_id . '">';
			$html .= '<label for="cloud-upload-file-' . $tree_id . '" class="cloud-upload-file">';
			$html .= '<span class="cloud-upload-label">Datei wählen</span>';
			$html .= '<input type="file" id="cloud-upload-file-' . $tree_id . '" size="60" name="rpicld_file" onchange="this.parentNode.parentElement.style.border=\'none\'">';
			$html .= '</label>';
			$html .= '</p>';
		}

		//submit
		$html .= '<p class="form-field">';
		$html .=    '<input type="submit" name="submit_rpicld_form" class="button button-primary" value="' . $txt_submit  . '">';
		$html .= '</p>';




		//hidden fields
		$html .= '<input type="hidden" id="' . $tree_id . '-cloud-'.$slug.'-dir" name="dir" value="">';
		$html .= '<input type="hidden" id="' . $tree_id . '-cloud-'.$slug.'-nodekey" name="nodekey" value="">';
		$html .= '<input type="hidden" name="tree_id" value="'.$tree_id.'">';
		$html .= '<input id="' . $tree_id . '-cloud-'.$slug.'-startdir" type="hidden" name="startdir" value="'. $startdir .'">';
		$html .= '<input type="hidden"  class="cloud-username" name="form_user" value="">';


		//hidden fields
		$html .= '<input type="hidden" name="rpicloud_key" value="'. $transkey .'">';
		$html .= '<input type="hidden" name="id" value="' . $post_id . '">';
		// Output the nonce field
		$html .= wp_nonce_field( 'upload_rpicld_file', 'rpicld_nonce', true, false );
		$html .= '<input type="hidden" name="action" value="wp_handle_upload">';


		$html .= '</form>';
		$html .= '</div></div>';


		return $html;

	}

	static function handle_file_upload() {
		// Stop immidiately if form is not submitted
		if ( ! isset( $_POST['submit_rpicld_form'] ) ) {
			return;
		}
		if ( ! isset( $_POST['rpicloud_key'] ) ) {
			wp_die( esc_html__( 'Transient Key missing', 'theme-text-domain' ) );
		}
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['rpicld_nonce'], 'upload_rpicld_file' ) ) {
			wp_die( esc_html__( 'Nonce mismatched', 'theme-text-domain' ) );
		}

		// Throws a message if no file is selected
		if ( ! $_FILES['rpicld_file']['name'] && ! isset($_POST['newdir']) ) {
			wp_die( esc_html__( 'empty content', 'theme-text-domain' ) );
		}

		$username = Cloud_Helper::get_username('Anonymous');

		$file_size = $_FILES['rpicld_file']['size'];
		$allowed_file_size = 20480000; // Here we are setting the file size limit to 500 KB = 500 × 1024

		// Check for file size limit
		if ( $file_size >= $allowed_file_size ) {
			wp_die( sprintf( esc_html__( 'Dateigrößenlimit überschritten, Dateigröße sollte kleiner sein als %d KB', 'rpi cloud' ), $allowed_file_size / 1000 ) );
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$cfg = new Cloud_Config($_POST['rpicloud_key']);

		$overrides = array( 'action' => 'wp_handle_upload' );

		if(! isset($_POST['newdir'])){

			$file = $_FILES['rpicld_file'];

			$movefile     = wp_handle_upload( $file,$overrides);
			if(isset($movefile['error'])){
				wp_die($movefile['error']);
			}

			$c = file_get_contents($movefile['file']);


			$file_type = wp_check_filetype( $_FILES['rpicld_file']['name'] );
			$file_extension = $file_type['ext'];

			// Check for valid file extension
			if (!$cfg->get_allowedExtensions() && ! in_array( $file_extension, $cfg->get_allowedExtensions() ) ) {
				wp_die( sprintf(  esc_html__( 'Ungültige Dateierweiterung, nur erlaubt: %s', 'rpicloud' ), implode( ', ', $cfg->get_allowedExtensions() ) ) );
			}

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


		}else{
			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => $cfg->get_baseUri() . encodePath($uploaddir .$_FILES['rpicld_file']['name']),
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
		}


		$username = Cloud_Helper::get_username();


		$tree_no = intval(str_replace('tree','',$_POST['tree_id']))-1;

		$dirlabel = substr($uploaddir, strlen($_POST['startdir']));
		if($dirlabel){

			if(isset($_POST['nodekey']) && !empty($_POST['nodekey']) ){
				$keyparts = explode('_',$_POST['nodekey']);
				$key = intval($keyparts[1])+$tree_no;
				$key = $keyparts[0].'_'.strval($key);
			}else{
				$key = '';
			}


		}else{
			$key ='';
		}


		$event = new stdClass();
		$event->filename = $_FILES['rpicld_file']['name'];
		$event->uploaddir = $uploaddir;
		$event->dirlabel = $dirlabel;
		$event->fileurl = Cloud_Core::$shorturl .  $cfg->get_transkey() .'/'. encodePath( $uploaddir . $_FILES['rpicld_file']['name']);
		$event->username = $username;
		$event->date = date("j.n.Y H:i");
		$event->origin = get_permalink($_POST['id']).$key;


		$html = Cloud_Helper::get_activity_html($event,'upload');

		Cloud_Helper::write_log($html,$_POST['id'],  $_POST['tree_id']);



		wp_redirect(get_permalink($_POST['id']) .$key );

		die();
	}

	static function allow_myme_types( $mime_types ) {
		$mime_types['svg'] = 'image/svg+xml';     // Adding .svg extension
		$mime_types['json'] = 'application/json'; // Adding .json extension
		$mime_types['txt'] = 'text/plain'; // Adding .json extension

		return $mime_types;
	}

}

