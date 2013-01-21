<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
 	var $mailMain = null;
 	var $mdbView = null;
 	var $session = null;
 	var $usersView = null;

	// validatorから受け取るため
	var $mail = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$content_mail = $this->session->getParameter("multidatabase_mail_content_id");
		$content_id = intval($content_mail['content_id']);

		if (empty($content_id)) {
			return 'success';
		}
		$params = array(
			"content_id" => $content_id
		);
		$contents = $this->db->selectExecute("multidatabase_content", $params);
		if($contents === false || !isset($contents[0])) {
			return 'error';
		}

		$multidatabase_id = $contents[0]['multidatabase_id'];
		$metadatas = $this->mdbView->getMetadatas(array("multidatabase_id" => $multidatabase_id));
    	if($metadatas === false) {
    		return 'error';
    	}

		$mail = $this->mdbView->getMail($content_id, $metadatas);
		if ($mail === false) {
			return 'error';
		}
		$data = "";
		foreach (array_keys($metadatas) as $i) {
			$data .= htmlspecialchars($metadatas[$i]['name']) . ':';
			if ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_IMAGE || $metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_FILE) {
				$data .= $this->mdbView->getFileLink($mail['content'.$metadatas[$i]['metadata_id']],
														$mail['file_name'.$metadatas[$i]['metadata_id']],
														$mail['physical_file_name'.$metadatas[$i]['metadata_id']],
														$metadatas[$i], BASE_URL.INDEX_FILE_NAME);

			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_WYSIWYG) {
				$data .= $mail['content'.$metadatas[$i]['metadata_id']];
			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_DATE && !empty($mail['content'.$metadatas[$i]['metadata_id']])) {
				$data .= timezone_date_format($mail['content'.$metadatas[$i]['metadata_id']], _DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_INSERT_TIME) {
				$data .= timezone_date_format($mail['insert_time'], _FULL_DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_UPDATE_TIME) {
				$data .= timezone_date_format($mail['update_time'], _FULL_DATE_FORMAT);
			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_AUTONUM) {
				$data .= intval($mail['content'.$metadatas[$i]['metadata_id']]);
			} elseif ($metadatas[$i]['type'] == MULTIDATABASE_META_TYPE_TEXTAREA) {
				$data .= str_replace("\n", '<br />', htmlspecialchars($mail['content'.$metadatas[$i]['metadata_id']]));
			} else {
				$data .= htmlspecialchars($mail['content'.$metadatas[$i]['metadata_id']]);
			}
			$data .= '<br />';
		}
		$this->mailMain->setSubject($mail['mail_subject']);
		$this->mailMain->setBody($mail['mail_body']);

		$tags['X-MDB_NAME'] = htmlspecialchars($mail['multidatabase_name']);
		$tags['X-DATA'] = $data;
		$tags['X-USER'] = htmlspecialchars($mail['insert_user_name']);
		$tags['X-TO_DATE'] = $mail['insert_time'];
		$tags['X-URL'] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=multidatabase_view_main_detail".
							"&content_id=". $content_id.
							"&multidatabase_id=". $multidatabase_id.
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);

		if($content_mail['agree_flag'] == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_CHIEF);
		}else if($content_mail['agree_flag'] == MULTIDATABASE_STATUS_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, $mail['mail_authority']);
		}
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("multidatabase_mail_content_id");

		return 'success';
    }
}
?>
