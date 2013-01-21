<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯用クラスFilter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_MobileConvert extends Filter
{
    var $_classname = "Filter_MobileConvert";

    var $_container;
    var $_log;
    var $_filterChain;
    var $_session;
    var $_request;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_MobileConvert()
    {
        parent::Filter();
    }

    /**
     * Mobile用クラス実行
     *
     * @access  public
     * 
     */
    function execute()
    {
        $this->_container =& DIContainerFactory::getContainer();
        $this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_session =& $this->_container->getComponent("Session");
        $this->_request =& $this->_container->getComponent("Request");

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();
        
        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }
    
    /**
     * プレフィルタ
     *
     * @access  private
     */
    function _preFilter()
    {
    	$attributes = $this->getAttributes();
		if (empty($attributes)) { return; }
		if ($this->_session->getParameter("_mobile_flag") == _OFF) { return; }

		foreach ($attributes as $key=>$val) {
			$contents = $this->_request->getParameter($key);
			$contents = htmlspecialchars($contents);
			$contents = nl2br($contents);
			$this->_request->setParameter($key, $contents);
		}
    }

    /**
     * ポストフィルタ
     *
     * @access  private
     */
    function _postFilter()
    {

    }
}
?>