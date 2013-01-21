<?php
//
// $Id: ResponseExtra.class.php,v 1.3 2006/09/29 06:16:28 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/core/Response.class.php';


/**
 * 出力を補助するクラス
 *
 * @package     Maple
 * @author      Ryuji Masukawa
 * @copyright  
 * @license    
 */
class ResponseExtra extends Response
{
    /**
     * @var javascriptからのredirect先を保持する(location.href='')
     *
     * @access  private
     */
    var $_script_redirect;
    /**
     * @var javascriptのコードを保持する　画面を描画後実行
     *
     * @access  private
     */
    var $_script;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function ResponseExtra()
    {
    	$this->Response();
        $this->_script_redirect           = NULL;
    }

    /**
     * Redirectの値を返却
     *
     * @return  string  redirectの値
     * @access  public
     */
    function getRedirectScript()
    {
        return $this->_script_redirect;
    }

    /**
     * Redirectの値をセット
     *
     * @param   string  $redirect   redirectの値
     * @access  public
     */
    function setRedirectScript($redirect)
    {
        $this->_script_redirect = $redirect;
    }
    
    /**
     * Scriptの値を返却
     *
     * @return  string  redirectの値
     * @access  public
     */
    function getScript()
    {
        return $this->_script;
    }

    /**
     * Scriptの値をセット
     *
     * @param   string  $redirect   redirectの値
     * @access  public
     */
    function setScript($redirect)
    {
        $this->_script = $redirect;
    }
}
?>
