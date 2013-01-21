<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-携帯からの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Action_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $id = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $request = null;
	
	// 値をセットするため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if(!isset($this->id) || $this->id == "0") {
			$this->id = $this->session->getParameter("_user_id");
		}
		$this->session->setParameter("user_id", $this->id);
		$this->request->setParameter("user_id", $this->id);
		return 'success';
	}
}
?>
