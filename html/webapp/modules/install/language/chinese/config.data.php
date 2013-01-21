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

define('INSTALL_CONF_ADD_PRIVATE_SPACE_NAME_CHINESE', '{X-HANDLE}的房间');
define('INSTALL_CONF_CLOSESITE_TEXT_CHINESE', '本网站正在维护中，请稍后再尝试链接。');
define('INSTALL_CONF_FROMNAME_CHINESE', 'NetCommons管理员');
define('INSTALL_CONF_AUTOREGIST_DISCLAIMER_CHINESE', '当网站管理员和管理助手在忙于删除或修改一些不受欢迎的素材时，用户无法浏览任何文章。所以用户有必要了解，发表在网站上的文章表达的是作者本人的观点而非网站管理员等工作人员的，并且我们也没有义务阻止用户发表观点。

您同意不发表带有辱骂、淫秽、粗俗、诽谤、憎恶、威胁、性歧视以及其他任何违反有关法律的文章。如果您发表了类似的文章，将会被立即并永久性禁止登录本网站（同时会通知您的服务提供商）。所有发表文章的IP地址都会被记录下来以保证惩罚措施被强制执行。一个用户创建多个帐户是不允许的。您同意本网站的管理员有权利在任何他们认为合适的时间移除、编辑、更改文件夹或关闭任意一个话题。作为用户，您同意您输入的所有信息都被存入数据库。同时我们保证未经您的允许不向任何第三方泄漏您的信息，但网站管理员等工作人员不会为因黑客攻击而造成的数据泄漏负责。

本网站系统通过cookies将信息存入您的电脑。cookies并不包含您输入的任何信息，它只是用来使您的浏览更流畅，邮箱也仅仅是用来确认您的注册信息和密码（并在您忘记密码时提醒您当前使用的密码）。 

如果您同意上述条款请按"提交"继续安装。');
define('INSTALL_CONF_MAIL_ADD_ANNOUNCE_SUBJECT_CHINESE', '[{X-SITE_NAME}]新注册用户');
define('INSTALL_CONF_MAIL_ADD_ANNOUNCE_BODY_CHINESE', '一位新用户刚刚完成注册。
请点击下面的链接激活该用户的帐户。');
define('INSTALL_CONF_MAIL_APPROVAL_SUBJECT_CHINESE', '欢迎来到{X-SITE_NAME}');
define('INSTALL_CONF_MAIL_APPROVAL_BODY_CHINESE', '感谢您注册{X-SITE_NAME}网站。
您的邮箱已被用来注册帐户。\r\n如果您并不想注册，只需删除该邮件即可。
请点击下面的链接来确认您的申请：');
define('INSTALL_CONF_MAIL_GET_PASSWORD_SUBJECT_CHINESE', '关于您在[{X-SITE_NAME}]网站上的用户密码');
define('INSTALL_CONF_MAIL_GET_PASSWORD_BODY_CHINESE', '您在{X-SITE_NAME}网站上的账户要求重新设置用户密码。
如果您不记得或者不需要新的用户密码，只需删除该邮件即可。
您可以通过点击下面的链接获得新的用户密码：
');
define('INSTALL_CONF_MAIL_NEW_PASSWORD_SUBJECT_CHINESE', '您在[{X-SITE_NAME}]网站上的新的用户密码');
define('INSTALL_CONF_MAIL_NEW_PASSWORD_BODY_CHINESE', '您在{X-SITE_NAME}网站上的账户要求重新设置用户密码。
这是您新帐户的相关信息。
请在您方便的时候使用新密码登录。
');
define('INSTALL_CONF_MAIL_ADD_USER_SUBJECT_CHINESE', '欢迎来到{X-SITE_NAME}');
define('INSTALL_CONF_MAIL_ADD_USER_BODY_CHINESE', '感谢您注册{X-SITE_NAME}网站。
昵称：{X-HANDLE}
用户名：{X-LOGIN_ID}
登录密码：{X-PASSWORD}
注册邮箱：{X-EMAIL}
进入房间：
{X-ENTRY_ROOM}
您可以通过点击此链接或将链接复制到浏览器上登录：
{X-URL}');
define('INSTALL_CONF_MAIL_WITHDRAW_MEMBERSHIP_SUBJECT_CHINESE', '[{X-SITE_NAME}]用户注销通知');
define('INSTALL_CONF_MAIL_WITHDRAW_MEMBERSHIP_BODY_CHINESE', '{X-SITE_NAME}的{X-HANDLE}已经注销。');
?>