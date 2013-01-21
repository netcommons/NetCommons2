<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ファイルの追加チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_Upload extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$cabinetView =& $container->getComponent("cabinetView");
    	$commonMain =& $container->getComponent("commonMain");
    	$fileView =& $commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");

		$cabinet_id = $attributes["cabinet_id"];
		$folder_id = $attributes["folder_id"];
		$cabinet = $attributes["cabinet"];
		$commonMain =& $container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$filelist = $uploadsAction->uploads(_OFF);
    	if (!$filelist || $filelist[0]["file_name"] == "" || $filelist[0]["error_mes"] != "") {
    		return $errStr;
    	}
    	
		if ($cabinet["upload_max_size"] != 0 && $cabinet["upload_max_size"] < $filelist[0]["file_size"]) {
			$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
			$suffix_maxsize = $fileView->formatSize($cabinet["upload_max_size"]);
			return sprintf(_FILE_UPLOAD_ERR_SIZE, $suffix_maxsize);
		}
		
		$used_size = $cabinetView->getUsedSize();
		if ($used_size === false) {
			return $errStr;
		}
		
		if ($cabinet["cabinet_max_size"] != 0 && $cabinet["cabinet_max_size"] < ($used_size + $filelist[0]["file_size"])) {
			$uploadsAction->delUploadsById($filelist[0]["upload_id"]);
			$suffix_maxsize = $fileView->formatSize($cabinet["cabinet_max_size"]);
			$suffix_usedsize = $fileView->formatSize($cabinet["cabinet_max_size"]-$used_size);
			return sprintf(CABINET_ERROR_MAX_SIZE, $suffix_maxsize, $suffix_usedsize);
		}

        $extension = $filelist[0]['extension'];
        $file_name = strtr($filelist[0]["file_name"], array(".".$extension=>""));

        $request =& $container->getComponent("Request");
        $request->setParameter("upload_id", $filelist[0]["upload_id"]);

        $request->setParameter("file_name", $file_name);
        $request->setParameter("extension", $extension);
        $request->setParameter("size", $filelist[0]["file_size"]);
        
        $request->setParameter("filelist", $filelist);
    }
}
?>