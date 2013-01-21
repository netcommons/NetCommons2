<?php


/**
 * ブロックグルーピング時チェック
 *
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license      
 * @access      public
 */
class Pages_Validator_Grouping extends Validator
{
    /**
     * ブロックグルーピング時チェック
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
    	$grouping_list = $attributes;
    	$getdata =& $container->getComponent("GetData");
    	//$request =& $container->getComponent("Request");
    	$blocksView =& $container->getComponent("blocksView");
    	$blocks_obj = $getdata->getParameter("blocks");
    	
    	$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
    	
    	if($grouping_list == "" || $grouping_list == null) {
    		return $errStr;	
    	}
    	$cellLists = explode(":", $grouping_list);
    	foreach ($cellLists as $cellList) {
    		$rowLists = explode(",", $cellList);
    		foreach ($rowLists as $rowList) {
    			$block_id = $rowList;
    			$blocks_obj[$block_id] =& $blocksView->getBlockById($block_id);
		    	//ブロックが存在しないならばエラー
		    	if(!$blocks_obj[$block_id] && !is_array($blocks_obj[$block_id])) {
		    		return $errStr;	
		    	}
    		}
    	}
    	$getdata->setParameter("blocks",$blocks_obj);
    	
    	BeanUtils::setAttributes($action, array("grouping_list"=>$grouping_list));
    }
}
?>