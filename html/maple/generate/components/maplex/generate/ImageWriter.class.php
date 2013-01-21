<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * imageを書き出す
 *
 * @package     NetCommons.generate
 * @author      Ryuji.M
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */

/**
 * imageを書き出す
 *
 * @package     NetCommons.generate
 * @author      Ryuji.M
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @access      public
 */
class Maplex_Generate_ImageWriter
{
    /**
     * @var  object  $fileUtil
     */
    var $fileUtil;

    /**
     * $templateを読み込んでディレクトリを作成し、$copyImageDirの中のファイルを書き出す
     * 
     * @param  String    $template
     * @param  String    $copyImageDir
     * @return boolean
     */
    function write($template, $copyImageDir)
    {
        $this->fileUtil->makeDir($template);
        $fileArray = $this->fileUtil->find($copyImageDir);
        $result_list = array();
        foreach( $fileArray as $key => $value){
        	$value = basename($value);
        	if (file_exists($template.$value)) {
        		$stat = 'exists';
        	} else {
        		$ret = copy( $copyImageDir.$value, $template.$value );
        		if($ret)
        			$stat = 'create';
        		else
        			$stat = 'fail';
        		$result_list[$template.$value] = $stat;
        	}
		}
        
        return $result_list;
    }
}
?>
