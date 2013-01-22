<?php
define("_ON",1);
define("_OFF",0);

define('_CHARSET', 'UTF-8');

define("_SPACE_TYPE_UNDEFINED",0);	//未定義
define("_SPACE_TYPE_PUBLIC",1);		//パブリックスペース
define("_SPACE_TYPE_GROUP",2);		//グループスペース

//----------------デフォルトアクション-Indexファイル名称-------------------------
if(!is_writeable(INSTALL_INC_DIR . "/". "install.inc.php") || !is_dir(BASE_DIR."/webapp/modules/install/")) {
	define('DEFAULT_ACTION', 'pages_view_main');
} else {
	define('DEFAULT_ACTION', 'install_view_main_init');
}
define('DEFAULT_MOBILE_ACTION', 'pages_view_mobile');
define('INDEX_FILE_NAME', '/index.php');

//----------------権限-----------------------------------------
define("_AUTH_ADMIN",5);			// 管理者
define("_AUTH_CHIEF",4);			// 主担
define("_AUTH_MODERATE",3);			// モデレータ
define("_AUTH_GENERAL",2);			// 一般
define("_AUTH_GUEST",1);			// ゲスト
define("_AUTH_OTHER",0);			// その他

//----------------ロール権限(権限管理での一覧)-----------------
define("_ROLE_AUTH_ADMIN",1);			// 管理者
define("_ROLE_AUTH_CHIEF",2);			// 主担
define("_ROLE_AUTH_MODERATE",3);		// モデレータ
define("_ROLE_AUTH_GENERAL",4);			// 一般
define("_ROLE_AUTH_GUEST",5);			// ゲスト
define("_ROLE_AUTH_CLERK",6);			// 事務局
define("_ROLE_AUTH_OTHER",0);			// その他

//----------------権限の階層(hierarchy)-----------------
// モデレータは細分化によりインクリメントされる可能性があるためdefine不可
// レベルは0～100までの指定のため、モデレータは3～103の範囲
define("_HIERARCHY_ADMIN",105);			// 管理者
define("_HIERARCHY_CHIEF",104);			// 主担
define("_HIERARCHY_GENERAL",2);			// 一般
define("_HIERARCHY_GUEST",1);			// ゲスト
define("_HIERARCHY_OTHER",0);			// その他

//---------------display_scope:TODO:現状未使用-----------------------------
//1:すべてのユーザが検索可能とし公開を許す(マイページのみ表示項目)
//2:すべてのユーザにページタイトルを検索可能にする
//3:ログインユーザにページタイトルを検索可能にする
//4:タイトルを検索不可にし、共有設定させない
define("_DISPLAY_SCOPE_PUBLIC",1);
define("_DISPLAY_SCOPE_ALLSEARCHABLE",2);
define("_DISPLAY_SCOPE_SEARCHABLE",3);
define("_DISPLAY_SCOPE_NONE",4);

//----------------表示位置-------------------------
define("_DISPLAY_POSITION_CENTER",0);			// 中央カラム
define("_DISPLAY_POSITION_LEFT",1);				// 左カラム
define("_DISPLAY_POSITION_RIGHT",2);			// 右カラム
define("_DISPLAY_POSITION_HEADER",3);			// ヘッダーカラム
define("_DISPLAY_POSITION_FOOTER",4);			// フッターカラム(未使用）

define('_USER_ACTIVE_FLAG_OFF',     0);		//利用不可
define('_USER_ACTIVE_FLAG_ON',      1);		//利用可能
define('_USER_ACTIVE_FLAG_PENDING', 2);		//承認待ち
define('_USER_ACTIVE_FLAG_MAILED',  3);		//承認済み

//----------------AUTHORITIES------------------
define('_SYSTEM_ROLE_AUTH_ID', 1);		//システム管理者ロール権限ID

//----------------PAGES------------------
define('_SELF_TOPPUBLIC_ID', 1);		//自サイトトップパブリックスペースID
define('_SELF_TOPGROUP_ID', 2);			//自サイトトップグループスペースID
//define('_SELF_TOPMYPORTAL_ID', 13);		//自サイトトップパブリックスペースID

//----------------DEBUGモード------------------
define("_DEBUG_PHP",1);				// PHPデバッグ
define("_DEBUG_SQL",2);				// SQLデバッグ
define("_DEBUG_SMARTY",3);			// SMARTYデバッグ
define("_DEBUG_MAPLE",4);			// MAPLEデバッグ
//-----------------CONFIG関連-------------------
define("_SYS_CONF_MODID",0);
define("_MAIN_CONF_CATID", 0);

//-----------------システム管理-セキュリティ管理-------------------
define("_GENERAL_CONF_CATID",    0);		// 一般
define("_SERVER_CONF_CATID",     1);		// サーバ関連
define("_MAIL_CONF_CATID",       2);		// メール関連
define("_META_CONF_CATID",       3);		// Meta関連
define("_PAGESTYLE_CONF_CATID",  4);		// ページスタイル関連(getdataに保持)
define("_ENTER_EXIT_CONF_CATID", 5);		// 入退会機能関連
define("_DEBUG_CONF_CATID",      6);		// 開発者向け機能関連
define("_SECURITY_CONF_CATID",   7);		// セキュリティ管理関連(getdataに保持)

