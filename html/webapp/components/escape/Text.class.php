<?php
 /**
 * WYSIWYGエディタ,テキスト、テキストエリアコンバート処理用
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Escape_Text {
	var $_className = "Escape_Text";

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	var $patterns = array("/\&nbsp;/u","/\&quot;/u","/\&lt;/u","/\&gt;/u","/\&acute;/u","/\&cedil;/u","/\&circ;/u","/\&lsquo;/u","/\&rsquo;/u","/\&ldquo;/u","/\&rdquo;/u","/\&amp;/u","/\&apos;/u","/\&#039;/u");
	var $replacements = array(" ","\"","<",">","´","¸","&_circ;","‘","’","“","”","&","'","'");
	var $replacements_back = array("/ /u","/\"/u","/</u","/>/u","/´/u","/¸/u","/&_circ;/u","/‘/u","/’/u","/“/u","/”/u","/&/u","/'/u","/'/u");
	var $patterns_back = array("&nbsp;","&quot;","&lt;","&gt;","&acute;","&cedil;","&circ;","&lsquo;","&rsquo;","&ldquo;","&rdquo;","&amp;","&apos;","&#039;");

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Escape_Text() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * WYSIWYGエディターで出力するテキストをエスケープ
	 * @param string
	 * @return	string
	 * @access	public
	 **/
	function escapeWysiwyg(&$string) {
		if(preg_match('/^\s*<div><\/div>\s*$/iu', $string) || preg_match('/^\s*<br\s*\/?>\s*$/iu', $string)) {
			return "";
		}

		//絶対パスを相対パスへ変換
		//$string = preg_replace ("/". preg_quote(BASE_URL."/", "/") ."/i", "./", $string);
		//HTMLを許す権限ならばエスケープしない。権限によってスルーする処理をいれる
		$session =& $this->_container->getComponent("Session");
		if ($session->getParameter("_allow_htmltag_flag") == _ON) {
			return $this->_escapeWysiwygAllowHtmltag($string);
		}

		$allowable_tags = "";
		$allowable_attribute = array();
		$common_attribute = array();
		$protocol_check_attribute = array();
		$allowable_protocol = array();

		//許可するタグと属性を読み込み
		$sql = "SELECT tag,attribute FROM {textarea_tag}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$allowable_tags .= "<" . $result[$i]["tag"] . ">";
			if (isset($result[$i]["attribute"])) {
				$allowable_attribute[$result[$i]["tag"]] = array();
				$attributes = explode(",", $result[$i]["attribute"]);
				foreach ($attributes as $attribute) {
					$allowable_attribute[$result[$i]["tag"]][$attribute] = true;
				}
			}
		}

		//タグ全体で許可する属性を読み込み
		$sql = "SELECT attribute,value_regexp FROM {textarea_attribute}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$common_attribute[$result[$i]["attribute"]] = true;
			$allowable_common_attribute_value[$result[$i]["attribute"]] = $result[$i]["value_regexp"];
		}

		//プロトコルをチェックする属性を読み込み
		$sql = "SELECT attribute FROM {textarea_attribute_protocol}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$protocol_check_attribute[$result[$i]["attribute"]] = true;
		}

		//許可するプロトコルを読み込み
		$sql = "SELECT protocol FROM {textarea_protocol}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$allowable_protocol[$result[$i]["protocol"]] = true;
		}

		//許可するstyle属性を読み込み
		$sql = "SELECT css,value_regexp FROM {textarea_style}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		for ($i = 0; $i < $count; $i++) {
			$allowable_style[$result[$i]["css"]] = $result[$i]["value_regexp"];
		}

		//許可するvideoのURLを読み込み
		$sql = "SELECT url,action_name FROM {textarea_video_url}";
		$result = $this->_db->execute($sql);

		$sql = "SELECT url FROM {sites}";
		$sites = $this->_db->execute($sql);

		$allowable_video = array();
		foreach($result as $video) {
			if($video['url'] == '') {
				$allowable_video['.\/'] = $video['action_name'];
				foreach($sites as $site) {
					$site['url'] = ($site['url'] == "BASE_URL") ? BASE_URL : $site['url'];
					$allowable_video[preg_quote($site['url'], "/")] = $video['action_name'];
				}
			} else {
				$allowable_video[preg_quote($video['url'], "/")] = $video['action_name'];
			}
		}

		//許可するparamタグのValueを読み込み
		$sql = "SELECT name,value_regexp FROM {textarea_param_tag}";
		$result = $this->_db->execute($sql);
		$count = count($result);
		$allowable_param_tag = array();
		for ($i = 0; $i < $count; $i++) {
			$allowable_param_tag[strtolower($result[$i]["name"])] = $result[$i]["value_regexp"];
		}

		// 許可されたタグ以外を除去
		$string = strip_tags($string, $allowable_tags);
		// 改行、タブ除去
