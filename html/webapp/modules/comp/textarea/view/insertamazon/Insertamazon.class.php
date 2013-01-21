<?php
/**
 * ファイルアップロードダイアログ表示
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Comp_Textarea_View_Insertamazon extends Action
{
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $block_id = null;
	var $module_id = null;

	var $parent_id_name = null;
	var $top_id_name = null;

	var $keyword = null;
	var $category = null;
	var $type = null;
	var $target = null;
	var $pageNumber = null;

	// コンポーネントを受け取るため
	var $requestMain = null;
	//var $request = null;
	//var $session = null;

	// 値をセットするため
	var $amazonList = "";
	var $_dialog_name = "";

	function execute()
	{
		$this->pageNumber = intval($this->pageNumber) + 1;
		if(!empty($this->keyword)) {
			$query['Operation'] = "ItemSearch";
			$query['SearchIndex'] = empty($this->category) ? "Books" : $this->category;
			//if( $query['SearchIndex'] != "All" && $query['SearchIndex'] != "Blended" ) {
			//	$query['Sort'] = "daterank";	// 発売日が新しいものから表示
			//}
			$query['ResponseGroup'] = "ItemAttributes%2CImages";
			if($this->type == "detail") {
				$query['ResponseGroup'] .= "%2CReviews";
			}
			$query['Keywords'] = rawurlencode($this->keyword);
			$query["ItemPage"] = $this->pageNumber;
			$url = $this->getAmazonURL($query);
			$this->amazonList = $this->getAmazonSearch($url);
			if ($this->amazonList === false) {
				return 'success';
			}
		}

		return 'success';
	}


	/**
	 * AmazonURLを取得
	 *
	 * @return str	Amazon URL
	 * @access	public
	 */
	function getAmazonURL($query)
	{
		$query['Service'] = "AWSECommerceService";
		$query['AWSAccessKeyId'] = rawurlencode(COMP_AWS_ACCESS_KEY_ID);
		$query['AssociateTag'] = rawurlencode(COMP_AWS_ASSOCIATE_TAG);
		$query['Timestamp'] = rawurlencode(gmdate("Y-m-d\TH:i:s\Z"));
		$query['Version'] = rawurlencode('2010-06-01');
		ksort($query);

		$parameters = array();
		foreach ($query as $key => $value) {
			$parameters[] = "{$key}={$value}";
		}
		$queryString = implode('&', $parameters);

		/* 正規化リクエスト */
		$signatureRequestUrl = sprintf(COMP_AWS_REST, $queryString);

		include_once MAPLE_DIR."/includes/pear/PHP/Compat.php";
		PHP_Compat::loadFunction('hash_hmac');

		/* sha256 ハッシュ計算 */
		if (function_exists("hash_hmac")) {
			$hashString = base64_encode(hash_hmac("sha256", $signatureRequestUrl, COMP_AWS_SECRET_KEY_ID, true));
			$signature = rawurlencode($hashString);
			$url = COMP_AWS_URL. "?" . $queryString . "&Signature=" . $signature;
		} else {
			$url = COMP_AWS_URL. "?" . $queryString;
		}
		return $url;
	}

	/**
	 * Amazon Webサービスの取得
	 *
	 * @param str $url
	 *
	 * @return int data_id
	 */
	function &getAmazonSearch($url)
	{
		$xml = $this->requestMain->getResponseHtml($url);
    	if (empty($xml)) {
    		$result = false;
            return $result;
        }
		$xml = trim(mb_convert_encoding($xml, COMP_XML_ENCODING, "auto"));

		include_once MAPLE_DIR."/includes/pear/XML/Unserializer.php";
		$options = array(
			XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => 'parseAttributes',
			XML_UNSERIALIZER_OPTION_ENCODING_SOURCE => COMP_XML_ENCODING
		);

		$unserializer = new XML_Unserializer($options);
		$unserializer->unserialize($xml);
		$amazonXml = $unserializer->getUnserializedData();
    	if (empty($amazonXml)) {
    		$result = false;
            return $result;
        }
        if (!empty($amazonXml["Error"]) || empty($amazonXml["Items"])) {
    		$result = false;
            return $result;
        }

        $amazonXml = $amazonXml["Items"];
		$amazonList = array();
		$amazonList["allcount"] = isset($amazonXml["TotalResults"]) ? intval($amazonXml["TotalResults"]) : (!empty($amazonXml) && is_array($amazonXml) ? count($amazonXml) : 0);
		$amazonList["totalPages"] = isset($amazonXml["TotalPages"]) ? intval($amazonXml["TotalPages"]) : 0;

		if ($amazonList["allcount"] == 0) {
			return $amazonList;
		}
		if (!empty($amazonXml["Request"]["Errors"]["Error"]) &&
			$amazonXml["Request"]["Errors"]["Error"]["Code"] == "AWS.ECommerceService.NoExactMatches") {

			$amazonList["allcount"] = 0;
			return $amazonList;
		}
        if (empty($amazonXml["Item"])) {
    		$result = false;
            return $result;
        }

		if ($amazonList["allcount"] <= 1 || !empty($amazonXml["Item"]["ASIN"])) {
			$item = $amazonXml["Item"];
			$result = $this->_parseAmazon($item);
			$amazonList["result"][0] = $result;
		} elseif (!empty($amazonXml["Item"])) {
			foreach ($amazonXml["Item"] as $i=>$item) {
				$result = $this->_parseAmazon($item);
				$amazonList["result"][$i] = $result;
			}
		}
		return $amazonList;
	}

    /**
     * Amazon Webサービスデータの整形
     *
     * @param   array   $item     Amazonのデータ
     * @return  array   $item     Amazonのデータ
     * @access  public
     */
    function &_parseAmazon(&$item)
    {
    	$result = array();
    	$result["asin"] = $item["ASIN"];
    	$result["amazonURL"] = $item["DetailPageURL"];

    	if($this->type == "small") {
    		$result["ImageURL"] = isset($item["SmallImage"]["URL"]) ? $item["SmallImage"]["URL"] : "";
    	} else if($this->type == "large") {
    		$result["ImageURL"] = isset($item["LargeImage"]["URL"]) ? $item["LargeImage"]["URL"] : "";
    	} else {
    		$result["ImageURL"] = isset($item["MediumImage"]["URL"]) ? $item["MediumImage"]["URL"] : "";
    	}

    	foreach ($item["ItemAttributes"] as $key=>$val) {
    		$lowkey = strtolower($key);
    		switch ($key) {
    			case "PackageDimensions":
    				break;
    			case "Artist":
    				$result[$lowkey] = $val;
    			case "Author":
    				$result[$lowkey] = is_array($val) ? implode(", ", $val) : $val;
    				break;
    			case "Creator":
					if (isset($val["Role"])) {
						$result[$lowkey] = $val["Role"]. ":" . $val["_content"];
					} else {
						$result[$lowkey] = "";
						foreach ($val as $key2=>$val2) {
							$result[$lowkey] .= (empty($result[$lowkey]) ? "" : ", "). $val2["_content"];
						}
					}
    				break;
    			case "ReleaseDate";
    			case "PublicationDate":
    				$result[$lowkey] = str_replace("-", COMP_INSERT_AMAZON_DATE_SEP, $val);
    				break;
    			case "ListPrice":
					foreach ($val as $key2=>$val2) {
						$result[strtolower($key2)] = $val2;
					}
    				break;
    			default:
    				$result[$lowkey] = $val;
    		}
    	}
    	if($this->type == "detail") {
    		// お勧め度
    		foreach ($item["CustomerReviews"] as $key=>$val) {
    			if($key == "AverageRating") {
    				$result["AverageRating"] = $val;
    			} else if($key == "TotalReviews") {
    				$result["TotalReviews"] = $val;
    			} else {
    				continue;
    			}
    		}
    	}

		return $result;
    }
}
?>
