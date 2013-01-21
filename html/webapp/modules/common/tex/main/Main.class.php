<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Tex数式画像を出力
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Tex_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $c = null;
	var $s = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	//$c = escapeshellcmd($this->c);
    	$c = $this->c;

    	switch ($this->s) {
    		case "Large":
    		case "large":
    		case "small":
    			$s = "\\".$this->s;
    			break;
    		case "n":
    			$s = "\\normalsize";
    			break;
    		default:
    			$s = "\Large";
    	}

		$mimetex_path = MODULE_DIR . "/common/tex/mimetex/";

		if (substr(PHP_OS, 0, 3) == 'WIN') {
			$mimetex = $mimetex_path . "mimetex.exe";
		} else {
			$mimetex = $mimetex_path . "mimetex.cgi";
		}

		if (function_exists("is_executable")) {
			// is_executableがWindows上ではver5.0.0において使用可能になった為
			if (!is_executable($mimetex)) {
				return 'success';
			}
		} else {
			if (!file_exists($mimetex)) {
				return 'success';
			}
		}

		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: image/gif");
		header("Content-Disposition: attachment; filename=\"".md5($c).".gif\"");
		header("Content-Transfer-Encoding: Binary");

		$c = rawurldecode(str_replace("%_", "%", $c));
		passthru($mimetex.' -d '. escapeshellarg($s." ".$c));

		return 'success';
    }
}
?>