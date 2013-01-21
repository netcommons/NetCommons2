var clsCalendar = Class.create();
var calendarCls = Array();

clsCalendar.prototype = {
	initialize: function(id, main_el_id) {
		this.id = id;
		this.main_el_id = main_el_id;
		this.move_popup_id = null;

		this.CALENDAR_YEARLY = "1";
		this.CALENDAR_S_MONTHLY = "2";
		this.CALENDAR_L_MONTHLY = "3";
		this.CALENDAR_WEEKLY = "4";
		this.CALENDAR_DAILY = "5";
		this.CALENDAR_T_SCHEDULE = "6";
		this.CALENDAR_U_SCHEDULE = "7";

		this.CALENDAR_LINK_NONE = "0";
		this.CALENDAR_LINK_TODO = "1";
		this.CALENDAR_LINK_RESERVATION = "2";

		this.view_date = null;
		this.textarea = null;
		this.scrollTop = null;
		this.time_visible = null;
		this.daily_time_height = null;

		this.titleIcon = null;
		this.title_openschedule = null;
		this.title_closeschedule = null;
		this.timeline_visible = null;
		this.timeline_hidden = null;
	},
	onmouseImage: function(el, image, highlight) {
		if (el.tagName == "img") {
			var img_el = el;
		} else {
			var img_el = el.getElementsByTagName("img")[0];
		}
		if (highlight) {
			var re = new RegExp(image + "\.gif$", "i");
			img_el.src = img_el.src.replace(re, image + "_select.gif");
		} else {
			var re = new RegExp(image + "_select\.gif$", "i");
			img_el.src = img_el.src.replace(re, image + ".gif");
		}
	},
	showHelp: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_calendar_help";
		params["action"] = "calendar_view_main_help";
		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},


	initMain: function(date, type, time_visible) {
		this.type = type;
		this.view_date = date;
		this.time_visible = time_visible;
	},
	changeCalendar: function(date, type, move_popup_id) {
		if (type) {
			this.type = type;
		}
		if (date) {
			this.view_date = date;
		}
		if (this.type == this.CALENDAR_DAILY) {
			var top_el = $(this.id);
			var scroll_el = Element.getChildElementByClassName(top_el, "calendar_daily_time");
			if (scroll_el) this.scrollTop = scroll_el.scrollTop;
			commonCls.sendView(this.id, {"action":"calendar_view_main_init", "date":this.view_date, "display_type":this.type, "time_visible":this.time_visible});
		} else if (this.type == this.CALENDAR_T_SCHEDULE || this.type == this.CALENDAR_U_SCHEDULE) {
			this.showMain(this.view_date);
		} else {
			this.scrollTop = null;
			commonCls.sendView(this.id, {"action":"calendar_view_main_init", "date":this.view_date, "display_type":this.type});
		}
		if (move_popup_id) {
			this.move_popup_id = move_popup_id;
		}
		if (this.move_popup_id) {
			var move_popup_el = $(this.move_popup_id);
		}
		if (move_popup_el) {
			calendarCls[this.move_popup_id].switchMoveDate(null, this.view_date, this.type);
		} else {
			this.move_popup_id = move_popup_id;
		}
	},
	showMain: function(date, calendar_id) {
		var top_el = $(this.id);
		var params = new Object();
		params["top_el"] = top_el;
		switch (this.type) {
			case this.CALENDAR_YEARLY:
			case this.CALENDAR_S_MONTHLY:
			case this.CALENDAR_L_MONTHLY:
			case this.CALENDAR_WEEKLY:
				this.changeCalendar(date);
				break;
			case this.CALENDAR_DAILY:
				if (calendar_id) {
					params["param"] = {"action":"calendar_view_main_list_daily", "date":date, "display_type":this.type, "calendar_id":calendar_id};
				} else {
					params["param"] = {"action":"calendar_view_main_list_daily", "date":date, "display_type":this.type};
				}
				this.showMainDaily(date, params);
				break;
			case this.CALENDAR_T_SCHEDULE:
			case this.CALENDAR_U_SCHEDULE:
				params["param"] = {"action":"calendar_view_main_list_schedule", "date":date, "display_type":this.type};
				this.showMainSchedule(date, params);
				break;
			default:
				return;
		}
	},
	showMainDaily: function(date, params) {
		var top_el = $(this.id);
		params["callbackfunc"] = function(res) {
			var allday_tag = res.getElementsByTagName("allday")[0];
			var allday_el = Element.getChildElementByClassName(top_el, "calendar_daily_allday");
			allday_el.innerHTML = allday_tag.firstChild.nodeValue;

			var daily_el = Element.getChildElementByClassName(top_el, "_calendar_daily");
			var time_el = Element.getChildElementByClassName(top_el, "_calendar_time");
			if (!this.daily_time_height) {
				this.daily_time_height = time_el.offsetHeight;
			}
			var rows = res.getElementsByTagName("row");
			var time_top_el = $("_calendar_daily_time"+this.id);
			for (var i = 0; i<rows.length; i++) {
				var branch = rows[i].getElementsByTagName("branch")[0].firstChild.nodeValue;
				var branches = daily_el.childNodes;
				var branch_el = null;
				for (var j=0; j<branches.length; j++) {
					if (Element.hasClassName(branches[j], "_calendar_branch_" + branch)) {
						branch_el = branches[j];
						break;
					}
				}
				if (!branch_el) {
					var branch_el = document.createElement("DIV");
					daily_el.appendChild(branch_el);
					Element.addClassName(branch_el, "_calendar_branch_" + branch);
					Element.addClassName(branch_el, "float-left");
					Element.addClassName(branch_el, "calendar_branch");
				}

				var plan_el = document.createElement("DIV");
				branch_el.appendChild(plan_el);
				var height = rows[i].getElementsByTagName("height")[0].firstChild.nodeValue;
				plan_el.style.height = this.daily_time_height * height + "px";

				var data_el = rows[i].getElementsByTagName("data")[0];
				var data = data_el.firstChild.nodeValue;
				var class_name = Element.readAttribute(data_el, "class")
				if (class_name) {
					Element.addClassName(plan_el, class_name);
					var title_name = Element.readAttribute(data_el, "title")
					if (title_name) {
						plan_el.title = title_name;
					}
				}
				plan_el.innerHTML = data_el.firstChild.nodeValue;
			}
			var br_el = document.createElement("BR");
			Element.addClassName(br_el, "float-clear");

			var total_branch = res.getElementsByTagName("total_branch")[0].firstChild.nodeValue;
			var width_branch = time_el.offsetWidth;
			for (var i = 0; i<=total_branch; i++) {
				var branch_el = Element.getChildElementByClassName(top_el, "_calendar_branch_" + branch);
				width_branch += branch_el.offsetWidth;
			}
			var brank_img_el = Element.getChildElementByClassName(top_el, "calendar_time_blank");
			if (allday_el.offsetWidth < width_branch + 20) {
				brank_img_el.style.width = width_branch + 20 + "px";
			}
		}.bind(this);
		commonCls.send(params);
	},
	setTimeFocus: function(time_str) {
		var top_el = $(this.id);
		var scroll_el = Element.getChildElementByClassName(top_el, "calendar_daily_time");
		if (this.scrollTop) {
			scroll_el.scrollTop = this.scrollTop;
		} else {
			if (!this.daily_time_height) {
				var time_el = Element.getChildElementByClassName(top_el, "_calendar_time");
				this.daily_time_height = time_el.offsetHeight;
			}
			scroll_el.scrollTop = this.daily_time_height * time_str.substr(0,2);
			if (scroll_el.scrollTop > 0) {
				scroll_el.scrollTop = scroll_el.scrollTop - 5;
			}
			this.scrollTop = scroll_el.scrollTop;
		}
	},
	visibleTime: function(el) {
		var top_el = $(this.id);
		var daily_el = Element.getChildElementByClassName(top_el, "calendar_daily_time");
		var img_el = Element.getChildElementByClassName(el, "_calendar_time_visible_image");

		if (!Element.hasClassName(daily_el, "display-none")) {
			Element.addClassName(daily_el, "display-none");
			img_el.src = img_el.src.replace("time_hidden.gif", "time_visible.gif");
			img_el.alt = this.timeline_visible;
			img_el.title = this.timeline_visible;
			this.time_visible = 0;
		} else {
			Element.removeClassName(daily_el, "display-none");
			img_el.src = img_el.src.replace("time_visible.gif", "time_hidden.gif");
			img_el.alt = this.timeline_hidden;
			img_el.title = this.timeline_hidden;
			this.time_visible = 1;
		}
	},
	showMainSchedule: function(date, params) {
		var top_el = $(this.id);
		params["callbackfunc"] = function(res) {
			var schedule_el = $("_calendar_schedule"+this.id);
			var dates_list = schedule_el.getElementsByTagName("div");
			for (var i = 0; i<dates_list.length; i++) {
				if (!Element.hasClassName(dates_list[i], "calendar_date")) { continue; }
				var classNames = dates_list[i].className.split(" ");

				var plan_data = res.getElementsByTagName(classNames[0]);
				if (!plan_data || plan_data.length == 0) { continue; }
				var plan_el = Element.getChildElementByClassName(dates_list[i], "calendar_plan_frame");
				plan_el.innerHTML = plan_data[0].firstChild.nodeValue;

				var plan_data = res.getElementsByTagName(classNames[0]+"_count");
				if (!plan_data || plan_data.length == 0) { continue; }
				var plan_el = Element.getChildElementByClassName(dates_list[i], "calendar_plan_count");
				plan_el.innerHTML = plan_data[0].firstChild.nodeValue;
			}

			var table_list = top_el.getElementsByTagName("table");
			for (var i=0; i<table_list.length; i++) {
				if (Element.hasClassName(table_list[i], "calendar_head") || Element.hasClassName(table_list[i], "calendar_foot")) {
					var time_el = Element.getChildElementByClassName(table_list[i], "calendar_t_schedule");
					var user_el = Element.getChildElementByClassName(table_list[i], "calendar_u_schedule");

					if (this.type == this.CALENDAR_T_SCHEDULE) {
						commonCls.displayNone(user_el);
						commonCls.displayVisible(time_el);
					} else {
						commonCls.displayVisible(user_el);
						commonCls.displayNone(time_el);
					}
				}
			}
		}.bind(this);
		commonCls.send(params);
	},
	switchOpenClose: function(this_el) {
		var parent_el = this_el.parentNode;
		var img_el = this_el.getElementsByTagName("img")[0];
		var count_el = this_el.getElementsByTagName("span")[0];
		var frame_el = parent_el.getElementsByTagName("div")[0];
		if (img_el.src.match("schedule_close.gif")) {
			img_el.src = img_el.src.replace("schedule_close.gif", "schedule_open.gif");
			img_el.title = this.title_closeschedule;
		} else {
			img_el.src = img_el.src.replace("schedule_open.gif", "schedule_close.gif");
			img_el.title = this.title_openschedule;
		}
		commonCls.displayChange(frame_el);
		commonCls.displayChange(count_el);
	},


	setAddPlanRoom: function(select_el) {
		if (select_el.selectedIndex < 0) {
			return;
		}
		var option_el = select_el.options[select_el.selectedIndex];
		if (!Element.hasClassName(option_el, "disable_lbl")) { return; }

		for (var i=0; i<select_el.options.length; i++) {
			var option_el = select_el.options[i];
			if (!Element.hasClassName(option_el, "disable_lbl")) {
				select_el.selectedIndex = i;
				break;
			}
		}
	},
	showPlanList: function(event, date, dateTopId) {
		var popupElement = $(dateTopId);

		var params = new Object();
		params["prefix_id_name"] = "popup_calendar_date";
		params["action"] = "calendar_view_main_plan_list";
		params["date"] = date;
		var optParams = new Object();
		var top_el = $(this.id);
		optParams["top_el"] = top_el;
		if (!event) {
			var date_el = Element.getChildElementByClassName(top_el, "carendar_" + date);
			if (date_el == null) {
				var date_el = Element.getChildElementByClassName(top_el, "calendar_body");
			}
			var offset = Position.positionedOffset(date_el);
			optParams["x"] = offset[0];
			optParams['y'] = offset[1];
		}
		commonCls.sendPopupView(event, params, optParams);

		if (popupElement) {
			commonCls.sendView(dateTopId, params);
		}
	},
	showAddPlan: function(event, date, time, details_flag) {
		var top_el = $(this.id);
		var params = new Object();
		params["prefix_id_name"] = "popup_calendar_0";
		params["action"] = "calendar_view_main_plan_add";
		params["date"] = date;
		params["details_flag"] = details_flag;
		if (details_flag) {
			var form_el = top_el.getElementsByTagName("form")[0];
			params["plan_room_id"] = form_el.plan_room_id.value;
			params["title"] = form_el.title.value;
			params["title_icon"] = form_el.icon_name.value;
			if (!form_el.notification_mail || form_el.notification_mail == undefined) {
				params["notification_mail"] = 0;
			} else {
				if (form_el.notification_mail.checked) {
					params["notification_mail"] = 1;
				} else {
					params["notification_mail"] = 0;
				}
			}
			if (form_el.allday_flag.checked) {
				params["allday_flag"] = 1;
			} else {
				params["allday_flag"] = 0;
			}
			params["start_date"] = form_el.start_date.value;
			params["start_time"] = form_el.start_hour.value + form_el.start_minute.value;
			params["end_time"] = form_el.end_hour.value + form_el.end_minute.value;
			var absolute_el = $(this.id).parentNode;
			var optParams = new Object();
			optParams["callbackfunc"] = function(){commonCls.moveAutoPosition(this);}.bind(absolute_el);
			commonCls.sendView(this.id, params, optParams);
		} else {
			if (time) {
				params["time"] = time;
			}
			var type = calendarCls[this.main_el_id].type;
			if (type == this.CALENDAR_YEARLY || type == this.CALENDAR_S_MONTHLY) {
				var optParams = new Object();
				var absolute_el = $(this.id).parentNode;
				var offset = Position.positionedOffset(absolute_el);
				optParams["x"] = offset[0];
				optParams["y"] = offset[1];
				optParams["callbackfunc"] = function(){
					commonCls.removeBlock(this.id);
				}.bind(this);
				optParams["top_el"] = top_el;
				optParams["modal_flag"] = true;
				commonCls.sendPopupView(event, params, optParams);
			} else {
				commonCls.sendPopupView(event, params, {"top_el":top_el, "modal_flag":true});
			}
		}
	},
	showModifyPlan: function(event, calendar_id, edit_rrule) {
		var params = new Object();
		params["prefix_id_name"] = "popup_regist_calendar";
		params["action"] = "calendar_view_main_plan_modify";
		params["calendar_id"] = calendar_id;
		params["edit_rrule"] = edit_rrule;

		var optParams = new Object();
		optParams["top_el"] = $(this.id);
		optParams["modal_flag"] = true;
		optParams["center_flag"] = true;
		optParams["callbackfunc"] = function(){commonCls.removeBlock(this.id)}.bind(this);
		commonCls.sendPopupView(event, params, optParams);
	},
	showDetails: function(event, calendar_id, popup_flag) {
		var params = new Object();
		params["prefix_id_name"] = "popup_calendar_" + calendar_id;
		params["action"] = "calendar_view_main_plan_details";
		params["calendar_id"] = calendar_id;
		if (popup_flag) {
			var absolute_el = $(this.id).parentNode;
			var optParams = new Object();
			optParams["callbackfunc"] = function(){
				commonCls.moveAutoPosition(this);
			}.bind(absolute_el);
			commonCls.sendView(this.id, params, optParams);
		} else {
			var type = calendarCls[this.main_el_id].type;
			if (type == this.CALENDAR_YEARLY || type == this.CALENDAR_S_MONTHLY) {
				var optParams = new Object();
				var absolute_el = $(this.id).parentNode;
				var offset = Position.positionedOffset(absolute_el);
				optParams["x"] = offset[0];
				optParams["y"] = offset[1];
				optParams["callbackfunc"] = function(){
					commonCls.removeBlock(this.id);
				}.bind(this);
				optParams["top_el"] = $(this.id);
				commonCls.sendPopupView(event, params, optParams);
			} else {
				commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
			}
		}
	},
	showModLink: function(event, link_flag, link_id, action_name) {
		var params = new Object();
		if (link_flag == this.CALENDAR_LINK_RESERVATION) {
			params["prefix_id_name"] = "popup_reservation_" + link_id;
			var action_name = "action=" + action_name;
			var action_params = action_name.split("&");
			for (var i=0; i<action_params.length; i++) {
				var action_param = action_params[i].split("=");
				params[action_param[0]] = action_param[1];
			}
			commonCls.sendPopupView(event, params);

		} else {
			params["prefix_id_name"] = "popup_calendar_link" + link_id;
			params["theme_name"] = "system";
			var action_name = "action=" + action_name;
			var action_params = action_name.split("&");
			for (var i=0; i<action_params.length; i++) {
				var action_param = action_params[i].split("=");
				params[action_param[0]] = action_param[1];
			}
			commonCls.sendPopupView(event, params);
		}

	},


	switchTime24: function(form_el) {
		if (form_el.allday_flag.checked) {
			form_el.end_minute.disabled = true;
			return true;
		}
		if (form_el.end_hour.value == "24") {
			form_el.end_minute.disabled = true;
			form_el.end_minute.value = "00";
		} else {
			form_el.end_minute.disabled = false;
		}
	},
	showIcon: function() {
		if (this.titleIcon == null || (this.titleIcon.popup.getPopupElement()).contentWindow == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("icon_name_img" + this.id), $("icon_name_hidden" + this.id));
	},
	initModifyPlan: function(details_flag) {
		var top_el = $(this.id);
		this.titleIcon = null;
		var form_el = top_el.getElementsByTagName("form")[0];
		if (details_flag) {
			this.textarea = new compTextarea();
			this.textarea.uploadAction = {
				image    : "calendar_action_upload_image",
				file     : "calendar_action_upload_file"
			};
			this.textarea.textareaShow(this.id, "comptextarea", "full");
			this.calendarSDate = new compCalendar(this.id, "start_date"+this.id);
			this.calendarEDate = new compCalendar(this.id, "end_date"+this.id);
			commonCls.closeCallbackFunc(this.id, function(){
				this.calendarSDate = null;
				this.calendarEDate = null;
				this.textarea = null;
			}.bind(this));
		} else {
			this.calendarSDate = new compCalendar(this.id, "start_date"+this.id);
			commonCls.closeCallbackFunc(this.id, function(){
				this.calendarSDate = null;
			}.bind(this));
		}
	},
	switchAllday: function(form_el) {
		if (form_el.allday_flag.checked) {
			form_el.start_hour.disabled = true;
			form_el.start_minute.disabled = true;
			form_el.end_hour.disabled = true;
			form_el.end_minute.disabled = true;
		} else {
			form_el.start_hour.disabled = false;
			form_el.start_minute.disabled = false;
			form_el.end_hour.disabled = false;
			this.switchTime24(form_el);
		}
	},
	addPlan: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();

		if (form_el.details_flag.value == "1") {
			var description = this.textarea.getTextArea();
			params["param"] = "calendar_action_main_plan_add&" + Form.serialize(form_el) +
						"&description=" + encodeURIComponent(description);
		} else {
			form_el.end_date.value = form_el.start_date.value;
			params["param"] = "calendar_action_main_plan_add&" + Form.serialize(form_el);
		}
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["callbackfunc"] = function(res){
			if (form_el.notification_mail != undefined && form_el.notification_mail.checked) {
				this.sendMail();
			}
			var type = calendarCls[this.main_el_id].type;
			var view_date = calendarCls[this.main_el_id].view_date;
			if (type == this.CALENDAR_YEARLY || type == this.CALENDAR_S_MONTHLY) {
				calendarCls[this.main_el_id].showPlanList(null, res);
				if (type == this.CALENDAR_YEARLY) {
					calendarCls[this.main_el_id].showMain(res.substr(0,6));
				} else {
					calendarCls[this.main_el_id].showMain(view_date);
				}
			} else {
				calendarCls[this.main_el_id].changeCalendar();
			}
			commonCls.removeBlock(this.id);
		}.bind(this);
		commonCls.send(params);
	},
	modifyPlan: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		var description = this.textarea.getTextArea();
		params["param"] = "calendar_action_main_plan_modify&" + Form.serialize(form_el) +
					"&description=" + encodeURIComponent(description);
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["callbackfunc"] = function(res){
			if (form_el.notification_mail != undefined && form_el.notification_mail.checked) {
				this.sendMail();
			}
			var id_params = commonCls.getParams(top_el);
			if (id_params["block_id"] == "0") {
				calendarCls[this.main_el_id].showMain(res, form_el.calendar_id.value);
			} else {
				var type = calendarCls[this.main_el_id].type;
				var view_date = calendarCls[this.main_el_id].view_date;
				if (type == this.CALENDAR_YEARLY || type == this.CALENDAR_S_MONTHLY) {
					if ($("_popup_calendar_date" + this.main_el_id)) {
						commonCls.removeBlock("_popup_calendar_date" + this.main_el_id);
					}
					calendarCls[this.main_el_id].showPlanList(null, res);
					if (type == this.CALENDAR_YEARLY) {
						calendarCls[this.main_el_id].showMain(res.substr(0,6));
					} else {
						calendarCls[this.main_el_id].showMain(view_date);
					}
				} else {
					calendarCls[this.main_el_id].changeCalendar();
				}
			}
			commonCls.removeBlock(this.id);
		}.bind(this);
		commonCls.send(params);
	},
	deletePlan: function(calendar_id, edit_rrule, confirm_mes) {
		if (commonCls.confirm(confirm_mes)) {
			var top_el = $(this.id);
			var params = new Object();
			params["param"] = "calendar_action_main_plan_delete&calendar_id=" + calendar_id + "&edit_rrule=" + edit_rrule;
			params["method"] = "post";
			params["top_el"] = top_el;
			params["callbackfunc"] = function(res){
				commonCls.removeBlock(this.id);
				var id_params = commonCls.getParams(top_el);
				if (id_params["block_id"] == "0") {
					history.back();
				} else {
					if ($("_popup_calendar_date" + this.main_el_id)) {
						commonCls.removeBlock("_popup_calendar_date" + this.main_el_id);
					}
					calendarCls[this.main_el_id].changeCalendar(null, null);
				}
			}.bind(this);
			commonCls.send(params);
		}
	},


	initMoveDate: function(today) {
		this.today = today;
		this.calendarMoveDate = new compCalendar(this.id, "move_date"+this.id);
		commonCls.closeCallbackFunc(this.id, function(){
			this.calendarMoveDate = null;
		}.bind(this));
		calendarCls[this.main_el_id].move_popup_id = this.id;
	},
	showDateMove: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_calendar_move";
		params["action"] = "calendar_view_main_movedate";
		params["date"] = this.view_date;
		params["display_type"] = this.type;
		params["theme_name"] = "system";
		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},
	moveDate: function(form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res){
			calendarCls[this.main_el_id].changeCalendar(res, null, this.id);
		}.bind(this);
		commonCls.sendPost(this.id, 'action=calendar_action_main_movedate&' + Form.serialize(form_el), params);
	},
	switchMoveDate: function(el, date, type) {
		this.view_date = date;
		this.type = type;

		var top_el = $("calendar_movedate"+this.id);
		var img_list = top_el.getElementsByTagName("img");
		for (var i=0; i<img_list.length; i++) {
			if (!Element.hasClassName(img_list[i], "calendar_move_highlight")) { continue; }
			if (!Element.hasClassName(img_list[i], "display-none")) {
				Element.addClassName(img_list[i], "display-none");
				if (Element.hasClassName(img_list[i].nextSibling, "display-none")) {
					Element.removeClassName(img_list[i].nextSibling, "display-none");
				}
				break;
			}
		}
		if (!el) {
			switch (type) {
				case this.CALENDAR_YEARLY:
					var el = $("calendar_yearly"+this.id);
					break;
				case this.CALENDAR_S_MONTHLY:
					var el = $("calendar_s_monthly"+this.id);
					break;
				case this.CALENDAR_L_MONTHLY:
					var el = $("calendar_l_monthly"+this.id);
					break;
				case this.CALENDAR_WEEKLY:
					var el = $("calendar_weekly"+this.id);
					break;
				case this.CALENDAR_DAILY:
					var el = $("calendar_daily"+this.id);
					break;
				default:
			}
		}
		if (!Element.hasClassName(el, "display-none")) {
			Element.addClassName(el, "display-none");
		}
		if (Element.hasClassName(el.previousSibling, "display-none")) {
			Element.removeClassName(el.previousSibling, "display-none");
		}
		var y_jump_el = $("calendar_jump_yearly" + this.id);
		if (type == this.CALENDAR_YEARLY) {
			commonCls.displayVisible(y_jump_el);
			var today_link_el = $("calendar_move_this_year" + this.id);
			if (date.substr(0,4) == this.today.substr(0,4)) {
				commonCls.displayNone(today_link_el);
				commonCls.displayVisible(today_link_el.nextSibling);
			} else {
				commonCls.displayVisible(today_link_el);
				commonCls.displayNone(today_link_el.nextSibling);
			}
			$("yearly_year" + this.id).value = date.substr(0,4);
		} else {
			commonCls.displayNone(y_jump_el);
		}
		var m_jump_el = $("calendar_jump_monthly" + this.id);
		if (type == this.CALENDAR_S_MONTHLY || type == this.CALENDAR_L_MONTHLY) {
			commonCls.displayVisible(m_jump_el);
			var today_link_el = $("calendar_move_this_month" + this.id);
			if (date.substr(0,6) == this.today.substr(0,6)) {
				commonCls.displayNone(today_link_el);
				commonCls.displayVisible(today_link_el.nextSibling);
			} else {
				commonCls.displayVisible(today_link_el);
				commonCls.displayNone(today_link_el.nextSibling);
			}
			$("monthly_year" + this.id).value = date.substr(0,4);
			$("monthly_month" + this.id).value = date.substr(4,2);
		} else {
			commonCls.displayNone(m_jump_el);
		}
		var d_jump_el = $("calendar_jump_daily" + this.id);
		if (type == this.CALENDAR_WEEKLY || type == this.CALENDAR_DAILY) {
			commonCls.displayVisible(d_jump_el);
			var today_link_el = $("calendar_move_today" + this.id);
			if (date.substr(0,8) == this.today.substr(0,8)) {
				commonCls.displayNone(today_link_el);
				commonCls.displayVisible(today_link_el.nextSibling);
			} else {
				commonCls.displayVisible(today_link_el);
				commonCls.displayNone(today_link_el.nextSibling);
			}
			$("move_date" + this.id).value = date.substr(0,4)+compCalendarLang.day_separator+date.substr(4,2)+compCalendarLang.day_separator+date.substr(6,2);
		} else {
			commonCls.displayNone(d_jump_el);
		}
	},

	switchRoom: function(select_el) {
		var form_el = $("calendar_form" + this.id);
		if (select_el.selectedIndex < 0) {
			var error_el = $("calendar_error_message_notfound" + this.id);
			select_el.disabled = true;
		} else {
			var error_el = $("calendar_error_message" + this.id);
		}
		var link_el = $("calendar_addplan_details_link" + this.id);
		if (form_el.plan_room_id.value == "-" || select_el.selectedIndex < 0) {
			if (link_el) {
				commonCls.displayNone(link_el);
				commonCls.displayVisible(link_el.nextSibling);
			}
			commonCls.displayVisible(error_el);
			form_el.regist.disabled = true;
		} else {
			form_el.regist.disabled = false;
			if (link_el) {
				commonCls.displayVisible(link_el);
				commonCls.displayNone(link_el.nextSibling);
			}
			commonCls.displayNone(error_el);
		}

	},

	switchDispType: function(form_el) {
		var inputList = form_el.getElementsByTagName("input");
		for (var i = 0; i<inputList.length; i++) {
			if (Element.hasClassName(inputList[i], "_calendar_yearly")) {
				if (form_el.display_type.value == this.CALENDAR_YEARLY) {
					inputList[i].disabled = false;
				} else {
					inputList[i].disabled = true;
				}
			}
			if (Element.hasClassName(inputList[i], "_calendar_weekly")) {
				if (form_el.display_type.value == this.CALENDAR_WEEKLY || form_el.display_type.value == this.CALENDAR_T_SCHEDULE || form_el.display_type.value == this.CALENDAR_U_SCHEDULE) {
					inputList[i].disabled = false;
				} else {
					inputList[i].disabled = true;
				}
			}
		}
		if (form_el.display_type.value == this.CALENDAR_T_SCHEDULE || form_el.display_type.value == this.CALENDAR_U_SCHEDULE) {
			form_el.display_count.disabled = false;
		} else {
			form_el.display_count.disabled = true;
		}
	},
	changeStyle: function(form_el) {
		commonCls.sendPost(this.id, 'action=calendar_action_edit_style&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},


	regAuth: function(form_el) {
		commonCls.sendPost(this.id, 'action=calendar_action_edit_auth&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},


	sendMail: function() {
		var params = new Object();
		params["param"] = "calendar_action_main_plan_mail";
		params["method"] = "post";
		params["top_el"] = $(this.id);
		commonCls.send(params);
	},
	changeMailSend: function(send) {
		$("calendar_mail_subject" + this.id).disabled = send;
		$("calendar_mail_body" + this.id).disabled = send;
		$("calendar_mail_authority1" + this.id).disabled = send;
		$("calendar_mail_authority2" + this.id).disabled = send;
		$("calendar_mail_authority3" + this.id).disabled = send;
	},
	regMail: function(form_el) {
		commonCls.sendPost(this.id, 'action=calendar_action_edit_mail&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},


	initRepeat: function() {
		this.calendarUntil = new compCalendar(this.id, "calendar_rrule_until"+this.id);
		this.switchRepeat();
	},
	switchRepeatBtn: function(el) {
		el.form.repeat_flag[0].checked = false;
		el.form.repeat_flag[1].checked = true;
		el.form.repeat_freq[0].checked = true;

		var repeat_el = Element.getParentElementByClassName(el, "calendar_repeat");
		var div_list = repeat_el.getElementsByTagName("div");
		for (var i=0; i<div_list.length; i++) {
			if (!Element.hasClassName(div_list[i], "_calendar_disp_repeat")) { continue; }
			commonCls.displayChange(div_list[i]);
		}
		this.switchRepeat();
	},
	switchRepeat: function(form_el, freq) {
		if (!form_el) {
			var form_el = $(this.id).getElementsByTagName("form")[0];
		}
		if (!freq) {
			var freq = "NONE";
			if (form_el.repeat_flag[1].checked) {
				for (var i=0; i < form_el.repeat_freq.length; i++) {
					if (form_el.repeat_freq[i].checked) {
						var freq = form_el.repeat_freq[i].value;
						break;
					}
				}
			}
		}
		var repeat_el = $("_calendar_repeat" + this.id);
		var div_list = repeat_el.getElementsByTagName("div");
		for (var i=0; i<div_list.length; i++) {
			if (!Element.hasClassName(div_list[i], "calendar_r_daily") &&
				!Element.hasClassName(div_list[i], "calendar_r_weekly") &&
				!Element.hasClassName(div_list[i], "calendar_r_monthly") &&
				!Element.hasClassName(div_list[i], "calendar_r_yearly") &&
				!Element.hasClassName(div_list[i], "calendar_terminator")) { continue; }

			var disabled = true;
			if (Element.hasClassName(div_list[i], "calendar_r_daily")) {
				if (freq == "DAILY") var disabled = false;
				var current_freq = "DAILY";
			}
			if (Element.hasClassName(div_list[i], "calendar_r_weekly")) {
				if (freq == "WEEKLY") var disabled = false;
				var current_freq = "WEEKLY";
			}
			if (Element.hasClassName(div_list[i], "calendar_r_monthly")) {
				if (freq == "MONTHLY") var disabled = false;
				var current_freq = "MONTHLY";
			}
			if (Element.hasClassName(div_list[i], "calendar_r_yearly")) {
				if (freq == "YEARLY") var disabled = false;
				var current_freq = "YEARLY";
			}
			if (Element.hasClassName(div_list[i], "calendar_terminator")) {
				if (freq != "NONE") var disabled = false;
				var current_freq = "TERM";
			}
			var input_el = div_list[i].getElementsByTagName("input");
			for (var j=0; j<input_el.length; j++) {
				if (freq != "NONE" && input_el[j].name == "repeat_freq") {input_el[j].disabled = false; continue;}
				input_el[j].disabled = disabled;
			}
			var select_el = div_list[i].getElementsByTagName("select");
			for (var j=0; j<select_el.length; j++) {
				select_el[j].disabled = disabled;
			}
			if (freq == "MONTHLY" && current_freq == "MONTHLY") {
				this.switchRruleMonthly(form_el);
			}
			if (freq != "NONE" && current_freq == "TERM") {
				this.switchTerm(form_el);
			}
		}
	},
	switchRruleMonthly: function(form_el) {
		if (form_el["rrule_byday[MONTHLY][]"].value == "" && form_el["rrule_bymonthday[MONTHLY][]"].value == "") {
			form_el["rrule_byday[MONTHLY][]"].disabled = false;
			form_el["rrule_bymonthday[MONTHLY][]"].disabled = false;
		} else if (form_el["rrule_byday[MONTHLY][]"].value != "") {
			form_el["rrule_byday[MONTHLY][]"].disabled = false;
			form_el["rrule_bymonthday[MONTHLY][]"].disabled = true;
		} else {
			form_el["rrule_byday[MONTHLY][]"].disabled = true;
			form_el["rrule_bymonthday[MONTHLY][]"].disabled = false;
		}
	},
	switchTerm: function(form_el) {
		if (form_el.rrule_term[0].checked) {
			form_el.rrule_count.disabled = false;
			form_el.rrule_until.disabled = true;
		} else {
			form_el.rrule_count.disabled = true;
			form_el.rrule_until.disabled = false;
		}
	},
	showRRuleEdit: function(this_el) {
		if (this.popup == null || !$(this.popup.popupID)) {
			this.popup = new compPopup(this.id, "_calendar_rrule" + this.id);
		}
		this.popup.loadObserver = this._focusRRuleEdit.bind(this);
		this.popup.showPopup(this.popup.getPopupElementByEvent(this_el), this_el);
	},
	_focusRRuleEdit: function() {
		var form = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		form.edit_rrule[0].focus();
		form.edit_rrule[0].select();
	},
	popupModifyPlan: function(event, form_el) {
		if (form_el.edit_rrule[2].checked) {
			this.showModifyPlan(event, form_el.rrule_calendar_id.value, form_el.edit_rrule[2].value);
		} else if (form_el.edit_rrule[1].checked) {
			this.showModifyPlan(event, form_el.calendar_id.value, form_el.edit_rrule[1].value);
		} else {
			this.showModifyPlan(event, form_el.calendar_id.value, form_el.edit_rrule[0].value);
		}
	},
	popupDeletePlan: function(form_el,confirm_mes) {
		if (form_el.edit_rrule[2].checked) {
			this.deletePlan(form_el.rrule_calendar_id.value, form_el.edit_rrule[2].value, confirm_mes);
		} else if (form_el.edit_rrule[1].checked) {
			this.deletePlan(form_el.calendar_id.value, form_el.edit_rrule[1].value, confirm_mes);
		} else {
			this.deletePlan(form_el.calendar_id.value, form_el.edit_rrule[0].value, confirm_mes);
		}
	},


	switchImportRoom: function() {
		var form_el = $("calendar_form" + this.id);
		var error_room_el = $("calendar_error_message_room" + this.id);
		if (form_el.plan_room_id.value == "-") {
			commonCls.displayVisible(error_room_el);
		} else {
			commonCls.displayNone(error_room_el);
		}
		if (form_el.plan_room_id.value != "-") {
			form_el.regist_btn.disabled = false;
		} else {
			form_el.regist_btn.disabled = true;
		}
	},
	takingICal: function(plan_room_id, all_del_flg) {
		if(all_del_flg){
			var all_del_flg = 1;
		}else{
			var all_del_flg = 0;
		}
		var top_el = $(this.id);
		var params = new Object();
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params["param"] = new Object();
		params["param"]["action"] = "calendar_action_edit_ical_taking";
		params["param"]["unique_id"] = 0;
		params["callbackfunc"] = function(file_list, res){
			commonCls.sendView(this.id, {"action":"calendar_view_edit_ical_import", "plan_room_id":plan_room_id, "all_del_flg":all_del_flg});
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	importICal: function(form_el) {
		commonCls.sendPost(this.id, 'action=calendar_action_edit_ical_import&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	setSelectRoom: function(form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res) {
			commonCls.removeBlock(this.id);
		}.bind(this);

		commonCls.frmAllSelectList(form_el, "not_enroll_room[]");
		commonCls.frmAllSelectList(form_el, "enroll_room[]");

		commonCls.sendPost(this.id, Form.serialize(form_el), params);
	}
}
