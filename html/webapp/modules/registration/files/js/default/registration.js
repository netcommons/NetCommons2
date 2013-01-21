var clsRegistration = Class.create();
var registrationCls = Array();

clsRegistration.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentRegistrationID = null;
		this.registration_id = null;
		this.item_id = null;

		this.optionIteration = 0;
		this.errorSeparator = null;
	},

	checkCurrent: function() {
		var currentRow = $("registration_current_row" + this.currentRegistrationID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("registration_current" + this.currentRegistrationID + this.id);
		current.checked = true;
	},

	changeCurrent: function(registrationID) {
		var oldCurrentRow = $("registration_current_row" + this.currentRegistrationID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentRegistrationID = registrationID;
		var currentRow = $("registration_current_row" + this.currentRegistrationID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"registration_action_edit_registration_current",
			"registration_id":registrationID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "registration_view_edit_registration_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	changeActivity: function(element, registrationID, activity) {
		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}
		var post = {
			"action":"registration_action_edit_registration_activity",
			"registration_id":registrationID,
			"active_flag":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "registration_view_edit_registration_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	setblock: function(registrationID, activity) {
		var post = {
			"action":"registration_action_edit_registration_setblock",
			"registration_id":registrationID,
			"active_flag":activity
		};
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "registration_view_edit_registration_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	deleteRegistration: function(registration_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"registration_action_edit_registration_delete",
			"registration_id":registration_id
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "registration_view_edit_registration_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	deleteData: function(registration_id, data_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"registration_action_edit_data_delete",
			"registration_id":registration_id,
			"data_id":data_id
		};

		var params = new Object();
		params["target_el"] = $(this.id);

		commonCls.sendPost(this.id, post, params);
	},

	changeMailSend: function(send) {
		if (send) {
			Element.removeClassName($("registration_mail_send_content" + this.id), "display-none");
		} else {
			Element.addClassName($("registration_mail_send_content" + this.id), "display-none");
		}
	},

	changeOldUse: function(use) {
		if (use) {
			$("registration_old" + this.id).disabled = false;
		} else {
			$("registration_old" + this.id).disabled = true;
		}
	},

	showItem: function(event, item_id) {
		var params = new Object();
		var top_el = $(this.id);
		params["top_el"] = top_el;
		params["target_el"] = top_el;
		params["center_flag"] = true;
		params["modal_flag"] = true;

		var popupParams = new Object();
		popupParams["action"] = "registration_view_edit_item_entry";
		popupParams["registration_id"] = this.registration_id;
		popupParams["item_id"] = item_id;
		popupParams["prefix_id_name"] = "registration_item";

		commonCls.sendPopupView(event, popupParams, params);
	},

	changeListFlag: function() {
		if ($("registration_list_flag" + this.id).checked) {
			$("registration_sort_flag" + this.id).disabled = false;
			Element.removeClassName($("registration_sort_flag_label" + this.id), "disable_lbl");
		} else {
			var sortFlag = $("registration_sort_flag" + this.id);
			sortFlag.checked = false;
			sortFlag.disabled = true;
			Element.addClassName($("registration_sort_flag_label" + this.id), "disable_lbl");
		}
	},

	changeItemType: function() {
		var optionArea = $("registration_option_area" + this.id);
		var type = $("registration_item_type" + this.id).value;

		if (type == $("registration_item_type_radio" + this.id).value
				|| type == $("registration_item_type_check" + this.id).value
				|| type == $("registration_item_type_select" + this.id).value) {
			Element.removeClassName(optionArea, "display-none");
		} else {
			Element.addClassName(optionArea, "display-none");
		}
	},

	enterItem: function() {
		var registrationForm = $("registration_item_form" + this.id);
		var post = Form.serialize(registrationForm);

		var params = new Object();
		params["callbackfunc"] = function(res){
										var params = new Object();
										params["action"] = "registration_view_edit_item_list";
										params["registration_id"] = this.registration_id;
										commonCls.sendView(this.id.replace("_registration_item", ""), params);
										commonCls.removeBlock(this.id);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	deleteItem: function(item_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"registration_action_edit_item_delete",
			"registration_id":this.registration_id,
			"item_id":item_id
		};

		var params = new Object();
		params["callbackfunc"] = function(res){
										commonCls.sendView(this.id, {"action":"registration_view_edit_item_list", "registration_id":this.registration_id});
									}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	changeItemSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"registration_action_edit_item_sequence",
			"registration_id":this.registration_id,
			"drag_item_id":drag_id.match(/\d+/)[0],
			"drop_item_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},

	addOption: function() {
		var params = new Object();
		var top_el = $(this.id);

		params["param"] = {
			"action":"registration_view_edit_option_add",
			"registration_id":this.registration_id,
			"option_iteration":this.optionIteration
		};

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("registration_option_add_area" + this.id);
		params["callbackfunc"] = function(res){
										var option = $("registration_option" + this.id);
										option.firstChild.appendChild($("registration_option_add_area" + this.id).firstChild.firstChild.firstChild);
										var inputs = option.getElementsByTagName("input");
										commonCls.focus(inputs[inputs.length - 1]);
										this.optionIteration++;
									}.bind(this);

		commonCls.send(params);
	},

	deleteOption: function(element, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var option = $("registration_option" + this.id);
		option.firstChild.removeChild(element.parentNode.parentNode);
	},

	showPopupDataList: function(event, registration_id) {
		var params = new Object();
		var top_el = $(this.id);
		params["top_el"] = top_el;
		params["target_el"] = top_el;

		var popupParams = new Object();
		popupParams["action"] = "registration_view_edit_data_list";
		popupParams["registration_id"] = registration_id;
		popupParams["prefix_id_name"] = "registration_data_list" + registration_id;

		commonCls.sendPopupView(event, popupParams, params);
	},

	showDataList: function(param) {
		var params = new Object();
		params["action"] = "registration_view_edit_data_list";
		params["registration_id"] = this.registration_id;
		Object.extend(params, param);
		commonCls.sendView(this.id, params);
	},

	confirmData: function() {
		var params = new Object();
		params["param"] = {
			"action":"registration_action_main_confirm"
		};
		params["top_el"] = $(this.id);
		params["callbackfunc"] = function(files, res){
											commonCls.sendView(this.id, "registration_view_main_confirm");
										}.bind(this);
		params["callbackfunc_error"] = function(files, res){
											if (!res.match(this.errorSeparator)) {
												commonCls.alert(res);
												return;
											}

											var resArray = res.split(this.errorSeparator);
											var elementID = resArray.shift();

											res = resArray.join("\n");
											commonCls.alert(res);
											$(elementID + this.id).focus();
										}.bind(this);

		commonCls.sendAttachment(params);
	},

	enterData: function() {
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
										commonCls.sendPost(this.id, "registration_action_main_mail");
									}.bind(this);

		commonCls.sendPost(this.id, "registration_action_main_entry", params);
	}
}