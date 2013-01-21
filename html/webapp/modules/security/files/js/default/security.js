var clsSecurity = Class.create();
var securityCls = Array();

clsSecurity.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	_setValue: function(el, def_val) {
			if (typeof def_val != "undefined") {
				Form.Element.setValue(el, def_val);
			} else {
				switch (el.tagName.toLowerCase()) {
				case "input":
					switch (el.type.toLowerCase()) {
					case "text":
						Form.Element.setValue(el, "");
						break;
				    case 'checkbox':
				    case 'radio':
					default:
						el.checked = false;
					}
					break;
				case "select":
					el.options[0].selected = true;
					break;
				case "textarea":
					Form.Element.setValue(el, "");
					break;
				default:
				}
			}
	},
	// セキュリティレベルごとのデフォルトを設定する
	chgSecurity: function(form_el) {
		// セキュリティ：高
		if (form_el.security_level.value == "2") {
			this._setValue(form_el.log_level, "255");
			this._setValue(form_el.san_nullbyte, "1");
			this._setValue(form_el.contami_action, "3");
			this._setValue(form_el.isocom_action, "2");
			this._setValue(form_el.union_action, "2");
			this._setValue(form_el.file_dotdot, "1");
			this._setValue(form_el.dos_expire, "60");
			this._setValue(form_el.dos_f5count, "20");
			this._setValue(form_el.dos_f5action, "5");
//			this._setValue(form_el.reliable_ips, "");
			this._setValue(form_el.dos_crcount, "60");
			this._setValue(form_el.dos_craction, "5");
//			this._setValue(form_el.dos_crsafe, "/(msnbot|Googlebot|Yahoo! Slurp)/i");
			this._setValue(form_el.bip_except_admin);
			this._setValue(form_el.bip_except_admin, "1");
			this._setValue(form_el.bip_except_chief);
			this._setValue(form_el.bip_except_chief, "0");
			this._setValue(form_el.bip_except_moderate);
			this._setValue(form_el.bip_except_moderate, "0");
			this._setValue(form_el.bip_except_general);
			this._setValue(form_el.bip_except_general, "0");
			this._setValue(form_el.bip_except_guest);
			this._setValue(form_el.bip_except_guest, "0");
			this._setValue(form_el.bip_except, "5");
//			this._setValue(form_el.enable_badips, "0");
//			this._setValue(form_el.bad_ips, "127.0.0.1");
			this._setValue(form_el.censor_enable, "1");
//			this._setValue(form_el.censor_words, "fuck|shit");
//			this._setValue(form_el.censor_replace, "#OOPS#");
//			this._setValue(form_el.extension, "1");
//			this._setValue(form_el.allow_extension, "csv,hqx,doc,dot,bin,lha,lzh,class,so,dll,pdf,ai,eps,ps,smi,smil,wbxml,wmlc,wmlsc,xla,xls,xlt,ppt,csh,dcr,dir,dxr,spl,gtar,sh,swf,sit,tar,tcl,xht,xhtml,ent,dtd,mod,gz,tgz,zip,au,snd,mid,midi,kar,mp1,mp2,mp3,aif,aiff,m3u,ram,rm,rpm,ra,wav,bmp,gif,jpeg,jpg,jpe,png,tiff,tif,wbmp,pnm,pbm,pgm,ppm,xbm,xpm,ics,ifb,css,html,htm,asc,txt,rtf,sgml,sgm,tsv,wml,wmls,xsl,mpeg,mpg,mpe,qt,mov,avi,wmv,asf,tex,dvi");
//			this._setValue(form_el.deny_extension, "");
			this._setValue(form_el.groups_denyipmove_admin);
			this._setValue(form_el.groups_denyipmove_admin, "1");
			this._setValue(form_el.groups_denyipmove_chief);
			this._setValue(form_el.groups_denyipmove_chief, "1");
			this._setValue(form_el.groups_denyipmove_moderate);
			this._setValue(form_el.groups_denyipmove_moderate, "1");
			this._setValue(form_el.groups_denyipmove_general);
			this._setValue(form_el.groups_denyipmove_general, "0");
			this._setValue(form_el.groups_denyipmove_guest);
			this._setValue(form_el.groups_denyipmove_guest, "0");
			this._setValue(form_el.groups_denyipmove, "5|4|3");
//			this._setValue(form_el.passwd_disabling_bip, "");
		// セキュリティ：中
		} else if (form_el.security_level.value == "1") {
			this._setValue(form_el.log_level, "63");
			this._setValue(form_el.san_nullbyte, "1");
			this._setValue(form_el.contami_action, "0");
			this._setValue(form_el.isocom_action, "0");
			this._setValue(form_el.union_action, "0");
			this._setValue(form_el.file_dotdot, "0");
			this._setValue(form_el.dos_expire, "60");
			this._setValue(form_el.dos_f5count, "20");
			this._setValue(form_el.dos_f5action, "0");
//			this._setValue(form_el.reliable_ips, "");
			this._setValue(form_el.dos_crcount, "60");
			this._setValue(form_el.dos_craction, "0");
//			this._setValue(form_el.dos_crsafe, "/(msnbot|Googlebot|Yahoo! Slurp)/i");
			this._setValue(form_el.bip_except_admin);
			this._setValue(form_el.bip_except_admin, "1");
			this._setValue(form_el.bip_except_chief);
			this._setValue(form_el.bip_except_chief, "1");
			this._setValue(form_el.bip_except_moderate);
			this._setValue(form_el.bip_except_moderate, "1");
			this._setValue(form_el.bip_except_general);
			this._setValue(form_el.bip_except_general, "0");
			this._setValue(form_el.bip_except_guest);
			this._setValue(form_el.bip_except_guest, "0");
			this._setValue(form_el.bip_except, "5|4|3");
			this._setValue(form_el.enable_badips, "0");
//			this._setValue(form_el.bad_ips, "127.0.0.1");
			this._setValue(form_el.censor_enable, "0");
//			this._setValue(form_el.censor_words, "fuck|shit");
//			this._setValue(form_el.censor_replace, "#OOPS#");
//			this._setValue(form_el.extension, "1");
//			this._setValue(form_el.allow_extension, "csv,hqx,doc,dot,bin,lha,lzh,class,so,dll,pdf,ai,eps,ps,smi,smil,wbxml,wmlc,wmlsc,xla,xls,xlt,ppt,csh,dcr,dir,dxr,spl,gtar,sh,swf,sit,tar,tcl,xht,xhtml,ent,dtd,mod,gz,tgz,zip,au,snd,mid,midi,kar,mp1,mp2,mp3,aif,aiff,m3u,ram,rm,rpm,ra,wav,bmp,gif,jpeg,jpg,jpe,png,tiff,tif,wbmp,pnm,pbm,pgm,ppm,xbm,xpm,ics,ifb,css,html,htm,asc,txt,rtf,sgml,sgm,tsv,wml,wmls,xsl,mpeg,mpg,mpe,qt,mov,avi,wmv,asf,tex,dvi");
//			this._setValue(form_el.deny_extension, "");
			this._setValue(form_el.groups_denyipmove_admin);
			this._setValue(form_el.groups_denyipmove_admin, "1");
			this._setValue(form_el.groups_denyipmove_chief);
			this._setValue(form_el.groups_denyipmove_chief, "0");
			this._setValue(form_el.groups_denyipmove_moderate);
			this._setValue(form_el.groups_denyipmove_moderate, "0");
			this._setValue(form_el.groups_denyipmove_general);
			this._setValue(form_el.groups_denyipmove_general, "0");
			this._setValue(form_el.groups_denyipmove_guest);
			this._setValue(form_el.groups_denyipmove_guest, "0");
			this._setValue(form_el.groups_denyipmove, "5");
//			this._setValue(form_el.passwd_disabling_bip, "");
		// セキュリティ：カスタマイズ
		} else {
		}
	},
	// セキュリティレベルを「セキュリティチェックなし」にする
	securityChgNone: function(form_el) {
		this._setValue(form_el.security_level, "0");
		this.chgSecurity(form_el);
	},
	// セキュリティレベルを「中」にする
	securityChgMedium: function(form_el) {
		this._setValue(form_el.security_level, "1");
		this.chgSecurity(form_el);
	},
	// セキュリティレベルを「カスタマイズ」にする
	securityChgCustom: function(form_el) {
		this._setValue(form_el.security_level, "3");
	},
	//一般画面初期処理
	securityInit: function() {
	},
	// セキュリティ設定を適用する
	setSecurityConfig: function(view_name, msg) {
		var top_el = $(this.id);
		var form_el = $("form" + this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		var action_name = this._getConfigAction(view_name);
		action_params["param"] = "action=" + action_name + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["callbackfunc"] = function(res) {
//			commonCls.sendView(this.id, view_name);
			commonCls.alert(msg);
		}.bind(this);
		action_params["callbackfunc_error"] = function(res) {
//			commonCls.sendView(this.id, view_name);
			commonCls.alert(res);
		}.bind(this);
		commonCls.send(action_params);
	},
	_getConfigAction: function(action_name) {
		return action_name.gsub('^security_view_main_', 'security_action_');
	},
	// テーブルのコピーを作成する,削除する
	securityPrefixmanager: function(view_name, action_name, msg, db_prefix_old) {
		var top_el = $(this.id);
		var form_el = $("form" + this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		action_params["param"] = "action=" + action_name + "&"+ "db_prefix_old=" + db_prefix_old + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, view_name);
			commonCls.alert(msg);
		}.bind(this);
		action_params["callbackfunc_error"] = function(res) {
			commonCls.sendView(this.id, view_name);
			commonCls.alert(res);
		}.bind(this);
		commonCls.send(action_params);
	},
	// ログを削除する
	securityDisplaylog: function(view_name, msg) {
		var top_el = $(this.id);
		var form_el = $("form" + this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		var action_name = this._getConfigAction(view_name);
		action_params["param"] = "action=" + action_name + "&"+ Form.serialize(form_el);
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, view_name);
			commonCls.alert(msg);
		}.bind(this);
		action_params["callbackfunc_error"] = function(res) {
			commonCls.sendView(this.id, view_name);
			commonCls.alert(res);
		}.bind(this);
		commonCls.send(action_params);
	},
	// すべてのログをチェックする
	securityDisplaylogCheckAll: function(form_el, displaylog_count) {
		for (var i=0;i<displaylog_count;i++) {
			form_el["displaylog_lids[" + i+ "]"].checked = true;
		}
	}
}
