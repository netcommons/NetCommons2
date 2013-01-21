<?php


 /**
 * URLマッピング用Filter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Restfulurl extends Filter {
	
    var $_container;

    var $_log;

    var $_filterChain;
    
    var $_actionChain;
    
    var $_request;
    
    var $_session;
     
    var $_className;
    
    var $_db;
    
    var $_abbreviateurlView;
    
    var $_errorList;
    
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_Restfulurl() {
		parent::Filter();
	}

	/**
	 * URLマッピング用Filter
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_request =& $this->_container->getComponent("Request");
        $this->_session =& $this->_container->getComponent("Session");
        $this->_db =& $this->_container->getComponent("DbObject");
        $this->_abbreviateurlView =& $this->_container->getComponent("abbreviateurlView");
        
        $this->_className = get_class($this);
        
        $this->_errorList =& $this->_actionChain->getCurErrorList();
    	
    	$this->_prefilter();
    	
        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
        

        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}
	
	/**
     * プレフィルタ
     * 初期処理を行う
     * @access private
     */
    function _prefilter()
    {
    	if ($this->_errorList->isExists()) {
    		//既にエラーがあればそのまま返却
    		return;	
    	}
    	if(DEFAULT_ACTION == 'install_view_main_init') {
    		return;
    	}
    	
    	$page_id = $this->_request->getParameter("page_id");
    	$_permalink_flag = $this->_session->getParameter("_permalink_flag");

    	$name = $this->_request->getParameter("_restful_permalink");
    	$name = str_replace("%2F", "/", $name);

    	$block_id = $this->_request->getParameter("block_id");
    	// $name = mb_convert_encoding(rawurldecode($name), 'UTF-8', 'auto');
    	if (isset($name) && $name != "") {
    		$name_strlen = strlen($name);
    		if(substr($name, 0, 1) == "/") {
				// 最初の「/」を除去
				$name = substr($name, 1, $name_strlen - 1);
				$name_strlen = strlen($name);
			}
			if(substr($name, $name_strlen-1, $name_strlen) == "/") {
				// 最後の「/」を除去
				$name = substr($name, 0, $name_strlen - 1);
				$name_strlen = strlen($name);
			}
			if(substr($name, $name_strlen - strlen(INDEX_FILE_NAME), $name_strlen) == INDEX_FILE_NAME) {
				// 最後の「/index.php」を除去
				$name = substr($name, 0, $name_strlen - strlen(INDEX_FILE_NAME));
			}
			if($_permalink_flag == _ON) {
				$this->_request->setParameter("_restful_permalink", $name);
			}
			if (isset($name) && $name != "" &&
				$name != _PERMALINK_PUBLIC_PREFIX_NAME &&
				$name != _PERMALINK_MYPORTAL_PREFIX_NAME &&
				$name != _PERMALINK_PRIVATE_PREFIX_NAME &&
				$name != _PERMALINK_GROUP_PREFIX_NAME) {
				$where_params = array('permalink' => $name);			
				$pages = $this->_db->execute("SELECT * FROM {pages} WHERE permalink=?",$where_params);
				if ($pages !== false && isset($pages[0])) {
					if(!isset($page_id) || $page_id == "0") {
						if(empty($pages[0]['lang_dirname'])) {
							$page_id = $pages[0]["page_id"];
						}else {
							//言語切替
							$lang = $this->_session->getParameter('_lang');
							$break_flag = false;
							foreach ($pages as $page) {
								if($page['lang_dirname'] == $lang){
									$page_id = $page["page_id"];
									$break_flag = true;
									break;
								}
							}
							if(!$break_flag) {
								$page_id = $pages[0]["page_id"];
								//$this->_request->setParameter('_restful_permalink', $pages[0]['permalink']);
							}
							
							// 掲示板等の固定リンクから言語切替時に、エラーとなるため対処
							if(!empty($block_id)) {
								$blocksView =& $this->_container->getComponent("blocksView");
								$buf_block = $blocksView->getBlockById($block_id);
								if($buf_block['page_id'] != $page_id) {
									$page_id = $buf_block['page_id'];
								}
							}
						}
					}
					$this->_request->setParameter("page_id", $page_id);
	    		} else if(!$this->_getAbbreviateurl($name)){
					$commonMain =& $this->_container->getComponent("commonMain");
	    			if($this->_session->getParameter('_lang') == 'japanese') {
						$commonMain->redirectHeader('', 3, '存在しないページが指定されました。');
					}else if($this->_session->getParameter('_lang') == 'chinese') {
						$commonMain->redirectHeader('', 3, '页面不存在。');
					}else {
						$commonMain->redirectHeader('', 3, 'This page does not exist.');
					}
				}
			}
			if($name == _PERMALINK_PRIVATE_PREFIX_NAME) $this->_request->setParameter("page_id", -1);
    	} else {
    		$name = $this->_request->getParameter(_ABBREVIATE_URL_REQUEST_KEY);
    		if(!$this->_getAbbreviateurl($name)) {
	    		if(empty($page_id)) return;
	    		$page = $this->_db->selectExecute('pages', array('page_id' => $page_id));
	    		if($page !== false && isset($page[0])) {
	    			if($page[0]['lang_dirname'] != '' && $page[0]['lang_dirname'] != $this->_session->getParameter('_lang')) {
	    				$this->_request->setParameter("page_id", $page[0]['root_id']);
	    			}
	    		}
    		}
    	}
    }
    
    /**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    }
    
    
    /**
	 * URL短縮形取得処理
	 * @access private
	 */
    function _getAbbreviateurl($name)
    {
    	$nameArray = explode("-", $name);
		$block_id = intval($this->_request->getParameter("block_id"));

		if (count($nameArray) > 2) { return false; }
		if (!preg_match("/^[".preg_quote(_ABBREVIATE_URL_PATTERN)."]+$/ius", $nameArray[0])) { return false; }
		if (count($nameArray) == 2 && !preg_match("/^[0-9]+$/iu", $nameArray[1])) { return false; }
		
		if (count($nameArray) == 2) {
				$block_id = intval($nameArray[1]);
			}
			
			//短縮形URLのデータ取得
			$params = array(
				'short_url' => $nameArray[0]
			);
			$abbreviateUrls = $this->_db->selectExecute('abbreviate_url', $params, null, 1);
			if (empty($abbreviateUrls)) { return false; }

			$dirnameArray = explode("_", $abbreviateUrls[0]['dir_name']);
			$module_name = $dirnameArray[0];

			$_blockcheck = $this->_abbreviateurlView->getIniFile($module_name, "_blockcheck");
			
			//block_id取得
			if ($block_id > 0 && (!isset($_blockcheck)) || $_blockcheck == "check") {
				$sql = "SELECT {blocks}.block_id, {blocks}.module_id, {pages}.room_id" .
						" FROM {blocks},{pages}" .
						" WHERE {blocks}.page_id = {pages}.page_id" .
						" AND {blocks}.block_id = ?";
				$params = array(
					'block_id' => $block_id
				);
				$blocks = $this->_db->execute($sql, $params, 1);
				if ($blocks === false) { return false; }
				if (empty($blocks)) {
					$block_id = 0;
				} elseif ($blocks[0]['module_id'] != $abbreviateUrls[0]['module_id'] || $blocks[0]['room_id'] != $abbreviateUrls[0]['room_id']) {
					return false;
				}
			}

			$block_sql = $this->_abbreviateurlView->getIniFile($module_name, "block_sql");

			if (!empty($block_sql)) {
				//install.iniにblock_id取得SQLが記述されている場合
				$pos = strpos($block_sql, '{abbreviate_url}');
				if ($pos !== false) {
					$params = array(
						'short_url' => $nameArray[0]
					);
					$sql = $block_sql . " AND {abbreviate_url}.short_url = ?";
					$block_id = $this->_db->execute($sql, $params, null, null, true, array($this,"_getBlockId"), array($block_id));
					if (empty($block_id)) { return; }
				} elseif ($block_id > 0) {
					$pos = strpos($block_sql, '{blocks}');
					$params = array(
						'block_id' => $block_id
					);
					$sql = $block_sql . " AND {blocks}.block_id = ?";
					$blocks = $this->_db->execute($sql, $params, 1);
					if (!empty($blocks)) {
						$block_id = $blocks[0]["block_id"];
					}
				}
			}
			if ($block_id == 0) { return false; }

			//URL短縮形変換
			$url_format = $this->_abbreviateurlView->getIniFile($module_name, $dirnameArray[0]);
			$patternParams = array('{block_id}', '{contents_id}', '{unique_id}');
			$replaceParams = array($block_id, $abbreviateUrls[0]['contents_id'], $abbreviateUrls[0]['unique_id']);
	
			$urlParamStr = str_replace($patternParams, $replaceParams, $url_format);
			$urlParams = explode("&", $urlParamStr);
			foreach ($urlParams as $param) {
				$params = explode("=", $param);
				$this->_request->setParameter($params[0], $params[1]);
			}
			return true;
    }
    
    	
	/**
	 * ブロック取得処理
	 * @access private
	 */
	function _getBlockId(&$recordSet, &$optParams)
	{
		while ($row = $recordSet->fetchRow()) {
			if ($optParams[0] == 0) {
				return $row["block_id"];
			}

			if (!isset($block_id)) {
				$block_id = $row["block_id"];
			}
			if ($optParams[0] == $row["block_id"]) {
				return $row["block_id"];
			}
		}
		return (!isset($block_id) ? 0 : $block_id);
	}
}
?>
