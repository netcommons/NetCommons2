<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム情報画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_View_Main_Room extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    
    // バリデートによりセット
    var $user = null;
    
    // 使用コンポーネントを受け取るため
    var $monthlynumberView = null;
    var $session = null;
    var $authoritiesView = null;
    
    // Filterによりセット
    var $room_list = null;
    var $room_id_arr = null;
    
    // 値をセットするため
    var $count = 0;
    var $monthlynumber = null;
    var $authorities_count = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$this->count = count($this->room_id_arr);
    	
    	$this->monthlynumber =& $this->monthlynumberView->getUserAccessTime($this->user_id);
    	if($this->monthlynumber === false)  return 'error';
    	
		//
		// モデレータの細分化された一覧を取得
		//
		$where_params = array("user_authority_id" => _AUTH_MODERATE);
		$order_params = array("hierarchy" => "DESC");
		$authorities = $this->authoritiesView->getAuthorities($where_params, $order_params, null, null, true);
		if($authorities === false) {
			return 'error';
		}
		$this->authorities_count = count($authorities);
    	
    	return 'success';
    }
}
?>
