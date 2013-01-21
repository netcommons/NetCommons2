var clsChat = Class.create();
var chatCls = Array();

clsChat.prototype = {
	initialize: function(id) {
		this.id = id;
		this.block_id = null;
		this.chat_id = null;
		this.login = null;
		this.reload = null;
		this.status = null;
		this.line_num = null;
		this.display_type = null;
		this.login_user = null;
		this.logout_user = null;
		this.timerId = null;
		this.inSubmit = false;
		
		//メッセージ
		this._message_user = null;
		this._message_user_item = null;
		this._message_login = null;
		this._message_logout = null;
	},	
	openWindow: function(src, height, width) {
		chat_window = window.open(src, "chat" + this.id, "height=" + (parseInt(height) + 61) + ",width=" + width + ",resizable=yes");
		this.stopTimer();
		chat_window.document.bgColor = "#ffffff";
		chat_window.focus();
	},
	editChat: function(form_el) {
		commonCls.sendPost(this.id, "action=chat_action_edit_modify" + "&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},	
	chatCancel: function() {
		commonCls.sendView(this.id,"chat_view_main_init");
	},	
	initChat: function(block_id, reload, status, line_num, display_type) {
		this.block_id = block_id;
		this.reload = reload;
		this.status = status;
		this.line_num = line_num;
		this.display_type = display_type;
		this.chat_id = "";
		
		this.timerId = setInterval('chatCls["'+this.id+'"].reloadChat()', this.reload * 1000);
		this.reloadChat();
	},
	chatSubmit: function() {
		this.inSubmit = true;
		var top_el = $("chat_form" + this.id);
		var val_el = Element.getChildElementByClassName(top_el, "chat_text");
		var chat_text = val_el ? val_el.value : null;
		var params = new Object();
		params["method"] = "post";
		params["top_el"] = top_el;
		params['callbackfunc'] = this.sendCallBack.bind(this);
		params["param"] = {
			"action":"chat_action_main_post",
			"block_id":this.block_id,
			"chat_text":chat_text,
			"chat_id":this.chat_id,
			"line_num":this.line_num
		};
		commonCls.send(params);
		val_el.value = "";
	},
	reloadChat: function() {
		this.inSubmit = true;
		var top_el = $("chat_contents"+this.id);
		if(!top_el)	{
			this.stopTimer();
			return false;
		}
		var send_params = new Object();
		send_params["method"] = "post";
		send_params["top_el"] = top_el;
		send_params['callbackfunc'] = this.sendCallBack.bind(this);
		send_params['callbackfunc_error'] = function(res){
												this.stopTimer();
												commonCls.alert(res);
											}.bind(this);
		
		send_params["param"] = {
			"action":"chat_action_main_content",
			"block_id":this.block_id,
			"chat_id":this.chat_id,
			"line_num":this.line_num
		};
		commonCls.send(send_params);
	},
	sendCallBack: function(res) {
		var work = document.createElement('div');
		work.innerHTML = res;
		this.status = work.firstChild.innerHTML;
		if(this.status != 1) {
			parent.window.close();
			work = null;
			return false;
		}
		work.removeChild(work.firstChild);
		var win = work.firstChild.innerHTML;
		work.removeChild(work.firstChild);
		this.reload = work.firstChild.innerHTML;
		work.removeChild(work.firstChild);
		var work_login = work.firstChild.innerHTML;
		this.logout_user = "";
		this.login_user = "";
		if (work_login != this.login) {
			this.getChatUserAction(this.login, work_login);
			this.login = work_login;
		}
		this.showAllUsers(false);
		work.removeChild(work.firstChild);
		var work_id = work.firstChild.innerHTML;
		work.removeChild(work.firstChild);
		this.line_num = work.firstChild.innerHTML;
		work.removeChild(work.firstChild);
		var chat_el = $("chat_contents"+this.id);
		if(this.logout_user) {
			this.printUserLogoutAction(chat_el);
		}

		if(this.login_user) {
			this.printUserLoginAction(chat_el);
		}
		
		if(work_id) {
			this.chat_id = work_id;	
			while (work.firstChild) {
				chat_el.insertBefore(work.lastChild, chat_el.firstChild);
			}
			var lines = chat_el.getElementsByClassName("chat_content");
			var count = lines.length;
			while (count > this.line_num) {
				chat_el.removeChild(chat_el.lastChild);
				--count;
			}
		}
		this.inSubmit = false;
		work = null;
	},
	clearChat: function() {
		var top_el = $(this.id);
		var params = new Object();
		params["method"] = "post";
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params['callbackfunc'] = this.clearChatCallBack.bind(this);
		params["param"] = {
			"action":"chat_action_main_clear",
			"block_id":this.block_id,
			"chat_id":this.chat_id
		};
		commonCls.send(params);
	},
	clearChatCallBack: function() {
		var chat_el = $("chat_contents"+this.id);
		var lines = chat_el.getElementsByClassName("chat_content");
		var count = lines.length;
		while (count > 0) {
			chat_el.removeChild(chat_el.lastChild);
			--count;
		}
	},
	getChatUserAction: function(old_user, new_user) {
		var ret = Array();
		var old_login = new Array();
		var new_login = new Array();
		if(old_user) {
			if(old_user.indexOf(";|")) {
				old_login = old_user.split(";|");
			}else {
				old_login.push(old_user);
			}
		}
		if(new_user) {
			if(new_user.indexOf(";|")) {
				new_login = new_user.split(";|");
			}else {
				new_login.push(new_user);
			}
			new_login = new_user.split(";|");
		}
		var arr = old_login.concat(new_login);
		for(var i=0;i<arr.length;i++) {
        	var b=true;
        	for(var j=0;j<arr.length;j++){
                if(i!=j&&arr[i]==arr[j]) {
                	b=false;
                }
        	}
        	if(b) {
        		ret.push(arr[i]);
        	}
		}
		
		if(old_user) {
			for(var i=0;i<ret.length;i++) {
				for(var j=0;j<old_login.length;j++) {
					if(ret[i] == old_login[j]) {
						this.logout_user += ret[i] + this._message_logout + "<br />";
					}
				}
				for(var m=0;m<new_login.length;m++) {
					if(ret[i] == new_login[m]) {
						this.login_user += ret[i] + this._message_login + "<br />";
					}
				}
			}
		}
	},
	printUserLoginAction: function(div) {
		var user = document.createElement('div');
		Element.setStyle(user,{"color":"blue","font-size":"120%"});
		user.innerHTML = this.login_user;
		var hrdiv = document.createElement('div');
		Element.addClassName(hrdiv,"hr");
		div.insertBefore(hrdiv, div.firstChild);
		div.insertBefore(user, div.firstChild);
		hrdiv = null;
		user = null;
	},
	printUserLogoutAction: function(div) {
		var user = document.createElement('div');
		Element.setStyle(user,{"color":"blue","font-size":"120%"});
		user.innerHTML = this.logout_user;
		var hrdiv = document.createElement('div');
		Element.addClassName(hrdiv,"hr");
		div.insertBefore(hrdiv, div.firstChild);
		div.insertBefore(user, div.firstChild);
		hrdiv = null;
		user = null;
	},
	showAllUsers: function(flag) {
		var chat_user = $("chat_user" + this.id);
		var work_login_user = new Array();
		if(this.login != "" && this.login != null) {
			work_login_user = this.login.split(";|");
		}
		var work_print = "";
		for(var i=0;i<work_login_user.length;i++) {
			if(i<work_login_user.length-1) {
				work_print += work_login_user[i] + ",";
			}else if(i==10 && !flag) {
				work_print += "&nbsp;<a href='#' onclick='chatCls["+this.id+"].showAllUsers(true)'>....</a>";
				break;
			}else {
				work_print += work_login_user[i];	
			}
		}
		chat_user.innerHTML = this._message_user + "(" + work_login_user.length + this._message_user_item +")" + work_print;
		if(flag) {
			chat_user.innerHTML += "<br />" + "&nbsp;<a href='#' onclick='chatCls["+this.id+"].showAllUsers(false)'>back</a>";
		}
	},
	stopTimer: function() {
		clearInterval(this.timerId);
	}
}