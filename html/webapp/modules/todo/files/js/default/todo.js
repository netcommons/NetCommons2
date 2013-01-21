var clsTodo = Class.create();
var todoCls = Array();

clsTodo.prototype = {
	initialize: function(id) {
		this.id = id;

		this.todo_id = null;
		this.currentTodoID = null;
		this.taskList = null;
		this.popup = null;
		this.popupInitialize = null;
		this.popupForm = null;
		this.ascImg = null;
		this.descImg = null;
		this.sortCol = null;
		this.sortDir = "DESC";
		this.oldSortCol = null;
		this.taskPeriod = null;
		this.stateLang = new Object();
	},

	checkCurrent: function() {
		var currentRow = $("todo_current_row" + this.currentTodoID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("todo_current" + this.currentTodoID + this.id);
		current.checked = true;
	},

	changeCurrent: function(todoID) {
		var oldCurrentRow = $("todo_current_row" + this.currentTodoID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentTodoID = todoID;
		var currentRow = $("todo_current_row" + this.currentTodoID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"todo_action_edit_current",
			"todo_id":todoID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "todo_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	changeStyle: function(formElement) {
		var params = new Object();
		params["callbackfunc"] = function(res){
											this.sortCol = null;
										}.bind(this);
		params["target_el"] = $(this.id);
		params["focus_flag"] = true;
		commonCls.sendPost(this.id, Form.serialize(formElement), params);
	},

	referTodo: function(event, todo_id) {
		var params = new Object();
		params["action"] = "todo_view_main_init";
		params["todo_id"] = todo_id;
		params["prefix_id_name"] = "popup_todo_reference" + todo_id;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},

	deleteTodo: function(todo_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var post = {
			"action":"todo_action_edit_delete",
			"todo_id":todo_id
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.sendView(this.id, "todo_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	showPopup: function(task_id, itemValue, eventElement) {
		var params = new Object();
		if (task_id) {
			params["prefix_id_name"] = "popup_todo_task_modify";
		} else {
			params["prefix_id_name"] = "popup_todo_task_add";
		}
		params["prefix_id_name"] = "popup_todo_task_entry";
		params["action"] = "todo_view_main_entry";
		params["task_id"] = task_id;
		params["item_value"] = itemValue;

		var optionParams = new Object();
		optionParams["top_el"] = $(this.id);
		optionParams["modal_flag"] = true;
		commonCls.sendPopupView(eventElement, params, optionParams);
	},

	enterTask: function(form_el, id) {
		var params = new Object();
		params["callbackfunc"] = function(res){
											commonCls.removeBlock(this.id);
											if(todoCls[id].sortCol != null) {
												todoCls[id].sortMethod();
											}else {
												commonCls.sendView(id, "todo_view_main_init");
											}
										}.bind(this);
		var post = Form.serialize(form_el);
		if (form_el["state"] && !form_el["state"].checked) {
			post += "&state=";
		}

		commonCls.sendPost(this.id, post, params);
	},

	changeState: function(eventElement, period_class_name) {
		var params = new Object();
		params["callbackfunc"] = function(res){
									if (form_el["state"].value == "1") {
										this.changeStatus("0", eventElement, period_class_name);
										return;
									}

									this.changeStatus("1", eventElement, period_class_name);
									var rowElement = Element.getParentElementByClassName(eventElement, "grid_row");
									var progressElement = rowElement.childNodes[4].firstChild;
									progressElement.innerHTML = res;
									var progressForm = rowElement.childNodes[4].lastChild;
									progressForm["progress"].value = "100";
								}.bind(this);

		var form_el = eventElement.nextSibling;
		var post = "action=todo_action_main_entry&task_id=" + form_el["task_id"].value;
		if (form_el["state"].value == "1") {
			post += "&state=";
		} else {
			post += "&state=1";
		}
		commonCls.sendPost(this.id, post, params);
	},

	showEnterProgress: function(eventElement) {
		commonCls.displayNone(eventElement);
		commonCls.displayVisible(eventElement.nextSibling);
		commonCls.focus(eventElement.nextSibling);
	},

	hideEnterProgress: function(eventElement) {
		commonCls.displayVisible(eventElement.previousSibling);
		commonCls.displayNone(eventElement);
	},

	enterProgress: function(formElement, periodClass) {
		var progress_el = formElement.previousSibling;

		var params = new Object();
		params["callbackfunc_error"] = function(res){
											if(this.sortCol != null) {
												this.sortMethod();
											}else {
												commonCls.sendView(this.id, "todo_view_main_init");
											}
										}.bind(this);
		params["callbackfunc"] = function(res){
									progress_el.innerHTML = res;
									commonCls.blockNotice(null, progress_el);
									this.hideEnterProgress(formElement);

									var rowElement = Element.getParentElementByClassName(progress_el, "grid_row");
									var stateElement = rowElement.childNodes[1].firstChild;
									if (formElement["progress"].value == "100") {
										this.changeStatus("1", stateElement, periodClass);
									} else {
										this.changeStatus("0", stateElement, periodClass);
									}
								}.bind(this);
		var post = "action=todo_action_main_entry&" + Form.serialize(formElement) + "&progressSuccess=1";
		commonCls.sendPost(this.id, post, params);
	},

	changeSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"todo_action_main_sequence",
			"drag_task_id":drag_id.match(/\d+/)[0],
			"drop_task_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},

	deleteTask: function(task_id, confirmMessage) {
		if (!confirm(confirmMessage)) return false;

		var params = new Object();
		var post = {
			"action":"todo_action_main_delete",
			"task_id":task_id
		};

		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
											if(this.sortCol != null) {
												this.sortMethod();
											}else {
												commonCls.sendView(this.id, "todo_view_main_init");
											}
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	sortBy: function(sort_col, todo_id) {
		this.todo_id = todo_id;
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

		this.sortMethod();
	},

	sortMethod: function() {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {
			"action":"todo_view_main_init",
			"todo_id":this.todo_id,
			"sort_col":this.sortCol,
			"sort_dir":this.sortDir
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = top_el;
		params["callbackfunc"] = function(res) {
										var imgObj = $("todo_sort_img" + this.id + "_" + this.sortCol);
										if(this.sortDir == "ASC") {
											imgObj.src = this.ascImg;
										}else {
											imgObj.src = this.descImg;
										}
										commonCls.displayVisible(imgObj);
										if(this.oldSortCol != null) {
											var oldImgObj = $("todo_sort_img" + this.id + "_" + this.oldSortCol);
											commonCls.displayNone(oldImgObj);
											this.oldSortCol = null;
										}
									}.bind(this);

		commonCls.send(params);
	},

	changeStatus: function(state, stateElement, periodClass) {
		var stateForm = stateElement.nextSibling;
		stateForm["state"].value = state;

		if (state == "1") {
			stateElement.innerHTML = this.stateLang["finish"];
		} else {
			stateElement.innerHTML = this.stateLang["none"];
		}

		if (periodClass == "") {
			return;
		}

		var rowElement = Element.getParentElementByClassName(stateElement, "grid_row");
		if (state == "0"
			&& !Element.hasClassName(rowElement, periodClass)) {
			Element.addClassName(rowElement, periodClass);
		}
		if (state == "1"
			&& Element.hasClassName(rowElement, periodClass)) {
			Element.removeClassName(rowElement, periodClass);
		}
	},

	setCategory: function(id, form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res){
			commonCls.sendView(id, "action=todo_view_edit_category_init&todo_id=" + form_el["todo_id"].value);
			commonCls.removeBlock(this.id);
		}.bind(this);
		var param_str = "action=todo_action_edit_category_entry&" + Form.serialize(form_el);
		commonCls.sendPost(this.id, param_str, params);
	}
}