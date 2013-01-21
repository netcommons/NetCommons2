<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 認証画像を出力
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Imageauth_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $id = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	require_once 'Text/CAPTCHA.php';
        // Set CAPTCHA image options (font must exist!)
        $imageOptions = array(
            'font_size'        => 15,
            'font_path'        => BASE_DIR."/".MAPLE_DIR.'/includes/font/',
            'font_file'        => 'Vrinda.ttf',
            'text_color'       => '#DDFF99',
            'lines_color'      => '#CCEEDD',
            'background_color' => '#555555'
        );
        // Set CAPTCHA options
        $options = array(
            'width' => 70,
            'height' => 20,
            'output' => 'jpg',
            'imageOptions' => $imageOptions
        );
    	
    	$c = Text_CAPTCHA::factory('Image');
        $retval = $c->init($options);
        if (PEAR::isError($retval)) {
            printf('Error initializing CAPTCHA: %s!',
                $retval->getMessage());
            exit;
        }
        // Get CAPTCHA secret passphrase
        $this->session->setParameter(array(_SESSION_IMAGE_AUTH.$this->id), $c->getPhrase());
    
        // Get CAPTCHA image (as Jpeg)
        $png = $c->getCAPTCHA();
        if (PEAR::isError($png)) {
            printf('Error generating CAPTCHA: %s!',
                $png->getMessage());
            exit;
        }

		header("Content-type:   image/jpeg");
		echo $png;
    }
}
?>