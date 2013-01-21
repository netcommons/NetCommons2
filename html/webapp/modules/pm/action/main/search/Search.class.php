<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Action_Main_Search extends Action
{
	// 使用コンポーネントを受け取るため
	var $request = null;
	var $session = null;
	
    /**
     * メール転送登録アクション
     *
     * @access  public
     */
    function execute()
    {
		if ($this->session->getParameter("search") == null) {
			$search = array(
				"search_sender" =>  $this->request->getParameter("search_sender"),
				"search_cc" =>  $this->request->getParameter("search_cc"),
				"search_subject" =>  $this->request->getParameter("search_subject"),
				"search_keywords" =>  $this->request->getParameter("search_keywords"),
				"search_date_from" =>  $this->request->getParameter("search_date_from"),
				"search_date_to" =>  $this->request->getParameter("search_date_to"),
				"search_upload_flag" =>  $this->request->getParameter("search_upload_flag"),
				"search_range" =>  $this->request->getParameter("search_range"),
				"search_flag" => $this->request->getParameter("search_flag"),
			);
			
			$this->session->setParameter("search",$search);
		}
		
		$this->request->setParameter("sort_col", $this->request->getParameter("sort_col"));
		$this->request->setParameter("sort_dir", $this->request->getParameter("sort_dir"));
		
		return "success";
    }
}
?>
