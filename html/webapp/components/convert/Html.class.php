<?php
 /**
 * Convert_Htmlクラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Convert_Html {
	var $_className = "Convert_Html";

	/**
	 * HtmlからText変換処理
	 * @param string Html文字列
	 * @return string	Plain Text文字列
	 **/
	function convertHtmlToText($str) {
		$patterns = array();
		$replacements = array();
		//\nを削除
		$patterns[] = "/\\n/su";
		$replacements[] = "";

		//brを\n
		$patterns[] = "/<br(.|\s)*?>/u";
		$replacements[] = "\n";

		//divを\n
		$patterns[] = "/<\/div>/u";
		$replacements[] = "</div>\n";

		//pを\n
		$patterns[] = "/<\/p>/u";
		$replacements[] = "</p>\n";

		//blockquoteを\n
		$patterns[] = "/<\/blockquote>/u";
		$replacements[] = "</blockquote>\n";

		//liを\n
		$patterns[] = "/[ ]*<li>/u";
		$replacements[] = "    <li>";

		$patterns[] = "/<\/li>/u";
		$replacements[] = "</li>\n";

		//&npspを空白
		$patterns[] = "/\&nbsp;/u";
		$replacements[] = " ";

		//&quot;を"
		$patterns[] = "/\&quot;/u";
		$replacements[] = "\"";

		//&acute;を´
		$patterns[] = "/\&acute;/u";
		$replacements[] = "´";

		//&cedil;を¸
		$patterns[] = "/\&cedil;/u";
		$replacements[] = "¸";

		//&circ;を?
		$patterns[] = "/\&circ;/u";
		$replacements[] = "?";

		//&lsquo;を‘
		$patterns[] = "/\&lsquo;/u";
		$replacements[] = "‘";

		//&rsquo;を’
		$patterns[] = "/\&rsquo;/u";
		$replacements[] = "’";

		//&ldquo;を“
		$patterns[] = "/\&ldquo;/u";
		$replacements[] = "“";

		//&rdquo;を”
		$patterns[] = "/\&rdquo;/u";
		$replacements[] = "”";

		//&apos;を'
		$patterns[] = "/\&apos;/u";
		$replacements[] = "'";

		//&#039;を'
		$patterns[] = "/\&#039;/u";
		$replacements[] = "'";

		//&amp;を&
		$patterns[] = "/\&amp;/u";
		$replacements[] = "&";

		$str = preg_replace($patterns, $replacements, $str);
		$quote_arr = explode("<blockquote class=\"quote\">", $str);
		$quote_cnt = count($quote_arr);
		if($quote_cnt > 1) {
			$result_str = "";
			$indent_cnt = 0;
			$count = 0;
			foreach($quote_arr as $quote_str) {
				if($count == 0 || $quote_cnt == $count) {
					$result_str .= $quote_str;
					$count++;
					continue;
				}
				$indent_cnt++;
				$quote_close_arr = explode("</blockquote>", $quote_str);
				$quote_close_cnt = count($quote_close_arr);
				if($quote_close_cnt > 1) {
					$close_count = 0;
					foreach($quote_close_arr as $quote_close_str) {
						//if($close_count == 0 || $quote_close_cnt == $close_count) {
//						if($quote_close_cnt == $close_count+1) {
//							$result_str .= $quote_close_str;
//							$close_count++;
//							continue;
//						}
						$indent_str = $this->_getIndentStr($indent_cnt);
						if($indent_str != "") {
							$quote_pattern = "/\n/u";
							$quote_replacement = "\n".$indent_str;
							$result_str = preg_replace("/(> )+$/u", "", $result_str);
							if($quote_close_cnt != $close_count+1) {
								if(!preg_match("/\n$/u", $result_str)) {
									$result_str .= "\n";
								}
								$result_str .= preg_replace("/^(> )+\n/u", "", $indent_str.preg_replace($quote_pattern, $quote_replacement, $quote_close_str));
								$indent_cnt--;
							} else {
								$result_str .= preg_replace($quote_pattern, $quote_replacement, $quote_close_str);
							}
						} else {
							$result_str .= $quote_close_str;
						}
						$close_count++;
					}

				} else {
					$indent_str = $this->_getIndentStr($indent_cnt);
					$quote_pattern = "/\n/u";
					$quote_replacement = "\n".$indent_str;
					$result_str .= $indent_str.preg_replace($quote_pattern, $quote_replacement, $quote_str);
				}
				$count++;
			}
			$str = $result_str;
		}
		$str = strip_tags($str);
		
		// strip_tagsで「<」、「>」があるとそれ以降の文字が消えるため、strip_tags後に変換
		$patterns = array();
		$replacements = array();

		//&lt;を<
		$patterns[] = "/\&lt;/u";
		$replacements[] = "<";

		//&gt;を>
		$patterns[] = "/\&gt;/u";
		$replacements[] = ">";

		return preg_replace($patterns, $replacements, $str);
	}

	function _getIndentStr($indent_cnt = 0)
	{
		$indent_str = "";
		$tab_str = "";
		for($i = 0; $i < $indent_cnt; $i++) {
			$indent_str .= "> ";
			$tab_str .= "";
		}
		return $tab_str.$indent_str;
	}

	/**
	 * HtmlからText変換処理
	 * @param string Html文字列
	 * @return string	Plain Text文字列
	 **/
	function convertMobileHtml($str, $convert=false)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");
		if (!isset($mobile_flag)) {
			$mobileCheck =& MobileCheck::getInstance();
			$mobile_flag = $mobileCheck->isMobile();
			$session->setParameter("_mobile_flag", $mobile_flag);
		}
		if ($mobile_flag == _ON) {
			$patterns = array();
			$replacements = array();

			if ($session->getParameter("_reader_flag") == _OFF) {
				// 画像にsession_idを付与
				$matches = array();

				$pattern = "/(href=|src=)([\"'])?(\\.?\\/?)(\\?)/";
				$str = preg_replace_callback($pattern, array($this, "_replaceRelative2Absolute"), $str);

				$pattern_url = preg_replace("/\//", "\\\/", preg_quote(BASE_URL));
				$pattern = "/(href=|src=)([\"'])?(".$pattern_url.")([^\\/]*?)?([^ \"'>]*)?([ \"'>])?/";
				$str = preg_replace_callback($pattern, array($this, "_replaceSesion"), $str);
			}

			//「 />」「/>」を「>」
			$patterns[] = "/( )?\/>/ui";
			$replacements[] = ">";

			$str = preg_replace($patterns, $replacements, $str);
			if ($convert) {
				//mb_stringがロードされているかどうか
		    	if (!extension_loaded('mbstring') && !function_exists("mb_convert_encoding")) {
	    			include_once MAPLE_DIR  . '/includes/mbstring.php';
		    	} else if(function_exists("mb_detect_order")){
		    		mb_detect_order(_MB_DETECT_ORDER_VALUE);
		    	}
		    	$str = mb_convert_encoding($str, "shift_jis", _CHARSET);
			}
		}
		return $str;
	}

	/**
	 * HtmlからText変換処理
	 * @param string Html文字列
	 * @return string	Plain Text文字列
	 **/
	function _replaceRelative2Absolute($matches) 
	{
		return $matches[1].$matches[2].BASE_URL.INDEX_FILE_NAME.$matches[4];
	}

	/**
	 * HtmlからText変換処理
	 * @param string Html文字列
	 * @return string	Plain Text文字列
	 **/
	function _replaceSesion($matches)
	{
		$session_value = session_name()."=".session_id();
		if (preg_match("/".$session_value."/", $matches[5])) {
			return $matches[0];
		}

		$pos = strpos($matches[5], "?");
		$pause = $pos !== false ? "&" : "?";

		$pos = strpos($matches[5], "#");
		$matches[5] = $pos !== false ? substr($matches[5],0,$pos). $pause .$session_value.substr($matches[5],$pos) : $matches[5]. $pause .$session_value;

		return $matches[1].$matches[2].$matches[3].$matches[4].$matches[5].$matches[6];
	}
}
?>