//-----------------SQL関連-------------------
define("_DEFAULT_SQL_KIND","mysql");	//DB種類のデフォルト値

define("_SYS_TABLE_INI","table.sql");
define("_SYS_TABLE_SEQID_POSTFIX", "_seq_id");

//-----------------セッション-------------------
define("_SESSION_IMAGE_AUTH", "_session_image_auth");	//画像認証用prefix

//-----------------Cache-------------------
define("_SMARTY_CACHE_EXPIRE", 60*24*7);			//smarty_cacheの有効期間（分）:1週間 デフォルト値

//-----------------アップロード許可フラグ-------------
define("_ALLOW_ATTACHMENT_NO", 0);
define("_ALLOW_ATTACHMENT_IMAGE" ,1);
define("_ALLOW_ATTACHMENT_ALL", 2);

//-----------------自動ログイン-------------
define("_AUTOLOGIN_NO", 0);					//自動ログイン禁止
define("_AUTOLOGIN_LOGIN_ID" ,1);			//ログインIDのみ保存
define("_AUTOLOGIN_OK", 2);					//自動ログインさせる
define("_AUTOLOGIN_LIFETIME", 604800);		// 自動ログインCookie保持期間（デフォルト1週間）

//-----------------自動登録-------------
define("_AUTOREGIST_SELF", 0);					//ユーザ自身の確認が必要
define("_AUTOREGIST_AUTO" ,1);					//自動的にアカウントを有効にする
define("_AUTOREGIST_ADMIN", 2);					//管理者の承認が必要

//-----------------プライベートスペースを公開するかどうか-------------
define("_OPEN_PRIVATE_SPACE_GROUP" ,1);					//ログイン会員ならば表示可
define("_OPEN_PRIVATE_SPACE_PUBLIC", 2);				//ログインしていなくても表示可（パブリックスペース化）
define("_OPEN_PRIVATE_SPACE_MYPORTAL_GROUP" ,3);		//ログイン会員ならば表示可なマイポータルと、マイルームを両方使用
define("_OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC", 4);		//ログインしていなくても表示可なマイポータル（パブリックスペース化）と、マイルームを両方使用

//-----------------セキュリティ関連-------------
define("_SECURITY_LEVEL_NONE",   0);						//チェックしない
define("_SECURITY_LEVEL_MEDIUM", 1);						//中
define("_SECURITY_LEVEL_HIGH",   2);						//高
define("_SECURITY_LEVEL_CUSTOM", 3);						//カスタマイズ

define("_SECURITY_LOG_LEVEL_NONE",       0);				//ログ出力なし
define("_SECURITY_LOG_LEVEL_HIGH",      15);				//危険性の高いものだけログをとる
define("_SECURITY_LOG_LEVEL_MEDIUM",    63);				//危険性のやや高いものだけログをとる
define("_SECURITY_LOG_LEVEL_LOW",      255);				//全種類のロギングを有効とする

define("_SECURITY_VALUE_NONE",       0);					//ログ出力なし
define("_SECURITY_VALUE_LOGONLY",    1);					//ログのみとる
define("_SECURITY_VALUE_DETOX",      2);					//無害化
define("_SECURITY_VALUE_CRASH",      3);					//強制終了
define("_SECURITY_VALUE_EXIT",       4);					//exit
define("_SECURITY_VALUE_REJECT_IP",  5);					//拒否IPリストへ入れる

//-----------------pages:display_flag-------------
define("_PAGES_DISPLAY_FLAG_DISABLED", 2);					//使用不可(現状、プライベートスペースのみ)

//-----------------サイト閉鎖-------------
define("_CLOSESITE_ALLOW_ACTION", "login_view_main_init|login_action_main_init|login_action_main_logout");	//サイト閉鎖時に許すアクション

//-----------------ブロックデザイン-------------------

define("_BLOCKSTYLE_METHOD_DIALOG","commonCls.sendPopupView(event,{'action':'dialog_blockstyle_view_edit_init','page_id':<{\$block_obj.page_id}>,'block_id':<{\$block_obj.block_id}>,'parent_id_name':'<{\$id}>','prefix_id_name':'dialog_blockstyle'});");
define("_EDIT_DESIGN_METHOD","commonCls.sendView('<{\$id}>',{'action':'dialog_blockstyle_view_edit_init','block_id':<{\$block_id}>,'parent_id_name':'<{\$id}>','inside_flag':1},null, 1);");

//-----------------モジュール操作タブ(移動-コピー-ショートカット)--------
define("_OPERATION_METHOD","commonCls.sendView('<{\$id}>',{'action':'common_operation_view_init','block_id':<{\$block_id}>,'parent_id_name':'<{\$id}>','module_id':<{\$module_id}>},null, 1);");

//-----------------style/themes-----------------------
define("_CATEGORY_INIFILE",          "category.ini");
define("_BACKGROUND_INIFILE",         "background.ini");
define("_THEME_INIFILE",             "theme.ini");
define("_THEME_ICON_COLOR_INIFILE",  "icon_color.ini");
define("_PAGETHEME_CUSTOM_INIFILE",  "page_custom.ini");
define("_BLOCKTHEME_CUSTOM_INIFILE", "block_custom.ini");

