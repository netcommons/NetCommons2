<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once MAPLE_DIR.'/core/Request.class.php';
define('REQUESTEXTRA_VALUE_LEN', 255);
define('REQUESTEXTRA_VALUE_MAX_LEN', 2083);

/**
 * POST/GETで受け取った値を格納する
 *
 * @package     Maple
 * @author      Ryuji Masukawa
 * @copyright
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 */
class RequestExtra extends Request
{

    var $_str_params = null;
    var $_str_params_parent = null;

    //var $_doubtful_requests = null;

    var $_container = null;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function RequestExtra()
    {
    	//POST の場合、POSTとGETをマージしてリクエストとする
        //$this->Request();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        	$request = $_POST + $_GET;
            //$request = $_POST;
        } else {
            $request = $_GET;
        }

        if (get_magic_quotes_gpc()) {
            $request = $this->_stripSlashesDeep($request);
        }

        if (!ini_get("mbstring.encoding_translation") &&
            (INPUT_CODE != INTERNAL_CODE)) {
			mb_convert_encoding($request, INTERNAL_CODE, INPUT_CODE);
             //mb_convert_variables(INTERNAL_CODE, INPUT_CODE, $request);
        }

        $this->_params = $request;

        //$this->_rawurlDecode($this->_params);

        //$url = BASE_URL.INDEX_FILE_NAME.$this->getStrParameters();
		//define("CURRENT_URL",$url);

