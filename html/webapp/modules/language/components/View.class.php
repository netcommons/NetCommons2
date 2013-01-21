<?php
/**
 * 言語切替表示用クラス
 *
 * @package     [[package名]]
 * @author      Ryuji Masukawa
 * @copyright   copyright (c) 2006 NetCommons.org
 * @license     [[license]]
 * @access      public
 */
class Language_Components_View {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Language_Components_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * ブロックIDから言語管理データ取得
	 * @param int block_id
	 * @access	public
	 */
	function &getLanguageById($id) {
		$params = array(
			"block_id" => $id
		);
		
		$result = $this->_db->selectExecute('language_block' ,$params);
		if($result === false) {
			return $result;
		}
		return $result[0];
	}
	
	/**
	 * 言語用デフォルトデータを取得する
	 *
     * @return array	言語用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultLanguage() {
		$configView =& $this->_container->getComponent("configView");
		$request =& $this->_container->getComponent("Request");
		$module_id = $request->getParameter("module_id");
		$config = $configView->getConfig($module_id, false);
		if ($config === false) {
			return $config;
		}		
		$lang = array(
			'display_type' => empty($config['display_type']['conf_value'])?LANGUAGE_DISPLAY_TYPE_LIST:$config['display_type']['conf_value'],
			'display_language' => empty($config['display_language']['conf_value'])?LANGUAGE_JAPANESE:$config['display_language']['conf_value']
		);
		
		return $lang;
	}
	
	/**
	 * 表示言語データを取得する
	 *
     * @return array	言語データ配列
	 * @access	public
	 */
	function &getDisplayLanguage($select_flag=false) {		
		$request =& $this->_container->getComponent('Request');
		$session =& $this->_container->getComponent("Session");
		$block_id = $request->getParameter('block_id');
		$lang_block = $this->_db->selectExecute('language_block', array('block_id' => $block_id));
		if(empty($lang_block)) return false;
		
		$name = $request->getParameter("_restful_permalink");
		$where_params = array('permalink' => $name);	
		$pages = $this->_db->selectExecute('pages', $where_params, null, null, null, array($this, "_fetchcallbackPages"));
		
		$uri_arr = explode('?', $_SERVER['REQUEST_URI']);
		$uri = isset($uri_arr[1]) ? $uri_arr[1] : null;
		$_permalink_flag = $session->getParameter("_permalink_flag");
		if(!empty($_permalink_flag)) {
			$restful_permalink = $name;
			if(!empty($restful_permalink)) {
				$restful_permalink = $restful_permalink.'/';
			}				
		} else {
			$restful_permalink = "";
		}
		$return_top = strpos($uri, 'action=language_view_main_init');
		if(!empty($uri) && $return_top === false) {
			$patterns = array('/&?lang=[^&]*/i', '/^&/');
			$replacements = array('', '');
			$uri = preg_replace($patterns, $replacements, $uri);
			if($uri != "") $uri = '?'.$uri.'&';
			else $uri = '?'.$uri;
		} else {
			$uri = '?';
		}
		
		$fetch_params = array(
			'select_flag' => $select_flag,
			'display_language' => $lang_block[0]['display_language'],
			'restful_permalink' => $restful_permalink,
			'uri' => $uri,
			'pages' => $pages
		);
		$langs = $this->_db->selectExecute('language', null, array('display_sequence' => 'ASC'), 0, 0, array($this,"_getDisplayLanguagesFetch"), $fetch_params);
		
		return $langs;
	}
	
		/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackPages($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(!empty($row['lang_dirname']))
				$ret[$row['lang_dirname']] = $row;
		}
		return $ret;
	}
	
	/**
	 * 表示言語のコールバック関数
	 *
	 * @access	private
	 */
	function _getDisplayLanguagesFetch(&$recordSet, $fetch_params) {
		
		$data = array();
		$display_lang_arr = explode('|',$fetch_params['display_language']);
		while ($row = $recordSet->fetchRow()) {
			if (defined($row["display_name"])) {
				$name = constant($row["display_name"]);
			} else {
				$name = ucfirst($row["lang_dirname"]);
			}
			$display = false;
			foreach($display_lang_arr as $display_lang) {
				if($row["lang_dirname"] == $display_lang) {
					$display = true;
					break;
				}
			}
			if($fetch_params['select_flag']) {
				if($display) {
					$data[$row["lang_dirname"]]['name'] = $name;
					$data[$row["lang_dirname"]]['display'] = true;
				}
			}else {
				$data[$row["lang_dirname"]]['name'] = $name;
				$data[$row["lang_dirname"]]['display'] = $display;
			}
			if(isset($data[$row["lang_dirname"]]) && $data[$row["lang_dirname"]]['display']) {
				if(empty($fetch_params['pages']) || !empty($fetch_params['pages'][$row["lang_dirname"]])) {
					$data[$row["lang_dirname"]]['link'] = BASE_URL.'/'.$fetch_params['restful_permalink'].$fetch_params['uri'].'lang='.$row["lang_dirname"];
				} else {
					$data[$row["lang_dirname"]]['link'] = BASE_URL.'/'.$fetch_params['uri'].'lang='.$row["lang_dirname"];
				}
			}
		}
		return $data;
	}
}
?>
