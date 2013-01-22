<?php
/**
 * 共通関数
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

/**
 * タイムゾーンの計算処理
 * 登録時：第一引数が入っていれば画面から取得したものとみなして、_default_TZから引く
 * 　　　　第一引数がnullならば、_server_TZから引く
 * 表示時：GMTから会員のタイムゾーンを足す
 * @param  string time(YmdHis or Hisの形式)
 * @param  boolean insert_flag(登録、更新時かどうか) default:true
 * @param  string  format default:date("YmdHis")
 * @return string timezone str
 * @access	public
 */
function timezone_date($time = null, $insert_flag = true, $format = null) {
	$container =& DIContainerFactory::getContainer();
	$session =& $container->getComponent("Session");
	$getdata =& $container->getComponent("GetData");
	$config = $getdata->getParameter("config");
	//if($session->getParameter("_user_id") == "0") {
	//	// ログイン前
	//	$_default_TZ = $config[_GENERAL_CONF_CATID]['default_TZ']['conf_value'];
	//} else {
	//	// ログイン後
		$_default_TZ = $session->getParameter("_timezone_offset");
	//}
	$time_null_flag = false;
	if ($time === null) {
		$time_null_flag = true;
		if ($insert_flag) {
			$time = date("YmdHis");
		} else {
			$timezone_offset = $session->getParameter("_server_TZ");
			$timezone_minute_offset = 0;
			if(round($timezone_offset) != intval($timezone_offset)) {
				$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
				$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
			}

			$timezone_offset = -1 * $timezone_offset;
			$timezone_minute_offset = -1 * $timezone_minute_offset;
			$int_time = mktime(date("H") + $timezone_offset, date("i") + $timezone_minute_offset, date("s"), date("m"), date("d"), date("Y"));
			$time = date("YmdHis", $int_time);
		}
	}
	if($insert_flag) {
		// 登録時　サーバのタイムゾーンを引く
		$summertime_offset = 0;
		// サマータイムも取得できれば考慮する
		if(date("I")) {
			$summertime_offset = -1;
		}
		//
		// 第一引数が入っていれば画面から取得したものとみなして、_default_TZから引く
		//
		if ($time_null_flag) {
			$timezone_offset = -1 * $config[_GENERAL_CONF_CATID]['server_TZ']['conf_value'];
		} else {
			$timezone_offset = -1 * $_default_TZ;
		}
		$timezone_offset += $summertime_offset;
	} else {
		// 表示時　会員のタイムゾーンを足す（ログインしていない場合、デフォルトタイムゾーン）
		$timezone_offset = $session->getParameter("_timezone_offset");
		if($timezone_offset === null) {
			$timezone_offset = $_default_TZ;
		}
	}
	$timezone_minute_offset = 0;
	if(round($timezone_offset) != intval($timezone_offset)) {
		$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
		$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
	}
	if(strlen($time) == 6) {
		$int_time = mktime(intval(substr($time, 0, 2)) + $timezone_offset, intval(substr($time, 2, 2))+$timezone_minute_offset, intval(substr($time, 4, 2)));
		if($format == null) $format = "His";
	} else if(strlen($time) == 14) {
		$int_time = mktime(intval(substr($time, 8, 2)) + $timezone_offset, intval(substr($time, 10, 2))+$timezone_minute_offset, intval(substr($time, 12, 2)),
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
		if($format == null) $format = "YmdHis";
	}
	return date($format, $int_time);
}

/**
 * タイムゾーンの計算処理(Smartyから呼び出す場合)
 * @param string time(YmdHis or Hisの形式)
 * @return string timezone str
 * @access	public
 */
function timezone_date_format($time, $format) {
	return timezone_date($time, false, $format);
}

/**
 * テーマ毎のアイコン取得処理
 * ./themes/".$theme_first_name."/images/icons/".$images_dir."/".$file_name
 * @param string $file_name
 * @return string $file_path
 * @access	public
 */
function get_themes_image($file_name) {
	$renderer =& SmartyTemplate::getInstance();
	$_theme_first_name =& $renderer->get_template_vars("_theme_first_name");
	$_icon_color = $renderer->get_template_vars("_icon_color");

	if(file_exists(HTDOCS_DIR."/themes/".$_theme_first_name."/images/icons/".$_icon_color."/".$file_name)) {
		$file_path = "/themes/".$_theme_first_name."/images/icons/".$_icon_color."/".$file_name;
	} else if($_icon_color != "" && file_exists(HTDOCS_DIR."/themes/images/icons/".$_icon_color."/".$file_name)) {
		$file_path = "/themes/images/icons/".$_icon_color."/".$file_name;
	} else if(file_exists(HTDOCS_DIR."/themes/images/icons/default/".$file_name)) {
		$file_path = "/themes/images/icons/default/".$file_name;
	}
	if (CORE_BASE_URL == BASE_URL && isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$file_path = BASE_URL_HTTPS.$file_path;
	} else {
		$file_path = CORE_BASE_URL.$file_path;
	}
	return $file_path;
}
/**
 * テーマ毎のモジュール別アイコン取得処理
 * ./themes/".$theme_first_name."/images/icons/".$images_dir."/".$file_name
 * @param string $file_name
 * @return string $file_path
 * @access	public
 */
function get_modules_image($file_name) {
	$renderer =& SmartyTemplate::getInstance();
	$container =& DIContainerFactory::getContainer();
    $actionChain =& $container->getComponent("ActionChain");
    $action_name = $actionChain->getCurActionName();
    $pathList = explode("_", $action_name);
    $_icon_color = $renderer->get_template_vars("_icon_color");

	$file_path = "/images/". $pathList[0]. "/";
	if($_icon_color != "" && file_exists(HTDOCS_DIR. "/images/". $pathList[0]. "/". $_icon_color. "/". $file_name)) {
		$file_path .= $_icon_color. "/". $file_name;
	} else if(file_exists(HTDOCS_DIR. "/images/". $pathList[0]. "/". "default/". $file_name)) {
		$file_path .= "default/".$file_name;
	} else {
		$file_path .= $file_name;
	}
	if (CORE_BASE_URL == BASE_URL && isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$file_path = BASE_URL_HTTPS.$file_path;
	} else {
		$file_path = CORE_BASE_URL.$file_path;
	}
	return $file_path;
}

/**
 * 画像のCORE_BASE_URL取得処理
 *
 * @param string $dummy
 * @return string $file_path
 * @access	public
 */
function get_image_url($dummy="") {
	if (CORE_BASE_URL == BASE_URL && isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$file_path = BASE_URL_HTTPS;
	} else {
		$file_path = CORE_BASE_URL;
	}
	return $file_path;
}

/**
 * Wysiwygで追加された絵文字等の画像URLの変換処理
 *
 * @param string $str
 * @return string $file_path
 * @access	public
 */
function wysiwig_convert_url($str) {
	$str = preg_replace("/".sprintf(_WYSIWYG_CONVERT_OUTER, 'CORE_BASE_URL')."/iu", CORE_BASE_URL, $str);
	if (CORE_BASE_URL == BASE_URL) {
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$pattern = "/(src=[".preg_quote("\"'")."])". preg_quote(BASE_URL, "/") ."/iu";
			$replace = "$1".BASE_URL_HTTPS;
		} else {
			$pattern = "/(src=[".preg_quote("\"'")."])". preg_quote(BASE_URL_HTTPS, "/") ."/iu";
			$replace = "$1".BASE_URL;
		}
		$str = preg_replace($pattern, $replace, $str);
	}
	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
		$str = preg_replace("/".sprintf(_WYSIWYG_CONVERT_OUTER, 'BASE_URL')."/iu", BASE_URL_HTTPS, $str);
	} else {
		$str = preg_replace("/".sprintf(_WYSIWYG_CONVERT_OUTER, 'BASE_URL')."/iu", BASE_URL, $str);
	}

	$container =& DIContainerFactory::getContainer();
	$session =& $container->getComponent('Session');
	$isSmartphone = $session->getParameter('_smartphone_flag');
	if ($isSmartphone) {
		$str = preg_replace('/(<a.*href=(["\'])#\\2)(.*>)/iU', '\\1 onclick="$.mobile.silentScroll(0);"\\3', $str);
		$str = preg_replace('/(<a.*href=(["\']))(.*)#\S+(\\2.*>)/iU', '\\1\\3\\4', $str);
	}

	return $str;
}

