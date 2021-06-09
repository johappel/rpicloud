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

	function mm_get_plugins($plugins)
	{
		$args = array(
			'path' => ABSPATH.'wp-content/plugins/',
			'preserve_zip' => false
		);

		foreach($plugins as $plugin)
		{
			self::plugin_download($plugin['path'], $args['path'].$plugin['name'].'.zip');
			self::plugin_unpack($args, $args['path'].$plugin['name'].'.zip');
			if($plugin['activate']===true){
				self::plugin_activate($plugin['install']);
			}

		}
	}

	/**
	 * install depencies
	 */

	static function install_plugins()
	{
		$plugins = array(
			array(
				'name' => 'lazy-blocks',
				'path' => 'https://github.com/nk-crew/lazy-blocks/archive/refs/heads/master.zip',
				'install' => 'lazy-blocks/lazy-blocks.php',
				'activate' =>false
			)
		);


		$args = array(
			'path' => ABSPATH.'wp-content/plugins/',
			'preserve_zip' => false
		);

		foreach($plugins as $plugin)
		{
			self::plugin_download($plugin['path'], $args['path'].$plugin['name'].'.zip');
			self::plugin_unpack($args, $args['path'].$plugin['name'].'.zip');
			if($plugin['activate']===true){
				self::plugin_activate($plugin['install']);
			}

		}
	}
	static function plugin_download($url, $path)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		if(file_put_contents($path, $data))
			return true;
		else
			return false;
	}
	static function plugin_unpack($args, $target)
	{
		if($zip = zip_open($target))
		{
			while($entry = zip_read($zip))
			{
				$is_file = substr(zip_entry_name($entry), -1) == '/' ? false : true;
				$file_path = $args['path'].zip_entry_name($entry);
				if($is_file)
				{
					if(zip_entry_open($zip,$entry,"r"))
					{
						$fstream = zip_entry_read($entry, zip_entry_filesize($entry));
						file_put_contents($file_path, $fstream );
						chmod($file_path, 0777);
						//echo "save: ".$file_path."<br />";
					}
					zip_entry_close($entry);
				}
				else
				{
					if(zip_entry_name($entry))
					{
						mkdir($file_path);
						chmod($file_path, 0777);
						//echo "create: ".$file_path."<br />";
					}
				}
			}
			zip_close($zip);
		}
		if($args['preserve_zip'] === false)
		{
			unlink($target);
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

}
