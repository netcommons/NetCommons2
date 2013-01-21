<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Addcontent extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $multidatabase_id = null;
	var $content_id = null;
	var $upload_ids = null;
	var $temporary_flag = null;
	var $passwords = null;

	// バリデートによりセット
	var $mdb_obj = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $mdbView = null;
	var $request = null;
	var $uploadsAction = null;
	var $mdbAction = null;

	// バリデートによりセットするため;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$datas =& $this->session->getParameter(array("multidatabase_content", $this->block_id));
		if(!isset($datas) || $datas == null) {
			//セッションデータなし
			return 'error';
		}

		$_auth_id = $this->session->getParameter("_auth_id");

		if($this->temporary_flag == _ON) {
			if(!empty($this->content_id)) {
				$status = MULTIDATABASE_STATUS_TEMPORARY_VALUE;
			}else {
				$status = MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE;
			}
		}else {
			$status = MULTIDATABASE_STATUS_RELEASED_VALUE;
		}
		if($_auth_id < _AUTH_CHIEF && $this->mdb_obj['agree_flag'] == _ON) {
			$agree_flag = MULTIDATABASE_STATUS_WAIT_AGREE_VALUE;
		}else {
			$agree_flag = MULTIDATABASE_STATUS_AGREE_VALUE;
		}

		if(empty($this->content_id)) {
			$display_sequence = $this->db->maxExecute("multidatabase_content", "display_sequence", array("multidatabase_id" => $this->multidatabase_id));
			$insert_params = array(
				"multidatabase_id" => $this->multidatabase_id,
				"agree_flag" => $agree_flag,
				"temporary_flag" => $status,
				"display_sequence" => $display_sequence + 1
			);
			$content_id = $this->db->insertExecute("multidatabase_content", $insert_params, true, "content_id");
			if ($content_id === false) {
				return 'error';
			}
			// --- メール送信データ登録 ---
			if ($this->mdb_obj['mail_flag'] == _ON &&
					$status == MULTIDATABASE_STATUS_RELEASED_VALUE && $agree_flag == MULTIDATABASE_STATUS_AGREE_VALUE) {
				$this->session->setParameter("multidatabase_mail_content_id", array("content_id" => $content_id, "agree_flag" => MULTIDATABASE_STATUS_AGREE_VALUE));
			}

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->setAbbreviateUrl($this->multidatabase_id, $content_id);
			if ($result === false) {
				return 'error';
			}
			//--URL短縮形関連 End--

		}else {
			// 変更前データ取得
			$content_before_update = $this->db->selectExecute("multidatabase_content", array("content_id"=>$this->content_id, "multidatabase_id" => $this->multidatabase_id));
			if($content_before_update === false || empty($content_before_update[0])) {
				return "error";
			}

			// ステータスの更新
			if ($status == MULTIDATABASE_STATUS_TEMPORARY_VALUE && $content_before_update[0]['temporary_flag'] == MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE) {
				$status = MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE;
			}
			if ($status == MULTIDATABASE_STATUS_TEMPORARY_VALUE && $content_before_update[0]['agree_flag'] == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE) {
				$agree_flag = MULTIDATABASE_STATUS_WAIT_AGREE_VALUE;
			}
			$update_params = array(
				"agree_flag" => $agree_flag,
				"temporary_flag" => $status
			);
			if ($status == MULTIDATABASE_STATUS_RELEASED_VALUE && $content_before_update[0]["temporary_flag"] == MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE) {
				$update_params["insert_time"] = timezone_date();
			}
			$where_params = array(
				"content_id" => $this->content_id,
				"multidatabase_id" => $this->multidatabase_id
			);
			$result = $this->db->updateExecute("multidatabase_content", $update_params, $where_params, true);
			if ($result === false) {
				return 'error';
			}

			// メール送信データ登録
			if ($this->mdb_obj['mail_flag'] == _ON && $agree_flag == MULTIDATABASE_STATUS_AGREE_VALUE && $status == MULTIDATABASE_STATUS_RELEASED_VALUE
					&& ($content_before_update[0]['temporary_flag'] == MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE || $content_before_update[0]['agree_flag'] == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE)) {
				$this->session->setParameter("multidatabase_mail_content_id", array("content_id" => $this->content_id, "agree_flag" => MULTIDATABASE_STATUS_AGREE_VALUE));
			}

			if($content_before_update[0]['agree_flag'] == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE &&
				$agree_flag == MULTIDATABASE_STATUS_AGREE_VALUE &&
				$this->mdb_obj['agree_mail_flag'] == _ON) {
				$this->session->setParameter("multidatabase_confirm_mail_content_id", $this->content_id);
			}
		}

		$params = array(
				"multidatabase_id" => $this->multidatabase_id
		);
		$metadatas = $this->mdbView->getMetadatas($params);

		foreach($metadatas as $metadata_id => $metadata) {
			$data_value = "";
			if($datas[$metadata_id] != null) {
				if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
					//$data_value = $datas[$metadata_id]['upload_id'];
					//$data_value = "<a href='?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id']."' target='_blank'>".$datas[$metadata_id]['file_name']."</a>";
					if($datas[$metadata_id] == _ON) {
						$data_value = "";
					}else {
						$data_value = "?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id'];
					}
				//}else if($metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
					//$data_value = "<img src='?action=".$datas[$metadata_id]['action_name']."&upload_id=".$datas[$metadata_id]['upload_id']."' title='".$datas[$metadata_id]['file_name']."' alt='".$datas[$metadata_id]['file_name']."' style='height:".$height."px;width:".$width."px;'/>";

				} else {
					$data_value = $datas[$metadata_id];
				}
			}

			if(!empty($this->content_id)) {
				$where_params = array(
					"content_id" => $this->content_id,
					"metadata_id" => $metadata_id
				);
				$metadata_content = $this->db->selectExecute("multidatabase_metadata_content", $where_params);
				if($metadata_content === false) {
					return 'error';
				}
				if ($metadata['type'] == MULTIDATABASE_META_TYPE_AUTONUM) {
					if ($status == MULTIDATABASE_STATUS_RELEASED_VALUE && empty($metadata_content[0]["content"])) {
						$data_value = $this->mdbView->getAutoNumber($metadata_id);
					} else {
						$data_value = $metadata_content[0]["content"];
					}
				}
				if(!(($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE)
					&& $datas[$metadata_id] != _ON && !is_array($datas[$metadata_id]))) {

					$params = array(
						"content" => $data_value
					);

					if(!empty($metadata_content)) {
						$result = $this->db->updateExecute("multidatabase_metadata_content", $params, $where_params, true);
						if ($result === false) {
							return 'error';
						}

						if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
							$where_params = array(
								"metadata_content_id" => $metadata_content[0]['metadata_content_id']
							);

							$metadata_file = $this->db->selectExecute("multidatabase_file", $where_params);
							if($metadata_file === false) {
								return 'error';
							}

							if(!isset($metadata_file[0])) {
								$params = array(
									"metadata_content_id" => $metadata_content[0]['metadata_content_id'],
									"upload_id" => $datas[$metadata_id]['upload_id'],
									"file_name" => $datas[$metadata_id]['file_name'],
									"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
									"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
									"room_id" => $this->room_id
								);

								$result = $this->db->insertExecute("multidatabase_file", $params);
								if ($result === false) {
									return 'error';
								}
							}else {
								$params = array(
									"upload_id" => $datas[$metadata_id]['upload_id'],
									"file_name" => $datas[$metadata_id]['file_name'],
									"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
									"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
									"room_id" => $this->room_id
								);
								$result = $this->db->updateExecute("multidatabase_file", $params, $where_params);
								if ($result === false) {
									return 'error';
								}
							}
						}
					}else {
						if ($metadata['type'] == MULTIDATABASE_META_TYPE_AUTONUM && $status == MULTIDATABASE_STATUS_RELEASED_VALUE) {
							$data_value = $this->mdbView->getAutoNumber($metadata_id);
						}
						if( $this->_insertContent($this->content_id, $metadata_id, $data_value, $metadata, $datas) === false ){
							return 'error';	//_insertContent()の戻り値をみて、エラーなら'error'を返すよう修正 by AllCreator
						}
					}
				}

				if($datas[$metadata_id] === _ON && ($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE)) {
					if(!empty($metadata_content)) {
						$result = $this->mdbAction->deleteMetadataContent($metadata_content[0]['metadata_content_id']);
						if ($result === false) {
							return 'error';
						}
					}
				}else if($metadata['type'] == MULTIDATABASE_META_TYPE_FILE && (!empty($data_value) || !isset($metadata_file[0]))) {
					if(!empty($metadata_content)) {
						$where_params = array(
							"metadata_content_id" => $metadata_content[0]['metadata_content_id']
						);
						$metadata_file = $this->db->selectExecute("multidatabase_file", $where_params);
						if($metadata_file === false) {
							return 'error';
						}
						$params = array(
							"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:""
						);
						$result = $this->db->updateExecute("multidatabase_file", $params, $where_params);
						if ($result === false) {
							return 'error';
						}
					}
				}
			}else {
				if ($metadata['type'] == MULTIDATABASE_META_TYPE_AUTONUM && $status == MULTIDATABASE_STATUS_RELEASED_VALUE) {
					$data_value = $this->mdbView->getAutoNumber($metadata_id);
				}
				if( $this->_insertContent($content_id, $metadata_id, $data_value, $metadata, $datas) === false ){
					return 'error';	//_insertContent()の戻り値をみて、エラーなら'error'を返すよう修正 by AllCreator
				}
			}
		}

		//承認を付いた場合、管理者にメールで通知する
	 	if($this->mdb_obj['agree_flag'] == _ON && $status == MULTIDATABASE_STATUS_RELEASED_VALUE && $agree_flag == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE) {
			$this->session->setParameter("multidatabase_mail_content_id", array("content_id" => (empty($this->content_id) ? $content_id : $this->content_id), "agree_flag" => MULTIDATABASE_STATUS_WAIT_AGREE_VALUE));
		}

		//--新着情報関連 Start--
		$result = $this->mdbAction->setWhatsnew((empty($this->content_id) ? $content_id : $this->content_id));
		if ($result === false) {
			return 'error';
		}
		//--新着情報関連 End--

		// --- 投稿回数更新 ---
		$before_content = isset($content_before_update[0]) ? $content_before_update[0] : null;
		$edit_flag = (empty($this->content_id)) ? false : true;

		$result = $this->mdbAction->setMonthlynumber($edit_flag, $status, $agree_flag, $before_content);
		if ($result === false) {
			return 'error';
		}

		if(!empty($this->upload_ids)) {
			foreach($this->upload_ids as $key => $val) {
				$this->uploadsAction->updGarbageFlag($this->upload_ids[$key]);
			}
		}
		$this->session->removeParameter(array("multidatabase_content", $this->block_id));

		return 'success';
	}

	function _insertContent($content_id, $metadata_id, $data_value, $metadata, $datas) {
		$params = array(
			"metadata_id" => $metadata_id,
			"content_id" => $content_id,
			"content" => $data_value
		);
		$metadata_content_id = $this->db->insertExecute("multidatabase_metadata_content", $params, true, "metadata_content_id");
		if ($metadata_content_id === false) {
			return false;
		}

		if(($metadata['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata['type'] == MULTIDATABASE_META_TYPE_IMAGE) && !empty($datas[$metadata_id])) {
			$params = array(
				"metadata_content_id" => $metadata_content_id,
				"upload_id" => $datas[$metadata_id]['upload_id'],
				"file_name" => $datas[$metadata_id]['file_name'],
				"file_password" => isset($this->passwords[$metadata_id])?$this->passwords[$metadata_id]:"",
				"physical_file_name" => $datas[$metadata_id]['physical_file_name'],
				"room_id" => $this->room_id
			);
			$result = $this->db->insertExecute("multidatabase_file", $params);
			if ($result === false) {
				return false;
			}
		}
		return true;
	}
}
?>