/**
 * HtmlからText変換処理
 * @param string $str
 * @return string $str
 * @access	public
 */
function html_to_text($str) {
	$container =& DIContainerFactory::getContainer();
    $commonMain =& $container->getComponent("commonMain");
    $convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");

    $str = $convertHtml->convertHtmlToText($str);

	return htmlspecialchars($str);
}

/**
 * $$･･･$$で囲われたTextをtexに変換処理
 * @param string $str
 * @return string $str
 * @access	public
 */
function change_tex($str)
{
	$result = preg_replace_callback("/\\$(.*?)\\$/isu", "_callback_change_tex", $str);
	if (empty($result)) {
		return $str;
	} else {
		return $result;
	}
}

function _callback_change_tex($matches)
{
	$container =& DIContainerFactory::getContainer();
    $commonMain =& $container->getComponent("commonMain");
    $convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");

    if (empty($matches[1]) || trim($matches[1]) == "") {
    	return $matches[1];
    } else {
	    $matches[1] = $convertHtml->convertHtmlToText($matches[1]);
		return "<img class=\"icon\" border=\"0\" src=\"".BASE_URL. INDEX_FILE_NAME . "?action=common_tex_main&amp;s=n&amp;c=" . str_replace("%", "%_", str_replace("%C2%A5","%5C",rawurlencode($matches[1]))) . "\" alt=\"Tex\" />";
    }
}
/**
 * インラインstyle定義でrgb()が使われている部分を16進カラーコードに変換処理
 * @param string $str
 * @return string $str
 * @access	public
 */
function img_style_rgb_to_hex( $str )
{
	$pattern = '/<([^>]+)style\s*=\s*(["\'])([^>]+)rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)(.*?)\\2/iu';
	$result = preg_replace_callback( $pattern, "_callback_change_img_rgb_conv", $str );
	if( empty( $result ) ) {
		return( $str );
	}
	else {
		return( $result );
	}
}
function _callback_change_img_rgb_conv( $matches )
{
	return '<' . $matches[1] . 'style=' . $matches[2] . $matches[3] . ' #' .  sprintf("%02x",$matches[4]) . sprintf("%02x",$matches[5]) . sprintf("%02x",$matches[6]) . ' ' .  $matches[7] . $matches[2];
}
?>