//css_filesテーブル type

define("_CSS_TYPE_COMMON",      -1);	// 共通
define("_CSS_TYPE_MODULE",       0);	// モジュールCSS
define("_CSS_TYPE_THEME",        1);	// テーマCSS
define("_CSS_TYPE_PAGE_CUSTOM",  2);	// カスタム用(ページ)
define("_CSS_TYPE_BLOCK_CUSTOM", 3);	// カスタム用(ブロック)


//-----------------検索の種類-------------------
define("_SELECT_KIND_AND", 0);		//すべて(AND検索)
define("_SELECT_KIND_OR", 1);		//いずれか(OR検索)
define("_SELECT_KIND_PHRASE", 2);	//フレーズ

//----------------共通正規表現-------------------
define("_REGEXP_ALLOW_HALFSIZE_SYMBOL","/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/");	//半角または記号
define("_REGEXP_UPLOAD_ID","/<(?:img|a) .*?(?:src|href)\s*?=\s*?[\"']{1}.*?(?:&|&amp;)upload_id=([0-9]*?)[\"']{1}.*?>/iu");

// upload_id振替用
define("_REGEXP_PRE_TRANSFER_UPLOAD_ID","/(<(?:img|a) .*?(?:src|href)\s*?=\s*?[\"']{1}.*?(?:&|&amp;)upload_id=)");
define("_REGEXP_POST_TRANSFER_UPLOAD_ID","([\"']{1}.*?>)/iu");

// $_REQUESTに「id」で終わる項目を数値に強制変換されない項目名（正規表現文字列）
define("_REGEXP_REQUEST_ID_NO_CHECK_NAME", "/.*?(login_id|user_id|site_id|unique_id)$/");

// mb_detect_order値 mb関連の関数がインストールされている場合、有効
define("_MB_DETECT_ORDER_VALUE", "EUC-JP, SJIS, JIS, UTF-8, ASCII");

//----------------ファイルクリーンアップ-------------------
define("_CLEANUP_DEL_DAY", 1);			//削除対象日 1日前

//-----------------システムで使用不可テーブル名「,」区切りで複数指定可能--
define("_DB_RESERVED_TABLES", "");

//page_style.css
//style.css
//costomcolor.ini
//-----------------長さチェック用-------------------------------------------
define('_VALIDATOR_TITLE_LEN', 100);
define('_VALIDATOR_MAIL_LEN', 256);
define('_VALIDATOR_TEXTAREA_LEN', 60000);

//-----------------Scriptの出力する位置(header or footer)-------------------
define('_SCRIPT_OUTPUT_POS', "footer");
//-----------------フルバックアップにソースを含めるかどうか-----------------
define('_FULLBACKUP_OUTPUT_SOURCE', true);

//-----------------固定リンク関連 正規表現で記述-----------------
define('_PERMALINK_PUBLIC_PREFIX_NAME', '');
define('_PERMALINK_MYPORTAL_PREFIX_NAME', 'myportal');
define('_PERMALINK_PRIVATE_PREFIX_NAME', 'private');
define('_PERMALINK_GROUP_PREFIX_NAME', 'group');

define('_PERMALINK_CONTENT', '(%| |#|<|>|\+|\\\\|\"|\'|&|\?|\.$|=|\/|~|:|;|,|\$|@|^\.|\||\]|\[|\!|\(|\)|\*)');
define('_PERMALINK_PROHIBITION', "/"._PERMALINK_CONTENT."/i");
define('_PERMALINK_PROHIBITION_REPLACE', "-");
define('_PERMALINK_DIR_CONTENT', "^(install|css|js|images|themes|htdocs|webapp|maple|".
	_PERMALINK_PUBLIC_PREFIX_NAME."|"._PERMALINK_MYPORTAL_PREFIX_NAME."|"._PERMALINK_PRIVATE_PREFIX_NAME."|".
	_PERMALINK_GROUP_PREFIX_NAME.")$");
define('_PERMALINK_PROHIBITION_DIR_PATTERN', "/"._PERMALINK_DIR_CONTENT."/i");

//-----------------URL短縮形-------------------
define('_ABBREVIATE_URL_LENGTH', 9);
define('_ABBREVIATE_URL_PATTERN', 'abcdefghijklmnopqrstuvwxyz0123456789');
define('_ABBREVIATE_URL_REQUEST_KEY', 'key');

//-----------------HTTPS URL関連-------------------
if (!defined('BASE_URL_HTTPS')) {
	define('BASE_URL_HTTPS', preg_replace("/^http:\/\//i", "https://", BASE_URL));
}
define('_WYSIWYG_CONVERT_OUTER', '{{%s}}');

//-----------------MYSQL関連-----------------------
define("_MYSQL_FT_MIN_WORD_LEN",          4);

//------モジュールアップデート間隔(ミリ秒)---------
define('_MODULE_ALLUPDATE_INTERVALTIME', 0);

?>