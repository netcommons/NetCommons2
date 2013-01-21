<?php
define('INSTALL_DEFAULT_LANG', 'japanese');
define('INSTALL_DATABASE', 'mysql');
define('INSTALL_DATABASE_LIST', 'mysql|mysqli');	// 複数ある場合は、「|」区切り
define('INSTALL_DEFAULT_DBUSERNAME', '');
define('INSTALL_DEFAULT_DBPASS', '');
define('INSTALL_DEFAULT_DBHOST', 'localhost');
define('INSTALL_DEFAULT_DBNAME', 'netcommons');
define('INSTALL_DEFAULT_DATABASE_PREFIX', 'netcommons2');
define('INSTALL_DEFAULT_DATABASE_PERSIST', 0);

define('INSTALL_IMG_YES', '<img src="./install/yes.gif" alt="yes" class="install_img" />&nbsp;');
define('INSTALL_IMG_NO', '<img src="./install/no.gif" alt="no" class="install_img" />&nbsp;');

define('INSTALL_WELCOME_MES_FILENAME', "welcome.php");
define('INSTALL_CONFIG_DATA_FILENAME', "config.data.php");
define('INSTALL_INSERT_DATA_COMMON_FILENAME', "common_insert.data.php");
define('INSTALL_INSERT_DATA_FILENAME', "insert.data.php");
define('INSTALL_FINISH_MES_FILENAME', "finish.php");
define('INSTALL_MEMORY_LIMIT', "32M");

define('INSTALL_CONFIG_START_SEQ_ID', "200");
// モジュール管理の表示順
// モジュールのインストールがすべて完了した後、並べ替える
// ここに記載がないモジュールは、自動的に並べ替えられる
define('INSTALL_MODULES_SYS_DISPLAY_SEQ',"userinf,user,policy,room,authority,system,module,cleanup,backup,holiday,mobile,security,share");
define('INSTALL_MODULES_GENERAL_DISPLAY_SEQ',"announcement,iframe,questionnaire,chat,counter,todo,calendar,whatsnew,linklist,bbs,cabinet,assignment,quiz,online,reservation,rss,journal,photoalbum,multidatabase,registration,search,login,menu,imagine");

define('INSTALL_HTACCESS_FILENAME', ".htaccess");
define('INSTALL_HTACCESS_DATA_FILENAME', "htaccess.data.php");
?>