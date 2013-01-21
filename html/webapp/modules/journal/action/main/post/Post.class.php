<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投稿機能アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Post extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $journal_id = null;
    var $journal_date = null;
    var $journal_hour = null;
    var $journal_minute = null;
    var $icon_name = null;
    var $title = null;
    var $category_id = null;
    var $content = null;
    var $more_checked = null;
    var $more_title = null;
    var $more_content = null;
    var $hide_more_title = null;
    var $tb_url = null;
    var $edit_flag = null;
    var $post_id = null;
    var $temp_flag = null;
	var $journal_mobile_images = null;

    // バリデートによりセット
	var $journal_obj = null;

    // 使用コンポーネントを受け取るため
    var $journalView = null;
    var $journalAction = null;
    var $db = null;
 	var $whatsnewAction = null;
 	var $request = null;
 	var $session = null;
 	var $configView = null;

    // 値をセットするため

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$_auth_id = $this->session->getParameter("_auth_id");
    	if($_auth_id < $this->journal_obj['post_authority']) {
    		return 'error';
    	}

    	$mobile_flag = $this->session->getParameter("_mobile_flag");

    	if($this->temp_flag == _ON) {
			if($this->edit_flag == _ON) {
				$status = JOURNAL_POST_STATUS_TEMPORARY_VALUE;
			}else {
				$status = JOURNAL_POST_STATUS_BEFORE_REREASED_VALUE;
			}
		}else {
			$status = JOURNAL_POST_STATUS_REREASED_VALUE;
		}
		if($_auth_id < _AUTH_CHIEF && $this->journal_obj['agree_flag'] == _ON) {
			$agree_flag = JOURNAL_STATUS_WAIT_AGREE_VALUE;
		}else {
			$agree_flag = JOURNAL_STATUS_AGREE_VALUE;
		}

		$this->journal_hour = sprintf("%02d",intval($this->journal_hour) % 24);
		$this->journal_minute = sprintf("%02d",intval($this->journal_minute) % 60);
		$journal_date = timezone_date($this->journal_date.$this->journal_hour.$this->journal_minute."00", true, "YmdHis");

    	if(intval($this->more_checked) == _OFF) {
    		$this->more_title = "";
    		$this->more_content = "";
    		$this->hide_more_title = "";
    	}else {
    		if($this->more_title == "") {
    			$this->more_title = JOURNAL_MORE_TITLE;
    		}
    		if($this->hide_more_title == "") {
    			$this->hide_more_title = JOURNAL_HIDE_MORE_TITLE;
    		}
    	}

		if ($mobile_flag == _ON) {
			$br = '';
			if (substr(rtrim($body), -6) != '<br />') {
				$br = '<br />';
			}
			$current_mobile_image = $this->session->getParameter('journal_current_mobile_image');
			if (count($this->journal_mobile_images) > 0) {
				foreach ($this->journal_mobile_images as $image) {
					$this->content .= $br . '<img class="' . MOBILE_IMAGE . '" src=".' . INDEX_FILE_NAME . $image . '" />';
				}
			} elseif (!empty($current_mobile_image)) {
				$this->content .= $br . $current_mobile_image;
			}
		}

		$post_id = "";
    	$mail_flag = _OFF;
		if($this->edit_flag == _ON) {
    		// 変更前データ取得
    		$post_before_update = $this->db->selectExecute("journal_post", array("post_id"=>$this->post_id));
			if($post_before_update === false || empty($post_before_update[0])) {
	    		return 'error';
	    	}

			// ステータスの更新
			if($status == JOURNAL_POST_STATUS_TEMPORARY_VALUE && $post_before_update[0]['status'] == JOURNAL_POST_STATUS_BEFORE_REREASED_VALUE) {
				$status = JOURNAL_POST_STATUS_BEFORE_REREASED_VALUE;
			}
			if($status == JOURNAL_POST_STATUS_TEMPORARY_VALUE && $post_before_update[0]['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE) {
				$agree_flag = JOURNAL_STATUS_WAIT_AGREE_VALUE;
			}

    		$post_id = $this->post_id;
    		if($mobile_flag == _ON) {
	    		$params = array(
					"journal_date" => $journal_date,
					"category_id" => intval($this->category_id),
					"title" => $this->title,
					"content" => $this->content,
					"status" => $status,
					"agree_flag" => $agree_flag
				);
    		}else {
	    		$params = array(
					"journal_date" => $journal_date,
					"category_id" => $this->category_id,
					"title" => $this->title,
					"icon_name" => $this->icon_name,
					"content" => $this->content,
					"more_title" => $this->more_title,
					"more_content" => $this->more_content,
					"hide_more_title" => $this->hide_more_title,
					"tb_url" => $this->tb_url,
					"status" => $status,
					"agree_flag" => $agree_flag
				);
    		}
			$result = $this->db->updateExecute("journal_post", $params,  array("post_id"=>$post_id), true);
			if($result === false) {
				return 'error';
			}

			// メール送信データ登録
			if($this->journal_obj['mail_flag'] == _ON && $agree_flag == JOURNAL_STATUS_AGREE_VALUE && $params['status'] == JOURNAL_POST_STATUS_REREASED_VALUE
					&& ($post_before_update[0]['status'] == JOURNAL_POST_STATUS_BEFORE_REREASED_VALUE || $post_before_update[0]['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE)) {
				$this->session->setParameter("journal_mail_post_id", array("post_id" => $post_id, "agree_flag" => JOURNAL_STATUS_AGREE_VALUE));
				$mail_flag = _ON;
			}
    		if($post_before_update[0]['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE &&
    			$params['status'] == JOURNAL_POST_STATUS_REREASED_VALUE &&
    			$agree_flag == JOURNAL_STATUS_AGREE_VALUE &&
    			$this->journal_obj['agree_mail_flag'] == _ON) {
				$this->session->setParameter("journal_confirm_mail_post_id", $post_id);
			}
    	}else {
	    	$params = array(
				"journal_id" => $this->journal_id,
				"journal_date" => $journal_date,
				"category_id" => $this->category_id,
				"root_id" => _OFF,
				"parent_id" => _OFF,
				"title" => $this->title,
				"icon_name" => $this->icon_name,
				"content" => $this->content,
				"more_title" => $this->more_title,
				"more_content" => $this->more_content,
				"hide_more_title" => $this->hide_more_title,
				"tb_url" => $this->tb_url,
				"status" => $status,
				"agree_flag" => $agree_flag
			);

			$post_id = $this->db->insertExecute("journal_post", $params, true, "post_id");
			if($post_id === false) {
				return "error";
			}
			// メール送信データ登録
			if ($this->journal_obj['mail_flag'] == _ON && $status == JOURNAL_POST_STATUS_REREASED_VALUE && $agree_flag == JOURNAL_STATUS_AGREE_VALUE) {
				$this->session->setParameter("journal_mail_post_id", array("post_id" => $post_id, "agree_flag" => JOURNAL_STATUS_AGREE_VALUE));
				$mail_flag = _ON;
			}
			$this->post_id = $post_id;

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->setAbbreviateUrl($this->journal_id, $this->post_id);
			if ($result === false) {
				return 'error';
			}
//			$result = $abbreviateurlAction->setAbbreviateUrl($this->journal_id, $this->post_id, 'journal_trackback');
//			if ($result === false) {
//				return 'error';
//			}
			//--URL短縮形関連 End--
		}

		$this->session->removeParameter('journal_current_mobile_image');

    	//承認付いた場合、管理者にメールで通知する
		if($this->journal_obj['agree_flag'] == _ON && $status == JOURNAL_POST_STATUS_REREASED_VALUE && $agree_flag == JOURNAL_STATUS_WAIT_AGREE_VALUE) {
			$this->session->setParameter("journal_mail_post_id", array("post_id" => $post_id, "agree_flag" => JOURNAL_STATUS_WAIT_AGREE_VALUE));
			$mail_flag = _ON;
		}

		//--新着情報関連 Start--
		$result = $this->journalAction->setWhatsnew($post_id);
		if($result === false) {
			return 'error';
		}
		//--新着情報関連 End--

		// --- 投稿回数更新 ---
		$before_post = isset($post_before_update[0]) ? $post_before_update[0] : null;
		$result = $this->journalAction->setMonthlynumber($this->edit_flag, $status, $agree_flag, $before_post);
		if ($result === false) {
			return 'error';
		}

    	$this->request->setParameter("category_id", "");

    	//トラックバックの処理
    	if ($this->temp_flag != _ON && $agree_flag == JOURNAL_STATUS_AGREE_VALUE && $mobile_flag == _OFF && $this->journal_obj['trackback_transmit_flag'] == _ON) {
    		$trackback_result = $this->journalAction->setTrackBack($this->journal_obj, $post_id, $params);
    		if(!empty($trackback_result)) {
    			$this->request->setParameter("trackback_result", $trackback_result);
    		}
    	}

    	if ($mail_flag == _ON && $mobile_flag == _ON) {
    		return 'mail';
    	} else {
	        return 'success';
    	}
    }
}
?>