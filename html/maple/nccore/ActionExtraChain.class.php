<?php
//
// $Id: ActionExtraChain.class.php,v 1.12 2008/04/11 01:19:04 Ryuji.M Exp $
//
require_once MAPLE_DIR.'/nccore/Action.class.php';
require_once MAPLE_DIR.'/core/ActionChain.class.php';

require_once MAPLE_DIR.'/nccore/SmartyTemplate.class.php';


 /**
 * Actionを管理するクラス
 *
 * @package     NetCommons.nccore
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class ActionExtraChain extends ActionChain {
	
	// 再帰的かどうか（preexecute）
	var $_recursive_action = "";
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function ActionExtraChain() {
		$this->ActionChain();
	}
	
    /**
     * Actionクラスをセット
     * 存在しないアクションがあればエラーリストへ追加
     * 
     * @param   string  $name   Actionのクラス名
     * @access  public
     */
    function add($name)
    {
    	$error_mes =null;
        $log =& LogFactory::getLog();

        //
        // 何も指定されていなかったらデフォルトのActionを切り替え
        //
        //
        if ($name == "") {
            $name = DEFAULT_ACTION;
        }

        //
        // Actionのクラス名が不正だったらデフォルトのActionを切り替え
        //
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            $error_mes = sprintf("不正なActionが指定されています(%s)",$name);
            //$log->info("不正なActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
        }

        //
        // ファイルが存在していなければデフォルトのActionを切り替え
        //
        list ($className, $filename) = $this->makeNames($name, true);

        if (!$className) {
        	//TODO:親で定義できる言語ファイルのしくみがないため現状、日本語のみ対応メッセージ(_NONEXISTS_ACTION)
        	$error_mes = sprintf("存在していないActionが指定されています(%s)",$name);
        	
            //$log->info("存在していないActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
            list ($className, $filename) = $this->makeNames($name, true);
        }

        //
        // 既に同名のActionが追加されていたら何もしない
        //
        if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
            $log->info("このActionは既に登録されています(${name})", "ActionChain#add");
            return true;
        }

        //
        // オブジェクトの生成に失敗していたらエラー
        //
        include_once($filename);

        $action =& new $className();

        if (!is_object($action)) {
            $log->error("Actionの生成に失敗しました(${name})", "ActionChain#add");
            return false;
        }

        $this->_list[$name]      =& $action;
        ////$this->_errorList[$name] =& new ErrorList();
        $this->_errorList[$name] =& new ErrorExtraList();
        $this->_position[]       =  $name;
		
		if($error_mes != null) {
			$errorList =& $this->_errorList[$name];	//$this->getCurErrorList();
        	$errorList->add("Action_Error", $error_mes);
	    	$errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする	
		}
        return true;
    }
	
	
	/**
	 * Actionを実行
	 * 
	 * @return	string	実行したActionの返却値
	 **/
	function execute() {
		
		$log =& LogFactory::getLog();

		if ($this->getSize() < 1) {
			$log->error("Actionが追加されていません", "ActionChain#execute");
			return false;
		}
		
		$name = $this->getCurActionName();
		
		$action =& $this->getCurAction();

		if (!is_object($action)) {
			$log->error("Actionの取得に失敗しました(${name})", "ActionChain#execute");
			return false;
		}
		//
        //cache_idセット
        //
        $container =& DIContainerFactory::getContainer();
        $common =& $container->getComponent("commonMain");
    	$cache_id = $common->getCacheid();
    	$renderer =& SmartyTemplate::getInstance();
    	//$renderer->is_cached(null,$cache_id,null);
    	if($name == DEFAULT_ACTION || $name == "control_view_main" || !$renderer->is_cached(null,$cache_id,null)) {
			return $action->execute();
    	} else {
			return USE_CACHE;
    	}
		
	}
	
    /**
	 * ActionChainの最後のデータを削除する
	 * 
	  * @access	public
	 **/
    function array_pop() {
    	array_pop($this->_list);
    	array_pop($this->_errorList);
    	array_pop($this->_position);
    }
    
    /**
     * ActionChainを前に戻す
     *
     * @access  public
     */
    function previous()
    {
        if ($this->_index > 0) {
            $this->_index--;
        }
    }
    
    function setRecursive($recursive)
    {
        $this->_recursive_action = $recursive;
    }
    
    function getRecursive()
    {
        return $this->_recursive_action;
    }
}
?>
