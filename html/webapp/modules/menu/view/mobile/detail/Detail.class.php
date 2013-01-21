<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯メニュー画面：指定ページ内のブロック一覧を出す
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Menu_View_Mobile_Detail extends Action
{
	// コンポーネントを使用するため
	var $request = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->request->setParameters( array( "t"=>1 ) );
		return 'success';
	}
}
?>