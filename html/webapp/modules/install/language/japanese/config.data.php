<?php
/**
 * config data
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
define('INSTALL_CONF_ADD_PRIVATE_SPACE_NAME_JAPANESE', '{X-HANDLE}のお部屋');
define('INSTALL_CONF_CLOSESITE_TEXT_JAPANESE', 'このサイトはただいまメンテナンス中です。後程お越しください');
define('INSTALL_CONF_FROMNAME_JAPANESE', 'NetCommons管理者');
define('INSTALL_CONF_AUTOREGIST_DISCLAIMER_JAPANESE', '本規約は、当サイトにより提供されるコンテンツの利用条件を定めるものです。以下の利用条件をよくお読みになり、これに同意される場合にのみご登録いただきますようお願いいたします。

当サイトを利用するにあたり、以下に該当する又はその恐れのある行為を行ってはならないものとします。 

・公序良俗に反する行為 
・法令に違反する行為 
・犯罪行為及び犯罪行為に結びつく行為 
・他の利用者、第三者、当サイトの権利を侵害する行為 
・他の利用者、第三者、当サイトを誹謗、中傷する行為及び名誉・信用を傷つける行為 
・他の利用者、第三者、当サイトに不利益を与える行為 
・当サイトの運営を妨害する行為 
・事実でない情報を発信する行為 
・プライバシー侵害の恐れのある個人情報の投稿 
・その他、当サイトが不適当と判断する行為 

【免責】

利用者が当サイト及び当サイトに関連するコンテンツ、リンク先サイトにおける一切のサービス等をご利用されたことに起因または関連して生じた一切の損害（間接的であると直接的であるとを問わない）について、当サイトは責任を負いません。');
define('INSTALL_CONF_MAIL_ADD_ANNOUNCE_SUBJECT_JAPANESE', '[{X-SITE_NAME}]承認待ち会員のお知らせ');
define('INSTALL_CONF_MAIL_ADD_ANNOUNCE_BODY_JAPANESE', '{X-SITE_NAME}にて新規登録ユーザがありました。

ログインを許可する場合は、下記のリンクをクリックして登録ユーザ宛てに承認メールを送信してください。');
define('INSTALL_CONF_MAIL_APPROVAL_SUBJECT_JAPANESE', '[{X-SITE_NAME}]会員登録確認メール');
define('INSTALL_CONF_MAIL_APPROVAL_BODY_JAPANESE', '{X-SITE_NAME}におけるユーザ登録用メールアドレスとしてあなたのメールアドレスが使用されました。
もし{X-SITE_NAME}でのユーザ登録に覚えがない場合はこのメールを破棄してください。

{X-SITE_NAME}でのユーザ登録を完了するには下記のリンクをクリックして登録の承認を行ってください。');
define('INSTALL_CONF_MAIL_GET_PASSWORD_SUBJECT_JAPANESE', '[{X-SITE_NAME}]新規パスワードのリクエスト');
define('INSTALL_CONF_MAIL_GET_PASSWORD_BODY_JAPANESE', ' {X-SITE_NAME}におけるログイン用パスワードの新規発行リクエストがありました。
新たにパスワードを発行する場合は下記のリンクをクリックしてください。
指定のメールアドレスに新しいパスワードをお送りします。

何かの手違いでこのメールが届いた場合には、ただちにこのメールを削除してください。
これまでのパスワードでログインすることができます。');
define('INSTALL_CONF_MAIL_NEW_PASSWORD_SUBJECT_JAPANESE', '[{X-SITE_NAME}]新規パスワードの発行');
define('INSTALL_CONF_MAIL_NEW_PASSWORD_BODY_JAPANESE', '{X-SITE_NAME}におけるログイン用パスワードの新規発行リクエストがありました。
下記があなたのログイン用IDと新しいパスワードです。
セキュリティを保つため、ただちに{X-SITE_NAME}にログインし、パスワードを変更することをお勧めします。');
define('INSTALL_CONF_MAIL_ADD_USER_SUBJECT_JAPANESE', '{X-SITE_NAME}へようこそ');
define('INSTALL_CONF_MAIL_ADD_USER_BODY_JAPANESE', '会員登録が完了しましたのでお知らせします。
ハンドル：{X-HANDLE}
ログインID：{X-LOGIN_ID}
パスワード：{X-PASSWORD}
e-mail：{X-EMAIL}
参加ルーム：
{X-ENTRY_ROOM}
下記アドレスからログインしてください。
{X-URL}');
define('INSTALL_CONF_MAIL_WITHDRAW_MEMBERSHIP_SUBJECT_JAPANESE', '[{X-SITE_NAME}]会員退会のお知らせ');
define('INSTALL_CONF_MAIL_WITHDRAW_MEMBERSHIP_BODY_JAPANESE', '{X-SITE_NAME}の{X-HANDLE}が退会しました。');
?>