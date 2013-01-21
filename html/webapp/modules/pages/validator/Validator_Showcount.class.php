<?php


/**
 * ページ更新時チェック
 *
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license      
 * @access      public
 */
class Pages_Validator_Showcount extends Validator
{
    /**
     * ブロック移動時のチェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$show_count = $attributes['_show_count'];
    	
    	$getdata =& $container->getComponent("GetData");
    	$pages_obj = $getdata->getParameter("pages");
    	$blocks_obj = $getdata->getParameter("blocks");
    	
    	$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		
    	//ブロックオブジェクトチェック
    	$page_id = null;
    	if(isset($attributes['block_id']) && $attributes['block_id'] != 0)
    		$block_id = $attributes['block_id'];
    	else if(isset($attributes['page_id']) && $attributes['page_id'] != 0)
    		$page_id = $attributes['page_id'];
    		
    	if(!$page_id) {
	    	if(!$block_id || !is_array($blocks_obj[$block_id])) {
	    		return $errStr;	
	    	} else {
	    		$page_id = $blocks_obj[$block_id]['page_id'];
	    	}
    	}
    	//ページオブジェクトがないならばエラー
    	//TODO:左右カラムの場合、page_idが0であるので処理を追加する必要性あり。
    	//左右カラムもpage_idをふる仕様に後に変更
    	if(!is_array($pages_obj[$page_id])) {
    		return $errStr;	
    	}
   	
    	//不整合チェック
    	//  page-show_countが画面と変わっていた場合、不整合とする
    	if($show_count != $pages_obj[$page_id]['show_count']) {
    		return $errStr;	
    	}
    	if(!isset($attributes['page_id']))
    		BeanUtils::setAttributes($action, array("page_id"=>$page_id));
    }
}
?>