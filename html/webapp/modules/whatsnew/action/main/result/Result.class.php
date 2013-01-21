<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着条件の切替
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action_Main_Result extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $display_days = null;
	var $display_type = null;
	var $display_number = null;

    // 使用コンポーネントを受け取るため
	var $session = null;
	var $request = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if (isset($this->display_type)) { 
			$this->session->setParameter(array("whatsnew", $this->block_id, "display_type"), intval($this->display_type));
    	}
    	if (isset($this->display_days)) { 
			$this->session->setParameter(array("whatsnew", $this->block_id, "display_days"), intval($this->display_days));
    	}
    	if (isset($this->display_number)) {
			$this->session->setParameter(array("whatsnew", $this->block_id, "display_number"), intval($this->display_number));
    	}

		$this->request->setParameter("result_only", _ON);
		return 'success';
    }
}
?>