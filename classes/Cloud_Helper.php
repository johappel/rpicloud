<?php


class Cloud_Helper {


	static function display_tree($counter=1,$tree_id='tree1', $tree_content){

		$html = '<div id="'.$tree_id.'" class="tree">';
		$html .= $tree_content;
		$html .= '</div>';
		return $html ;
    }

	static function get_activity_html(stdClass $event){

		$pattern = '%s hat die Datei <a href="%s">%s</a> im Ordner <a href="%s">%s</a> am %s hochgeladen';

		$html = sprintf ($pattern, $event->username,$event->fileurl,$event->filename,$event->origin, $event->uploaddir ,$event->date);

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

}