//		$string = preg_replace("/[\t\r\n]/", " ", $string);
//		$string = preg_replace("/[\s]+/", " ", $string);
		// コメント除去
		$string = preg_replace("/<\!\-\-(?:.|\s)+?\-\->/", "", $string);
//		$string = preg_replace("/<style .+?\/style>/i", "", $string);
//		$string = preg_replace("/<script .+?\/script>/i", "", $string);

		// 文字列を分割
		$parts = preg_split("/(<\/?[^>]+?>)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		$parts = array_diff($parts, array(" "));
		$parts = array_diff($parts, array("  "));

		// 禁止ワード用データ取得
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _SECURITY_CONF_CATID);

		$censor_words = "";
		if (isset($config['censor_enable']) &&
			$config['censor_enable']['conf_value'] == _ON) {
			$censor_words = explode("|",$config['censor_words']['conf_value']);
			$censor_replace = $config['censor_replace']['conf_value'];
		}

		$script_src = false;
		$string = "";
		foreach ($parts as $part) {
			$line_string = "";
			if ($part[0] != "<") {
				if($script_src == true)
					continue;				// scriptタグの中身は許さない
				// Value　禁止ワードチェック
				if($censor_words != "") {
					foreach($censor_words as $censor_word) {
						if ( !empty($censor_word) ) {
							$censor_word = quotemeta($censor_word);
							$patterns[] = "/".$censor_word."/siU";
							$replacements[] = $censor_replace;
							$part = preg_replace($patterns, $replacements, $part);
						}
					}
				}
				$string .= $part;
				continue;
			}
			$part = preg_replace ("/^<\s*/", "", $part);
			$part = preg_replace ("/\s*>$/", "", $part);

			// 囲み内の半角スペースをダミー文字列<!--dummy-->で保護
			// <!--dummy-->：コメントが既に除去されているのでダミーに使用
			$part_split = preg_split("/(\"[^\"]*?\")|(\'[^\']*?\')/", $part, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			$part ="";
			foreach ($part_split as $value) {
				if (substr($value, 0, 1) == "\"" || substr($value, 0, 1) == "'" ){
					$value = str_replace(" ", "<!--dummy-->", $value);
				}
				$part = $part.$value;
			}

			// タグ内を分解
			$tag_split = explode(" ", $part);
			$tag_split = array_diff($tag_split, array(""));//空を除去
			$attribute_count = count($tag_split);
			// 0番目は、タグ名
			$line_string .= "<" . $tag_split[0];
			$param_tag_name = "";
			$param_tag_value = "";
			for ($i = 1; $i < $attribute_count; $i++) {
				$value = str_replace ("<!--dummy-->", " ", $tag_split[$i]);
				// 属性を=で分解
				$attribute_split = preg_split("/(=)/", $value, 2, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
				$attribute_split[0] = strtolower($attribute_split[0]);
				if (!isset($common_attribute[$attribute_split[0]]) &&
					!isset($allowable_attribute[$tag_split[0]][$attribute_split[0]])) {

					continue;
				}

				if (isset($protocol_check_attribute[$attribute_split[0]])) {
					$attribute_split[2] = preg_replace("/&#(?:0*58|x0*3a);/i", ":", $attribute_split[2]);	// コロンの置換
					$attribute_split[2] = preg_replace("/^[\"\']/", "", $attribute_split[2]);				// "(')の削除

					$pos = strpos($attribute_split[2], ":");
					if ($pos !== FALSE) {
						$protocol = substr($attribute_split[2], 0, $pos);
						if (!isset($allowable_protocol[$protocol])) {
							continue;
						}
					} else {
						// ./ と ../　の許可
						if($attribute_split[2] != "#" && !preg_match("/^\.\//", $attribute_split[2]) && !preg_match("/^\.\.\//", $attribute_split[2])) {
							$attribute_split[2] = "./".$attribute_split[2];
						}
					}
				}
				//Class名、Id名チェック
				//先頭が_(アンダーバー)のクラス名、ID名は許さない
				/*
				if($attribute_split[0] == "id" || $attribute_split[0] == "class") {
					$attribute_split[2] = preg_replace("/&#(?:0*95|x0*5f|);/i", "_", $attribute_split[2]);	// アンダーバーの置換
					$attribute_split[2] = preg_replace("/^[\"\']|[\"\']$/", "", $attribute_split[2]);				// "(')の削除
					$css_split = explode(" ", $attribute_split[2]);
					$css_split = array_diff($css_split, array(""));				//空を除去
					if(is_array($css_split)) {
						$css_value = "";
						foreach($css_split as $css) {
							if(trim($css) == "")
								continue;
							if(!preg_match("/^_/", $css)) {
								if($css_value != "") $css_value.= " ";
								$css_value = $css;
							}
						}
						$attribute_split[2] = $css_value;
					}

				}
				*/
				//styleタグ チェック
				if($attribute_split[0] == "style") {
					$continue_flag = false;
					$attribute_split[2] = preg_replace("/&#(?:0*58|x0*3a|);/i", ":", $attribute_split[2]);	// コロンの置換
					$attribute_split[2] = preg_replace("/&#(?:0*59|x0*3b|);/i", ";", $attribute_split[2]);	// カンマの置換
					$attribute_split[2] = preg_replace("/^[\"\']|[\"\']$/", "", $attribute_split[2]);				// "(')の削除

					$css_split = explode(";", $attribute_split[2]);
					$css_split = array_diff($css_split, array(""));				//空を除去
					if(is_array($css_split)) {
						foreach($css_split as $css) {
							if(trim($css) == "")
								continue;
							$css_detail_split = explode(":", $css);
							$css_detail_split = array_diff($css_detail_split, array(""));				//空を除去
							if(is_array($css_detail_split) && count($css_detail_split) == 2) {
								$css_key = strtolower(trim($css_detail_split[0]));
								$css_value = trim($css_detail_split[1]);
								if(!isset($allowable_style[$css_key])) {
									$continue_flag = true;
								} else {
									//css_valueチェック
									if($allowable_style[$css_key] != "" && !preg_match("/".$allowable_style[$css_key]."/i", $css_value)) {
										$continue_flag = true;
									}
								}
							} else {
								$continue_flag = true;
							}
							if($continue_flag) {
								break;
							}
						}
					} else {
						$continue_flag = true;
					}
					if($continue_flag) {
						continue;
					}
				//flashチェック
				} else if((($tag_split[0] == "embed" || $tag_split[0] == "iframe") && $attribute_split[0] == "src") || $attribute_split[0] == "flashvars") {
					$attribute_split[2] = preg_replace("/^[\"\']|[\"\']$/", "", $attribute_split[2]);				// "(')の削除
					if(!$this->_checkVideoURL($attribute_split[2], $allowable_video)) {
						$line_string = "";
						break;
					}
				} else if(isset($allowable_common_attribute_value[$attribute_split[0]]) &&
						$allowable_common_attribute_value[$attribute_split[0]] != "") {
					//属性のvalueチェック
					$attribute_split[2] = preg_replace("/^[\"\']|[\"\']$/", "", $attribute_split[2]);				// "(')の削除
					if($attribute_split[0] == "class") {
						$css_split = explode(" ", $attribute_split[2]);
						$css_split = array_diff($css_split, array(""));				//空を除去
						if(is_array($css_split)) {
							$css_value = "";
							foreach($css_split as $css) {
								if(trim($css) == "")
									continue;
								if(preg_match("/".$allowable_common_attribute_value[$attribute_split[0]]."/", $css)) {
									if($css_value != "") $css_value.= " ";
									$css_value .= $css;
								}
							}
							$attribute_split[2] = $css_value;
							if($attribute_split[2] == "") {
								//空になった
								continue;
							}
						}
					} else {
						if(!preg_match("/".$allowable_common_attribute_value[$attribute_split[0]]."/", $attribute_split[2])) {
							//属性のValueがマッチしていない
							continue;
						}
					}
				}
				// scriptチェック
				if($tag_split[0] == "script" && $attribute_split[0] == "src") {
					$attribute_split[2] = preg_replace("/^[\"\']|[\"\']$/", "", $attribute_split[2]);				// "(')の削除
					// 自サイトならば許す：キャビネットなどでhoge.txtをアップロードし、その内容に
					// javascriptを含むと任意のjavascriptが実行されてしまうため、自サイトならば許すだけではセキュリティ上問題
					// action_nameまでみて判断
					// 現状常に許さない
					$line_string = "";
					break;
				}
				//if (isset($protocol_check_attribute[$attribute_split[0]])) {
				//
				//	$allowable_style
				//}
				$attribute_split_count = count($attribute_split);
				if ($attribute_split_count == 1) {
					$attribute_split[1] = $attribute_split[0];
				}
				$attribute_value = "";
				for ($j = 1; $j < $attribute_split_count; $j++) {
					$attribute_value = $attribute_value.$attribute_split[$j] ;
				}
				$attribute_value = preg_replace("/^=/", "", $attribute_value);
				$attribute_value = preg_replace("/^[\"\']/", "", $attribute_value);
				$attribute_value = preg_replace("/[\'\"]$/", "", $attribute_value );
				if($attribute_split[0] == "src" || $attribute_split[0] == "href") {
					$attribute_value = preg_replace($this->patterns,$this->replacements,$attribute_value);
					$attribute_value = preg_replace($this->replacements_back,$this->patterns_back,$attribute_value);
					//絶対パスを相対パスへ変換
					//$attribute_value = preg_replace("/". preg_quote(BASE_URL."/", "/") ."/i", "./", $attribute_value);
					$attribute_value = $this->_convertBaseURL($attribute_value, $attribute_split[0]);
				}
				$line_string .= " " . $attribute_split[0] . "=\"" . $attribute_value . "\"";
				if($tag_split[0] == "param" && $attribute_split[0] == "name") {
					$param_tag_name = strtolower($attribute_value);
				} else if($tag_split[0] == "param" && $attribute_split[0] == "value") {
					$param_tag_value = strtolower($attribute_value);
				}
			}

			if($tag_split[0] == "param") {
				if($param_tag_name == "" || $param_tag_value == "" || !isset($allowable_param_tag[$param_tag_name]) ||
					!preg_match("/".$allowable_param_tag[$param_tag_name]."/i", $param_tag_value)) {
					$line_string = "";
				}

				// 共有設定のURL＋Youtubeで動画モジュールからのアップデートファイルならばOK
				if($param_tag_name == "movie") {
					if(!$this->_checkVideoURL($param_tag_value, $allowable_video)) {
						$line_string = "";
					}
				}
			} else if(($tag_split[0] == "script" && $script_src == false) ||
						($script_src == true && $tag_split[0] != "script" && $tag_split[0] != "/script")) {
				// scriptにもかかわらずsrc属性がない or scriptの内部(scriptが閉じる前)
				$line_string = "";
			}

			if($tag_split[0] == "/script") {
				if($script_src == true)
					$script_src = false;		// scriptタグ終了
				else
					$line_string = "";
			}
			if($line_string == "") continue;
			if ($tag_split[$i-1] == "/") {
				$line_string .= " /";
				if($tag_split[0] == "script") {
					$script_src = false;
				}
			}
			$line_string .= ">";

			$string .= $line_string;
		}
		if($script_src == true)
			$string .= '</script>';	// scriptが閉じていないため、強制的に閉じる
		return $string;
	}

	/**
	 * WYSIWYGエディターで出力するテキストをエスケープ(管理者用)　-　src,hrefの&を&amp;に変換
	 * @param string
	 * @return	string
	 * @access	public
	 **/
	function _escapeWysiwygAllowHtmltag($string) {
		// 文字列を分割
		$comment_count = 0;
		$comment = array();
		$ret= preg_match_all("/<\!\-\-((?:.|\s)+?)\-\->/u", $string, $matches);
		if($ret && isset($matches[0])) {
			foreach($matches[0] as $key => $value) {
				$comment[] = $value;
				//$string = preg_replace('/'.preg_quote($value, '/').'/u', "<!--".htmlspecialchars($matches[1][$key])."-->", $string);
			}
			$string = preg_replace("/<\!\-\-(?:.|\s)+?\-\->/", "<!--comment-->", $string);
		}
		$parts = preg_split("/(<\/?[^>]+?>)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		$parts = array_diff($parts, array(" "));
		$parts = array_diff($parts, array("  "));

		$string = "";
		$script_flag = false;
		foreach ($parts as $part) {
			// script-/scriptまではそのまま連結
			if(preg_match("/^<script.*>/u", $part) || $script_flag == true) {
				$script_flag = true;
				if (preg_match("/<\!\-\-comment\-\->/u", $part)) {
					$part = preg_replace("/<\!\-\-comment\-\->/u", $comment[$comment_count], $part);
					$comment_count++;
				}
				$string .= $part;
				continue;
			} else if(preg_match("/<\/script>$/u", $part)) {
				$script_flag = false;
				$string .= $part;
				continue;
			}

			if (preg_match("/<\!\-\-comment\-\->/u", $part)) {
				$string .= $comment[$comment_count];
				$comment_count++;
				continue;
			}
			if ($part[0] != "<") {
				$string .= $part;
				continue;
			}

			$part = preg_replace ("/^<\s*/", "", $part);
			$part = preg_replace ("/\s*>$/", "", $part);

			// 囲み内の半角スペースをダミー文字列<!--dummy-->で保護
			// <!--dummy-->：コメントが既に除去されているのでダミーに使用
			$part_split = preg_split("/(\"[^\"]*?\")|(\'[^\']*?\')/", $part, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			$part ="";
			foreach ($part_split as $value) {
				if (substr($value, 0, 1) == "\"" || substr($value, 0, 1) == "'" ){
					$value = str_replace(" ", "<!--dummy-->", $value);
				}
				$part = $part.$value;
			}
			// タグ内を分解
			$tag_split = explode(" ", $part);
			$tag_split = array_diff($tag_split, array(""));//空を除去

			// 0番目は、タグ名
			$string .= "<" . $tag_split[0];
			$attribute_count = count($tag_split);
			for ($i = 1; $i < $attribute_count; $i++) {
				$value = str_replace ("<!--dummy-->", " ", $tag_split[$i]);
				// 属性を=で分解
				$attribute_split = preg_split("/(=)/", $value, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
				$attribute_split[0] = strtolower($attribute_split[0]);

				$attribute_split_count = count($attribute_split);
				if ($attribute_split_count == 1) {
					$attribute_split[1] = $attribute_split[0];
				}
				$attribute_value = "";
				for ($j = 1; $j < $attribute_split_count; $j++) {
					$attribute_value = $attribute_value.$attribute_split[$j] ;
				}
				$attribute_value = preg_replace("/^=/", "", $attribute_value);
				$attribute_value = preg_replace("/^[\"\']/", "", $attribute_value);
				$attribute_value = preg_replace("/[\'\"]$/", "", $attribute_value );
				if($attribute_split[0] == "src" || $attribute_split[0] == "href") {
					$attribute_value = preg_replace($this->patterns,$this->replacements,$attribute_value);
					$attribute_value = preg_replace($this->replacements_back,$this->patterns_back,$attribute_value);
					//絶対パスを相対パスへ変換
					//$attribute_value = preg_replace("/". preg_quote(BASE_URL."/", "/") ."/i", "./", $attribute_value);
					$attribute_value = $this->_convertBaseURL($attribute_value, $attribute_split[0]);
				}
				if(isset($attribute_split[0]) && $attribute_split[0] != "" && $attribute_split[0] != "/") {
					$string .= " " . $attribute_split[0] . "=\"" . $attribute_value . "\"";
				}
			}
			if ($tag_split[$i-1] == "/") {
				$string .= " /";
			}
			$string .= ">";
		}
		return $string;
	}

	/**
	 * 出力テキストエスケープ
	 * @param string
	 * @return	string
	 * @access	public
	 **/
	function escapeText(&$string) {
		// 禁止ワード用データ取得
		$configView =& $this->_container->getComponent("configView");
		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _SECURITY_CONF_CATID);

		$censor_words = "";
		if (isset($config['censor_enable']) &&
			$config['censor_enable']['conf_value'] == _ON) {
			$censor_words = explode("|",$config['censor_words']['conf_value']);
			$censor_replace = $config['censor_replace']['conf_value'];
		}
		// 禁止ワードチェック
		if($censor_words != "") {
			$patterns = array();
			$replacements = array();
			foreach($censor_words as $censor_word) {
				if ( !empty($censor_word) ) {
					$censor_word = quotemeta($censor_word);
					$patterns[] = "/".$censor_word."/siU";
					$replacements[] = $censor_replace;
					$string = preg_replace($patterns, $replacements, $string);
				}
			}
		}
		return $string;
	}

	/**
	 * Videoアップロードチェック
	 * @param string
	 * @param array
	 * @return	string
	 * @access	public
	 **/
	function _checkVideoURL($value, $allowable_video) {
		$session =& $this->_container->getComponent("Session");
		$_allow_video_flag = $session->getParameter("_allow_video_flag");
		if($_allow_video_flag != _ON) {
			// 動画アップロード不可
			return false;
		}

		// URlチェック
		$error_flag = true;
		foreach($allowable_video as $url_key => $video) {
			$req_str = ($video != "") ? $url_key . "?action=" . $video : $url_key;
			if(preg_match('/^'.$req_str . '/iu', $value)) {
				$error_flag = false;
				break;
			}
		}
		if($error_flag == true) {
			return false;
		}
		return true;
	}

	/**
	 * 絶対パスから相対パスへ変換
	 *
	 * @param string
	 * @return	string
	 *
	 * @access	private
	 **/
	function _convertBaseURL($string, $attribute=null)
	{
		$base_url = preg_replace("/^https:/i","http:", BASE_URL);
		$https_base_url = preg_replace("/^http:/i","https:", BASE_URL);
		$core_base_url = preg_replace("/^https:/i","http:", CORE_BASE_URL);
		$https_core_base_url = preg_replace("/^http:/i","https:", CORE_BASE_URL);

		if ($attribute == "src") {
			$pattern = "/". preg_quote($https_base_url, "/") ."|" . preg_quote($base_url, "/") ."|" .
				preg_quote($core_base_url, "/") ."|" . preg_quote($https_core_base_url, "/") . "/iu";
			if (!preg_match($pattern, $string)) { return $string; }

			$parseUrls = parse_url($string);
			if (empty($parseUrls['scheme'])) {
				return $string;
			}

			if (empty($parseUrls['query'])) {
				if (BASE_URL == CORE_BASE_URL) {
					$pattern = "/". preg_quote($https_base_url, "/") ."|" . preg_quote($base_url, "/") .
							"|" . preg_quote($core_base_url, "/") ."|" . preg_quote($https_core_base_url, "/"). "/iu";
				} else {
					$pattern = "/". preg_quote($core_base_url, "/") ."|" . preg_quote($https_core_base_url, "/"). "/iu";
				}
				$convert_url = sprintf(_WYSIWYG_CONVERT_OUTER, 'CORE_BASE_URL');
			} else {
				$pattern = "/(". preg_quote($https_base_url, "/") ."|" . preg_quote($base_url, "/") . ").*?".preg_quote("?")."/iu";
				//$convert_url = sprintf(_WYSIWYG_CONVERT_OUTER, 'BASE_URL'). INDEX_FILE_NAME ."?";
				$convert_url = "./?";
			}
			$string = preg_replace($pattern, $convert_url, $string);
		}

		return $string;
	}

    /**
	 * 文字の名寄せ
	 * 　
	 * @return string dirname
     * @access  public
	 */
	function convertSynonym($str)
	{
		//半角ｶﾅ(濁点付き)→全角カナに変換
		$replace_of = array('ｳﾞ', 'ｶﾞ', 'ｷﾞ', 'ｸﾞ',
							'ｹﾞ', 'ｺﾞ', 'ｻﾞ', 'ｼﾞ',
							'ｽﾞ', 'ｾﾞ', 'ｿﾞ', 'ﾀﾞ',
							'ﾁﾞ', 'ﾂﾞ', 'ﾃﾞ', 'ﾄﾞ',
							'ﾊﾞ', 'ﾋﾞ', 'ﾌﾞ', 'ﾍﾞ',
							'ﾎﾞ', 'ﾊﾟ', 'ﾋﾟ', 'ﾌﾟ', 'ﾍﾟ', 'ﾎﾟ');

		$replace_by = array('ヴ', 'ガ', 'ギ', 'グ',
							'ゲ', 'ゴ', 'ザ', 'ジ',
							'ズ', 'ゼ', 'ゾ', 'ダ',
							'ヂ', 'ヅ', 'デ', 'ド',
							'バ', 'ビ', 'ブ', 'ベ',
							'ボ', 'パ', 'ピ', 'プ', 'ペ', 'ポ');
		$_result = str_replace($replace_of, $replace_by, $str);

		//半角ｶﾅ→全角カナに変換
		$replace_of = array('ｱ', 'ｲ', 'ｳ', 'ｴ', 'ｵ',
							'ｶ', 'ｷ', 'ｸ', 'ｹ', 'ｺ',
							'ｻ', 'ｼ', 'ｽ', 'ｾ', 'ｿ',
							'ﾀ', 'ﾁ', 'ﾂ', 'ﾃ', 'ﾄ',
							'ﾅ', 'ﾆ', 'ﾇ', 'ﾈ', 'ﾉ',
							'ﾊ', 'ﾋ', 'ﾌ', 'ﾍ', 'ﾎ',
							'ﾏ', 'ﾐ', 'ﾑ', 'ﾒ', 'ﾓ',
							'ﾔ', 'ﾕ', 'ﾖ', 'ﾗ', 'ﾘ',
							'ﾙ', 'ﾚ', 'ﾛ', 'ﾜ', 'ｦ',
							'ﾝ', 'ｧ', 'ｨ', 'ｩ', 'ｪ',
							'ｫ', 'ヵ', 'ヶ', 'ｬ', 'ｭ',
							'ｮ', 'ｯ', '､', '｡', 'ｰ',
							'｢', '｣', 'ﾞ', 'ﾟ');

		$replace_by = array('ア', 'イ', 'ウ', 'エ', 'オ',
							'カ', 'キ', 'ク', 'ケ', 'コ',
							'サ', 'シ', 'ス', 'セ', 'ソ',
							'タ', 'チ', 'ツ', 'テ', 'ト',
							'ナ', 'ニ', 'ヌ', 'ネ', 'ノ',
							'ハ', 'ヒ', 'フ', 'ヘ', 'ホ',
							'マ', 'ミ', 'ム', 'メ', 'モ',
							'ヤ', 'ユ', 'ヨ', 'ラ', 'リ',
							'ル', 'レ', 'ロ', 'ワ', 'ヲ',
							'ン', 'ァ', 'ィ', 'ゥ', 'ェ',
							'ォ', 'ヶ', 'ヶ', 'ャ', 'ュ',
							'ョ', 'ッ', '、', '。', 'ー',
							'「', '」', '”', '');

		$_result = str_replace($replace_of, $replace_by, $_result);

		$_result = $this->convertSingleByte($_result);

		return $_result;
	}

   /**
	 * 文字の名寄せ
	 * 　
	 * @return string dirname
     * @access  public
	 */
	function convertSingleByte($str)
	{
		//全角数字→半角数字に変換
		$replace_of = array('１', '２', '３', '４', '５',
							'６', '７', '８', '９', '０');

		$replace_by = array('1', '2', '3', '4', '5',
							'6', '7', '8', '9', '0');

		$_result = str_replace($replace_of, $replace_by, $str);

		//全角英字→半角英字に変換
		$replace_of = array('Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ',
							'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ',
							'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ',
							'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ',
							'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ',
							'Ｚ',
							'ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ',
							'ｆ', 'ｇ', 'ｈ', 'ｉ', 'ｊ',
							'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ',
							'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ',
							'ｕ', 'ｖ', 'ｗ', 'ｘ', 'ｙ',
							'ｚ');

		$replace_by = array('A', 'B', 'C', 'D', 'E',
							'F', 'G', 'H', 'I', 'J',
							'K', 'L', 'M', 'N', 'O',
							'P', 'Q', 'R', 'S', 'T',
							'U', 'V', 'W', 'X', 'Y',
							'Z',
							'a', 'b', 'c', 'd', 'e',
							'f', 'g', 'h', 'i', 'j',
							'k', 'l', 'm', 'n', 'o',
							'p', 'q', 'r', 's', 't',
							'u', 'v', 'w', 'x', 'y',
							'z');

		$_result = str_replace($replace_of, $replace_by, $_result);

		return $_result;
	}

}
?>
