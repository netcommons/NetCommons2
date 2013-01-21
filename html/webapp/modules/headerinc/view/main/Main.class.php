<?php

require_once MAPLE_DIR.'/nccore/SmartyTemplate.class.php';

/**
 * headタグに出力するscript,cssファイル書き出す
 *
 * @package
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license     [[license]]
 * @access      public
 */

class Headerinc_View_Main extends Action
{
	// 使用コンポーネントを受け取るため
	var $requestMain=null;

	// リクエストパラメータを受け取るため
	var $action_name = null;
	var $block_id = null;
	var $theme_name = null;
	var $temp_name = null;
	var $print_flag = null;
	var $page_id = null;

	function execute()
	{
		$renderer =& SmartyTemplate::getInstance();
        $this->theme_name = ($this->theme_name == "") ? "system" : $this->theme_name;
		if($this->action_name != "") {
			$curPath = MODULE_DIR;
			$pathList = explode("_", $this->action_name);
			//$count = count($pathList);
			$now_count = 1;

 			$css = array();
 			$js = array();

 			$themeStrList = explode("_", $this->theme_name);
			if(count($themeStrList) == 1) {
				$themeDir = "themes/".$this->theme_name."/css";
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$themeDir = "themes/".$bufthemeStr."/css/".implode("/", $themeStrList);
			}
			$css["blocks:style.css".$this->theme_name] = $themeDir . "/style.css";
			foreach ($pathList as $path) {
 				//$curPath .= "${path}/";
 				$curPath .= "/" . $path;
 				$include = $this->_getConfig($curPath,($now_count == count($pathList)));
				if($include) {
 					foreach ($include as $key => $value) {
 						//1つにjsファイルを結合したため、コメント
 						//jsファイルを画面毎で分けると
						//画面遷移時のインクルードがうまく動作しない
						//今後、jsファイルのインクルードが正常に動くようにブラウザが対応した場合、
						//以下の処理を使用可能
						/*
						if($key == "js" || preg_match("/_js/", $key)) {
							$include_files = $value;
							$include_pathList = explode(",", $include_files);

							foreach($include_pathList as $include_file){
								if($include_file != "") {
									if(!isset($js[$include_file.":".$this->temp_name])) {
										$pathList = explode(":", $include_file);
										$file_path = "js/".$pathList[0] . "/" . $pathList[1];
										//if (@file_exists(HTDOCS_DIR."/".$file_path)) {
											$js[$include_file.":".$this->temp_name] = BASE_URL."/".$file_path;
										//}
									}
								}
					        }
						}
						*/
						//if($key == "css" || preg_match("/_css/", $key)) {
							$include_files = $value;
							$include_pathList = explode(",", $include_files);

							foreach($include_pathList as $include_file){
								if($include_file != "") {
									if(!isset($css[$include_file.":".$this->temp_name])) {
										$pathList = explode(":", $include_file);
										$file_path = $pathList[0] . "/" . $pathList[1];
										//if (@file_exists(HTDOCS_DIR."/".$file_path)) {
											$css[$include_file.":".$this->temp_name] = $file_path;
										//}
									}
								}
					        }
						//}
					}
 				}
 				/****
 				//key:js,css固定
 				if($include) {
 					if(isset($include["js"])) {
		 				$include_files = $include["js"];
		 				$include_pathList = explode(",", $include_files);

						foreach($include_pathList as $include_file){
							if($include_file != "") {
								if(!isset($js[$include_file.":".$this->temp_name])) {
									$pathList = explode(":", $include_file);
									$file_path = "js/".$pathList[0] . "/" . $pathList[1];

									//if (@file_exists(HTDOCS_DIR."/".$file_path)) {
										$js[$include_file.":".$this->temp_name] = BASE_URL."/".$file_path;
									//}
								}
							}
				        }
	 				}
	 				if(isset($include["css"])) {

	 					$include_files = $include["css"];
		 				$include_pathList = explode(",", $include_files);

						foreach($include_pathList as $include_file){
							if($include_file != "") {
								if(!isset($css[$include_file.":".$this->temp_name])) {
									$pathList = explode(":", $include_file);
									$file_path = "css/".$pathList[0] . "/" . $pathList[1];
									//if (@file_exists(HTDOCS_DIR."/".$file_path)) {
										$css[$include_file.":".$this->temp_name] = BASE_URL."/".$file_path;
									//}
								}
							}
				        }
	 				}
 				}
 				***/
 				$now_count++;
 			}
 			$headerinc_arr = serialize(array("css" => $css));
 			//$headerinc_arr = serialize(array("js" => $js, "css" => $css));
 			$renderer->assign('headerinc_arr',$headerinc_arr);

 			//キャッシュ処理をしない
			$caching = $renderer->getCaching();
    		$renderer->setCaching(0);

 			$template_dir = MODULE_DIR . "/headerinc/templates/";
			$template = "headerinc.html";

			//template_dirセット
			$renderer->setTemplateDir($template_dir);
			$result = $renderer->fetch($template,null,"/headerinc/templates/");

			//キャッシュ処理を元に戻す
			$renderer->setCaching($caching);

			if($this->print_flag)
				print $result;
			else
				return $result;
		}
		return 'success';
	}

	//action_name,theme_name,thmp_name Setter
	//自サイトから呼び出す場合、使用
	function setParams($action_name, $block_id, $theme_name, $temp_name) {
		$this->action_name = $action_name;
		$this->block_id = $block_id;
		$this->theme_name = $theme_name;
		$this->temp_name = $temp_name;
	}

	/**
     * Pageに配置されたモジュールの設定ファイルを読み込み
     * HeaderIncの項目の値をreturn
     *
     * @param   string  $path   設定ファイルを読むパス
     * @param   boolean $level   最下層に達したかどうか？
     * @return  string  $include_script	インクルードするスクリプト
     * @access  private
     */
    function _getConfig($path, $level = false)
    {
        $filename = $path . "/" . CONFIG_FILE;

        if (!@file_exists($filename)) {
            return false;
        }

		if (version_compare(phpversion(), '5.0.0', '>=')) {
			$initializer =& DIContainerInitializerLocal::getInstance();
			$config = $initializer->read_ini_file($filename, true);
		} else {
			$config = parse_ini_file($filename, TRUE);
		}
		$configValue = array();
        //
        // Actionと同じレベルかどうかで処理を切り分け
        //
        //TODO:GlobalFilterの定義が変わったため、Actionと同じレベルかみないようにとりあえず修正
        //if ($level != true) {
        //    //
        //    // GlobalFilterセクションに記述されているHeaderIncフィルターをセットする
        //    //
        //    if (isset($config["GlobalFilter"]) &&
        //        is_array($config["GlobalFilter"]) &&
        //        isset($config["HeaderInc"])) {
        //        foreach ($config["GlobalFilter"] as $key => $value) {
        //            if ($key == "HeaderInc") {
        //                return $config["HeaderInc"];
        //            }
        //        }
        //    }
        //} else {
        	if (isset($config["HeaderInc"])) {
        		return $config["HeaderInc"];
            }
        //}
        return false;
    }
}
?>