		$this->_container =& DIContainerFactory::getContainer();
    }
    /**
     * パラメータチェック
     * @param int $security_chk_flag
     *
     * @access  public
     */
	function chkRequest($security_chk_flag = false)
    {
    	return $this->_rawurlDecode($this->_params, $security_chk_flag);
    }

    /**
     * パラメータDecode
     *
     * @access  private
     */
    function _rawurlDecode(&$params, $security_chk_flag = false) {
    	$getdata =& $this->_container->getComponent("GetData");
    	$config =& $getdata->getParameter("config");
    	if($security_chk_flag) {
    		$securityManager =& $this->_container->getComponent("securityManager");
    	}
    	$actionChain =& $this->_container->getComponent("ActionChain");
       	$errorList =& $actionChain->getCurErrorList();

    	$id_forceintval_flag = false;
    	if(isset($config[_SECURITY_CONF_CATID]['id_forceintval']) &&
    		$config[_SECURITY_CONF_CATID]['id_forceintval']['conf_value'] == _ON) {
	    	$id_forceintval_flag = true;
    	}
		$nocheck_name = _REGEXP_REQUEST_ID_NO_CHECK_NAME;

    	if(is_array($params)) {
    		foreach($params as $key => $value) {
    			//
    			// 変数汚染が見つかった時の処理
    			//
    			if($security_chk_flag) {
    				$securityManager->chkContamiAction($key);
    			}

    			if(is_array($value)) {
    				$this->_rawurlDecode($params[$key]);
    			} else {
    				if($security_chk_flag) {
	    				// ヌル文字列をスペースに変更する
	    				$securityManager->chkNullByte($value);
	    				//疑わしいファイル指定の禁止
						$securityManager->chkParentDir($value);

						// 疑わしい値のリクエストをチェック
						if( preg_match( '?[\s\'"`/]?' , $value ) ) {
							//孤立コメントが見つかった時の処理
							$securityManager->chkIsocomAction($value);

							//UNIONが見つかった時の処理
							$securityManager->chkUnionAction($value);
						}
    				}

					//変数名が_idで終わるものを、数字だと強制認識させます。
    				if(!preg_match($nocheck_name, $key) && substr( $key , -3 ) == '_id') {
    					$params[$key] = (string) intval($value);
    				} else {
    					$params[$key] = $value;
    				}
    			}
    		}
    	}

    	if ($errorList->isExists()) {
    		return false;
    	}
    	return true;
    }

	function clear()
    {
    	unset($this->_params);
    	$this->_str_params = null;
    	$this->_params = null;
    }

    /**
     * POST/GETの値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   string  $value  パラメータの値
     * @access  public
     */
    function setParameter($key, $value)
    {
    	$this->_params[$key] = $value;
        //初期化
        $this->_str_params = null;
        $this->_str_params_parent = null;
    }

    /**
     * POST/GETの値をセット(オブジェクトをセット)
     *
     * @param   string  $key    パラメータ名
     * @param   Object  $value  パラメータの値
     * @access  public
     */
    function setParameterRef($key, &$value)
    {
        $this->_params[$key] =& $value;
        //初期化
        $this->_str_params = null;
        $this->_str_params_parent = null;
    }


    /**
     * POST/GETの値をセット
     *
     * @param   array  $params    パラメータ配列
     * @access  public
     */
    function setParameters($params)
    {
    	if($this->_params == null) {
    		$this->_params =& $params;
    	} else {
    		$this->_params = array_merge($this->_params, $params);
    	}
    }

    /**
     * POST/GETの値を返却(文字列で返却)
     * @param boolean parent_flag(trueならば親が指定したパラメータを含める)
     * @return  string  パラメータの値
     * @access  public
     */
    function getStrParameters($parent_flag = true)
    {
    	$session =& $this->_container->getComponent("Session");
    	$_permalink_flag = $session->getParameter("_permalink_flag");
    	$actionChain =& $this->_container->getComponent("ActionChain");
		$action_name = $actionChain->getCurActionName();

    	if($this->_str_params == null) {
    		//
    		// POSTした値を使って画面を描画しようとしたとき、input name=_urlの項目が長くなってしまいsendRefreshを呼ぶ際に
    		// 「The requested URL's length exceeds the capacity limit for this server.」のエラーに可能性があるため修正
    		//
	    	$str = "";
	    	$main_str = "";
	    	$str_parent = "";
	    	$main_params_arr = array("page_id","action_name","block_id","module_id","active_center","active_block_id",
	    								"prefix_id_name","active_action","theme_name");
	    	foreach($this->_params as $key => $value) {
	    		if(!is_array($value)) {
	    			//
	    			// researchmap用にカスタマイズ
	    			//
	    			if($key == "_restful_permalink") {
		    			$value = rawurldecode($value);
		    		}
		    		//配列の場合、パラメータ連結しない
		    		if($key == "action") {
		    			// actionは常にActiveActionをセット
		    			$key = "action";
		    			$value = $action_name;
		    			if($action_name != DEFAULT_ACTION) {
		    				$value = $action_name;
		    			} else {
		    				$value = null;
		    			}
		    		} else if($key == "block_id" && intval($value) == 0) {
		    			$value = null;
		    		} else if($_permalink_flag == _ON && $key == "page_id" && !isset($_REQUEST['page_id']) &&
		    					$action_name == DEFAULT_ACTION) {
		    			$value = null;
		    		} else {
		    			$key = htmlspecialchars($key, ENT_QUOTES);
		    			$value = rawurlencode($value);
		    		}

		    		//$value = htmlspecialchars($value, ENT_QUOTES);
		    		if($value != null) {
		    			if($key == "room_id" || substr($key, 0, 1) == "_") {
		    				$str_parent.= "&" . $key."=".$value;
		    			} else if(in_array($key, $main_params_arr)) {
		    				$main_str.= "&" . $key."=".$value;
		    			} else if(strlen($value) <= REQUESTEXTRA_VALUE_LEN) {
		    				$str.= "&" . $key."=".$value;
		    			}
		    		}
	    		}
	    	}
	    	if($main_str.$str != "") {
		    	if(strlen($main_str.$str) <= REQUESTEXTRA_VALUE_MAX_LEN) {
		    		$str = $main_str.$str;
		    		$str = "?".substr($str, 1,strlen($str) - 1);

		    	} else {
		    		$str = "?".substr($main_str, 1,strlen($main_str) - 1);
		    	}
	    	}
	    	$this->_str_params = $str;
	    	if($str=="" && $str_parent != "") {
	    		$str_parent = "?".substr($str_parent, 1,strlen($str_parent) - 1);
	    	}
	    	$this->_str_params_parent = $str_parent;
    	}

    	if($parent_flag)
    		return $this->_str_params.$this->_str_params_parent;

    	return $this->_str_params;
    }

    /**
     * 与えられた値を削除（ViewFilter,action:指定時）
     *
     * @param   array  $params    パラメータ配列(Value)
     * @access  public
     */
    function removeParameters($params = null)
    {
    	//
    	//$params = array_merge($params, $notRemoves);

    	if($params == null) {
    		// nullならば
    		// "action", "page_id", "block_id", "module_id"以外のパラメータを削除
    		$notRemoves = array("action", "page_id", "block_id", "module_id");
    		foreach(array_keys($this->_params) as $key) {
	    		if(!in_array($key, $notRemoves) && substr($key, 0, 1) != "_") {
	    			//削除
	    			$this->_params[$key] = null;
	    		}
	    	}
    		return;
    	}
    	foreach(array_keys($this->_params) as $key) {
    		if(in_array($key, $params)) {
    		//if(!in_array($key, $params) && substr($key, 0, 1) != "_") {
    			//削除
    			$this->_params[$key] = null;
    		}
    	}
    }
}
?>
