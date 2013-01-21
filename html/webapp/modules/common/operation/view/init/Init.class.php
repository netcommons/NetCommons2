<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 移動、コピー、ショートカットを行う画面表示
 * （現状、移動のみ実装：2008/05）
 * block_titleは「お知らせ」のようなタイプのでは、nullのまま
 * 掲示板のようなタイプならば、まず、掲示板のアクションに通したのちアクションを呼ぶ
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @param int    $block_id(必須)
 * @param string $parent_id_name(必須)
 * @param int    $module_id(必須)
 * @param string $block_title
 * @access      public
 */
class Common_Operation_View_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $parent_id_name = null;
	var $module_id = null;
	
	var $unique_id = null;
	
	// Filterによりセット
	//var $page_id_arr = null;
	//var $page_arr_flat = null;
	var $pages_list = null;
	
    // 使用コンポーネントを受け取るため
    var $modulesView = null;
    var $getdata = null;
    var $pagesView = null;
    var $actionChain = null;
    var $db = null;
    var $configView = null;
    
    // 値をセットするため
	var $title = null;
	
	var $block = null;
	var $module =null;
	var $page_name = null;
	
	var $_classname = "Common_Operation_View_Init";
	
	var $err_pos = false;
	var $column_space_type_use = null;
	var $headercolumn_page_id = null;
	var $leftcolumn_page_id = null;
	var $rightcolumn_page_id = null;
	
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	// データ取得
    	$errorList =& $this->actionChain->getCurErrorList();
    	$blocks =& $this->getdata->getParameter("blocks");	
    	$this->block =& $blocks[$this->block_id];
    	$this->module = $this->modulesView->getModulesById($this->module_id);
    	if($this->module === false) return 'error';
    	$buf_pages_obj = $this->pagesView->getPageById($this->block['page_id']);
    	if($buf_pages_obj === false || !isset($buf_pages_obj['page_id'])) return 'error';
    	
    	// dirname取得
    	$action_name = isset($this->module['action_name']) ? $this->module['action_name'] : null;
		if($action_name == null) {
			$errorList->add($this->_classname, _INVALID_INPUT);
    		return 'error';
		}
		$pathList = explode("_", $action_name);
		$dirname = $pathList[0];
		
		$buf_top_pages_obj = $this->pagesView->getPageById(_SELF_TOPPUBLIC_ID);
		if($buf_top_pages_obj['authority_id'] >= _AUTH_CHIEF) {
	    	//
	    	// configデータ取得
	    	// 左右カラム-ヘッダーを移動先に表示させるため
	    	//
	    	$config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _PAGESTYLE_CONF_CATID);
	        if ($config === false) {
				return 'error';
	        }
	        $this->column_space_type_use = $config['column_space_type_use']['conf_value'];
	        $headercolumn_page_id_str  = $config['headercolumn_page_id']['conf_value'];
	        $leftcolumn_page_id_str    = $config['leftcolumn_page_id']['conf_value'];
	        $rightcolumn_page_id_str   = $config['rightcolumn_page_id']['conf_value'];
	        
	        $headercolumn_page_id_arr = explode("|",$headercolumn_page_id_str);
			$leftcolumn_page_id_arr   = explode("|",$leftcolumn_page_id_str);
			$rightcolumn_page_id_arr  = explode("|",$rightcolumn_page_id_str);
	        if($this->column_space_type_use == _OFF) {
	        	$this->headercolumn_page_id = $headercolumn_page_id_arr[0];
	        	$this->leftcolumn_page_id = $leftcolumn_page_id_arr[0];
	        	$this->rightcolumn_page_id = $rightcolumn_page_id_arr[0];
	        } else {
	        	$this->headercolumn_page_id = $headercolumn_page_id_arr;
	        	$this->leftcolumn_page_id = $leftcolumn_page_id_arr;
	        	$this->rightcolumn_page_id = $rightcolumn_page_id_arr;
	        }
	        $this->unioncolumn_str = "|".$headercolumn_page_id_str ."|".$leftcolumn_page_id_str."|".$rightcolumn_page_id_str."|";
		}	
    	// install.ini読み込み
    	$file_path = MODULE_DIR."/".$dirname.'/install.ini';
		if (file_exists($file_path)) {
			$install_ini = $this->_read_ini_file($file_path);
	        if(isset($install_ini['Operation']['select_sql']) && isset($install_ini['Operation']['select_args'])) {
	        	$select_sql = $install_ini['Operation']['select_sql'];
	        	$select_args = explode(",", $install_ini['Operation']['select_args']);
	        	$where_params = array();
	        	if(count($select_args) > 0) {
		        	foreach($select_args as $select_arg) {
		        		switch($select_arg) {
		        			case "block_id":
		        				$where_params[$select_arg] = $this->block_id;
		        				break;
		        			case "page_id":
		        				$where_params[$select_arg] = $this->block['page_id'];
		        				break;	
		        			case "room_id":
		        				$where_params[$select_arg] = $buf_pages_obj['room_id'];
		        				break;
		        		}
		        	}
	        	}
	        	$result = $this->db->execute($select_sql, $where_params, null, null, false);
				if ($result === false) {
					$this->db->addError();
			       	return 'error';
				}
				if(!isset($result[0])) {
					//　配置されていない
					$this->err_pos = true;
					return 'success';
				}
				
				$this->unique_id = $result[0][0];
				if(isset($result[0][1])) {
	        		$this->title = $result[0][1];
				} else {
					$this->title = $blocks[$this->block_id]['block_name'];
				}
	        } else {
	        	$this->unique_id = $blocks[$this->block_id]['block_id'];
	        	$this->title = $blocks[$this->block_id]['block_name'];
	        }
		} else {
			$errorList->add($this->_classname, _INVALID_INPUT);
    		return 'error';
		}
		
		if($this->headercolumn_page_id != null) {
			if($this->column_space_type_use == _OFF) {
				if($this->headercolumn_page_id == $buf_pages_obj['page_id']) {
					$buf_pages_obj['page_name'] = _HEADER_COLUMN_LANG;
				} else if($this->leftcolumn_page_id == $buf_pages_obj['page_id']) {
					$buf_pages_obj['page_name'] = _LEFT_COLUMN_LANG;
				} else if($this->rightcolumn_page_id == $buf_pages_obj['page_id']) {
					$buf_pages_obj['page_name'] = _RIGHT_COLUMN_LANG;
				}
			} else {
				foreach($this->headercolumn_page_id as $key => $buf_headercolumn_page_id) {
					if($buf_headercolumn_page_id == $buf_pages_obj['page_id']) {
						$buf_pages_obj['page_name'] = _HEADER_COLUMN_LANG;
						if($key == 0) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PUBLIC.")";
						} else if($key == 1) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PRIVATE.")";
						} else {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_GROUP.")";
						}
					}
				}
				foreach($this->leftcolumn_page_id as $key => $buf_leftcolumn_page_id) {
					if($buf_leftcolumn_page_id == $buf_pages_obj['page_id']) {
						$buf_pages_obj['page_name'] = _LEFT_COLUMN_LANG;
						if($key == 0) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PUBLIC.")";
						} else if($key == 1) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PRIVATE.")";
						} else {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_GROUP.")";
						}
					}
				}
				foreach($this->rightcolumn_page_id as $key => $buf_rightcolumn_page_id) {
					if($buf_rightcolumn_page_id == $buf_pages_obj['page_id']) {
						$buf_pages_obj['page_name'] = _RIGHT_COLUMN_LANG;
						if($key == 0) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PUBLIC.")";
						} else if($key == 1) {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_PRIVATE.")";
						} else {
							$buf_pages_obj['page_name'] .= "("._SPACE_TYPE_NAME_GROUP.")";
						}
					}
				}
			}
		}
		$this->page_name = $buf_pages_obj['page_name'];
		
    	return 'success';
    }
    
    /**
     * read_ini_file
     * @param  srting $file_path
     * @return array
     * @access  private
     */
    function &_read_ini_file($file_path) {
    	if(version_compare(phpversion(), "5.0.0", ">=")){
        	$initializer =& DIContainerInitializerLocal::getInstance();
        	$install_ini = $initializer->read_ini_file($file_path, true);
        } else {
 	        $install_ini = parse_ini_file($file_path, true);
        }
        return $install_ini;
    }
}
?>
