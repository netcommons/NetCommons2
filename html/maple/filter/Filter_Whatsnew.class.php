<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Whatsnew用クラスデータ登録用Filter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Whatsnew extends Filter
{
    var $_classname = "Filter_Whatsnew";

    var $_container;
    var $_log;
    var $_actionChain;
    var $_filterChain;
    var $_db;
    var $_modulesView;
    var $_request;
    var $_session;
	var $_whatsnewAction;
	var $_module_whatsnew;
	
    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_Whatsnew()
    {
        parent::Filter();
    }

    /**
     * Whatsnew用クラスデータ登録用Filter
     *
     * @access  public
     * 
     */
    function execute()
    {
        $this->_container   =& DIContainerFactory::getContainer();
        $this->_log         =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_db   =& $this->_container->getComponent("DbObject");
        $this->_modulesView =& $this->_container->getComponent("modulesView");
        $this->_request     =& $this->_container->getComponent("Request");
        $this->_session  =& $this->_container->getComponent("Session");
        $this->_whatsnewAction =& $this->_container->getComponent("whatsnewAction");

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();
        
        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }
    
    /**
     * プリフィルタ
     *
     * @access  private
     */
    function _preFilter()
    {
    }

    /**
     * ポストフィルタ
     *
     * @access  private
     */
    function _postFilter()
    {
    	$this->_module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$this->_module_whatsnew) { return; }
		
    	$attributes = $this->getAttributes();
    	$this->mode = (isset($attributes['mode']) ? $attributes['mode'] : "auto");
    	$this->mode = explode(",", $this->mode);
    	$this->noblock_id = (isset($attributes['noblock_id']) ? intval($attributes['noblock_id']) : _OFF);
		$action =& $this->_actionChain->getCurAction();
		if (empty($action->whatsnew)) { return; }
		
		foreach ($this->mode as $i=>$mode) {
			if ($mode == "delete") {
				$result = $this->_whatsnewAction->delete($action->whatsnew["unique_id"]);
				if ($result === false) {
					return false;
				}
				continue;
			}
			
			if ($mode == "insert") {
				$result = $this->_whatsnewAction->insert($action->whatsnew, $this->noblock_id);
			} elseif ($mode == "update") {
				$result = $this->_whatsnewAction->update($action->whatsnew, $this->noblock_id);
			} else {
				$result = $this->_whatsnewAction->auto($action->whatsnew, $this->noblock_id);
			}
			if ($result === false) {
				return false;
			}
		}
    }
}
?>