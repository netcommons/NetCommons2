var clsPm = Class.create();
var pmCls = Array();

clsPm.prototype = {
	initialize: function(id, parent_id) {
		this.top_el_id = null;
		this.id = id;
		this.filterFlag = null;
		this.ascImg = null;
		this.descImg = null;
		this.sortCol = null;
		this.sortDir = "DESC";
		this.oldSortCol = null;
		this.textarea = null;
		this.mailbox = 0;
		this.page = 1;
		this.ccIndex = 1;

		this.search_date_from = null;
		this.search_date_to = null;
		this.popup_editor_count = 0;
		this.backup_avatar_html = "";

		this.parent_id = (parent_id != "") ? parent_id : null;
		this.selectUserCallback = null;
    },

	sortBy: function(sort_col, search_flag) {
		if(this.sortCol == null) {
			this.sortDir = "DESC";
		}else {
			if(this.sortCol != sort_col) {
				this.oldSortCol = this.sortCol;
				this.sortDir = "DESC";
			}else {
				if(this.sortDir == "DESC") {
					this.sortDir = "ASC";
				}else {
					this.sortDir = "DESC";
				}
			}
		}
		this.sortCol = sort_col;
		this.sortMethod(search_flag);
	},

	sortMethod: function(search_flag) {
		var top_el = $(this.id);
		var params = new Object();
		var action = "pm_view_main_init";

		if(search_flag == 'search'){
			action = "pm_view_main_search_result";
			this.mailbox = 4;
		}

		params["param"] = {
			"action":action,
			"sort_col":this.sortCol,
			"sort_dir":this.sortDir,
			"filter":this.filterFlag,
			"mailbox":this.mailbox,
			"search_flag":search_flag
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		params["callbackfunc"] = function(res) {
							var imgObj = $("pm_sort_img" + this.id + "_" + this.sortCol);
							if (this.sortDir == "ASC") {
								imgObj.src = this.ascImg;
							} else {
								imgObj.src = this.descImg;
							}
							commonCls.displayVisible(imgObj);
							if (this.oldSortCol != null) {
								var oldImgObj = $("pm_sort_img" + this.id + "_" + this.oldSortCol);
								commonCls.displayNone(oldImgObj);
								this.oldSortCol = null;
							}
						}.bind(this);

		commonCls.send(params);
	},

    /*メッセージ登録画面に遷移*/
    showMessagePopup: function(receiver_id, sender_handle, eventElement,flag, parent_el_id, top_id_name) {
		sender_handle = sender_handle.unescapeHTML();
		if(receiver_id > 0){
			var prefix_id_name = "popup_message_reply_" + receiver_id;
		}else{
			var prefix_id_name = "popup_message_new_" + this.popup_editor_count;
			this.popup_editor_count++;
		}

		if(parent_el_id != null){
			this.id = top_id_name;
		}else{
			var top_id_name = this.id;
		}

		var params = new Object();
		params["top_el"] = $(this.id);
		params["modal_flag"] = false;
		params["center_flag"] = true;
		params["loading_el"] = $(this.id);
		params["callbackfunc"] = function(res){
										if(parent_el_id != null && parent_el_id != "_active_center_0"){
											//active_centerの場合、親は消しない(_active_center_0)
											commonCls.removeBlock(parent_el_id);
										}
								  }.bind(this);

		commonCls.sendPopupView(eventElement, {'action':'pm_view_main_message_entry','prefix_id_name': prefix_id_name,'receiver_id':receiver_id,'sender_handle':sender_handle,'flag':flag, 'top_el_id':this.id.replace("_", ""),'top_id_name':top_id_name}, params);
	},

	/*メッセージ送信、下書き保存*/
    setMessage: function(form_el,sendFlag, top_el_id) {
		if (sendFlag == 1) {
			this.mailbox = "1";
		} else if (sendFlag == 2) {
			this.mailbox = "2";
		}

		var send_all_flag_value = 0;
		if(form_el.send_all_flag != null){
			send_all_flag_value = form_el.send_all_flag.value;
		}

		var top_el = $(this.id);
		var params = new Object();

		var messageBody = this.textarea.getTextArea();
			params["param"] = "pm_action_main_message_entry&" + Form.serialize(form_el) +
						"&body=" + encodeURIComponent(messageBody) +
						"&sendFlag=" + sendFlag;
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;

		if(!send_all_flag_value){
			var receivers = document.getElementsByName("receivers[]");
			var receiver_list = "";
			for (var i=0; i<receivers.length; i++) {
				receiver_list = receiver_list + receivers[i].value;
			}
		}else{
			var receiver_list = "";
		}

		var subject = "";
		if(form_el.subject != null){
			subject = form_el.subject.value;
		}
		messageBody = messageBody.replace('<br />','');
		messageBody = messageBody.replace( /\r|\n/g,'');

		params["callbackfunc"] = function(res){
									this.sendMail();
								 	commonCls.removeBlock(this.id);
								 	var parameters = new Object();
								 	parameters["action"] = "pm_view_main_init";
								 	parameters["mailbox"] = this.mailbox;
									commonCls.sendView(top_el_id, parameters);
							}.bind(this);
		commonCls.send(params);
	},

    /*メッセージ詳細画面に遷移*/
	showDetailPopup: function(receiver_id, row_id, read_state, page, eventElement,search_flag) {
		if(read_state <= 0){
			this.listRowOnRead(this.id, row_id);
		}

		commonCls.sendPopupView(eventElement, {'prefix_id_name' : "popup_pm_message_detail" + receiver_id, 'action': "pm_view_main_message_detail", 'receiver_id': receiver_id, 'mailbox': this.mailbox, 'filter': this.filterFlag, 'page': page, 'parent_id_name': this.id, 'theme_name': 'system', 'top_el_id': this.id.replace("_", ""), 'search_flag': search_flag, 'top_id_name':this.id}, {'top_el':$(this.id), 'modal_flag':false, 'center_flag':true});
	},

	closeDetailsPopup: function(id, receiver_id, page_id){
		if(this.id == "" || this.id == null) {
			this.id = id;
		}
		var top_el = $(this.id.replace("_popup_pm_message_detail" + receiver_id, ""));
		var params = new Object();
		params["param"] = {
			"action":"pm_view_main_init",
			"mailbox":0,
			"page_id":page_id
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		params["callbackfunc"] = function(res) {
			var id_prefix_match = (this.id).match(new RegExp("_popup_pm_message_detail" + receiver_id));
			if(id_prefix_match){
				commonCls.removeBlock(this.id);
			}
		}.bind(this);
		commonCls.send(params);
	},

	/*削除タグ*/
	deleteTag: function(tag_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var params = new Object();
		var post = {
			"action":"pm_action_main_tag_delete",
			"tag_id":tag_id
		};

		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	/*編集画面初期処理*/
	pmEditInit: function() {

		//テキストエリア
		this.textarea = new compTextarea();
		this.textarea.uploadAction = {
				image    : "pm_action_upload_image",
				file     : "pm_action_upload_file"
		};
		this.textarea.downloadAction = "pm_download_main";
		this.textarea.focus = false;
		// this.textarea.textareaShow(this.id, "comptextarea", "full");
		this.textarea.textareaShow(this.id, "comptextarea", "simple");
	},

	pmEditSubjectInit: function(form_el, subject, reply_flag){
		if(reply_flag == '1'){
			var re_count = 0;
			var re_match = subject.match(new RegExp("^(Re:){1,}"));
			if (re_match) {
				re_count = re_match[0].length / 3;
				subject = subject.gsub(/Re:/, "");
			}

			var re_match = subject.match(new RegExp("^(Re([0-9]+):)"));
			if (re_match) {
				re_count += valueParseInt(re_match[2]);
				subject = subject.replace(new RegExp(re_match[0]), "");
			}
			if (re_count > 0) {
				form_el.subject.value = "Re" + (re_count + 1) + ":" + subject;
			} else {
				form_el.subject.value = "Re:" + subject;
			}
		}else{
			form_el.subject.value = subject;
		}
	},

	/*絞り込み*/
	filter: function(form_el,filter) {
		var top_el = $(this.id);
		var params = new Object();

		this.filterFlag = filter;
		var search_flag = "none";
		if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }

		var main_window_action = "pm_view_main_init";
		if(search_flag == 'search'){
			main_window_action = "pm_view_main_search_result";
			this.mailbox = 4;
		}

		params["param"] = {
			"action":main_window_action,
			"sort_col":this.sortCol,
			"sort_dir":this.sortDir,
			"filter":this.filterFlag,
			"mailbox":this.mailbox,
			"search_flag":search_flag
		};

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		params["callbackfunc"] = function(res) {
		                            	this.setSortState();
									}.bind(this);
		commonCls.send(params);
	},

	/*タグ登録画面に遷移*/
	showTagPopup: function(tag_id, detail_id, eventElement, receiver_list, search_flag, select_all_flag, top_id_name,parent_id_name) {
		if(detail_id > 0){
			// var top_el_id = this.id.replace('_popup_pm_message_detail' + detail_id + "_", '');
			this.id = top_id_name;
			var top_el_id = top_id_name.replace('_', '');
		} else {
			var top_el_id = this.id.replace('_', '');
			var top_id_name = this.id;
			var parent_id_name = this.id;
		}

		commonCls.sendPopupView(eventElement, {'prefix_id_name':'popup_pm_tag_entry', 'action':'pm_view_main_tag_entry', 'top_el_id':top_el_id, 'tag_id':tag_id, 'receiver_list':receiver_list, 'mailbox':this.mailbox, 'search_flag':search_flag, 'select_all_flag':select_all_flag,'filter':this.filterFlag, 'sortCol':this.sortCol, 'sortDir':this.sortDir, 'page':this.page, 'top_id_name':top_id_name,'parent_id_name':parent_id_name}, {'top_el':$(this.id), 'modal_flag':true, 'center_flag':true});

		if($("otherOp" + this.id)){
			$("otherOp" + this.id).getElementsByTagName("option")[0].selected = true;
		}
	},

	resetOperationBox: function(top_el_id){
		if($("otherOp" + this.id)){
		   $("otherOp_" + top_el_id).getElementsByTagName("option")[0].selected = true;
		}
	},

    /*タグ*/
	enterTag: function(form_el, flag, receiver_list, mailbox, search_flag, top_el_id, parent_el_id) {
		var params = new Object();
		var post = Form.serialize(form_el);

		var action = "";

		if (flag == 1 &&  receiver_list != "") {
			action ="pm_view_main_init";
		    if(search_flag == 'search'){
				action = "pm_view_main_search_result";
				this.mailbox = 4;
			}
		} else if (flag == 2 || receiver_list == "") {
			action = "pm_view_main_tag_init"
		}

		var page = null;
		if(form_el.page != null){
			page = form_el.page.value;
		}

		var sortCol = null;
		if(form_el.sortCol != null){
			sortCol = form_el.sortCol.value;
		}

		var sortDir = null;
		if(form_el.sortDir != null){
			sortDir = form_el.sortDir.value;
		}

		var filter = null;
		if(form_el.filter != null){
			filter = form_el.filter.value;
		}

		params["callbackfunc"] = function(res){
									commonCls.removeBlock(this.id);

									var parameters = new Object();
									parameters["action"] = action;
									parameters["receiver_list"] = receiver_list;
									parameters["mailbox"] = mailbox;
									parameters["sort_col"] = sortCol;
									parameters["sort_dir"] = sortDir;
									parameters["filter"] = filter;
									parameters["page"] = page;
									parameters["top_el_id"] = top_el_id;
									// commonCls.sendView("_" + top_el_id, parameters);
									commonCls.sendView(top_el_id, parameters);

									if((parent_el_id != null && receiver_list != null) && (top_el_id != parent_el_id)){
										var parameters = new Object();
										parameters["action"] = "pm_view_main_message_detail";
										parameters["prefix_id_name"] = "popup_pm_message_detail" + receiver_list;
										parameters["receiver_id"] = receiver_list;
										parameters["page"] = this.page;
										parameters["filter"] = this.filterFlag;
										parameters["location"] = location;
										parameters["mailbox"] = mailbox;

										parameters["search_flag"] = search_flag;
										parameters["theme_name"] = "system";
										parameters["top_id_name"] = top_el_id;
										parameters["parent_id_name"] = parent_el_id;
										parameters["top_el_id"] = parent_el_id.replace("_", "");

										//commonCls.sendView("_popup_pm_message_detail"+receiver_list+"_"+top_el_id, parameters);
										commonCls.sendView(parent_el_id, parameters);
									}
								}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	/*メッセージ操作*/
	operation: function(form_el,op, top_id_name, parent_id_name, mailbox,filter,page,location,eventElement,confirm_msg, list_total_count) {
		if(this.id == "" || this.id == null) {
			this.id = parent_id_name;
		}
		var params = new Object();
		this.mailbox = mailbox;
		this.page = page;
		this.filterFlag = filter;

		var search_flag = "none";
		if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }

		var select_all_flag = null;
		if(form_el.select_all_flag != null){ select_all_flag = form_el.select_all_flag.value; }

		var main_window_action = "pm_view_main_init";
		if(search_flag == 'search'){
			main_window_action = "pm_view_main_search_result";
			this.mailbox = 4;
		}

		var receiver_list = "";
		var receiver_id = "";

		var receiver_cnt = 0;
		if (form_el.receiver_id != null) {
			receiver_id = form_el.receiver_id.value;
			receiver_cnt++;
		} else {
			var inps = document.getElementsByName("receiver_id[]");
			receiver_list = "";
			for (var i=0; i<inps.length; i++) {
				if (inps[i].checked==true){
					if (receiver_list == "") {
						receiver_list = inps[i].value;
					} else {
						receiver_list = receiver_list + "," + inps[i].value;
					}
					receiver_cnt++;
				}
			}
		}

		if (receiver_list != "" || receiver_id != "") {
			if (op != "") {
				if(op == "delete"){
					if(receiver_id == ""){
						if(form_el.select_all_flag != null && form_el.select_all_flag.value == "1"){
							var receiver_cnt = list_total_count;
						}
					}

					if(!window.confirm(confirm_msg.replace("%s", receiver_cnt))){
						return false;
					}
				}

				if (op == "newtag") {
					if (receiver_list == "") {
						this.showTagPopup("", receiver_id, eventElement, receiver_id, search_flag, select_all_flag,top_id_name, parent_id_name);
					} else if (receiver_id == "") {
						this.showTagPopup("", "", eventElement, receiver_list,search_flag, select_all_flag, this.id);
					}
				} else {
					params["callbackfunc"] = function(res){
								if (receiver_list != "") {
									var parameters = new Object();
									parameters["action"] = main_window_action;
									parameters["sort_col"] = this.sortCol;
									parameters["sort_dir"] = this.sortDir;
									parameters["filter"] = this.filterFlag;
									parameters["receiver_list"] = receiver_list;
									parameters["mailbox"] = this.mailbox;
									parameters["page"] = this.page;

									if(main_window_action == 'pm_view_main_search_result'){
										parameters["search_flag"] = search_flag;
									}
									commonCls.sendView(this.id, parameters);
								}

								if (receiver_id != "") {
									var parameters = new Object();
									parameters["action"] = main_window_action;
									parameters["sort_col"] = this.sortCol;
									parameters["sort_dir"] = this.sortDir;
									parameters["filter"] = this.filterFlag;
									parameters["mailbox"] = this.mailbox;
									parameters["page"] = this.page;

									if(main_window_action == 'pm_view_main_search_result'){
										parameters["search_flag"] = search_flag;
									}

									if(parent_id_name == "_active_center_0") {
										parameters["top_el_id"] = parent_id_name.replace("_", "");
										commonCls.sendView(parent_id_name, parameters);
									}else {
										commonCls.sendView(top_id_name, parameters);
									}
									if(this.parent_id != null) {
										if (op == "delete" && parent_id_name != "_active_center_0") {
											commonCls.removeBlock(parent_id_name);
										}else if(op != "delete"){
											parameters = new Object();
											parameters["action"] = "pm_view_main_message_detail";
											parameters["theme_name"] = "system";
											parameters["prefix_id_name"] = "popup_pm_message_detail" + receiver_id;
											parameters["receiver_id"] = receiver_id;
											parameters["page"] = this.page;
											parameters["filter"] = this.filterFlag;
											parameters["location"] = location;
											parameters["mailbox"] = this.mailbox;
											parameters["search_flag"] = search_flag;

											parameters["top_id_name"] = top_id_name;
											parameters["parent_id_name"] = parent_id_name;
											parameters["top_el_id"] = parent_id_name.replace("_", "");
											// commonCls.sendView(this.id, parameters);
											commonCls.sendView(parent_id_name, parameters);
										}
									}
								}
							}.bind(this);
					var post = Form.serialize(form_el);
					post += "&op=";
					post += op;
					commonCls.sendPost(this.id, post, params);
				}
			}
		}

		$("otherOp" + this.id).getElementsByTagName("option")[0].selected = true;
	},

	// トレイ変更
	changeBox: function(mailbox, action, active_center) {
		if(active_center == null){
			active_center = false;
		}

		this.filterFlag = "";
		this.mailbox = mailbox;
		this.sortCol = null;
		this.sortDir = "DESC";

		if(active_center == false){
			var parameters = new Object();
			parameters["action"] = action;
			parameters["mailbox"] = this.mailbox;
			commonCls.sendView(this.id, parameters);
		}
	},

	update:function(mailbox, search_flag) {
		this.filterFlag = "";
		this.mailbox = mailbox;
		var parameters = new Object();
		var action = "pm_view_main_init";
		if(search_flag == 'search'){
			action = "pm_view_main_search_result";
			this.mailbox = 4;
		}
		parameters["action"] = action;
		parameters["mailbox"] = this.mailbox;
		parameters["search_flag"] = search_flag;
		commonCls.sendView(this.id, parameters);
	},

	setSortState:function() {
		var imgObj = $("pm_sort_img" + this.id + "_" + this.sortCol);
		if(this.sortDir == "ASC") {
			imgObj.src = this.ascImg;
		} else {
			imgObj.src = this.descImg;
		}
		commonCls.displayVisible(imgObj);
		if(this.oldSortCol != null) {
			var oldImgObj = $("pm_sort_img" + this.id + "_" + this.oldSortCol);
			commonCls.displayNone(oldImgObj);
			this.oldSortCol = null;
		}
	},

	// タブセット
	createSettingTabset: function(activeIndex, tab1_title, tab2_title, tab3_title){
		if(!activeIndex) { activeIndex = 0; }

		var top_el = $(this.id);
		var tabset = new compTabset(top_el);

		if(activeIndex == 0){
			tabset.addTabset(tab1_title, null, null);
		}else{
			tabset.addTabset(tab1_title,
							 function(){ commonCls.sendView(this.id, 'pm_view_main_forward');  return false;}.bind(this),
							 null);
		}

		if(activeIndex == 1){
			tabset.addTabset(tab2_title, null, null);
		}else{
			tabset.addTabset(tab2_title,
							 function(){ commonCls.sendView(this.id, 'pm_view_main_filter_init');  return false;}.bind(this),
							 null);
		}

		if(activeIndex == 2){
			tabset.addTabset(tab3_title, null, null);
		}else{
			tabset.addTabset(tab3_title,
							 function(){ commonCls.sendView(this.id, 'pm_view_main_tag_init');  return false;}.bind(this),
							 null);
		}
		tabset.setActiveIndex(activeIndex);
		tabset.render();
		commonCls.focus($(this.id));
	},

	listRadioOnCicked: function(radio_el, row_el_id, receiver_id, cls_selected, cls_unselected){
		if($(row_el_id)){
			if(radio_el.checked == true){
				$(row_el_id).className = cls_selected;
				this.switchListColsClass(receiver_id, true);
			}else{
				$(row_el_id).className = cls_unselected;
				this.switchListColsClass(receiver_id, false);

				var select_all_checkbox_el = $("pm_form" + this.id + "_select_all_checkbox");
				var select_all_flag_el = $("pm_form" + this.id + "_select_all_flag");
				var select_all_message_el = $("pm" + this.id + "_select_all_message");

				if(select_all_checkbox_el && select_all_flag_el && select_all_message_el){
					select_all_checkbox_el.checked = false;
					select_all_flag_el.value = "0";
					commonCls.displayNone(select_all_message_el);
				}
			}
		}
	},

	listRowOnRead: function(top_el_id, row_el_id){
		Element.removeClassName($(row_el_id), "pm_list_inbox_unread");
		var radio_el_id = row_el_id.replace('pm_list_row', 'pm_list_row_radio');
		$(radio_el_id).onclick = function(event){
			pmCls[top_el_id].listRadioOnCicked(this, row_el_id, row_el_id.replace('pm_list_row' + top_el_id + '_', ''), 'pm_list_inbox_checked', '');
		};
	},

	saveMailForward: function(form_el){
		var post = Form.serialize(form_el);
		commonCls.sendPost(this.id, post);
	},

	showFilterPopup: function(filter_id, eventElement) {
		commonCls.sendPopupView(eventElement, {'prefix_id_name':'popup_pm_filter_entry' + this.id, 'action':'pm_view_main_filter_entry', 'filter_id':filter_id, 'top_id_name':this.id}, {'top_el':$(this.id), 'modal_flag':true, 'center_flag':true});
	},

	enterFilter: function(form_el, top_el_id){
		var params = new Object();
		var post = Form.serialize(form_el);
		params["callbackfunc"] = function(res){
									commonCls.removeBlock(this.id);
									var parameters = new Object();
									parameters["action"] = "pm_view_main_filter_init";
									// commonCls.sendView(this.id.replace("_popup_pm_filter_entry",""), parameters);
									commonCls.sendView(top_el_id, parameters);
								}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	/*削除タグ*/
	deleteFilter: function(filter_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var params = new Object();
		var post = {
			"action":"pm_action_main_filter_delete",
			"filter_id":filter_id
		};

		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	jumpMailBoxPage: function(form_el, mailbox, filter, page){
		this.mailbox = mailbox;
		this.filterFlag = filter;
		this.page = page;

		var top_el = $(this.id);
		var params = new Object();
		var search_flag = "none";
		if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }

		if(search_flag == 'search'){
			var action = "pm_view_main_search_result";
		}else{
			var action = "pm_view_main_init";
		}

		params["param"] = {
			"action":action,
			"sort_col":this.sortCol,
			"sort_dir":this.sortDir,
			"filter":this.filterFlag,
			"mailbox":this.mailbox,
			"page":this.page
		};

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		params["callbackfunc"] = function(res) {
										this.setSortState();
									}.bind(this);
		commonCls.send(params);
	},

	addCC: function(lang_delete, value, user_lang, user_id){
		var cc_el = $("pm" + this.id + "_addreceiver");

		var user_show_flag = false;
		if(user_id != null && value != null){
			user_show_flag = true;
			var user_link = '<a class="syslink" href="#" title="' + user_lang.replace("%s", value) + '" onclick="commonCls.showUserDetail(event, \'' + user_id + '\');return false;">' + value + '</a>';
		}

		var li = document.createElement('li');
		li.id = "pm" + this.id + "_cc_" + this.ccIndex;
		cc_el.appendChild(li);
		li.className = "pm_ins_subject_li pm_ins_subject_cc";



		if(user_show_flag){
			li.innerHTML = '<div class="pm_ins_cc_left_input"><input id="pm_form' + this.id + '_receivers' + this.ccIndex + '" class="pm_ins_subject_li_input" type="text" name="receivers[]" value="' + value + '" onkeyup="pmCls[\'' + this.id + '\'].enterCC(' + this.ccIndex + '); return false;" onblur="pmCls[\'' + this.id + '\'].renderCC(' + this.ccIndex + ',\'' + user_lang + '\', true)"/></div><div id="pm_form' + this.id + '_cc_user' + this.ccIndex + '" class="pm_ins_cc_left">' + user_link + '</div><div id="pm_form' + this.id + '_cc_line' + this.ccIndex + '" class="pm_ins_cc_center">&nbsp;|&nbsp;</div><div id="pm_form' + this.id + '_cc_delete' + this.ccIndex + '" class="pm_ins_cc_right display-block"><a href="#" onclick="pmCls[\'' + this.id + '\'].removeCC(' + this.ccIndex + '); return false;">' + lang_delete + '</a></div>';
		}else{
			value = value.replace('&nbsp;', ' ');
			li.innerHTML = '<div class="pm_ins_cc_left_input"><input id="pm_form' + this.id + '_receivers' + this.ccIndex + '" class="pm_ins_subject_li_input" type="text" name="receivers[]" value="' + value + '" onkeyup="pmCls[\'' + this.id + '\'].enterCC(' + this.ccIndex + '); return false;" onblur="pmCls[\'' + this.id + '\'].renderCC(' + this.ccIndex + ',\'' + user_lang + '\', true)"/></div><div id="pm_form' + this.id + '_cc_user' + this.ccIndex + '" class="pm_ins_cc_left display-none"></div><div id="pm_form' + this.id + '_cc_line' + this.ccIndex + '" class="pm_ins_cc_center display-none">&nbsp;|&nbsp;</div><div id="pm_form' + this.id + '_cc_delete' + this.ccIndex + '" class="pm_ins_cc_right display-block"><a href="#" onclick="pmCls[\'' + this.id + '\'].removeCC(' + this.ccIndex + '); return false;">' + lang_delete + '</a></div>';
		}
		this.ccIndex++;
	},

	removeCC: function(cc_id){
		this.enterCC(cc_id);

		var cc_el = document.getElementById("pm" + this.id + "_addreceiver");
		var li = document.getElementById("pm" + this.id + "_cc_" + cc_id);
		cc_el.removeChild(li);
	},

	enterCC: function(cc_id){
		if(cc_id == ''){
			var receivers_el = $('pm_form' + this.id + '_receivers');
			var receiver_user_el = $('pm_form' + this.id + '_receiver_user');

			if(receivers_el.value != ''){
				receiver_user_el.innerHTML = receivers_el.value;
				commonCls.displayVisible(receiver_user_el);
			}else{
				receiver_user_el.innerHTML = '';
				commonCls.displayNone(receiver_user_el);
			}
		}else{
			var cc_el = $('pm_form' + this.id + '_receivers' + cc_id);
			var cc_user_el = $('pm_form' + this.id + '_cc_user' + cc_id);
			var cc_line_el = $('pm_form' + this.id + '_cc_line' + cc_id);
			var cc_delete_el = $('pm_form' + this.id + '_cc_delete' + cc_id);

			if(cc_el.value != ''){
				cc_user_el.innerHTML = cc_el.value;
				commonCls.displayVisible(cc_user_el);
				commonCls.displayVisible(cc_line_el);
				commonCls.displayVisible(cc_delete_el);
			}else{
				cc_user_el.innerHTML = '';
				commonCls.displayNone(cc_user_el);
				commonCls.displayNone(cc_line_el);
			}
		}
	},

	renderCC: function(cc_id, user_lang, error_report){
		if(cc_id == ''){
			var input_el = $('pm_form' + this.id + '_receivers');
			var label_el = $('pm_form' + this.id + '_receiver_user');
			var avatar_el = $('pm_form' + this.id + '_avatar');
		}else{
			var input_el = $('pm_form' + this.id + '_receivers' + cc_id);
			var label_el = $('pm_form' + this.id + '_cc_user' + cc_id);
			var avatar_el = null;
		}

		if(input_el){
			var top_el = $(this.id);
			var params = new Object();
			params["param"] = {
				"action":"pm_action_main_userinfo",
				"handle":input_el.value,
				"flag": "info"
			};

			params["method"] = "post";
			params["top_el"] = top_el;
			//params["loading_el"] = top_el;

			params["callbackfunc_error"] = function(res) {
				var handle = input_el.value.escapeHTML();
				res = res.split("|");

				if(res[0] == 'false'){
					var send_all_flag_el = $('pm_form' + this.id + '_send_all_flag');
					if(send_all_flag_el != null){
						if(send_all_flag_el.checked == 1){
							error_report = false;
						}
					}

					if(error_report){
						window.alert(res[1].replace('<br />', ''));
					}
					label_el.innerHTML = '<span class="pm_error">' + handle + '</span>';
				}else{
					if(res[0] == 'true'){
						var user_id = res[1].replace('<br />', '');
						var html = '<a class="syslink" href="#" title="' + user_lang.replace("%s", handle) + '" ';
						html += 'onclick="commonCls.showUserDetail(event, \'' + user_id + '\');return false;">';
						html += handle + '</a>';
						label_el.innerHTML = html;

						if(avatar_el != null){
							this.loadingAvatar(avatar_el.id, user_id);
						}
					}
				}
			}.bind(this);
			commonCls.send(params);
		}
	},

	searchMessage: function(form_el){
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
											this.mailbox = "0";
										}.bind(this);

		var post = Form.serialize(form_el);
		commonCls.sendPost(this.id, post, params);
	},

	onClickAllDown: function(select_all_flag_id, clicker_id, msg_canvas_id,
							 msg_selected, msg_selected_link,
							 msg_unselected, msg_unselected_link){

		if(!$(select_all_flag_id)){
			return false;
		}
		if(!$(clicker_id)){
			return false;
		}
		if(!$(msg_canvas_id)){
			return false;
		}

		if($(clicker_id).checked == true){
			var msg_canvas_el = $(msg_canvas_id);
			var select_all_flag_el = $(select_all_flag_id);
			commonCls.displayVisible(msg_canvas_el);
			select_all_flag_el.value = "0";
			msg_selected_link = '<br/><a href="#" onclick="pmCls[\'' + this.id + '\'].onSelectAll(\'' + select_all_flag_id + '\', \'' + clicker_id + '\', \'' + msg_canvas_id + '\', \'' + msg_unselected + '\', \'' + msg_unselected_link + '\'); return false;">' + msg_selected_link + '</a>';
			msg_canvas_el.innerHTML = msg_selected + msg_selected_link;
			this.selectAllListRows();
		}else{
			var msg_canvas_el = $(msg_canvas_id);
			var select_all_flag_el = $(select_all_flag_id);
			select_all_flag_el.value = "0";
			commonCls.displayNone(msg_canvas_el);
			this.unSelectAllListRows();
		}
	},

	onSelectAll: function(select_all_flag_id, clicker_id, msg_canvas_id,
						  msg_unselected, msg_unselected_link){
		if(!$(select_all_flag_id)){
			return false;
		}
		if(!$(clicker_id)){
			return false;
		}
		if(!$(msg_canvas_id)){
			return false;
		}

		$(select_all_flag_id).value = "1";
		msg_unselected_link = '<br/><a href="#" onclick="pmCls[\'' + this.id + '\'].onUnselectAll(\'' + select_all_flag_id + '\', \'' + clicker_id + '\', \'' + msg_canvas_id + '\'); return false;">' + msg_unselected_link + '</a>';
		$(msg_canvas_id).innerHTML = msg_unselected + msg_unselected_link;
	},

	onUnselectAll: function(select_all_flag_id, clicker_id, msg_canvas_id){
		if(!$(select_all_flag_id)){
			return false;
		}
		if(!$(clicker_id)){
			return false;
		}
		if(!$(msg_canvas_id)){
			return false;
		}

		var clicker_el = $(clicker_id);
		var msg_canvas_el = $(msg_canvas_id);
		var select_all_flag_el = $(select_all_flag_id);
		clicker_el.checked = false;
		commonCls.displayNone(msg_canvas_el);
		select_all_flag_el.value = "0";
		msg_canvas_el.innerHTML = "";
		this.unSelectAllListRows();
	},

	selectAllListRows: function(){
		var radios = document.getElementsByName("receiver_id[]");
		for(var i = 0; i < radios.length; i++){
			radios[i].checked = true;
			var tableRow = $("pm_list_row" + this.id + "_" + radios[i].value);
			if(tableRow){
				if(tableRow.className != ""){
				   tableRow.className = tableRow.className + " pm_list_inbox_checked";
				}else{
				   tableRow.className = "pm_list_inbox_checked";
				}
			}
			this.switchListColsClass(radios[i].value, true);
		}
	},

	unSelectAllListRows: function(){
		var radios = document.getElementsByName("receiver_id[]");
		for(var i = 0; i < radios.length; i++){
			radios[i].checked = false;
			var tableRow = $("pm_list_row" + this.id + "_" + radios[i].value);
			if(tableRow){
				if(tableRow.className != ""){
				   Element.removeClassName(tableRow, "pm_list_inbox_checked");
				}
			}
			this.switchListColsClass(radios[i].value, false);
		}
	},

	switchListColsClass: function(receiver_id, select_flag){
		var col1_id = "pm_list_col" + this.id + "_" + receiver_id + "_check";
		var col2_id = "pm_list_col" + this.id + "_" + receiver_id + "_sender";
		var col3_id = "pm_list_col" + this.id + "_" + receiver_id + "_subject";
		var col4_id = "pm_list_col" + this.id + "_" + receiver_id + "_date";

		if( $(col1_id) && $(col2_id) && $(col3_id) && $(col4_id) ){
			if(select_flag){
				$(col1_id).className = $(col1_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
				$(col2_id).className = $(col2_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
				$(col3_id).className = $(col3_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
				$(col4_id).className = $(col4_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
			}else{
				$(col1_id).className = $(col1_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
				$(col2_id).className = $(col2_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
				$(col3_id).className = $(col3_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
				$(col4_id).className = $(col4_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
			}
		}
	},

	sendToAllMember: function(sent_all_flag_el){
		var send_normal_el = $("pm_form" +  this.id + "_send_normal");
		var hidden = false;

		if(sent_all_flag_el.checked == true){
			if(send_normal_el != null){
				commonCls.displayNone(send_normal_el);
			}
			hidden = true;
		}else{
			if(send_normal_el != null){
				commonCls.displayVisible(send_normal_el);
			}
			hidden = false;
		}

		this.updAvatarBox(hidden);
	},

	updAvatarBox: function(hidden_flag){
		var avatar_el = $("pm_form" +  this.id + "_avatar");
		if(hidden_flag == true){
			if(avatar_el != null){
				this.backup_avatar_html = avatar_el.innerHTML;
				avatar_el.innerHTML = "";
			}
		}else{
			if(avatar_el != null){
				avatar_el.innerHTML = this.backup_avatar_html;
			}
		}
	},

	sendMail: function() {
		var params = new Object();
		params["param"] = "pm_action_main_mail";
		params["method"] = "post";
		params["top_el"] = $(this.id);
		commonCls.send(params);
	},

	loadingAvatar: function(avatar_id, user_id){
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {
			"action":"pm_action_main_userinfo",
			"user_id":user_id,
			"flag": "avatar"
		};

		params["method"] = "post";
		params["top_el"] = top_el;
		//params["loading_el"] = top_el;

		params["callbackfunc_error"] = function(res) {
			var avatar_el = $(avatar_id);
			res = res.replace('<br />', '');

			if(res == "false"){
				res = "";
			}

			if(avatar_el != null){
				if(res == ""){
					res = "/images/common/avatar_thumbnail.gif";
					avatar_el.innerHTML = '<img src="' + _nc_base_url + res + '"/>';
				}else{
					avatar_el.innerHTML = '<img src="' + _nc_base_url + _nc_index_file_name + res + '&amp;thumbnail_flag=1"/>';
				}
			}
		}.bind(this);
		commonCls.send(params);
	},

	showSearchUser: function(event, lang_delete, user_lang) {
		var params = new Object();
		params["action"] = "pm_view_main_search_user";
		params["prefix_id_name"] = "pm";
		params["block_id"] = 0;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;
		popupParams["modal_flag"] = true;
		popupParams["callbackfunc"] = function(){
			if(!pmCls['_pm_0']) {
				pmCls['_pm_0'] = new clsPm('_pm_0','');
			}
			pmCls['_pm_0'].selectUserCallback = function(element){
				this.selectUser(element, lang_delete, user_lang);
			}.bind(this);
		}.bind(this);
		commonCls.sendPopupView(event, params, popupParams);
	},

	select: function(user_id) {
		var element = $(user_id + this.id);
		this.selectUserCallback(element);
	},

	selectUser: function(element, lang_delete, user_lang){
		var handleElement = element.getElementsByTagName("input")[1];

		var main_receiver_el = $('pm_form' + this.id + '_receivers');
		if(main_receiver_el.value == ""){
			main_receiver_el.value = handleElement.value;
			this.enterCC('');
			this.renderCC('', user_lang, true);
		}else{
			this.addCC(lang_delete, handleElement.value.escapeHTML(), user_lang);
			this.enterCC(this.ccIndex -1);
			this.renderCC(this.ccIndex -1, user_lang, true);
		}
	}
}


