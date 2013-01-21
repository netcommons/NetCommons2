var clsHoliday = Class.create();
var holidayCls = Array();

clsHoliday.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	showHoliday: function(line_num, count, year) {
		if (count == 0) { return; }
		var opts = {
			prefetchBuffer : false,
			sort : true
		};
		this.gridList = new compLiveGrid(this.id, line_num, count, "holiday_view_admin_list", opts);
	},
	switchHoliday: function(form_el) {
		var top_el = $(this.id);
		var switch_holiday_params = {"action":"holiday_view_admin_init", "year":form_el.select_year.value, "lang":form_el.select_lang.value};
		commonCls.sendView(this.id, switch_holiday_params);
	},
	showAddHoliday: function(event, id, year) {
		var top_el = $(this.id);
		var addholiday_params = new Object();
		
		addholiday_params["prefix_id_name"] = "popup_holiday";
		addholiday_params["action"] = "holiday_view_admin_add";
		addholiday_params["year"] = year;
		commonCls.sendPopupView(event, addholiday_params, {"top_el":top_el,"modal_flag":true});
	},
	switchHolidayType: function(form_el) {
		if (form_el.varidable_flag[0].checked) {
			form_el.day.disabled = false;
			form_el.week.disabled = true;
			form_el.wday.disabled = true;
			form_el.substitute_flag.disabled = false;
			var substitute_str_el = form_el.substitute_flag.nextSibling;
			if (Element.hasClassName(substitute_str_el, "disable_lbl")) {
				Element.removeClassName(substitute_str_el, "disable_lbl");
			}
		}
		if (form_el.varidable_flag[1].checked) {
			form_el.day.disabled = true;
			form_el.week.disabled = false;
			form_el.wday.disabled = false;
			form_el.substitute_flag.disabled = true;
			var substitute_str_el = form_el.substitute_flag.nextSibling;
			if (!Element.hasClassName(substitute_str_el, "disable_lbl")) {
				Element.addClassName(substitute_str_el, "disable_lbl");
			}
		}
	},
	showEditHoliday: function(event, id, holiday_id) {
		var top_el = $(this.id);
		var editholiday_params = new Object();

		editholiday_params["prefix_id_name"] = "popup_holiday";
		editholiday_params["action"] = "holiday_view_admin_edit";
		editholiday_params["holiday_id"] = holiday_id;
		commonCls.sendPopupView(event, editholiday_params, {"top_el":top_el,"modal_flag":true});
	},
	regHoliday: function(id, form_el) {
		var top_el = $(this.id);
		var regholiday_params = new Object();
		regholiday_params["param"] = "holiday_action_admin_edit&" + Form.serialize(form_el);
		regholiday_params["method"] = "post";
		regholiday_params["loading_el"] = top_el;
		regholiday_params["top_el"] = top_el;
		regholiday_params["target_el"] = top_el;
		regholiday_params["callbackfunc"] = function(res){
			commonCls.removeBlock(id);
		}.bind(this);
		
		commonCls.send(regholiday_params);
	},
	delHoliday: function(rrule_id, confirm_mess, second_confirm) {
		if (!commonCls.confirm(confirm_mess)) { return; }
		if (!commonCls.confirm(second_confirm)) { return; }
		
		var top_el = $(this.id);
		var delholiday_params = new Object();
		delholiday_params["param"] = "holiday_action_admin_delete&rrule_id=" + rrule_id;
		delholiday_params["method"] = "post";
		delholiday_params["loading_el"] = top_el;
		delholiday_params["top_el"] = top_el;
		delholiday_params["target_el"] = top_el;
		commonCls.send(delholiday_params);
	}
}
