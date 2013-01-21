<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Config表示用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Config_View
{
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

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access private
	 */
	var $_session = null;

	/**
	 * @var NetCommonsのバージョンから、多言語対応有無を保持
	 *
	 * @access	public
	 */
	var $isMultiLanguage = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Config_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent('Session');

		$this->isMultiLanguage = false;
		if (empty($this->_session)) {
			return;
		}

		$params = array(
			'version'
		);
		$sql = "SELECT conf_value ".
				"FROM {config} ".
				"WHERE conf_name = ?";
		$configs = $this->_db->execute($sql, $params);
		if ($configs === false) {
			$this->_db->addError();
			return false;
		}
		if (empty($configs)) {
			return false;
		}

		$versions = explode('.', $configs[0]['conf_value']);
		if (intval($versions[0]) >= 2
			&& intval($versions[1]) >= 3) {
			$this->isMultiLanguage = true;
		}
	}

	/**
	 * META情報取得
	 * @return array
	 * @access	public
	 */
	function getMetaHeader($page_id_arr = null, $dir_name_arr=null, $system_flag=false)
	{
		$getdata =& $this->_container->getComponent("GetData");
		$token =& $this->_container->getComponent("Token");
		$session =& $this->_container->getComponent("Session");
		$common =& $this->_container->getComponent("commonMain");
		$meta = $session->getParameter("_meta");
		$getdata =& $this->_container->getComponent("GetData");
		$request =& $this->_container->getComponent("Request");

		$script_str =& $getdata->getParameter("script_str");

		//$meta['script_header'] = "";
		if($request->getParameter("_noscript") == _ON) {
			$meta['script_header'] = "";
		} else {
			if(_SCRIPT_OUTPUT_POS == "header") {
				$scriptStr = $this->_getScript($dir_name_arr, $system_flag);
					//"<script type=\"text/javascript\">".$getdata->getParameter("script_str")."</script>";
			} else {
				$scriptStr = "";
			}
			$meta['script_header'] = $scriptStr;
		}

		//theme_name
		//$meta['css_header'] = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./themes/css/style.css" ."\" />";
		//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./themes/system/css/style.css" ."\" />";
		$meta['css_header']["/themes/system/css/style.css"] = "/themes/system/css/style.css";

		if(isset($page_id_arr) && $page_id_arr != 0) {
			if(is_array($page_id_arr)) {
				$page_id = $page_id_arr[0];
			} else {
				$page_id = $page_id_arr;
			}
			$pages = $getdata->getParameter("pages");
			if(!isset($pages[$page_id])) {
				// 再帰的(action:)に呼ぶ過程でpage_idを途中からセットした場合、setDafaultでセットしていないので取得
				$pagesView =& $this->_container->getComponent("pagesView");
				$buf_pages_obj =& $pagesView->getPageById($page_id);
				$pages[$page_id] =& $buf_pages_obj;
			}

			$themeStr = $pages[$page_id]['theme_name'];
			$tempStr = $pages[$page_id]['temp_name'];

			//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./themes/css/page_style.css" ."\" />";
			$themeStrList = explode("_", $themeStr);
			if(count($themeStrList) == 1) {
				$themeCssPath = "/themes/".$themeStr."/css";
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$themeCssPath = "/themes/".$bufthemeStr."/css/".implode("/", $themeStrList);
			}
			$meta['css_header']["/pages/".$tempStr. "/page_style.css"] = "/pages/".$tempStr. "/page_style.css";
			$meta['css_header'][$themeCssPath. "/page_style.css"] = $themeCssPath. "/page_style.css";
			//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"." . $themeCssPath. "/page_style.css" ."\" />";
			// カスタム用CSS
			//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"".CORE_BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=common_download_css&dir_name=" . $themeStr ."&type="._CSS_TYPE_PAGE_CUSTOM."\" />";

			//$footer['script_footer'] .= "<script type=\"text/javascript\">";
			$page_token_arr = array();
			$active_center_action = $request->getParameter("active_center");
			if(isset($active_center_action)) $active_center = _ON;
			else  $active_center = _OFF;
			$script_str .= "pagesCls.pageInit(".$active_center.");";
			if($token) {
				if(is_array($page_id_arr)) {
					foreach($page_id_arr as $page_id) {
						$page_id = intval($page_id);
						if(isset($pages[$page_id])) {
							$token->setName(array($page_id, DEFAULT_ACTION));
							$script_str .= "pagesCls.setShowCount(" .$page_id.",". $pages[$page_id]['show_count'] . ");";
							if($pages[$page_id]['display_position'] == _DISPLAY_POSITION_CENTER) {
								$script_str .= "pagesCls.setToken(" .$page_id.",\"". $token->getValue() . "\",true);";
							} else {
								$script_str .= "pagesCls.setToken(" .$page_id.",\"". $token->getValue() . "\");";
							}
						}
					}
				} else {
					$page_id_arr = intval($page_id_arr);
					$token->setName(array($page_id_arr, DEFAULT_ACTION));
					if(isset($pages[$page_id_arr])) {
						$script_str .= "pagesCls.setShowCount(" .$page_id_arr.",". $pages[$page_id_arr]['show_count'] . ");";
					}
					$script_str .= "pagesCls.setToken(" .$page_id_arr.",\"". $token->getValue() . "\",true);";
				}
			}
			//$footer['script_footer'] .= "</script>";
		} else {
			//
			// サイト閉鎖中のアクション実行中の場合、page_style.cssを読み込む
			//
			$actionChain =& $this->_container->getComponent("ActionChain");
			$action_name = $actionChain->getCurActionName();
			if($action_name == "pages_view_closesite") {
				$meta['css_header']['/css/page_style.css'] = '/css/page_style.css';
				//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./themes/css/page_style.css" ."\" />";
			}
		}
		////$getdata->setParameter("script_str",$script_str);
		return $meta;
	}

	/**
	 * Footer情報終了処理
	 * @param  boolean $system_flag
	 * @return array
	 * @access	public
	 */
	function terminateFooter($dir_name_arr=null, $system_flag=false)
	{
		$getdata =& $this->_container->getComponent("GetData");
		$request =& $this->_container->getComponent("Request");

		$footer =& $getdata->getParameter("footer_field");

		if($request->getParameter("_noscript") == _ON) {
			$footer['script_footer'] = "";
		} else {
			if(_SCRIPT_OUTPUT_POS == "footer") {
				$scriptStrSrc = $this->_getScript($dir_name_arr, $system_flag);
				$scriptStr = $getdata->getParameter("script_str_all");
				$scriptStr .= "<script type=\"text/javascript\">".$getdata->getParameter("script_str")."</script>";
			} else {
				$scriptStrSrc = "";
				$scriptStr = "";
				$footer['script_header'] = "<script type=\"text/javascript\">".$getdata->getParameter("script_str")."</script>";
			}
			$footer['script_footer_src'] = $scriptStrSrc;
			$footer['script_footer'] = $scriptStr;
		}
		return $footer;
	}
	/**
	 * Script文字列取得
	 * @param  array $dir_name_arr
	 * @param  boolean $system_flag
	 * @return string
	 * @access	public
	 */
	function _getScript($dir_name_arr=array(), $system_flag=false)
	{
		$session =& $this->_container->getComponent("Session");

		$lang = $session->getParameter("_lang");
		if (CORE_BASE_URL == BASE_URL && isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$base_url = BASE_URL_HTTPS;
		} else {
			$base_url = CORE_BASE_URL;
		}
		$script_name = $base_url.INDEX_FILE_NAME."?action=common_download_js";

		// パラメータの順番を同じようにするためソート(できるだけCasheで表示してもらうため)
		if($dir_name_arr != null) {
			sort($dir_name_arr);
			$script_name .= "&amp;dir_name=" .htmlspecialchars(implode("|", $dir_name_arr), ENT_QUOTES);
		}
		if($session->getParameter("_system_flag") || $system_flag) {
			$script_name .= "&amp;system_flag=" . _ON;
		} else {
			$script_name .= "&amp;system_flag=" . _OFF;
		}

		$lang_script_name = "";
		if (defined("_JS_VERSION")) {
			$script_name .= "&amp;vs=" . _JS_VERSION;
			$lang_script_name .= "?vs=" . _JS_VERSION;
		}
		$scriptStr = "<script type=\"text/javascript\" src=\"".$base_url."/js/".$lang."/lang_common.js".$lang_script_name."\"></script>".
									"<script type=\"text/javascript\" src=\"".$script_name."\"></script>";
		return $scriptStr;
	}

	/**
	 * conf_modidよりConfig情報取得
	 * @return array
	 * @access	public
	 */
	function &getConfig($conf_modid, $catid_as_key = true)
	{
		$fetchFunction = array($this, "_fetchcallbackGetConfig");
		$fetchFunctionParameters = array($catid_as_key);

		if (!$this->isMultiLanguage) {
			$where_params = array(
				"conf_modid" => $conf_modid
			);

			$configs =& $this->_db->selectExecute("config", $where_params, null, null, null, $fetchFunction, $fetchFunctionParameters);
		} else {
			$params = array(
				$this->_session->getParameter('_lang'),
				$conf_modid,
			);
			$sql = $this->_getConfigSQL();
			$configs = $this->_db->execute($sql, $params, null, null, true, $fetchFunction, $fetchFunctionParameters);
			if ($configs === false) {
				$this->_db->addError();
			}
		}

		return $configs;
	}
	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array configs
	 * @access	private
	 */
	function _fetchcallbackGetConfig($result, $func_param=null)
	{
		$config = array();
		$catid_as_key = null;
		if(isset($func_param[0])) $catid_as_key = $func_param[0];

		while ($row = $result->fetchRow()) {
			if (isset($row['CLValue'])) {
				$row['conf_value'] = $row['CLValue'];
			}

			if ($catid_as_key) {
				$config[$row['conf_catid']][$row['conf_name']] = $row;
			} else {
				$config[$row['conf_name']] = $row;
			}
		}
		return $config;
	}

	/**
	 * conf_catidよりConfig情報取得
	 *
	 * @param   int      $conf_modid  モジュールID
	 * @param   int      $conf_catid  カテゴリID
	 * @return array
	 * @access	public
	 */
	function &getConfigByCatid($conf_modid, $conf_catid, $limit = null, $offset = null, $func=null, $func_param=null)
	{
		if ($func == null) {
			$func = array($this, '_fetchcallbackGetConfig');
		}

		if (!$this->isMultiLanguage) {
			$where_params = array(
				"conf_modid"=>$conf_modid,
				"conf_catid" => $conf_catid
			);
			$order_params = null;
			$configs =& $this->_db->selectExecute("config", $where_params, $order_params, $limit, $offset, $func, $func_param);
		} else {
			$params = array(
				$this->_session->getParameter('_lang'),
				$conf_modid,
				$conf_catid
			);
			$sql = $this->_getConfigSQL()
					. "AND C.conf_catid = ?";
			$configs = $this->_db->execute($sql, $params, $limit, $offset, true, $func, $func_param);
			if ($configs === false) {
				$this->_db->addError();
			}
		}

		return $configs;
	}

	/**
	 * conf_nameよりConfig情報取得
	 *
	 * @param   int      $conf_modid  モジュールID
	 * @param   string   $conf_name   conf名
	 * @return array
	 * @access	public
	 */
	function &getConfigByConfname($conf_modid, $conf_name)
	{
		if (!$this->isMultiLanguage) {
			$where_params = array(
				"conf_modid" => $conf_modid,
				"conf_name" => $conf_name
			);
			$configs =& $this->_db->selectExecute("config", $where_params);
			if ($configs === false) {
				return $configs;
			}
		} else {
			$params = array(
				$this->_session->getParameter('_lang'),
				$conf_modid,
				$conf_name
			);
			$sql = $this->_getConfigSQL()
					. "AND C.conf_name = ?";
			$configs = $this->_db->execute($sql, $params);
			if ($configs === false) {
				$this->_db->addError();
				return $configs;
			}
		}

		if(empty($configs)) {
			$configs = null;
			return $configs;
		}

		$config = $configs[0];
		if (isset($config['CLValue'])) {
			$config['conf_value'] = $config['CLValue'];
		}

		return $config;
	}

	/**
	 * コンフィグテーブルとコンフィグ言語テーブルを結合するSQL文字列を取得する
	 *
	 * @return string SQL文字列
	 * @access private
	 */
	function &_getConfigSQL()
	{
		$sql = "SELECT C.*, "
					. "CL.conf_value AS CLValue "
				. "FROM {config} C "
					. "LEFT JOIN {config_language} CL "
					. "ON C.conf_name = CL.conf_name "
						. "AND CL.lang_dirname = ? "
				. "WHERE C.conf_modid = ? ";
		return $sql;
	}
}
?>