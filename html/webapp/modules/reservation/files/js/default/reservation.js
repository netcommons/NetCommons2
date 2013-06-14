var clsReservation = Class.create();
var reservationCls = Array();

clsReservation.prototype = {
	initialize: function(id) {
		this.id = id;

		this.RESERVATION_DEF_MONTHLY = "1";
		this.RESERVATION_DEF_WEEKLY = "2";
		this.RESERVATION_DEF_LOCATION = "3";

		this.RESERVATION_DEF_START_TIME_DEFAULT = "0";
		this.RESERVATION_DEF_START_TIME_FIXATION = "1";

		this.RESERVATION_DEF_H_INTERVAL = 60;
		this.RESERVATION_DEF_V_INTERVAL = 80;

		this.textarea = null;
		this.popup = null;
		this.reserveDate = null;
		this.reserveMoveDate = null;
		this.titleIcon = null;
		this.reserveUntil = null;

		this.today = null;
		this.disp_type = null;
		this.view_date = null;
		this.location_id = null;
		this.category_id = null;

		this.scrollTop = null;
		this.scrollLeft = null;

		this.message = new Object();
	},

	/*
	 * Common function
	 */
	onmouseImage: function(el, image, highlight) {
		if (el.tagName == "img") {
			var img_el = el;
		} else {
			var img_el = el.getElementsByTagName("img")[0];
		}
		if (highlight) {
			img_el.src = img_el.src.replace(image + ".gif", image + "_select.gif");
		} else {
			img_el.src = img_el.src.replace(image + "_select.gif", image + ".gif");
		}
	},
	showLocation: function(event, location_id) {
		var params = new Object();
		params["prefix_id_name"] = "popup_location" + location_id;
		params["action"] = "reservation_view_main_location_details";
		params["location_id"] = location_id;
		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},

	/*
	 * Function of the main
	 */
	changeReservation: function(view_date, disp_type, category_id, location_id) {
		if (!disp_type) {
			var disp_type = this.disp_type;
		}
		if (!view_date) {
			var view_date = this.view_date;
		}
		if (!category_id) {
			var category_id = this.category_id;
		}
		if (!location_id) {
			var location_id = this.location_id;
		}

		var params = new Object();
		params["action"] = "reservation_view_main_init";
		params["view_date"] = view_date;
		params["display_type"] = disp_type;
		if (disp_type == this.RESERVATION_DEF_MONTHLY || disp_type == this.RESERVATION_DEF_WEEKLY) {
			params["location_id"] = location_id;
		} else {
			params["category_id"] = category_id;
		}
		commonCls.sendView(this.id, params);

		if (disp_type == this.RESERVATION_DEF_MONTHLY) {
			this.scrollTop = null;
			this.scrollLeft = null;

		} else if (this.disp_type == this.RESERVATION_DEF_WEEKLY && disp_type == this.RESERVATION_DEF_LOCATION) {
			var scroll_el = $("reservation_time_frame" + this.id);
			if (scroll_el) {
				this.scrollTop = scroll_el.scrollTop;
				this.scrollLeft = scroll_el.scrollTop / this.RESERVATION_DEF_H_INTERVAL * this.RESERVATION_DEF_V_INTERVAL;
			}

		} else if (this.disp_type == this.RESERVATION_DEF_LOCATION && disp_type == this.RESERVATION_DEF_WEEKLY) {
			var scroll_el = $("reservation_time_frame" + this.id);
			if (scroll_el) {
				this.scrollTop = scroll_el.scrollLeft / this.RESERVATION_DEF_V_INTERVAL * this.RESERVATION_DEF_H_INTERVAL;
				this.scrollLeft = scroll_el.scrollLeft;
			}

		} else {
			var scroll_el = $("reservation_time_frame" + this.id);
			if (scroll_el) {
				this.scrollTop = scroll_el.scrollTop;
				this.scrollLeft = scroll_el.scrollLeft;
			}
		}

		this.disp_type = disp_type;
		this.view_date = view_date;
		this.category_id = category_id;
		this.location_id = location_id;
	},
	switchMainLocation: function(select_el) {
		if (select_el.value != "-") {
			this.changeReservation(null, null, null, select_el.value);
		} else {
			this.changeReservation(null, null, null, select_el.options[select_el.selectedIndex+1].value);
		}
	},

	/*
	 * Function of move reservation
	 */
	initMoveDate: function(id, input_date) {
		this.reserveMoveDate = new compCalendar(this.id, "reservation_move_date" + this.id);
		commonCls.closeCallbackFunc(this.id, function(){
			this.reserveMoveDate = null;
		}.bind(this));
		this.setMoveDate(id, input_date);
	},
	showDateMove: function(event) {
		var params = new Object();
		params["prefix_id_name"] = "popup_reservation_move";
		params["action"] = "reservation_view_main_movedate";
		params["view_date"] = this.view_date;
		params["display_type"] = this.disp_type;
		params["category_id"] = this.category_id;
		params["location_id"] = this.location_id;
		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},
	setMoveDate: function(id, input_date) {
		var top_el = $("reservation_popup_move" + this.id);
		if (!top_el) return;

		var img_list = top_el.getElementsByTagName("img");
		for (var i=0; i<img_list.length; i++) {
			if (!Element.hasClassName(img_list[i], "reservation_move_highlight")) { continue; }
			if (Element.hasClassName(img_list[i], "display-none")) { continue; }

			commonCls.displayNone(img_list[i]);
			commonCls.displayVisible(img_list[i].nextSibling);
			break;
		}

		var view_date = reservationCls[id].view_date;
		var disp_type = reservationCls[id].disp_type;
		var today = reservationCls[id].today;

		switch (disp_type) {
			case this.RESERVATION_DEF_MONTHLY:
				var event_el = $("reservation_anchor_monthly" + this.id);
				break;
			case this.RESERVATION_DEF_WEEKLY:
				var event_el = $("reservation_anchor_weekly" + this.id);
				break;
			case this.RESERVATION_DEF_LOCATION:
				var event_el = $("reservation_anchor_location" + this.id);
				break;
			default:
		}

		commonCls.displayNone(event_el);
		commonCls.displayVisible(event_el.previousSibling);

		var m_jump_el = $("reservation_jump_month" + this.id);
		if (disp_type == this.RESERVATION_DEF_MONTHLY) {
			commonCls.displayVisible(m_jump_el);
			var today_link_el = $("reservation_move_this_month" + this.id);
			if (view_date.substr(0,6) == today.substr(0,6)) {
				commonCls.displayNone(today_link_el);
				commonCls.displayVisible(today_link_el.nextSibling);
			} else {
				commonCls.displayVisible(today_link_el);
				commonCls.displayNone(today_link_el.nextSibling);
			}
			$("reservation_select_year" + this.id).value = view_date.substr(0,4);
			$("reservation_select_month" + this.id).value = view_date.substr(4,2);
		} else {
			commonCls.displayNone(m_jump_el);
		}

		var l_jump_el = $("reservation_jump_date" + this.id);
		if (disp_type == this.RESERVATION_DEF_WEEKLY || disp_type == this.RESERVATION_DEF_LOCATION) {
			commonCls.displayVisible(l_jump_el);
			var today_link_el = $("reservation_move_today" + this.id);
			if (view_date == today) {
				commonCls.displayNone(today_link_el);
				commonCls.displayVisible(today_link_el.nextSibling);
			} else {
				commonCls.displayVisible(today_link_el);
				commonCls.displayNone(today_link_el.nextSibling);
			}

			$("reservation_move_date" + this.id).value = input_date;
		} else {
			commonCls.displayNone(l_jump_el);
		}

		var category_div_el = $("reservation_category_div" + this.id);
		var location_div_el = $("reservation_location_div" + this.id);
		if (disp_type == this.RESERVATION_DEF_MONTHLY || disp_type == this.RESERVATION_DEF_WEEKLY) {
			commonCls.displayNone(category_div_el);
			commonCls.displayVisible(location_div_el);
		} else {
			commonCls.displayVisible(category_div_el);
			commonCls.displayNone(location_div_el);
		}

		$("reservation_switch_category" + this.id).value = reservationCls[id].category_id;
		$("reservation_switch_location" + this.id).value = reservationCls[id].location_id;

		var location_details = $("reservation_location_details" + id);
		if (location_details) {
			$("reservation_switch_location" + this.id).nextSibling.title = location_details.title;
		}
	},
	moveDate: function(form_el) {
		var id = form_el.id.value;

		var params = new Object();
		params["action"] = "reservation_view_main_init";
		params["display_type"] = reservationCls[id].disp_type;
		if (params["display_type"] == this.RESERVATION_DEF_MONTHLY) {
			params["move_year"] = form_el.move_year.value;
			params["move_month"] = form_el.move_month.value;
		} else {
			params["move_date"] = form_el.move_date.value;
		}
		if (params["display_type"] == this.RESERVATION_DEF_MONTHLY || params["display_type"] == this.RESERVATION_DEF_WEEKLY) {
			params["location_id"] = $("reservation_switch_location" + this.id).value;
		} else {
			params["category_id"] = $("reservation_switch_category" + this.id).value;
		}
		commonCls.sendView(id, params);
	},


	/*
	 * Function of the reservation
	 */
	showEasyAddReserve: function(event, date, time, location_id, timeframe_id) {
		var params = new Object();
		params["prefix_id_name"] = "popup_regist_reservation";
		params["action"] = "reservation_view_main_reserve_add";
		params["date"] = date;
		if (this.disp_type == this.RESERVATION_DEF_MONTHLY || this.disp_type == this.RESERVATION_DEF_WEEKLY) {
			params["location_id"] = this.location_id;
		} else {
			params["category_id"] = this.category_id;
			params["location_id"] = location_id;
		}
		if (time) {
			params["time"] = time;
		}
		if (timeframe_id) {
			params["timeframe_id"] = timeframe_id;
		}
		commonCls.sendPopupView(event, params, {"top_el":$(this.id),"modal_flag":true});
	},
	showReserveDetails: function(event, reserve_id) {
		var params = new Object();
		params["prefix_id_name"] = "popup_reservation_" + reserve_id;
		params["action"] = "reservation_view_main_reserve_details";
		params["reserve_id"] = reserve_id;

		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},
	showAddReserve: function(event, form_el) {
		var params_str = "action=reservation_view_main_reserve_add&details_flag=1&" + Form.serialize(form_el) + "&prefix_id_name=popup_regist_reservation";
		if (form_el.allday_flag.checked) {
			params_str += "&start_hour=" + form_el.start_hour.value + "&start_minute=" + form_el.start_minute.value;
			params_str += "&end_hour=" + form_el.end_hour.value + "&end_minute=" + form_el.end_minute.value;
		}

		var absolute_el = $(this.id).parentNode;

		var optParams = new Object();
		optParams["callbackfunc"] = function(){commonCls.moveAutoPosition(this);}.bind(absolute_el);
		commonCls.sendView(this.id, params_str, optParams);
	},
	showModifyReserve: function(event, reserve_id, edit_rrule) {
		var params = new Object();
		params["prefix_id_name"] = "popup_regist_reservation";
		params["action"] = "reservation_view_main_reserve_modify";
		params["reserve_id"] = reserve_id;
		params["edit_rrule"] = edit_rrule;

		var optParams = new Object();
		optParams["top_el"] = $(this.id);
		optParams["modal_flag"] = true;
		optParams["center_flag"] = true;
		optParams["callbackfunc"] = function(){commonCls.removeBlock(this.id)}.bind(this);
		commonCls.sendPopupView(event, params, optParams);
	},

	initReserve: function(details_flag) {
		var top_el = $(this.id);
		this.titleIcon = null;
		var form_el = top_el.getElementsByTagName("form")[0];

		this.displayReserveBtn($("reservation_form"+this.id), $("reservation_location_select"+this.id));
		this.displayReserveBtn($("reservation_form"+this.id), $("reservation_reserve_flag"+this.id));
		if (details_flag) {
			this.textarea = new compTextarea();
			this.textarea.uploadAction = {
				image    : "reservation_action_main_reserve_upload_image",
				file     : "reservation_action_main_reserve_upload_file"
			};
			this.textarea.textareaShow(this.id, "comptextarea", "full");

			this.reserveDate = new compCalendar(this.id, "reserve_date"+this.id);
			commonCls.closeCallbackFunc(this.id, function(){
				this.reserveDate = null;
				this.textarea = null;
			}.bind(this));
		} else {
			this.reserveDate = new compCalendar(this.id, "reserve_date"+this.id);
			commonCls.closeCallbackFunc(this.id, function(){
				this.reserveDate = null;
			}.bind(this));
		}
	},
	displayReserveBtn: function(form_el, el) {
		var details_el = $("reservation_details" + this.id);
		var error_el = $("reservation_reserve_error" + this.id);
		if (el.tagName == "SELECT" && el.value != "-") {
			form_el.regist.disabled = false;
			if (details_el) {
				commonCls.displayVisible(details_el);
				commonCls.displayNone(details_el.nextSibling);
			}
			if (error_el) {
				commonCls.displayNone(error_el);
			}
		} else {
			form_el.regist.disabled = true;
			if (details_el) {
				commonCls.displayNone(details_el);
				commonCls.displayVisible(details_el.nextSibling);
			}
			if (error_el) {
				commonCls.displayVisible(error_el);
			}
		}
	},
	switchReserveCategory: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "reservation_view_main_reserve_switch_category" +
							"&category_id=" + form_el.category_id.value + "&location_id=" + form_el.location_id.value;
		params["top_el"] = top_el;
		params["callbackfunc"] = function(res){
			var el = $("reservation_location_select" + this.id);
			el.parentNode.innerHTML = res;

			var el = $("reservation_location_select" + this.id);
			this.displayReserveBtn(form_el, el);
			if (el.tagName == "SELECT") {
				this.switchReserveLocation(form_el);
			} else {
				var el = $("reservation_reserve_flag" + this.id);
				commonCls.displayNone(el);
			}
		}.bind(this);
		commonCls.send(params);
	},
	switchReserveLocation: function(form_el) {
		var top_el = $(this.id);

		var timetables_el = $("reservation_location_timetables" + this.id);
		var timetable_list = timetables_el.getElementsByTagName("div");
		for (var i = 0; i<timetable_list.length; i++) {
			if (!Element.hasClassName(timetable_list[i], "display-none")) {
				Element.addClassName(timetable_list[i], "display-none");
				break;
			}
		}
		var el = $("reservation_location_id" + form_el.location_id.value + this.id);
		Element.removeClassName(el, "display-none");

		var params = new Object();
		params["param"] = "reservation_view_main_reserve_switch_location" +
							"&location_id=" + form_el.location_id.value + "&reserve_room_id=" + form_el.reserve_room_id.value;
		params["top_el"] = top_el;
		params["callbackfunc"] = function(res){
			var el = $("reservation_reserve_flag" + this.id);
			commonCls.displayVisible(el);
			el.parentNode.innerHTML = res;

			var el = $("reservation_reserve_flag" + this.id);
			this.displayReserveBtn(form_el, el);
		}.bind(this);
		commonCls.send(params);
	},
	showIcon: function() {
		if (this.titleIcon == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("icon_name_img" + this.id), $("icon_name_hidden" + this.id));
	},

	popupModifyReserve: function(event, form_el) {
		if (form_el.edit_rrule[2].checked) {
			this.showModifyReserve(event, form_el.rrule_reserve_id.value, form_el.edit_rrule[2].value);
		} else if (form_el.edit_rrule[1].checked) {
			this.showModifyReserve(event, form_el.reserve_id.value, form_el.edit_rrule[1].value);
		} else {
			this.showModifyReserve(event, form_el.reserve_id.value, form_el.edit_rrule[0].value);
		}
	},
	popupDeleteReserve: function(id, form_el, confirm_mes) {
		if (form_el.edit_rrule[2].checked) {
			this.deleteReserve(id, form_el.rrule_reserve_id.value, form_el.edit_rrule[2].value, confirm_mes);
		} else if (form_el.edit_rrule[1].checked) {
			this.deleteReserve(id, form_el.reserve_id.value, form_el.edit_rrule[1].value, confirm_mes);
		} else {
			this.deleteReserve(id, form_el.reserve_id.value, form_el.edit_rrule[0].value, confirm_mes);
		}
	},

	addReserve: function(id, form_el, details_flag) {

		this._switchTimeframeDisable(form_el, false);

		if (details_flag == "1") {
			var description = this.textarea.getTextArea();
			var params_str = "action=reservation_action_main_reserve_add&details_flag=1&" + Form.serialize(form_el) +
							"&description=" + encodeURIComponent(description);
		} else {
			var params_str = "action=reservation_action_main_reserve_add&" + Form.serialize(form_el);
		}

		var params = new Object();
		params["callbackfunc"] = function(res){
			if (form_el.notification_mail != undefined && form_el.notification_mail.checked) {
				this.sendMail();
			}
			reservationCls[id].changeReservation();
			commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(res) {
			this._switchTimeframeDisable(form_el, true);
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendPost(this.id, params_str, params);
	},
	modifyReserve: function(id, form_el) {

		this._switchTimeframeDisable(form_el, false);

		var description = this.textarea.getTextArea();
		var params_str = "action=reservation_action_main_reserve_modify&" + Form.serialize(form_el) +
						"&description=" + encodeURIComponent(description);

		var params = new Object();
		params["callbackfunc"] = function(res){
			if (form_el.notification_mail != undefined && form_el.notification_mail.checked) {
				this.sendMail();
			}
			reservationCls[id].changeReservation();
			commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(res) {
			this._switchTimeframeDisable(form_el, true);
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendPost(this.id, params_str, params);
	},
	deleteReserve: function(id, reserve_id, edit_rrule, confirm_mes) {
		if (!commonCls.confirm(confirm_mes)) { return; }

		var params = new Object();
		params["callbackfunc"] = function(res){
			reservationCls[id].changeReservation();
			commonCls.removeBlock(this.id);
		}.bind(this);

		commonCls.sendPost(this.id, "action=reservation_action_main_reserve_delete&reserve_id=" + reserve_id +
									"&edit_rrule=" + edit_rrule, params);
	},
	_switchTimeframeDisable: function(form_el, opeflag) {
		if(form_el.start_timeframe && form_el.end_timeframe) {
			if($F(form_el.start_timeframe).length != 0) {
				form_el.start_hour.disabled = opeflag;
				form_el.start_minute.disabled = opeflag;
			}
			if($F(form_el.end_timeframe).length != 0) {
				form_el.end_hour.disabled = opeflag;
				form_el.end_minute.disabled = opeflag;
			}
		}
	},
	sendMail: function() {
		commonCls.sendPost(this.id, "action=reservation_action_main_reserve_mail", {"loading_el":null});
	},

	/*
	 * Function of repetition reserve registration
	 */
	initRepeat: function() {
		this.reserveUntil = new compCalendar(this.id, "reservation_rrule_until"+this.id);
		this.switchRepeat();
	},
	switchRepeatBtn: function(el) {
		el.form.repeat_flag[0].checked = false;
		el.form.repeat_flag[1].checked = true;

		el.form.repeat_freq[0].checked = true;

		var repeat_el = Element.getParentElementByClassName(el, "reservation_repeat");
		var div_list = repeat_el.getElementsByTagName("div");
		for (var i=0; i<div_list.length; i++) {
			if (!Element.hasClassName(div_list[i], "reservation_disp_repeat")) { continue; }
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
		var repeat_el = $("reservation_repeat" + this.id);
		var div_list = repeat_el.getElementsByTagName("div");
		for (var i=0; i<div_list.length; i++) {
			if (!Element.hasClassName(div_list[i], "reservation_r_daily") &&
				!Element.hasClassName(div_list[i], "reservation_r_weekly") &&
				!Element.hasClassName(div_list[i], "reservation_r_monthly") &&
				!Element.hasClassName(div_list[i], "reservation_r_yearly") &&
				!Element.hasClassName(div_list[i], "reservation_terminator")) { continue; }

			var disabled = true;
			if (Element.hasClassName(div_list[i], "reservation_r_daily")) {
				if (freq == "DAILY") var disabled = false;
				var current_freq = "DAILY";
			}
			if (Element.hasClassName(div_list[i], "reservation_r_weekly")) {
				if (freq == "WEEKLY") var disabled = false;
				var current_freq = "WEEKLY";
			}
			if (Element.hasClassName(div_list[i], "reservation_r_monthly")) {
				if (freq == "MONTHLY") var disabled = false;
				var current_freq = "MONTHLY";
			}
			if (Element.hasClassName(div_list[i], "reservation_r_yearly")) {
				if (freq == "YEARLY") var disabled = false;
				var current_freq = "YEARLY";
			}
			if (Element.hasClassName(div_list[i], "reservation_terminator")) {
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

	showRepeatReserve: function(this_el) {
		if (this.popup == null || !$(this.popup.popupID)) {
			this.popup = new compPopup(this.id, "reservation_rrule" + this.id);
		}
		this.popup.loadObserver = this._focusRepeatReserve.bind(this);
		this.popup.showPopup(this.popup.getPopupElementByEvent(this_el), this_el);
	},
	_focusRepeatReserve: function() {
		var form = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		form.edit_rrule[0].focus();
		form.edit_rrule[0].select();
	},


	/*
	 * Function of the weekly reservaton
	 */
	setTimeFocus: function(time_str) {
		var top_el = $(this.id);
		var scroll_el = $("reservation_time_frame" + this.id);

		if (this.disp_type == this.RESERVATION_DEF_WEEKLY) {
			if (this.scrollTop) {
				scroll_el.scrollTop = this.scrollTop;
			} else {
				var time_el = Element.getChildElementByClassName(scroll_el, "reservation_time_height");
				scroll_el.scrollTop = time_el.offsetHeight * time_str.substr(0,2);
				if (scroll_el.scrollTop > 0) {
					scroll_el.scrollTop = scroll_el.scrollTop - 5;
				}
				this.scrollTop = scroll_el.scrollTop;
			}
		} else if (this.disp_type == this.RESERVATION_DEF_LOCATION) {
			if (this.scrollLeft) {
				scroll_el.scrollLeft = this.scrollLeft;
			} else {
				var time_el = Element.getChildElementByClassName(scroll_el, "reservation_time_width");
				scroll_el.scrollLeft = time_el.offsetWidth * time_str.substr(0,2);
				if (scroll_el.scrollLeft > 0) {
					scroll_el.scrollLeft = scroll_el.scrollLeft;
				}
				this.scrollLeft = scroll_el.scrollLeft;
			}
			var scroll_parent_el = scroll_el.parentNode;
			scroll_el.style.height = scroll_parent_el.offsetHeight + "px";
		}
	},
	switchAllday: function(form_el) {
		if (form_el.allday_flag.checked) {
			form_el.start_hour.disabled = true;
			form_el.start_minute.disabled = true;
			form_el.end_hour.disabled = true;
			form_el.end_minute.disabled = true;
			if(form_el.start_timeframe) {
				form_el.start_timeframe.disabled = true;
			}
			if(form_el.end_timeframe) {
				form_el.end_timeframe.disabled = true;
			}
		} else {
			form_el.start_hour.disabled = false;
			form_el.start_minute.disabled = false;
			form_el.end_hour.disabled = false;
			form_el.end_minute.disabled = false;
			if(form_el.start_timeframe) {
				form_el.start_timeframe.disabled = false;
			}
			if(form_el.end_timeframe) {
				form_el.end_timeframe.disabled = false;
			}
		}
		this.switchTime24Reserve(form_el);
	},
	switchTime24Reserve: function(form_el) {
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


	/*
	 * Function of the display method change
	 */
	switchDefaultCategory: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = "reservation_view_edit_style_switchcate&category_id=" + form_el.category_id.value + "&location_id=" + form_el.location_id.value;
		params["top_el"] = top_el;
		params["callbackfunc"] = function(res){
			var el = $("reservation_location_select" + this.id);
			el.parentNode.innerHTML = res;
			this.switchDispType(form_el);
		}.bind(this);
		commonCls.send(params);
	},
	switchDispType: function(form_el) {
		var el = $("reservation_location_select" + this.id);
		if (form_el.display_type.value == this.RESERVATION_DEF_LOCATION) {
			if (el.tagName == "SELECT") {
				form_el.location_id.disabled = true;
				form_el.regist.disabled = false;
				var parent_el = Element.getParentElementByClassName(el, "reservation_style_location_tr");
				commonCls.displayNone(parent_el);
			} else {
				var parent_el = Element.getParentElementByClassName(el, "reservation_style_location_tr");
				commonCls.displayVisible(parent_el);

				var parent_el = Element.getParentElementByClassName(el, "reservation_style_location_td");
				commonCls.displayNone(parent_el.previousSibling.firstChild);
				form_el.regist.disabled = true;
			}
		} else {
			if (el.tagName == "SELECT") {
				form_el.location_id.disabled = false;
				form_el.regist.disabled = false;
			} else {
				form_el.regist.disabled = true;
			}
			var parent_el = Element.getParentElementByClassName(el, "reservation_style_location_tr");
			commonCls.displayVisible(parent_el);

			var parent_el = Element.getParentElementByClassName(el, "reservation_style_location_td");
			commonCls.displayVisible(parent_el.previousSibling.firstChild);
		}
	},
	switchStartTime: function(form_el) {
		if (form_el.start_time_type[1].checked) {
			form_el.start_time_fixation.disabled = false;
		} else {
			form_el.start_time_fixation.disabled = true;
		}
	},
	switchTimeframe: function(el, form_el, tgt_str) {
		var times_str = $F(el);
		var tgt_hour_el = $("reservation_" + tgt_str + "_hour" + this.id);
		var tgt_minute_el = $("reservation_" + tgt_str + "_minute" + this.id);

		if(times_str.length == 0) {
			tgt_hour_el.disabled = false;
			tgt_minute_el.disabled = false;
		}
		else {
			var times = $F(el).split('|');
			Form.Element.SetSerializers.select(tgt_hour_el, times[0]);
			Form.Element.SetSerializers.select(tgt_minute_el, times[1]);
			tgt_hour_el.disabled = true;
			tgt_minute_el.disabled = true;
/*
			if(tgt_str=="start") {
				if($F("reservation_end_timeframe"+this.id).length == 0) {
					$("reservation_end_timeframe"+this.id).selectedIndex = el.selectedIndex;
					this.switchTimeframe($("reservation_end_timeframe"+this.id), form_el, "end");
				}
			}
*/
		}
	},
	changeStyle: function(form_el) {
		this.scrollTop = null;
		this.scrollLeft = null;
		commonCls.sendPost(this.id, 'action=reservation_action_edit_style&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	/*
	 * Function of location registration
	 */
	initLocationRegist: function(add_auth_id) {
		var form_el = $("reservation_form" + this.id);
		form_el["add_authority[" + add_auth_id + "]"].checked = true;

		commonCls.changeAuthority(form_el["add_authority[" + add_auth_id + "]"], this.id);

		this.switchLocationRoom(form_el.allroom_flag);
		this.switchLocationAlldayTime(form_el);

		var top_el = $(this.id);
		this.textarea = new compTextarea();
		this.textarea.uploadAction = {
			image    : "reservation_action_edit_location_upload_image",
			file     : "reservation_action_edit_location_upload_file"
		};
		this.textarea.textareaShow(this.id, "comptextarea", "full");
	},
	switchLocationTime24: function(form_el) {
		if (form_el.allday_flag.checked) {
			form_el.end_minute.disabled = true;
		} else if (form_el.end_hour.value == "24") {
			form_el.end_minute.disabled = true;
			form_el.end_minute.value = "00";
		} else {
			form_el.end_minute.disabled = false;
		}
	},
	switchLocationAlldayTime: function(form_el) {
		if (form_el.allday_flag.checked) {
			form_el.start_hour.disabled = true;
			form_el.start_minute.disabled = true;
			form_el.end_hour.disabled = true;
			form_el.end_minute.disabled = true;
		} else {
			form_el.start_hour.disabled = false;
			form_el.start_minute.disabled = false;
			form_el.end_hour.disabled = false;
			this.switchLocationTime24(form_el);
		}
	},
	switchLocationRoom: function(allroom_el) {
		var rooms_el = $("reservation_room_select" + this.id);
		var room_list = rooms_el.getElementsByTagName("div");
		for (var i = 0; i<room_list.length; i++) {
			var input_el = room_list[i].getElementsByTagName("input")[0];
			var mark_el = room_list[i].getElementsByTagName("img")[0];
			var classNames = mark_el.className.split(" ");
			if (allroom_el.checked) {
				input_el.disabled = true;
				if (Element.hasClassName(room_list[i], classNames[1])) {
					Element.removeClassName(room_list[i], classNames[1]);
				}
				if (!Element.hasClassName(mark_el, "reservation_disable")) {
					Element.addClassName(mark_el, "reservation_disable");
				}
				if (!Element.hasClassName(room_list[i], "disable_lbl")) {
					Element.addClassName(room_list[i], "disable_lbl");
				}
			} else {
				input_el.disabled = false;
				if (!Element.hasClassName(room_list[i], classNames[1])) {
					Element.addClassName(room_list[i], classNames[1]);
				}
				if (Element.hasClassName(mark_el, "reservation_disable")) {
					Element.removeClassName(mark_el, "reservation_disable");
				}
				if (Element.hasClassName(room_list[i], "disable_lbl")) {
					Element.removeClassName(room_list[i], "disable_lbl");
				}
			}
		}
	},
	addLocation: function(form_el) {
		var description = this.textarea.getTextArea();
		commonCls.sendPost(this.id, 'action=reservation_action_edit_location_add&' +
									Form.serialize(form_el) + "&description=" + encodeURIComponent(description), {"target_el":$(this.id)});
	},
	showModifyLocation: function(event, location_id) {
		var params = new Object();
		params["action"] = "reservation_view_edit_location_modify";
		params["location_id"] = location_id;
		commonCls.sendView(this.id, params);
	},
	modifyLocation: function(form_el, top_el) {
		var description = this.textarea.getTextArea();
		commonCls.sendPost(this.id, 'action=reservation_action_edit_location_modify&' +
									Form.serialize(form_el) + "&description=" + encodeURIComponent(description), {"target_el":$(this.id)});
	},

	/*
	 * Function of location management
	 */
	initLocation: function() {
		var top_el = $(this.id);
		this.dndDraggable = Class.create();
		this.dndDraggable.prototype = Object.extend((new compDraggable), {
			prestartDrag: function() {
				var htmlElement = this.getHTMLElement();
				this._displayChg(htmlElement);
			},
			cancelDrag: function() {
				var draggable = this.htmlElement;
				Element.setStyle(draggable, {opacity:""});
				this._displayChg(draggable, 1);
			},
			_displayChg: function(htmlElement, cancel_flag) {
	    		var reservationCls = this.getParams();
		    	if (!Element.hasClassName(htmlElement, "reservation_move_category_block")) { return; }

	    		var d_top_el = $(reservationCls.id);
	    		var fields = Element.getElementsByClassName(d_top_el, "reservation_category_display");

				if (cancel_flag) {
					fields.each(function(el) {
						commonCls.displayVisible(el);
					}.bind(this));
					return;
				} else {
					fields.each(function(el) {
						commonCls.displayNone(el);
					}.bind(this));
				}

				var top_row_el = Element.getChildElementByClassName(htmlElement, "reservation_category_display");
				commonCls.displayVisible(top_row_el);
		    }
		});


		this.dndDropzone = Class.create();
		this.dndDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				var params = this.getParams();
				var htmlElement = this.getHTMLElement();
				if (this._showHover(htmlElement)) {
					return;
				}
				if (params[1] && !Element.hasClassName(htmlElement, "reservation_move_category_block")) {
					this.showChgSeqHoverInside(event);
				} else {
					this.showChgSeqHover(event);
				}
			},
			hideHover: function(event) {
				this.hideChgSeqHover(event);
			},
			accept: function(draggableObjects) {
				this.acceptChgSeq(draggableObjects);
				var drag_el = draggableObjects[0].getHTMLElement();
				commonCls.blockNotice(null, drag_el);
			},
			save: function(draggableObjects) {
				if(this.ChgSeqPosition == null) {
					return false;
				}
				var htmlElement = this.getHTMLElement();
				var params = this.getParams();
				var reservationCls = params[0];
				var d_top_el = $(reservationCls.id);
		    	var drag_el = draggableObjects[0].getHTMLElement();

				if (Element.hasClassName(drag_el, "reservation_move_location_block")) {
					var drag_location_id = drag_el.className.match(/reservation_location_id[0-9]+/i)[0].replace("reservation_location_id", "");
					var params_str = "action=reservation_action_edit_location_sequence" +
									"&position=" + this.ChgSeqPosition +
									"&drag_location_id=" + drag_location_id;

					if (Element.hasClassName(htmlElement, "reservation_move_category")) {
						var drop_category_id = htmlElement.parentNode.className.match(/reservation_category_id[0-9]+/i)[0].replace("reservation_category_id", "");
						params_str += "&drop_category_id=" + drop_category_id;
					} else {
						var drop_location_id = htmlElement.className.match(/reservation_location_id[0-9]+/i)[0].replace("reservation_location_id", "");
						params_str += "&drop_location_id=" + drop_location_id;
					}

				} else {
		    		var fields = Element.getElementsByClassName(d_top_el, "reservation_category_display");
					fields.each(function(el) {
						commonCls.displayVisible(el);
					}.bind(this));

					var drag_category_id = drag_el.className.match(/reservation_category_id[0-9]+/i)[0].replace("reservation_category_id", "");
					var drop_category_id = htmlElement.className.match(/reservation_category_id[0-9]+/i)[0].replace("reservation_category_id", "");

					var params_str = "action=reservation_action_edit_category_sequence" +
									"&position=" + this.ChgSeqPosition +
									"&drag_category_id=" + drag_category_id +
									"&drop_category_id=" + drop_category_id;
				}
				commonCls.sendPost(reservationCls.id, params_str);
				return true;
			}
		});

		var range_el = $("reservation" + this.id);

		this.dndCategory = new compDragAndDrop();
		this.dndCategory.registerDraggableRange(range_el);

		this.dndLocation = new compDragAndDrop();
		this.dndLocation.registerDraggableRange(range_el);

		var fields = Element.getElementsByClassName(range_el, "reservation_move_category_block");
		fields.each(function(field_el) {
			var cate_el = field_el.childNodes[0];
			var move_el = Element.getChildElementByClassName(cate_el, "reservation_move_image");

			this.dndCategory.registerDropZone(new this.dndDropzone(field_el, {0:this,1:false}));
			this.dndCategory.registerDraggable(new this.dndDraggable(field_el, move_el, this));
			this.dndLocation.registerDropZone(new this.dndDropzone(cate_el, {0:this,1:true}));
		}.bind(this));

		var fields = Element.getElementsByClassName(range_el, "reservation_move_location_block");
		fields.each(function(field_el) {
			var move_el = Element.getChildElementByClassName(field_el, "reservation_move_image");

			this.dndLocation.registerDropZone(new this.dndDropzone(field_el, {0:this,1:false}));
			this.dndLocation.registerDraggable(new this.dndDraggable(field_el, move_el, this));
		}.bind(this));

	},
	showPopup: function(el) {
		if (this.popup == null || !$(this.popup.popupID)) {
			this.popup = new compPopup(this.id, this.id);
		}
		this.popup.loadObserver = this._focusPopup.bind(this);
		this.popup.showPopup(this.popup.getPopupElementByEvent(el), el);
	},
	_focusPopup: function() {
		var form_el = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		commonCls.focus(form_el);
	},
	addCategory: function(form_el) {
		var params_str = "action=reservation_action_edit_category_add&" + Form.serialize(form_el);
		commonCls.sendPost(this.id, params_str, {"target_el": $(this.id)});

		this.popup.closePopup();
	},
	deleteCategory: function(category_id) {
		var category_name = $("reservation_category_name" + category_id + this.id).innerHTML;
		var confirm_mess = this.message["del_confirm"].replace("%s", category_name.unescapeHTML());

		if (commonCls.confirm(confirm_mess)) {
			var params_str = "action=reservation_action_edit_category_delete&category_id=" + category_id;
			commonCls.sendPost(this.id, params_str, {"target_el": $(this.id)});
		}
	},
	deleteLocation: function(location_id) {
		var location_name = $("reservation_location_name" + location_id + this.id).innerHTML;
		var confirm_mess = this.message["del_confirm"].replace("%s", location_name.unescapeHTML());

		if (commonCls.confirm(confirm_mess)) {
			var params_str = "action=reservation_action_edit_location_delete&location_id=" + location_id;
			commonCls.sendPost(this.id, params_str, {"target_el": $(this.id)});
		}
	},
	showRenameInput: function(el) {
		commonCls.displayNone(el);
		commonCls.displayVisible(el.nextSibling);
		commonCls.focus(el.nextSibling);
	},
	renameCategory: function(event, el, category_id) {
		if (event.keyCode != 13) { return; }

		var params_str = "action=reservation_action_edit_category_rename&category_id=" + category_id + "&category_name=" +  encodeURIComponent(el.value);

		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
		}
		params["callbackfunc"] = function(res){
			//var link_el = $("reservation_category_delete_" + category_id + this.id);
			//link_el.title = this.message["del_title"].replace("%s", res);

			commonCls.displayNone(el);
			var prev_el = el.previousSibling
			prev_el.innerHTML = res.escapeHTML();
			commonCls.displayVisible(prev_el);
		}.bind(this);

		commonCls.sendPost(this.id, params_str, params);
	},
	renameLocation: function(event, el, location_id) {
		if (event.keyCode != 13) { return; }

		var params_str = "action=reservation_action_edit_location_rename&location_id=" + location_id + "&location_name=" +  encodeURIComponent(el.value);

		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
		}
		params["callbackfunc"] = function(res){
			//var link_el = $("reservation_location_details_" + location_id + this.id);
			//link_el.title = this.message["details_title"].replace("%s", res);

			//var link_el = $("reservation_location_edit_" + location_id + this.id);
			//link_el.title = this.message["edit_title"].replace("%s", res);

			//var link_el = $("reservation_location_delete_" + location_id + this.id);
			//link_el.title = this.message["del_title"].replace("%s", res);

			commonCls.displayNone(el);
			var prev_el = el.previousSibling
			prev_el.innerHTML = res.escapeHTML();
			commonCls.displayVisible(prev_el);
		}.bind(this);

		commonCls.sendPost(this.id, params_str, params);
	},

	/*
	 * Timeframe functions
	 */
	switchTimeframeTime24: function(form_el) {
		if (form_el.end_hour.value == "24") {
			form_el.end_minute.disabled = true;
			form_el.end_minute.value = "00";
		} else {
			form_el.end_minute.disabled = false;
		}
	},

	switchTimeframeColor: function(el, form_el) {
		var color = form_el.timeframe_color.value;
		Element.setStyle(el, {"backgroundColor":color});
	},

	setTimeframe: function(form_el, block_id) {
		var target_el = $("_"+block_id);
		var params = new Object();
		params["callbackfunc"] = function(res){
			commonCls.sendView(target_el, "action=reservation_view_edit_timeframe" );
			commonCls.removeBlock(this.id);
		}.bind(this);
		commonCls.sendPost(this.id, 'action=reservation_action_edit_timeframe_entry&' +
									Form.serialize(form_el) , params);
	},

	/*
	 * Function of mail registration
	 */
	changeMailSend: function(send) {
		$("reservation_mail_subject" + this.id).disabled = send;
		$("reservation_mail_body" + this.id).disabled = send;
		$("reservation_mail_authority1" + this.id).disabled = send;
		$("reservation_mail_authority2" + this.id).disabled = send;
		$("reservation_mail_authority3" + this.id).disabled = send;
	},
	regMail: function(form_el) {
		commonCls.sendPost(this.id, 'action=reservation_action_edit_mail&' + Form.serialize(form_el), {"target_el":$(this.id)});
	},

	importReserve: function(form_el, confirmMessage1, confirmMessage2) {
		if (form_el.undo_import.checked) {
			if (form_el.reserve_room_id.value == "0") {
				if (!commonCls.confirm(confirmMessage1)) { return; }
			} else {
				if (!commonCls.confirm(confirmMessage2)) { return; }
			}
		}

		var top_el = $(this.id);
		var params = new Object();
		params["method"] = "post";
		params["loading_el"] = top_el;
		params["top_el"] = top_el;
		params['form_prefix'] = "attachment_form";

		params["param"] = {
			"action": "reservation_action_edit_import",
			"location_id":form_el.location_id.value,
			"reserve_room_id":form_el.reserve_room_id.value
		};
		params["param"]["unique_id"] = 0;
		if (form_el.undo_import.checked) {
			params["param"]["undo_import"] = 1;
		} else {
			params["param"]["undo_import"] = 0;
		}
		if (form_el.title_duplication.checked) {
			params["param"]["title_duplication"] = 1;
		} else {
			params["param"]["title_duplication"] = 0;
		}

		params["callbackfunc"] = function(file_list, res){
			commonCls.alert(res);
			commonCls.sendView(this.id, 'reservation_view_main_init', {'target_el': $(this.id)});
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			var el = $("reservation_error" + this.id);
			el.innerHTML = res;
			commonCls.displayVisible(el);
		}.bind(this);
		commonCls.sendAttachment(params);
	}
}
