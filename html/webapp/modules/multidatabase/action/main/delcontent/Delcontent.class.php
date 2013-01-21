<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Delcontent extends Action
{
	// リクエストパラメータを受け取るため
	var $content_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $mdbAction = null;
	var $db = null;
	var $whatsnewAction = null;
	var $session = null;
	var $request = null;

	// バリデートによりセットするため

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$result = $this->mdbAction->deleteContent($this->content_id);
		if($result === false) {
			return 'error';
		}

		//--URL短縮形関連 Start--
		$container =& DIContainerFactory::getContainer();
		$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
		$result = $abbreviateurlAction->deleteUrl($this->content_id);
		if ($result === false) {
			//return 'error';
		}
		//--URL短縮形関連 End--

		$topid = $this->session->getParameter("_id");
	    $this->request->setParameter('_redirect_url', BASE_URL.INDEX_FILE_NAME."?active_action=multidatabase_view_main_init&block_id=$this->block_id&content_id=$this->content_id#$topid");

		return 'success';
	}
}
?>
