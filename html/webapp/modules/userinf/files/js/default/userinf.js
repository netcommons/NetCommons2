var clsUserinf = Class.create();
var userinfCls = Array();

clsUserinf.prototype = {
	initialize: function(id, user_id, prefix_id_name) {
		this.id = id;
		this.user_id = user_id;
		this.prefix_id_name = prefix_id_name;
		
		this.inEmailReception = new Object();
		this.inItems = new Object();
		this.inPublic = new Object();
		this.inUpdItems = new Object();
	},
	init:function() {
		var top_el = $(this.id);
		var focus_el = Element.getChildElementByClassName(top_el,"userinf_edit_item_label");
		commonCls.focus(focus_el);
	},
	/* 会員詳細-ラベルクリック時に編集可能であればclkItemsを呼び出す */
	clkLabel: function(this_el) {
		var edit_el = this_el.nextSibling;
		var a_el = Element.getChildElement(edit_el);
		if(a_el && a_el.tagName.toLowerCase() == "a") {
			a_el.onclick();
		}
	},
	/* 会員詳細-会員情報クリック処理 */
	clkItems: function(this_el, item_id, type) {
		var edit_el = this_el.nextSibling;
		commonCls.displayNone(this_el);
		commonCls.displayVisible(edit_el);
		switch (type) {
		    case "text": // Text
		    case "textarea": // TextArea
		    case "email": // Email
		    case "mobile_email":
		    	var input_el = Element.getChildElement(edit_el);
		    	input_el.focus();
		    	input_el.select();
				break;
		    case "file": // File
		    	var input_el = Element.getChildElement(edit_el, 2);
		    	input_el.focus();
		    	input_el.select();
				break;
			case "password": // Password
				var input_el = edit_el.getElementsByTagName("input")[0];
				input_el.focus();
		    	input_el.select();
				break;
			case "select": 		// Select
			case "radio": 		// Radio
			case "checkbox": 	// Checkbox
				var form_el = edit_el.getElementsByTagName("form")[0];
				if(type == "select") {
					var input_els = Form.getElements(form_el);
				} else {
					var input_els = Form.getInputs(form_el, type);
				}
				input_els[0].focus();
				break;
		}
	},
	// email-受信可否フラグのフォーカス移動では、更新処理を行わせないように制御するため
	focusReception: function(item_id, focus_flag) {
		this.inEmailReception[item_id] = focus_flag;
	},
	// 公開設定フラグのフォーカス移動では、更新処理を行わせないように制御するため
	focusPublic: function(item_id, focus_flag) {
		this.inPublic[item_id] = focus_flag;
	},
	// メインアイテムのフォーカス移動では、更新処理を行わせないように制御するため
	focusItem: function(item_id, focus_flag) {
		this.inItems[item_id] = focus_flag;
	},
	/* 会員詳細-会員情報更新処理 */
	updItems: function(event_type, this_el, item_id, type, focus_flag) {
		var input_type = this_el.type;
    	//var event_keycode = event.keyCode;	
		var reception_el = $("userinf_items_reception" + this.id + "_" + item_id);
		var public_el = $("userinf_items_public" + this.id + "_" + item_id);
		if(input_type == "text" && event_type == "keypress") {
			this_el.blur();
		}
		if((reception_el && !Element.hasClassName(reception_el.parentNode,"display-none")) || 
			(public_el && !Element.hasClassName(public_el.parentNode,"display-none"))) {
			// メールを受け取るにフォーカスがある場合、戻さない
			if(focus_flag == undefined) {
				setTimeout(function(){this.updItems(event_type, this_el, item_id, type, true)}.bindAsEventListener(this), 500);
				return;
			} else {
				if(this.inItems[item_id] === true || this.inEmailReception[item_id] === true || this.inPublic[item_id] === true) {				
					setTimeout(function(){this.updItems(event_type, this_el, item_id, type, true)}.bindAsEventListener(this), 500);
					return;
				}
			}
		} else if(type == "radio" && event_type == "blur" && focus_flag == undefined) {
			return;
		}
		
		var top_el = $(this.id);
		if(top_el == null) return;		//Xボタン押下されている
		
		var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
		var label_el = edit_el.previousSibling;
		var select_flag = false;
		
		var upd_params = new Object();
		if(type != "file") {
			upd_params['action'] = "userinf_action_main_init";
		} else {
			upd_params['action'] = "userinf_action_main_upload_image";
		}
		upd_params['item_id'] = item_id;
		upd_params['user_id'] = this.user_id;
		if(this.inUpdItems[upd_params['item_id']] == true) {
			// 更新中
			return;
		}
		this.inUpdItems[upd_params['item_id']] = true;
		
		switch (type) {
		    case "text": // Text
		    case "email": // Email
		    case "mobile_email":
		    	upd_params['content'] = this_el.value;
		    	if(reception_el) {
		    		upd_params['email_reception_flag'] = (reception_el.checked) ? 1 : 0;
		    	} else {
		    		if(type == "text") {
		    			upd_params['email_reception_flag'] = 0;
		    		} else {
		    			upd_params['email_reception_flag'] = 1;
		    		}
		    	}
		    	select_flag = true;
		    	break;
		    case "textarea": // TextArea
		    	var textarea_el = $("userinf_items" + this.id + "_" + upd_params['item_id']);
		    	upd_params['content'] = textarea_el.value;
		    	this_el = textarea_el;
		    	select_flag = true;
				break;
			case "password": // Password
				var current_el = $("userinf_items_current" + this.id + "_" + upd_params['item_id']);
				var new_el = $("userinf_items_new" + this.id + "_"  + upd_params['item_id']);
				var comfirm_el = $("userinf_items_confirm" + this.id + "_" + upd_params['item_id']);
				upd_params['content'] = new_el.value;
				if(comfirm_el) upd_params['confirm_content'] = comfirm_el.value;
				if(current_el) {
					upd_params['current_content'] = current_el.value;
					this_el = current_el;
				} else {
					this_el = new_el;
				}
				select_flag = true;
				break;
			case "file": // File
				upd_params['unique_id'] = this.user_id;
				//var upload_el = $("userinf_items" + this.id + "_" + upd_params['item_id']);
				//upd_params['content'] = upload_el.value;
				//this_el = upload_el;
				break;
			case "select": 		// Select
			case "radio": 		// Radio
			case "checkbox": 	// Checkbox
				var form_el = $("userinf_form" + this.id + "_" + item_id);
				if(type == "select") {
					var input_els = Form.getElements(form_el);
				} else {
					var input_els = Form.getInputs(form_el, type);
				}
				var value_lang = "";
				upd_params['content'] = "";
				for (var i = 0, length = input_els.length; i < length; i++) {
					////upd_params[input_els[i].name] = Form.Element.getValue(input_els[i]);
					if(i == 0) {
						this_el = input_els[i];
					}
					var value = Form.Element.getValue(input_els[i]);
					if(type == "select") {
						var checked_flag = true;
					} else {
						var checked_flag = input_els[i].checked;
					}
					if(value != null && checked_flag) {
						var value_arr = value.split("|");
						if(value_arr[1]) {
							upd_params['content'] += value_arr[0] + "|";
							value_lang += value_arr[1] + "|";
						} else {
							upd_params['content'] += value + "|";
						}
					}
				} 
				break;
		}
		if(public_el) {
    		upd_params['public_flag'] = (public_el.checked) ? 1 : 0;
    	} else {
    		upd_params['public_flag'] = 1;
    	}
    	upd_params['prefix_id_name'] = this.prefix_id_name;
    	
		var send_param = new Object();
		send_param["method"] = "post";
		send_param["param"] = upd_params;
		send_param["top_el"] = top_el;
		send_param["callbackfunc"] = function(file_list, res){
			// 正常終了
			if(type == "text" || type == "email" || type == "mobile_email" || type == "textarea" ||
				type == "select" || type == "radio" || type == "checkbox") {
				upd_params['content'] = upd_params['content'].escapeHTML();
			}
			if(upd_params['content'] == "") upd_params['content'] = "&nbsp;"; 
			switch (type) {
			    case "text": // Text
			    case "email": // Email
			    case "mobile_email":
			    	label_el.innerHTML = upd_params['content'];
			    	break;
				case "textarea": // TextArea
					var re_cut = new RegExp("\n", "g");
				 	label_el.innerHTML = upd_params['content'].replace(re_cut, "<br />");
				 	break;
				case "password": // Password
					if(current_el) current_el.value = upd_params['content'];
					if(comfirm_el) comfirm_el.value = "";
					new_el.value = "";
					break;
				case "file": // File
					var url = "?action="+ file_list[0]['action_name'] + "&upload_id=" + file_list[0]['upload_id'];
					var img_el = Element.getChildElement(label_el);
					img_el.src = url;
					commonCls.displayVisible(img_el);
					break;
				case "select": 		// Select
				case "radio": 		// Radio
				case "checkbox": 	// Checkbox
					var re_sep = new RegExp("\\|", "g");
					if(value_lang != "") {
						var content_str = value_lang.replace(re_sep,",").substr(0,value_lang.length - 1);
					} else {
						var content_str = upd_params['content'].replace(re_sep,",").substr(0,upd_params['content'].length - 1);
					}
					label_el.innerHTML =content_str;
					break;
			}
			commonCls.displayNone(edit_el);
			commonCls.displayVisible(label_el);
			if(input_type == "radio" || input_type == "select-one"
				 || input_type == "button") {
				label_el.focus();
			}
			this.inUpdItems[upd_params['item_id']] = false;
		}.bind(this);
		if(type == "file") {
			send_param["param"]['unique_id'] = this.user_id;
			send_param['form_prefix'] = "userinf_attachment_"+ upd_params['item_id'];
			send_param["callbackfunc_error"] = function(file_list, res){
				// エラー時(File)
				commonCls.alert(res);
				this_el.focus();
				if(select_flag) this_el.select();
				this.inUpdItems[upd_params['item_id']] = false;
			}.bind(this);
			commonCls.sendAttachment(send_param);
		} else {
			send_param["callbackfunc_error"] = function(res){
				// エラー(File以外)
				commonCls.alert(res);
				this_el.focus();
				if(select_flag) this_el.select();
				this.inUpdItems[upd_params['item_id']] = false;
			}.bind(this);
			commonCls.send(send_param);
		}
	},
	cancelItems: function(event, this_el, type) {
		var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
		var label_el = edit_el.previousSibling;
		commonCls.displayNone(edit_el);
		commonCls.displayVisible(label_el);
		label_el.focus();
	},
	delImage: function(event, this_el, type) {
		var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
		var label_el = edit_el.previousSibling;
		var img_el = Element.getChildElement(label_el);
		Element.addClassName(img_el, "display-none");
		img_el.src = "";
		commonCls.displayNone(edit_el);
		commonCls.displayVisible(label_el);
		label_el.focus();
	},
		
	/* 参加ルーム */
	initRoom: function(count, visible_rows) {
		//階層で取得しているため、ソートさせない
		//var opts = {
		//	sort : true
		//};
		var opts = null;
		new compLiveGrid (this.id, visible_rows, count, null, opts);
	},
	/* アクセス状況 */
	initMonthly: function() {
		
		var offset = 400;	// 固定
		var monthlynumber_list_el = $("monthlynumber_list" + this.id);
		var height = monthlynumber_list_el.offsetHeight;
		if(height >= offset) {
			Element.setStyle(monthlynumber_list_el, {overflow:"auto"});
			Element.setStyle(monthlynumber_list_el, {height:offset+"px"});
			if(!browser.isIE) {
				Element.setStyle(monthlynumber_list_el, {width:monthlynumber_list_el.offsetWidth+"px"});
			}
			var parent_el = monthlynumber_list_el.parentNode;
			if(browser.isIE) {
				Element.setStyle(parent_el, {"padding-right":"20px"});
			}
		}
		//Element.removeClassName(monthlynumber_list_el,"visible-hide");
	},
	/* レポート */
	initModulesinfo: function() {
		var top_el = $(this.id);
		var tabset = new compTabset(top_el);
		tabset.render();
	},
	/* 以上の内容を了解する */
	withdrawAccept: function(this_el) {
		var next_btn_el = $("userinf_next" + this.id);
		if(this_el.checked) {
			next_btn_el.disabled = false;
		} else {
			next_btn_el.disabled = true;
		}
	}
}