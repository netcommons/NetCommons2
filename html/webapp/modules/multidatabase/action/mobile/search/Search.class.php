<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯向け検索アクション(now_pageの制御だけして、本来のPCの検索アクションにつなげる)
 *
 * @package     NetCommons
 * @author      Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2009 AllCreator Co., Ltd.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NC Support Project, provided by AllCreator Co., Ltd.
 * @access      public
 */
class Multidatabase_Action_Mobile_Search extends Action
{
    // リクエストパラメータを受け取るため
    var $now_page = null;
    var $prev_pg = null;
    var $next_pg = null;
    
    // 使用コンポーネントを受け取るため
    var $request = null;
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
        if( isset($this->prev_pg) && isset($this->now_page) && $this->now_page > 1 ){
            $this->request->setParameter("now_page", $this->now_page - 1 );
        }
        else if( isset($this->next_pg) && isset($this->now_page) ){
            $this->request->setParameter("now_page", $this->now_page + 1 );
        }

        return "success";
    }
}
