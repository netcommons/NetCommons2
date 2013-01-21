<?php


/**
 * 固定リンクエラーチェック
 * 
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Dialog_Validator_Permalink extends Validator
{
    /**
     * 固定リンクエラーチェック
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
    	$pagesView =& $container->getComponent("pagesView");
    	$session =& $container->getComponent("Session");
    	$db =& $container->getComponent("DbObject");
    	
	  	if($session->getParameter("_permalink_flag") == _OFF) {
	  		// 固定リンクを使用しない
	  		return;
	  	}
    	
    	$permalink = $attributes[0];
    	$titletag = $attributes[1];
    	$main_page_id = $attributes[2];
    	$page =& $pagesView->getPageById($main_page_id);
    	
    	if(preg_match(_PERMALINK_PROHIBITION, $permalink) || preg_match(_PERMALINK_PROHIBITION_DIR_PATTERN, $permalink)) {
			return $errStr;
		}
    	
    	$meta = $pagesView->getDafaultMeta($page);

    	$permalink_arr = explode('/', $meta['permalink']);
    	$all_count = count($permalink_arr);
    	$count = 1;
    	$perma_parent_permalink = "";
    	foreach($permalink_arr as $cur_permalink) {
    		if($all_count == $count) {
    			$last_permalink = $cur_permalink;
    		} else {
    			if($perma_parent_permalink != "") {
    				$perma_parent_permalink .= '/';
    			}
    			$perma_parent_permalink .= $cur_permalink;
    		}
    		$count++;	
    	}
    	
    	if($last_permalink != $permalink) {
    		$perma_parent_permalink = ($perma_parent_permalink != "") ? $perma_parent_permalink.'/' : $perma_parent_permalink;
    		$set_permalink = $perma_parent_permalink.$permalink;
    		$where_params = array(
    			"permalink" => $set_permalink,
    			'lang_dirname' => $session->getParameter('_lang'),
    			"page_id!=".intval($main_page_id) => null
			);
			$pages = $db->selectExecute("pages", $where_params);
	    	if(isset($pages[0])) {
	    		//同名の固定リンクあり
	    		return DIALOG_PAGESTYLE_ERR_MES_PERMALINK_SAMENAME;
	    	}
    	} else {
    		// 変更なし
    		$set_permalink = "";
    	}
    	
    	$actionChain =& $container->getComponent("ActionChain");
	  	$action =& $actionChain->getCurAction();
	  	if(isset($params[0])) BeanUtils::setAttributes($action, array($params[0]=>$set_permalink));
	  	if(isset($params[1])) BeanUtils::setAttributes($action, array($params[1]=>$meta));
	  	return ;
    }
}
?>