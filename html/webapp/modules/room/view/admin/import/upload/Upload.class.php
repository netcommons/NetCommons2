<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * インポートファイルのアップロード受付
 *
 * @package	 NetCommons
 * @author	  Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2012 AllCreator Co., Ltd.
 * @project	 NC Support Project, provided by AllCreator Co., Ltd.
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @access	  public
 */

class Room_View_Admin_Import_Upload extends Action
{
	var $db = null;
	var $session = null;
	var $actionChain = null;
	var $uploadsAction = null;
	var $csvMain = null;
	var $authoritiesView = null;
	var $roomView = null;

	var $parent_page_id = null;
	var $edit_current_page_id = null;
	var $parent_page = null;
	var $page = null;

	/**
	 * インポートファイルのアップロード
	 *
	 * @access  public
	 */
	function execute()
	{
		// 処理時間のリミット
		set_time_limit(ROOM_TIME_LIMIT);

		// これから出力するかもしれないエラー情報をためておくところ
		$errorList =& $this->actionChain->getCurErrorList();

		// アップロードされたファイルを取得します
		$garbage_flag = _ON;
		$filelist = $this->uploadsAction->uploads($garbage_flag);

		$file = FILEUPLOADS_DIR.'room/'.$filelist[0]['physical_file_name'];
		$filename = $filelist[0]['file_name'];

		// アップロードフィルタ側で何らかのエラーが発生していたようであればエラーリターンする
		if(isset($filelist['error_mes']) && $filelist['error_mes'] != '') {
			$errorList->add(get_class($this), sprintf(_FILE_UPLOAD_ERR_FAILURE.'(%s)', $filelist['error_mes']));
			return 'error';
		}
		else if($filelist[0]['extension'] != 'csv') {
			// ファイル種別チェック
			// 拡張子がCSVでない場合はエラーとする
			$errorList->add(get_class($this), sprintf(_FILE_UPLOAD_ERR_FILENAME_REJECRED.'(%s)', $filename));
			$this->_delImportFile($file);
			return 'error';
		}

		// ファイルを開く
		$handle = fopen($file, 'r');
		if($handle == false) { // ファイルオープンエラー
			$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_OPEN_ERR.'(%s)', $file));
			$this->_delImportFile($file);
			return 'error';
		}

		//
		// 過去のセッションにあるインポートデータを削除する
		//
		$this->session->removeParameter(array('room', 'import', 'data'));
		$this->session->removeParameter(array('room', 'import', 'count'));
		$this->session->removeParameter(array('room', 'import', 'warn'));

		// 対象となる権限達
		$tmp_authorities = $this->authoritiesView->getAuthorities(array('(system_flag ='. _ON . ' OR user_authority_id = '. _AUTH_MODERATE. ') AND user_authority_id != '. _AUTH_ADMIN => null));
		$authorities = array();
		foreach($tmp_authorities as $a) {
			$a['new_total'] = 0;
			$a['added_num'] = 0;
			$authorities[$a['role_authority_id']] = $a;
		}

		// 該当ルームへの参加者情報を取得する
		$room_users = $this->roomView->getRoomUsersList($this->page, $this->parent_page, $authorities);
		if($room_users == false) {
			$errorList->add(get_class($this), ROOM_IMPORT_UPLOAD_NOROOM_ERR);
			$this->_delImportFile($file);
			return 'error';
		}

		// ところで、操作しているあなたは、このルームの主担権限をもっているの？
		if(isset($room_users[ $this->session->getParameter('_handle')])) {
			if($room_users[ $this->session->getParameter('_handle')]['authority_id'] != _ROLE_AUTH_CHIEF) {
				$errorList->add(get_class($this), ROOM_IMPORT_UPLOAD_NO_PERMISSION);
				$this->_delImportFile($file);
				return 'error';
			}
		}
		else {  // ルーム情報にない？
			$errorList->add(get_class($this), ROOM_IMPORT_UPLOAD_NO_PERMISSION);
			$this->_delImportFile($file);
			return 'error';
		}

		$my_handle = $this->session->getParameter("_handle");

		// その後のファイルの内容を展開し
		// １行ずつチェックしていく
		// 成功した行はセッション変数に覚えさせる
		// エラーがあったら速攻リターン
		$chg_num = 0;
		$delete_num = 0;
		$line = -1;
		while( ($row=$this->csvMain->fgets($handle)) != false ) {
			if($line>=ROOM_IMPORT_LINE_LIMIT) {
				$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_LINE_OVER_ERR."(%s)", ROOM_IMPORT_LINE_LIMIT, $filename));
				$this->cleanup($file);
				return 'error';
			}

			// SJISで来るので文字コード変換
			$row = $this->convertCsv($row);
			// CSVのカラム数チェック おかしな行が１行でもあれば処理を中断
			if(count($row) != ROOM_IMPORT_ITEM_COLUMN) {
				$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_COLUMN_ERR."(%s)", $line+1, $filename));
				$this->cleanup($file);
				return 'error';
			}

			$line++;
			// １行目はヘッダ扱いとしてスキップ
			if($line == 0) {
			}
			else {
				// その人は会員情報に存在するのか？
				if(!isset($room_users[$row[0]])) {
					$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_NOUSER_ERR, $line+1, $row[0]));
					$this->cleanup($file);
					return 'error';
				}
				else {
					$target_user = $room_users[$row[0]];
				}

				$now_auth = $target_user['authority_id'];
				$room_users[$row[0]]['authority_id'] = $now_auth;

				// 権限欄が空の場合はスキップ
				if($row[1]=='') {
					continue;
				}
				// ハンドル名が空の場合はスキップ
				if($row[0] =='') {
					continue;
				}
				// 自分自身がCSVにある場合はスキップ
				if($target_user['handle'] == $my_handle) {
					$this->session->setParameter(array("room", "import", "warn", $row[0]), ROOM_IMPORT_CONFIRM_MYSELF_WRAN);
					continue;
				}
				// ベース権限が管理者はスキップ
				if($target_user['user_authority_id'] == _AUTH_ADMIN) {
					$this->session->setParameter(array("room", "import", "warn", $row[0]), ROOM_IMPORT_CONFIRM_ADMINUSER_WRAN);
					continue;
				}
				// 不参加に変更
				if($row[1]==_ROLE_AUTH_OTHER) {
					//Public空間の場合はスキップ
					if($this->page['space_type']==_SPACE_TYPE_PUBLIC) {
						$this->session->setParameter(array("room", "import", "warn", $row[0]), ROOM_IMPORT_CONFIRM_PUBLIC_OTHER_WRAN);
						continue;
					}
					// 現状の権限が不参加状態でないなら、削除データをセッション記録
					if($now_auth != _ROLE_AUTH_OTHER) {
						$this->session->setParameter(array("room", "import", "data", $row[0]), $row[1]);
						$room_users[$row[0]]['new_authority'] = _ROLE_AUTH_OTHER;
						$delete_num++;
					}
				}
				// 参加状態 権限指定
				else {
					// それは存在する権限IDか
					if(!isset($target_user['permitted_auth'][$row[1]])) {
						$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_NOAUTH_ERR, $line+1, $row[1]));
						$this->cleanup($file);
						return 'error';
					}
					// このユーザーに許可された権限か
					if(!($target_user['permitted_auth'][$row[1]])) {
						$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_NOT_PERMIT_AUTH, $line+1, $row[0], $row[1]));
						$this->cleanup($file);
						return 'error';
					}
					// それは現在設定されている権限IDと一緒？それとも異なる？
					if($now_auth != $row[1]) {
						// 異なるならば、変更対象として処理続行
						$this->session->setParameter(array("room", "import", "data", $row[0]), $row[1]);
						$room_users[$row[0]]['new_authority'] = $row[1];
						// 移行人数カウントアップ
						$authorities[$row[1]]['added_num']++;
						$chg_num++;
					}
				}
			}
		}
		// ファイルクローズ＆削除
		fclose($handle);
		$this->_delImportFile($file);

		// ファイルの中身が空エラー
		if($line<1) {
			$errorList->add(get_class($this), sprintf(ROOM_IMPORT_UPLOAD_NODATAS_ERR."(%s)", $filename));
			$this->_delImportFile($file);
			return 'error';
		}
		// 変更となる人がいない場合はエラー
		if($chg_num==0 && $delete_num==0) {
			$errorList->add(get_class($this), ROOM_IMPORT_UPLOAD_NO_CHAGE);
			$this->_delImportFile($file);
			return 'error';
		}
        foreach($room_users as $handle=>$user) {
            if(isset($user['new_authority'])) {
                $count_auth = $user['new_authority'];
            }
            else {
                $count_auth = $user['authority_id'];
            }
			if($count_auth != _ROLE_AUTH_OTHER) {
				if(isset($authorities[$count_auth])) {
					$authorities[$count_auth]['new_total']++;
				}
			}
		}
		foreach($authorities as $key=>$a) {
			$this->session->setParameter(array('room', 'import', 'count', $key, 'added_num'), $a['added_num']);
			$this->session->setParameter(array('room', 'import', 'count', $key, 'new_total'), $a['new_total']);
		}
		$this->session->setParameter(array('room', 'import', 'count', 'delete_num'), $delete_num);
		return 'success';
	}
	function cleanup($file) {
		$this->session->removeParameter(array('room', 'import', 'data'));
		$this->session->removeParameter(array('room', 'import', 'count'));
		$this->session->removeParameter(array('room', 'import', 'warn'));
		$this->_delImportFile($file);
	}
	/**
	 * 配列のデータそれぞれを文字エンコード
	 *
	 * @access  private
	 */
	function convertCsv($row) {
		$ret = array();
		foreach($row as $index=>$value) {
			$ret[$index] = mb_convert_encoding($value, _CHARSET, 'SJIS');
		}
		return $ret;
	}
	/**
	 * インポートファイルの掃除
	 *
	 * @access  private
	 */
	function _delImportFile($file_path) {
		if(file_exists($file_path)) {
			@chmod($file_path, 0777);
			unlink($file_path);
		}
	}
}
?>