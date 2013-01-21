<?php
/**
 * スマイリーダイアログ表示
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Comp_Textarea_View_Insertsmiley extends Action
{
	var $parent_id_name = null;
	var $session = null;
	var $fileView = null;

	var $smiley_list = null;
	var $image_path = null;

	function execute()
	{
		$lang = $this->session->getParameter("_lang");
		$ini_path = MODULE_DIR."/comp/language/".$lang."/textarea/smiley.ini";	//iniパス
		$smiley_path_name = "smiley/";

		$this->image_path = "/images/comp/textarea/";
		if (version_compare(phpversion(), '5.0.0', '>=')) {
			$initializer =& DIContainerInitializerLocal::getInstance();
			$this->smiley_list = $initializer->read_ini_file($ini_path, true);
		} else {
			$this->smiley_list = parse_ini_file($ini_path, true);
		}

		//実際のファイルの一覧から、iniファイルに書かれていないものを追加 altタグは、filename
		$dir_list = $this->fileView->getCurrentFiles(HTDOCS_DIR.$this->image_path.$smiley_path_name);
		foreach($dir_list as $file_name) {
			if(!isset($this->smiley_list[$smiley_path_name.$file_name])) {
				$this->smiley_list[$file_name] = $file_name;
			}
		}
		return 'success';
	}
}
?>
