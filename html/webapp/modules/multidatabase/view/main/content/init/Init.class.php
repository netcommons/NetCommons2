<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Main_Content_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
    var $multidatabase_id = null;
    var $content_id = null;

    // 使用コンポーネントを受け取るため
    var $mdbView = null;
    var $session = null;
 
    // 値をセットするため
    var $metadatas_layout = null;
    //var $session_params = null;
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
    		"multidatabase_id" => intval($this->multidatabase_id)
    	);
    	$this->metadatas_layout = $this->mdbView->getLayout($params);
    	if($this->metadatas_layout === false) {
    		return 'error';
    	}
    	
    	$this->session->removeParameter(array("multidatabase_content", $this->block_id));
    	
		if(!empty($this->content_id)) {
	    	$metadatas = $this->mdbView->getMetadatas($params);
	    	if($metadatas === false) {
	    		return 'error';
	    	}
    		$detail = $this->mdbView->getMdbEditData($this->content_id, $metadatas);
	    	if($detail === false) {
	    		return 'error';
	    	}
	    	$this->session->setParameter(array("multidatabase_content", $this->block_id), $detail['value']);
    	}
		return 'success';
    }
}
?>
