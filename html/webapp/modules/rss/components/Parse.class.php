<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSSパース処理コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Rss_Components_Parse
{
	/**
	 * @var エラー情報を保持
	 *
	 * @access	private
	 */
	var $_errors = array();

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Rss_Components_Parse()
	{
	}

	/**
	 * RSSデータを解析し返す
	 *
	 * @param	string	$xml	XML文字列
     * @return string	RSSデータ文字列
	 * @access	public
	 */
	function &parse($xml, $encoding)
	{
    	if (empty($xml)) {
    		return $xml;
    	}

		$xml = trim(mb_convert_encoding($xml, $encoding, "auto"));

		include_once MAPLE_DIR."/includes/pear/XML/Unserializer.php";
		$options = array(
			XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => 'parseAttributes',
			XML_UNSERIALIZER_OPTION_ENCODING_SOURCE => $encoding
		);

		$unserializer = new XML_Unserializer($options);
		$unserializer->unserialize($xml);
		$xmlArray = $unserializer->getUnserializedData();
		if (empty($xmlArray)) return $xmlArray;
		if (strtolower(get_class($xmlArray)) == "pear_error") {
			$container =& DIContainerFactory::getContainer();
	    	$actionChain =& $container->getComponent("ActionChain");
			$errorList =& $actionChain->getCurErrorList();

			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			$errorList->add(null, $smartyAssign->getLang("rss_parse_error"));

			$xmlArray = false;
			return $xmlArray;
		}

		if (stristr(substr($xml, 0, 255), "xmlns=\"http://www.w3.org/2005/Atom\"") ||
			stristr(substr($xml, 0, 255), "xmlns=\'http://www.w3.org/2005/Atom\'") ||
			stristr(substr($xml, 0, 255), "http://purl.org/atom/")) {
			$atomArray["channel"] = array(
				"title" => !is_array($xmlArray["title"]) ? $xmlArray["title"] : $xmlArray["title"]["_content"],
				"link" => !empty($xmlArray["link"]["href"]) ? $xmlArray["link"]["href"] : (!empty($xmlArray["link"][0]["href"]) ? $xmlArray["link"][0]["href"] : ""),
				"lastbuilddate" => !empty($xmlArray["updated"]) ? $xmlArray["updated"] : $xmlArray["modified"],
				"id" => !empty($xmlArray["id"]) ? $xmlArray["id"] : "",
				"webmaster" => $xmlArray["author"]["name"],
				"generator" => !empty($xmlArray["generator"]) ? $xmlArray["generator"] : "",
			);
			if (isset($xmlArray["entry"]["title"])) {
				$entry = $xmlArray["entry"];
				unset($xmlArray["entry"]);
				$xmlArray["entry"][0] = $entry;
			}

			foreach ($xmlArray["entry"] as $entry ) {
				if (!empty($entry["content"])) {
					$description = !is_array($entry["content"]) ? $entry["content"] : $entry["content"]["_content"];
				} elseif (!empty($entry["summary"])) {
					$description = !is_array($entry["summary"]) ? $entry["summary"] : $entry["summary"]["_content"];
				} else {
					$description = "";
				}
				$atomArray["item"][] = array(
					"title" => !is_array($entry["title"]) ? $entry["title"] : $entry["title"]["_content"],
					"link" => $entry["link"]["href"],
					"id" => $entry["id"],
					"pubdate" => !empty($entry["updated"]) ? $entry["updated"] : (!empty($entry["modified"]) ? $entry["modified"] : ""),
					"description" => $description
				) ;
			}

			$xmlArray = $atomArray;

		}

		// 「作成」をセット
		if (empty($xmlArray["channel"]["generator"])) {
				if (!empty($xmlArray["channel"]["dc:creator"])) {
					$xmlArray["channel"]["generator"] = $xmlArray["channel"]["dc:creator"];
				}
		}
		// 「最終更新時刻」をセット
		if (empty($xmlArray["channel"]["lastbuilddate"])) {
				if (!empty($xmlArray["channel"]["dc:date"])) {
					$xmlArray["channel"]["lastbuilddate"] = $xmlArray["channel"]["dc:date"];
				}
		}

		//itemがchannelの内にある場合とchannelの外にある場合がある
		if (isset($xmlArray["channel"]["item"])) {
			$xmlArray["item"] = $xmlArray["channel"]["item"];
		}

		$pubdateNone = false;
		$itemsExtracted = array();
		if (empty($xmlArray["item"])) {						/* item が０の場合 */
			$item_count = 0;
			return $xmlArray;
		} else if (!empty($xmlArray["item"]["title"])) {	/* item が１つの場合 */
			$items["0"] = $xmlArray["item"];
			$item_count = 1;
		} else {											/* item が２つ以上の場合 */
			$items = $xmlArray["item"];
			$item_count = count($items);
		}
		for ($key=0; $key<$item_count; $key=$key+1) {
			if (empty($items[$key]["pubdate"])) {
				if (empty($items[$key]["dc:date"])) {
					if (empty($items[$key]["pubDate"])) {
						$items[$key]["pubdate"] = "" ;
						$pubdateNone = true ;
					} else {
						$items[$key]["pubdate"] = $items[$key]["pubDate"];
					}
				} else {
					$items[$key]["pubdate"] = $items[$key]["dc:date"];
				}
			}

			array_walk($items[$key], array($this, "stripTags"));
			if (!empty($items[$key]["description"])) {
				$items[$key]["description"] = nl2br(preg_replace('/(\n{2,})/s', "\n", $items[$key]["description"]));
			}
			$items[$key]["link"] = str_replace('"', "&quot;", $items[$key]["link"]);

			$itemsExtracted[] = $items[$key];
		}
		if (!$pubdateNone) {
			//usort($itemsExtracted, create_function('$a,$b', 'return $a["pubdate"] < $b["pubdate"] ? 1 : -1 ;'));
		}

		$xmlArray["item"] = $itemsExtracted;
		return $xmlArray;
	}

	/**
	 * Sets error messages
	 *
	 * @param	$error	string	an error message
	 */
    function setErrors($error)
    {
        $this->errors[] = trim($error);
    }

	/**
	 * Gets all the error messages
	 *
	 * @param	$ashtml	bool	return as html?
	 * @return	mixed	エラー配列 or エラー文字列
	 */
    function &getErrors($ashtml = true)
    {
        if (!$ashtml) {
            return $this->errors;
        } else {
        	$ret = "";
        	foreach ($this->errors as $error) {
        	    $ret .= $error. "<br />\n";
        	}
        	return $ret;
        }
    }

	/**
	 * HTMLタグを取り除く
	 *
	 * @param	$value	mixed	取り除く値
	 */
	function stripTags(&$value)
	{
		if (!is_string($value)) return;
		$value = strip_tags($value, "<br>");
	}
}
?>