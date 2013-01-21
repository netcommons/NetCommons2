var clsLogin = Class.create();
var loginCls = Array();

clsLogin.prototype = {
	initialize: function(id) {
		this.id = id;
		this.sslIframeSrc = null;
		this.loginIdValue = null;
	},

	showLogin: function(event) {
		commonCls.displayVisible($('login_popup'));
		commonCls.sendPopupView(event, {'action':'login_view_main_init'}, {'center_flag':true});

		var sslIframe = $('login_ssl_iframe' + this.id);
		if (sslIframe == null) {
			this.initializeFocus();
			return;
		}
		
		if (this.sslIframeSrc != null) {
			sslIframe.focus();
			sslIframe.src = this.sslIframeSrc;
		}
	},

	initializeFocus: function(errorCount) {
		try {
			var formElement = $('login_form' + this.id);
			var loginIdElement = formElement['login_id'];
			loginIdElement.focus();
			loginIdElement.select();

			if (browser.isIE) {
				loginIdElement.fireEvent('ondblclick');
				loginIdElement.fireEvent('onblur');
			}
		}catch(e){
			if (errorCount < 5) {
				errorCount++;
				setTimeout(function(){this.initializeFocus(errorCount);}.bind(this), 300);
			}
		}
	},

	setButtonStyle: function(element) {
		if (element == null) {
			return;
		}
		var styleValue = "border-radius:4px;-webkit-border-radius:4px;-moz-border-radius:4px;";
		element.setAttribute('style', styleValue);
	},

	loginLogout: function(event) {
		var load_el = Event.element(event);
		//パラメータ取得
		var logout_params = new Object();
		var top_el = $(this.id);
		
		logout_params["method"] = "post";
		logout_params["param"] = {"action":"login_action_main_logout"};
		logout_params["loading_el"] = load_el;

		commonCls.send(logout_params);
	},

	insAutoregist: function (form_el) {
		var reg_params = new Object();
		reg_params["param"] = {'action': "login_action_main_autoregist"};
		reg_params["form_el"] = form_el;
		reg_params["top_el"] = $(this.id);
		reg_params["target_el"] = $("target"+this.id);
		reg_params["method"] = "post";
		reg_params['form_prefix'] = "login_attachment";
		reg_params["callbackfunc_error"] = function(file_list, res){
			// エラー時(File)
			this.focusError(res);
		}.bind(this);
		commonCls.sendAttachment(reg_params);
	},
	focusError: function(res) {
		res = commonCls.cutErrorMes(res);
		if(res.match(":")) {
			var mesArr = res.split(":");
			var alert_res = "";
			for(var i = 1; i < mesArr.length; i++) {
				alert_res += mesArr[i];
			}
			// チェックボックス等の場合、うまく動作しないかも
			var focus_el = $("login_items"+ this.id + "_" + mesArr[0]);
			if(focus_el) {
				commonCls.alert(alert_res);
				commonCls.focus(focus_el);
			} else {
				commonCls.alert(res);
			}
		} else {
			commonCls.alert(res);
		}
	}
}