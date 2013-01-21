<?php


/**
 * ブロックテンプレートがあるかどうかチェック
 *
 * @author      Ryuji Masukawa
 * @copyright   2006
 * @license      
 * @access      public
 */
class Dialog_Validator_Tempcheck extends Validator
{
    /**
     * ブロックテンプレートがあるかどうかチェック
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$commonMain =& $container->getComponent("commonMain");
        $fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        
    	$getData =& $container->getComponent("GetData");
    	$blocks_obj =& $getData->getParameter("blocks");
    	$block_id = $attributes[1];
    	$check_flag = false;
    	
    	if(isset($blocks_obj[$block_id])) {
    		
    		if($blocks_obj[$block_id]['action_name'] == "pages_view_grouping") {
    			$check_flag = true;
    		} else {
		    	$pathList = explode("_", $blocks_obj[$block_id]['action_name']);
		    	
		    	$temp_path = MODULE_DIR  . "/" . $pathList[0]. "/templates/";
				$temp_arr = $fileView->getCurrentDir($temp_path);
				$main_temp_name = $attributes[0];
				if($temp_arr) {
					foreach($temp_arr as $temp_name) {
						if($temp_name == $main_temp_name) {
							$check_flag = true;
							break;	
						}	
					}
				}
    		}
    	}
		if(!$check_flag)
			return $errStr;
    }
}
?>
