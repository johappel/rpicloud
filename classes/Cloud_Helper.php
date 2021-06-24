<?php


class Cloud_Helper {

	static function get_username($username = null){
		if(is_user_logged_in()){
			$wp_user = wp_get_current_user();
			$username = $wp_user->display_name;
		}elseif(isset($_POST['form_user'])){
			$username = $_POST['form_user'];
			$arr_cookie_options = array (
				'path' => '/',
			);
			setcookie( 'rpicloud-user', $username,$arr_cookie_options);
		}elseif(isset($_COOKIE['rpicloud-user'])){
			$username = $_COOKIE['rpicloud-user'];
		}else{
			$username = ($username)? $username : '';
		}
		return $username;
	}


	static function get_activity_html(stdClass $event,$action){

		switch($action){
			case 'upload':

				if($event->dirlabel){
					$pattern = '[%s]: %s hat die Datei <a href="%s">%s</a> im Ordner <a href="%s">%s</a> hochgeladen';
					$html = sprintf ($pattern, $event->date,$event->username,$event->fileurl,$event->filename,$event->origin, $event->dirlabel);

				}else{
					$pattern = '[%s]: %s hat die Datei <a href="%s">%s</a> hochgeladen';
					$html = sprintf ($pattern, $event->date,$event->username,$event->fileurl,$event->filename);
				}

				break;
			case 'delete':

				if($event->dirlabel){
					$pattern = '[%s]: %s hat die Datei %s im Ordner <a href="%s">%s</a> gelöscht';
					$html = sprintf ($pattern, $event->date, $event->username,$event->filename,$event->origin, $event->dirlabel);
				}else{
					$pattern = '[%s]: %s hat die Datei <em>%s</em> gelöscht';
					$html = sprintf ($pattern, $event->date, $event->username,$event->filename);
				}
				break;
		}


		$html = apply_filters('rpi-cloud-activity', $html,$event ,$action);

		return $html;
	}

	static function struuid($entropy = false)
	{
		$s=uniqid("",$entropy);
		$num= hexdec(str_replace(".","",(string)$s));
		$index = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$base= strlen($index);
		$out = '';
		for($t = floor(log10($num) / log10($base)); $t >= 0; $t--) {
			$a = floor($num / pow($base,$t));
			$out = $out.substr($index,$a,1);
			$num = $num-($a*pow($base,$t));
		}
		return $out;
	}

	/**
	 * @param wp_post_id $post_id
	 * @param fancytree_id $tree_id
	 * @return file_name
	 */
	static function log_file($post_id = null, $tree_id = null){

		$wpdir = wp_upload_dir();
		$dir = $wpdir['basedir'] . '/rpicloud';
		if (! is_dir($dir)) {
			mkdir( $dir, 0700 );
		}

		if($post_id && $tree_id){
			$dir .= '/'.$post_id.'-'.$tree_id.'.log';
		}elseif($post_id){
			$dir .= '/'.$post_id.'.log';
		}else{
			$dir .= '/all.log';
		}

		if(!file_exists($dir)){

			$date = date("j.n.Y H:i");

			file_put_contents($dir,"<li>[$date]: Logfile wurde erzeugt.\n</li>");
		}
		return $dir;
	}
	/**
	 * @param wp_post_id $post_id int
	 * @param fancytree_id $tree_id string
	 * @return log-entry html string
	 */
	static function include_log($post_id = null, $tree_id = null){

		$file =self::log_file($post_id, $tree_id);
		$html = '<div class="rpicloud-log-wrapper">';
		$html .= '<ul>';
		$html .= file_get_contents($file);
		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * @param logentry $log
	 * @param wp_post_id $post_id
	 * @param fancytree_id $tree_id
	 */
	static function write_log( $log, $post_id = null, $tree_id = null ){

		$c = file_get_contents(self::log_file($post_id, $tree_id));
		$entry = '<li>'.$log."</li>\n".$c;
		file_put_contents( self::log_file($post_id, $tree_id), $entry );

		$c = file_get_contents(self::log_file($post_id));
		$entry = '<li>'.$log."</li>\n".$c;
		file_put_contents( self::log_file($post_id), $entry);

		$c = file_get_contents(self::log_file());
		$entry = '<li>'.$log."</li>\n".$c;
		file_put_contents( self::log_file(), $entry );

	}


	/**
	 * install depencies
	 */

	static function install_plugins()
	{
		$plugins = array(
			array(
				'name' => 'lazy-blocks',
				'url' => 'https://downloads.wordpress.org/plugin/lazy-blocks.2.3.1.zip',
				//'activate' => 'lazy-blocks/lazy-blocks.php',
			)
		);


		foreach($plugins as $plugin)
		{
			$pluginzip = WP_PLUGIN_DIR.'/'.$plugin['name'].'.zip';
			$success = self::plugin_download($plugin['url'], $pluginzip);
			if($success &&isset($plugin['activate'])){
				self::plugin_activate($plugin['activate']);
			}


		}
	}
	static function plugin_download($url, $pluginzip)
	{
		$response = wp_remote_get($url);
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			if(file_put_contents($pluginzip,$response['body'])) {

				$dir = unzip_file($pluginzip, WP_PLUGIN_DIR.'/');
				unlink($pluginzip);
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	static function plugin_activate($installer)
	{
		$current = get_option('active_plugins');
		$plugin = plugin_basename(trim($installer));

		if(!in_array($plugin, $current))
		{
			$current[] = $plugin;
			sort($current);
			do_action('activate_plugin', trim($plugin));
			update_option('active_plugins', $current);
			do_action('activate_'.trim($plugin));
			do_action('activated_plugin', trim($plugin));
			return true;
		}
		else
			return false;
	}

	/**
	 * @todo Dateien aus der der NC über MediaLibrary einfügen
	 */
	static function addTreeToMediaController(){

		//https://ibenic.com/extending-wordpress-media-uploader-custom-tab
		//https://atimmer.github.io/wordpress-jsdoc/wp.media.view.MediaFrame.Select.html

	}

}
