<?php


use function Sabre\HTTP\encodePath;

class Cloud_Delete {
	static function display_form($atts){

		$atts = array_merge(array(
			'key'=> '',
			'tree_id'=>'',
			'post_id'=>'',
			'startdir'=>'/',
			),
			$atts
		);

		$tree_id = $atts['tree_id'];
		$transkey = $atts['key'];
		$confirm = 'Möchtest du %name% wirklich löschen?';
		$post_id = $atts['post_id'];
		$startdir = $atts['startdir'];

		$form = '<a href="#del" class="rpicloud-handle delete" id="del_'.$tree_id.'" onclick="rpicloud.delete(this.id,\''.$confirm.'\')">';
		$form .= '<span title="Löschen" class="dashicons dashicons-no"></span>';
		//$form .= '<span title="Löschen" class="toolbar-icon delete"></span>';
		$form .= '</a>';

		$form .= '<div id="' . $tree_id . '-cloud-confirm-delete-message" class="cloud_del">';
		$form .= '<div class="rpicloud-wrapper">';

		$form .= '<form method="POST" enctype="multipart/form-data" action="?rpicloud_key='.$transkey.'">';

		$form .= '<input name="tree_id" value="'.$tree_id.'" type="hidden">';
		$form .= '<input name="post_id" value="'.$post_id.'" type="hidden">';
		$form .= '<input type="hidden" id="' . $tree_id . '-cloud-base-startdir" name="startdir" value="'.$startdir.'">';
		$form.=   wp_nonce_field( 'delete_rpicld_file', 'rpicld_nonce', true, false );

		//filled out on tree selecting
		$form .= '<input id="' . $tree_id . '-cloud-del-file" name="file" value="" type="hidden">';
		$form .= '<input id="' . $tree_id . '-cloud-del-nodekey" name="nodekey" value="" type="hidden">';
		$form .= '<input type="hidden" id="' . $tree_id . '-cloud-base-dir" name="dir" value="">';
		$form .= '<input class="cloud-username" name="form_user" value="" type="hidden">';

		$form .= '<p  class="cloud-confirm-delete-message"></p>';
		$form .= '<input type="submit" value="Löschen" class="button button-primary" name="delete_rpicld_file">';
		$form .= '<input type="button" class="button button-secondary" value="Abbrechen" onclick="jQuery(\'#' . $tree_id . '-cloud-confirm-delete-message\').hide()">';
		$form .= '</form>';

		$form .= '</div>';
		$form .= '</div>';

		return $form;

	}
	public static function handle_delete(){

		// Stop immidiately if form is not submitted
		if ( ! isset( $_POST['delete_rpicld_file'] ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['rpicloud_key'] ) ) {
			wp_die( esc_html__( 'Transient Key missing', 'theme-text-domain' ) );
		}
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['rpicld_nonce'], 'delete_rpicld_file' ) ) {
			wp_die( esc_html__( 'Nonce mismatched' ) );
		}
		if(! isset($_POST['file'])){
			wp_die( esc_html__( 'No File or Directory selected' ) );
		}

		# get path
		$cfg = Cloud_Config::get_Instance();

		$client = $cfg->get_client();

		$username = Cloud_Helper::get_username('Anonymous');

		$file = base64_decode($_POST['file']);


		$curl = curl_init();


		curl_setopt_array($curl, [
			CURLOPT_URL => $cfg->get_baseUri().$file,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "DELETE",
			CURLOPT_COOKIE => "ocrvzaedddzo=5956e0affcdacc3282111f567c36f5bb; oc_sessionPassphrase=KGdLwwyOekgYXA0dBPBrzdpbtlzb7gjBHZu8cc8es%252BiebBxcGGZkoAH4ItiG89ngsr6UTfFQBE9VdssPfG1uESBBjaqU%252BkC7wOVKqx5SUn5WfO30f21YeENi0u1kPtdx; __Host-nc_sameSiteCookielax=true; __Host-nc_sameSiteCookiestrict=true",
			CURLOPT_HTTPHEADER => [
				"Authorization: Basic ". base64_encode($cfg->get_userName().':'.$cfg->get_password())
			],
		]);


		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		$file = urldecode($file);
		$splitter = strrpos($file,'/');
		$dir = substr($file,0, $splitter);

		$dirlabel = substr($dir, strlen($_POST['startdir']));


		$filename = substr($file,$splitter+1);
		$event = new stdClass();
		$event->filename = $filename;
		$event->uploaddir = $dir;
		$event->dirlabel = $dirlabel;
		$event->fileurl = '';
		$event->username = $username;
		$event->date = date("j.n.Y H:i");
		$event->origin = get_permalink($_POST['post_id']).$_POST['nodekey'];

		$html = Cloud_Helper::get_activity_html($event, 'delete');

		Cloud_Helper::write_log($html, $_POST['post_id'],  $_POST['tree_id']);

		wp_redirect(get_permalink($_POST['post_id']) .$_POST['nodekey'] );

		die();


	}
}
