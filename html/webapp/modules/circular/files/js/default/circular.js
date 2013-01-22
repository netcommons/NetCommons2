var clsCircular = Class.create();
var circularCls = Array();

clsCircular.prototype = {

	initialize: function(id) {
		this.id = id;
		this.textarea = null;
		this.now_page = 1;
		this.list_type = null;
		this.order_type = null;
		this.blank_img = _nc_core_base_url + '/images/comp/textarea/titleicon/blank.gif';
		this.asc_img = _nc_core_base_url + '/images/comp/livegrid/sort_asc.gif';
		this.desc_img = _nc_core_base_url + '/images/comp/livegrid/sort_desc.gif';
	 	this.asc_str = "ASC";
	 	this.desc_str = "DESC";
		this.calendar = null;
		this.titleIcon = null;

		this.choiceIteration = 0;
	},

	displayList: function (now_page, list_type, order) {
		if (now_page) {
			this.now_page = now_page;
		}
		if (list_type) {
			this.list_type = list_type;
		}
		if (order) {
			if (this.order_type == this.desc_str) {
				this.order_type = this.asc_str;
			} else {
				this.order_type = this.desc_str;
			}
		}

		var params = new Object();
		params['action'] = "circular_view_main_init";
		params['now_page'] = this.now_page;
		params['list_type'] = this.list_type;
		params['order_type'] = this.order_type;

		commonCls.sendView(this.id, params);
	},

	displayEntry: function() {
		var params = new Object();
		var form_el = $("detail_circular_form" + this.id);
		if (form_el != null) {
			params["circular_id"] = form_el.circular_id.value;
			params["reply_type"] = form_el.reply_type.value;
		}
		params["action"] = "circular_view_main_create";
		commonCls.sendView(this.id, params);
	},

	createCircular: function() {
		var form_el = $("create_circular_form" + this.id);

		var circular_body = this.textarea.getTextArea();
		if (circular_body.toUpperCase() == "<BR />\n" || circular_body.toUpperCase() == "<P />") {
			circular_body = "";
		}
		form_el.circular_body.value = circular_body;

		var selectedUsers = form_el.selected_user_list;
		var userIds = "";
		for (i = 0; i < selectedUsers.length; i++) {
			if (i != 0) {
				userIds += ",";
			}
			userIds += selectedUsers.options[i].value;
		}
		form_el.receive_user_ids.value = userIds;

		var parameter = new Object();
		parameter["top_el"] = $(this.id);
		parameter["callbackfunc"] = function () {
			if (form_el.notification_mail != undefined && form_el.notification_mail.checked) {
				commonCls.sendPost(this.id, {"action":"circular_action_main_mail"});
			}
			commonCls.sendView(this.id, 'circular_view_main_init');
		}.bind(this);
		commonCls.sendPost(this.id, 'action=circular_action_main_add&' + Form.serialize(form_el), parameter);
	},

	replyCircular: function() {
		var form_el = $("reply_circular_form" + this.id);
		var circular_id = form_el.circular_id.value;

		var params = new Object();
		params["callbackfunc"] = function(){
			commonCls.sendView(this.id.replace("_popup_circular_reply", ""), {"action":"circular_view_main_detail", "circular_id":circular_id});
			commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendPost(this.id, 'action=circular_action_main_reply&' + Form.serialize(form_el), params);
	},

	deleteCircular: function(msg) {
		var form_el = $("detail_circular_form" + this.id);
		if (!commonCls.confirm(msg)) {
			return false;
		}
		commonCls.sendPost(this.id, "action=circular_action_main_delete&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	setImage: function(order_type) {
		var imgObj = $("circular_sort_img"+this.id);
		if (imgObj == undefined) {
			return;
		}
		switch (order_type) {
			case this.desc_str :
				imgObj.src = this.desc_img;
				break;
			case this.asc_str :
				imgObj.src = this.asc_img;
				break;
			default:
				imgObj.src = this.blank_img;
				break;
		}
		this.order_type = order_type;
	},

	moveUser: function(type) {
		var addUsersEl = null;
		var removeUsersEl = null;

		if (type == "add") {
			removeUsersEl = $("group_user_list" + this.id);
			addUsersEl = $("selected_user_list" + this.id);
		} else {
			addUsersEl = $("group_user_list" + this.id);
			removeUsersEl = $("selected_user_list" + this.id);
		}
		var length = addUsersEl.length;
		var flag = false;
		if (removeUsersEl.length > 0) {
			for (i=0; removeUsersEl.length; i++) {
				if (removeUsersEl.options[i] == undefined) {
					break;
				}
				if (removeUsersEl.options[i].selected) {
					length++;
					addUsersEl.length = length;
					addUsersEl.options[length-1].value = removeUsersEl.options[i].value;
					addUsersEl.options[length-1].text = removeUsersEl.options[i].text;
				    removeUsersEl.remove(i);
				    i--;
				    flag = true;
				}
			}
			if (type == "remove" && flag) {
				this.getRoomUser();
			}
		}
	},

	selectAllUser: function(type) {
		var usersEl = $(type+"_user_list" + this.id);
		if (usersEl.length > 0) {
			for (i = 0;i < usersEl.length; i++) {
				usersEl.options[i].selected = true;
			}
		}
	},

	getRoomUser: function() {
		var selectedUsersEl = $("selected_user_list" + this.id);
		var userIds = "";
		for (i = 0; i < selectedUsersEl.length; i++) {
			if (i != 0) {
				userIds += ",";
			}
			userIds += selectedUsersEl.options[i].value;
		}

		var element = $("selected_room_id"+this.id);
		var params = new Object();
		params['action'] = "circular_view_main_users";
		params['receive_user_ids'] = userIds;
		params['selected_room_id'] = element.options[element.selectedIndex].value;
		params['selected_group_id'] = element.options[element.selectedIndex].value.replace("group_", "");

		var parameter = new Object();
		parameter['target_el'] = $("room_users" + this.id);
		commonCls.sendView(this.id, params, parameter);

	},

	wysiwygInit: function() {
		this.textarea = new compTextarea();
		this.textarea.uploadAction = {
			image	: "circular_action_main_upload_image",
			file	: "circular_action_main_upload_attachment"
		};
		this.textarea.focus = true;
		this.textarea.textareaShow(this.id, "comptextarea", "full");
	},

	editStyle: function(form_el) {
		commonCls.sendPost(this.id, "action=circular_action_edit_style&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	editMail: function(form_el) {
		commonCls.sendPost(this.id, "action=circular_action_edit_option&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	showIcon: function() {
		this.titleIcon = new compTitleIcon(this.id);
		this.titleIcon.showDialogBox($("circular_icon_name_img" + this.id), $("circular_icon_name_hidden" + this.id));
		this.titleIcon == null;
	},

	popupReply: function (event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_circular_reply";
		params["action"] = "circular_view_main_reply_init";
		params["circular_id"] = $("circular_id" + this.id).value;
		params["reply_type"] = $("reply_type" + this.id).value;
		params["now_page"] = this.now_page;
		params["list_type"] = this.list_type;
		params["order_type"] = this.order_type;

		var optionParams = new Object();
		optionParams["top_el"] = $(this.id);
		optionParams["modal_flag"] = true;
		commonCls.sendPopupView(event, params, optionParams);
	},

	changePeriod: function() {
		this.calendar.disabledCalendar(!$("circular_period_checkbox" + this.id).checked);
	},

	toPage: function(el, action_name, num_classname, now_page, position) {
		var num_name = null;
		if(position == "bottom") {
			num_name = num_classname + "_bottom";
		}else {
			num_name = num_classname;
		}
		var visible_item = $(num_name);
		var numlist = document.getElementsByClassName(num_classname);
		var nums = $A(numlist);
		nums.each(function(n){
			n.selectedIndex = visible_item.selectedIndex;
		});
		var num = visible_item.options[visible_item.selectedIndex].value;
		var top_el = $(this.id);
		var params = new Object();

		params["param"] = {
			"action":action_name,
			"visible_row":num,
			"now_page":now_page
		};
		if (action_name == "circular_view_main_detail") {
			params["param"]["circular_id"] = $("circular_id" + this.id).value;
		}
		params["top_el"] = top_el;
		params["loading_el"] = el;
		params["target_el"] = top_el;
	   commonCls.send(params);
	},

	changeReplyType: function(type) {
		var choiceArea = $("circular_choice_area" + this.id);
		if (type == $("circular_reply_type_textarea" + this.id).value) {
			Element.addClassName(choiceArea, "display-none");
			return;
		} else {
			Element.removeClassName(choiceArea, "display-none");
		}
	},

	addChoice: function() {
		var params = new Object();
		var top_el = $(this.id);

		params["param"] = {
			"action":"circular_view_main_choice_add",
			"circular_id":this.circular_id,
			"choice_count":this.choiceIteration
		};

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("circular_choice_add" + this.id);
		params["callbackfunc"] = function(){
			var choice = $("circular_choice" + this.id);
			choice.firstChild.appendChild($("circular_choice_add" + this.id).firstChild.firstChild.firstChild);
			var textareas = choice.getElementsByTagName("textarea");
			commonCls.focus(textareas[textareas.length - 1]);
			this.choiceIteration++;
		}.bind(this);

		commonCls.send(params);
	},

	deleteChoice: function(element, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;
		var choice = $("circular_choice" + this.id);
		choice.firstChild.removeChild(element.parentNode.parentNode);
	},

	popupPostscript: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_circular_postscript";
		params["action"] = "circular_view_main_postscript_init";
		params["circular_id"] = $("circular_id" + this.id).value;

		var optionParams = new Object();
		optionParams["top_el"] = $(this.id);
		optionParams["modal_flag"] = true;
		commonCls.sendPopupView(event, params, optionParams);
	},

	addPostscript: function() {
		var form_el = $("postscript_circular_form" + this.id);

		form_el.postscript_body.value = this.textarea.getTextArea();

		var viewParams = new Object();
		viewParams["action"] = "circular_view_main_postscript_add";
		viewParams["circular_id"] = form_el.circular_id.value;

		var viewParameter = new Object();
		var top_el = $(this.id);
		var id = this.id.replace("_popup_circular_postscript", "");
		viewParameter["top_el"] = top_el;
		viewParameter["loading_el"] = top_el;
		viewParameter["target_el"] = $("circular_postscript_add" + id);
		viewParameter["callbackfunc"] = function(){
			var postscripts = $("circular_postscripts" + id);
			postscripts.appendChild($("circular_postscript_add" + id).firstChild.firstChild);
		}

		var parameter = new Object();
		parameter["callbackfunc"] = function(){
			for (i=0; i<form_el.postscript_mail_flag.length; i++) {
				if (form_el.postscript_mail_flag[i].checked) {
					chkflg  = form_el.postscript_mail_flag[i].value
					break;
				}
			}
			if (chkflg == "1") {
				var mailParams = new Object();
				mailParams["action"] = "circular_action_main_postscript_mail";
				mailParams["circular_id"] = form_el.circular_id.value;
				mailParams["mail_subject"] = form_el.postscript_mail_subject.value;
				mailParams["mail_body"] = form_el.postscript_mail_body.value;
				commonCls.sendPost(this.id, mailParams);
			}
			commonCls.removeBlock(this.id);
			commonCls.sendView(id, viewParams, viewParameter);
		}.bind(this);

		commonCls.sendPost(this.id, 'action=circular_action_main_postscript_add&' + Form.serialize(form_el), parameter);
	},

	popupEditGroup: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_edit_group";
		params["action"] = "circular_view_main_group_init";

		var optionParams = new Object();
		optionParams["top_el"] = $(this.id);
		optionParams["modal_flag"] = true;
		commonCls.sendPopupView(event, params, optionParams);
	},

	entryGroupMember: function() {
		var selectedUsers = $("selected_user_list" + this.id);
		var userIds = "";
		for (i = 0; i < selectedUsers.length; i++) {
			if (i != 0) {
				userIds += ",";
			}
			userIds += selectedUsers.options[i].value;
		}

		var params = new Object();
		params["action"] = "circular_action_main_group_entry";

		groupId = $("group_id" + this.id).value;
		if (groupId == null) {
			groupId = ""
		}
		groupName = $("group_name" + this.id).value;

		params["group_id"] = groupId;
		params["group_name"] = groupName;
		params["group_member"] = userIds;

		var parameter = new Object();
		parameter["callbackfunc"] = function (resGroupId) {
			commonCls.removeBlock(this.id);

			if (groupId != "" || resGroupId == "") {
				return false;
			}
			var array = resGroupId.split("|");
			var roomObj = new Option();
			roomObj.value = "group_"+array[0];
			roomObj.text = array[1];
			var selectRoomObj = $("selected_room_id" + this.id.replace("_popup_edit_group", ""));

			var strUA = "";
			strUA = navigator.userAgent.toLowerCase();

			if(strUA.indexOf("msie") != -1) {
				selectRoomObj.add(roomObj);
			} else {
				selectRoomObj.appendChild(roomObj);
			}

		}.bind(this);
		commonCls.sendPost(this.id, params, parameter);
	},

	setGroupInfo: function() {
		var params = new Object();
		params['action'] = "circular_view_main_group_init";
		params["group_id"] = $("group_list" + this.id).value;

		var parameter = new Object();
		parameter["callbackfunc"] = function () {
			this.getRoomUser();
		}.bind(this);
		commonCls.sendView(this.id, params, parameter);
	},

	deleteGroup: function(msg) {
		var groupListObj = $("group_list" + this.id);
		var groupId = groupListObj.value
		if (groupId == 0) {
			return false;
		}
		if (!commonCls.confirm(msg)) {
			return false;
		}
		var params = new Object();
		params['action'] = "circular_action_main_group_delete";
		params["group_id"] = groupId;

		var parameter = new Object();
		parameter["callbackfunc"] = function (resGroupId) {
			for (i = 0; i < groupListObj.length; i++) {
				if (groupListObj.options[i].value == resGroupId) {
					groupListObj.options[i] = null;
					break;
				}
			}
			var selectRoomObj = $("selected_room_id" + this.id.replace("_popup_edit_group", ""));
			for (i = 0; i < selectRoomObj.length; i++) {
				if (selectRoomObj.options[i].value == "group_"+resGroupId) {
					selectRoomObj.options[i] = null;
					break;
				}
			}
			this.clearGroupInfo(this.id.replace("_popup_edit_group", ""));
		}.bind(this);
		commonCls.sendPost(this.id, params, parameter);
	},

	clearGroupInfo: function() {
		$("group_id" + this.id).value = "";
		$("group_name" + this.id).value = "";
		var selectedUsers = $("selected_user_list" + this.id);
		var length = selectedUsers.length;
		for (i = 0; i < length; i++) {
			selectedUsers.options[selectedUsers.length-1] = null;
		}
		$("group_list" + this.id).value = 0;
		this.getRoomUser();
	},

	extendPeriod: function(calendarDate) {
		var period = $("circular_period" + this.id);

		if (calendarDate != undefined) {
			this.calendar.options.onClickCallback = null;
			this.calendar.onDayClick(calendarDate);
			this.calendar.options.onClickCallback = this.extendPeriod.bind(this);
		}
		var params = new Object();
		params["action"] = "circular_action_main_period";
		params["circular_id"] = $("circular_id" + this.id).value;
		params["period_checkbox"] = 1;
		params["period"] = period.value;

		var option = new Object();
		option["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.focus(period);
		}.bind(this);
		commonCls.sendPost(this.id, params, option);
	}
}