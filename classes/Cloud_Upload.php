<?php


use function Sabre\HTTP\encodePath;

class Cloud_Upload {


	/**
	 * Output the form.
	 *
	 * @param      array  $atts   User defined attributes in shortcode tag
	 */

	static function display_form($atts, $notallowed=false){

		$atts= array_merge(array(
			'key' => '0',
			'dir' => '/',
			'prefix' => 'tree1',
			'header-label'=> 'Neue Dateien hinzufügen',
			'folder-label'=> 'Datei in neuen Unterordner kopieren?',
			'folder-placeholder'=> 'Ordnername oder frei lassen'

		),$atts);

		$txt_header = $atts['header-label'];
		$txt_folder = $atts['folder-label'];
		$txt_placeholder = $atts['folder-placeholder'];

		if( $notallowed === false ) {

			$html = '<div class="cloud_upload">';


			$html .= '<details>';
			$html .= '<summary>' . $txt_header . '</summary>';
			$html .= '<form class="wpcfu-form" method="POST" enctype="multipart/form-data">';

			//Fileupload
			$html .= '<p class="form-field">';
			$html .= '<label for="cloud-upload-file" class="cloud-upload-file">';
			$html .= '<input type="file" id="cloud-upload-file" size="60" name="wpcfu_file">';
			$html .= 'Datei wählen';
			$html .= '</label>';
			$html .= '</p>';

			$html .= '<p class="form-field">';
			$html .= '<label>' . $txt_folder . '</label>';
			$html .= '<input type="text" name="newdir" class="cloud-upload-dir" value="" placeholder="' . $txt_placeholder . '">';
			$html .= '</p>';

		}
		if( $notallowed === false && !is_user_logged_in()) {

			$html .= '<p class="form-field">';
			$html .= '<label>Name</label>';
			$html .= '<input type="text" name="form_user" class="cloud-upload-dir" value="" placeholder="Dein Name">';
			$html .= '</p>';


		}

		if($notallowed === false){

			//submit
			$html .= '<p class="form-field">';
			$html .=    '<input type="submit" name="submit_wpcfu_form" value="' . esc_html__( 'Upload' ) . '">';
			$html .= '</p>';


		}

		if($notallowed === false || true) {

			//hidden fields
			$html .= '<input type="hidden" id="' . $atts['prefix'] . '-cloud-upload-dir" name="dir" value="">';
			$html .= '<input type="hidden" id="' . $atts['prefix'] . '-cloud-upload-nodekey" name="nodekey" value="_0">';
		}
		if( $notallowed === false && is_user_logged_in()) {
			$user = wp_get_current_user();
			$html .= '<input type="hidden" name="form_user" value="'. $user->display_name .'">';
		}

		if($notallowed === false) {

			//hidden fields
			$html .= '<input type="hidden" name="transkey" value="'. $atts['key'] .'">';
			$html .= '<input type="hidden" name="startdir" value="'. $atts['dir'] .'">';

			$html .= '<input type="hidden" name="id" value="' . get_the_ID() . '">';
			// Output the nonce field
			$html .= wp_nonce_field( 'upload_wpcfu_file', 'wpcfu_nonce', true, false );
			$html .= '<input type="hidden" name="action" value="wp_handle_upload">';


		}
		$html .= '</form>';
		$html .= '</details>';
		$html .= '</div>';

		return $html;

	}

	static function handle_file_upload() {
		// Stop immidiately if form is not submitted
		if ( ! isset( $_POST['submit_wpcfu_form'] ) ) {
			return;
		}
		if ( ! isset( $_POST['transkey'] ) ) {
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


		$cfg = new Cloud_Config($_POST['transkey']);

		$file_type = wp_check_filetype( $_FILES['wpcfu_file']['name'] );
		$file_extension = $file_type['ext'];

		// Check for valid file extension
		if ( ! in_array( $file_extension, $cfg->get_allowedExtensions() ) ) {
			//	wp_die( sprintf(  esc_html__( 'Invalid file extension, only allowed: %s', 'theme-text-domain' ), implode( ', ', $allowed_extensions ) ) );
		}

		$uploaddir = isset($_POST['dir'])?$_POST['startdir'].$_POST['dir']:'';
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

		$event = new stdClass();
		$event->filename = $_FILES['wpcfu_file']['name'];
		$event->uploaddir = $uploaddir;
		$event->fileurl = Cloud_Core::$pluginurl . $cfg->get_transkey() . encodePath( $uploaddir . $_FILES['wpcfu_file']['name']);
		$event->username = $_POST['form_user'];
		$event->date = date("j.n.Y H:i");
		$event->origin = get_permalink($_POST['id']).$_POST['nodekey'];

		$html = Cloud_Helper::get_activity_html($event);

		$html = apply_filters('rpicloud_upload', $html, $event);



		wp_redirect(get_permalink($_POST['id']) .$_POST['nodekey'] );

		die();
	}
}


