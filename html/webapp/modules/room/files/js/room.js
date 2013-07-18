var clsRoom = Class.create();
var roomCls = Array();

clsRoom.prototype = {
	initialize: function(id) {
		this.id = id;
		this.usersGrid = null;
		
		this.select_auth = -1;	//全選択用
		this.send_count = 0;
	},
	/* ルーム一覧 */
	initList: function(visible_rows, count) {
		this.select_auth = -1;	//全選択用
		var opts = null;
		new compLiveGrid (this.id, visible_rows, count, null, opts);
		commonCls.moveAutoPosition($(this.id).parentNode);
		var room_create_el = $("room_create"+this.id);
		if(room_create_el) commonCls.focus(room_create_el);
	},
	/* 参加者選択 */
	initSelectUsers: function(count, visible_rows, parent_page_id, edit_current_page_id) {
		if(count == 0) return;
		var opts = {
				prefetchBuffer : true,
				requestParameters : new Array("parent_page_id="+parent_page_id, "edit_current_page_id="+edit_current_page_id)
			};
		
		
		//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
		this.usersGrid = new compLiveGrid (this.id, visible_rows, count, "room_view_admin_regist_userslist", opts);
		if(this.select_auth != -1) {
			this.usersGrid.options.requestParameters[2] = 'selected_auth_id='+this.select_auth;
		}
		commonCls.moveAutoPosition($(this.id).parentNode);
	},
	/* 登録内容確認 */
	initRegistConfirm: function(count, visible_rows, edit_current_page_id) {
		this.select_auth = -1;	//全選択用
		var top_el = $(this.id);
		
		if(count != 0) {
			var opts = {
				prefetchBuffer : true,
				requestParameters : new Array("xml_flag=1", "edit_current_page_id="+edit_current_page_id)
			};
			
			//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
			new compLiveGrid (this.id, visible_rows, count, "room_view_admin_regist_confirm", opts);
		}
		
		var tabset = new compTabset(top_el);
		tabset.render();
		
		commonCls.moveAutoPosition($(this.id).parentNode);
	},
	/* ルーム名称変更 */
	roomNameChange: function(this_el, parent_page_id, edit_current_page_id) {
		var input_next =  $("room_next" + this.id);
		var errorstr_el=  $("room_errorstr" + this.id);
		if(errorstr_el.innerHTML != "") {
			errorstr_el.innerHTML = "";
		}
		if(this_el.value != "") {
			input_next.disabled = false;
		} else {
			input_next.disabled = true;
		}
	},
	roomNameCheck: function(id, parent_page_id, edit_current_page_id) {
		//
		// 同名チェック
		//
		var form_el =  $("form" + this.id);
		var input_next =  $("room_next" + this.id);
		var errorstr_el=  $("room_errorstr" + this.id);
		var parameter = new Object();
		parameter["callbackfunc_error"] = function(res){
													errorstr_el.innerHTML = res;
													input_next.focus();
													input_next.select();
													input_next.disabled = true;
												}.bind(this);
		commonCls.sendView(this.id,'action=room_view_admin_regist_selectusers&'+Form.serialize(form_el),parameter);
	},
	/* 参加会員選択-全選択クリック */
	selAuth: function(auth_id, form_el) {
		this.select_auth = auth_id;
		this.usersGrid.options.requestParameters[2] = 'selected_auth_id='+auth_id;
		if(form_el!=undefined) {
			var inputList = form_el.getElementsByTagName("input");
			for (var i = 0; i < inputList.length; i++) {
				if(inputList[i] && inputList[i].type == "checkbox" && inputList[i].disabled == false) {
					var child_el = Element.getParentElement(inputList[i], 3);
					Element.removeClassName(child_el, "room_active_auth_id");
					inputList[i].checked=false;
				}
			}	
		}
	},
	/* 検索実行-会員絞込み */
	searchUser: function(popup_id) {
		var top_el = $(this.id);
		var parent_form_el =  $("form" + this.id);
		var popup_el = $(popup_id);
		var user_search_main_el = Element.getChildElementByClassName(popup_el, "user_search_main");
		var form_el = $("form" + popup_id);
		var search_params = new Object();
		search_params["method"] = "post";
		search_params["param"] = "action=room_action_admin_search&"+ Form.serialize(form_el);
		search_params["top_el"] = top_el;
		search_params["loading_el"] = top_el;
		search_params["callbackfunc"] = function(res){
											this.select_auth = -1;
											commonCls.removeBlock(popup_el);
											commonCls.sendView(this.id, commonCls.getUrl(top_el).parseQuery());
										}.bind(this);
		commonCls.send(search_params);
	},
	/* 確認画面表示 																	  */
	/* 会員数が何人いても問題なく動作するようにsendをいくつかに分けてセッションに登録後、 */
	/* 確認画面表示	(セッションテーブルに入りきれなくなった時が限界)    				  */
	showConfirm: function(action_name, parent_page_id, current_page_id, users_regist_once, subgroup_flag) {
		// Loading
		commonCls.showLoading(this.id, null, null, null, $(this.id));
		if(current_page_id == undefined) current_page_id = "0";
		var form_el =  $("form" + this.id);
    	var inputs = form_el.getElementsByTagName('input');
    	
    	var regist_params = new Object();
    	regist_params["method"] = "post";
    	regist_params["top_el"] = $(this.id);
    	regist_params["param"] = new Object();
    	regist_params["param"]['action'] = "room_action_admin_regist_users";
    	regist_params["param"]['edit_current_page_id'] = current_page_id;
    	
		this.send_count = 0;
		var regist_flag = false;	
		for (var i = 0, length = inputs.length,count = 0; i < length; i++) {
			var input = inputs[i];
			if (input.type == "radio") {
				var key = input.name, value = Form.Element.getValue(input);
				if (value != null) {
					// user_idの取得
					var user_id_arr = key.match(/^room_authority\[\'(.+)\'\]/i);
					if(user_id_arr != null) {
						// サブグループ作成フラグ
						var user_id = user_id_arr[1];
						var createroom_flag_el = $("create_room_flag" + user_id +"_"+ this.id);
						if(createroom_flag_el && createroom_flag_el.checked) {
							regist_params["param"]["room_createroom_flag["+user_id+"]"] = "1";
						}
						if(!regist_params["param"]["room_authority"]) {
							regist_params["param"]["room_authority"] = new Array();
						}
						regist_params["param"]["room_authority"][user_id] = value;
					}
					regist_flag = true;
					count++;
				}
			}
			if(regist_flag && (count > users_regist_once - 1 || i == length - 1)) {
				// send
				regist_params["callbackfunc"] = function(res) {
					this.send_count--;
				}.bind(this);
				this.send_count++;
				commonCls.send(regist_params);
				// 初期化
				count = 0;
				regist_flag = false;
				regist_params["param"] = new Object();
				regist_params["top_el_id"] = null;
				regist_params["url"] = null;
    			regist_params["param"]['action'] = "room_action_admin_regist_users";
			}
		}
		// 登録処理が終わるまでSleep
		setTimeout(function(){this.showConfirmComp(action_name, parent_page_id, current_page_id, subgroup_flag);}.bind(this), 0);
	},
	showConfirmComp: function(action_name, parent_page_id, current_page_id, subgroup_flag) {
		
		if(this.send_count != 0) {
			setTimeout(function(){this.showConfirmComp(action_name, parent_page_id, current_page_id, subgroup_flag);}.bind(this), 200);
			return;
		}
		var continue_flag = false;
		if(action_name == "room_action_admin_regist_continue") {
			action_name = "room_action_admin_regist_selectusers";
			continue_flag = true;
		}
		var parameter = null;
		if(action_name == "room_action_admin_regist_selectusers") {
			parameter = {'method':'post'};
		}
		var sendParam = {'action':action_name,'parent_page_id':parent_page_id,'edit_current_page_id':current_page_id};
		if(this.select_auth != -1) {
			sendParam['selected_auth_id'] = this.select_auth;
		}
		if(subgroup_flag != undefined || subgroup_flag != null) sendParam['subgroup_flag'] = 1;
		if(continue_flag) sendParam['continue_flag'] = 1;
		commonCls.sendView(this.id, sendParam, parameter);		
		// Loading Hide
		commonCls.hideLoading(this.id);
	},
	chkRadioSelectauth: function(check_el) {
		if(check_el.disabled != true) {
			check_el.checked = true;
		}
		var td_el = Element.getParentElement(check_el, 3);
		var tr_el = Element.getParentElement(td_el);
		if(check_el.type == "radio") {
			var tdList = tr_el.getElementsByTagName("td");
			for (var i = 0; i < tdList.length; i++) {
				var child_el = Element.getChildElement(tdList[i], 3);
				if(child_el && child_el.type == "radio") {
					Element.removeClassName(tdList[i], "room_active_auth_id");
				}
			}
		}
		if(check_el.checked) {
			Element.addClassName(td_el, "room_active_auth_id");
		} else {
			Element.removeClassName(td_el, "room_active_auth_id");
		}
	},
	chkCheckboxCreateroom: function(check_el) {
		var td_el = Element.getParentElement(check_el, 3);
		if(check_el.checked) {
			Element.addClassName(td_el, "room_active_auth_id");
		} else {
			Element.removeClassName(td_el, "room_active_auth_id");
		}
	},
	changeLanguage: function(select_lang,default_entry_flag,space_type,private_flag) {
		var params = {
			'action':'room_view_admin_list',
			'show_default_entry_flag':default_entry_flag,
			'show_space_type':space_type,
			'show_private_flag':private_flag,
			'lang':select_lang
		}
		commonCls.sendView(this.id, params);
	},
	roomUsersExport: function(event, parent_page_id, edit_current_page_id, confirm_mes) {

        var top_el = $(this.id);

		var params = {
			'action':'room_action_admin_export',
			'parent_page_id':parent_page_id,
			'edit_current_page_id':edit_current_page_id
		}
		var parameter = new Object();
		parameter["callbackfunc"] = function(res){
			if(!commonCls.confirm(confirm_mes)) 
			{
				return false;
			}
			location.href= '.' + _nc_index_file_name + '?action=room_view_admin_export&_token='+ $('_token' +this.top_el_id).value;
		}
		parameter["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
		}
		commonCls.sendPost(this.id, params, parameter);
	},
	_isSelectFile: function(frm) {
		if($F("room_import_file_select").length > 0) {
			return(true);
		}
		return(false);
	},
	_alertImport: function(confirm_message, target) {
		var keyword = $(target).textContent;
		var newmsg = confirm_message.replace('%error_item%', keyword);
		commonCls.alert(newmsg);
		return;
	},
	doUserImport: function(frm, confirm_message) {
		var keyword;
		if(!this._isSelectFile(frm)) {
			this._alertImport(confirm_message, 'room_import_label_file');
			return false;
		}

		var this_el = $("room_import_frm");
		var top_el = $(this.id);
		var params = new Object();
		params["method"] = "post";

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["timeout_flag"] = 0;

		params['form_prefix'] = "room_import_attachment";



		params["param"] = new Object();
		params["param"]["action"] = "room_view_admin_import_upload";

		params["callbackfunc"] = function(res) {
			this.user_checkdata(this_el);
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	user_checkdata: function(this_el) {
		var top_el = $(this.id);
		var chk_params = new Object();
		chk_params["param"] = {"action":"room_view_admin_import_confirm"};
		chk_params["top_el"] = top_el;
		chk_params["loading_el"] = top_el;
		chk_params["target_el"] = $(this.id);

		commonCls.send(chk_params);
	},
	user_importdata: function() {
		var top_el = $(this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		action_params["param"] = {"action":"room_action_admin_import_confirm"};
		action_params["top_el"] = top_el;
		action_params["loading_el"] = top_el;
		action_params["target_el"] = $(this.id);
		commonCls.send(action_params);
	}
}